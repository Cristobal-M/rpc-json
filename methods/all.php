<?php
$receiver->registerMethod('hello',Services\HelloWorld::class, 'hello_world');
$receiver->registerMethod('suma2',Services\HelloWorld::class, 'suma');