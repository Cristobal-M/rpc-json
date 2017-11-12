<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 17:44
 */

namespace Server;


class Validator
{
    const SPACES = array("\r", "\n", " ", "\t");

    /**
     * @var Schema
     */
    protected $schema;
    protected $errors;

    protected function isEmpty($value){
        if( empty($value) ) return true;
        $value = str_replace("\xEF\xBB\xBF", "", $value);
        return str_replace(self::SPACES, '', $value) === '';
    }

    protected function addError($message){
        $this->errors[] = $message;
    }

    public function __construct(Schema $schema)
    {
        $this->errors = array();
        $this->schema = $schema;
    }

    public function clear(){
        $this->errors = array();
    }

    public function lastOk(){
        return count($this->errors) === 0;
    }
    /**
     * @return boolean
     */
    public function validate($data){
        $this->clear();
        $type = $this->schema->getType();

        switch ($type){
            case Schema::TYPE_OBJECT:
                if(!is_object($data)){
                    $this->addError("{$this->schema->getTitle()} with value $data is not an object");
                }
                break;
            case Schema::TYPE_STRING:
                if(!is_string($data)){
                    $this->addError("{$this->schema->getTitle()} with value $data is not an string");
                }
                break;
            case Schema::TYPE_ARRAY:
                if(!is_array($data)){
                    $this->addError("{$this->schema->getTitle()} with value $data is not an array");
                }
                break;
            case Schema::TYPE_INT:
                if(!is_numeric($data) || !is_int($data + 0)){
                    $this->addError("{$this->schema->getTitle()} with value $data is not int");
                }
                break;
            case Schema::TYPE_DOUBLE:
                if(!is_numeric($data) || !is_double($data + 0)){
                    $this->addError("{$this->schema->getTitle()} with value $data is not double");
                }
        }

        foreach ($this->schema->getRequired() as $reqProp){
            if( !empty($data[$reqProp]) || $this->isEmpty($data[$reqProp]) ){
                $this->addError("{$this->schema->getTitle()} property $reqProp is required");
            }
        }

        $origSchema = $this->schema;
        $propertiesSchemas = $this->schema->getProperties();
        foreach ($propertiesSchemas as $propSchema){
            $prop = $propSchema->getTitle();
            if( empty($data[$prop]) ) continue;
            $this->schema = $propSchema;
            $this->validate($data[$prop]);
        }
        $this->schema = $origSchema;


        $itemSchemas = $this->schema->getItems();
        $itemNum = count($itemSchemas);
        if( $itemNum === 1){
            $origSchema = $this->schema;
            $this->schema = $itemSchemas[0];
            foreach ($data as $item){
                $this->validate($item);
            }
            $this->schema = $origSchema;
        }
        elseif ($itemNum > 1){
            $origSchema = $this->schema;
            foreach ($itemSchemas as $index => $itemSchema){
                if( !isset($data[$index]) ) continue;
                $this->schema = $itemSchema;
                $this->validate($data[$index]);
            }
            $this->schema = $origSchema;
        }

        return $this->lastOk();
    }
}