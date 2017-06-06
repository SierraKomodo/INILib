<?php
/**
 * INI file parsing and manipulation library.
 *
 * @author SierraKomodo
 * @license GPL3
 */

namespace SierraKomodo\INILib;

/**
 * Primary INI library class
 *
 * Leveraging a provided SplFileObject pointing to an INI file, this class provides in-memory reading and modifying of
 *   data stored in INI files. Methods are also provided to write any changes made in memory to the INI file.
 *
 * @package SierraKomodo\INIController
 * @version 0.1.0-review.2 Peer review version 2. Currently in development; Not fully tested yet.
 */
class INILib
{
    /**
     * @var \SplFileObject The INI file being read and modified by this class
     * @used-by INILib::__construct()
     * @used-by INILib::parseINIData()
     */
    protected $fileObject;
    /**
     * @var array The contents of the INI file, converted to a multi-layer array (Same format as \parse_ini_file())
     * @used-by INILib::parseINIData()
     * @used-by INILib::generateFileContent()
     */
    protected $iniDataArray = array();
    
    const FETCH_STRING = 1;
    const FETCH_MIXED  = 2;
    
    
    /**
     * INILib constructor.
     *
     * @param \SplFileObject $parFile The INI file to initialize the object with
     * @param int $parScannerMode See parseINIData() parameter $parScannerMode
     * @uses INILib::$fileObject
     * @uses INILib::parseINIData()
     */
    public function __construct(\SplFileObject $parFile, int $parScannerMode = INI_SCANNER_NORMAL)
    {
        $this->fileObject = $parFile;
        $this->parseINIData($parScannerMode);
    }
    
    
    /**
     * Getter for $iniDataArray, to prevent arbitrary modifications to the array.
     *
     * @return array $this->$iniDataArray
     * @uses INILib::$iniDataArray
     */
    public function dataArray(): array
    {
        return $this->iniDataArray;
    }
    
    
    /**
     * Fetches a specified key=value pair from the data array. Alternative to using dataArray() to fetch the entire array
     *
     * @param string $parSection
     * @param string $parKey
     * @return mixed|null The requested value (Type dependent on scanner mode used in the last parseINIData() call), or
     *   NULL if no matching entry was found
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
     * Reads the INI file and stores the contents into memory as a multi-layered array.
     *
     * Any 'unsaved changes' to the INI data in memory are lost.
     *
     * @param int $parScannerMode One of INI_SCANNER_NORMAL, INI_SCANNER_RAW, INI_SCANNER_TYPED.
     *   Defaults to INI_SCANNER_NORMAL. See Parameters > scanner_mode here:
     *   http://php.net/manual/en/function.parse-ini-string.php
     * @uses INILib::$fileObject
     * @uses INILib::$iniDataArray
     * @throws INILibException if the file could not be locked, read, or parsed
     */
    public function parseINIData(int $parScannerMode = INI_SCANNER_NORMAL)
    {
        // Lock the file for reading
        if ($this->fileObject->flock(LOCK_SH) === false) {
            throw new INILibException(
                "Failed to acquire a shared file lock",
                INILibException::ERR_FILE_LOCK_FAILED
            );
        }
        
        // Set pointer to start of file
        $this->fileObject->rewind();
        
        // Pull the file's contents
        $fileContents = $this->fileObject->fread($this->fileObject->getSize());
        // TODO: Test possibility of using fpassthru() instead of fread()
        // $fileContents = $this->fileObject->fpassthru();
        if ($fileContents === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new INILibException(
                "Failed to read data from file",
                INILibException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Parse data into data array
        $result = parse_ini_string($fileContents, true, $parScannerMode);
        if ($result === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new INILibException(
                "Failed to parse file contents",
                INILibException::ERR_INI_PARSE_FAILED
            );
        }
        $this->iniDataArray = $result;
        
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
     * @throws INILibException if any parameters do not fit proper INI formatting or would cause INI parsing errors if
     *   saved to a file
     * @uses INILib::$iniDataArray
     */
    public function setKey(string $parSection, string $parKey, string $parValue)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey     = trim($parKey);
        $parValue   = trim($parValue);
        
        // Parameter validations
        // As [ and ] are 'control' characters for sections, they shouldn't exist in section names
        if ((strpos($parSection, '[') !== false) or (strpos($parSection, ']') !== false)) {
            throw new INILibException(
                "Parameter 1 (section name) cannot contain the characters '[' or ']'",
                INILibException::ERR_INVALID_PARAMETER
            );
        }
        // For similar reasons as above, a key name should not start with [
        if (substr($parKey, 0, 1) == '[') {
            throw new INILibException(
                "First character of parameter 2 (key name) cannot be '['",
                INILibException::ERR_INVALID_PARAMETER
            );
        }
        // A key name should also not contain =, as this is a control character that separates key from value
        if (strpos($parKey, '=') !== false) {
            throw new INILibException(
                "Parameter 2 (key name) cannot contain the character '='",
                INILibException::ERR_INVALID_PARAMETER
            );
        }
        // Section and key should not start with ; or #, as these are used to denote comments. Handling of comments is
        //  outside the scope of this class.
        if ((substr($parSection, 0, 1) == '#') or (substr($parSection, 0, 1) == ';') or (substr($parKey, 0, 1) == '#') or (substr($parKey, 0, 1) == ';')) {
            throw new INILibException(
                "First character of parameters 1 (section name) and 2 (key name) cannot be '#' or ';'",
                INILibException::ERR_INVALID_PARAMETER
            );
        }
        
        // Modify the data array
        $this->iniDataArray[$parSection][$parKey] = $parValue;
    }
    
    
    /**
     * Deletes a key=value pair from a specified section header in memory
     *
     * Parameters are trimmed of leading and trailing whitespace using trim() for consistency with the functionality of
     *   $this->setKey()
     *
     * @param string $parSection INI section
     * @param string $parKey INI key
     * @uses INILib::$iniDataArray
     */
    public function deleteKey(string $parSection, string $parKey)
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
     * Saves configuration data from memory into the INI file
     *
     * @throws INILibException If the file could not be locked, or if there was some other failure with write operations
     * @uses INILib::$fileObject
     * @uses INILib::generateFileContent()
     */
    public function saveData()
    {
        // Check if file is writable
        if ($this->fileObject->isWritable() === false) {
            throw new INILibException(
                "File is not writable. Did you set the SplFileObject's open mode?",
                INILibException::ERR_FILE_NOT_WRITABLE
            );
        }
        
        // Lock the file for writing
        if ($this->fileObject->flock(LOCK_EX) === false) {
            throw new INILibException(
                "Failed to acquire an exclusive file lock",
                INILibException::ERR_FILE_LOCK_FAILED
            );
        }
        
        // Set pointer to start of file
        $this->fileObject->rewind();
        
        // Clear current file contents
        if ($this->fileObject->ftruncate(0) === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new INILibException(
                "Failed to clear current data",
                INILibException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Generate formatted INI file content and write to file
        if ($this->fileObject->fwrite($this->generateFileContent()) === null) {
            $this->fileObject->flock(LOCK_UN);
            throw new INILibException(
                "Failed to write data to file",
                INILibException::ERR_FILE_READ_WRITE_FAILED
            );
        }
        
        // Unlock the file when done
        $this->fileObject->flock(LOCK_UN);
    }
    
    
    /**
     * Generates a formatted string of INI data, primarily used for writing to INI files
     *
     * @return string The formatted string of INI data
     * @uses    INILib::$iniDataArray
     * @used-by INILib::saveData()
     */
    public function generateFileContent()
    {
        // Convert data array to formatted INI string
        $iniString = '';
        foreach ($this->iniDataArray as $section => $keyPair) {
            $iniString .= "[{$section}]\r\n";
            
            foreach ($keyPair as $key => $value) {
                $iniString .= "{$key}={$value}\r\n";
            }
            
            // Extra line break after sections for readability purposes
            $iniString .= "\r\n";
        }
        
        return $iniString;
    }
}
