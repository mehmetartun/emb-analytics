<?php
include("utils/embx_dbconn.php");
include("utils/embx_functions.php");
//include("utils/fpdf/fpdf.php");
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EMB End of Day</title>
	<link rel="shortcut icon" href="images/embx-a-favicon.ico">
	<link rel="icon" sizes="16x16 32x32 64x64" href="images/embx-a-favicon.ico">
	<link rel="icon" type="image/png" sizes="196x196" href="images/embx-a-favicon-192.png">
	<link rel="icon" type="image/png" sizes="160x160" href="images/embx-a-favicon-160.png">
	<link rel="icon" type="image/png" sizes="96x96" href="images/embx-a-favicon-96.png">
	<link rel="icon" type="image/png" sizes="64x64" href="images/embx-a-favicon-64.png">
	<link rel="icon" type="image/png" sizes="32x32" href="images/embx-a-favicon-32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="images/embx-a-favicon-16.png">
	<link rel="apple-touch-icon" href="images/embx-a-favicon-57.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/embx-a-favicon-114.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/embx-a-favicon-72.png">
	<link rel="apple-touch-icon" sizes="144x144" href="images/embx-a-favicon-144.png">
	<link rel="apple-touch-icon" sizes="60x60" href="images/embx-a-favicon-60.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/embx-a-favicon-120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="images/embx-a-favicon-76.png">
	<link rel="apple-touch-icon" sizes="152x152" href="images/embx-a-favicon-152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="images/embx-a-favicon-180.png">
	<meta name="msapplication-TileColor" content="#FFFFFF">
	<meta name="msapplication-TileImage" content="images/embx-a-favicon-144.png">
	<meta name="msapplication-config" content="images/browserconfig.xml">
    <link rel="stylesheet" href="stylesheets/app.css" />
    <link rel="stylesheet" href="images/foundation-icons.css" />	
	<link href='http://fonts.googleapis.com/css?family=Source+Code+Pro:200,300,400,500,600,700,900' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,600,700,900,200italic,300italic,400italic,600italic,700italic,900italic' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:200,300,400,600,700,900,200italic,300italic,400italic,600italic,700italic,900italic' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="stylesheets/jquery.datetimepicker.css"/>
	
    <script src="bower_components/modernizr/modernizr.js"></script>
	<style>
	.file-upload {
	  position: relative;
	  overflow: hidden;
	 }
	.file-upload input.file-input {
	  position: absolute;
	  top: 0;
	  right: 0;
	  margin: 0;
	  padding: 0;
	  font-size: 20px;
	  cursor: pointer;
	  opacity: 0;
	  filter: alpha(opacity=0); 
  	}

	</style>
  </head>
<body>
	<nav class="top-bar" data-topbar role="navigation" style="margin-bottom: 20px;">
		<ul class="title-area">
			<li class="name">
				<h1><a href="#">EMBonds End of Day</a></h1>
			</li>
			<!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
			<li class="toggle-topbar menu-icon">
				<a href="#"><span>Menu</span></a>
			</li>
		</ul>

		<section class="top-bar-section">
			<!-- Right Nav Section -->
			<!--
			<ul class="right">
				<li class="active"><a href="#">Right Button Active</a></li>
				<li ><a id="" href="index.php">Home</a></li>
				<li class="has-dropdown">
					<a href="#">Test</a>
					<ul class="dropdown">
						<li><a id="" href="#">Test</a></li>
					</ul>
				</li>
			</ul>
			 -->
			<!-- Left Nav Section -->
			<ul class="left">
				<li><a href="index.php">Home</a></li>
			</ul>
		</section>
	</nav>

	<div class="row">
		<div class="small-12 large-4 columns" id="detailcolumn">
			<div class='row'>
				<div class="small-12 columns">
					<h5 id="detailheader">Select a Date</h5>
				</div>
				<div id="detailcontent" class="small-12 columns">
					<input id="tradingday" type='text'/>
					<p ><strong><span id="chosentradingday"></span></strong></p>
				</div>
			</div>
		</div>
		<div class="small-12 large-8 columns">
			<h5 id="pageheader">Please select a trading day from the left</h5>
			<div class="row">
				<div id="v_lbt" class="small-3 columns">
					Vol Live Bid
				</div>
				<div id="v_lat" class="small-3 columns">
					Vol Live Ask
				</div>
				<div id="v_ibt" class="small-3 columns">
					Vol Ind Bid
				</div>
				<div id="v_lat" class="small-3 columns">
					Vol Ind Ask
				</div>
				
			</div>
			<div class="row">
				<div id="v_lb" class="small-3 columns" style='font-weight: 700;'>
				</div>
				<div id="v_la" class="small-3 columns" style='font-weight: 700;'>
				</div>
				<div id="v_ib" class="small-3 columns" style='font-weight: 700;'>
				</div>
				<div id="v_ia" class="small-3 columns" style='font-weight: 700;'>
				</div>
				
			</div>
			<div class="row">
				<div id="pagecontent" class="small-12 columns">
				</div>
			</div>
		</div>
	</div>	

