<?php
/**
 * API class that allows static usage of the master INI controller
 *
 * @author SierraKomodo
 * @license GPL3
 */


namespace SierraKomodo\INIController;

use SierraKomodo\INIController\Controller as Controller;


/**
 * API class that allows static usage of the master INI controller
 *
 * Provides a static interface for direct manipulation of INI files. All methods in this class directly read from and
 * write to INI files.
 *
 * @api
 * @package SierraKomodo\INIController
 * @uses \SierraKomodo\INIController\Controller
 * @version 0.1.0-review.1 Peer review version 1. Currently in development; Not fully tested yet.
 */
class StaticController extends Controller
{
    /**
     * Adds/sets a specified key=value pair to an INI file.
     *
     * @param string $parFile
     * @param string $parSection
     * @param string $parKey
     * @param string $parValue
     * @param bool $parCreateFile If set to bool 'TRUE', will attempte to create $parFile if it doesn't already exist. Defaults to bool 'FALSE'
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     * @uses \SierraKomodo\INIController\Controller::writeFile()
     */
    public static function set(
        string $parFile,
        string $parSection,
        string $parKey,
        string $parValue,
        bool $parCreateFile = false
    ): bool {
        $self = new static;
        
        // Create the file if it doesn't exist
        if (!file_exists($parFile) and $parCreateFile) {
            touch($parFile);
        }
        
        // Read the INI file
        $self->readFile($parFile);
        
        // Set the new key=value pair
        $self->fileArray[$parSection][$parKey] = $parValue;
        
        // Write to the file
        return $self->writeFile($parFile);
    }
    
    
    /**
     * Fetches the full INI file as a multi-level associative array
     *
     * @param string $parFile
     * @return array In the format of $array['Section']['Key'] = 'Value'
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public static function fetchFile(string $parFile): array
    {
        $self = new static;
        
        // Read the INI file
        $self->readFile($parFile);
        
        // Return the full array
        return $self->fileArray;
    }
    
    
    /**
     * Fetches an INI section from a file as an associative array
     *
     * @param string $parFile
     * @param string $parSection
     * @return array|bool In the format of $array['Key'] = 'Value'. Returns boolean 'FALSE' if no matching section was found
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public static function fetchSection(string $parFile, string $parSection)
    {
        $self = new static;
        
        // Read the INI file
        $self->readFile($parFile);
        
        // Return the section, or false if the section doesn't exist
        if (isset($self->fileArray[$parSection])) {
            return $self->fileArray[$parSection];
        } else {
            return false;
        }
    }
    
    
    /**
     * Fetches the value of a requested key=value pair from an INI file.
     *
     * @param string $parFile
     * @param string $parSection
     * @param string $parKey
     * @return bool|string The value of the requested key=value pair, OR boolean 'FALSE' if no matching entry was found.
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     */
    public static function fetchKey(string $parFile, string $parSection, string $parKey)
    {
        $self = new static;
        
        // Read the INI file
        $self->readFile($parFile);
        
        // Return the key=value pair, or false if the entry doesn't exist
        if (isset($self->fileArray[$parSection][$parKey])) {
            return $self->fileArray[$parSection][$parKey];
        } else {
            return false;
        }
    }
    
    
    /**
     * Deletes a key=value pair from an INI file.
     *
     * @param string $parFile
     * @param string $parSection
     * @param string $parKey
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     * @uses \SierraKomodo\INIController\Controller::writeFile()
     */
    public static function delete($parFile, $parSection, $parKey = null)
    {
        $self = new static;
        
        // Read the INI file
        $self->readFile($parFile);
        
        // If $parKey is null, remove the whole section. Otherwise, remove only a specific key.
        if ($parKey == null) {
            unset($self->fileArray[$parSection]);
        } else {
            unset($self->fileArray[$parSection][$parKey]);
        }
        
        // Write to the file
        return $self->writeFile($parFile);
    }
}