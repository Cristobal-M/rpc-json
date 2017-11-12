<?php

class RequestRPC  implements JsonSerializable {
  private $method;
  private $params;
  private $id;
  private $jsonrpc;

  public function __construct($array = null){
    $this->jsonrpc = '2.0';
    if($array !== null){
      $error = false;
      if( empty($array['id']) ) $error = true;
      if( empty($array['method']) ) $error = true;
      if( isset($array['params']) ) $this->params = $array['params'];
      if(!$error){
        $this->id = $array['id'];
        $this->method = $array['method'];

      }
      else{
        throw new Exception("Error, request array not valid: " . print_r($array, true), 1);
      }
    }
  }

  public function isMethod($rpcMethodName, $numParams){
    return $this->method === $rpcMethodName && count($this->params) === $numParams;
  }

  public function getParamCount(){
    return count($this->params);
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

  public function toArray(){
    return array(
      'jsonrpc' => $this->jsonrpc,
      'id' => $this->id,
      'method' => $this->method,
      'params' => $this->params
    );
  }

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

  public function jsonSerialize() {
      return $this->toArray();
  }
}

class MethodData{
  private $className;
  private $methodName;
  private $methodRPCName;
  private $paramsOrderedNames;

  private $title;
  private $description;

  private $returnData;// [ ['name'] => string, ['type'] => string, ['description'] => string]
  private $paramsData;//[ [name] => [ ['type'] => string, ['validator'] => Validator, ['description'] => string] ]


}

class ResponseRPC implements JsonSerializable {
  private $method;
  private $params;
  private $id;
  private $jsonrpc;
  private $result;

  function __construct($param){
    $this->jsonrpc = '2.0';
    if( $param instanceof int ){
      $this->id = $param;
    }
    elseif($param instanceof RequestRPC){
      $this->fillFromRequest($param);
    }
    else{
      throw new Exception("Invalid param for constructor, int or ".RequestRPC::class, 1);
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
    return array(
      'jsonrpc' => $this->jsonrpc,
      'id' => $this->id,
      'method' => $this->method,
      'params' => $this->params,
      'result' => $this->result
    );
  }

  public function jsonSerialize() {
    return $this->toArray();
  }
}

class ReceptorRPC{
    private $endpoint;
    private $requests;
    private $completed;
    private $methodsData;
    private $namespace;
    private $stop;
    private $callFirstCoincidence;

    function __construct($endpoint, $reqData = null){
      $stop = false;
      $this->endpoint = $endpoint;
      if($reqData)$this->setRequest($reqData);

      $this->methodsData = array();
      $this->namespace = '';
    }

    public function setRequest($reqData){
      if( $reqData instanceof RequestRPC ){
        $this->requests = [$reqData];
      }
      elseif ( is_array($reqData) ) {
        foreach ($reqData as $request) {
          if( !$request instanceof RequestRPC){
            throw new Exception("Error processing array of requests, not instance of " . RequestRPC::class, 1);
          }
          $this->requests[]=$request;
        }
      }
      $this->callFirstCoincidence = count($this->requests) === 1;
    }

    public function addMethod($rpcName, $numParams, $method){
      if($this->stop) return;
      $methodData = explode('@', $method, 2);
      $class = $methodData[0];
      $classMethod = $methodData[1];
      $this->methodsData[] = array('class' => $class, 'method' => $classMethod, 'rpcMethod' => $rpcName, 'numParams' => $numParams);

        //throw new Exception(print_r($this->requests[0], true), 1);
      //Si solo tenemos una petidion en cuanto a la primera coincidencia hacemos la llamada
      if($this->callFirstCoincidence && $this->requests[0]->isMethod($rpcName, $numParams)){

        $resp = $this->callMethod($this->requests[0], end($this->methodsData));
        ResponseRPC::Send($resp);
        $this->stop = true;
      }

    }

    private function callMethod(RequestRPC $request, array $data){
        $class = "{$this->namespace}\\" . $data['class'];
        $method = $data['method'];
        $handler = new $class();

        $resp = new ResponseRPC($request);

        //$resp->setResult( call_user_func_array( array($handler, $method), $request->getParams() ) );
        $resp->setResult( $handler->{$method}( ...$request->getParams() ) );
        return $resp;
    }

    public function respond(){
      if($this->stop) return;
      $responses = array();
      foreach ($this->requests as $request) {
        foreach ($this->methodsData as $methodData) {
          if($request->isMethod($methodData['rpcMethod'], $methodData['numParams'])){
            $responses[] = $this->callMethod($request, $methodData);
          }
        }
      }
      ResponseRPC::Send($responses);
    }

    public function generateClientClass($name="ClientRPC"){
      $txtMethods = '';

      foreach ($this->methodsData as $methodData) {
        $paramNames = array();
        for ($i=0; $i < count($methodData['numParams']); $i++) {
          $paramNames[] = "\$paramN$i";
        }
        $paramNames = implode(', ', $paramNames);
        $txtMethods .= "public function {$methodData['rpcMethod']}($paramNames){
          \t\treturn \$this->makeRPC('{$methodData['rpcMethod']}', [$paramNames]);\n
        \t}\n";
      }

      return "
        <?php
            class $name
            {
                private static \$reqCounter = 0;
                private \$endpoint='{$this->endpoint}';

                public function getEndpoint(){
                  return \$this->endpoint;
                }

                private function makeRPC(\$method, \$params){
                  \$payload = array('jsonrpc' => '2.0',
                    'id' => ++self::\$reqCounter,
                    'method' => \$method,
                    'params' => \$params
                  );
                  \$payloadJSON = json_encode(\$payload);
                  // Create Http context details
                  \$context = stream_context_create(array (
                    'http'=> array(
                              'method' => 'POST',
                              'header' => \"Content-Type: application/json\\r\\n\".
                                          \"Accept: application/json\\r\\n\",
                              'content'=> \$payloadJSON
                            )
                    )
                  );

                  // Read page rendered as result of your POST request
                  \$result =  file_get_contents (
                                \$this->endpoint,  // page url
                                false,
                                \$context);

                  // Server response is now stored in \$result variable so you can process it
                  return \$result;
                }
                $txtMethods
            }
        ?>
      ";
    }
}

class Hello{

  public function helloWorld($hello){

    return "Hello world!!, $hello";
  }
}

$receptor = new ReceptorRPC('http://localhost:8000/rpc.php');
$receptor->addMethod('hello_world', 1, 'Hello@helloWorld');

echo $receptor->generateClientClass();

$receptor->setRequest(RequestRPC::Receive());
$receptor->respond();
//echo $receptor->generateClientClass();
