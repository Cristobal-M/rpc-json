<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 12/11/17
 * Time: 14:52
 */

namespace Server\Exceptions;

class WrongInstanceException extends RPCServerException
{

    public function __construct($message = "The object is not instance of the correct class", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}