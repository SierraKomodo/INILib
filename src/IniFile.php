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
 * Leveraging an `SplFileObject` pointing to an INI file, this class provides in-memory reading and modifying of data
 *   stored in INI files. Methods are also provided to write any changes made in memory to the INI file.
 *
 * NOTE: Due to current limitations, values in the data array will be directly converted to strings when saved to file,
 *   with no other parsing/changes being performed (I.e., boolean `true` will be saved to file as string `1` instead of
 *   `true`, `on`, etc.)
 *
 * @package SierraKomodo\INILib
 * @version 1.0.0 First full release
 */
class IniFile
{
    /**
     * @var SplFileObject The INI file being read and modified by this class
     *
     * @used-by IniFile::__construct() to instantiate the SplFileObject
     * @used-by IniFile::saveDataToFile() to check if the file is writable, toggle file locks, and write to the file
     * @used-by IniFile::parseIniData() to acquire file locks and read from the file
     */
    protected $fileObject;
    
    /**
     * @var array The contents of the INI file, converted to a multi-layer array. Essentially, the output of
     *   `parse_ini_file()`
     *
     * @used-by IniFile::deleteEntry() to remove the specified entry from memory
     * @used-by IniFile::deleteSection() to remove the specified section from memory
     * @used-by IniFile::fetchDataArray() as the returned array
     * @used-by IniFile::fetchEntry() to retrieve the requested entry
     * @used-by IniFile::fetchSection() to retrieve the requested section
     * @used-by IniFile::setEntry() to add/modify the specified key=value pair in memory
     * @used-by IniFile::setSection() to add/modify the specified section in memory
     * @used-by IniFile::generateFileContent() to parse and format the data array into INI content
     * @used-by IniFile::parseIniData() to populate the data array with the parsed INI data
     */
    protected $iniDataArray = array();
    
    /**
     * @var int The INI scanner mode to use when running `parse_ini_*` operations. Should be one of the predefined
     *   `INI_SCANNER_*` options.
     *
     * @used-by IniFile::__construct() to pass on the `$parScannerMode` parameter
     * @used-by IniFile::parseIniData() to pass onto the `parse_ini_string` call used to parse INI data
     */
    protected $iniScannerMode;
    
