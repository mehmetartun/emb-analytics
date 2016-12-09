<?php
include("embx_dbconn.php");
include("embx_functions.php");
//
//		FIle for processing the single page application
//
$pagefunction = $_GET["pf"];
switch ($pagefunction) {
	case "reset":
	$table = $_GET["table"];
	$tradingday = $_GET["tradingday"];

		switch ($table){
			case "resetendofdaytable":
				embx_sql("delete from endofday where tradingday = '".$tradingday."'");
				echo "Reset End of Day Table";
			break;
			case "resetorderstable":
				embx_sql("delete from orders where date(ordertime) = '".$tradingday."'");
				echo "Reset Orders Table";
			break;
			case "resetrfqstable":
				embx_sql("delete from rfq where date(actiontime) = '".$tradingday."'");
				echo "Reset RFQ Table";
			break;
			case "resettradestable":
				embx_sql("delete from trades where date(tradetime) = '".$tradingday."'");
				echo "Reset Trades Table";
			break;
			case "resetlogfilestable":
				embx_sql("update processed set processed=0 
						where date(datetime_from) = '".$tradingday."' and 
						date(datetime_to) = '".$tradingday."' ");
				echo 	"update processed set processed=1 
						where date(datetime_from) = '".$tradingday." 00:00:00' and 
						date(datetime_to) = '".$tradingday." 23:30:00' ";	
				//echo "Reset Log Files Table";
			break;
		}

	//echo $table . " " . $tradingday;	
	
	break; 
} 



?>

