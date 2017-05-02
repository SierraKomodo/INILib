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
     * ObjectController constructor.
     * @param string $parFile
     */
    public function __construct($parFile = null)
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
    public function load($parFile = null)
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
    public function save($parFile = null)
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
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     */
    public function set($parSection, $parKey, $parValue)
    {
        // Set the new value
        $this->fileArray[$parSection][$parKey] = $parValue;
        
        // Indicate success
        return true;
    }
    
    
    /**
     * Fetches the data from memory.
     *
     * This method can be used to retrieve the entirety of the INI data as a multi-level associative array, an entire INI section as an associative array, or a specific key=value pair.
     *
     * @param string $parSection
     * @param string $parKey
     * @return array|bool|string Returns one of four things: 1, if Section is ommitted/null, returns the entire associative array of INI entries loaded in memory in the format of $array['Section]['Key'] = 'Value'; 2, if Key is ommitted/null, returns the entire associative array of a specific INI section in the format of $array['Key'] = 'Value'; 3, returns a string containing the value of a requested key; 4, in any of the previous situations, returns 'false' if there was no entry for the section and/or key.
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     */
    public function fetch($parSection = null, $parKey = null)
    {
        if ($parSection == null) {
            return $this->fileArray;
        } elseif ($parKey == null) {
            if (isset($this->fileArray[$parSection])) {
                return $this->fileArray[$parSection];
            } else {
                return false;
            }
        } else {
            if (isset($this->fileArray[$parSection][$parKey])) {
                return $this->fileArray[$parSection][$parKey];
            } else {
                return false;
            }
        }
    }
    
    
    /**
     * Deletes a key=value pair from memory
     *
     * @param string $parSection
     * @param string $parKey
     * @return bool True on success
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     */
    public function delete($parSection, $parKey = null)
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