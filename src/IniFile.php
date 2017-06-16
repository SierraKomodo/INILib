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
     * @used-by IniFile::parseINIData()
     */
    protected $fileObject;
    /**
     * @var array The contents of the INI file, converted to a multi-layer array (Same format as \parse_ini_file())
     * @used-by IniFile::parseINIData()
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
     * IniFile constructor.
     *
     * @param SplFileObject $parFile The INI file to initialize the object with
     * @param int $parScannerMode See parseINIData() parameter $parScannerMode
     * @uses IniFile::$fileObject
     * @uses IniFile::parseINIData()
     * @throws IniFileException for invalid parameters
     */
    public function __construct(SplFileObject $parFile, int $parScannerMode = INI_SCANNER_TYPED)
    {
        // Parameter validation
        if (in_array($parScannerMode, [INI_SCANNER_NORMAL, INI_SCANNER_TYPED, INI_SCANNER_RAW], true) === false) {
            throw new IniFileException(
                'Provided scanner mode is invalid. Must be one of `INI_SCANNER_NORMAL`, `INI_SCANNER_TYPED`, or `INI_SCANNER_RAW`',
                IniFileException::ERR_INVALID_PARAMETER
            );
        }

        $this->fileObject = $parFile;
        $this->iniScannerMode = $parScannerMode;
        $this->parseINIData();
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
     * Format of key=value pairs is dependent on `IniFile::$iniScannerMode` Any 'unsaved changes' to the INI data in
     *   memory are lost.
     *
     * @uses IniFile::$fileObject
     * @uses IniFile::$iniDataArray
     * @throws IniFileException if the file could not be locked, read, or parsed
     */
    public function parseINIData()
    {
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
     * @throws IniFileException if any parameters do not fit proper INI formatting or would cause INI parsing errors if
     *   saved to a file
     * @uses IniFile::$iniDataArray
     */
    public function setEntry(string $parSection, string $parKey, string $parValue)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey = trim($parKey);
        $parValue = trim($parValue);

        // Parameter validations
        // As [ and ] are 'control' characters for sections, they shouldn't exist in section names
        if ((strpos($parSection, '[') !== false) or (strpos($parSection, ']') !== false)) {
            throw new IniFileException(
                "Parameter 1 (section name) cannot contain the characters '[' or ']'",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        // For similar reasons as above, a key name should not start with [
        if (substr($parKey, 0, 1) == '[') {
            throw new IniFileException(
                "First character of parameter 2 (key name) cannot be '['",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        // A key name should also not contain =, as this is a control character that separates key from value
        if (strpos($parKey, '=') !== false) {
            throw new IniFileException(
                "Parameter 2 (key name) cannot contain the character '='",
                IniFileException::ERR_INVALID_PARAMETER
            );
        }
        // Section and key should not start with ; or #, as these are used to denote comments. Handling of comments is
        //  outside the scope of this class.
        if ((substr($parSection, 0, 1) == '#') or (substr($parSection, 0, 1) == ';') or (substr($parKey, 0, 1) == '#') or (substr($parKey, 0, 1) == ';')) {
            throw new IniFileException(
                "First character of parameters 1 (section name) and 2 (key name) cannot be '#' or ';'",
                IniFileException::ERR_INVALID_PARAMETER
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
     * @uses IniFile::$iniDataArray
     */
    public function deleteEntry(string $parSection, string $parKey)
    {
        // Trim whitespace
        $parSection = trim($parSection);
        $parKey = trim($parKey);

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
     * @throws IniFileException If the file could not be locked, or if there was some other failure with write operations
     * @uses IniFile::$fileObject
     * @uses IniFile::generateFileContent()
     */
    public function saveDataToFile()
    {
        // Check if file is writable
        if ($this->fileObject->isWritable() === false) {
            throw new IniFileException(
                "File is not writable. Did you set the SplFileObject's open mode?",
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

        // Set pointer to start of file
        $this->fileObject->rewind();

        // Clear current file contents
        if ($this->fileObject->ftruncate(0) === false) {
            $this->fileObject->flock(LOCK_UN);
            throw new IniFileException(
                "Failed to clear current data",
                IniFileException::ERR_FILE_READ_WRITE_FAILED
            );
        }

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
     * Generates a formatted string of INI data, primarily used for writing to INI files
     *
     * @return string The formatted string of INI data
     * @uses    IniFile::$iniDataArray
     * @used-by IniFile::saveDataToFile()
     */
    public function generateFileContent()
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
}
