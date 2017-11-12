<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 16:38
 */

namespace Server;


class MethodData implements \JsonSerializable
{

    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var string
     */
    private $methodRPCName;
    /**
     * @var array
     */
    private $paramsOrderedNames;

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description;

    /**
     * @var Schema
     */
    private $returnSchema;
    /**
     * @var array
     */
    private $paramSchemas;//[ [name] => Schema ]

    public static function FromDefinition($definition, $rpcMethod, $numParams = 0){
        $methodData = explode('@', $definition, 2);
        $new = new self($rpcMethod, $methodData[0], $methodData[1]);
        for ($i=0; $i < $numParams; $i++){
            $new->paramsOrderedNames[] = "param_n$i";
        }
    }


    public function __construct($rpcMethod, $className, $methodName, $title = null, $description = null)
    {
        $this->methodRPCName = $rpcMethod;
        $this->className = $className;
        $this->methodName = $methodName;
        $this->title = $title !== null ? $title : $rpcMethod;
        $this->description = $description;
        $this->paramsOrderedNames = [];
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getMethodRPCName()
    {
        return $this->methodRPCName;
    }

    /**
     * @param string $methodRPCName
     */
    public function setMethodRPCName($methodRPCName)
    {
        $this->methodRPCName = $methodRPCName;
    }

    /**
     * @return array
     */
    public function getParamsOrderedNames()
    {
        return $this->paramsOrderedNames;
    }

    /**
     * @param array $paramsOrderedNames
     */
    public function setParamsOrderedNames($paramsOrderedNames)
    {
        $this->paramsOrderedNames = $paramsOrderedNames;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Schema
     */
    public function getReturnSchema()
    {
        return $this->returnSchema;
    }

    /**
     * @param Schema $returnSchema
     */
    public function setReturnSchema($returnSchema)
    {
        $this->returnSchema = $returnSchema;
    }

    /**
     * @return array
     */
    public function getParamSchemas()
    {
        return $this->paramSchemas;
    }

    /**
     * @param array $paramSchemas
     */
    public function setParamSchemas($paramSchemas)
    {
        $this->paramSchemas = $paramSchemas;
    }

    /**
     * @return int
     */
    public function getNumParams(){
        return count($this->paramsOrderedNames);
    }

    public function jsonSerialize() {
        return array(
            'title' => $this->title,
            'description' => $this->description,
            'method' => $this->methodRPCName,
            'params_num' => $this->getNumParams(),
            'params' => $this->getParamSchemas(),
            'return' => $this->getReturnSchema()
        );
    }

}