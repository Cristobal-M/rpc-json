<?php
namespace Server;

class ResponseRPC implements \JsonSerializable {
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $params;
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $jsonrpc;
    /**
     * @var array
     */
    private $result;

    /**
     * @var ErrorRPC
     */
    private $error;

    function __construct($param){
        $this->jsonrpc = '2.0';
        if( is_int($param) ){
            $this->id = $param;
        }
        elseif($param instanceof RequestRPC){
            $this->fillFromRequest($param);
        }
        else{
            throw new \Exception("Invalid param for constructor, int or ".RequestRPC::class, 1);
        }
    }

    public static function Send($resp){
        $fp = fopen('php://output', 'w');
        //throw new Exception(print_r($resp, true), 1);

        fwrite($fp, json_encode($resp));
        fclose($fp);
    }

    public function fillFromRequest(RequestRPC $request){
        $this->method = $request->getMethod();
        $this->params = $request->getParams();
        $this->id = $request->getId();

    }
    public function getParams(){
        return $this->params;
    }

    public function getId(){
        return $this->id;
    }

    public function getMethod(){
        return $this->method;
    }

    public function setResult($result){
        return $this->result = $result;
    }

    public function getResult(){
        return $this->result;
    }

    public function toArray(){
        $result = $this->result;
        if( $result !== null && (!is_string($result) && !is_numeric($result)) ){
            $result = json_encode($result);
        }

        return array_filter(array(
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params,
            'result' => $result,
            'error' => $this->error
        ), function($var){return !is_null($var);});
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    public function error($code, $data){
        $this->error = new ErrorRPC($code,$data);
    }

    public function getError(){
        return $this->error;
    }

    public function setError($error){
        $this->error = $error;
    }

    public static function ErrorResponse(RequestRPC $request, ErrorRPC $error){
        $new = new self($request->getId());
        $new->jsonrpc = '2.0';
        $new->method = $request->getMethod();
        $new->error = $error;
        return $new;
    }
}