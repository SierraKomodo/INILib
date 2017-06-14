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
 * @package SierraKomodo\INILib
 * @version 0.1.0-review.3 Peer review version 3. Currently in development; Not fully tested yet.
 */
class IniFileException extends \Exception
{
    // Error code constants
    const ERR_FILE_LOCK_FAILED       = 1;
    const ERR_FILE_READ_WRITE_FAILED = 2;
    const ERR_INI_PARSE_FAILED       = 3;
    const ERR_INVALID_PARAMETER      = 4;
    const ERR_FILE_NOT_WRITABLE      = 5;
    const ERR_FILE_NOT_READABLE      = 6;
    const ERR_UNDEFINED              = -1;
    
    
    /**
     * INILibException constructor.
     *
     * The only difference from Exception::__construct() is the default for parameter $code being set to
     *   self::ERR_UNDEFINED
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = self::ERR_UNDEFINED, \Throwable $previous = null)
    {
        parent::__construct();
    }
}
