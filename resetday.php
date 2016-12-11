<?php
include("utils/embx_dbconn.php");
include("utils/embx_functions.php");
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
			<h5 id="pageheader">Resetting following tables</h5>
			<div class='row'>
				<div class='small-12 columns'>
					For <strong><span id='tradingdayselected'><span style='color: #aaaaaa;' >Select Trading Day</span></span></strong>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resetendofdaytable'>Clear End of Day</a>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resetorderstable'>Clear Orders</a>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resetrfqstable'>Clear RFQs</a>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resettradestable'>Clear Trades</a>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resetlogfilestable'>Clear Log Files</a>
				</div>
				<div class='small-6 columns'>
				<a href='#' class='button  disabled resetbutton expand' id='resetsnapshottable'>Clear Snapshot</a>
				</div>

				
			</div>
		</div>
	</div>	

	  

	<!-- <script src="bower_components/jquery/dist/jquery.min.js"></script> -->
	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script> -->

	<script src="bower_components/jquery/dist/jquery.min.js"></script>
	<script src="js/jquery.form.js"></script>	
    <script src="bower_components/foundation/js/foundation.min.js"></script>
	<script src="hc/js/highcharts.js"></script>
	<script src="hc/js/modules/exporting.js"></script>
	<script src="js/jquery.datetimepicker.full.js"></script>
	<script src="js/numeral.min.js"></script>
	<!--
	<script src="js/soundapp.js"></script>
	-->
	<script>
		$('#tradingday').datetimepicker({
			inline:true,
			timepicker: false,
			format: 'Y-m-d'
		});
		$('#tradingday').change(function(){
			$('#tradingdayselected').html($('#tradingday').val());
			$('#resetendofdaytable').removeClass('disabled');
			$('#resetendofdaytable').html('Clear End of Day for '+$('#tradingday').val());	
			$('#resetorderstable').removeClass('disabled');
			$('#resetorderstable').html('Clear Orders for '+$('#tradingday').val());	
			$('#resetrfqstable').removeClass('disabled');
			$('#resetrfqstable').html('Clear RFQs for '+$('#tradingday').val());	
			$('#resettradestable').removeClass('disabled');
			$('#resettradestable').html('Clear Trades for '+$('#tradingday').val());	
			$('#resetsnapshottable').removeClass('disabled');
			$('#resetsnapshottable').html('Clear Snapshots for '+$('#tradingday').val());	
			$('#resetlogfilestable').removeClass('disabled');
			$('#resetlogfilestable').html('Clear Log Files for '+$('#tradingday').val());	
			$('.resetbutton').removeClass('alert');
		});
		$('.resetbutton').click(function(){
			var thisid = this.id;
			$("#"+thisid).addClass("alert");
			//alert('hello');
			//var thisid = this.id;
			$.get("utils/embx_removetables.php?pf=reset&table=" + this.id + "&tradingday=" + $('#tradingday').val(), function(data){
				//$("#"+thisid).removeClass('alert');
				//alert(data);
			});
		});
		
		
		
		
	</script>	


  </body>
</html>









