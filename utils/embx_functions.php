<?php
function embx_sql($sql) {
	$ret = [];
	$rs = EMBXDB::get()->query($sql);
		if ($rs ){
			if (mysqli_num_rows($rs) > 0) {
				$row = mysqli_fetch_assoc($rs);
				$j = 0;
				do {
					foreach($row as $key => $value) {
						$ret[$j][$key] = $value;	
					};
					$j=$j+1;
				} while ($row = mysqli_fetch_assoc($rs));
			};
		};
	return $ret;
}

function embx_lookup($table,$key_name,$key_value,$lookup_name){
	$sqltxt = "SELECT " . $lookup_name . " FROM " . $table . " WHERE " . $key_name  . " = " . $key_value;
	//echo $sqltxt;
	$rs = EMBXDB::get()->query($sqltxt);
	if ($rs){
		if (mysqli_num_rows($rs) > 0) {
			$row = mysqli_fetch_assoc($rs);
			return $row[$lookup_name];	
		} else {
			return null;
		}
	}
}

function embx_processfile($filename) {
	$rs = EMBXDB::get()->query("truncate orderstemp");
	$rs = EMBXDB::get()->query("truncate temp");
	$f = embx_lookup("processed","filename","'". $filename . "'" ,"id");
	$fileid = $f;
	$lines = file("../source_files/" . $filename);
	foreach ($lines as $line) {

		$array = explode(";",$line);
		if (count($array) >= 8 ){
			$logid = $array[0];	
			$time_1 = $array[1];
			$time_2 = $array[2];
			$type = $array[3];
			$user = $array[4];
			$counterparty = $array[5];
			$action = $array[6];
			if (count($array) == 8) {
				$content = $array[7];
			} else {
				$content = $array[8];
			}
			$rs = EMBXDB::get()->query("insert into temp (logid, logtime, logothertime, type, user, cpty, action, content) values ( " 
					. $logid . ", '" . $time_1 . "', '" . $time_2 . "', '" . $type . "', '" 
					. $user . "', '" . $counterparty . "', '" . $action . "', '" . $content . "')" );
		} else {
			$array = explode(";",$line);
		}
	}

	$lines = embx_sql("select * from temp order by logid asc, logtime asc");
	$i=0;
	foreach ($lines as $line)  {
		$i = $i + 1;
		$thisline = $line;
		if (count($thisline == 8 )) {
			embx_processline($line,$fileid);
		};
	}
		$rs = EMBXDB::get()->query("insert into orders (orderid,username,counterparty,isin,quotetype,size,price,side,ordertime,
									reason,endtime,ordertype,timeinforce,filltype,anonymity,logid,action) select 
									orderid,username,counterparty,isin,quotetype,size,price,side,ordertime,
									reason,endtime,ordertype,timeinforce,filltype,anonymity,logid,action
									 from orderstemp");
		
		//$rs = EMBXDB::get()->query("truncate orderstemp");
									
		return "<strong>".$filename."</strong> has been processed with ".$i." lines.";
	
}

function embx_processline($line,  $fileid){
	$array = $line;

	if (count($array) == 8 ){

		$logid = $array["logid"];	
		$time_1 = $array["logtime"];
		$time_2 = $array["logothertime"];
		$type = $array["type"];
		$user = $array["user"];
		$counterparty = $array["cpty"];
		$action = $array["action"];
		$content = $array["content"];
		
		switch ($action) {
			case "order/add":
				embx_order_add($time_1, $user, $counterparty, $content, $logid);
			break;
			case "order/update":
				embx_order_update($time_1, $user, $counterparty, $content, $logid);
			break;
			case "order/takeover":
				embx_order_update($time_1, $user, $counterparty, $content, $logid, 'takeover');
			break;
				
			case "order/cancel":
				embx_order_cancel($time_1, $user, $counterparty, $content, $logid);
			break;
			case "panic/user":
				embx_order_cancel_panic($time_1, $user, $counterparty, $content, $logid);
			break;	
			case "order/excel/cancel":
				embx_order_cancel_panic($time_1, $user, $counterparty, $content, $logid);
			break;	
			case "panic/counterparty":
				embx_order_cancel_panic($time_1, $user, $counterparty, $content, $logid);
			break;	
			case "panic/counterparties":
				embx_order_cancel_panic($time_1, $user, $counterparty, $content, $logid);
			break;	
			case "trade/capture":
				embx_trade_capture($time_1, $user, $counterparty, $content, $logid);
			break;
			case "trader1info":
				embx_tradeinfo($time_1, $user, $counterparty, $content, $logid);
			break;
			case "trader2info":
				embx_tradeinfo($time_1, $user, $counterparty, $content, $logid);
			break;
			case "rfq/initial":
			case "rfq/change":
			case "rfq/revoke":
			case "rfq/accept":
			case "rfq/counter":
			case "rfq/timeout":
				embx_rfqentry($time_1,$user,$counterparty, $content, $action, $logid);
			break;

			default:
			/*
			$temp = "";
			foreach ($line as $item){
				$temp = $temp . $item . ";";
			}
			$temp = substr($temp, 0, strlen($temp)-1);
			$rs = EMBXDB::get()->query("insert into rejects (logid, fileid, line) values (" . $logid . "," . $fileid . ",'" . EMBXDB::get()->escape_string($temp) . "')");
			echo "Catch default - insert into rejects (logid, fileid, line) values (" . $logid . "," . $fileid . ",'" . EMBXDB::get()->escape_string($temp) . "')";
			*/

			
			}
		return;
	} else {
			return;
	} 
}

function embx_rfqentry($time_1,$user,$counterparty,$contentraw,$action,$logid){
		$limitSell = 0;
		$limitBuy = 0;
		$rfqtype = "";
		$responders = "";
		$responderuser = "";
		$respondercounterparty = "";
		$giveruser = "";
		$givercounterparty = "";
		$size = 0;
		$bidprice = 0;
		$askprice = 0;
		$tradeprice = 0;
		
		$content = str_replace('"','',$contentraw);
		$beg = strpos($content, 'RFQ ');
		$end = strpos($content, ']');
		$rfqid = substr($content, $beg+4,$end-$beg-4);

		if ($action == "rfq/timeout"){
			
		}
		
		if ($action == "rfq/revoke"){
			
		}

		if ($action == "rfq/initial"){
			
			$beg = strpos($content, '<');
			$end = strpos($content, ' on ');
			$rfqtype = substr($content, $beg+1,$end-$beg-1);

			$beg = strpos($content, '(ISIN:');
			$isin = substr($content, $beg+6,12);

			$beg = strpos($content, 'with ');
			$end = strpos($content, ' (size)');
			$size = substr($content, $beg+5,$end-$beg-5);

			$beg = strpos($content, 'bonds to [');
			$end = strlen($content);
			$responders = substr($content, $beg+10,$end-$beg-13);
		}
		
		if ($action == "rfq/accept"){
			$beg = strpos($content, 'with ');
			$end = strpos($content, ' (size)');
			$size = substr($content,$beg+5,$end-$beg-5);

			$beg = strpos($content, ' in ');
			$end = strpos($content, ' side  from');
			$tradedirection = substr($content, $beg+12,$end-$beg-12);

			$beg = strpos($content, 'bonds at ');
			$end = strpos($content, ' in');
			$tradeprice = substr($content, $beg+9,$end-$beg-15);
			
			$beg = strpos($content, 'side  from ');
			$end = strpos($content, ' from CP:',$beg);
			$giveruser = substr($content, $beg+11,$end-$beg-11);
			
			$beg1 = strpos($content, 'side  from ');
			$beg = strpos($content, 'from CP: ',$beg1);
			$givercounterparty = substr($content, $beg+9,strlen($content)-$beg-9);
		}
		
		if ($action == "rfq/change"){
			$responderuser = $user;
			$respondercounterparty = $counterparty;
			if (strpos($content, 'limitSell') === false){
				$hassell = false;
			} else {
				$hassell = true;
			}
			if (strpos($content, 'limitBuy') === false){
				$hasbuy = false;
			} else {
				$hasbuy = true;
			}
			$limitSell = 0;
			$limitBuy = 0;
			if ($hassell == true && $hasbuy == true){
				$beg = strpos($content, 'limitSell:');
				$end = strpos($content, ',',$beg);
				$begbuy = strpos($content, 'limitBuy:');
				$endbuy = strpos($content,">",$begbuy);
				$limitSell = substr($content,$beg+10,$end-$beg-10);
				$limitBuy = substr($content,$begbuy+9,$endbuy-$begbuy-9);
			}
			if ($hassell == false && $hasbuy == true){
				
				$begbuy = strpos($content, 'limitBuy:');
				$endbuy = strpos($content,">",$begbuy);
				$limitBuy = substr($content,$begbuy+9,$endbuy-$begbuy-9);
				
			}
			if ($hassell == true && $hasbuy == false){
				$beg = strpos($content, 'limitSell:');
				$end = strpos($content, '>',$beg);
				$limitSell = substr($content,$beg+10,$end-$beg-10);
			}
		}

		if ($action == "rfq/counter"){

			$beg = strpos($content,']  from ');
			$end = strpos($content,' from CP: ');
			$responderuser = substr($content,$beg+8,$end-$beg-8);

			$beg = strpos($content,'from CP: ');
			$end = strpos($content,' <');
			$respondercounterparty = substr($content,$beg+9,$end-$beg-9);
			

			if (strpos($content, ')(') === false){  // Simple Counter
				// simple counter
				$beg = strpos($content,'[( size: ');
				$end = strpos($content,' user',$beg);
				$size = substr($content,$beg+9,$end-$beg-9);
				
				$beg = strpos($content,'user: ',$beg);
				$beg = strpos($content,' ',$beg+7);
				$end = strpos($content,':',$beg);
				$direction = substr($content,$beg+1,$end-$beg-1);
				
				$beg = $end+2;
				$end = strpos($content,')]');
				$price = substr($content,$beg,$end-$beg);

				if ($direction == 'limitSell'){
					$limitBuy = $price;
				} else {
					$limitSell = $price;
				}
			} else {							// Counter Counter
				$beg = strpos($content, '[( size: ');
				$end = strpos($content,' user',$beg);
				$size = substr($content,$beg+9,$end - $beg - 9);

				$beg = strpos($content, 'user: ',$beg);
				$end = strpos($content, ' lim',$beg);
				$user1 = substr($content, $beg+6,$end - $beg -6);
				
				$beg = strpos($content, 'lim', $beg);
				$end = strpos($content, ':', $beg);
				$direction1 = substr($content,$beg, $end - $beg);

				$beg = strpos($content,': ',$beg+6);
				$end = strpos($content,')',$beg);
				$price1 = substr($content,$beg+2,$end -$beg -2);
				
				$beg = strpos($content, ')(');
				$beg = strpos($content, 'user: ',$beg);
				$end = strpos($content, ' lim',$beg);
				$user2 = substr($content, $beg+6,$end - $beg -6);

				$beg = strpos($content, 'lim', $beg);
				$end = strpos($content, ':', $beg);
				$direction2 = substr($content,$beg, $end - $beg);

				$beg = strpos($content,': ',$beg+6);
				$end = strpos($content,')',$beg);
				$price2 = substr($content,$beg+2,$end -$beg -2);
				
				$limitSell = 0;
				$limitBuy = 0;

				if ($user == $user1){
					if ($direction1 == 'limitBuy'){
						$limitSell = $price1;
					} else {
						$limitBuy = $price1;
					}
				} else {
					if ($direction2 == 'limitBuy'){
						$limitSell = $price2;
					} else {
						$limitBuy = $price2;
					}
				}
			} 
		} 

		
		$sql = "insert into rfq (rfqid, bidprice,askprice,tradeprice,size,logid, 
				action,actiontime,tradedirection,responderuser,respondercounterparty,content,responders,
				isin,rfqtype,giveruser,givercounterparty,counterparty,user) values 
				(	".$rfqid.",
					".$limitBuy.",
					".$limitSell.",
					".$tradeprice.",
					".$size.",
					".$logid.",
					'".$action."',
					'".$time_1."',
					'".$tradedirection."',
					'".$responderuser."',
					'".$respondercounterparty."',
					'".$content."',
					'".$responders."',
					'".$isin."',
					'".$rfqtype."',
					'".$giveruser."',
					'".$givercounterparty."',
					'".$counterparty."',
					'".$user."')";
		$rs = EMBXDB::get()->query($sql);
		
}

