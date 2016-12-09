<?php
include("embx_dbconn.php");
include("embx_functions.php");
//
//		FIle for processing the single page application
//


$graphcontent = $_GET["gc"];
switch ($graphcontent) {
	case "graphmarket":
	$isin = $_GET["isin"];
	$tradingday = $_GET["tradingday"];
	$data = embx_markethistory($isin, $tradingday);
    //print_r( $data);
	//echo "<br>";
	$graphtitle = "Market for ".$isin." on ".$tradingday;
	$subtitle = "Live: " . 	number_format($data["max_sz_livebid"]/1000000,2) . "/" . 
							number_format($data["max_sz_liveask"]/1000000,2) . " MM | " . 
								"Indicative: " . number_format($data["max_sz_indicativebid"]/1000000,2) . "/" . 
							number_format($data["max_sz_indicativeask"]/1000000,2) . " MM " ;
	
	$ret = embx_markethistorygraph("pagecontent", $data,  "Hour", "Price", $graphtitle , $subtitle);
	//echo "Ret is <br>";
	//print_r($ret);
	echo  "<script>" . $ret . "</script>";
	//echo $ret;
	break;
	
	case "graph_periodnumisins":
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
			//var_dump($sql);
			//var_dump($res);
			echo "<script>".
				embx_datescattergraph("pagecontent", $res, "tradingday","isincount", "Date", "No of ISINs", "No of ISINs", "","pointclick"," ISINs").
					"</script>";
	break;
	
	case "graph_periodnumtrades":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "
			
			
			select 
					t1.tradedate as tradedate, count(t1.tradeid) as tradecount 
				from 
			    	(select distinct date(tradetime) as tradedate, tradeid from trades where  
					date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'
					and isin !='".TESTISIN."') as t1 
				group by 
					tradedate order by tradedate asc ";
					
					
			$res = embx_sql($sql);
			//var_dump($sql);
			//var_dump($res);
			echo "<script>".
				embx_datescattergraph("pagecontent", $res, "tradedate","tradecount", "Date", "No of Trades", "No of Trades", "").
					"</script>";
	break;

	case "graph_periodvolrfqsmonthly":
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
		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, sum(floor(rfq.size/currencies.rate/10000)/100) as rfqvolume 
				from 
			    	rfq, currencies, bonds where bonds.isin = rfq.isin and currencies.currency = bonds.currency and  
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial' and rfq.isin !='".TESTISIN."' 
				group by 
					rfqmonth order by rfqmonth asc ";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["rfqvolume"] = $item["rfqvolume"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_columngraph("pagecontent", $data, "month", "rfqvolume", "Month", "Vol of RFQs", "Vol of RFQs Monthly", $fromdate." to " . $todate,
				"	", "<b>{point.y:,.0f} m USD</b><br>" ,"\${point.y:,.0f} m ").
					"</script>";
			}
	break;	
	
	case "graph_periodvolrfqsmonthly_line":
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
		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, sum(floor(rfq.size/currencies.rate/10000)/100) as rfqvolume 
				from 
			    	rfq, currencies, bonds where bonds.isin = rfq.isin and currencies.currency = bonds.currency and  
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial'  and rfq.isin !='".TESTISIN."'
				group by 
					rfqmonth order by rfqmonth asc ";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["rfqvolume"] = $item["rfqvolume"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_linegraph("pagecontent", $data, "month", "rfqvolume", "Month", "Vol of RFQs", "Vol of RFQs Monthly", $fromdate." to " . $todate,
				"	", "<b>{point.y:,.0f} m USD</b><br>" ).
					"</script>";
			}
	break;
	
	
	
	

	case "graph_periodnumtradingpartiesmonthly":
	
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
				echo "<script>".
						embx_columngraph(	"pagecontent", $data, "month", "cptycount", "Month", "No of Trading Parties",
					 						"No of Trading Parties by Month", $fromdate." to " . $todate,
											"
											$.get('utils/embx_ajax.php?pf=tradingpartiessummaryfortradingmonth&tradingmonth=' + this.category,function(data){
													$('#subdetail').html(data);
													});
											
											
											$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
													$('#subdetaillower').html(data);
													});
											", "<b>{point.y:,.0f} Cptys</b><br>" ).
					"</script>";
			}

	
	break;	
	
	


	case "graph_periodnumrfqpartiesmonthly":
	
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
				echo "<script>".
						embx_columngraph(	"pagecontent", $data, "month", "cptycount", "Month", "No of RFQ Parties",
					 						"No of RFQ Parties by Month", $fromdate." to " . $todate,
				"	", "<b>{point.y:,.0f} Cptys</b><br>" ).
					"</script>";
			}

	
	break;	
	
	case "graph_periodnumrfqtradingpartiesmonthly":
	
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
					$data1[$i]["month"] = date("M y",$month);
					$data1[$i]["cptycount"] = $item["cptycount"];
					$i = $i+1;
				}
				//var_dump($data);
			}
			$res = embx_monthlyrfqparties(
						$fdtt, $tdtt);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["month"]);
					$data2[$i]["month"] = date("M y",$month);
					$data2[$i]["cptycount"] = $item["cptycount"];
					$i = $i+1;
				}
				//var_dump($data);
			}
			
			echo "<script>".
					embx_doublecolumngraph(	"pagecontent", $data1, $data2 , 
											"month", "cptycount", "cptycount", 
											"Month", "No of Parties",
				 							"No of Trading and RFQ Parties by Month", $fromdate." to " . $todate,
											"	", "<b>{point.y:,.0f} Cptys</b><br>" , "{point.y:.0f}", "Traded", "RFQ").
				"</script>";
			

	
	break;	



	case "graph_periodnumrfqsmonthly":

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
		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, count(rfqid) as rfqcount 
				from 
			    	rfq where 
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial'  and isin !='".TESTISIN."' 
				group by 
					rfqmonth order by rfqmonth asc ";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["rfqcount"] = $item["rfqcount"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_columngraph("pagecontent", $data, "month", "rfqcount", "Month", "No of RFQs", "No of RFQs Monthly", $fromdate." to " . $todate,
				"	", "<b>{point.y:,.0f} RFQs</b><br>" ).
					"</script>";
			}

	
	break;	
	case "graph_periodnumrfqsmonthly_line":

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
		$sql = "select 
					(year(actiontime)*12+month(actiontime)) as rfqmonth, count(rfqid) as rfqcount 
				from 
			    	rfq where 
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action = 'rfq/initial'  and isin   !='".TESTISIN."'
				group by 
					rfqmonth order by rfqmonth asc ";
			echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["rfqmonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["rfqcount"] = $item["rfqcount"];
					$i = $i+1;
				}
				echo "<script>".
						embx_linegraph("pagecontent", $data, "month", "rfqcount", "Month", "No of RFQs", "No of RFQs Monthly", $fromdate." to " . $todate,
				"	", "<b>{point.y:,.0f} RFQs</b><br>" ).
					"</script>";
			}
	break;	

	case "graph_periodnumtradesmonthly":

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
		$sql = "select 
					(year(t1.tradedate)*12+month(t1.tradedate)) as trademonth, count(t1.tradeid) as tradecount 
				from 
			    	(select distinct date(tradetime) as tradedate, tradeid from trades where  
					date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'
					  and isin !='".TESTISIN."'  ) as t1 
				group by 
					trademonth order by trademonth asc ";
			echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradecount"] = $item["tradecount"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_columngraph("pagecontent", $data, "month", "tradecount", "Month", "No of Trades", "No of Trades Monthly", $fromdate." to " . $todate,
				"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});
				", "<b>{point.y:,.0f} Trades</b><br>" ).
					"</script>";
			}

	
	break;
	
	
	
	case "graph_periodnumtradesmonthly_line":

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
		$sql = "select 
					(year(t1.tradedate)*12+month(t1.tradedate)) as trademonth, count(t1.tradeid) as tradecount 
				from 
			    	(select distinct date(tradetime) as tradedate, tradeid from trades where  
					date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'
					 and isin !='".TESTISIN."'  ) as t1 
				group by 
					trademonth order by trademonth asc ";
			echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradecount"] = $item["tradecount"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_linegraph("pagecontent", $data, "month", "tradecount", "Month", "No of Trades", "No of Trades Monthly", $fromdate." to " . $todate,
				"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});
				", "<b>{point.y:,.0f} Trades</b><br>" ).
					"</script>";
			}

	
	break;
	
	case "graph_periodvoltradesmonthly":

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
				 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'  and trades.isin !='".TESTISIN."' group by trademonth asc
					";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_columngraph("pagecontent", $data, "month", "tradevolume", "Month", "Trade Volume in USD m", "Volume of Trades Monthly", $fromdate." to " . $todate,
				"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});
				", "<b>{point.y:,.2f} m USD</b><br>" ,"\${point.y:,.0f} m ").
					"</script>";
			}

	
	break;	
	
	case "graph_periodvoltradesmonthly_line":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
		
		$todate = date("Y-m-d",strtotime($todate));
		
		$data = [];
		$sql = "	select (year(tradetime)*12+month(tradetime)) as trademonth, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
				 from trades,  currencies where currencies.currency = trades.currency and
				 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'  and trades.isin !='".TESTISIN."' group by trademonth asc
					";
			echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_linegraph("pagecontent", $data, "month", "tradevolume", "Month", "Trade Volume in USD m", "Trade Volume by Month (USD m)", $fromdate." to " . $todate,
				"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});
				", "<b>{point.y:,.2f} m USD</b><br>" ,"\${point.y:.0f} m").
					"</script>";
			}

	
	break;	
	
	case "graph_periodvolnumtradesmonthly_doubleline":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
		
		$todate = date("Y-m-d",strtotime($todate));
		
		$data = [];
		$sql = "	select (year(tradetime)*12+month(tradetime)) as trademonth, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume,
					count(trades.id) as tradecount
				 from trades,  currencies where currencies.currency = trades.currency and
				 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'  and trades.isin !='".TESTISIN."' group by trademonth asc
					";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$data[$i]["tradecount"] = $item["tradecount"];
					
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_doublelinegraph("pagecontent", $data, "month", "tradevolume", "tradecount","Month", "Trade Volume in USD m",
					 	"No of Trades", 
						"Volume and No of Trades Monthly", 
						$fromdate." to " . $todate,
						"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});", 
						"<b>{point.y:,.2f} m USD</b><br>","<b>{point.y:,.0f} trades</b><br>" ,
						"\${point.y:.0f} m","" ).
					"</script>";
			}

	
	break;	
	
	case "graph_periodvolnumtradesmonthly_linecolumn":
		date_default_timezone_set("UTC");
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		
		$fdt = strtotime(substr($fromdate,0,8)."01");
		$tdt = strtotime(substr($todate,0,8)."01");
		$fromdate = date("Y-m-d",$fdt);
		$todate = date("Y-m-d",$tdt);
		$todate = embx_add_date($todate,0,1,0);
		$todate = embx_add_date($todate,-1,0,0);
		
		$todate = date("Y-m-d",strtotime($todate));
		
		$data = [];
		$sql = "	select (year(tradetime)*12+month(tradetime)) as trademonth, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume,
					count(trades.id) as tradecount
				 from trades,  currencies where currencies.currency = trades.currency and
				 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'  and trades.isin !='".TESTISIN."' group by trademonth asc
					";
			//echo $sql;
			$res = embx_sql($sql);
			if($res){
				$i=0;
				foreach($res as $item){
					$month = monthtodate($item["trademonth"]);
					$data[$i]["month"] = date("M y",$month);
					$data[$i]["tradevolume"] = $item["tradevolume"];
					$data[$i]["tradecount"] = $item["tradecount"];
					
					$i = $i+1;
				}
				//var_dump($data);
				echo "<script>".
						embx_linecolumngraph("pagecontent", $data, "month", "tradevolume", "tradecount","Month", "Trade Volume in USD m",
					 	"No of Trades", 
						"Volume and No of Trades Monthly", 
						$fromdate." to " . $todate,
						"$.get('utils/embx_ajax.php?pf=tradesummaryfortradingmonth&tradingmonth=' + this.category,function(data){
						$('#subdetail').html(data);
						});", 
						"<b>{point.y:,.2f} m USD</b><br>","<b>{point.y:,.0f} trades</b><br>" ,
						"\${point.y:.0f} m","" ).
					"</script>";
			}

	
	break;	
	
	case "graph_periodvoltrades":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "
			select date(tradetime) as tradedate, sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
		 from trades,  currencies where currencies.currency = trades.currency and
		 date(tradetime) >=  '".$fromdate."' and date(tradetime) <= '".$todate."'  and trades.isin !='".TESTISIN."' group by tradedate asc";
			$res = embx_sql($sql);
			//var_dump($sql);
			//var_dump($res);
			echo "<script>".
				embx_datescattergraph("pagecontent", $res, "tradedate","tradevolume", "Date", "Trade Volume in USD m", "Daily Trade Volume in USD m", "",'pointclick',' m USD').
					"</script>";
	break;
	
	
	case "graph_periodnumrfqs":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select 
					t1.rfqdate as tradingday, count(t1.rfqid) as rfqcount 
				from 
			    	(select distinct date(actiontime) as rfqdate, rfqid from rfq where  
					date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."'
					and action='rfq/initial'  and isin !='".TESTISIN."') as t1 
				group by 
					tradingday order by tradingday asc ";
			$res = embx_sql($sql);
			//var_dump($sql);
			//var_dump($res);
			echo "<script>".embx_datescattergraph("pagecontent", $res, "tradingday","rfqcount", "Date", "No of RFQs", "No of RFQs", "",'pointclick',"RFQs")."</script>";
			
	
	break;
	
	case "graph_periodvolrfqs":
		$fromdate = $_GET["fromdate"];
		$todate = $_GET["todate"];
		$sql = "select date(rfq.actiontime) as rfqdate, sum(floor(rfq.size/currencies.rate/10000)/100) as rfqvolume 
			from rfq, currencies, bonds 
			where bonds.isin = rfq.isin and currencies.currency = bonds.currency and
			date(actiontime) >=  '".$fromdate."' and date(actiontime) <= '".$todate."' and action='rfq/initial'
			 and rfq.isin !='".TESTISIN."' group by rfqdate";
					
			$res = embx_sql($sql);
			//var_dump($sql);
			//var_dump($res);
			echo "<script>".embx_datescattergraph("pagecontent", $res, "rfqdate","rfqvolume", "Date",  "RFQ Volume in USD m", "Daily RFQ Volume in USD m", "",'pointclick',"m USD")."</script>";
	break;
	

	case "graph_isincount":
		$sql = "select 
					t1.orderdate as tradingday, count(t1.isin) as isincount 
				from 
			    	(select distinct date(ordertime) as orderdate, isin from orders where   isin !='".TESTISIN."') as t1 
				group by 
					tradingday order by tradingday DESC limit 20";
		$data = embx_sql($sql);

		?>
		<script>
		$(function () {
		    $('#pagecontent').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: 'Number of ISIN\'s quoted'
		        },
		        subtitle: {
		            text: 'Includes live and indicative orders'
		        },
		        xAxis: {
		            type: 'category',
					categories: [ <?php
					$dum = 0;
					foreach ($data as $item){
						if ($dum > 0){ echo ",";}
						echo "'" . $item["tradingday"] . "'";
						$dum = $dum +1;
					}	
						
					?> ],

		            labels: {
		                rotation: -45,
		                style: {
		                    fontSize: '12px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: 'Number of ISIN\'s'
		            }
		        },
		        plotOptions: {
		            series: {
		                cursor: 'pointer',
		                point: {
		                    events: {
		                        click: function () {
									$.get("utils/embx_ajax.php?pf=bondlistforday&tradingday=" + this.category,function(data){
										$('#detailcontent').html(data);
									});
									$('#detailheader').html("ISIN's quoted on " + this.category);
		                        }
		                    }
		                }
		            }
		        },
		        legend: {
		            enabled: false
		        },
		        tooltip: {
		            pointFormat: '<b>{point.y:,.0f} ISIN\'s</b>'
		        },
		        series: [{
		            name: 'TradeData',
		            data: [
						<?php
						echo embx_columnchartformat($data,"tradingday","isincount");
						?>
		            ],
		            dataLabels: {
		                enabled: true,
		                rotation: -90,
		                color: '#FFFFFF',
		                align: 'right',
		                format: '{point.y:,.0f}', // one decimal
		                y: 10, // 10 pixels down from the top
		                style: {
		                    fontSize: '13px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        }]
		    });
		});	
		</script>
		<?php
		
	break;
	case "graph_isincount_live":
		$sql = "select 
					t1.orderdate as tradingday, count(t1.isin) as isincount 
				from 
			    	(select distinct date(ordertime) as orderdate, isin from orders where ordertype='Live'  and isin !='".TESTISIN."') as t1 
				group by 
					tradingday order by tradingday DESC limit 20";
		$data = embx_sql($sql);

		?>
		<script>
		$(function () {
		    $('#pagecontent').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: 'Number of Live ISIN\'s quoted'
		        },
		        subtitle: {
		            text: 'Includes live  orders'
		        },
		        xAxis: {
		            //type: 'category',
					categories: [ <?php
					$dum = 0;
					foreach ($data as $item){
						if ($dum > 0){ echo ",";}
						echo "'" . $item["tradingday"] . "'";
						$dum = $dum +1;
					}	
						
					?> ],
		            labels: {
		                rotation: -45,
		                style: {
		                    fontSize: '13px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: 'Number of Live ISIN\'s'
		            }
		        },
		        legend: {
		            enabled: false
		        },
		        tooltip: {
		            pointFormat: '<b>{point.y:.1f} ISIN\'s</b>'
		        },
		        plotOptions: {
		            series: {
		                cursor: 'pointer',
		                point: {
		                    events: {
		                        click: function () {
									$.get("utils/embx_ajax.php?pf=bondlistforday&tradingday=" + this.category,function(data){
										$('#detailcontent').html(data);
									});
									$('#detailheader').html("ISIN's quoted on " + this.category);
		                        }
		                    }
		                }
		            }
		        },
		        series: [{
		            name: 'TradeData',
		            data: [
		/*                ['Shanghai', 23.7],
		                ['Lagos', 16.1],
		                ['Instanbul', 14.2],
		                ['Karachi', 14.0],
		                ['Mumbai', 12.5],
		                ['Moscow', 12.1],
		                ['São Paulo', 11.8],
		                ['Beijing', 11.7],
		                ['Guangzhou', 11.1],
		                ['Delhi', 11.1],
		                ['Shenzhen', 10.5],
		                ['Seoul', 10.4],
		                ['Jakarta', 10.0],
		                ['Kinshasa', 9.3],
		                ['Tianjin', 9.3],
		                ['Tokyo', 9.0],
		                ['Cairo', 8.9],
		                ['Dhaka', 8.9],
		                ['Mexico City', 8.9],
		                ['Lima', 8.9]
		*/
						<?php
						echo embx_columnchartformat($data,"tradingday","isincount");
						?>
		            ],
		            dataLabels: {
		                enabled: true,
		                rotation: -90,
		                color: '#FFFFFF',
		                align: 'right',
		                format: '{point.y:,.0f}', // one decimal
		                y: 10, // 10 pixels down from the top
		                style: {
		                    fontSize: '13px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        }]
		    });
		});	
		</script>
		<?php
		
	break;
	case "graph_usercount":
		$sql = "select 
					t1.orderdate as tradingday, count(t1.username) as usercount 
				from 
			    	(select distinct date(ordertime) as orderdate, username from orders where   isin !='".TESTISIN."') as t1 
				group by 
					tradingday order by tradingday desc limit 20";
					
		$data = embx_sql($sql);

		$ret = embx_columngraph("pagecontent", 
								$data, 
								"tradingday", 
								"usercount", 
								"Date", 
								"No of Users", 
								"Number of Active Users", 
								"Includes only those entering orders to the platform", 
								"$.get('utils/embx_ajax.php?pf=bondlistforday&tradingday=' + this.category,function(data){
										$('#detailcontent').html(data);
										});
								$('#detailheader').html('ISIN\'s quoted on ' + this.category);", 
								"<b>{point.y:,.0f} Users</b>" );
		echo  "<script>" . $ret . "</script>";
	break;
	
	



} 




?>

