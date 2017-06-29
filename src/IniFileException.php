<?php
/**
 * INI file parsing and manipulation library.
 *
 * @author SierraKomodo
 * @license GPL3
 */

namespace SierraKomodo\INILib;

/**
 * Extension of \Exception to provide predefined 'exception codes' for debugging/error handling purposes
 *
 * Exceptions from `IniFile` SHOULD use this class and provide one of the predefined error codes, and a
 *   programmer-friendly error message. Error codes are meant to allow a try/catch block to handle errors, while
 *   messages can provide specific information in logs for debugging and bug fixing.
 *
 * @package SierraKomodo\INILib
 * @version 0.1.0-review.3 Peer review version 3. Currently in development; Not fully tested yet.
 */
class IniFileException extends \Exception
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
     * @var Integer Exception code; No exception code was provided in the throw statement, or no pre-defined codes match
     *   the scenario
     */
    const ERR_UNDEFINED = -1;
    
    
    /**
     * INILibException constructor.
     *
     * The only difference from Exception::__construct() is the default for parameter $code being set to
     *   self::ERR_UNDEFINED
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "", $code = self::ERR_UNDEFINED, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