function embx_order_add($time_1, $user, $counterparty, $content, $logid){
	$counterpartycode = substr($user,0,strlen($user)-3);
	$username = $user;
	if ($user == 'system'){
		$beg = strpos($content,' from ')+6;
		$end = strpos($content,' ',$beg);
		$username = substr($content,$beg,$end-$beg);
		$sysauto = 1;
	}

	$beg = strpos($content, '[');
	$end = strpos($content, ']');
	$orderid = substr($content, $beg+7,$end-$beg-7);
	
	$beg = strpos($content, 'with');
	$end = strpos($content, '(size)');

	$size = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '(ISIN:');
	$end = strpos($content, ') bonds');

	$isin = substr($content, $beg+6,$end-$beg-6);
	
	$beg = strpos($content, 'bonds at ');
	$end = strpos($content, '(', strpos($content, "(ISIN")+1);
	$price = substr($content, $beg+9,$end-$beg-9);
	$beg = strpos($content, '(', strpos($content, "(ISIN")+1);
	$quotetype = substr($content, $beg+1,5);
	
	$beg = strpos($content, ') on');
	$end = strpos($content, 'side');

	$side = substr($content, $beg+5,$end-$beg-6);

	$beg = strpos($content, 'Type: [');
	$end = strpos($content, ']',$beg);

	$ordertype = explode(",",str_replace(" ","",substr($content, $beg+7,$end-$beg-7)));
	
	/* $sql = "select id from orderstemp 
			where left(username,4) = '".$counterpartycode."' and 
		 	side = '".$side."' and 
			isin = '".$isin."' and
			isnull(endtime) and 
			date(ordertime) = date('".$time_1."')";
	if ($idlist){
		foreach ($idlist as $id) {
			$sql = " update orderstemp set endtime = '".$time_1."', reason = 'cleanup' where id=".$id['id'];
			$rs = EMBXDB::get()->query($sql);
		} 
	}
	*/
	
	if ($ordertype[0] == "FoK") {
		if ($user == 'system'){
			$thereason = 'trdrem-';
			$sql = "insert into orderstemp (orderid, logid, username, counterparty, isin, 
			side, quotetype, size, price, ordertime, endtime, reason, ordertype, 
			timeinforce, filltype, anonymity,  action, sysauto) values 
			(" .
			$orderid . ", " .
				$logid . ", " .
					"'" . $username . "', " .
						"'" . $counterparty . "', " .
							"'" . $isin . "', " .
								"'" . $side . "', " .
									"'" . $quotetype . "', " .
										"" . $size. ", " .
											"" . $price . ", " .
												"'" . $time_1 . "', " . "'" . $time_1 . "', " . "'FoK'," .
													"'" . $ordertype[2] . "', " .
														"'" . $ordertype[0] . "', " .
															"'" . $ordertype[3] . "', " .
																"'" . $ordertype[1] . "', " .
																	"'" . $thereason . $logid . "'" .
																		" ,1)";
		} else {
			$thereason = 'add-';
			$sql = "insert into orderstemp (orderid, logid, username, counterparty, isin, 
			side, quotetype, size, price, ordertime, endtime, reason, ordertype, 
			timeinforce, filltype, anonymity,  action) values 
			(" .
			$orderid . ", " .
				$logid . ", " .
					"'" . $username . "', " .
						"'" . $counterparty . "', " .
							"'" . $isin . "', " .
								"'" . $side . "', " .
									"'" . $quotetype . "', " .
										"" . $size. ", " .
											"" . $price . ", " .
												"'" . $time_1 . "', " . "'" . $time_1 . "', " . "'FoK'," .
													"'" . $ordertype[2] . "', " .
														"'" . $ordertype[0] . "', " .
															"'" . $ordertype[3] . "', " .
																"'" . $ordertype[1] . "', " .
																	"'" . $thereason . $logid . "'" .
																		" )";
		}

		
	}
	else
	{
		if ($user == 'system'){
			$thereason = 'trdrem-';
			$sql = "insert into orderstemp (orderid, logid, username, counterparty, isin, 
			side, quotetype, size, price, ordertime, ordertype, 
			timeinforce, filltype, anonymity,  action, sysauto) values 
			(" .
			$orderid . ", " .
				$logid . ", " .
					"'" . $username . "', " .
						"'" . $counterparty . "', " .
							"'" . $isin . "', " .
								"'" . $side . "', " .
									"'" . $quotetype . "', " .
										"" . $size. ", " .
											"" . $price . ", " .
												"'" . $time_1 . "', " .
													"'" . $ordertype[2] . "', " .
														"'" . $ordertype[0] . "', " .
															"'" . $ordertype[3] . "', " .
																"'" . $ordertype[1] . "', " .
																	"'" . $thereason . $logid . "'" .
																		", 1 )";
		} else {
			$thereason = 'add-';
			$sql = "insert into orderstemp (orderid, logid, username, counterparty, isin, 
			side, quotetype, size, price, ordertime, ordertype, 
			timeinforce, filltype, anonymity,  action) values 
			(" .
			$orderid . ", " .
				$logid . ", " .
					"'" . $username . "', " .
						"'" . $counterparty . "', " .
							"'" . $isin . "', " .
								"'" . $side . "', " .
									"'" . $quotetype . "', " .
										"" . $size. ", " .
											"" . $price . ", " .
												"'" . $time_1 . "', " .
													"'" . $ordertype[2] . "', " .
														"'" . $ordertype[0] . "', " .
															"'" . $ordertype[3] . "', " .
																"'" . $ordertype[1] . "', " .
																	"'" . $thereason . $logid . "'" .
																		" )";
		}

	}
	$rs = EMBXDB::get()->query($sql);
}

function embx_order_cancel($time_1, $user, $counterparty, $content, $logid){
	$beg = strpos($content, '[');
	$end = strpos($content, ']');
	$orderid = substr($content, $beg+7,$end-$beg-7);
	
	$beg = strpos($content, 'with');
	$end = strpos($content, '(size)');
	$size = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '(ISIN:');
	$end = strpos($content, ') bonds');
	$isin = substr($content, $beg+6,$end-$beg-6);

	$beg = strpos($content, 'bonds at ');
	$end = strpos($content, '(', strpos($content, "(ISIN")+1);
	$price = substr($content, $beg+9,$end-$beg-9);

	$beg = strpos($content, '(', strpos($content, "(ISIN")+1);
	$quotetype = substr($content, $beg+1,5);

	$beg = strpos($content, ') on');
	$end = strpos($content, 'side');
	$side = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '] from');
	$end = strpos($content, 'from CP:');
	$user1 = substr($content, $beg+7,$end-$beg-8);
	
	$beg = strpos($content, 'CP:');
	$end = strpos($content, 'with');
	$cpty1 = substr($content, $beg+3,$end-$beg-4);

	$beg = strpos($content, 'Type: [');
	$end = strpos($content, ']',$beg);
	$ordertype = explode(",",str_replace(" ","",substr($content, $beg+7,$end-$beg-7)));
	

	
	$id = embx_sql("select id from orderstemp where orderid = " . $orderid . 
			" and ordertime <= '" . $time_1 . "' and isnull(endtime) order by logid asc , id desc limit 1" );
	

	if (!strpos($content,"automatically")){
//		$id = embx_sql("select id from orderstemp where orderid = " . $orderid . 
//				" and ordertime <= '" . $time_1 . "' and isnull(endtime) order by logid asc , id desc limit 1" );
	
		$sqlupdate = "update orderstemp set endtime = '" . $time_1 . 
				"', reason = 'cxl-" . $logid . "' where id = " . $id[0]["id"];
		$rs = EMBXDB::get()->query($sqlupdate);
	} else {
		
		if ($ordertype[0] == "FoK"){
			if (isset($id[0]["id"])) {
				$sqlupdate  = "update orderstemp set endtime = '" . $time_1 . 
					"', reason = 'expire-" . $logid . "' where id = " . $id[0]["id"];
				$rs         = EMBXDB::get()->query($sqlupdate);
			}
		}
		else {
			if (isset($id[0]["id"])) {
				$sqlupdate  = "update orderstemp set endtime = '" . $time_1 . 
					"', reason = 'syspull-" . $logid . "' where id = " . $id[0]["id"];
				$rs         = EMBXDB::get()->query($sqlupdate);
			}
		}		
	}
}

