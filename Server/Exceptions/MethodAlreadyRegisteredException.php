<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 14/11/17
 * Time: 15:45
 */

namespace Server\Exceptions;


class MethodAlreadyRegisteredException extends RPCServerException
{
    public function __construct($message="Method is already registered", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}