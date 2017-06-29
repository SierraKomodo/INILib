<?php
/**
 * INI file parsing and manipulation library.
 *
 * @author SierraKomodo
 * @license GPL3
 */

namespace SierraKomodo\INILib;

use SplFileObject;

/**
 * Primary INI library class
 *
 * Leveraging a provided SplFileObject pointing to an INI file, this class provides in-memory reading and modifying of
 *   data stored in INI files. Methods are also provided to write any changes made in memory to the INI file.
 *
 * @package SierraKomodo\INILib
 * @version 0.1.0-review.3 Peer review version 3. Currently in development; Not fully tested yet.
 */
class IniFile
{
    /**
     * @var SplFileObject The INI file being read and modified by this class
     * @used-by IniFile::__construct()
     * @used-by IniFile::parseIniData()
     */
    protected $fileObject;
    /**
     * @var array The contents of the INI file, converted to a multi-layer array (Same format as \parse_ini_file())
     * @used-by IniFile::parseIniData()
     * @used-by IniFile::generateFileContent()
     */
    protected $iniDataArray = array();
    /**
     * @var int The INI scanner mode to use when running parse_ini_* operations. Should be one of the predefined
     *   INI_SCANNER_* options.
     * @used-by IniFile::__construct()
     */
    protected $iniScannerMode;
    /**
     * @var bool Read-only flag. Determines if any write operations are allowed.
     * @used-by IniFile::__construct()
     */
    protected $readOnly = false;
    
    
    /**
     * IniFile constructor.
     *
     * @param string $parFile The full or relative path to the INI file to initialize the `SplFileObject` with
     * @param bool $parReadOnly Read-only flag
     * @param int $parScannerMode See parseINIData() parameter $parScannerMode
     * @uses IniFile::$fileObject
     * @uses IniFile::parseIniData()
     * @throws IniFileException for invalid parameters, if the file doesn't exist, or if the file is not readable
     */
    public function __construct(string $parFile, bool $parReadOnly = false, int $parScannerMode = INI_SCANNER_TYPED)
    {
        // Parameter validation
        if (file_exists($parFile) === false) {
            throw new IniFileException(
                "The file {$parFile} does not exist",
                IniFileException::ERR_FILE_NOT_EXIST
            );
        }
        
        if (in_array($parScannerMode, [INI_SCANNER_NORMAL, INI_SCANNER_TYPED, INI_SCANNER_RAW], true) === false) {
            throw new IniFileException(
                'Provided scanner mode is invalid. Must be one of `INI_SCANNER_NORMAL`, `INI_SCANNER_TYPED`, or `INI_SCANNER_RAW`',
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        
        // Create the SplFileObject
        if ($parReadOnly === true) {
            $this->fileObject = new SplFileObject($parFile, 'r');
        } else {
            $this->fileObject = new SplFileObject($parFile, 'r+');
        }
        
        // Verify the file is readable by `SplFileObject` - This validation is done here, in the off chance
        //  `is_readable()` returns `true`, but `SplFileObject::isReadable()` returns `false`
        if ($this->fileObject->isReadable() === false) {
            throw new IniFileException(
                "The file {$this->fileObject->getPathname()} could not be read",
                IniFileException::ERR_FILE_NOT_READABLE
            );
        }
        
        $this->readOnly       = $parReadOnly;
        $this->iniScannerMode = $parScannerMode;
        $this->parseIniData();
    }
    
    
    /**
     * Deletes a key=value pair from a specified section header in memory
     *
     * Parameters are trimmed of leading and trailing whitespace using trim() for consistency with the functionality of
     *   $this->setKey()
     *
     * @param string $parSection INI section
     * @param string $parKey INI key
     * @return void
     * @uses IniFile::$iniDataArray
     */
    public function deleteEntry(string $parSection, string $parKey)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey     = trim($parKey);
        
        // Omitting parameter validations - As this method only deletes existing entries, any invalid section or key
        //  names will just have no effect.
        
        // Modify the data array
        if (!empty($this->iniDataArray[$parSection][$parKey])) {
            unset($this->iniDataArray[$parSection][$parKey]);
        }
    }
    
    
    /**
     * Deletes a full section from memory
     *
     * @param string $parSection
     * @return void
     * @uses IniFile::$iniDataArray
     */
    public function deleteSection(string $parSection)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        
        // Modify the data array
        if (!empty($this->iniDataArray[$parSection])) {
            unset($this->iniDataArray[$parSection]);
        }
    }
    
    
    /**
     * Getter for $iniDataArray, to prevent arbitrary modifications to the array.
     *
     * @return array $this->$iniDataArray
     * @uses IniFile::$iniDataArray
     */
    public function fetchDataArray(): array
    {
        return $this->iniDataArray;
    }
    
    
    /**
     * Fetches a specified key=value pair from the data array. Alternative to using dataArray() to fetch the entire array
     *
     * @param string $parSection
     * @param string $parKey
     * @return mixed|null The requested value or `NULL` if no matching entry was found
     * @uses IniFile::$iniDataArray
     */
    public function fetchEntry(string $parSection, string $parKey)
    {
        // If the entry is empty, return null
        if (empty($this->iniDataArray[$parSection][$parKey])) {
            return null;
        }
        
        // Return the value
        return $this->iniDataArray[$parSection][$parKey];
    }
    
    
    /**
     * Fetches a specified section array from the full data array.
     *
     * @param string $parSection
     * @return array|null A key indexed array of values from the specified INI section, or `NULL` if no matching entry
     *   was found
     * @uses IniFile::$iniDataArray
     */
    public function fetchSection(string $parSection)
    {
        // If the entry is empty, return null
        if (empty($this->iniDataArray[$parSection])) {
            return null;
        }
        
        // Return the section
        return $this->iniDataArray[$parSection];
    }
    
    
    /**
     * Saves configuration data from memory into the INI file
     *
     * @return void
     * @throws IniFileException If the read only flag is set, the file could not be locked, or if there was some other
     *   failure with write operations
     * @uses IniFile::$fileObject
     * @uses IniFile::generateFileContent()
     */
    public function saveDataToFile()
    {
        // Check if read-only flag is set
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_FILE_NOT_WRITABLE
            );
        }
        
        // Check if file is writable
        if ($this->fileObject->isWritable() === false) {
            throw new IniFileException(
                "File is not writable by the SplFileObject",
                IniFileException::ERR_FILE_NOT_WRITABLE
            );
        }
        
        // Lock the file for writing
        if ($this->fileObject->flock(LOCK_EX) === false) {
            throw new IniFileException(
                "Failed to acquire an exclusive file lock",
                IniFileException::ERR_FILE_LOCK_FAILED
            );
        }
        
        // Clear current file contents
        if ($this->fileObject->ftruncate(0) === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new IniFileException(
                "Failed to clear current data",
                IniFileException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Set pointer to start of file
        $this->fileObject->rewind();
        
        // Generate formatted INI file content and write to file
        if ($this->fileObject->fwrite($this->generateFileContent()) === null) {
            $this->fileObject->flock(LOCK_UN);
            throw new IniFileException(
                "Failed to write data to file",
                IniFileException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Unlock the file when done
        $this->fileObject->flock(LOCK_UN);
    }
    
    
    /**
     * Sets a key=value pair within an INI section header in memory.
     *
     * Data passed into parameters is validated to ensure generated INI files will be properly formatted and will not
     *   produce any parsing errors.
     *
     * Parameters are also trimmed of leading and trailing whitespace prior to validation for INI formatting purposes
     *   (I.e., saving '[section]' instead of '[ section ]', or 'key=value' instead of 'key = value'. This allows for
     *   better consistency in writing and reading of INI files between this class, parse_ini_* functions, and any other
     *   programs written in other languages that may need to access these files.
     *
     * @param string $parSection INI section
     * @param string $parKey INI key
     * @param string $parValue Desired new value
     * @return void
     * @throws IniFileException if any parameters do not fit proper INI formatting or would cause INI parsing errors if
     *   saved to a file
     * @uses IniFile::$iniDataArray
     */
    public function setEntry(string $parSection, string $parKey, string $parValue)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey     = trim($parKey);
        $parValue   = trim($parValue);
        
        // Parameter validations
        $check = $this->validateSection($parSection);
        if ($check !== true) {
            throw new IniFileException(
                "Parameter 1 (section name) {$check}",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        
        $check = $this->validateKey($parKey);
        if ($check !== true) {
            throw new IniFileException(
                "Parameter 2 (key name) {$check}",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        
        $check = $this->validateValue($parValue);
        if ($check !== true) {
            throw new IniFileException(
                "Parameter 3 (value) {$check}",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        
        // Modify the data array
        $this->iniDataArray[$parSection][$parKey] = $parValue;
    }
    
    
    /**
     * Sets a full section within the INI data in memory
     *
     * Data passed into parameters is validated to ensure generated INI files will be properly formatted and will not
     *   produce any parsing errors.
     *
     * Parameters are also trimmed of leading and trailing whitespace prior to validation for INI formatting purposes
     *   (I.e., saving '[section]' instead of '[ section ]', or 'key=value' instead of 'key = value'. This allows for
     *   better consistency in writing and reading of INI files between this class, parse_ini_* functions, and any other
     *   programs written in other languages that may need to access these files.
     *
     * @param string $parSection INI section name
     * @param array $parKeyValuePairs An associative array of key=value pairs the INI section should contain
     * @param bool $parMergeArrays Default `FALSE`. If set to `TRUE`, existing entries under the given section name will
     *   be merged with the new data. Key name conflicts will be overwritten by the new data.
     * @return void
     * @throws IniFileException if any parameters do not fit proper INI formatting or would cause INI parsing errors if
     *   saved to a file
     */
    public function setSection(string $parSection, array $parKeyValuePairs, bool $parMergeArrays = false)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        foreach ($parKeyValuePairs as $key => $value) {
            unset($parKeyValuePairs[$key]);
            $parKeyValuePairs[trim($key)] = trim($value);
        }
        
        // Parameter validations
        $check = $this->validateSection($parSection);
        if ($check !== true) {
            throw new IniFileException(
                "Parameter 1 (section name) {$check}",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        
        foreach ($parKeyValuePairs as $key => $value) {
            $check = $this->validateKey($key);
            if ($check !== true) {
                throw new IniFileException(
                    "Parameter 2 (key=value pair list) keys {$check}",
                    IniFileException::ERR_INVALID_PARAMETER
                );
            }
            
            $check = $this->validateValue($value);
            if ($check !== true) {
                throw new IniFileException(
                    "Parameter 2 (key=value pair list) values {$check}",
                    IniFileException::ERR_INVALID_PARAMETER
                );
            }
        }
        
        // Modify the data array
        if ($parMergeArrays === true) {
            // Merge section if Merge Arrays is set
            // Verify the section already exists before running `array_merge()` to prevent undefined index errors
            if (isset($this->iniDataArray[$parSection])) {
                $this->iniDataArray[$parSection] = array_merge($this->iniDataArray[$parSection], $parKeyValuePairs);
            } else {
                $this->iniDataArray[$parSection] = $parKeyValuePairs;
            }
        } else {
            // Overwrite section if Merge Arrays is not set
            $this->iniDataArray[$parSection] = $parKeyValuePairs;
        }
    }
    
    
    /**
     * Generates a formatted string of INI data, primarily used for writing to INI files
     *
     * @return string The formatted string of INI data
     * @uses    IniFile::$iniDataArray
     * @used-by IniFile::saveDataToFile()
     */
    protected function generateFileContent()
    {
        // Convert data array to formatted INI string
        $iniString = '';
        foreach ($this->iniDataArray as $section => $keyPair) {
            $iniString .= "[{$section}]" . PHP_EOL;
            
            foreach ($keyPair as $key => $value) {
                $iniString .= "{$key}={$value}" . PHP_EOL;
            }
            
            // Extra line break after sections for readability purposes
            $iniString .= PHP_EOL;
        }
        
        return $iniString;
    }
    
    
    /**
     * Reads the INI file and stores the contents into memory as a multi-layered array.
     *
     * Format of key=value pairs is dependent on `IniFile::$iniScannerMode` Any 'unsaved changes' to the INI data in
     *   memory are lost.
     *
     * Note that if the file is empty (Has a file size of 0), this method will store an empty array instead or reading
     *   the file, due to parameter restrictions in the SplFileObject::fread() method.
     *
     * @return void
     * @uses IniFile::$fileObject
     * @uses IniFile::$iniDataArray
     * @throws IniFileException if the file could not be locked, read, or parsed
     */
    protected function parseIniData()
    {
        // If file size is 0, set an empty array - fread() will fail otherwise
        if ($this->fileObject->getSize() == 0) {
            $this->iniDataArray = array();
            return;
        }
        
        // Lock the file for reading
        if ($this->fileObject->flock(LOCK_SH) === false) {
            throw new IniFileException(
                "Failed to acquire a shared file lock",
                IniFileException::ERR_FILE_LOCK_FAILED
            );
        }
        
        // Set pointer to start of file
        $this->fileObject->rewind();
        
        // Pull the file's contents
        $fileContents = $this->fileObject->fread($this->fileObject->getSize());
        if ($fileContents === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new IniFileException(
                "Failed to read data from file",
                IniFileException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Unlock file when done
        $this->fileObject->flock(LOCK_UN);
        
        // Parse data into data array
        $result = parse_ini_string($fileContents, true, $this->iniScannerMode);
        if ($result === false) {
            throw new IniFileException(
                "Failed to parse file contents",
                IniFileException::ERR_INI_PARSE_FAILED
            );
        }
        $this->iniDataArray = $result;
    }
    
    
    /**
     * Validates a given string is a properly formatted INI key name
     *
     * Invalid inputs include line breaks (`\r` and `\n`) and specific 'control' characters such as those that define
     *   sections (`[` and `]`), comments (`;` and `#`), and separate keys from values (`=`)
     *
     * @param string $parKey
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     */
    protected function validateKey(string $parKey)
    {
        // Check for line breaks or specific 'key' characters:
        //   [ and ] are 'control' characters for section names
        //   ; and # are 'control' characters that designate the start of comments
        //   = is a 'control' character that separates key from value
        if (preg_match('/\[|\]|\r|\n|=|^(;|#)/', $parKey)) {
            return "cannot contain the characters '[', ']', ';', '#', '=', or line breaks";
        }
        
        // If all checks passed, return true
        return true;
    }
    
    
    /**
     * Validates a given string is a properly formatted INI section name
     *
     * Invalid inputs include line breaks (`\r` and `\n`) and specific 'control' characters such as those that define
     *   sections (`[` and `]`)
     *
     * @param string $parSection
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     */
    protected function validateSection(string $parSection)
    {
        // Check for line breaks or specific 'key' characters:
        //   [ and ] are 'control' characters for section names
        if (preg_match('/\[|\]|\r|\n/', $parSection)) {
            return "cannot contain the characters '[', ']', ';', '#', or line breaks";
        }
        
        // If all checks passed, return true
        return true;
    }
    
    
    /**
     * Validates a given string is a properly formatted INI value
     *
     * Invalid inputs include line breaks (`\r` and `\n`)
     *
     * @param string $parValue
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     */
    protected function validateValue(string $parValue)
    {
        // Check for line breaks
        if (preg_match('/\r|\n/', $parValue)) {
            return "cannot contain line breaks";
        }
        
        // If all checks passed, return true
        return true;
    }
}
