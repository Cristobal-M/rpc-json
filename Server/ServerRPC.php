<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 18:53
 */

namespace Server;

//use Controller;

use Server\Exceptions\MethodAlreadyRegisteredException;
use Server\Exceptions\WrongMethodNameException;

class ServerRPC{
    const METHOD_RPC_SUFFIX = 'RPC';
    const METHOD_DEF_SUFFIX = 'DEF';
    const CLASS_METHOD_SEP = '__';
    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var RequestRPC[]
     */
    private $requests;
    /**
     * @var MethodData[]
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

    /**
     * @var Service[]
     */
    private $instances;

    function __construct($endpoint, $namespace = ''){
        $this->stop = false;
        $this->endpoint = $endpoint;

        $this->methodsData = array();
        $this->namespace = $namespace;
        $this->instances = array();
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

    public function registerMethod($rpcMethod, $class, $classMethod){
        if($this->stop) return;
        if( isset($this->methodsData[$rpcMethod]) ){
            return new Exceptions\MethodAlreadyRegisteredException("The rpc method $rpcMethod is already registered");
        }
        $classInstance = $this->getServiceInstance($class);
        $classMethodSchema = $classMethod . self::METHOD_DEF_SUFFIX;
        $classMethod .= self::METHOD_RPC_SUFFIX;

        if( !method_exists($classInstance, $classMethod) ){
            throw new WrongMethodNameException("There is no method $classMethod in $class");
        }

        $this->methodsData[$rpcMethod] = new MethodData($rpcMethod, $class, $classMethod);
        $methodData = &$this->methodsData[$rpcMethod];
        if( method_exists($classInstance, $classMethodSchema) ){
            $methodData->setMethodSchemaName($classMethodSchema);
        }

        $reflectionMethod = new \ReflectionMethod($classInstance, $classMethod);
        $params = array_map(
            function(\ReflectionParameter $re_p){
                return $re_p->getName();
            },
            $reflectionMethod->getParameters()
        );
        $methodData->setParamsOrderedNames($params);

        //throw new Exception(print_r($this->requests[0], true), 1);
        //Si solo tenemos una petidion en cuanto a la primera coincidencia hacemos la llamada
        if($this->callFirstCoincidence && $this->requests[0]->getMethod() === $rpcMethod){
            $resp = $this->callMethod($this->requests[0], $methodData);
            ResponseRPC::Send($resp);
            $this->stop = true;
        }
        unset($methodData);
    }

    public function registerClass($class){
        if($this->stop) return false;
        $service = $this->getServiceInstance($class);
        $reflecClass = new \ReflectionClass($service);
        $shortClassName = $reflecClass->getShortName();
        $methods = $reflecClass->getMethods();
        foreach ($methods as $method){
            $shortMethodName = $method->getShortName();
            if(substr($shortMethodName, -strlen(self::METHOD_RPC_SUFFIX)) === self::METHOD_RPC_SUFFIX)
            {
                $rpcMethodName = $shortClassName . self::CLASS_METHOD_SEP . substr($shortMethodName, 0, -strlen(self::METHOD_RPC_SUFFIX));
                $methodData = $this->methodsData[$rpcMethodName] =
                    new MethodData($rpcMethodName, $class, $shortMethodName);

                if($this->callFirstCoincidence && $this->requests[0]->getMethod() === $rpcMethodName){

                    $resp = $this->callMethod($this->requests[0], $methodData);
                    ResponseRPC::Send($resp);
                    $this->stop = true;
                    return;
                }
            }
        }
    }

    private function callMethod(RequestRPC $request, MethodData $methodData){
        $handler = $this->getServiceInstance($methodData->getClassName());

        $resp = new ResponseRPC($request);

        //$resp->setResult( call_user_func_array( array($handler, $method), $request->getParams() ) );
        $resp->setResult( $handler->{$methodData->getMethodName()}( ...$request->getParamsForCall($methodData->getParamsOrderedNames()) ) );
        return $resp;
    }

    public function finish(){
        if($this->stop) return;
        $responses = array();
        foreach ($this->requests as $request) {
            if( !isset($this->methodsData[$request->getMethod()]) ){
                $this->sendErrorNotFound($request);
                continue;
            }
            $methodData = $this->methodsData[$request->getMethod()];
            $responses[] = $this->callMethod($request, $methodData);
        }
        if(count($responses) !== 0){
            ResponseRPC::Send($responses);
        }

    }

    public function includeMethodsFrom($regex){
        includeByRegex($this, $regex);
    }

    public function sendClientCode($endpoint){
        includeClientGenerator($this->methodsData, $endpoint);
    }

    public function sendErrorNotFound(RequestRPC $request, $data = null, $msg = null){
        $error = new ErrorRPC(ErrorRPC::E_METHOD_NOT_FOUND, $data, $msg);

        ResponseRPC::Send( ResponseRPC::ErrorResponse($request, $error) );
    }

    public function sendDocumentation(){
        $methodSchemas = [];
        foreach ($this->methodsData as $rpc => $methodData){
            if($methodData->getMethodSchemaName() === null){
                $methodSchemas[$rpc] = null;
                continue;
            }
            $inst = $this->getServiceInstance($methodData->getClassName());
            $auxSchema = $inst->{$methodData->getMethodSchemaName()}();
            $auxSchema->setMethod($rpc);
            $methodSchemas[$rpc] = $auxSchema;

        }

        includeDocGenerator($methodSchemas);
        $this->stop = true;
    }


    /**
     * @param $class
     * @return Service
     * @throws Exceptions\WrongInstanceException
     */
    public function getServiceInstance($class){
        if( !isset($this->instances[$class]) ){
            $this->instances[$class] = new $class;
        }
        return $this->instances[$class];
    }
}
function includeByRegex($receiver, $regex){
    foreach (glob($regex) as $filename){
        include $filename;
    }
}

function includeDocGenerator($methodSchemas){
    include __DIR__ . '/generator_files/doc_generator.php';
}

function includeClientGenerator($methodsData, $endpoint){
    include __DIR__ . '/generator_files/client_class_generator.php';
}