<input type='hidden' value='' name='numisins' id='numisins' />
<input type='hidden' value='' name='currentisin' id='currentisin' />
<input type='hidden' value='0' name='vol_la' id='vol_la' />
<input type='hidden' value='0' name='vol_lb' id='vol_lb' />
<input type='hidden' value='0' name='vol_ia' id='vol_ia' />
<input type='hidden' value='0' name='vol_ib' id='vol_ib' />

<!--<script src="bower_components/jquery/dist/jquery.min.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="js/jquery.form.js"></script>	
<script src="bower_components/foundation/js/foundation.min.js"></script>
<script src="hc/js/highcharts.js"></script>
<script src="hc/js/modules/exporting.js"></script>
<script src="js/jquery.datetimepicker.full.js"></script>
<script src="js/numeral.min.js"></script>
<script src="js/soundapp.js"></script>
<script>

var isinlist = [];

$('#currentisin').change(function(){
	console.log('Current isin = '+eval($('#currentisin').val()));
	if (eval($('#currentisin').val()) < (eval($('#numisins').val()))){
		processisin(eval($('#currentisin').val()));
	} else {
	}
});

$('#tradingday').datetimepicker({
	inline: true,
	timepicker: false,
	format: 'Y-m-d'
});
$('#tradingday').change(function(){
	$('#chosentradingday').html($('#tradingday').val());
	isinlist = [];
	$.getJSON('utils/embx_json.php',
		{
			tradingday: $("#tradingday").val(),
			pf: "bondlistforday"
		},function( data ) {
			console.log(data);
			var tmptext = "";
			var hiddentext = "";
	      	$("#pagecontent").html("Bonds quoted on " + $("#tradingday").val() );
			tmptext = tmptext + "<table class='embx-table'><thead>"+
			"<tr><th style=' text-align: left'>Bond</th>"+
			"<th  style='text-align: center' colspan=2>Live Bid</th>"+
			"<th  style='text-align: center' colspan=2>Live Ask</th>"+
			"<th  style='text-align: center' colspan=2>Ind Bid</th>"+
			"<th  style='text-align: center' colspan=2>Ind Ask</th></tr>"+
			"</thead><tbody>";
			data.items.forEach( function( item ) {
				tmptext = tmptext + "<tr><td class='bondname' id='" + item.isin + 
				"' style=' text-align: left'><span class='label' style='font-size: 12px;' id='ccy"+item.isin+"'>" +
				 item.currency + "</span> "+ "<span data-tooltip aria-haspopup='true' class='has-tip' title='"+item.isin+"'>"+item.bondname+"</span>" + "</td>" + 
				"<td  class='size' id='lbs"+item.isin+"'  style=' text-align: right'></td>"+
				"<td  class='liveprice' id='lb"+item.isin+"'  style=' text-align: right'></td>"+
				"<td  class='liveprice' id='la"+item.isin+"' style=' text-align: right'></td>"+
				"<td  class='size' id='las"+item.isin+"' style=' text-align: right'></td>"+
				"<td  class='size'id='ibs"+item.isin+"' style=' text-align: right'></td>"+
				"<td  class='indicativeprice' id='ib"+item.isin+"' style=' text-align: right'></td>"+
				"<td  class='indicativeprice' id='ia"+item.isin+"' style=' text-align: right'></td>"+
				"<td  class='size' id='ias"+item.isin+"' style=' text-align: right'></td></tr>";
				isinlist.push(item.isin);
				hiddentext = hiddentext + "<input type='hidden' id='rt" + item.isin + "' value='"+ item.rate +"'/>";
			});
			tmptext = tmptext + "</tbody></table>";
			tmptext = tmptext + hiddentext;
	      	$("#pagecontent").append(tmptext);
			$(document).foundation();
			var v_lb = 0;
			var v_la = 0;
			var v_ib = 0;
			var v_ia = 0;
			
			$("#v_lb").html(numeral(0).format('($ 0 a)'));
			$("#v_la").html(numeral(0).format('($ 0 a)'));
			$("#v_ib").html(numeral(0).format('($ 0 a)'));
			$("#v_ia").html(numeral(0).format('($ 0 a)'));
			$("#vol_lb").val(0);
			$("#vol_la").val(0);
			$("#vol_ib").val(0);
			$("#vol_ia").val(0);
			
			$("#numisins").val(isinlist.length);
			$("#currentisin").val(0).trigger('change');

	});
	
/*	.done(
		function(){
			$("#numisins").val(isinlist.length);
			$("#currentisin").val(0).trigger('change');
		}
	); */
});

