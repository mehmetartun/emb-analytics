<?php
include("utils/embx_dbconn.php");
include("utils/embx_functions.php");
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Platform Engagement</title>
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

  </head>
<body>
	<nav class="top-bar" data-topbar role="navigation" style="margin-bottom: 20px;">
		<ul class="title-area">
			<li class="name">
				<h1><a href="#">EMBonds Platform Engagement</a></h1>
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
					<h5 id="functionheader">Platform Metric Selection</h5>
					<form>
						<select id='functionselect'>
							<option value='' selected disabled>Select</option>
							<option value='periodnumisins'>ISIN Count</option>
							<option value='periodnumtrades'>Trade Count</option>
							<option value='periodvoltrades'>Trade Volume</option>
							<option value='periodnumrfqs'>RFQ Count</option>
							<option value='periodvolrfqs'>RFQ Volume</option>
							<option value='periodnumrfqsmonthly'>Monthly RFQ Ct</option>
							<option value='periodnumrfqsmonthly_line'>Monthly RFQ Ct (Line)</option>
							<option value='periodvolrfqsmonthly'>Monthly RFQ Vol</option>
							<option value='periodvolrfqsmonthly_line'>Monthly RFQ Vol (Line)</option>
							<option value='periodnumtradesmonthly'>Monthly Trade Ct</option>
							<option value='periodnumtradesmonthly_line'>Monthly Trade Ct (Line)</option>
							<option value='periodvoltradesmonthly'>Monthly Trade Vol</option>
							<option value='periodvoltradesmonthly_line'>Monthly Trade Vol (Line)</option>
							<option value='periodvolnumtradesmonthly_doubleline'>Monthly Trade Vol and Ct (Line)</option>
							<option value='periodvolnumtradesmonthly_linecolumn'>Monthly Trade Vol and Ct (Line/Col)</option>
							<option value='periodnumtradingpartiesmonthly'>Monthly Traded Parties</option>
							<option value='periodnumrfqpartiesmonthly'>Monthly RFQ Parties</option>
							<option value='periodnumrfqtradingpartiesmonthly'>Monthly RFQ and Traded Parties</option>
							


						</select>
					</form>
					<h5 id="detailheader">Date selection</h5>
					<a href='#' id='graphbutton' class='button disabled expand'>Graph&nbsp;&nbsp;<span id="chosenfromdate"></span><span id="chosentodate"></span></a>
					<a href='#' id='csvbutton' class='button success disabled expand'>Download CSV</a>

				</div>
				<div id="detailcontent" class="small-12 columns">
					<h6>From</h6>	
					<input id="fromdate" type='text'/>
					<h6>To</h6>
					<input id="todate" type='text' />
					
				</div>
			</div>
		</div>
		<div class="small-12 large-8 columns">
			
			
			<div class="row">
				<div id="pagecontent" class="small-12 columns">
					<h3>Platform Engagement Metrics</h3>
					<p>Please use the selectors on the left to select the type of data you would like to view. 
						Once you have made a selection and chosen a valid date range, you will be able to click on the graph button to view the graph.</p>
						<p>Each graph can also be downloaded as a CSV file using the <strong>Download CSV</strong> link.</p>
						<p>In the graph data clicking on the datapoint may reveal more information below the graph for certain types of graphs.</p>
				</div>
			</div>
			<div class="row">
				<div id="subdetail" class="small-12 columns">
				</div>
			</div>
			<div class="row">
				<div id="subdetaillower" class="small-12 columns">
				</div>
			</div>
		</div>
	</div>	
	<div id='pagecontentscript' style='display: none;'></div>
	<div id='csvdata' style='display: none;'></div>
	

<script src="bower_components/jquery/dist/jquery.min.js"></script> 



<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script> -->
<script src="js/jquery.form.js"></script>	
<script src="bower_components/foundation/js/foundation.min.js"></script>
<script src="hc/js/highcharts.js"></script>
<script src="hc/js/modules/exporting.js"></script>
<script src="js/jquery.datetimepicker.full.js"></script>
<script src="js/jquery-dateFormat.min.js"></script>

