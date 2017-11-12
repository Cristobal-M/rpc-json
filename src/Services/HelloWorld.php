<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 19:42
 */

namespace Services;

class HelloWorld{

    public function helloWorldRPC($hello){

        return "Hello world!!, $hello";
    }
}