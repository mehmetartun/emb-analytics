<?php
include("embx_dbconn.php");
include("embx_functions.php");

$tradingday = $_GET["tradingday"];
$minutes = $_GET["minutes"];
$isin = $_GET["isin"];


$ret = embx_marketsnapshot($isin,$tradingday, $minutes);


var_dump($ret);

echo "<br/>";

$ret = embx_markethistory($isin, $tradingday);
var_dump($ret["minute"]);
echo "<br/>";
var_dump($ret["px_indicativebid"]);
echo "<br/>";


	
	
?>