<?php

//;pad the soap extension if not loaded
if(!extension_loaded("soap")){
  dl("php_soap.dll");
}

//disable cache
ini_set("soap.wsdl_cache_enabled","0");

//get the raw HTTP post data
$post = file_get_contents('php://input');

?>