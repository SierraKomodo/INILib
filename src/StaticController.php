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
 * @version 0.1.0-dev Currently in development; Not fully tested yet.
 */
class StaticController extends Controller
{
    /**
     * @var array $fileArray Array containing the INI data from self::readFile(). Converted to a static property for use
     * in static functions.
     * @used-by \SierraKomodo\INIController\StaticController::set()
     * @used-by \SierraKomodo\INIController\StaticController::fetch()
     * @used-by \SierraKomodo\INIController\StaticController::delete()
     */
    protected static $fileArray = array();
    
    
    /**
     * Adds/sets a specified key=value pair to an INI file.
     *
     * @param string $parFile
     * @param string $parSection
     * @param string $parKey
     * @param string $parValue
     * @return bool True on success
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     * @uses \SierraKomodo\INIController\Controller::writeFile()
     */
    public static function set($parFile, $parSection, $parKey, $parValue)
    {
        // Read the INI file
        self::readFile($parFile);
        
        // Set the new key=value pair
        self::$fileArray[$parSection][$parKey] = $parValue;
        
        // Write to the file
        return self::writeFile($parFile);
    }
    
    
    /**
     * Fetches the value of a requested key=value pair from an INI file.
     *
     * @param string $parFile
     * @param string $parSection
     * @param string $parKey
     * @return bool|string The value of the requested key=value pair, or FALSE if no matching entry was found.
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     */
    public static function fetch($parFile, $parSection, $parKey)
    {
        // Read the INI file
        self::readFile($parFile);
        
        // Return the key=value pair, or false if the entry doesn't exist
        if (isset(self::$fileArray[$parSection][$parKey])) {
            return self::$fileArray[$parSection][$parKey];
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
     * @uses \SierraKomodo\INIController\StaticController::$fileArray
     * @uses \SierraKomodo\INIController\Controller::readFile()
     * @uses \SierraKomodo\INIController\Controller::writeFile()
     */
    public static function delete($parFile, $parSection, $parKey = null)
    {
        // Read the INI file
        self::readFile($parFile);
        
        // If $parKey is null, remove the whole section. Otherwise, remove only a specific key.
        if ($parKey == null) {
            unset(self::$fileArray[$parSection]);
        } else {
            unset(self::$fileArray[$parSection][$parKey]);
        }
        
        // Write to the file
        return self::writeFile($parFile);
    }
}