function embx_tradeinfo($time_1, $user, $counterparty, $content, $logid){
	$beg = strpos($content, 'transaction');
	$end = strpos($content, ']');
	$tradeid = substr($content, $beg+12,$end-$beg-12);
	
	$beg = strpos($content, 'User');
	$end = strpos($content, '(');
	$user = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '<');
	$end = strpos($content, 'S>');
	$side = substr($content, $beg+1,$end-$beg-1);
	
	$beg = strpos($content, '(');
	$end = strpos($content, ')');
	$role = substr($content, $beg+1,$end-$beg-1);
	
	$beg = strpos($content, 'with');
	$end = strpos($content, '(size');
	$size = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '(ISIN:');
	$end = strpos($content, ') bonds');
	$isin = substr($content, $beg+6,$end-$beg-6);
	
	$beg = strpos($content, ' in ');
	$end = strpos($content, ').');
	$ccy = substr($content, $beg+4,$end-$beg-4);
	
	$beg = strpos($content, 'bonds at ');
	$end = strpos($content, '(', strpos($content, "(ISIN")+1);
	$price = substr($content, $beg+9,$end-$beg-9);
	
	$beg = strpos($content, '(', strpos($content, "(ISIN")+1);
	$quotetype = substr($content, $beg+1,5);
	
	$check = embx_sql("select * from trades where tradeid = " . $tradeid);
	if ($side == "BUY") {   // We record market snapshot here - not twice
		$side = 'buyer';
		$snapshot = embx_recordsnapshot("TRADE",$check[0]["tradeid"],$isin, $time_1);
	} else { $side = 'seller';}
	if ($check){
		$sql = "update trades set " . $side . "='" . $user . "', " . $role . "='" . $user . "' where tradeid = " . $check[0]["tradeid"];
		$rs = EMBXDB::get()->query($sql);
	} else {
		$sql = "insert into trades (tradeid, logid, " . $role . ", " . $side . ",  
				isin,  quotetype, size, price, tradetime, currency) values 
			(" .
				$tradeid . ", " .
				$logid . ", " .
				"'" . $user . "', " .
				"'" . $user . "', " .
				"'" . $isin . "', " .
				"'" . $quotetype . "', " .
				"" . $size. ", " .
				"" . $price . ", " .
				"'" . $time_1 . "', " .
				"'" . $ccy . "' )";
	}	
	$rs = EMBXDB::get()->query($sql);
}


function embx_order_cancel_panic($time_1, $user, $counterparty, $content, $logid){
	$beg = strpos($content, '[');
	$end = strpos($content, ']');
	$orders = substr($content, $beg+1,$end-$beg-1);
	$orderlist = explode(',',$orders);
	if (count($orderlist)>0){
		foreach($orderlist as $order){
			$id = embx_sql("select id from orderstemp where orderid = " . $order . " and ordertime <='" . $time_1 . "' order by ordertime desc, id desc limit 1" );
	
			$sqlupdate = "update orderstemp set endtime = '" . $time_1 . "', reason = 'panic' where id = " . $id[0]["id"];
			$rs = EMBXDB::get()->query($sqlupdate);
		}
	}
}

function embx_trade_capture($time_1, $user, $counterparty, $content, $logid){
	$beg = strpos($content, 'match');
	$end = strpos($content, ']');
	if ($beg && ($beg < $end)){
		$orderid = substr($content, $beg+6,$end-$beg-6);
		$id = embx_sql("select id from orderstemp where orderid = " . $orderid . " 
			and ordertime <='" . $time_1 . "' order by logid asc limit 1" );
		$sqlupdate = "update orderstemp set endtime = '" . $time_1 . "', reason = 'trade' where id = " . $id[0]["id"];
		$rs = EMBXDB::get()->query($sqlupdate);
	}
	
	
	
}

function embx_order_update($time_1, $user, $counterparty, $content, $logid, $reasontext = 'update'){
	$beg = strpos($content, '[');
	$end = strpos($content, ']');
	$orderid = substr($content, $beg+7,$end-$beg-7);
	
	$beg = strpos($content, 'with');
	$end = strpos($content, '(size)');

	$size = substr($content, $beg+5,$end-$beg-6);
	
	$beg = strpos($content, '(ISIN:');
	$end = strpos($content, ') bonds');

	$isin = substr($content, $beg+6,$end-$beg-6);
	
	$beg = strpos($content, 'bonds at ');
	$end = strpos($content, '(', strpos($content, "(ISIN")+1);
	$price = substr($content, $beg+9,$end-$beg-9);
	
	$beg = strpos($content, '(', strpos($content, "(ISIN")+1);
	$quotetype = substr($content, $beg+1,5);
	
	$beg = strpos($content, ') on');
	$end = strpos($content, 'side');

	$side = substr($content, $beg+5,$end-$beg-6);

	$beg = strpos($content, 'Type: [');
	$end = strpos($content, ']',$beg);

	$ordertype = explode(",",str_replace(" ","",substr($content, $beg+7,$end-$beg-7)));
	
	$id = embx_sql("select id from orderstemp where orderid = " . $orderid . " and ordertime <= '" . $time_1 . "' and isnull(endtime) order by ordertime desc, id desc limit 1" );
	$sqlupdate = "update orderstemp set endtime = '" . $time_1 . "', reason = '" . $reasontext . "-" . $logid . "' where id = " . $id[0]["id"];
	$rs = EMBXDB::get()->query($sqlupdate);
	$sql = "insert into orderstemp (orderid, logid, username, counterparty, isin, side, quotetype, size, price, ordertime, ordertype, 
								timeinforce, filltype, anonymity, action) values 
								(" .
								$orderid . ", " .
								$logid . ", " .
								"'" . $user . "', " .
								"'" . $counterparty . "', " .
								"'" . $isin . "', " .
								"'" . $side . "', " .
								"'" . $quotetype . "', " .
								"" . $size . ", " .
								"" . $price . ", " .
								"'" . $time_1 . "', " .
								"'" . $ordertype[2] . "', " .
								"'" . $ordertype[0] . "', " .
								"'" . $ordertype[3] . "', " .
								"'" . $ordertype[1] . "', " .
								"'update-" . $logid . "' " .
								" )";
	$res = EMBXDB::get()->query($sql);
}

function embx_bondupdate($isin){
	$sql = "select * from orders where isin='" . $isin . "'";
	echo $sql;
	$orders = embx_sql($sql);
	if ($orders != ""){
		foreach ($orders as $order){
			$i = $i+1;
			echo $order["orderid"] . " " . $order["logid"] . "<br />";
			$orderid = $order["orderid"];
			if ($i < 250) {
				$rs = EMBXDB::get()->query("update orders set isin='" . $isin . "' where isnull(isin) and orderid=" . $orderid);
				echo "Time=" . time() . "<br />";
				echo "update orders set isin='" . $isin . "' where isnull(isin) and orderid=" . $orderid . "<br />";
			}
		}
	}
}

function embx_cleantables(){
	$rs = EMBXDB::get()->query("truncate processed");
	$rs = EMBXDB::get()->query("truncate orders");
}

function embx_columnchartformat($data,$x,$y){
	$chartdata = "";
	foreach ($data as $item){
		$chartdata = $chartdata . "['" . $item[$x] .   "', " . $item[$y] . "],";
	}
	$chartdata = substr($chartdata,0,strlen($chartdata)-1);
	return $chartdata;
}

function embx_markethistorygraph($containerid, $data,  $xaxis, $yaxis, $title, $subtitle){
	$livebids = embx_preparedata($data["hour"],$data["px_livebid"]);
	$indicativebids = embx_preparedata($data["hour"],$data["px_indicativebid"]);
	$liveasks = embx_preparedata($data["hour"],$data["px_liveask"]);
	$indicativeasks = embx_preparedata($data["hour"],$data["px_indicativeask"]);
	$ret = "
	$(function () {
	    $('#".$containerid."').highcharts({
	        chart: {
	            type: 'scatter',
	            zoomType: 'xy'
	        },
	        title: {
	            text: '".$title."'
	        },
	        subtitle: {
	            text: '".$subtitle."'
	        },
	        xAxis: {
				type: 'datetime',
	            title: {
	                enabled: true,
	                text: '".$xaxis."'
	            },
	            //startOnTick: true,
	            //endOnTick: true,
	            min: 25200000,
				max: 64800000,
				showLastLabel: true
	        },
	        yAxis: {
	            title: {
	                text: '".$yaxis."'
	            }
	        },
	        legend: {
				enabled: false,
	            layout: 'vertical',
	            align: 'left',
	            verticalAlign: 'top',
	            x: 100,
	            y: 70,
	            floating: true,
	            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
	            borderWidth: 1
	        },
	        plotOptions: {
	            scatter: {
	                marker: {
	                    radius: 4,
	                    states: {
	                        hover: {
	                            enabled: true,
	                            lineColor: 'rgb(100,100,100)'
	                        }
	                    }
	                },
	                states: {
	                    hover: {
	                        marker: {
	                            enabled: false
	                        }
	                    }
	                },
	                tooltip: {
	                    headerFormat: '<b>{series.name}</b><br>',
	                    pointFormat: 'Time {point.x:%H:%M}, Px {point.y:.4f}'
	                }
	            }
	        },
	        series: [
						{
				            name: 'LiveBids',
				            color: 'rgba(255, 83, 83, .9)',
				            data: ".$livebids.",
							marker: {
								radius: 7,
								symbol: 'circle'}
								
						},
					 	{
				            name: 'LiveAsks',
				            color: 'rgba(83, 255, 83, .9)',
				            data: ".$liveasks.",
							marker: {
								radius: 7,
								symbol: 'circle'}
		        		},
						{
				            name: 'IndicativeBids',
				            color: 'rgba(255, 83, 83, .4)',
				            data: ".$indicativebids.",
							marker: {
								radius: 4,
								symbol: 'circle'}
						},
					 	{
				            name: 'IndicativeAsks',
				            color: 'rgba(83, 255, 83, .4)',
				            data: ".$indicativeasks.",
							marker: {
								radius: 4,
								symbol: 'circle'}
		        		}
					]
	    });
	});	
	";
	return $ret;	
}

function embx_datescattergraph($containerid, $data, $xcol, $ycol, $xaxis, $yaxis, $title, $subtitle, $clickfunction = 'pointclick', $valdef = 'ISINs'){
	/*
		It expects a data array where each item of the array is of the form:
			item[$xcol] and item[$ycol]
			item[$xcol] ->  Date string of the form  '2016-05-02'
			item[$ycol] ->  Number
		$xaxis, $yaxis, $title, $subtitle are related to the labelling.
	*/
	$ret = "
	$(function () {
    $('#".$containerid."').highcharts({
        chart: {
            type: 'scatter',
            zoomType: 'xy'
        },
        title: {
            text: '".$title."'
        },
        subtitle: {
            text: '".$subtitle."'
        },
        xAxis: {
			type: 'datetime',
            title: {
                enabled: true,
                text: '".$xaxis."'
            },
            startOnTick: true,
            endOnTick: true,
            showLastLabel: true
        },
        yAxis: {
            title: {
               text: '".$yaxis."'
            }
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 100,
            y: 70,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
            borderWidth: 1
        },
        plotOptions: {
            scatter: {
                marker: {
                    radius: 5,
                    states: {
                        hover: {
                            enabled: true,
                            lineColor: 'rgb(100,100,100)'
                        }
                    }
                },
                states: {
                    hover: {
                        marker: {
                            enabled: false
                        }
                    }
                },
		        tooltip: {
					xDateFormat: '%e %b %y',
					headerFormat: '{point.key}',
					pointFormat: '<br><b>{point.y}</b> ".$valdef."'
		        },
				point: { 
					events:{
						click: function(){
							".$clickfunction."(this.x);
						}
					}
				}
            }
        },
        series: [{
            name: '".$yaxis."',
            color: 'rgba(223, 83, 83, .5)',
            data: ".embx_dateseriestostring($data,$xcol,$ycol)."
        }]
    });
	});
	";
return $ret;
}

function embx_linegraph($containerid, $data, $xcol, $ycol, $xaxis, $yaxis, $title, $subtitle, $clickfunction, $tooltipformat , $label = "{point.y:.0f}"){
	if ($label == ""){
		$labelenabled = 'false';
	} else {
		$labelenabled = 'true';
	}			
	$ret = "
	$(function () {
	    $('#" . $containerid . "').highcharts({
	        chart: {
	            type: 'line'
	        },
	        title: {
	            text: '" . $title . "'
	        },
	        subtitle: {
	            text: '" . $subtitle . "'
	        },
	        xAxis: {
	            type: 'category',
				categories: [ " ;
				
				$dum = 0;
				foreach ($data as $item){
					if ($dum > 0){ $ret = $ret .  ",";}
					$ret = $ret . "'" . $item[$xcol] . "'";
					$dum = $dum +1;
				}	
					
				$ret = $ret . " ],

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
	                text: '" . $yaxis . "'
	            }
	        },
	        plotOptions: {
	            series: {
	                cursor: 'pointer',
	                point: {
	                    events: {
	                        click: function () { ";
							$ret = $ret . $clickfunction . "
	                        }
	                    }
	                }
	            }
	        },
	        legend: {
	            enabled: false
	        },
	        tooltip: {
	            pointFormat: '" . $tooltipformat . "'
	        },
	        series: [{
	            name: 'TradeData',
	            data: [";
				$chartdata = "";
				foreach ($data as $item){
					$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol] . "],";
				}
				$chartdata = substr($chartdata,0,strlen($chartdata)-1);
				$ret = $ret .  $chartdata;
				$ret = $ret . "
	            ],
	            dataLabels: {
	                enabled: ".$labelenabled.",
	                rotation: 0,
	                color: '#444444',
	                align: 'right',
	                format: '".$label."', // one decimal
	                y: 10, // 10 pixels down from the top
	                style: {
	                    fontSize: '12px',
	                    fontFamily: 'Verdana, sans-serif',
						fontWeight: 'normal'
	                }
	            }
	        }]
	    });
	});
	";
	return $ret;
}

