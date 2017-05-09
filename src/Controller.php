<?php
/**
 * Master class that all other INI controllers in this package will extend.
 *
 * @author SierraKomodo
 * @license GPL3
 */


namespace SierraKomodo\INIController;


/**
 * Master class that all other INI controllers in this package will extend.
 *
 * This class handles all of the common, internal functions for the INI controller. You probably don't want to directly
 * use this class unless you're writing your own controller that extends it; Instead use one of the other classes that
 * extend this.
 *
 * @internal This class is designed for use by child classes that extend it, it is not meant to be used directly.
 * @package SierraKomodo\INIController
 * @used-by \SierraKomodo\INIController\ObjectController
 * @used-by \SierraKomodo\INIController\StaticController
 * @version 0.1.0-review.1 Peer review version 1. Currently in development; Not fully tested yet.
 */
class Controller
{
    /**
     * @var string $file Contains the filepath used in the last call to readFile()
     * @used-by \SierraKomodo\INIController\Controller::readFile()
     * @used-by \SierraKomodo\INIController\Controller::writeFile()
     */
    protected $file = '';
    /**
     * @var array $fileArray Array containing the output of parse_ini_file generated in the last call to readFile()
     * @used-by \SierraKomodo\INIController\Controller::readFile()
     * @used-by \SierraKomodo\INIController\Controller::writeFile()
     * @used-by \SierraKomodo\INIController\Controller::generateFile()
     */
    protected $fileArray = array();
    /**
     * @var string $fileContent Contains the output of the last call to generateFile()
     * @used-by \SierraKomodo\INIController\Controller::writeFile()
     * @used-by \SierraKomodo\INIController\Controller::generateFile()
     */
    protected $fileContent = '';
    
    
    /**
     * Reads the specified file.
     *
     * Reads the specified file, storing the file name in $this->file, output of parse_ini_file() in $this->fileArray,
     * and then running $this->generateFile() afterwards.
     *
     * @param string $parFile Full/relative path to the INI file to read. Defaults to $this->file
     * @return bool True on success
     * @throws \BadFunctionCallException If $parFile and $this->file are both null
     * @throws \RuntimeException If $this->file does not exist or is unreadable
     * @throws \RuntimeException If parse_ini_file() fails to parse $this->file
     * @uses \SierraKomodo\INIController\Controller::$file
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     * @uses \SierraKomodo\INIController\Controller::generateFile()
     */
    protected function readFile(string $parFile = null): bool
    {
        if ($parFile == null) {
            $parFile = $this->file;
        }
        
        // Input validation
        if ($parFile == null) {
            throw new \BadFunctionCallException("Parameter File must not be null if no filepath has previously been defined");
        }
        if (!is_file($parFile)) {
            throw new \RuntimeException("File '{$parFile}' does not exist or is inaccessable");
        }
        if (!is_readable($parFile)) {
            throw new \RuntimeException("File '{$parFile}' is not readable");
        }
        
        // Load and attempt to parse the INI file
        $array = parse_ini_file($parFile, true);
        if ($array === false) {
            throw new \RuntimeException("Failed to parse '{$parFile}' as an INI file");
        }
        
        // Set object variables
        $this->file      = $parFile;
        $this->fileArray = $array;
        $this->generateFile();
        
        // Indicate success
        return true;
    }
    
    
    /**
     * Writes data stored in $this->fileArray into $this->file
     *
     * @param string $parFile Full/relative path to the INI file to read. Defaults to $this->file
     * @return bool True on success
     * @throws \BadFunctionCallException If $parFile and $this->file are both null
     * @throws \RuntimeException If $this->file is not writable
     * @throws \RuntimeException If file_put_contents() fails to write to $this->file
     * @uses \SierraKomodo\INIController\Controller::$file
     * @uses \SierraKomodo\INIController\Controller::$fileContent
     * @uses \SierraKomodo\INIController\Controller::generateFile()
     */
    protected function writeFile(string $parFile = null): bool
    {
        if ($parFile == null) {
            $parFile = $this->file;
        }
        
        // Input validation
        if ($parFile == null) {
            throw new \BadFunctionCallException("Parameter File must not be null if no filepath has previously been defined");
        }
        if (file_exists($parFile) and !is_writable($parFile)) {
            throw new \RuntimeException("File '{$parFile}' is not writable");
        }
        
        // Ensure the latest changes to content are converted to the full content string
        $this->generateFile();
        
        // Write content string to file
        if (file_put_contents($parFile, $this->fileContent) === false) {
            throw new \RuntimeException("Failed to write content to '{$parFile}'");
        }
        
        // Indicate success
        return true;
    }
    
    
    /**
     * Generates a fully formatted INI string and stores it in $this->fileContent
     *
     * @return bool True on success
     * @uses    \SierraKomodo\INIController\Controller::$fileArray
     * @uses    \SierraKomodo\INIController\Controller::$fileContent
     * @used-by \SierraKomodo\INIController\Controller::readFile()
     * @used-by \SierraKomodo\INIController\Controller::writeFile()
     */
    protected function generateFile(): bool
    {
        $this->fileContent = '';
        
        // Convert array into formatted INI string
        foreach ($this->fileArray as $section => $block) {
            // Section header, on its own line
            $this->fileContent .= "[{$section}]\r\n";
            
            // Enter each key=value pair on separate lines
            foreach ($block as $key => $value) {
                $this->fileContent .= "{$key}={$value}\r\n";
            }
            
            // Blank lines between sections/at the end of the file
            $this->fileContent .= "\r\n";
        }
        
        // Indicate success
        return true;
    }
}