<script src="js/numeral.min.js"></script>
<script src="js/soundapp.js"></script>
<script>
Highcharts.setOptions({
	global: {
		useUTC: true,
		timezoneOffset: 0
	}
})
$('#fromdate').datetimepicker({
	inline: true,
	timepicker: false,
	format: 'Y-m-d'
});
$('#todate').datetimepicker({
	inline: true,
	timepicker: false,
	format: 'Y-m-d'
});

$('#fromdate').change(function(){
	$('#chosenfromdate').html($('#fromdate').val()+' to ');
	graphbutton();
})
$("#functionselect").change(function(){
	graphbutton();
})
$('#todate').change(function(){
	$('#chosentodate').html($('#todate').val());
	graphbutton();
})
$('#graphbutton').click(function(){
	$("#subdetail").html("");
	$("#subdetaillower").html("");
	
	//alert("utils/embx_graph.php?gc=graph_"+$("#functionselect").val()+"&fromdate="+$('#fromdate').val()+'&todate='+$('#todate').val());
	$.get("utils/embx_graph.php?gc=graph_"+$("#functionselect").val()+"&fromdate="+$('#fromdate').val()+'&todate='+$('#todate').val(),function(data){
		$("#pagecontentscript").html(data);
	});
});
$('#csvbutton').click(function(){
	
});

function graphbutton(){
	//alert('checking now');
	if ($('#fromdate').val() && $('#todate').val() ){
		var fromdate = new Date($('#fromdate').val());
		var todate = new Date($('#todate').val());
		if ((fromdate - todate) < 0 && $("#functionselect").val() ) {
			$("#graphbutton").removeClass('disabled');
			$("#csvbutton").removeClass('disabled');
			$("#csvbutton").attr("href","utils/embx_csv.php?pf=csv_"+$("#functionselect").val()+"&fromdate="+$('#fromdate').val()+
			"&todate="+$('#todate').val());
		} else {
			$("#graphbutton").addClass('disabled');
			$("#csvbutton").addClass('disabled');
		}
	} else {
		$('#graphbutton').addClass('disabled');
		$('#csvbutton').addClass('disabled');
	}
}
function pointclick(x){
	if ($("#functionselect").val() == 'periodnumtrades'){
		var tradingday = new Date(x);
		var tradingdaystr = $.format.date(tradingday, 'yyyy-MM-dd');
		$.get('utils/embx_ajax.php?pf=tradesummaryfortradingday&tradingday='+tradingdaystr,function(data){
			$("#subdetail").html(data);
		});
	}
	if ($("#functionselect").val() == 'periodvoltrades'){
		var tradingday = new Date(x);
		var tradingdaystr = $.format.date(tradingday, 'yyyy-MM-dd');
		$.get('utils/embx_ajax.php?pf=tradesummaryfortradingday&tradingday='+tradingdaystr,function(data){
			$("#subdetail").html(data);
		});
	}
	if ($("#functionselect").val() == 'periodnumrfqs'){
		var tradingday = new Date(x);
		var tradingdaystr = $.format.date(tradingday, 'yyyy-MM-dd');
		$.get('utils/embx_json.php?pf=rfqs&tradingday='+tradingdaystr, function(data){
			//$('#pageheader').html("RFQ's on " + $("#chosentradingday").html());
			$("#subdetail").html(data);
			$(document).foundation();
		});
	}
	if ($("#functionselect").val() == 'periodvolrfqs'){
		var tradingday = new Date(x);
		var tradingdaystr = $.format.date(tradingday, 'yyyy-MM-dd');
		$.get('utils/embx_json.php?pf=rfqs&tradingday='+tradingdaystr, function(data){
			//$('#pageheader').html("RFQ's on " + $("#chosentradingday").html());
			$("#subdetail").html(data);
			$(document).foundation();
		});
	}
}
//$(document).foundation();
</script>	
</body>
</html>