function embx_doublelinegraph($containerid, $data, $xcol, $ycol1, $ycol2, $xaxis, $yaxis1, $yaxis2, $title, $subtitle, $clickfunction, $tooltip1format, $tooltip2format , $label1, $label2){
	if ($label1 == ""){
		$label1enabled = 'false';
	} else {
		$label1enabled = 'true';
	}
	if ($label2 == ""){
		$label2enabled = 'false';
	} else {
		$label2enabled = 'true';
	}
	
	$ret = "
	$(function () {
	    $('#" . $containerid . "').highcharts({
	        //chart: {
	        //    type: 'line'
	        //},
	        title: {
	            text: '" . $title . "'
	        },
	        subtitle: {
	            text: '" . $subtitle . "'
	        },
	        xAxis: {
	            type: 'category',
				categories: [ " ;
				
				$dum = 0;
				foreach ($data as $item){
					if ($dum > 0){ $ret = $ret .  ",";}
					$ret = $ret . "'" . $item[$xcol] . "'";
					$dum = $dum +1;
				}	
					
				$ret = $ret . " ],

	            labels: {
	                rotation: -45,
	                style: {
	                    fontSize: '13px',
	                    fontFamily: 'Verdana, sans-serif'
	                }
	            }
	        },
	        yAxis: [{
		            min: 0,
		            title: {
		                text: '" . $yaxis1 . "'
		            }
		        },
				{
		            min: 0,
		            title: {
		                text: '" . $yaxis2 . "'
		            },
					opposite: true
		        }],
	        plotOptions: {
	            series: {
	                cursor: 'pointer',
	                point: {
	                    events: {
	                        click: function () { ";
							$ret = $ret . $clickfunction . "
	                        }
	                    }
	                }
	            }
	        },
	        legend: {
	            layout: 'vertical',
	            align: 'left',
	            x: 80,
	            verticalAlign: 'top',
	            y: 80,
	            floating: true,
	            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
	        },
	        series: [{
	            name: '".$yaxis1."',
				type: 'line',
				color: '#dd9999',
	            data: [";
				$chartdata = "";
				foreach ($data as $item){
					//$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol1] . "],";
					$chartdata = $chartdata . " " . $item[$ycol1] . ",";
				}
				$chartdata = substr($chartdata,0,strlen($chartdata)-1);
				$ret = $ret .  $chartdata;
				$ret = $ret . "
	            ],
				tooltip: {
					pointFormat: '" . $tooltip1format . "'
					},
	            dataLabels: {
	                enabled: ".$label1enabled.",
	                rotation: 0,
	                color: '#444444',
	                align: 'right',
	                format: '".$label1."', // one decimal
	                y: 10, // 10 pixels down from the top
	                style: {
	                    fontSize: '12px',
	                    fontFamily: 'Verdana, sans-serif',
						fontWeight: 'normal',
						textShadow: 'none'
	                }
	            }
	        },
			{
		            name: '".$yaxis2."',

					yAxis: 1,
					type: 'line',
		            data: [";
					$chartdata = "";
					foreach ($data as $item){
						//$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol2] . "],";
						$chartdata = $chartdata . " " . $item[$ycol2] . ",";						
					}
					$chartdata = substr($chartdata,0,strlen($chartdata)-1);
					$ret = $ret .  $chartdata;
					$ret = $ret . "
		            ],

					tooltip: {
						pointFormat: '" . $tooltip2format . "'
						},
		            dataLabels: {
		                enabled: ".$label2enabled.",
		                rotation: 0,
		                color: '#444444',
		                align: 'right',
		                format: '".$label2."', // one decimal
		                y: 10, // 10 pixels down from the top
		                style: {
		                    fontSize: '12px',
		                    fontFamily: 'Verdana, sans-serif',
							textShadow: 'none',
							fontWeight: 'normal'
		                }
		            }
			}]
	    });
	});	
	";
	return $ret;
}

function embx_linecolumngraph($containerid, $data, $xcol, $ycol1, $ycol2, $xaxis, $yaxis1, $yaxis2, $title, $subtitle, $clickfunction, $tooltip1format, $tooltip2format , $label1, $label2){
	if ($label1 == ""){
		$label1enabled = 'false';
	} else {
		$label1enabled = 'true';
	}
	if ($label2 == ""){
		$label2enabled = 'false';
	} else {
		$label2enabled = 'true';
	}
	
	$ret = "
	$(function () {
	    $('#" . $containerid . "').highcharts({
	        //chart: {
	        //    type: 'line'
	        //},
	        title: {
	            text: '" . $title . "'
	        },
	        subtitle: {
	            text: '" . $subtitle . "'
	        },
	        xAxis: {
	            type: 'category',
				categories: [ " ;
				
				$dum = 0;
				foreach ($data as $item){
					if ($dum > 0){ $ret = $ret .  ",";}
					$ret = $ret . "'" . $item[$xcol] . "'";
					$dum = $dum +1;
				}	
					
				$ret = $ret . " ],

	            labels: {
	                rotation: -45,
	                style: {
	                    fontSize: '13px',
	                    fontFamily: 'Verdana, sans-serif'
	                }
	            }
	        },
	        yAxis: [{
		            min: 0,
		            title: {
		                text: '" . $yaxis1 . "'
		            }
		        },
				{
		            min: 0,
		            title: {
		                text: '" . $yaxis2 . "'
		            },
					opposite: true
		        }],
	        plotOptions: {
	            series: {
	                cursor: 'pointer',
	                point: {
	                    events: {
	                        click: function () { ";
							$ret = $ret . $clickfunction . "
	                        }
	                    }
	                }
	            }
	        },
	        legend: {
	            layout: 'vertical',
	            align: 'left',
	            x: 80,
	            verticalAlign: 'top',
	            y: 80,
	            floating: true,
	            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
	        },
	        series: [
					{
		            name: '".$yaxis2."',

					yAxis: 1,
					type: 'column',
					color: 'rgba(100,150,200,0.3)',
		            data: [";
					$chartdata = "";
					foreach ($data as $item){
						//$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol2] . "],";
						$chartdata = $chartdata . " " . $item[$ycol2] . ",";						
					}
					$chartdata = substr($chartdata,0,strlen($chartdata)-1);
					$ret = $ret .  $chartdata;
					$ret = $ret . "
		            ],

					tooltip: {
						pointFormat: '" . $tooltip2format . "'
						},
		            dataLabels: {
		                enabled: ".$label2enabled.",
		                rotation: 0,
		                color: '#444444',
		                align: 'right',
		                format: '".$label2."', // one decimal
		                y: 10, // 10 pixels down from the top
		                style: {
		                    fontSize: '12px',
		                    fontFamily: 'Verdana, sans-serif',
							textShadow: 'none',
							fontWeight: 'normal'
		                }
		            }
			},
			{
				            name: '".$yaxis1."',
							type: 'line',
							color: '#dd9999',
				            data: [";
							$chartdata = "";
							foreach ($data as $item){
								//$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol1] . "],";
								$chartdata = $chartdata . " " . $item[$ycol1] . ",";
							}
							$chartdata = substr($chartdata,0,strlen($chartdata)-1);
							$ret = $ret .  $chartdata;
							$ret = $ret . "
				            ],
							tooltip: {
								pointFormat: '" . $tooltip1format . "'
								},
				            dataLabels: {
				                enabled: ".$label1enabled.",
				                rotation: 0,
				                color: '#444444',
				                align: 'right',
				                format: '".$label1."', // one decimal
				                y: 10, // 10 pixels down from the top
				                style: {
				                    fontSize: '12px',
				                    fontFamily: 'Verdana, sans-serif',
									fontWeight: 'normal',
									textShadow: 'none'
				                }
				            }
				        }]
	    });
	});	
	";
	return $ret;
}


function embx_columngraph($containerid, $data, $xcol, $ycol, $xaxis, $yaxis, $title, $subtitle, $clickfunction, $tooltipformat , $label = "{point.y:.0f}" ) {
	if ($label == ""){
		$labelenabled = 'false';
	} else {
		$labelenabled = 'true';
	}
	$ret = "
	$(function () {
	    $('#" . $containerid . "').highcharts({
	        chart: {
	            type: 'column'
	        },
	        title: {
	            text: '" . $title . "'
	        },
	        subtitle: {
	            text: '" . $subtitle . "'
	        },
	        xAxis: {
	            type: 'category',
				categories: [ " ;
				
				$dum = 0;
				foreach ($data as $item){
					if ($dum > 0){ $ret = $ret .  ",";}
					$ret = $ret . "'" . $item[$xcol] . "'";
					$dum = $dum +1;
				}	
					
				$ret = $ret . " ],

	            labels: {
	                rotation: -45,
					color: '#444444',
	                style: {
	                    fontSize: '13px',
	                    fontFamily: 'Verdana, sans-serif',
						textShadow: 'none'
	                }
	            }
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: '" . $yaxis . "'
	            }
	        },
	        plotOptions: {
	            series: {
	                cursor: 'pointer',
	                point: {
	                    events: {
	                        click: function () { ";
							$ret = $ret . $clickfunction . "
	                        }
	                    }
	                }
	            }
	        },
	        legend: {
	            enabled: false
	        },
	        tooltip: {
	            pointFormat: '" . $tooltipformat . "'
	        },
	        series: [{
	            name: 'TradeData',
	            data: [";
				$chartdata = "";
				foreach ($data as $item){
					$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol] . "],";
				}
				$chartdata = substr($chartdata,0,strlen($chartdata)-1);
				$ret = $ret .  $chartdata;
				$ret = $ret . "
	            ],
	            dataLabels: {
	                enabled: ".$labelenabled.",
	                rotation: 0,
	                color: '#444444',
	                align: 'center',
	                format: '".$label."', // one decimal
	                y: 0, // 10 pixels down from the top
	                style: {
	                    fontSize: '12px',
	                    fontFamily: 'Verdana, sans-serif',
						fontWeight: 'normal',
						textShadow: 'none'
	                }
	            }
	        }]
	    });
	});	
	";
	return $ret;
}

function embx_doublecolumngraph($containerid, $data1, $data2, $xcol, $ycol1, $ycol2, $xaxis, $yaxis, $title, 
								$subtitle, $clickfunction, $tooltipformat , $label = "{point.y:.0f}" , $s1name = "Series1", $s2name = "Series2") {
	if ($label == ""){
		$labelenabled = 'false';
	} else {
		$labelenabled = 'true';
	}
	$ret = "
	$(function () {
	    $('#" . $containerid . "').highcharts({
	        chart: {
	            type: 'column'
	        },
	        title: {
	            text: '" . $title . "'
	        },
	        subtitle: {
	            text: '" . $subtitle . "'
	        },
	        xAxis: {
	            type: 'category',
				categories: [ " ;
				
				$dum = 0;
				foreach ($data1 as $item){
					if ($dum > 0){ $ret = $ret .  ",";}
					$ret = $ret . "'" . $item[$xcol] . "'";
					$dum = $dum +1;
				}	
					
				$ret = $ret . " ],

	            labels: {
	                rotation: -45,
					color: '#444444',
	                style: {
	                    fontSize: '13px',
	                    fontFamily: 'Verdana, sans-serif',
						textShadow: 'none'
	                }
	            }
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: '" . $yaxis . "'
	            }
	        },
	        plotOptions: {
	            series: {
	                cursor: 'pointer',
	                point: {
	                    events: {
	                        click: function () { ";
							$ret = $ret . $clickfunction . "
	                        }
	                    }
	                }
	            }
	        },
	        legend: {
	            enabled: true
	        },
	        tooltip: {
	            pointFormat: '" . $tooltipformat . "'
	        },
	        series: [
			{
	            name: '".$s1name."',
	            data: [";
				$chartdata = "";
				foreach ($data1 as $item){
					$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol1] . "],";
				}
				$chartdata = substr($chartdata,0,strlen($chartdata)-1);
				$ret = $ret .  $chartdata;
				$ret = $ret . "
	            ],
	            dataLabels: {
	                enabled: ".$labelenabled.",
	                rotation: 0,
	                color: '#444444',
	                align: 'center',
	                format: '".$label."', // one decimal
	                y: 0, // 10 pixels down from the top
	                style: {
	                    fontSize: '12px',
	                    fontFamily: 'Verdana, sans-serif',
						fontWeight: 'normal',
						textShadow: 'none'
	                }
	            }
	        },
			{
	            name: '".$s2name."',
	            data: [";
				$chartdata = "";
				foreach ($data2 as $item){
					$chartdata = $chartdata . "['" . $item[$xcol] .   "', " . $item[$ycol2] . "],";
				}
				$chartdata = substr($chartdata,0,strlen($chartdata)-1);
				$ret = $ret .  $chartdata;
				$ret = $ret . "
	            ],
	            dataLabels: {
	                enabled: ".$labelenabled.",
	                rotation: 0,
	                color: '#444444',
	                align: 'center',
	                format: '".$label."', // one decimal
	                y: 0, // 10 pixels down from the top
	                style: {
	                    fontSize: '12px',
	                    fontFamily: 'Verdana, sans-serif',
						fontWeight: 'normal',
						textShadow: 'none'
	                }
	            }
	        }
			
			]
	    });
	});	
	";
	return $ret;
}



