<?php echo "<?php\n"; ?>
class <?= isset($className) ? $className : 'ClientRPC' ?>
{
    private static $reqCounter = 0;
    const DEFAULT_ENDPOINT='<?= $endpoint ?>';
    private $endpoint;

    function __construct($endpoint = self::DEFAULT_ENDPOINT){
        $this->endpoint = $endpoint;
    }
    public function getEndpoint(){
        return $this->endpoint;
    }

    public function setEndpoint($endpoint){
        return $this->endpoint = $endpoint;
    }

    private function makeRPC($method, $params){
        $payload = array('jsonrpc' => '2.0',
            'id' => ++self::$reqCounter,
            'method' => $method,
            'params' => $params
        );
        $payloadJSON = json_encode($payload);
        // Create Http context details
        $context = stream_context_create(array (
                'http'=> array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n".
                "Accept: application/json\r\n",
                'content'=> $payloadJSON)
            )
        );

        // Read page rendered as result of your POST request
        $result =  file_get_contents (
            $this->endpoint,  // page url
            false,
            $context);

        // Server response is now stored in $result variable so you can process it
        return $result;
    }

<?php foreach ($methodsData as $method): ?>
<?php $paramNames = '$' . implode(', $', $method->getParamsOrderedNames()); ?>
    public function <?= $method->getMethodRPCName() ?>(<?= $paramNames ?>){
        return $this->makeRPC('<?= $method->getMethodRPCName() ?>', [<?= $paramNames ?>]);
    }
<?php endforeach; ?>
}

