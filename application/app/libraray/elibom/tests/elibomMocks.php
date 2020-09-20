<?php

require('src/elibom_client.php');
use Elibom\APIClient\APIClient as APIClient;
use Elibom\APIClient\ElibomClient as ElibomClient;

class MockApiClient extends APIClient
{
    private $expectedRequest;
    private $response;
    
    //This method is called when the request is going to be executed
    //Avoid to make a real http request
    protected function executeRequest($request, $resource) {
        if (!$this->isValidRequest($request)) {
            throw new Exception("Invalid Request");
        }

        return json_decode($this->response);
    }

    public function stubRequest($expectedRequest, $response) {
        $this->expectedRequest = $expectedRequest;
        $this->response = $response;
    }

    private function isValidRequest($request) {
        return $this->isValidURL($request["url"])
                && $this->isValidMethod($request["method"])
                && $this->areValidHeaders($request["headers"])
                && $this->isValidBody($request["body"]);
    }

    private function isValidURL($URL) {
        $result = $this->expectedRequest["url"] == $URL;
        if (!$result) {
            throw new Exception("\nInvalid URL - Expected : " . $this->expectedRequest["url"] . "  URL : " . $URL);
        }
        return $result;
    }

    private function isValidMethod($method) {
        $result = $this->expectedRequest["method"] == $method;
        if (!$result) {
            throw new Exception("\nInvalid Method - Expected : " . $this->expectedRequest["method"] . "  Method : " . $method);
        }
        return $result;
    }

    private function areValidHeaders($headers) {
        if (array_key_exists("headers", $this->expectedRequest)) {
            foreach ($this->expectedRequest["headers"] as $key => $value) {
                if ($headers[$key] != $value) {
                    throw new Exception("\nInvalid Headers - Expected : " . json_encode($this->expectedRequest["headers"]) . " Headers : " . json_encode($headers));
                    return false;
                }
            }
        }

        return true;
    }

    private function isValidBody($body) {
        $result = $this->expectedRequest["body"] == $body;
        if (!$result) {
            throw new Exception("\nInvalid Body - Expected : " . $this->expectedRequest["body"] . " Body : " . $body);
        }
        return $result;
    }
}


class MockElibomClient extends ElibomClient
{
    public function __construct($u, $t) {
        //Override apiClient by MockApiClient
        $this->apiClient = new MockApiClient($u, $t);
    }

    public function stubRequest($expectedRequest, $expectedResponse) {
        $this->apiClient->stubRequest($expectedRequest, $expectedResponse);
    }
}
?>