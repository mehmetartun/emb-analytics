// Foundation JavaScript
// Documentation can be found at: http://foundation.zurb.com/docs
$(document).foundation();

$(document).ready(function(){
	$.get("utils/embx_graph.php?gc=graph_isincount",function(data){
		$('#pagecontentscript').html(data);
		$('#pageheader').html("");
	});
});

$("#logfilelist").click(function(){
	cleandetail();
	$('#pagecontent').html("Refreshing");
	$.get("utils/embx_ajax.php?pf=logfilelist",function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Log Files");
	});
	$("#divlogfileupload").removeClass("hide");
});

function cleandetail(){
	$("#pagecontentscript").html("");
	$("#detailcontent").html("");
	$("#detailheader").html("");
	$("#divlogfileupload").addClass("hide");
}

$("#bondlist").click(function(){
	cleandetail();
	$('#pagecontent').html("Refreshing");
	$.get("utils/embx_ajax.php?pf=bondlist",function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Bonds quoted on the platform");
	});
});

$("#userlist").click(function(){
	cleandetail();

	$('#pagecontent').html("Refreshing");
	$.get("utils/embx_ajax.php?pf=userlist",function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Users engaged on the platform");
	});
});







$("#cptylist").click(function(){
	cleandetail();

	$('#pagecontent').html("Refreshing");
	$.get("utils/embx_ajax.php?pf=cptylist",function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Counterparties engaged on the platform");
	});
});



$("#tradesummary").click(function(){
	cleandetail();

	$('#pagecontent').html("Refreshing");
	$('#detailcontent').html("");
	$('#detailheader').html("");	
	$.get("utils/embx_ajax.php?pf=tradesummary",function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Trades");
	});
});

//$.datetimepicker.setLocale('en');
//$('.dtpicker').datetimepicker();

$("#cleantables").click(function(){
	cleandetail();

	$('#pagecontent').html("<form><label>From<input type='text' id='fromdate' class='dtpicker'></label><label>To<input type='text' id='todate' class='dtpicker'></label><a href='#' class='button tiny'>OK</a></form>");
	$('#pageheader').html("Deletion of orders");
	$('.dtpicker').datetimepicker(
		{format: 'Y-d-m H:i:s'}
	);
});

$("#settings").click(function(){
	cleandetail();
	$('#pageheader').html("Settings");
	$.get("utils/embx_ajax.php?pf=settings"  ,function(data){
		$('#pagecontent').html(data);
		
	});

});

$(".graphlink").click(function(){
	cleandetail();

	$('#pageheader').html("");
	$.get("utils/embx_graph.php?gc=" + this.id ,function(data){
		$('#pagecontentscript').html(data);
	});
});



function embx_getbonddetail(isin){
	$.get("utils/embx_ajax.php?pf=bonddetail&isin=" + isin ,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Detail for " + isin);
	});
}
function embx_getuserdetail(user){
	$.get("utils/embx_ajax.php?pf=userdetail&user=" + user ,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Detail for " + user);
	});
}

function embx_getcptydetail(cpty){
	$.get("utils/embx_ajax.php?pf=cptydetail&cpty=" + cpty ,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Detail for " + user);
	});
}

function embx_getbonddetailforday(isin,tradingday){
	$.get("utils/embx_ajax.php?pf=bonddetailforday&isin=" + isin + "&tradingday=" + tradingday ,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Detail for " + isin );
	});
}

function embx_graphmarket(isin,tradingday){
	//cleandetail();
	$.get("utils/embx_graph.php?gc=graphmarket&isin=" + isin + "&tradingday=" + tradingday ,function(data){
//		alert(data);
		console.log(data);
		$('#pagecontentscript').html(data);
		//console.log(data);
		$('#pageheader').html("Market history for " + isin + " on " + tradingday );
	});
}




