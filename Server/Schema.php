<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 16:51
 */

namespace Server;


class Schema implements \JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const TYPE_INT = 'int';
    const TYPE_DOUBLE = 'double';
    const TYPES_ALLOWED = [self::TYPE_OBJECT, self::TYPE_ARRAY, self::TYPE_DOUBLE, self::TYPE_INT, self::TYPE_STRING];
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $properties;
    /**
     * @var array
     */
    protected $items;
    /**
     * @var string
     */
    protected $required;
    /**
     * @var string
     */
    protected $type;

    public function __construct($title)
    {
        $this->title = $title;
        $this->items = array();
        $this->properties = array();
        $this->required = array();
        $this->type = self::TYPE_OBJECT;
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
     * @return Schema[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Schema[] $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param array $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if(!in_array($type, self::TYPES_ALLOWED)){
            throw new \Exception("Type $type is not allowed for this schema");
        }
        $this->type = $type;
    }

    /**
     * @param string $property
     */
    public function addRequired($property)
    {
        $this->required[] = $property;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @param Schema $item
     */
    public function addItem(Schema $item){
        $this->items[]=$item;
    }

    /**
     * @param string $name
     * @param Schema $propertySchema
     */
    public function addProperty($name, Schema $propertySchema)
    {
        $this->properties[$name] = $propertySchema;
    }

    public function jsonSerialize() {
        $result =array(
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'required' => $this->required
        );
        if($this->type === self::TYPE_ARRAY){
            $result['items'] = $this->items;
        }
        elseif ($this->type === self::TYPE_OBJECT){
            $result['properties'] = $this->properties;
        }
        return $result;
    }
}