    /**
     * @var bool Read-only flag. Determines if any write operations are allowed.
     *
     * @used-by IniFile::__construct() to pass on the `$parReadOnly` parameter
     * @used-by IniFile::saveDataToFile() to check if the object is set to read only mode
     * @used-by IniFile::setEntry() to check if the object is set to read only mode
     * @used-by IniFile::setSection() to check if the object is set to read only mode
     */
    protected $readOnly = false;
    
    
    /**
     * IniFile constructor.
     *
     * Creates an `SplFileObject` object using the provided filename and generates a data array from the file contents.
     *   If ReadOnly is `TRUE`, the `IniFile` object will not allow any operations that could modify the data array or
     *   the file itself.
     *
     * @param string $parFile The full or relative path to the INI file to initialize the `SplFileObject` with
     * @param bool $parReadOnly Optional - Default: `FALSE`. Read-only flag. See description above.
     * @param int $parScannerMode Optional - Default `INI_SCANNER_RAW`. The INI scanner mode to use. See the
     *   scanner_mode section of http://php.net/manual/en/function.parse-ini-file.php for details. Should be one of
     *   `INI_SCANNER_NORMAL`, `INI_SCANNER_TYPED`, or `INI_SCANNER_RAW`
     *
     * @throws IniFileException code `IniFileException::ERR_FILE_NOT_EXIST` if the provided filepath does not exist
     * @throws IniFileException code `IniFileException::ERR_INVALID_PARAMETER` if the scanner mode provided is not one
     *   of the accepted scanner modes.
     * @throws IniFileException code `IniFileException::ERR_FILE_NOT_READABLE` if the provided filepath is unreadable
     *   according to `SplFileObject::isReadable()`
     *
     * @uses IniFile::$fileObject to instantiate the SplFileObject and verify readability of the file
     * @uses IniFile::$iniScannerMode to pass on the `$parScannerMode` parameter
     * @uses IniFile::$readOnly to pass on the `$parReadOnly` parameter
     * @uses IniFile::parseIniData() to initialise `IniFile::iniDataArray`
     */
    public function __construct(string $parFile, bool $parReadOnly = false, int $parScannerMode = INI_SCANNER_RAW)
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
     * Parameters are trimmed of leading and trailing whitespace using `trim()` for consistency with the functionality
     *   of `IniFile::setKey()`
     *
     * Validation is not performed on section or key names as invalid names will simply have no effect on the data array
     *
     * @param string $parSection INI section to delete from
     * @param string $parKey INI key to delete
     *
     * @return void
     *
     * @throws IniFileException code `IniFileException::ERR_READ_ONLY_MODE` if the `$readOnly` property is set to `TRUE`
     *
     * @uses IniFile::$readOnly to check if the object is set to read only mode
     * @uses IniFile::$iniDataArray to remove the specified entry from memory
     */
    public function deleteEntry(string $parSection, string $parKey)
    {
        // Check for read only state
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_READ_ONLY_MODE
            );
        }
        
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey     = trim($parKey);
        
        // Modify the data array
        unset($this->iniDataArray[$parSection][$parKey]);
    }
    
    
    /**
     * Deletes a full section from memory
     *
     * Parameters are trimmed of leading and trailing whitespace using `trim()` for consistency with the functionality
     *   of `IniFile::setSection()`
     *
     * Validation is not performed on section or key names as invalid names will simply have no effect on the data array
     *
     * @param string $parSection INI section to delete
     *
     * @return void
     *
     * @throws IniFileException code `IniFileException::ERR_READ_ONLY_MODE` if the `$readOnly` property is set to `TRUE`
     *
     * @uses IniFile::$iniDataArray to remove the specified section from memory
     */
    public function deleteSection(string $parSection)
    {
        // Check for read only state
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_READ_ONLY_MODE
            );
        }
        
        // Trim whitespace
        $parSection = trim($parSection);
        
        // Modify the data array
        unset($this->iniDataArray[$parSection]);
    }
    
    
    /**
     * Getter for the full $iniDataArray, to prevent arbitrary modifications to the array.
     *
     * @return array[] The contents of `IniFile::$iniDataArray`. This will be a nested associative array in the format of
     *   `$array['Section']['Key'] = 'Value'`
     *
     * @uses IniFile::$iniDataArray as the returned array
     */
    public function fetchDataArray(): array
    {
        return $this->iniDataArray;
    }
    
    
    /**
     * Fetches a specified key=value pair from the data array. Alternative to using `IniFile::fetchDataArray()` to fetch
     *   the entire array
     *
     * NOTE: If `IniFile::$iniScannerMode` is set to `INI_SCANNER_TYPED`, any INI entries with a value of 'null' will
     *   be parsed by PHP as the literal constant `NULL`, and in turn this method will return `NULL` instead of a
     *   string.
     *
     * @param string $parSection INI section to fetch the entry from
     * @param string $parKey INI key to fetch
     *
     * @return mixed|null The requested value or `NULL` if no matching entry was found. Type of the returned value is
     *   dependent on `IniFile::$iniScannerMode` and the value entered in the INI file itself.
     *
     * @uses IniFile::$iniDataArray to retrieve the requested entry
     */
    public function fetchEntry(string $parSection, string $parKey)
    {
        // If the entry does not exist, return null (Prevents undefined index errors)
        if (array_key_exists($parSection, $this->iniDataArray) === false) {
            return null;
        }
        if (array_key_exists($parKey, $this->iniDataArray[$parSection]) === false) {
            return null;
        }
        
        // Return the value
        return $this->iniDataArray[$parSection][$parKey];
    }
    
    
    /**
     * Fetches a specified section array from the full data array. Alternative to using `IniFile::fetchDataArray()` to
     *   fetch the entire array
     *
     * @param string $parSection
     *
     * @return mixed[]|null An associative array of key=value pairs from the specified INI section, or `NULL` if no
     *   matching section was found
     *
     * @uses IniFile::$iniDataArray to retrieve the requested section
     */
    public function fetchSection(string $parSection)
    {
        // If the entry does not exist, return null (Prevents undefined index errors)
        if (array_key_exists($parSection, $this->iniDataArray) === false) {
            return null;
        }
        
        // Return the section
        return $this->iniDataArray[$parSection];
    }
    
    
    /**
     * Saves configuration data from memory into the INI file
     *
     * NOTE: This method will attempt to acquire an exclusive file lock before writing to the file.
     *
     * @return void
     *
     * @throws IniFileException code `IniFileException::ERR_READ_ONLY_MODE` if the `$readOnly` property is set to `TRUE`
     * @throws IniFileException code `IniFileException::ERR_FILE_NOT_WRITABLE` if the file is unwritable according to
     *   `SplFileObject::isWritable()`
     * @throws IniFileException code `IniFileException::ERR_FILE_LOCK_FAILED` if `SplFileObject::flock()` failed to
     *   acquire an exclusive lock for writing
     * @throws IniFileException code `IniFileException::ERR_FILE_READ_WRITE_FAILED` if the file write operations failed
     *
     * @uses IniFile::$fileObject to check if the file is writable, toggle file locks, and write to the file
     * @uses IniFile::$readOnly to check if the object is set to read only mode
     * @uses IniFile::generateFileContent() to generate properly formatted INI content from the data array
     */
    public function saveDataToFile()
    {
        // Check if read-only flag is set
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_READ_ONLY_MODE
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
     * @param string $parSection INI section to modify
     * @param string $parKey INI key to add/change
     * @param string $parValue Desired new value
     *
     * @return void
     *
     * @throws IniFileException code `IniFileException::ERR_READ_ONLY_MODE` if the `$readOnly` property is set to `TRUE`
     * @throws IniFileException code `IniFileException::ERR_INVALID_PARAMETER` if the section, key, or value failed
     *   validation checks
     *
     * @uses IniFile::$iniDataArray to add/modify the specified key=value pair in memory
     * @uses IniFile::$readOnly to check if the object is set to read only mode
     * @uses IniFile::validateKey() to validate the key parameter
     * @uses IniFile::validateSection() to validate the section parameter
     * @uses IniFile::validateValue() to validate the value parameter
     */
    public function setEntry(string $parSection, string $parKey, string $parValue)
    {
        // Check for read only state
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_READ_ONLY_MODE
            );
        }
        
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
     * @param string $parSection INI section to add/modify
     * @param array[] $parKeyValuePairs An associative array of key=value pairs the INI section should contain
     * @param bool $parMergeArrays Optional - Default `FALSE`. If set to `TRUE`, existing entries under the given
     *   section name will be merged with the new data using `array_merge()`. Key name conflicts will be overwritten by
     *   the new data.
     *
     * @return void
     *
     * @throws IniFileException code `IniFileException::ERR_READ_ONLY_MODE` if the `$readOnly` property is set to `TRUE`
     * @throws IniFileException code `IniFileException::ERR_INVALID_PARAMETER` if the section or any keys or values from
     *   the `$parKeyValuePairs` parameter failed validation checks
     *
     * @uses IniFile::$readOnly to check if the object is set to read only mode
     * @uses IniFile::$iniDataArray to add/modify the specified section in memory
     * @uses IniFile::validateKey() to validate the each key name in the `$parKeyValuePairs` parameter
     * @uses IniFile::validateSection() to validate the section parameter
     * @uses IniFile::validateValue() to validate the each value in the `$parKeyValuePairs` parameter
     */
    public function setSection(string $parSection, array $parKeyValuePairs, bool $parMergeArrays = false)
    {
        // Check for read only state
        if ($this->readOnly === true) {
            throw new IniFileException(
                'IniFile object is in read only mode',
                IniFileException::ERR_READ_ONLY_MODE
            );
        }
        
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
     * NOTE: Line breaks use the `PHP_EOL` built-in constant. This may cause some inconsistency between files generated
     *   on windows based systems (`\r\n`) and unix based systems (`\n`).
     *
     * @return string The formatted string of INI data
     *
     * @uses IniFile::$iniDataArray to parse and format the data array into INI content
     *
     * @used-by IniFile::saveDataToFile() to generate properly formatted INI content from the data array
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
     *
     * @throws IniFileException code `IniFileException::ERR_FILE_LOCK_FAILED` if `SplFileObject::flock()` failed to
     *   acquire a shared lock for reading
     * @throws IniFileException code `IniFileException::ERR_FILE_READ_WRITE_FAILED` if `SplFileObject` read operations
     *   failed
     * @throws IniFileException code `IniFileException::ERR_INI_PARSE_FAILED` if `parse_ini_string` failed to parse file
     *   contents
     *
     * @uses IniFile::$fileObject to acquire file locks and read from the file
     * @uses IniFile::$iniDataArray to populate the data array with the parsed INI data
     * @uses IniFile::$iniScannerMode to pass onto the `parse_ini_string` call used to parse INI data
     *
     * @used-by IniFile::__construct() to initialise `IniFile::iniDataArray`
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
     *
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     *
     * @used-by IniFile::setEntry() to validate the key parameter
     * @used-by IniFile::setSection() to validate the each key name in the `$parKeyValuePairs` parameter
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
     *
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     *
     * @used-by IniFile::setEntry() to validate the section parameter
     * @used-by IniFile::setSection() to validate the section parameter
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
     *
     * @return bool|string True if the input is valid, or a string containing details on why the input is invalid
     *
     * @used-by IniFile::setEntry() to validate the value parameter
     * @used-by IniFile::setSection() to validate the each value in the `$parKeyValuePairs` parameter
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
