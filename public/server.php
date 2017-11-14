<?php
/**
 * Created by PhpStorm.
 * User: cristobal
 * Date: 11/11/17
 * Time: 19:09
 */
require_once __DIR__ . '/../vendor/autoload.php';

$receiver = new \Server\ServerRPC('Server.php', 'Services');


switch ($_SERVER['REQUEST_METHOD']){
    case 'POST':
        $receiver->receive();
        $receiver->includeMethodsFrom("../methods/*.php");


        break;
    case 'GET':
        $receiver->includeMethodsFrom("../methods/*.php");
        $receiver->sendClientCode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
}


$receiver->finish();