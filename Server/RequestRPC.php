<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 18:37
 */

namespace Server;


class RequestRPC  implements \JsonSerializable
{
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $params;
    /**
     * @var mixed
     */
    private $id;
    /**
     * @var string
     */
    private $jsonrpc;
    /**
     * @var string
     */
    private $notification;
    /**
     * @var ErrorRPC
     */
    private $error;

    /**
     * RequestRPC constructor.
     * @param null $array
     */
    public function __construct($array = null){
        if($array !== null){
            $error = false;
            if( !isset($array['id']) ) $this->notification = true;
            if( empty($array['method']) ) $error = true;
            if( empty($array['jsonrpc']) || $array['jsonrpc'] !== '2.0' ) $error = true;
            if( isset($array['params']) ) $this->params = $array['params'];
            if(!$error){
                $this->id = $array['id'];
                $this->method = $array['method'];
                $this->jsonrpc = $array['jsonrpc'];
            }
            else{
                throw new \Exception("Error, request not valid: " . print_r($array, true), 1);
            }
        }
    }

    /**
     * @param $rpcMethodName
     * @param $numParams
     * @return bool
     */
    public function match($rpcMethodName, $numParams){
        return $this->method === $rpcMethodName && count($this->params) === $numParams;
    }

    /**
     * @return int
     */
    public function getParamCount(){
        return count($this->params);
    }

    /**
     * @return array
     */
    public function getParams(){
        return $this->params;
    }

    /**
     * @return array
     */
    public function getParamsForCall($orderedByName = null){
        $isAssociative = array_keys($this->params) !== range(0, count($this->params) - 1);

        if($orderedByName !== null && $isAssociative){
            $result = array();
            foreach ($orderedByName as $name){
                $result[] = $this->params[$name];
            }
            return $result;
        }
        if($isAssociative){
            return array_values($this->params);
        }
        return $this->params;
    }

    /**
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(){
        return $this->method;
    }

    /**
     * @return array
     */
    public function toArray(){
        return array(
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params
        );
    }

    /**
     * @return RequestRPC|RequestRPC[]
     */
    public static function Receive(){
        $data = json_decode(file_get_contents('php://input'), true);
        if( count( array_filter(array_keys($data), 'is_numeric') ) > 0 ){
            $result = array();
            foreach ($data as $dat) {
                $result[] = new self($dat);
            }
            return $result;
        }
        else{
            return new self($data);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }
}