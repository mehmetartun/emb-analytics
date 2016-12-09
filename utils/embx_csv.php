<?php
include("embx_dbconn.php");
include("embx_functions.php");
//
//		FIle for processing the single page application
//
$pagefunction = $_GET["pf"];
switch ($pagefunction) {
	case "csv_periodnumisins":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select 
					t1.orderdate as tradingday, count(t1.isin) as isincount 
				from 
			    	(select distinct date(ordertime) as orderdate, isin from orders where  
					date(ordertime) >=  '".$fromdate."' and date(ordertime) <= '".$todate."'
					) as t1 
				group by 
					tradingday order by tradingday asc ";
				
				
		$res = embx_sql($sql);
		$filename = "NumISINs_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('tradingday','numisins');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($res as $item){
			fputcsv($fp,$item);
		}
	exit;
	
	case "csv_periodnumrfqs":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select 
					t1.rfqdate as tradingday, count(t1.rfqid) as rfqcount 
				from 
			    	(select distinct date(actiontime) as rfqdate, rfqid from rfq where  
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action='rfq/initial') as t1 
				group by 
					tradingday order by tradingday asc ";
				
				
		$res = embx_sql($sql);
		$filename = "NumRFQs_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('tradingday','numrfqs');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($res as $item){
			fputcsv($fp,$item);
		}
	break;

	case "csv_periodnumtradesmonthly":
	case "csv_periodnumtradesmonthly_line":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
	
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
	
		//$td = embx_add_date($td,0,1,0);
		//$td = embx_add_date($td,-1,0,0);
	
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
	
		$todate = date("Y-m-d",strtotime($todate));

		$sql = "select 
					(year(t1.tradedate)*12+month(t1.tradedate)) as trademonth, count(t1.tradeid) as tradecount 
				from 
			    	(select distinct date(tradetime) as tradedate, tradeid from trades where  
					date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'
					) as t1 
				group by 
					trademonth order by trademonth asc ";
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("Y-m-d",$month);
					$data[$i]["tradecount"] = $item["tradecount"];
					$i = $i+1;
				}
			}
		$filename = "NumTrades_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('month','numtrades');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($data as $item){
			fputcsv($fp,$item);
		}
	break;

	case "csv_periodvolrfqsmonthly":
	case "csv_periodvolrfqsmonthly_line":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
	
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
	
		//$td = embx_add_date($td,0,1,0);
		//$td = embx_add_date($td,-1,0,0);
	
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
	
		$todate = date("Y-m-d",strtotime($todate));

		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, sum(floor(rfq.size/currencies.rate/10000)/100) as rfqvolume 
				from 
			    	rfq, currencies, bonds where bonds.isin = rfq.isin and currencies.currency = bonds.currency and  
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial' 
				group by 
					rfqmonth order by rfqmonth asc ";
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("Y-m-d",$month);
					$data[$i]["rfqcount"] = $item["rfqcount"];
					$i = $i+1;
				}
			}
		$filename = "VolRFQsMonthly_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('month','numrfqs');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($data as $item){
			fputcsv($fp,$item);
		}
	break;
	
	case "csv_periodnumrfqsmonthly":
	case "csv_periodnumrfqsmonthly_line":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
	
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
	
		//$td = embx_add_date($td,0,1,0);
		//$td = embx_add_date($td,-1,0,0);
	
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
	
		$todate = date("Y-m-d",strtotime($todate));

		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, count(rfqid) as rfqcount 
				from 
			    	rfq where 
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial' 
				group by 
					rfqmonth order by rfqmonth asc ";
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("Y-m-d",$month);
					$data[$i]["rfqcount"] = $item["rfqcount"];
					$i = $i+1;
				}
			}
		$filename = "NumRFQsMonthly_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('month','numrfqs');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($data as $item){
			fputcsv($fp,$item);
		}
	break;
	
	case "csv_periodvoltradesmonthly":
	case "csv_periodvoltradesmonthly_line":
	date_default_timezone_set("UTC");
	$fromdate = $_GET["fromdate"];
	$todate = $_GET["todate"];
	
	$fdt = strtotime(substr($fromdate,0,8)."01");
	$tdt = strtotime(substr($todate,0,8)."01");
	
	//$td = embx_add_date($td,0,1,0);
	//$td = embx_add_date($td,-1,0,0);
	
	$fromdate = date("Y-m-d",$fdt);
	$todate = date("Y-m-d",$tdt);
	$todate = embx_add_date($todate,0,1,0);
	$todate = embx_add_date($todate,-1,0,0);
	
	$todate = date("Y-m-d",strtotime($todate));
	
	$data = [];
	$sql = "	select (year(tradetime)*12+month(tradetime)) as trademonth, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
			 from trades,  currencies where currencies.currency = trades.currency and
			 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."' group by trademonth asc
				";
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("Y-m-d",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$i = $i+1;
				}
			}
		$filename = "VolTradesMonthly_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('month','voltrades');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($data as $item){
			fputcsv($fp,$item);
		}
	break;
	
	case "csv_periodvolnumtradesmonthly":
	case "csv_periodvolnumtradesmonthly_doubleline":
	date_default_timezone_set("UTC");
	$fromdate = $_GET["fromdate"];
	$todate = $_GET["todate"];
	
	$fdt = strtotime(substr($fromdate,0,8)."01");
	$tdt = strtotime(substr($todate,0,8)."01");
	
	//$td = embx_add_date($td,0,1,0);
	//$td = embx_add_date($td,-1,0,0);
	
	$fromdate = date("Y-m-d",$fdt);
	$todate = date("Y-m-d",$tdt);
	$todate = embx_add_date($todate,0,1,0);
	$todate = embx_add_date($todate,-1,0,0);
	
	$todate = date("Y-m-d",strtotime($todate));
	
	$data = [];
	$sql = "	select (year(tradetime)*12+month(tradetime)) as trademonth, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
			, count(trades.id) as tradecount
			 from trades,  currencies where currencies.currency = trades.currency and
			 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."' group by trademonth asc
				";
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("Y-m-d",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$data[$i]["tradecount"] = $item["tradecount"];
 					$i = $i+1;
				}
			}
		$filename = "VolNumTradesMonthly_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('month','voltrades','numtrades');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($data as $item){
			fputcsv($fp,$item);
		}
	break;
	
	
	 // settings
	
	case "csv_periodnumtrades":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select 
					t1.tradedate as tradedate, count(t1.tradeid) as tradecount 
				from 
			    	(select distinct date(tradetime) as tradedate, tradeid from trades where  
					date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'
					) as t1 
				group by 
					tradedate order by tradedate asc ";
				
				
		$res = embx_sql($sql);
		$filename = "NumTrades_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('tradingday','numtrades');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($res as $item){
			fputcsv($fp,$item);
		}
	break; // settings	
	
	
	case "csv_periodvoltrades":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "
			select date(tradetime) as tradedate, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
		 from trades,  currencies where currencies.currency = trades.currency and
		 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."' group by tradedate asc";
				
				
		$res = embx_sql($sql);
		$filename = "VolTrades_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('tradingday','voltrades');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($res as $item){
			fputcsv($fp,$item);
		}
	break; // settings
	
	case "csv_periodvolrfqs":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select date(rfq.actiontime) as rfqdate, sum(floor(rfq.size/currencies.rate/10000)/100) as rfqvolume 
			from rfq, currencies, bonds 
			where bonds.isin = rfq.isin and currencies.currency = bonds.currency and
			date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."' and action='rfq/initial'
			group by rfqdate";
				
				
		$res = embx_sql($sql);
		$filename = "VolRFQs_".$fromdate."_".$todate.".csv";
		$fp = fopen('php://output', 'w');

		$header = array('tradingday','volrfqs');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		fputcsv($fp, $header);

		foreach($res as $item){
			fputcsv($fp,$item);
		}
	break;
	
	
	case "csv_periodnumtradingpartiesmonthly":
	
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
		
		//$td = embx_add_date($td,0,1,0);
		//$td = embx_add_date($td,-1,0,0);
		
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
		
		$todate = date("Y-m-d",strtotime($todate));
		
		$data = [];
		/*$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, count(rfqid) as rfqcount 
				from 
			    	rfq where 
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial'  and isin !='".TESTISIN."' 
				group by 
					rfqmonth order by rfqmonth asc ";
		*/
			//echo $sql;
			//$res = embx_sql($sql);
			$fdtt = date("Y",strtotime($fromdate))*12+date("m",strtotime($fromdate));
			$tdtt = date("Y",strtotime($todate))*12+date("m",strtotime($todate));
			$res = embx_monthlytradingparties(
						$fdtt, $tdtt);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["month"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["cptycount"] = $item["cptycount"];
					$i = $i+1;
				}
				//var_dump($data);
				//echo "<script>".
				//		embx_columngraph(	"pagecontent", $data, "month", "cptycount", "Month", "No of Trading Parties",
				//	 						"No of Trading Parties by Month", $fromdate." to " . $todate,
				//"	", "<b>{point.y:,.0f} Cptys</b><br>" ).
				//	"</script>";
			}
			
			$filename = "NumTradingParties_".$fromdate."_".$todate.".csv";
			$fp = fopen('php://output', 'w');

			$header = array('month','numtradingparties');
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename='.$filename);
			fputcsv($fp, $header);

			foreach($data as $item){
				fputcsv($fp,$item);
			}
			

	
	break;	
	
	case "csv_periodnumrfqpartiesmonthly":
	
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
		
		//$td = embx_add_date($td,0,1,0);
		//$td = embx_add_date($td,-1,0,0);
		
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
		
		$todate = date("Y-m-d",strtotime($todate));
		
		$data = [];
		/*$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, count(rfqid) as rfqcount 
				from 
			    	rfq where 
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial'  and isin !='".TESTISIN."' 
				group by 
					rfqmonth order by rfqmonth asc ";
		*/
			//echo $sql;
			//$res = embx_sql($sql);
			$fdtt = date("Y",strtotime($fromdate))*12+date("m",strtotime($fromdate));
			$tdtt = date("Y",strtotime($todate))*12+date("m",strtotime($todate));
			$res = embx_monthlyrfqparties(
						$fdtt, $tdtt);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["month"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["cptycount"] = $item["cptycount"];
					$i = $i+1;
				}
				//var_dump($data);
				//echo "<script>".
				//		embx_columngraph(	"pagecontent", $data, "month", "cptycount", "Month", "No of Trading Parties",
				//	 						"No of Trading Parties by Month", $fromdate." to " . $todate,
				//"	", "<b>{point.y:,.0f} Cptys</b><br>" ).
				//	"</script>";
			}
			
			$filename = "NumRFQParties_".$fromdate."_".$todate.".csv";
			$fp = fopen('php://output', 'w');

			$header = array('month','numrfqparties');
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename='.$filename);
			fputcsv($fp, $header);

			foreach($data as $item){
				fputcsv($fp,$item);
			}
			

	
	break;	

} 




?>