function embx_add_date($givendate,$day=0,$mth=0,$yr=0) {
      $cd = strtotime($givendate);
      $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),date('i',$cd), date('s',$cd), date('m',$cd)+$mth,date('d',$cd)+$day, date('Y',$cd)+$yr));
      return $newdate;
}

function monthtodate($monthyear){
	date_default_timezone_set("UTC");
	$year = floor($monthyear/12);
	$month = $monthyear - $year*12;
	if ($month<10){
		$monthstr = "0".$month;
	} else {
		$monthstr = $month;
	}
	$date = strtotime($year."-".$monthstr."-01");
	return $date;
}

function embx_tradesfordayforisin($theisin,$thedate){
	date_default_timezone_set("UTC");
	$ret = "";
	$orders = embx_sql("select * from trades where isin = '" . $theisin . "' and date(tradetime) = '" . $thedate . "' order by tradetime");
	if ($orders){
		$ret = $ret .  "<h6>Trade Summary for ". $thedate . " and for ". $theisin . "</h6>";
		$ret = $ret .  "<table class='embx-table'>";
		if (count($orders)>0){
			foreach ($orders as $order) {
				$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
				if ($thedate != $prevdate){
					$ret = $ret .   "<tr><th colspan='7'>" . $thedate . "</th></tr>";
				}
				$prevdate = $thedate;
				$price =  number_format($order["price"],4);
				if ($order["buyer"] == $order["giver"]) {
					$ret = $ret .   "<tr><td><span class='label success'>B</span></td><td>" . $order["buyer"] . "</td>" .
						"<td><span class='label alert'>S</span></td><td>" . $order["seller"] . "</td>" .
							"<td class='liveprice'>" . $price . "</td>" .
							"<td style='text-align: right;'>" . $order["currency"] . " " . number_format($order["size"],0) . "</td>" .
							"<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\"" . substr($order["tradetime"],0,10) . "\")'>" . $order["isin"] . "</a></td></tr>" ;
				} else {
					$ret = $ret .  "<tr><td><span class='label alert'>S</span></td><td>" . $order["seller"] . "</td>" .
						"<td><span class='label success'>B</span></td><td>" . $order["buyer"] . "</td>" .
							"<td class='liveprice'>" . $price. "</td>" .
							"<td style='text-align: right;'>" . $order["currency"] . " " . number_format($order["size"],0) . "</td>" .
							"<td><a href='javascript:embx_getbonddetailforday(\"" . $order["isin"] . "\",\"" . substr($order["tradetime"],0,10)  . "\")'>" . $order["isin"] . "</a></td></tr>" ;
				}
			}
			$ret = $ret .  "</table>";
		}
	}
	return $ret;
}

function embx_pricehistory($isin, $date){
	// 1. create a timestamp array for that date
	// 2. poll the database for bids and offers for live prices on each timestamp array
}

function embx_dateseriestostring($data,$xcol,$ycol){
	date_default_timezone_set("UTC");
	if ($data){
		$i = 0;
		foreach ($data as $item){
			$datex = strtotime($item[$xcol])*1000;
			$i=$i+1;
			$ret .= "[".$datex.",".$item[$ycol]."],";
		}
		$ret = "[".substr($ret,0,strlen($ret)-1)."]";
	} else {
		$ret = "";
	}
	return $ret;
}

