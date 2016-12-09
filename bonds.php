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
	  filter: alpha(opacity=0); }
	</style>
  </head>
<body>
	<? include("topbar.php") ?>

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
				<h5 id="pageheader">Please select action above</h5>
			
				<div class="row">
					<div id="information" class="small-12 columns">

					</div>
					
					<div id="pagecontent" class="small-12 columns">

					</div>
				</div>
			</div>
		</div>


	  
	  

	<div id="pagecontentscript" style="display: none;">
				
		
	</div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
	<script src="js/jquery.form.js"></script>	
    <script src="bower_components/foundation/js/foundation.min.js"></script>
	<script src="hc/js/highcharts.js"></script>
	<script src="hc/js/modules/exporting.js"></script>
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









