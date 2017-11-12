<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 18:53
 */

namespace Server;

//use Controller;

class ServerRPC{
    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var RequestRPC[]
     */
    private $requests;
    /**
     * @var array
     */
    private $methodsData;
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var boolean
     */
    private $stop;
    /**
     * @var boolean
     */
    private $callFirstCoincidence;

    function __construct($endpoint, $namespace = ''){
        $this->stop = false;
        $this->endpoint = $endpoint;

        $this->methodsData = array();
        $this->namespace = $namespace;
    }

    public function receive(){
        $this->setRequest(RequestRPC::Receive());
    }

    public function setRequest($reqData){
        $error = false;
        if( $reqData instanceof RequestRPC ){
            $this->requests = [$reqData];
        }
        elseif ( is_array($reqData) ) {
            foreach ($reqData as $request) {
                if( !$request instanceof RequestRPC) $error = true;
                $this->requests[]=$request;
            }
        }
        if($error){
            throw new \Exception("Error processing request or array of requests, not instance of " . RequestRPC::class, 1);
        }
        $this->callFirstCoincidence = count($this->requests) === 1;
    }

    public function addMethod($rpcName, $numParams, $classAndMethod){
        if($this->stop) return;
        echo "\n\n$classAndMethod\n\n";

        $methodData = explode('@', $classAndMethod, 2);
        $class = $methodData[0];
        $classMethod = $methodData[1];
        $this->methodsData[$rpcName . '__' . $numParams] = array(
            'class' => $class,
            'method' => $classMethod,
            'rpcMethod' => $rpcName,
            'numParams' => $numParams
        );

        //throw new Exception(print_r($this->requests[0], true), 1);
        //Si solo tenemos una petidion en cuanto a la primera coincidencia hacemos la llamada
        if($this->callFirstCoincidence && $this->requests[0]->match($rpcName, $numParams)){
            $resp = $this->callMethod($this->requests[0], $class, $classMethod);
            ResponseRPC::Send($resp);
            $this->stop = true;
        }

    }

    private function callMethod(RequestRPC $request, $class, $classMethod, $suffix = 'RPC'){
        $class = "{$this->namespace}\\" . "{$class}";
        echo print_r($this, true);
        $handler = new $class();

        $resp = new ResponseRPC($request);

        //$resp->setResult( call_user_func_array( array($handler, $method), $request->getParams() ) );
        $resp->setResult( $handler->{$classMethod . $suffix}( ...$request->getParams() ) );
        return $resp;
    }

    public function finish(){
        if($this->stop) return;
        $responses = array();
        foreach ($this->requests as $request) {
            $methodData = $this->methodsData[$request->getMethod() . '__' . $request->getParamCount()];
            $responses[] = $this->callMethod($request, $methodData['class'], $methodData['method']);
        }
        ResponseRPC::Send($responses);
    }

    public function includeMethodsFrom($regex){
        includeByRegex($this, $regex);
    }

    public function sendClientCode(){

    }
}
function includeByRegex($receiver, $regex){
    foreach (glob($regex) as $filename){
        include $filename;
    }
}