<?php
include("utils/embx_dbconn.php");
include("utils/embx_functions.php");




?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EMB Audio File Generation</title>
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
				<h1><a href="#">EMBonds Audio</a></h1>
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
			<div class="small-12 large-3 columns">
				<h5>Batch Processing</h5>
				<p>Please select a file to process. The file should be a <strong>.csv</strong> file with no header line. 
				The first column will be used for the filename and the second column will be used for the text source. </p>
				<form action="utils/embx_processupload.php" method="post" enctype="multipart/form-data" id="FileUploadForm">
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
				<p id="output">&nbsp;</p>
				<a href="#" id="processbatchaudio" class="button expand">Process Audio</a>
						
			</div>
			<div class="small-12 large-9 columns">
				<h5>Processing a single line of text</h5>
				<form id="soundform2">
					<div class="row">
						<div class="small-4 columns">
							<div class="row collapse">
								<label>Filename <span id="singleaudiolink"><audio id="audiotest"></audio></span> <span id='audioplay' class='label'>PLAY</span></label>
								<div class="small-9 columns">
									<input type='text' id='audiofilename' placeholder='Enter Output Filename' />
								</div>
								<div class="small-3 columns">
									<span class="postfix">.ogg</span>
								</div>
							</div>
						</div>

						<div class="small-8 columns">
							<label>Text to Speak into File</label>
							<div class="row collapse">
								<div class="small-10 columns">
									<input type='text' id='singletext' placeholder='Enter text to be recorded'/>
								</div>
								<div class="small-2 columns">
									<a href="#" id="singlespeak" class='button postfix'>Go</a>
								</div>
							</div>
						</div>
					</div>
				</form>
				<form id="soundform">
					<div class="row">
						<div class="small-12 columns">
							<label>Text to Speak Out</label>
							<div class="row collapse">
								<div class="small-10 columns">
									<input type='text' id='speakouttext'  placeholder="Enter text to speak (for testing)"/></label>
								</div>
								<div class="small-2 columns">
									<a href="#" id="speakoutbutton" class='button postfix'>Go</a>
								</div>
							</div>
						</div>
					</div>
				</form>
				<h5>Batch Processing Details</h5>
				<div id="batchprocessoutput"></div>
			</div>
		</div>
	
<!--		<audio id="audiotest">
			<source src="audio/USM0375YAK49.mp3" type="audio/mpeg">
		</audio>
-->		
		<a class="playnow" href="#" style="display: none;"></a>
		<input type="hidden" id="selectedfileval" />
		<audio id="speakimmediate">
			
		</audio>
		
	  
	  

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
	<script src="js/jquery.form.js"></script>	
    <script src="bower_components/foundation/js/foundation.min.js"></script>
	<script src="hc/js/highcharts.js"></script>
	<script src="hc/js/modules/exporting.js"></script>
	<script src="js/jquery.datetimepicker.full.js"></script>

		
		
		
	<script>
		
		$("#FileInput").change(function(){
			$("#selectedfile").html("<strong>" + $("#FileInput").val().split("\\").pop() + "</strong>");
			$("#selectedfileval").val($("#FileInput").val().split("\\").pop());
			if ($("#FileInput").val().split("\\").pop().length > 0 ) {
				$("#batchprocess").removeClass("disabled");	
				$("#output").html("");  
				$('#uploadprogress').html('<span class="meter" style="width: 0%"></span>');
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
		
		
		$( "#showit" ).click(function(){
			alert($("#uploadfilename").val().split("\\").pop());
		})
		
	    $("#speakouttext").on( "keydown", function(event) {
	         if(event.which == 13) {
				
				$.get("utils/sound_ajax.php?pf=speakaudio&textstring=" + encodeURIComponent($("#speakouttext").val()) + "").success(function(data){
				$("#speakimmediate").html(data);
				$("#speakimmediate")[0].load();
				$("#speakimmediate").trigger('play');
			});
				//$("#textstring").val($("#speakouttext").val());
			}
		});
		
	    $("#singletext").on( "keydown", function(event) {
	         if(event.which == 13) {
				//alert(encodeURIComponent($("#singletext").val()));
 				$.get("utils/sound_ajax.php?pf=singleaudio&textstring='" + encodeURIComponent($("#singletext").val()) + "'&filename=" + $("#audiofilename").val()).success(function(data){
		 				$("#singleaudiolink").html(data);
		 				$("#audiotest")[0].load();
		 			});
			}
				//$("#textstring").val($("#speakouttext").val());
			
		});
		
		
		
		
		$("#singlespeak").click(function(){
			$.get("utils/sound_ajax.php?pf=singleaudio&textstring=" + encodeURIComponent($("#singletext").val()) + "&filename=" + $("#audiofilename").val()).success(function(data){
			
				$("#singleaudiolink").html(data);
				$("#audiotest")[0].load();
			});
		});
		$("#speakoutbutton").click(function(){
			//alert("utils/sound_ajax.php?pf=speakaudio&textstring='" + encodeURIComponent($("#speakouttext").val()) + "'");
			$.get("utils/sound_ajax.php?pf=speakaudio&textstring=" + encodeURIComponent($("#speakouttext").val()) + "").success(function(data){
				//alert(data);
				$("#speakimmediate").html(data);
				$("#speakimmediate")[0].load();
				$("#speakimmediate").trigger('play');
			});
		});
		$("#processbatchaudio").click(function(){
			$.get("utils/sound_ajax.php?pf=processbatchaudio&batchfilename=" + $("#selectedfileval").val()).success(function(data){
				$("#batchprocessoutput").html(data);
				bindhoverplay();
				bindaudioupdate();
			});
			
		});
		
		$("#audioplay").click(function(){
			$("#audiotest").trigger('play');
		});

	</script>
		
		    <script src="js/soundapp.js"></script>

<script>
		function bindhoverplay(){
		$(".playnow").hover(function(){
			//alert($(this).attr('href'));
		/*<audio id="speakimmediate">
			
		</audio>*/
			console.log("<source src='" + $(this).attr('href') + "' type='audio/ogg'>");
			$("#speakimmediate").html("<source src='" + $(this).attr('href') + "' type='audio/ogg'>" );
			$("#speakimmediate")[0].load();
			$("#speakimmediate").trigger('play');
			
		},function(){
			$("#speakimmediate").trigger('pause');
		});	
	}
	
	function bindaudioupdate(){
		$(".audioupdate").on("keypress",function(event){
			if (event.which == 13){
				console.log(this);
				//alert(this.id);
				var thisid = this.id;
				var isin = thisid.substring(2,thisid.length);
				var bondtext = encodeURIComponent($("#bt" + isin).val());
				var csvfile = encodeURIComponent($("#batchfilename").val());
				var zipfile = encodeURIComponent($("#zipfilename").val());

				//alert(isin);
				//alert(bondtext);
				//alert("utils/sound_ajax.php?pf=audioupdate&isin=" + isin + "&bondtext=" + bondtext + "&zipfilename=" + zipfile + "&batchfilename=" + csvfile);
				$.get("utils/sound_ajax.php?pf=audioupdate&isin=" + isin + "&bondtext=" + bondtext + "&zipfilename=" + zipfile + "&batchfilename=" + csvfile ).done(function(data){
					$("#speakimmediate").html("<source src='downloads/" + isin + ".ogg' type='audio/ogg'>" );
					$("#speakimmediate")[0].load();
					$("#speakimmediate").trigger('play');
				});	
				
			}
		});
	}
</script>
  </body>
</html>









