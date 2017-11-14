<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 16:38
 */

namespace Server;


class MethodData
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
    private $methodSchemaName;
    /**
     * @var string
     */
    private $methodRPCName;
    /**
     * @var array
     */
    private $paramsOrderedNames;


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
    public function getMethodSchemaName()
    {
        return $this->methodSchemaName;
    }

    /**
     * @param string $methodSchemaName
     */
    public function setMethodSchemaName($methodSchemaName)
    {
        $this->methodSchemaName = $methodSchemaName;
    }


}