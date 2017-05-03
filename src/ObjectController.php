<?php
/**
 * API class that allows object/in memory usage of the master INI controller
 *
 * @author SierraKomodo
 * @license GPL3
 */


namespace SierraKomodo\INIController;

use SierraKomodo\INIController\Controller as Controller;


/**
 * API class that allows object/in memory usage of the master INI controller
 *
 * Provides an object for loading INI files into memory. Methods in this class will read from and write to data stored
 * in memory. INI files are only directly read or modified using the 'load' and 'save' methods.
 *
 * @api
 * @package SierraKomodo\INIController
 * @uses \SierraKomodo\INIController\Controller
 * @version 0.1.0-dev Currently in development; Not fully tested yet.
 */
class ObjectController extends Controller
{
    /**
     * ObjectController constructor. Alias of self::load()
     * @param string $parFile
     */
    public function __construct(string $parFile = null)
    {
        $this->load($parFile);
    }
    
    
    /**
     * Loads the specified INI file into memory.
     *
     * @param string $parFile The INI file to load into memory. Defaults to the last INI file that was loaded into memory or written to.
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::readFile()
     */
    public function load(string $parFile = null): bool
    {
        // Read the given file. parent::readFile already handles storing all information we care about at this stage
        return $this->readFile($parFile);
    }
    
    
    /**
     * Saves the INI data currently in memory to the specified file.
     *
     * @param string $parFile The INI file to save data to. Defaults to the last INI file that was loaded into memory or written to.
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::writeFile()
     */
    public function save(string $parFile = null): bool
    {
        // Write to the file
        return $this->writeFile($parFile);
    }
    
    
    /**
     * Adds/sets a key=value pair in memory
     *
     * @param string $parSection
     * @param string $parKey
     * @param string $parValue
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public function set(string $parSection, string $parKey, string $parValue): bool
    {
        // Set the new value
        $this->fileArray[$parSection][$parKey] = $parValue;
        
        // Indicate success
        return true;
    }
    
    
    /**
     * Fetches the full INI file from memory as a multi-level associative array
     *
     * @return array In the format of $array['Section']['Key'] = 'Value'
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public function fetchFile(): array
    {
        return $this->fileArray;
    }
    
    
    /**
     * Fetches an INI section from memory as an associative array
     *
     * @param string $parSection
     * @return array|bool In the format of $array['Key'] = 'Value'. Returns boolean 'FALSE' if no matching section was found
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public function fetchSection(string $parSection)
    {
        if (isset($this->fileArray[$parSection])) {
            return $this->fileArray[$parSection];
        } else {
            return false;
        }
    }
    
    
    /**
     * Fetches a value from a key=value pair
     *
     * @param string $parSection
     * @param string $parKey
     * @return string|bool Returns the value of a key=value pair OR boolean 'FALSE' if no matching entry was found
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public function fetchKey(string $parSection, string $parKey)
    {
        if (isset($this->fileArray[$parSection][$parKey])) {
            return $this->fileArray[$parSection][$parKey];
        } else {
            return false;
        }
    }
    
    
    /**
     * Deletes a key=value pair from memory
     *
     * @param string $parSection
     * @param string $parKey
     * @return bool True on success
     * @uses \SierraKomodo\INIController\Controller::$fileArray
     */
    public function delete(string $parSection, string $parKey = null): bool
    {
        // If $parKey is null, delete the entire section. Otherwise, delete the specific key.
        if ($parKey == null) {
            unset($this->fileArray[$parSection]);
        } else {
            unset($this->fileArray[$parSection][$parKey]);
        }
        
        // Indicate success
        return true;
    }
}