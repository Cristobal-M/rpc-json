<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 13/11/17
 * Time: 16:37
 */

namespace Server;


class MethodSchema implements \JsonSerializable
{

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

    /**
     * @var string
     */
    private $method;//[ [name] => Schema ]


    public function __construct($arg1, $arg2 = null)
    {
        if(is_array($arg1)){
            $this->title = isset($arg1['title']) ? $arg1['title'] : '';
            $this->description = isset($arg1['description']) ? $arg1['description'] : '';
            $this->method = isset($arg1['method']) ? $arg1['method'] : '';
            $this->paramSchemas = array();
            if(isset($arg1['params'])){
                foreach ($arg1['params'] as $param){
                    if($param instanceof Schema) $this->paramSchemas[] = $param;
                    else $this->paramSchemas[] = new Schema($param);
                }
            }
            if(isset($arg1['return'])){
                $this->returnSchema = $arg1['return'] instanceof Schema ? $arg1['return'] : new Schema($arg1['return']);
            }
        } else{
            $this->__construct2($arg1, $arg2);
        }
    }

    private function __construct2($title, $description){
        $this->title = $title;
        $this->description = $description;
        $this->paramSchemas = [];
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
        return count($this->paramSchemas);
    }

    public function addParam(Schema $schema){
        $this->paramSchemas[] = $schema;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }



    public function jsonSerialize() {
        return array(
            'title' => $this->title,
            'description' => $this->description,
            'method' => $this->method,
            'num_params' => $this->getNumParams(),
            'params' => $this->getParamSchemas(),
            'return' => $this->getReturnSchema()
        );
    }

}