function processisin(isinid){
	var v_la = eval($('#vol_la').val());
	var v_lb = eval($('#vol_lb').val());
	var v_ia = eval($('#vol_ia').val());
	var v_ib = eval($('#vol_ib').val());
	console.log('vol_ib = '+v_ib);
	var isin = isinlist[isinid];
	console.log('isin = '+isin);
	console.log($('#tradingday').val());
	$("#ccy"+isin).addClass("alert");
	$.getJSON('utils/embx_json.php',
		{
			pf: "processbond",
			tradingday: $("#tradingday").val(),
			isin: isin
		},function(data){
			console.log(data);
			var rate = Number($("#rt" + isin).val());
			if (Number(data.items.px_last_live_bid) == 0) {
				$("#lb" + isin).html("");
				$("#lbs" + isin).html("");
			} else {
				$("#lb" + isin).html(numeral(data.items.px_last_live_bid).format('0.0000'));
				$("#lbs" + isin).html(numeral(data.items.max_sz_livebid).format('(0.0 a)'));
				v_lb = v_lb + (Number(data.items.max_sz_livebid) / rate);
			}
			if (Number(data.items.px_last_live_ask) == 0) {
				$("#la" + isin).html("");
				$("#las" + isin).html("");
			} else {
				$("#la" + isin).html(numeral(data.items.px_last_live_ask).format('0.0000'));
				$("#las" + isin).html(numeral(data.items.max_sz_liveask).format('(0.0 a)'));
				v_la = v_la + (Number(data.items.max_sz_liveask) / rate);
			}
			if (Number(data.items.px_last_indicative_bid) == 0 ) {
				$("#ib" + isin).html("");
				$("#ibs" + isin).html("");
			} else {
				$("#ib" + isin).html(numeral(data.items.px_last_indicative_bid).format('0.0000'));
				$("#ibs" + isin).html(numeral(data.items.max_sz_indicativebid).format('(0.0 a)'));
				 v_ib = v_ib + (Number(data.items.max_sz_indicativebid) / rate);
			}
			if (Number(data.items.px_last_indicative_ask) == 0) {
				$("#ia" + isin).html("");
				$("#ias" + isin).html("");
			} else {
				//alert(numeral(data.items.max_sz_indicativeask).format('(0.0 a)'));
				//console.log('isin = ' + isin);
				//console.log($("#ias" + isin).html());
				$("#ia" + isin).html(numeral(data.items.px_last_indicative_ask).format('0.0000'));
				$("#ias" + isin).html(numeral(data.items.max_sz_indicativeask).format('(0.0 a)'));
				v_ia = v_ia + (Number(data.items.max_sz_indicativeask) / rate);
			}
			
			$("#v_lb").html(numeral(v_lb).format('($ 0 a)'));
			$("#v_la").html(numeral(v_la).format('($ 0 a)'));
			$("#v_ib").html(numeral(v_ib).format('($ 0 a)'));
			$("#v_ia").html(numeral(v_ia).format('($ 0 a)'));
			$("#vol_lb").val(v_lb);
			$("#vol_la").val(v_la);
			$("#vol_ib").val(v_ib);
			$("#vol_ia").val(v_ia);
			
			$("#ccy"+isin).removeClass("alert");
			$("#currentisin").val(parseInt(isinid)+1).trigger('change');
			console.log(isinid);
			//alert('I have done it and changed the currentisin to '+(isinid+1));
	});
}


</script>	


  </body>
</html>









