<?php
/**
 * INI file parsing and manipulation library.
 *
 * @author SierraKomodo
 * @license GPL3
 */

namespace SierraKomodo\INIController;

/**
 * Class INILibException
 * @package SierraKomodo\INIController
 */
class INILibException extends \Exception
{
    // Error code constants
    const NO_ERR                     = 0;
    const ERR_FILE_LOCK_FAILED       = 1;
    const ERR_FILE_READ_WRITE_FAILED = 2;
    const ERR_INI_PARSE_FAILED       = 3;
    const ERR_INVALID_PARAMETER      = 4;
    const ERR_UNDEFINED              = -1;
    
    
    /**
     * INILibException constructor. The only difference from Exception::__construct() is the default for parameter $code
     *   being set to self::ERR_UNDEFINED
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = self::ERR_UNDEFINED, \Throwable $previous = null)
    {
        parent::__construct();
    }
}