<!DOCTYPE html>
<?php
    function printProperty($schema){
        echo "<div class=\"property\">";
        echo "<h4> {$schema->getTitle()} &lt;{$schema->getType()}&gt;</h4>\n";
        echo "<p> {$schema->getDescription()} </p>\n";
        $required = implode(', ', $schema->getRequired());
        echo "<p>Required properties: {$required} </p>\n";
        echo "<p>Properties: {$required} </p>\n";
        foreach ($schema->getProperties() as $propSchema){
            printProperty($propSchema);
        }
        echo "</div>";
    }
?>
<html>
<head>
    <meta name="viewport" content="initial-scale=1">

    <style>
        div.property{
            margin-left: 25px;
            padding:10px;
            border: 1px solid black;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="left-menu">
        <?php foreach ($methodSchemas as $rpc => $methodSchema): ?>
        <a href="#<?=$rpc?>"><?= $rpc ?> </a>
        <?php endforeach; ?>
    </div>
    <pre class="right-menu">
        <?php foreach ($methodSchemas as $rpc => $methodSchema): ?>
            <?php if($methodSchema !== null): ?>
                <div id="<?=$rpc?>">
                    <h3><?=$methodSchema->getTitle() ?> [<?=$methodSchema->getMethod() ?>]</h3>
                    <p><?=$methodSchema->getDescription() ?></p>
                    <?php $indentP = 20; ?>
                    <?php foreach ($methodSchema->getParamSchemas() as $paramSchema){
                        printProperty($paramSchema);
                    }
                    ?>
                </div>
            <?php else: ?>
                <div id="<?=$rpc?>">
                    <h3>[<?=$rpc ?>] No hay disponible informacion para este metodo</h3>
                    ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </pre>
</div>
</body>
</html>