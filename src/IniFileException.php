<?php
/**
 * INI file parsing and manipulation library.
 *
 * @author SierraKomodo
 * @license GPL3
 */

namespace SierraKomodo\INILib;

use SierraKomodo\PhpCommonLibrary\PclExceptionWithCode;

/**
 * Extension of \Exception to provide predefined 'exception codes' for debugging/error handling purposes
 *
 * Exceptions from `IniFile` SHOULD use this class and provide one of the predefined error codes, and a
 *   programmer-friendly error message. Error codes are meant to allow a try/catch block to handle errors, while
 *   messages can provide specific information in logs for debugging and bug fixing.
 *
 * @package SierraKomodo\INILib
 * @version 1.0.0 First full release
 */
class IniFileException extends PclExceptionWithCode
{
    // Error code constants
    /**
     * @var Integer Exception code; Failed to lock the file for reading or writing
     */
    const ERR_FILE_LOCK_FAILED = 1;
    /**
     * @var Integer Exception code; Failed to read from or write to the file for a reason not defined in other codes
     */
    const ERR_FILE_READ_WRITE_FAILED = 2;
    /**
     * @var Integer Exception code; Failed to parse the raw INI data
     */
    const ERR_INI_PARSE_FAILED = 3;
    /**
     * @var Integer Exception code; A provided method parameter failed validation checks
     */
    const ERR_INVALID_PARAMETER = 4;
    /**
     * @var Integer Exception code; The INI file is not writable by the web server
     */
    const ERR_FILE_NOT_WRITABLE = 5;
    /**
     * @var Integer Exception code; The INI file is not readable by the web server
     */
    const ERR_FILE_NOT_READABLE = 6;
    /**
     * @var Integer Exception code; The INI file does not exist
     */
    const ERR_FILE_NOT_EXIST = 7;
    /**
     * @var Integer Exception code; The object is in read only mode and a write operation was attempted
     */
    const ERR_READ_ONLY_MODE = 8;
}