function embx_marketsnapshot($isin,$tradingday, $minutes, $tablename = ""){
	$sz_livebid = 0;
	$sz_liveask = 0;
	$sz_indicativebid = 0;
	$sz_indicativeask = 0;
	$px_livebid = 0;
	$px_liveask = 0;
	$px_indicativebid = 0;
	$px_indicativeask = 0;

	if ($tablename == ""){
		$orderstable = "orders";
	} else {
		$orderstable = $tablename;
	}

	date_default_timezone_set("UTC");
	$starttradingday = "'" . $tradingday . " 00:00:00'";
	
	$sst = strtotime($tradingday) + $minutes * 60;
	$snapshottime = date("Y-m-d H:i:s",$sst);

	$sql = "select price, size, username from ".$orderstable." where side = 'BUY'
			and ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime . 
		"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
		" and ordertype = 'Live' and isin = '" . $isin . "' order by price desc";
	$bidlive = embx_sql($sql);

	$sql = "select price, size, username from ".$orderstable." where side = 'SELL'  
			and ordertime > " . $starttradingday . "   and ordertime <= '" . $snapshottime . 
		"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
		" and ordertype = 'Live' and isin = '" . $isin . "'  order by price asc";
	$asklive = embx_sql($sql);

	$sql = "select price, size, username from ".$orderstable." where side = 'BUY' 
			and ordertime > " . $starttradingday . "  and ordertime <= '" . $snapshottime . 
		"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
		"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price desc";
	$bidindicative = embx_sql($sql);
	
	$sql = "select price, size, username from ".$orderstable." where side = 'SELL' and  
			ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime .  
		"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
		"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price asc";
	$askindicative = embx_sql($sql);

	$livebids = count($bidlive);
	$liveasks = count($asklive);
	$indicativebids = count($bidindicative);
	$indicativeasks = count($askindicative);

	$ret["livebids"] = $bidlive;
	$ret["liveasks"] = $asklive;
	$ret["indicativebids"] = $bidindicative;
	$ret["indicativeasks"] = $askindicative;
	
	$ret["minute"] = $minutes;
	$ret["tradingday"] = $tradingday;
	if ($livebids) {
		$px_livebid = $bidlive[0]["price"];
		foreach ($bidlive as $livebid){
			$sz_livebid = $sz_livebid + $livebid["size"];
		}
	}
	if ($liveasks) {
		$px_liveask = $asklive[0]["price"];
		foreach ($asklive as $liveask){
			$sz_liveask = $sz_liveask + $liveask["size"];
		}
	}
	if ($indicativebids) {
		$px_indicativebid = $bidindicative[0]["price"];
		foreach ($bidindicative as $indicativebid){
			$sz_indicativebid = $sz_indicativebid + $indicativebid["size"];
		}
	}
	if ($indicativeasks) {
		$px_indicativeask = $askindicative[0]["price"];
		foreach ($askindicative as $indicativeask){
			$sz_indicativeask = $sz_indicativeask + $indicativeask["size"];
		}
	}
	$ret["sz_livebid"] = $sz_livebid;
	$ret["sz_liveask"] = $sz_liveask;
	$ret["sz_indicativebid"] = $sz_indicativebid;
	$ret["sz_indicativeask"] = $sz_indicativeask;
	$ret["px_livebid"] = $px_livebid;
	$ret["px_liveask"] = $px_liveask;
	$ret["px_indicativebid"] = $px_indicativebid;
	$ret["px_indicativeask"] = $px_indicativeask;
	
	return $ret;
}

function embx_markethistory($isin, $tradingday, $startminute = 420, $endminute = 1080, $interval = 15){
	$minute = $startminute;
	$itemp = 0;

	$sz_livebid = 0;
	$sz_liveask = 0;
	$sz_indicativebid = 0;
	$sz_indicativeask = 0;

	$px_last_live_bid = 0;
	$px_last_live_ask = 0;
	$px_last_indicative_bid = 0;
	$px_last_indicative_ask = 0;

	$ts_last_live_bid = 0;
	$ts_last_live_ask = 0;
	$ts_last_indicative_bid = 0;
	$ts_last_indicative_ask = 0;

	$temptablename = $isin.uniqid();
	embx_sql("create table ".$temptablename." select * from orders where date(ordertime) = '".$tradingday."' and isin = '".$isin."'");
	
	do {	$mkt = embx_marketsnapshot($isin,$tradingday, $minute, $temptablename);
			$sz_livebid = max($mkt["sz_livebid"],$sz_livebid);
			$sz_liveask = max($mkt["sz_liveask"],$sz_liveask);
			$sz_indicativebid = max($mkt["sz_indicativebid"],$sz_indicativebid);
			$sz_indicativeask = max($mkt["sz_indicativeask"],$sz_indicativeask);

			if($mkt["px_livebid"]){
				$px_last_live_bid = $mkt["px_livebid"];
				$ts_last_live_bid = $minute;
			};
			if($mkt["px_liveask"]){
				$px_last_live_ask = $mkt["px_liveask"];
				$ts_last_live_ask = $minute;
			};
			if($mkt["px_indicativebid"]){
				$px_last_indicative_bid = $mkt["px_indicativebid"];
				$ts_last_indicative_bid = $minute;
			};
			if($mkt["px_indicativeask"]){
				$px_last_indicative_ask = $mkt["px_indicativeask"];
				$ts_last_indicative_ask = $minute;
			};

			$ret["px_liveask"][$itemp] = $mkt["px_liveask"];
			$ret["px_indicativebid"][$itemp] = $mkt["px_indicativebid"];
			$ret["px_indicativeask"][$itemp] = $mkt["px_indicativeask"];

		
			$ret["minute"][$itemp] = $minute;
			$ret["hour"][$itemp] = $minute * 60000;	
			$ret["px_livebid"][$itemp] = number_format($mkt["px_livebid"],4);
			$ret["px_liveask"][$itemp] = number_format($mkt["px_liveask"],4);
			$ret["px_indicativebid"][$itemp] = number_format($mkt["px_indicativebid"],4);
			$ret["px_indicativeask"][$itemp] = number_format($mkt["px_indicativeask"],4);
			$ret["category"][$itemp] = floor($minute/60) . ":" . ($minute - floor($minute/60));
			$itemp = $itemp+1;
			$minute = $minute + $interval;
	} while ($minute <= $endminute);
	
	$ret["max_sz_livebid"] = $sz_livebid;
	$ret["max_sz_liveask"] = $sz_liveask;
	$ret["max_sz_indicativebid"] = $sz_indicativebid;
	$ret["max_sz_indicativeask"] = $sz_indicativeask;

	$ret["px_last_live_bid"] = number_format($px_last_live_bid,4) ;
	$ret["px_last_live_ask"] = number_format($px_last_live_ask,4) ;
	$ret["px_last_indicative_bid"] = number_format($px_last_indicative_bid,4) ;
	$ret["px_last_indicative_ask"] = number_format($px_last_indicative_ask,4) ;

	$ret["ts_last_live_bid"] = $ts_last_live_bid ;
	$ret["ts_last_live_ask"] = $ts_last_live_ask ;
	$ret["ts_last_indicative_bid"] = $ts_last_indicative_bid ;
	$ret["ts_last_indicative_ask"] = $ts_last_indicative_ask ;

	embx_sql("drop table ".$temptablename);

	return $ret;
}

function embx_preparedata($x, $y){
	/*
		This function takes two arrays $x and $y and so long as $y[i] is not empty or not 0.0000 then
		it creates a datapoint. Resulting string is:
		[  [$x[0],$y[0]] , [$x[1],$y[1]] , [$x[2],$y[2]] .... , [$x[n],$y[n]]  ]
	*/
	$numpoints = count($x);
	for ($j = 0; $j < $numpoints; $j++ ){
		if($y[$j] && $y[$j]!="0.0000"){
			$ret = $ret . ",[" . $x[$j] . "," . $y[$j] ."]";
		}
	}
	$ret = substr($ret, 1, strlen($ret)-1);
	$ret = "[" . $ret  ."]";
	return $ret;
}
function embx_preparecategories($x){
	/*
		This function takes an array $x and creates a string of the following format:
		[ $x[0],$x[1],...,$x[n] ]
	*/
	$numpoints = count($x);
	for ($j = 0; $j < $numpoints; $j++ ){
			$ret = $ret .  "," . $x[$j] ;
	}
	$ret = substr($ret, 1, strlen($ret)-1);
	$ret = "[" . $ret  ."]";
	return $ret;
}

function embx_getbondname_fromisin($isin,$bonds){
	$thebondname = "Undefined";
	foreach($bonds as $bond){
		if ($isin == $bond["isin"]){
			$thebondname = $bond["bondname"];
		}
	}
	return $thebondname;
}

function embx_getendofday($isin, $tradingday){
	$res = embx_sql("select * from endofday where isin='".$isin."' and tradingday='".$tradingday."'  ");
	if ($res) {
		$ret["max_sz_livebid"] = $res[0]["max_sz_livebid"];
		$ret["max_sz_liveask"] = $res[0]["max_sz_liveask"];
		$ret["max_sz_indicativebid"] = $res[0]["max_sz_indicativebid"];
		$ret["max_sz_indicativeask"] = $res[0]["max_sz_indicativeask"];
		$ret["px_last_live_bid"] = $res[0]["px_last_live_bid"];
		$ret["px_last_live_ask"] = $res[0]["px_last_live_ask"];
		$ret["px_last_indicative_bid"] = $res[0]["px_last_indicative_bid"];
		$ret["px_last_indicative_ask"] = $res[0]["px_last_indicative_ask"];
		$ret["ts_last_live_bid"] = $res[0]["ts_last_live_bid"];
		$ret["ts_last_live_ask"] = $res[0]["ts_last_live_ask"];
		$ret["ts_last_indicative_bid"] = $res[0]["ts_last_indicative_bid"];
		$ret["ts_last_indicative_ask"] = $res[0]["ts_last_indicative_ask"];
		return $ret;
	} else { return 0;}
}

