<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 18:37
 */

namespace Server;


class ErrorRPC  implements \JsonSerializable
{
/*
-32700	Parse error	Invalid JSON was received by the Server. An error occurred on the Server while parsing the JSON text.
-32600	Invalid Request	The JSON sent is not a valid Request object.
-32601	Method not found	The method does not exist / is not available.
-32602	Invalid params	Invalid method parameter(s).
-32603	Internal error	Internal JSON-RPC error.
-32000 to -32099	Server error	Reserved for implementation-defined Server-errors.
 */
    const E_PARSE = -32700;
    const E_INVALID_REQUEST = -32600;
    const E_METHOD_NOT_FOUND = -32601;
    const E_INVALID_PARAMS = -32602;
    const E_INTERNAL = -32603;

    const MESSAGES = array(
        self::E_PARSE => 'Parse error',
        self::E_INVALID_REQUEST => 'Invalid Request',
        self::E_METHOD_NOT_FOUND => 'Method not found',
        self::E_INVALID_PARAMS => 'Invalid params',
        self::E_INTERNAL => 'Internal error'
    );
    /**
     * @var int
     */
    private $code;
    /**
     * @var string
     */
    private $message;
    /**
     * @var mixed
     */
    private $data;


    /**
     * ErrorRPC constructor.
     * @param $code
     * @param null $data
     * @param null $msg
     * @throws \Exception
     */
    public function __construct($code, $data = null, $msg = null){
        $this->code = $code;
        if($msg === null){
            $this->message = self::MESSAGES[$code];
        }
        else{
            $this->message = $msg;
        }
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @throws \Exception
     */
    public function setCode($code)
    {
        switch ($code){
            case self::E_METHOD_NOT_FOUND: break;
            case self::E_INVALID_REQUEST: break;
            case self::E_INVALID_PARAMS: break;
            case self::E_INTERNAL: break;
            case self::E_PARSE: break;
            default:
                if($code > 32000 || $code < -32099)
                    throw new \Exception('Error code not valid');
        }
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }



    /**
     * @return array
     */
    public function jsonSerialize() {
        $result = array(
            'code' => $this->code,
            'message' => $this->message
        );
        if(!empty($this->data)) $result['data'] = $this->data;
        return $result;
    }
}