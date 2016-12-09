<?php
include("embx_dbconn.php");
include("embx_functions.php");
//
//		FIle for processing the single page application
//
$pagefunction = $_GET["pf"];
switch ($pagefunction) {
	case "settings":
	
	

	
	break; // settings
	
	
	case "cleantables":
		embx_cleantables();
		break;
	
	case "processlogfile":
		$filename = $_GET["filename"];
		//echo 'processing '.$filename."<br/>";
		
		$exists = embx_lookup("processed","filename","'". $filename . "'","processed");
		
		if ($exists) {
			echo "The file <strong>" . $filename . "</strong> appears to be already processed";
			//mysql_query("update processed set processed = 1 where filename='" . $filename . "'");
		} else {
			//$rs = EMBXDB::get()->query("insert into processed (filename, processed) values ('" . $filename . "',1) ");
			$rs = EMBXDB::get()->query("update processed set processed = 1 where filename='" . $filename . "' ");
			//echo "I'm here trying to process...<br>";
			$result = embx_processfile($filename);
			echo $result;
		}
		break; // processlogfile

/*
	case "showrejects":
		$filename = $_GET["filename"];
		$exists = embx_lookup("processed","filename","'". $filename . "'","id");
		echo "Exists: " . $exists . "<br />";
		if ($exists){
			$rejnoid = embx_sql("select * from rejects where fileid = " . $exists, " and isnull(logid)");
			$rejid = embx_sql("select * from rejects where fileid = " . $exists, " and not isnull(logid)");
			echo "Rejects without ID <br />";
			foreach($rejnoid as $rej){
				echo $rej["content"] . " " . "<br/>";
			}
			echo "Rejects with ID <br />";
			foreach($rejid as $rej){
				echo $rej["content"] . " " . "<br/>";
			}
		}
	break;
*/
	
	case "fileupload":
		$data = array();
		if(isset($_GET['files']))
		{  
		    $error = false;
		    $files = array();
		    $uploaddir = './uploads/';
		    foreach($_FILES as $file)
		    {
		        if(move_uploaded_file($file['tmp_name'], $uploaddir .basename($file['name'])))
		        {
		            $files[] = $uploaddir .$file['name'];
		        }
		        else
		        {
		            $error = true;
		        }
		    }
		    $data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);
		}
		else
		{
		    $data = array('success' => 'Form was submitted', 'formData' => $_POST);
		}
		echo json_encode($data);
	break;

	case "logfilelist":
		//$filelist =  scandir("../source_files");
		//natcasesort($filelist);
		//krsort($filelist);
		
		$filelist = embx_sql("select * from processed order by processed asc, filename desc");
		
		echo "<ol>";
		foreach ($filelist as $fileitem) {
			if (strpos($fileitem["filename"], ".csv")) {
				//$proc = embx_lookup("processed","filename","'" . $fileitem["filename"] . "'","processed");
				if ($fileitem["processed"] == 1) {
					$uniq = uniqid('');
					echo "<li><strong>"  . $fileitem["datetime_from"]." &nbsp;&nbsp;&nbsp;&nbsp;".$fileitem["datetime_to"] . "</strong><br> " . $fileitem["filename"] . " <span class='label success'>DONE</span>  </li>";
				} else {
					$uniq = uniqid('');
					echo "<li><strong>" . $fileitem["datetime_from"]." &nbsp;&nbsp;&nbsp;&nbsp;".$fileitem["datetime_to"] . "</strong><br> " . $fileitem["filename"] . " <a class='label' id='" . $uniq . "'>PROC</a></li>";
					?>
					<script>
					$("#<? echo $uniq; ?>").click(function(){
						$("#<? echo $uniq; ?>").addClass("alert");
						embx_js_processfile("<? echo $fileitem["filename"]; ?>");
					});
					</script>
					<?
				}
			} else {
				//echo "<li><pre>" . $filename . "</pre></li>";
			}
		}
		echo "</ol>";
	
		break;
	case "bondlist":
		$bonds = embx_sql("select distinct isin from orders order by isin asc");
		$bondnames = embx_sql("select * from bonds");
		
		if (count($bonds)>0){
			echo "<ul>";
			foreach ($bonds as $bond){
				if ($bond["isin"]){
					$bondname = embx_getbondname_fromisin($bond["isin"],$bondnames);	
					echo "<li><a href='javascript:embx_getbonddetail(\"" . $bond["isin"] . "\")'>" . $bond["isin"] . "</a> ".$bondname." </li>";
				}
			}
			echo "</ul>";
		}	
	break;
	case "bondlistforday":
		$bondnames = embx_sql("select * from bonds");
		$bonds = embx_sql("select distinct isin from orders where date(ordertime) = '" . $_GET["tradingday"] . "' order by isin asc");
		if (count($bonds)>0){
			echo "<ul>";
			foreach ($bonds as $bond){
				if ($bond["isin"]){
					$bondname = embx_getbondname_fromisin($bond["isin"],$bondnames);
					echo "<li><a href='javascript:embx_getbonddetailforday(\"" . $bond["isin"] . "\",\"" . $_GET["tradingday"]  . "\")'>" . $bondname . "</a>
						&nbsp;&nbsp; <a href='javascript:embx_graphmarket(\"" . $bond["isin"] . "\",\"" . $_GET["tradingday"]  . "\")'><i class='fi-graph-trend'></i></a></li>";
				}
			}
			echo "</ul>";
		}	
		break;
	case "bondlistfordayforuser":
		$user = $_GET["user"];
		$bonds = embx_sql("select distinct isin from orders where date(ordertime) = '" . $_GET["tradingday"] . "' 
					and username = '" .  $user . "'  order by isin asc");
		if (count($bonds)>0){
			echo "<ul>";
			foreach ($bonds as $bond){
				if ($bond["isin"]){
					echo "<li><a href='javascript:embx_getbonddetailforday(\"" . $bond["isin"] . "\",\"" . $_GET["tradingday"]  . "\")'>" . $bond["isin"] . "</a>
						</li>";
				}
			}
			echo "</ul>";
		}	
		break;
		case "bondlistfordayforcpty":
			$cpty = $_GET["cpty"];
			$bonds = embx_sql("select distinct isin from orders where date(ordertime) = '" . $_GET["tradingday"] . "' 
						and left(username,4) = '" .  $cpty . "'  order by isin asc");
			if (count($bonds)>0){
				echo "<ul>";
				foreach ($bonds as $bond){
					if ($bond["isin"]){
						echo "<li><a href='javascript:embx_getbonddetailforday(\"" . $bond["isin"] . "\",\"" . $_GET["tradingday"]  . "\")'>" . $bond["isin"] . "</a></li>";
					}
				}
				echo "</ul>";
			}	
			break; // bondlistfordayforuser
			
	case "bonddetail":
		date_default_timezone_set("UTC");
		$isin = $_GET["isin"];
		//embx_bondupdate($isin);
		$orders = embx_sql("select * from orders where isin ='" . $isin . "' order by ordertime");
		echo "<h6>Orders for ISIN: " . $isin . "</h6>";
		echo "<table>";
		if (count($orders)>0){
			$prevdate = date_format(date_create($orders[0]["ordertime"]),"j F Y" );
			//echo "<div class='alert-box'>" . $prevdate . "</div>";
			//echo "<ul>";
			echo "<tr><th colspan='9'>" . $prevdate . "</th></tr>";
			foreach ($orders as $order) {
				if ($order["ordertype"] == "Live") { 
					$price = "<span class='label'>" . number_format($order["price"],4) . "</span>";
				}	else {
					$price = "<span class='label secondary'>" . number_format($order["price"],4) . "</span>";
				}
				if ($order["side"] == "BUY") { 
					$side = "<span class='label success'>B</span>";
				}	else {
					$side = "<span class='label alert'>S</span>";
				}
				$thedate =  date_format(date_create($order["ordertime"]),"j F Y" );
				if ($thedate != $prevdate){
					//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
					echo "<tr><th colspan='9'>" . $thedate . "</th></tr>";
				}
				echo "	<tr>
							<td><strong>" . $order["orderid"] .  "</strong></td>
							<td>"  . $order["username"] . "</td>
							<td>" . substr($order["ordertime"],11,8) . "</td>
							<td>" . $order["action"]  . "</td>
							<td>" .  $side . "</td>
							<td>" . $price . "</td>
							<td style='text-align: right;'>" . number_format($order["size"],0) . "</td>
							<td>" . substr($order["endtime"],11,8) . "</td><td>" . $order["reason"] . "</td>
						</tr>";
						$prevdate = $thedate;
			}
			echo "</table>";
		}
	break;
	
	case "isincountperday":
	
	$isincountperday = embx_sql("select tradingday, count(isin) as isincount from (select date(ordertime) as tradingday, 
							isin from orders group by tradingday, isin) as temptable group by tradingday");
	if (count($isincountperday) > 0){
		echo "<table class='embx-table'><thead><tr><th>Trading Day</th><th>ISINs</th></tr></thead><tbody>";
		foreach ($isincountperday as $isincount){
			echo "<tr><td>" . $isincount["tradingday"] . "</td><td>" . $isincount["isincount"]. "</td></tr>";
		}
		echo "</tbody></table>";
	}
	
	break;	
	case "userlist":
		$users = embx_sql("	select users.username, count(users.isin) as isincount 
							from (select distinct username, isin from orders) as users 
							group by users.username 
							order by isincount desc");
		if (count($users)>0){
			echo "<ul>";
			foreach ($users as $user){
				if ($user["username"] && $user["username"] != "system"){
					echo "<li><a href='javascript:embx_getuserdetail(\"" . $user["username"] . "\")'>" 
								. $user["username"] .  "</a> ".$user["isincount"]." ISINs</li>";
				}
			}
			echo "</ul>";
		}	
	break;
		
	case "cptylist":
		$cptys = embx_sql(" select cptys.cpty, count(cptys.isin) as isincount 
							from (select distinct left(username,4) as cpty, isin from orders) as cptys 
							group by cptys.cpty order by isincount desc");
		if (count($cptys)>0){
			echo "<ul>";
			foreach ($cptys as $cpty){
				if ($cpty["cpty"] && $cpty["cpty"] != "syst"){
					echo "<li><a href='javascript:embx_getcptydetail(\"" . $cpty["cpty"] . "\")'>" 
							. $cpty["cpty"] .  "</a> ". $cpty["isincount"]." ISINs</li>";
				}
			}
			echo "</ul>";
		}	
		break;

	case "userdetail":
		$user = $_GET["user"];
		$detail = embx_sql("	select t1.tradingday, count(isin) as isincount 
								from (	select distinct date(ordertime) as tradingday, isin 
										from orders where username = '". $user ."') as t1 
								group by tradingday order by tradingday desc limit 20");
		$ret = embx_columngraph(	"pagecontent", 
								$detail, 
								"tradingday", 
								"isincount", 
								"Date", 
								"No of ISINs", 
								"Number of ISINs quoted by", 
								"Quoted ISINs by " . $user . " only", 
								"$.get('utils/embx_ajax.php?pf=bondlistfordayforuser&tradingday=' + this.category + '&user=" . $user . "',function(data){
										$('#detailcontent').html(data);
										});
								$('#detailheader').html('ISIN\'s quoted by " . $user . " <br />on ' + this.category);", 
								"<b>{point.y:,.0f} ISINs</b>" );
		echo  "<script>" . $ret . "</script>";
		
		break;
		
		case "cptydetail":
		$cpty = $_GET["cpty"];
		$detail = embx_sql("select t1.tradingday, count(isin) as isincount from (select distinct date(ordertime) as tradingday, 
					isin from orders where left(username,4) = '". $cpty ."') as t1 group by tradingday order by tradingday desc limit 20");
		
		//var_dump($detail);
		$ret = embx_columngraph("pagecontent", 
								$detail, 
								"tradingday", 
								"isincount", 
								"Date", 
								"No of ISINs", 
								"Number of ISINs quoted by", 
								"Quoted ISINs by " . $cpty . " only", 
								"$.get('utils/embx_ajax.php?pf=bondlistfordayforcpty&tradingday=' + this.category + '&cpty=" . $cpty . "',function(data){
										$('#detailcontent').html(data);
										});
								$('#detailheader').html('ISIN\'s quoted by " . $cpty . " <br />on ' + this.category);", 
								"<b>{point.y:,.0f} ISINs</b>" );
		echo  "<script>" . $ret . "</script>";
		
		break;
		



		case "marketsnapshot":
			date_default_timezone_set("UTC");
			$tradingday = ($_GET["tradingday"]);
			$starttradingday = "'" . $tradingday . " 00:00:00'";
			$minutes = $_GET["minutes"];
			
				$sst = strtotime($tradingday) + $minutes * 60;
				$snapshottime = date("Y-m-d H:i:s",$sst);
				if ($minutes == ""){
					$snapshottime = $tradingday . " " . $_GET["snapshottime"];
				}
				
				//echo $snapshottime;

				$isin = $_GET["isin"];

				$sql = "select price, size, username from orders where side = 'BUY'
						and ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime . 
					"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
					" and ordertype = 'Live' and isin = '" . $isin . "' order by price desc";
				$bidlive = embx_sql($sql);

				$sql = "select price, size, username from orders where side = 'SELL'  
						and ordertime > " . $starttradingday . "   and ordertime <= '" . $snapshottime . 
					"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
					" and ordertype = 'Live' and isin = '" . $isin . "'  order by price asc";
				$asklive = embx_sql($sql);

				$sql = "select price, size, username from orders where side = 'BUY' 
						and ordertime > " . $starttradingday . "  and ordertime <= '" . $snapshottime . 
					"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
					"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price desc";
				$bidindicative = embx_sql($sql);
				
				$sql = "select price, size, username from orders where side = 'SELL' and  
						ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime .  
					"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
					"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price asc";
				$askindicative = embx_sql($sql);

				echo "<h5>Market at " . $snapshottime . " "  .    "</h5>";
				$livebids = count($bidlive);
				$liveasks = count($asklive);
				$indicativebids = count($bidindicative);
				$indicativeasks = count($askindicative);
				?>
				<table>
					<thead>
						<tr>
							<th>Buyer</th>
							<th>Bid Amt</th>
							<th>Bid Price</th>
							<th>Ask Price</th>
							<th>Ask Amt</th>
							<th>Seller</th>
						</tr>
					</thead>
					<tbody>
				
				<?
				if ($livebids || $liveasks) {
					
					if ($livebids >= $liveasks) {
						$i=0;
						for($j=0; $j < $livebids; $j+=1){
							if ($i< $liveasks){
								?>
									<tr><td><? echo $bidlive[$j]["username"];?></td>
										<td><? echo number_format($bidlive[$j]["size"],0);?></td>
										<td><span class='label'><? echo number_format($bidlive[$j]["price"],4);?></span></td>
										<td><span class='label'><? echo number_format($asklive[$j]["price"],4);?></td>
										<td><? echo number_format($asklive[$j]["size"],0);?></td>
										<td><? echo $asklive[$j]["username"];?></td>
									</tr>
								<?
							} else {
								?>
									<tr><td><? echo $bidlive[$j]["username"];?></td>
										<td><? echo number_format($bidlive[$j]["size"],0);?></td>
										<td><span class='label'><? echo number_format($bidlive[$j]["price"],4);?></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								<?
							}
						$i = $i+1;	
						}
					} else {
						$i=0;
						for($j=0; $j < $liveasks; $j+=1){
							if ($i< $livebids){
								?>
									<tr>
										<td><? echo $bidlive[$j]["username"];?></td>
										<td><? echo number_format($bidlive[$j]["size"],0);?></td>
										<td><span class='label'><? echo number_format($bidlive[$j]["price"],4);?></td>
										<td><span class='label'><? echo number_format($asklive[$j]["price"],4);?></td>
										<td><? echo number_format($asklive[$j]["size"],0);?></td>
										<td><? echo $asklive[$j]["username"];?></td>
									</tr>
								<?
							} else {
								?>
									<tr>
										<td></td>
										<td></td>
										<td></td>
										<td><span class='label'><? echo number_format($asklive[$j]["price"],4);?></td>
										<td><? echo $asklive[$j]["size"];?></td>
										<td><? echo $asklive[$j]["username"];?></td>
									</tr>
								<?
							}
						$i = $i+1;	
						}						
					}
				}
				

				if ($indicativebids || $indicativeasks) {
					
					if ($indicativebids >= $indicativeasks) {
						$i=0;
						for($j=0; $j < $indicativebids; $j+=1){
							if ($i < $indicativeasks){
								?>
									<tr><td><? echo $bidindicative[$j]["username"];?></td>
										<td class='size'><? echo number_format($bidindicative[$j]["size"],0);?></td>
										<td><span class='label secondary'><? 
											echo number_format($bidindicative[$j]["price"],4);?></span></td>
										<td><span class='label secondary'><? echo number_format($askindicative[$j]["price"],4);?></td>
										<td class='size'><? echo number_format($askindicative[$j]["size"],0);?></td>
										<td><? echo $askindicative[$j]["username"];?></td>
									</tr>
								<?
							} else {
								?>
									<tr><td><? echo $bidindicative[$j]["username"];?></td>
										<td class='size'><? echo number_format($bidindicative[$j]["size"],0);?></td>
										<td><span class='label secondary'><? echo number_format($bidindicative[$j]["price"],4);?></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								<?
							}
						$i = $i+1;	
						}
					} else {
						$i=0;
						for($j=0; $j < $indicativeasks; $j+=1){
							if ($i < $indicativebids){
								?>
									<tr>
										<td><? echo $bidindicative[$j]["username"];?></td>
										<td  class='size'><? echo number_format($bidindicative[$j]["size"],0);?></td>
										<td><span class='label secondary'><? echo number_format($bidindicative[$j]["price"],4);?></td>
										<td><span class='label secondary'><? echo number_format($askindicative[$j]["price"],4);?></td>
										<td class='size'><? echo number_format($askindicative[$j]["size"],0);?></td>
										<td><? echo $askindicative[$j]["username"];?></td>
									</tr>
								<?
							} else {
								?>
									<tr>
										<td></td>
										<td></td>
										<td></td>
										<td><span class='label secondary'><? echo number_format($askindicative[$j]["price"],4);?></td>
										<td class='size'><? echo number_format($askindicative[$j]["size"],0);?></td>
										<td><? echo $askindicative[$j]["username"];?></td>
									</tr>
								<?
							}
						$i = $i+1;	
						}						
					}
				}


				
		break;		
		
		case "tradesummary":
			date_default_timezone_set("UTC");
			
			//embx_bondupdate($isin);
			$orders = embx_sql("select * from trades where isin != '".TESTISIN."'  order by tradetime");
			echo "<h6>Trade Summary</h6>";
			echo "<table class='embx-table'>";
			if (count($orders)>0){
				$prevdate = date_format(date_create($orders[0]["tradetime"]),"j F Y" );
				//echo "<div class='alert-box'>" . $prevdate . "</div>";
				//echo "<ul>";
				echo "<tr><th colspan='8'>" . $prevdate . "</th></tr>";
				foreach ($orders as $order) {
					$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
					$thetime =  date_format(date_create($order["tradetime"]),"H:i:s" );
					if ($thedate != $prevdate){
						//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
						echo "<tr><th colspan='8'>" . $thedate . "</th></tr>";
					}
					$prevdate = $thedate;
						$price =  number_format($order["price"],4);
						
						if ($order["buyer"] == $order["giver"]) {
							echo "<tr><td><span class='label radius success'>B</span></td><td>" . $order["buyer"] . "</td>" .
								"<td><span class='label radius alert'>S</span></td><td>" . $order["seller"] . "</td>" .
									"<td class='liveprice'>" . $price . "</td>" .
									"<td style='text-align: right;'>" . $order["currency"] . " " . number_format($order["size"],0) . "</td>" .
									"<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\"" . substr($order["tradetime"],0,10) . "\")'>" . $order["isin"] . "</a></td><td>".$thetime."</td></tr>" ;
						} else {
							echo "<tr><td><span class='label radius alert'>S</span></td><td>" . $order["seller"] . "</td>" .
								"<td><span class='label radius success'>B</span></td><td>" . $order["buyer"] . "</td>" .
									"<td class='liveprice'>" . $price. "</td>" .
									"<td style='text-align: right;'>" . $order["currency"] . " " . number_format($order["size"],0) . "</td>" .
									"<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\"" . substr($order["tradetime"],0,10)  . "\")'>" . $order["isin"] . "</a></td><td>".$thetime."</td></tr>" ;
							
						}


				}
				echo "</table>";
			}
			break;
			case "tradesummarydateisin":
				date_default_timezone_set("UTC");
				$theisin = $_GET["isin"];
				$thedate = $_GET["tradedate"];
				//embx_bondupdate($isin);
				$orders = embx_sql("select * from trades where isin = '" . $theisin . "' and date(tradetime) = '" . $thedate . "' order by tradetime");
				if ($orders){
					echo "<h6>Trade Summary for ". $thedate . " and for ". $theisin . "</h6>";
					echo "<table class='embx-table'>";
					if (count($orders)>0){
						foreach ($orders as $order) {
							$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
							if ($thedate != $prevdate){
								//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
								echo "<tr><th colspan='7'>" . $thedate . "</th></tr>";
							}
							$prevdate = $thedate;
								$price =  number_format($order["price"],4);
						
								if ($order["buyer"] == $order["giver"]) {
									echo 	"<tr>
												<td><span class='label success'>B</span></td>
												<td>" . $order["buyer"] . "</td>
												<td><span class='label alert'>S</span></td>
												<td>" . $order["seller"] . "</td>
												<td class='liveprice'>" . $price . "</td>
												<td style='text-align: right;'>" . $order["currency"] . " " 
													. number_format($order["size"],0) . "</td>
												<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\""
													. substr($order["tradetime"],0,10) . "\")'>" . $order["isin"] . "</a></td>
											</tr>" ;
								} else {
									echo 	"<tr>
												<td><span class='label alert'>S</span></td>
												<td>" . $order["seller"] . "</td>
												<td><span class='label success'>B</span></td>
												<td>" . $order["buyer"] . "</td>
												<td class='liveprice'>" . $price. "</td>
												<td style='text-align: right;'>" . $order["currency"] . " " 
													. number_format($order["size"],0) . "</td>
												<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\"" 
													. substr($order["tradetime"],0,10)  . "\")'>" . $order["isin"] . "</a></td>
											</tr>" ;
								}


						}
						echo "</table>";
					}
				}
				break;

		case "bonddetailforday":
			date_default_timezone_set("UTC");
			$isin = $_GET["isin"];
			//embx_bondupdate($isin);
			$orders = embx_sql("select * from orders where isin ='" . $isin 
						. "' and date(ordertime) = '" . $_GET["tradingday"] ."' order by ordertime");
			$trades = embx_tradesfordayforisin($isin,$_GET["tradingday"]);
			if ($trades){
				echo $trades;
			}
			//echo embx_tradesfordayforisin($isin,$_GET["tradingday"]);
			
			echo "<h6>Orders for ISIN: " . $isin . "</h6>";
			echo "<table>";
			if (count($orders)>0){
				$prevdate = date_format(date_create($orders[0]["ordertime"]),"j F Y" );
				//echo "<div class='alert-box'>" . $prevdate . "</div>";
				//echo "<ul>";
				echo "<tr><th colspan='9'>" . $prevdate . "</th></tr>";
				foreach ($orders as $order) {
					if ($order["ordertype"] == "Live") { 
						$price = "<span class='label'>" . number_format($order["price"],4) . "</span>";
					}	else {
						$price = "<span class='label secondary'>" . number_format($order["price"],4) . "</span>";
					}
					if ($order["side"] == "BUY") { 
						$side = "<span class='label success'>B</span>";
					}	else {
						$side = "<span class='label alert'>S</span>";
					}
					$thedate =  date_format(date_create($order["ordertime"]),"j F Y" );
					if ($thedate != $prevdate){
						//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
						echo "<tr><th colspan='9'>" . $thedate . "</th></tr>";
					}
					echo "<tr>	<td><strong>" . $order["orderid"] .  "</strong></td>
								<td>"  . $order["username"] . "</td>
								<td><a href='#' class='ordertime' id='" 
									. substr($order["ordertime"],11,8) . "'>" 
									. substr($order["ordertime"],11,8) . "</a></td>
								<td>" . $order["action"] . "</td>
								<td>" .  $side . "</td>
								<td>" . $price . "</td>
								<td style='text-align: right;'>" . number_format($order["size"],0) . "</td>
								<td><a href='#' class='ordertime'  id='" 
								. substr($order["endtime"],11,8) . "'>" 
								. substr($order["endtime"],11,8) . "</a></td>
								<td>" . $order["reason"] . "</td>
						</tr>";
					$prevdate = $thedate;
				}
				echo "</table>";
				?>
					<div class="row">
						<div class="small-2 columns">
							<div id="hourselection" class="range-slider vertical-range" 
								data-slider data-options="vertical: true; start:1080; end:480;">
								<span class="range-slider-handle" role="slider" tabindex="0"></span>
								<span class="range-slider-active-segment"></span>
								<input type="hidden">
							</div>
						</div>
						<div id="marketsnapshot" class="small-10 columns">
						</div>
					</div>
					<input type="hidden" value="0" id="marketsnapshotworking">
				<script>
				$(document).foundation();
				$(document).foundation('slider', 'reflow');
				if ($("#marketsnapshotworking").val() == 0) {
					$("#marketsnapshotworking").val(1);
					$.get("utils/embx_ajax.php?pf=marketsnapshot&isin=<? 
							echo $isin; ?>&tradingday=<? echo $_GET["tradingday"]; ?>&minutes=720",function(data){
						$('#marketsnapshot').html(data);
					}).done(function(){
						$("#marketsnapshotworking").val(0);
					});
				} else {
					
				}	
				$('#hourselection').on('change.fndtn.slider', function(){
					if ($("#marketsnapshotworking").val() == 0) {
						$("#marketsnapshotworking").val(1);
						$.get("utils/embx_ajax.php?pf=marketsnapshot&isin=<? 
							echo $isin; ?>&tradingday=<? echo $_GET["tradingday"]; 
							?>&minutes=" + $('#hourselection').attr('data-slider') ,function(data){
							$('#marketsnapshot').html(data);
						}).done(function(){
							$("#marketsnapshotworking").val(0);
						});
					}	
				});
						
				function selectsnapshot(stime){
					//alert(stime);
					$("#marketsnapshotworking").val(1);
			
					$.get("utils/embx_ajax.php?pf=marketsnapshot&isin=<? 
						echo $isin; ?>&tradingday=<? echo $_GET["tradingday"]; ?>&snapshottime=" + stime ,function(data){
							$('#marketsnapshot').html(data);
					}).done(function(){
						$("#marketsnapshotworking").val(0);
					});
				}
				$(".ordertime").click(function(){
					selectsnapshot(this.id);
				});
				</script>
				<?
			}
			break;

			case "tradesummaryfortradingday":
				$tradingday = $_GET["tradingday"];
				echo embx_tradesummaryfortradingday($tradingday);
			break;
			
			case "tradesummaryfortradingmonth":
				date_default_timezone_set("UTC");
				$tradingmonth = $_GET["tradingmonth"];
				$tradingday = "1 ".substr($tradingmonth,0,3)." 20".substr($tradingmonth,4,2);
				$tradingday = strtotime($tradingday);
				$tradingday = date("Y-m-d",$tradingday);
				echo embx_tradesummaryfortradingmonth($tradingday);
			break;
			
			case "tradingpartiessummaryfortradingmonth":
				date_default_timezone_set("UTC");
				$tradingmonth = $_GET["tradingmonth"];
				$tradingday = "1 ".substr($tradingmonth,0,3)." 20".substr($tradingmonth,4,2);
				$tradingday = strtotime($tradingday);
				$tradingday = date("Y-m-d",$tradingday);
				echo embx_tradingpartiessummaryfortradingmonth($tradingday);
			break;
			
			
			case "editbondlist":
			$sql = 'select * from currencies';
			$ccys = embx_sql($sql);
			$selectoptions = "<option value='-1' disabled selected>Select</option>";
			foreach ($ccys as $ccy){
				$selectoptions .= "<option value='".$ccy["currency"]."'>".$ccy["currency"]."</option>";
			}
			
			$sql = 'select * from (select distinct orders.isin as oisin from orders) as t1 left join bonds on t1.oisin = bonds.isin';
			$res = embx_sql($sql);
			if ($res){
				echo "<form>";
				foreach ($res as $bond){
					if (!$bond["id"]){
						echo "<div class='row'>
								<div class='small-3 columns'><label>ISIN</label><input type='text' value='".$bond["oisin"]."' id='isin".$bond["oisin"]."' disabled /></div>
						<div class='small-4 columns'><label>Name</label><input type='text' value='' id='name".$bond["oisin"]."'  /></div>
						<div class='small-3 columns'><label>Currency</label><select id='ccy".$bond["oisin"]."'>".$selectoptions."</select></div>
						<div class='small-2 columns'><label>&nbsp;</label><a href='#' class='button tiny bondeditbutton' id='".$bond["oisin"]."'>Go</a></div>
								</div>";

						//echo $bond["oisin"]."<br/>";
					}
				}
				echo "</form>";

			}
			
			break;
			
			case "updatebonddetails":
			$isin = $_GET["isin"];
			$name = $_GET["name"];
			$ccy = $_GET["ccy"];
			
			if (embx_sql("select * from bonds where isin = '".$isin."'")){
				$sql = "update bonds set bondname = '".$name."', currency='".$ccy."' where isin = '".$isin."'";
				embx_sql($sql);
				echo "Update Isin: " . $isin . "<br/>Name: " . $name . " Ccy: " . $ccy . "<br />";
			} else{
				$sql = "insert into bonds (isin,bondname,currency) values ('".$isin."','".$name."','".$ccy."')";
				embx_sql($sql);
				echo "Insert Isin: " . $isin . "<br/>Name: " . $name . " Ccy: " . $ccy . "<br />";
			}
			
			break;

			case "tradesummaryexcel":
				date_default_timezone_set("UTC");
			
				//embx_bondupdate($isin);
				$orders = embx_sql("select * from trades  order by tradetime");
				//echo "<h6>Trade Summary</h6>";
				echo "<table class='embx-table'>";
				if (count($orders)>0){
					$prevdate = date_format(date_create($orders[0]["tradetime"]),"j F Y" );
					//echo "<div class='alert-box'>" . $prevdate . "</div>";
					//echo "<ul>";
					//echo "<tr><th colspan='8'>" . $prevdate . "</th></tr>";
					echo "<tr><td>Date</td><td>D</td><td>Giver</td><td>D</td><td>Taker</td><td>Price</td><td>CCY</td><td>Size</td><td>ISIN</td></tr>";
					foreach ($orders as $order) {
						$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
						$thetime =  date_format(date_create($order["tradetime"]),"H:i:s" );
						if ($thedate != $prevdate){
							//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
							//echo "<tr><th colspan='9'>" . $thedate . "</th></tr>";
						}
						$prevdate = $thedate;
							$price =  number_format($order["price"],4);
						
							if ($order["buyer"] == $order["giver"]) {
								echo "<tr><td>".$order["tradetime"]."</td><td>B</td><td>" . $order["buyer"] . "</td>" .
									"<td>S</td><td>" . $order["seller"] . "</td>" .
										"<td class='liveprice'>" . $price . "</td>" .
										"<td style='text-align: right;'>" . $order["currency"] . "</td><td> " . $order["size"] . "</td>" .
										"<td>" . $order["isin"] . "</td></tr>" ;
							} else {
								echo "<tr><td>".$order["tradetime"]."</td><td>S</td><td>" . $order["seller"] . "</td>" .
									"<td>B</td><td>" . $order["buyer"] . "</td>" .
										"<td class='liveprice'>" . $price. "</td>" .
											"<td style='text-align: right;'>" . $order["currency"] . "</td><td> " . $order["size"] . "</td>" .
											"<td>" . $order["isin"] . "</td></tr>"  ;
							
							}


					}
					echo "</table>";
				}
				break;
			
				case "cptyrfqinits":
				echo embx_numinitiatedrfqs();
				break;
				
				case "resprate":
					$cpty = $_GET["cpty"];
					echo embx_rfqresponserate($cpty);
				break;
				
				case "involvedparties":
					$yearmonth = $_GET["yearmonth"];
					$sellers = embx_sql("select distinct left(seller,length(seller)-3) as cpty 
										from trades where isin <> 'XSTEST123456' and  ".$yearmonth." = (year(tradetime)*100+month(tradetime))");
					$buyers = embx_sql("select distinct left(buyer,length(buyer)-3) as cpty 
										from trades where isin <> 'XSTEST123456' and  ".$yearmonth." = (year(tradetime)*100+month(tradetime))");
					var_dump($sellers);
					var_dump($buyers);

					$everyone = array();
					foreach ($sellers as $seller){
						array_push($everyone,$seller["cpty"]);
					}
					foreach ($buyers as $buyer){
						array_push($everyone,$buyer["cpty"]);
					}
					$everyone = array_unique($everyone);
					var_dump($everyone);	
					
				
				break;
				
				
				case "testmyfunc":
					$myarr = embx_monthlytradingparties(201607,202102);
					var_dump($myarr);
					//echo "hello";
				break;
				
				

} 



?>

