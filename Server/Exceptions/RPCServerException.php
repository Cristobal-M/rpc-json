<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 14/11/17
 * Time: 15:46
 */

namespace Server\Exceptions;


abstract class RPCServerException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}