function embx_tradesummaryfortradingday($tradingday){
		date_default_timezone_set("UTC");

		$thedate = $tradingday;
		$orders = embx_sql("select * from trades where date(tradetime) = '" . $thedate . "' and isin != '".TESTISIN."'  order by tradetime");
		if ($orders){
			$ret .= "<h6>Trade Summary for ". $thedate . "</h6>";
			$ret .= "<table class='embx-table'>";
			if (count($orders)>0){
				foreach ($orders as $order) {
					$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
					if ($thedate != $prevdate){
						//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
						$ret .= "<tr><th colspan='7'>" . $thedate . "</th></tr>";
					}
					$prevdate = $thedate;
						$price =  number_format($order["price"],4);
				
						if ($order["buyer"] == $order["giver"]) {
							$ret .= 	"<tr>
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
							$ret .= 	"<tr>
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
				$ret .= "</table>";
			}
		}

		return $ret ;
}

function embx_tradesummaryfortradingmonthbkp($tradingday){
		date_default_timezone_set("UTC");

		$thedate = $tradingday;
		$orders = embx_sql("select * from trades 
			where (year(tradetime)*12+month(tradetime)) = (year('" . $thedate . "')*12+month('" . $thedate . "'))
			and isin != '".TESTISIN."'   order by tradetime");
		if ($orders){
			$ret .= "<h6>Trade Summary for ". date("M Y",strtotime($thedate)) . "</h6>";
			$ret .= "<table class='embx-table'>";
			if (count($orders)>0){
				foreach ($orders as $order) {
					$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
					if ($thedate != $prevdate){
						//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
						$ret .= "<tr><th colspan='7'>" . $thedate . "</th></tr>";
					}
					$prevdate = $thedate;
						$price =  number_format($order["price"],4);
				
						if ($order["buyer"] == $order["giver"]) {
							$ret .= 	"<tr>
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
							$ret .= 	"<tr>
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
				$ret .= "</table>";
			}
		}

		return $ret ;
}

function embx_tradingpartiessummaryfortradingmonth($fromdate,$todate = ""){
		date_default_timezone_set("UTC");

		//$thedate = $tradingday;
		
		if ($todate == ""){
			$orders = embx_sql("select * from trades 
				where (year(tradetime)*12+month(tradetime)) = (year('" . $fromdate . "')*12+month('" . $fromdate . "'))
				and isin != '".TESTISIN."' order by tradetime");
		} else {
			$orders = embx_sql("select * from trades 
				where (year(tradetime)*12+month(tradetime)) >= (year('" . $fromdate . "')*12+month('" . $fromdate . "')) and
				(year(tradetime)*12+month(tradetime)) <= (year('" . $todate . "')*12+month('" . $todate . "')) 
				 and isin != '".TESTISIN."' order by tradetime");
		}
		//$orders = embx_sql("select * from trades 
		//	where (year(tradetime)*12+month(tradetime)) = (year('" . $thedate . "')*12+month('" . $thedate . "'))
		//	 and isin != '".TESTISIN."'  order by tradetime");

		$parties = [];
		$users = [];	 
	    if($orders){
			$iuser = 0;
			foreach ($orders as $order){
				array_push($parties, substr($order["buyer"] ,0 , strlen($order["buyer"]) -3   ));
				array_push($parties, substr($order["seller"] ,0 , strlen($order["seller"])-3   ));
				
				$users[substr($order["buyer"] ,0 , strlen($order["buyer"])-3   )][$iuser] = $order["buyer"];
				$users[substr($order["seller"] ,0 , strlen($order["seller"])-3   )][$iuser+1] = $order["seller"];
				$iuser += 2;
				//$ret .= $order["buyer"]." ".$order["seller"]."<br/>";
			}
			$parties = array_unique($parties);
			//$ret .= print_r($users);
			if ($todate == ""){
				$ret .= "<h6>Involved Parties in  ". date("M Y",strtotime($fromdate)) . "</h6>";
			} else {
				$ret .= "<h6>Involved Parties betweeen  ". date("M Y",strtotime($fromdate)) . " and ". date("M Y",strtotime($todate)) . "</h6>";
			}
			//$ret .= "<h6>Involved Parties in  ". date("M Y",strtotime($fromdate)) . "</h6>";
			$ret .= "<table class='embx-table'>";
			$ret .= "<thead>
						<tr>
							<th>Cpty Code</th>
							<th style='text-align: right;'>Logins Involved</th>
							<th style='text-align: right;'>Vol USD</th>
							<th style='text-align: right;'>Trade Count</th>
							<th style='text-align: right;'>Avg Size USD</th>
						</tr>
					</thead>";
			$ret .="<tbody>";
			
			if ($todate == ""){
				$todate = $fromdate;
			}
			
			foreach ($parties as $party){
				$user = array_unique($users[$party]);
				$numusers = count($user);
				$sql = "	select 
								(year(tradetime)*12+month(tradetime)) as trademonth, 
								sum(floor(trades.size/currencies.rate/10000)/100) as tradevolume
							from trades,  currencies 
							where 
								currencies.currency = trades.currency and 
								( left(trades.buyer,length(trades.buyer)-3) = '".$party."'     or    
								  left(trades.seller,length(trades.seller)-3) = '".$party."'    ) and
								 (year('" . $fromdate . "')*12+month('" . $fromdate . "')) >= (year(tradetime)*12+month(tradetime))  and
								 (year('" . $todate . "')*12+month('" . $todate . "')) <= (year(tradetime)*12+month(tradetime))  and
								trades.isin !='".TESTISIN."' 
							group by trademonth asc
							";
				$res = embx_sql($sql);
				
				$sql2 = "select count(isin) as numtrades from trades where 
					( left(trades.buyer,length(trades.buyer)-3) = '".$party."'     or    
					  left(trades.seller,length(trades.seller)-3) = '".$party."'    ) and
					  (year('" . $fromdate . "')*12+month('" . $fromdate . "')) = (year(tradetime)*12+month(tradetime)) and 
					  (year('" . $todate . "')*12+month('" . $todate . "')) <= (year(tradetime)*12+month(tradetime))  and
					  trades.isin !='".TESTISIN."' 
					  	";
				
				$res2 = embx_sql($sql2);
				
				$ret .= "<tr>
							<td>".$party."</td>
							<td style='text-align: right;'>".number_format($numusers,0)."</td>
							<td style='text-align: right;'>".number_format($res[0]["tradevolume"],2)."</td>
							<td style='text-align: right;'>".number_format($res2[0]["numtrades"],0)."</td>
							<td style='text-align: right;'>".number_format($res[0]["tradevolume"]/$res2[0]["numtrades"],2)."</td>
						</tr>";
			}
			
			$ret .= "</tbody></table>";
		}
		
			
	 
			 
		if ($orders){

		} else {
				$ret .= "<h6>Trade Summary for ". date("M Y",strtotime($thedate)) . "</h6>";
				$ret .= "<p>No trades found in this period</p>";
		}

		return $ret ;
}


function embx_tradesummaryfortradingmonth($tradingday,$todate = ""){
		date_default_timezone_set("UTC");

		$thedate = $tradingday;
		
		if ($todate == ""){
			$orders = embx_sql("select * from trades 
				where (year(tradetime)*12+month(tradetime)) = (year('" . $thedate . "')*12+month('" . $thedate . "'))
				and isin != '".TESTISIN."' order by tradetime");
		} else {
			$orders = embx_sql("select * from trades 
				where (year(tradetime)*12+month(tradetime)) >= (year('" . $thedate . "')*12+month('" . $thedate . "')) and
				(year(tradetime)*12+month(tradetime)) <= (year('" . $todate . "')*12+month('" . $todate . "')) 
				 and isin != '".TESTISIN."' order by tradetime");
		}
		$orders = embx_sql("select * from trades 
			where (year(tradetime)*12+month(tradetime)) = (year('" . $thedate . "')*12+month('" . $thedate . "'))
			 and isin != '".TESTISIN."'  order by tradetime");
		if ($orders){
			$ret .= "<h6>Trade Summary for ". date("M Y",strtotime($thedate)) . "</h6>";
			$ret .= "<table class='embx-table'>";
			if (count($orders)>0){
				foreach ($orders as $order) {
					$thedate =  date_format(date_create($order["tradetime"]),"j F Y" );
					if ($thedate != $prevdate){
						//echo "</ul><div class='alert-box'>" . $thedate . "</div><ul>";
						$ret .= "<tr><th colspan='7'>" . $thedate . "</th></tr>";
					}
					$prevdate = $thedate;
						$price =  number_format($order["price"],4);
				
						if ($order["buyer"] == $order["giver"]) {
							$ret .= 	"<tr>
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
							$ret .= 	"<tr>
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
				$ret .= "</table>";
			}
		} else {
				$ret .= "<h6>Trade Summary for ". date("M Y",strtotime($thedate)) . "</h6>";
				$ret .= "<p>No trades found in this period</p>";
		}

		return $ret ;
}



	// embx_recordsnapshot("TRADE",$check[0]["tradeid"],$isin, $time_1);
function embx_recordsnapshot($activity ,$activityid ,$isin, $time_1){
	$snapshottime = $time_1;
	
	$temptablename = "orderstemp";
	$sql = "select ordertime, price, size, username, side, ordertype from ". 
		$temptablename." where ordertime > date('" . $snapshottime . 
			" 00:00:00') and  ordertime <= '" . $snapshottime . 
		"' and ( endtime >= '" . $snapshottime . "' or isnull(endtime) ) " . 
		" and isin = '" . $isin . "'  and timeinforce = 'GTC' and sysauto = 0 order by logid asc";
	
	$ords = embx_sql($sql);
	if (count($ords)){
		foreach ($ords as $ord){
			$sql = "insert into snapshot 
				(activity, activityid, user, price, size, ordertype, actiontime, direction) 
			    values (	'".	$activity ."',
							".	$activityid .",
							'".	$ord["username"] ."',
							".	$ord["price"] .",
							".	$ord["size"].",
							'".	$ord["ordertype"]."',
							'".	$snapshottime ."',
							'".	$ord["side"]."'  )";
			$res = embx_sql($sql);
		}
	}
}

function embx_marketstatus($tradingday,$snapshottimeofday,$isin){
	date_default_timezone_set("UTC");
	$ret = "";
	//$tradingday = ($_GET["tradingday"]);
	$starttradingday = "'" . $tradingday . " 00:00:00'";
	$snapshottime = $tradingday . " " . $snapshottimeofday;
	$temptablename = $isin.uniqid();
	embx_sql("create table ".$temptablename.
		" select * from orders where date(ordertime) = '".$tradingday."' and isin = '".$isin."'");
	
	
	//$minutes = $_GET["minutes"];
	
		//$sst = strtotime($tradingday) + $minutes * 60;
		//$snapshottime = date("Y-m-d H:i:s",$sst);
		/* if ($minutes == ""){
			$snapshottime = $tradingday . " " . $_GET["snapshottime"];
		}
			*/
		
		//echo $snapshottime;

		//$isin = $_GET["isin"];

		$sql = "select ordertime, price, size, username from ".$temptablename." where side = 'BUY'
				and ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime . 
			"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
			" and ordertype = 'Live' and isin = '" . $isin . "' order by price desc";
		$bidlive = embx_sql($sql);

		$sql = "select ordertime, price, size, username from ".$temptablename." where side = 'SELL'  
				and ordertime > " . $starttradingday . "   and ordertime <= '" . $snapshottime . 
			"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
			" and ordertype = 'Live' and isin = '" . $isin . "'  order by price asc";
		$asklive = embx_sql($sql);

		$sql = "select ordertime, price, size, username from ".$temptablename." where side = 'BUY' 
				and ordertime > " . $starttradingday . "  and ordertime <= '" . $snapshottime . 
			"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
			"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price desc";
		$bidindicative = embx_sql($sql);
		
		$sql = "select ordertime, price, size, username from ".$temptablename." where side = 'SELL' and  
				ordertime > " . $starttradingday . " and  ordertime <= '" . $snapshottime .  
			"' and ( endtime > '" . $snapshottime . "' or isnull(endtime) ) " . 
			"  and ordertype = 'Indicative' and isin = '" . $isin . "'  order by price asc";
		$askindicative = embx_sql($sql);

		$ret .= "<h5>Market at " . $snapshottime . " "  .    "</h5>";
		$livebids = count($bidlive);
		$liveasks = count($asklive);
		$indicativebids = count($bidindicative);
		$indicativeasks = count($askindicative);
		$ret .= "
		<table>
			<thead>
				<tr>
					<th>Buyer</th>
					<th>Time</th>
					<th>Bid Amt</th>
					<th>Bid Price</th>
					<th>Ask Price</th>
					<th>Ask Amt</th>
					<th>Time</th>
					<th>Seller</th>
				</tr>
			</thead>
			<tbody>
		";
		
		if ($livebids || $liveasks) {
			
			if ($livebids >= $liveasks) {
				$i=0;
				for($j=0; $j < $livebids; $j+=1){
					if ($i< $liveasks){
						$ret .= "
							<tr><td>".$bidlive[$j]["username"]."</td>
								<td>".substr($bidlive[$j]["ordertime"],11,5)."</td>
								<td>".number_format($bidlive[$j]["size"],0)."</td>
								<td><span class='label'>".number_format($bidlive[$j]["price"],4)."</span></td>
								<td><span class='label'>".number_format($asklive[$j]["price"],4)."</td>
								<td>".number_format($asklive[$j]["size"],0)."</td>
								<td>".substr($asklive[$j]["ordertime"],11,5)."</td>
								<td>".$asklive[$j]["username"]."</td>
							</tr>
						";
					} else {
						$ret .= "
							<tr><td>".$bidlive[$j]["username"]."</td>
								<td>".substr($bidlive[$j]["ordertime"],11,5)."</td>
								<td>".number_format($bidlive[$j]["size"],0)."</td>
								<td><span class='label'>".number_format($bidlive[$j]["price"],4)."</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>";
						
					}
				$i = $i+1;	
				}
			} else {
				$i=0;
				for($j=0; $j < $liveasks; $j+=1){
					if ($i< $livebids){
						$ret .="
							<tr>
								<td>".$bidlive[$j]["username"]."</td>
								<td>".substr($bidlive[$j]["ordertime"],11,5)."</td>
								<td>".number_format($bidlive[$j]["size"],0)."</td>
								<td><span class='label'>".number_format($bidlive[$j]["price"],4)."</td>
								<td><span class='label'>".number_format($asklive[$j]["price"],4)."</td>
								<td>".number_format($asklive[$j]["size"],0)."</td>
								<td>".substr($asklive[$j]["ordertime"],11,5)."</td>
								<td>".$asklive[$j]["username"]."</td>
							</tr>
						";
					} else {
						$ret .= "
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td><span class='label'>".number_format($asklive[$j]["price"],4)."</td>
								<td>".number_format($asklive[$j]["size"],0)."</td>
								<td>".substr($asklive[$j]["ordertime"],11,5)."</td>
								<td>".$asklive[$j]["username"]."</td>
							</tr>
						";
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
						$ret .="
							<tr><td>".$bidindicative[$j]["username"]."</td>
								<td>".substr($bidindicative[$j]["ordertime"],11,5)."</td>
								<td class='size'>".number_format($bidindicative[$j]["size"],0)."</td>
								<td><span class='label secondary'>".number_format($bidindicative[$j]["price"],4)."</span></td>
								<td><span class='label secondary'>".number_format($askindicative[$j]["price"],4)."</td>
								<td class='size'>".number_format($askindicative[$j]["size"],0)."</td>
								<td>".substr($askindicative[$j]["ordertime"],11,5)."</td>
								<td>".$askindicative[$j]["username"]."</td>
							</tr>
									";
					} else {
						$ret .= "
							<tr><td>".$bidindicative[$j]["username"]."</td>
						<td>".substr($bidindicative[$j]["ordertime"],11,5)."</td>
								<td class='size'>".number_format($bidindicative[$j]["size"],0)."</td>
								<td><span class='label secondary'>".number_format($bidindicative[$j]["price"],4)."</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						";
					}
				$i = $i+1;	
				}
			} else {
				$i=0;
				for($j=0; $j < $indicativeasks; $j+=1){
					if ($i < $indicativebids){
						$ret .= "
							<tr>
								<td>".$bidindicative[$j]["username"]."</td>
								<td>".substr($bidliveindicative[$j]["ordertime"],11,5)."</td>
								<td  class='size'>".number_format($bidindicative[$j]["size"],0)."</td>
								<td><span class='label secondary'>".number_format($bidindicative[$j]["price"],4)."</td>
								<td><span class='label secondary'>".number_format($askindicative[$j]["price"],4)."</td>
								<td class='size'>".number_format($askindicative[$j]["size"],0)."</td>
								<td>".substr($askindicative[$j]["ordertime"],11,5)."</td>
								<td>".$askindicative[$j]["username"]."</td>
							</tr>
						";
					} else {
						$ret .= "
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td><span class='label secondary'>".number_format($askindicative[$j]["price"],4)."</td>
								<td class='size'>".number_format($askindicative[$j]["size"],0)."</td>
								<td>".substr($askindicative[$j]["ordertime"],11,5)."</td>
								<td>".$askindicative[$j]["username"]."</td>
							</tr>
						";
					}
				$i = $i+1;	
				}						
			}
		}
		$ret .= "</tbody></table>";
		embx_sql("drop table ".$temptablename);
		return $ret;
	}


	function embx_numinitiatedrfqs($fromdate = '', $todate = ''){
		if ($fromdate == '' && $todate == ''){
			$res = embx_sql("select  substr(user,1,4-(7-length(user))) as cpty, count(rfqid) as numrfqinit from rfq where action = 'rfq/initial' and 
				isin != '".TESTISIN."' and substr(user,1,4) != 'EMBX' group by cpty
				 order by numrfqinit desc");
		} else {
			$res = embx_sql("select  substr(user,1,4-(7-length(user))) as cpty, count(rfqid) as numrfqinit from rfq where action = 'rfq/initial' 
				and date(actiontime) <= ".$todate." and date(actiontime) >= ".$fromdate." and 
				isin != '".TESTISIN."' and substr(user,1,4) != 'EMBX' group by cpty order by numrfqinit desc");
		}
		if ($res){
			$ret .= "<table class='embx-table' style='width:100%;'><thead><tr><th style='width:50px;'>Cpty</th><th  style='width:100px; text-align: right;'>RFQ Inits</th><th style='width:50px;'></th><th>Cpty Response Stats</th></tr></thead><tbody>";
			foreach($res as $item){
				$respstat = embx_rfqresponserate($item["cpty"]);
				$ret .= "<tr><td>".$item["cpty"]."</td><td style=' text-align: right;'>".$item["numrfqinit"]."</td>
					<td><i style='color: blue;' class='fi-results stats' id='".$item["cpty"]."'></i></td>
					<td id='".$item["cpty"]."stats'>".$respstat."</td>
					</tr>";
			}
			$ret .= "</tbody></table>";
			return $ret;
		}
	}

	function embx_rfqresponserate($cpty){
		//embx_sql("select * from rfq where (action = 'rfq/initial' or action = 'rfq/change') and responders ")
		$res = embx_sql("select (numrfqresponses/numrfqrequests) as percentageresponse, numrfqresponses, numrfqrequests from 
		(select count(rfqid) as numrfqresponses from 
		      (select distinct rfq.rfqid 
		           from rfq, (SELECT * FROM `rfq` WHERE responders like '%".$cpty."%' and substr(user,1,4) != 'EMBX' and isin != '".TESTISIN."') as t1 
		           where t1.rfqid = rfq.rfqid and 
		              rfq.action = 'rfq/change' and 
					  				rfq.isin != '".TESTISIN."' and 
		              substr(rfq.user,1,4-(7-length(rfq.user))) = '".$cpty."') 
		       as t2) 
		 as t3,
		(select count(rfqid) as numrfqrequests from (select distinct rfqid from rfq where responders like '%".$cpty."%' and action='rfq/initial' and 
		 substr(user,1,4) != 'EMBX' and isin != 'XSTEST123456') as t4) as t5");
		if ($res){
			//return $res[0];
			if ($res[0]["percentageresponse"]>0.5){
				$spancolor = ' success';
			} else {
				$spancolor = ' ';
			}
			$ret = "<span class='label".$spancolor."' style='text-align: right; font-size: 12px; padding: 2px 5px; width: 60px;'>".number_format($res[0]["percentageresponse"]*100,2)."%</span> &nbsp;&nbsp;&nbsp;".$res[0]["numrfqresponses"]."/".$res[0]["numrfqrequests"]."";
			return $ret;
		}
	}
	function embx_monthlytradingparties($fromyearmonth, $toyearmonth){
		$res = array();
		for ($i = $fromyearmonth; $i <= $toyearmonth; $i++){
			$sellers = embx_sql("select distinct left(seller,length(seller)-3) as cpty 
								from trades where isin <> '".TESTISIN."' and  ".$i." = (year(tradetime)*12+month(tradetime))");
			$buyers = embx_sql("select distinct left(buyer,length(buyer)-3) as cpty 
								from trades where isin <> '".TESTISIN."' and  ".$i." = (year(tradetime)*12+month(tradetime))");
			$everyone = array();
			foreach ($sellers as $seller){
				array_push($everyone,$seller["cpty"]);
			}
			foreach ($buyers as $buyer){
				array_push($everyone,$buyer["cpty"]);
			}
			$everyone = array_unique($everyone);
			array_push($res,array("month" => $i, "cptycount" => count($everyone)));
			
		}
		return $res;
	}
	
	function embx_monthlyrfqparties($fromyearmonth, $toyearmonth){
		$res = array();
		for ($i = $fromyearmonth; $i <= $toyearmonth; $i++){
			$cptys = embx_sql("select distinct left(rfq.user,length(rfq.user)-3) as cpty 
								from rfq where  isin <> '".TESTISIN."' and  ".$i." = (year(actiontime)*12+month(actiontime)) and user <> ''");
			//$buyers = embx_sql("select distinct left(buyer,length(buyer)-3) as cpty 
			//					from trades where isin <> 'XSTEST123456' and  ".$i." = (year(tradetime)*12+month(tradetime))");
			$everyone = array();
			foreach ($cptys as $cpty){
				array_push($everyone,$cpty["cpty"]);
			}
			//foreach ($buyers as $buyer){
			//	array_push($everyone,$buyer["cpty"]);
			//}
			$everyone = array_unique($everyone);
			array_push($res,array("month" => $i, "cptycount" => count($everyone)));
			
		}
		return $res;
	}	
	


?>