<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 19:42
 */

namespace Services;
use Server\MethodSchema;
use Server\Schema;

class HelloWorld{

    public function hello_worldRPC($hello, $goodbye){

        return "Hello world!!, $hello, $goodbye";
    }

    public function hello_worldDEF(){
        $result = new MethodSchema(array(
            'title' => 'Un hello world',
            'description' => 'una simple funcion que obtiene un numero de '.
                'parametro y lo devuelve precedido por un texto de hello world',
            'params' => array(
                ['title'=> 'hello', 'type' => 'string'],
                ['title'=> 'goodbye', 'type' => 'string']
            )
        ));
        return $result;
    }


    public function sumaRPC($num1, $num2){

        return $num1 + $num2;
    }

    public function sumaDEF(){
        $result = new MethodSchema(array(
            'title' => 'Un metodo de suma',
            'description' => 'una simple funcion para sumar dos numeros',
            'params' => array(
                ['title'=> 'num1', 'type' => 'int'],
                ['title'=> 'num2', 'type' => 'object', 'properties'=>[
                    ['title'=>'cosa', 'type' => 'int']
                ], 'required' => 'cosa']
            )
        ));
        echo json_encode($result);
        return $result;
    }
}