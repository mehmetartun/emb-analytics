<?php
include("utils/embx_dbconn.php");
include("utils/embx_functions.php");


?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EMB Analytics</title>
	<link rel="shortcut icon" href="images/favicon.ico">
	<link rel="icon" sizes="16x16 32x32 64x64" href="images/favicon.ico">
	<link rel="icon" type="image/png" sizes="196x196" href="images/favicon-192.png">
	<link rel="icon" type="image/png" sizes="160x160" href="images/favicon-160.png">
	<link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96.png">
	<link rel="icon" type="image/png" sizes="64x64" href="images/favicon-64.png">
	<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16.png">
	<link rel="apple-touch-icon" href="images/favicon-57.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/favicon-114.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/favicon-72.png">
	<link rel="apple-touch-icon" sizes="144x144" href="images/favicon-144.png">
	<link rel="apple-touch-icon" sizes="60x60" href="images/favicon-60.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/favicon-120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="images/favicon-76.png">
	<link rel="apple-touch-icon" sizes="152x152" href="images/favicon-152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="images/favicon-180.png">
	<meta name="msapplication-TileColor" content="#FFFFFF">
	<meta name="msapplication-TileImage" content="images/favicon-144.png">
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
	  filter: alpha(opacity=0); }
	</style>
  </head>
<body>
	<nav class="top-bar" data-topbar role="navigation" style="margin-bottom: 20px;">
		<ul class="title-area">
			<li class="name">
				<h1><a href="#">EMBonds Analytics</a></h1>
			</li>
			<!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
			<li class="toggle-topbar menu-icon">
				<a href="#"><span>Menu</span></a>
			</li>
		</ul>

		<section class="top-bar-section">
			<!-- Right Nav Section -->
			<ul class="right">
				<!-- <li class="active"><a href="#">Right Button Active</a></li> -->
				<li ><a id="logfilelist" href="#">Log Files</a></li>
				<li class="has-dropdown"><a href="#">Metrics</a>
					<ul class="dropdown">
				<li ><a id="bondlist" href="#">Bonds</a></li>
				<li ><a id="userlist" href="#">Users</a></li>
				<li ><a id="cptylist" href="#">Cptys</a></li>
				<li ><a id="isincountperday" href="#">Daily ISINs</a></li>
				<li><a id="RFQs" href="rfqs.php">RFQs</a></li>
				<li><a id="Stats" href="stats.php">Stats</a></li>
				
					</ul>
				</li>
				<li ><a id="tradesummary" href="#">Trades</a></li>
				<li class="has-dropdown">
					<a href="#">Utils</a>
					<ul class="dropdown">
						<li><a id="processtradingday" href="endofday.php">End of Day</a></li>
						<li><a id="createaudio" href="sound.php">Audio Generation</a></li>
						<li><a id="excel" href="excel.php">Excel Openfin</a></li>
						<li><a id="excelv2" href="excelexample.html">Excel Example</a></li>
				<li><a id="Stats" href="bonddefs.php">Bond Defs</a></li>

					</ul>
				</li>
				<li class="has-dropdown">
					<a href="#">Graphs</a>
					<ul class="dropdown">
						<li><a id="graph_isincount" class="graphlink" href="#">ISIN Count</a></li>
						<li><a id="graph_isincount_live" class="graphlink" href="#">Live ISIN Count</a></li>
						<li><a id="graph_usercount" class="graphlink" href="#">User Count</a></li>
						
					</ul>
				</li>
			</ul>

		<!-- Left Nav Section -->
		<ul class="left">
			<li><a href="index.php">Home</a></li>
		</ul>
	</section>
	  </nav>

		<div class="row">
			

			
			<div class="small-12 large-3 columns hide" id="divlogfileupload" >
				<h5>Log File Upload</h5>
				<p>Please select a log file to upload. The file should be a <strong>.csv</strong> file. 
				You have to ensure that a log file for that day <strong>doesn't exist</strong>. </p>

				<form action="utils/embx_processupload.php?filetype=logfile" method="post" enctype="multipart/form-data" id="FileUploadForm">
				<button class="file-upload expand">            
					<input type="file" name="FileInput" id='FileInput' class='file-input'>Select</button>
				</form>
				<label>Selected File</label>
				<p id="selectedfile">Not Selected</p>
				<a href="#" id="batchprocess" class="button expand disabled">Upload</a>
				<label>Progress</label>
				<div class="progress small-12 radius" id = "uploadprogress">
				  <span class="meter" style="width: 0%"></span>
				</div>
	
				<p id="output">&nbsp;</p> <!--
				<a href="#" id="processbatchaudio" class="button expand">Process Audio</a>
					-->
			</div>
			<div class="small-12 large-3 columns" id="detailcolumn">

				<div class='row'>
					<div class="small-12 columns">
						<h5 id="detailheader">&nbsp;</h5>
					</div>
					
					<div id="detailcontent" class="small-12 columns">


					</div>
				</div>
			</div>
			<div class="small-12 large-9 columns">
				<h5 id="pageheader"></h5>
				
				<div class="row">
					<div id="information" class="small-12 columns">
						<div class="row">
							<div class="small-8 columns">
								<img src="images/EMBondsAnalytics.png">
							</div>
							<div class="small-4 columns">
							</div>
						</div>
					</div>
					
					<div id="pagecontent" class="small-12 columns">

					</div>
				</div>
			</div>
		</div>


	  
	  

	<div id="pagecontentscript" style="display: none;">
				
		
	</div>

    <script src="js/jquery.min.js"></script>
	<script src="js/jquery.form.js"></script>	
    <script src="js/foundation.min.js"></script>
	<script src="js/highcharts.js"></script>
	<script src="js/exporting.js"></script>
	<script src="js/jquery.datetimepicker.full.js"></script>
    <script src="js/app.js"></script>
	<script>
	$("#FileInput").change(function(){
		
		$("#selectedfile").html("<strong>" + $("#FileInput").val().split("\\").pop() + "</strong>");
		$("#selectedfileval").val($("#FileInput").val().split("\\").pop());
		if ($("#FileInput").val().split("\\").pop().length > 0 ) {
			$("#batchprocess").removeClass("disabled");	
			$("#output").html("");  
			$('#uploadprogress').html('<span class="meter" style="width: 0%"></span>');
			//$("#testmyaudio").trigger('play');
		} else {
			$("#batchprocess").addClass("disabled");
			$("#selectedfile").html("Not Selected");
			$("#output").html("");  
			 $('#uploadprogress').html('<span class="meter" style="width: 0%"></span>');
		};
	});
	$("#batchprocess").click(function(){
		if (!$("#batchprocess").hasClass("disabled")){

				$("#FileUploadForm").trigger("submit");
		}
	});
	
	
	</script>
	
  </body>
</html>









