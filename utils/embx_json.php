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

	case "bondlistforday":
		$bonds = embx_sql("select distinct orders.isin, bonds.currency, bonds.bondname, currencies.rate from orders, 
			bonds, currencies where date(orders.ordertime) = '" . $_GET["tradingday"] . "' 
			and bonds.isin = orders.isin and bonds.currency = currencies.currency and  orders.isin != '".TESTISIN."' order by bonds.bondname");
		$ret = json_encode($bonds);
		echo '{"items": ' . $ret . "}";

	break;
	
	case "processbond":
		$isin = $_GET["isin"];
		$tradingday = $_GET["tradingday"];
		$res = embx_getendofday($isin, $tradingday);
		if ($res) {
			
		} else {
			$res = embx_markethistory($isin, $tradingday);
			$sql = "DELETE FROM endofday WHERE tradingday = '" . $tradingday . "' AND isin='". $isin . "'";
			embx_sql($sql);
			$sql = "INSERT INTO endofday (isin,tradingday, max_sz_livebid, 
										max_sz_liveask, max_sz_indicativebid,
										max_sz_indicativeask, px_last_live_bid, px_last_live_ask,
										px_last_indicative_bid, px_last_indicative_ask,
										ts_last_live_bid, ts_last_live_ask, ts_last_indicative_bid,
										ts_last_indicative_ask) VALUES ('" . $isin . "', " .
										"'". $tradingday . "', ".
										$res["max_sz_livebid"].", ".
										$res["max_sz_liveask"].", ".
										$res["max_sz_indicativebid"].", ".
										$res["max_sz_indicativeask"].", ".
										$res["px_last_live_bid"].", ".
										$res["px_last_live_ask"].", ".
										$res["px_last_indicative_bid"].", ".
										$res["px_last_indicative_ask"].", ".
										$res["ts_last_live_bid"].", ".
										$res["ts_last_live_ask"].", ".
										$res["ts_last_indicative_bid"].", ".
										$res["ts_last_indicative_ask"].") ";
										//echo $sql;
			embx_sql($sql);
		}
		
		$bondname = embx_lookup("bonds","isin","'".$isin."'","bondname");
		$res["bondname"] = $bondname;								
		$ret = json_encode($res);
		echo '{"items": ' . $ret . "}";
	break;
	
	case "rfqsbkp":
		//$isin = $_GET["isin"];
		$tradingday = $_GET["tradingday"];
		//$rfqids = embx_sql("select distinct rfqid from rfq where date(actiontime) = '".$tradingday."'");
		$rfqs = embx_sql("select * from rfq where date(actiontime) = '".$tradingday."' order by rfqid asc, actiontime asc");
		$result = "";
		$numrfqs = 0;
		$numtrades = 0;

		$prevrfqid = 0;
		if ($rfqs){
			$result .= "<dl class='accordion' data-accordion>";
			foreach ($rfqs as $rfq){
				if ($rfq['action'] == 'rfq/initial'){
					$numrfqs = $numrfqs+1;
					$size = $rfq["size"];
					$rfqid = $rfq['rfqid'];
					$bondres = embx_sql("select * from bonds where isin ='".$rfq['isin']."'");
					if ($bondres){
						$bondccy = $bondres[0]["currency"];
						$bondname = $bondres[0]["bondname"];
					} else {
						$bondccy = "CCY";
						$bondname = $rfq['isin'];
					}
					
					
					$rfqtype = 'error';
					if ($rfq['rfqtype'] =="BOTH"){
						$rfqtype = "<span style='width: 50px;' class='label'>2WAY</span>";
					}
					if ($rfq['rfqtype'] == "OFFER"){
						$rfqtype = "<span style='width: 50px;' class='label success'>OFFER</span>";
					}
					if ($rfq['rfqtype']== "BID"){
						$rfqtype = "<span style='width: 50px;' class='label alert'>BID</span>";
					}


					if ($prevrfqid != $rfq['rfqid'] && $prevrfqid != 0) {
						
						$result .= "</tbody></table></div></dd>";
					}
						$result .=  "
							<dd class='accordion-navigation'>
							<a href='#rfq".$rfq["rfqid"]."'>".$rfqtype." ".substr($rfq['actiontime'],11,5)." 
								<strong>".$rfq["user"]."</strong> 
								<i class='fi-arrow-right'></i>
								 <strong>".$rfq["responders"]."</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<em>"
								.$bondccy." ".number_format($rfq["size"],0).
								"</em> of <strong>".$bondname."</strong>
							</a><div id='rfq".$rfq["rfqid"]."' class='content'>";
						//echo "Initiator: ".$rfq["user"]."<br/>";
						//echo "Responders: ".$rfq["responders"]."<br/>";
						$result .=  "<table class='embx-table'><thead><tr><th>Time</th><th>Action</th><th>User</th>
							<th style = 'text-align: right;'>Size</th><th style = 'text-align: right;'>Bid</th>
						<th style = 'text-align: right;'>Ask</th></tr></thead><tbody>";
				}
				if ($rfq['action'] == 'rfq/change'){
					if ($rfq['bidprice'] == 0){
						$bidprice = "";
					} else {
						$bidprice = number_format($rfq["bidprice"],4);
					}
					if ($rfq['askprice'] == 0){
						$askprice = "";
					} else {
						$askprice = number_format($rfq["askprice"],4);
					}
					
					
					$result .=
						"<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($size,0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
				}
				if ($rfq['action'] == 'rfq/revoke'){
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".
						$rfq["action"]."</td><td>".$rfq["user"]."</td>
					<td></td><td></td><td></td>";
				}
				if ($rfq['action'] == 'rfq/counter'){
					if ($rfq['bidprice'] == 0){
						$bidprice = "";
					} else {
						$bidprice = number_format($rfq["bidprice"],4);
					}
					if ($rfq['askprice'] == 0){
						$askprice = "";
					} else {
						$askprice = number_format($rfq["askprice"],4);
					}
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"].
						"</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($rfq['size'],0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
										
				}
				if ($rfq['action'] == 'rfq/timeout'){
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8).
						"</td><td>".$rfq["action"]."</td><td colspan=4></td></tr>";
				}
				if ($rfq['action'] == 'rfq/accept'){
					$numtrades = $numtrades+1;
					if ($rfq['tradedirection'] == "SELL"){
						$bidprice = "";
						$askprice = "<strong>".number_format($rfq["tradeprice"],4)."</strong>";
						$tradetext = " sells to ";
					} else {
						$askprice = "";
						$bidprice = "<strong>".number_format($rfq["tradeprice"],4)."</strong>";
						$tradetext = " buys from " ;
					}

					$result .= "<tr><td>".substr($rfq["actiontime"],11,8).
						"</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($rfq['size'],0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
					$result .= "<tr>".substr($rfq["actiontime"],11,8)."<td></td>
						<td><strong>trade</strong></td><td colspan=4>".
						$rfq["user"].$tradetext.$rfq["giveruser"]."</td></tr>";
					
				}
				$prevrfqid = $rfq['rfqid'];
			}
			$result .= "</div></dd></dl>";
			$result = "No of RFQs: ".$numrfqs."&nbsp;&nbsp;&nbsp;&nbsp; No of Trades:".$numtrades.$result;
		}
		echo $result;
	break;

	case "rfqs":
		//$isin = $_GET["isin"];
		$tradingday = $_GET["tradingday"];
		//$rfqids = embx_sql("select distinct rfqid from rfq where date(actiontime) = '".$tradingday."'");
		$rfqs = embx_sql("select * from rfq where date(actiontime) = '".$tradingday."' order by rfqid asc, actiontime asc");
		$rfqswithtrades = embx_sql("select rfqid from rfq where date(actiontime) = '".$tradingday.
			"' and action='rfq/accept' order by rfqid asc, actiontime asc");
		$result = "";
		$numrfqs = 0;
		$numtrades = 0;

		$prevrfqid = 0;
		if ($rfqs){
			$result .= "<dl class='accordion' data-accordion>";
			foreach ($rfqs as $rfq){
				if ($rfq['action'] == 'rfq/initial'){
					

					$isin = $rfq["isin"];
					
					
					$numrfqs = $numrfqs+1;
					$size = $rfq["size"];
					$rfqid = $rfq['rfqid'];
					$tradesearch = array_search($rfqid,array_column($rfqswithtrades,'rfqid'));
					if ($tradesearch !== false){
						$tradeindicator = "<span class ='label info'>TRADE</span>";
					} else {
						$tradeindicator = "";
					}
					$bondres = embx_sql("select * from bonds where isin ='".$rfq['isin']."'");
					if ($bondres){
						$bondccy = $bondres[0]["currency"];
						$bondname = $bondres[0]["bondname"];
					} else {
						$bondccy = "CCY";
						$bondname = $rfq['isin'];
					}
					
					
					$rfqtype = 'error';
					if ($rfq['rfqtype'] =="BOTH"){
						$rfqtype = "<span style='width: 80px;' class='label'>2WAY</span>&nbsp;&nbsp;";
					}
					if ($rfq['rfqtype'] == "OFFER"){
						$rfqtype = "<span style='width: 80px;' class='label success'>OFFER</span>&nbsp;&nbsp;";
					}
					if ($rfq['rfqtype']== "BID"){
						$rfqtype = "<span style='width: 80px;' class='label alert'>BID</span>&nbsp;&nbsp;";
					}


					if ($prevrfqid != $rfq['rfqid'] && $prevrfqid != 0) {
						$result .= "</tbody></table>".$marketsnapshot."</div></dd>";
					}
					
					if ($_GET["bringmarketsnapshot"] == 'true'){
						$marketsnapshot = 
							embx_marketstatus(substr($rfq['actiontime'],0,10),substr($rfq['actiontime'],11,8),$rfq['isin']);
					} else {
						$marketsnapshot = '';
					}
					

						$result .=  "
							<dd class='accordion-navigation'>
							<a href='#rfq".$rfq["rfqid"]."' >".$rfqtype." ".substr($rfq['actiontime'],11,5)." 
								<strong>".$rfq["user"]."</strong> 
								<i class='fi-arrow-right'></i> <strong>".$rfq["responders"]."</strong> ".$tradeindicator."<br/>
								<span class='label' style='background-color:#666666; color: #dddddd; width: 80px;'>".
								$rfqid."</span>&nbsp;&nbsp; <em>"
								.$bondccy." ".number_format($rfq["size"],0).
								"</em> of <strong>".$bondname."</strong> 
							</a><div id='rfq".$rfq["rfqid"]."' class='content'>";
						//echo "Initiator: ".$rfq["user"]."<br/>";
						//echo "Responders: ".$rfq["responders"]."<br/>";
						$result .=  "<table class='embx-table'><thead><tr><th>Time</th><th>Action</th><th>User</th>
							<th style = 'text-align: right;'>Size</th><th style = 'text-align: right;'>Bid</th>
						<th style = 'text-align: right;'>Ask</th></tr></thead><tbody>";
				}
				if ($rfq['action'] == 'rfq/change'){
					if ($rfq['bidprice'] == 0){
						$bidprice = "";
					} else {
						$bidprice = number_format($rfq["bidprice"],4);
					}
					if ($rfq['askprice'] == 0){
						$askprice = "";
					} else {
						$askprice = number_format($rfq["askprice"],4);
					}
					
					
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($size,0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
				}
				if ($rfq['action'] == 'rfq/revoke'){
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
					<td></td><td></td><td></td>";
				}
				if ($rfq['action'] == 'rfq/counter'){
					if ($rfq['bidprice'] == 0){
						$bidprice = "";
					} else {
						$bidprice = number_format($rfq["bidprice"],4);
					}
					if ($rfq['askprice'] == 0){
						$askprice = "";
					} else {
						$askprice = number_format($rfq["askprice"],4);
					}
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($rfq['size'],0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
										
				}
				if ($rfq['action'] == 'rfq/timeout'){
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td colspan=4></td></tr>";
				}
				if ($rfq['action'] == 'rfq/accept'){
					$numtrades = $numtrades+1;
					if ($rfq['tradedirection'] == "SELL"){
						$bidprice = "";
						$askprice = "<strong>".number_format($rfq["tradeprice"],4)."</strong>";
						$tradetext = " sells to ";
					} else {
						$askprice = "";
						$bidprice = "<strong>".number_format($rfq["tradeprice"],4)."</strong>";
						$tradetext = " buys from " ;
					}

					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td><td>".$rfq["action"]."</td><td>".$rfq["user"]."</td>
						<td style = 'text-align: right;'>"
					.number_format($rfq['size'],0)."</td><td style = 'text-align: right;'>"
					.$bidprice."</td><td style = 'text-align: right;'>"
					.$askprice."</td>";
					$result .= "<tr><td>".substr($rfq["actiontime"],11,8)."</td>
						<td><strong>trade</strong></td><td colspan=4>".$rfq["user"].$tradetext.$rfq["giveruser"]."</td></tr>";
					
				}
				$prevrfqid = $rfq['rfqid'];
			}
			
			$result .= "</tbody></table>".$marketsnapshot."</div></dd></dl>";
			$result = "No of RFQs: ".$numrfqs."&nbsp;&nbsp;&nbsp;&nbsp; No of Trades:".$numtrades.$result;
		}
		echo $result;
	break;
	
	case "rfqcleanup":
	$tradingday = $_GET["tradingday"];
	embx_sql("delete from rfq where date(actiontime) = '".$tradingday."'");
	embx_sql("delete from trades where date(tradetime) = '".$tradingday."'");
	embx_sql("delete from orders where date(ordertime) = '".$tradingday."'");
	embx_sql("delete from processed where filename like  '%".$tradingday."%'");
	echo 'done it';
	
	break;
	
	
	case "rfqfix":
	$res = embx_sql("select * from rfq ");
	foreach ($res as $item){
		embx_sql('delete from rfq where id = '.$item['id']);
		echo $item['content'];
		embx_rfqentry($item['actiontime'],$item['user'],$item['counterparty'],$item['content'],$item['action'],$item['logid']);
	}
	
	break;



} 




?>