function embx_getbonddetailfordayforuser(isin,tradingday,user){
	$.get("utils/embx_ajax.php?pf=bondlistfordayforuser&isin=" + isin + "&tradingday=" + tradingday + "&user=" + user ,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Detail for " + isin );
	});
}

function embx_js_processfile(filename){
	
	$.get("utils/embx_ajax.php?pf=processlogfile&filename=" + filename).done(function(){
		$('#filelist').html("Refreshing");
		$.get("utils/embx_ajax.php?pf=logfilelist",function(data){
			$('#pagecontent').html(data);
			$('#pageheader').html("Log Files");
		});
	});
}
function embx_js_showrejects(filename){
	$.get("utils/embx_ajax.php?pf=showrejects&filename=" + filename,function(data){
		$('#pagecontent').html(data);
		$('#pageheader').html("Rejects from " + filename);
	});
}

$(document).ready(function() { 
	var options = { 
			target:   '#output',   // target element(s) to be updated with server response 
			beforeSubmit:  beforeSubmit,  // pre-submit callback 
			success:       afterSuccess,  // post-submit callback 
			uploadProgress: OnProgress, //upload progress callback 
			resetForm: true        // reset the form after successful submit 
		}; 
	
	 $('#FileUploadForm').submit(function() { 
			$(this).ajaxSubmit(options);  			
			// always return false to prevent standard browser submit and page navigation 
			return false; 
		}); 
	

//function after succesful file upload (when server response)
function afterSuccess()
{

	$("#batchprocess").toggleClass("disabled");	
	$('#submit-btn').show(); //hide submit button
	$('#loading-img').hide(); //hide submit button
	$('#progressbox').delay( 1000 ).fadeOut(); //hide progress bar
	$.get("utils/embx_ajax.php?pf=logfilelist",function(data){
		$('#pagecontent').html(data);
			//$('#pageheader').html("Log Files");
	});
	
}

//function to check file size before uploading.
function beforeSubmit(){
    //check whether browser fully supports all File API
   if (window.File && window.FileReader && window.FileList && window.Blob)
	{
	
		if( !$('#FileInput').val()) //check empty input filed
		{
			$("#output").html("Are you kidding me?");
			return false
		}
	
		var fsize = $('#FileInput')[0].files[0].size; //get file size
		var ftype = $('#FileInput')[0].files[0].type; // get file type
	

		//allow file types 
		switch(ftype)
        {
            case 'image/png': 
			case 'image/gif': 
			case 'image/jpeg': 
			case 'image/pjpeg':
			case 'text/plain':
			case 'text/html':
			case 'application/x-zip-compressed':
			case 'application/pdf':
			case 'application/msword':
			case 'application/vnd.ms-excel':
			case 'video/mp4':
			case 'text/csv'	:
                break;
            default:
                $("#output").html("<b>"+ftype+"</b> Unsupported file type!");
				return false
        }
	
		//Allowed file size is less than 5 MB (1048576)
		if(fsize>5242880) 
		{
			$("#output").html("<b>"+bytesToSize(fsize) +"</b> Too big file! <br />File is too big, it should be less than 5 MB.");
			return false
		}
			
		$('#submit-btn').hide(); //hide submit button
		$('#loading-img').show(); //hide submit button
		$("#output").html("");  
	}
	else
	{
		//Output error to older unsupported browsers that doesn't support HTML5 File API
		$("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
		return false;
	}
}

//progress bar function
function OnProgress(event, position, total, percentComplete)
{
    //Progress bar
	//$('#progressbox').show();
    $('#uploadprogress').html('<span class="meter" style="width: ' + percentComplete + '%"></span>');
					//update progressbar percent complete
    //$('#statustxt').html(percentComplete + '%'); //update status text
    //if(percentComplete>50)
    //    {
    //        $('#statustxt').css('color','#000'); //change status text to white after 50%
    //    }
}

//function to format bites bit.ly/19yoIPO
function bytesToSize(bytes) {
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes == 0) return '0 Bytes';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

}); 
