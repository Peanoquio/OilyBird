<?php

/**
 * This is the page that will be requested through the Ajax call made in TestClientPage.php
 * @author oliver.chong
 *
 */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//this will be invoked through Ajax

//get the request
$testA = $_REQUEST["testA"];
$testB = $_REQUEST["testB"];
$testC = $_REQUEST["testC"];

//return the response
echo json_encode( array( "testA" => "OK: $testA", "testB" => "OK: $testB", "testC" => "OK: $testC" ) );

?>