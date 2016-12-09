<?php
include("embx_dbconn.php");
include("embx_functions.php");

if(isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"]== UPLOAD_ERR_OK)
{
	############ Edit settings ##############;
	
	$currentdir = getcwd();
	
	if (isset($_GET["filetype"])){
		$ft = $_GET["filetype"];
	} else {
		$ft = "";
	}
	
	
	
//	$UploadDirectory	= '/Users/Shared/'; //specify upload directory ends with / (slash)
	
	if ($ft == "logfile") { 
		$UploadDirectory =  EMB_SOURCE_FILE_DIRECTORY;
	} else { 
		$UploadDirectory = EMB_UPLOAD_DIRECTORY;
	} 
	
	##########################################
	
	/*
	Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini". 
	Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit 
	and set them adequately, also check "post_max_size".
	*/
	
	//check if this is an ajax request
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		die();
	}
	
	
	//Is file size is less than allowed size.
	if ($_FILES["FileInput"]["size"] > 100000000) {
		die("File size is too big!");
	}
	
	//allowed file type Server side check
	switch(strtolower($_FILES['FileInput']['type']))
		{
			//allowed file types
            case 'image/png': 
			case 'image/gif': 
			case 'image/jpeg': 
			case 'image/pjpeg':
			case 'text/plain':
			case 'text/html': //html file
			case 'application/x-zip-compressed':
			case 'application/pdf':
			case 'application/msword':
			case 'application/vnd.ms-excel':
			case 'video/mp4':
			case 'text/csv':
				break;
			default:
				die('Unsupported File!'); //output error
	}
	
	$File_Name          = $_FILES['FileInput']['name'];
	$File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention
	$Random_Number      = rand(0, 9999999999); //Random number to be added to name.
	//$NewFileName 		= $Random_Number.$File_Ext; //new file name
	
	if(move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory.$File_Name ))
	   {
		if ((substr($File_Name,0,4) == "Log-")  || (substr($File_Name,0,4) == "log-") ) {
			$datefrom = substr($File_Name,4,10)." ".substr($File_Name,15,2).":".substr($File_Name,18,2).":00";
			$dateto =   substr($File_Name,21,10)." ".substr($File_Name,32,2).":".substr($File_Name,35,2).":00";
		} else {
			$datefrom = substr($File_Name,10,10)." ".substr($File_Name,21,2).":".substr($File_Name,24,2).":00";
			$dateto =   substr($File_Name,27,10)." ".substr($File_Name,38,2).":".substr($File_Name,41,2).":00";
		}  
		
		$rs = EMBXDB::get()->query("insert into processed (filename, processed,datetime_from,datetime_to) values ('" . $File_Name . "',0,'".$datefrom."','".$dateto."') ");
		die('Success! File Uploaded.');
	}else{
		die('error uploading File!');
	}
	
}
else
{
	die('Something wrong with upload! Is "upload_max_filesize" set correctly?');
}