<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 12/11/17
 * Time: 12:32
 */

namespace Services;


abstract class Service
{
    const DEF_SUFFIX = 'DEF';

    protected static $methods;

    public static function GetMethods(){
        return !isset(static::$methods) ? array() : static::$methods;
    }

    public function getMethodDefinition($name){
        if( !method_exists($this, $name . self::DEF_SUFFIX) ) return null;

        return $this->{$name . self::DEF_SUFFIX}();
    }

}