<?php
include("embx_dbconn.php");
include("embx_functions.php");
function base64_encode_audio ($filename, $filetype) {
    if ($filename) {
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        return 'data:audio/' . $filetype . ';base64,' . base64_encode($imgbinary);
    }
}

set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/bin' . PATH_SEPARATOR . '/usr/sbin' . PATH_SEPARATOR . '/usr/local/bin'  );


$pf = $_GET["pf"];


//$filepath = "/Library/WebServer/Documents/embx-a/downloads/";

$filepath = AUDIO_FILE_PATH;

switch ($pf) {
	
	case "singleaudio":
		$textstring = urldecode($_GET["textstring"]);
		$filename = $_GET["filename"];
		$filename = str_replace(" ","_",$filename);
		//echo get_include_path() . "<br />";
		//echo "say -v Allison --file-format=mp4f -o ".$filepath.$filename.".mp4 " . $textstring . "<br />";
		exec("say -v Allison --file-format=mp4f -o ".$filepath.$filename.".mp4 " . $textstring,$output, $return);
		//echo "Output = <br />";
		//var_dump( $output);
		//echo "<br />";
		//echo "Return = <br />";
		//var_dump( $return);
		//echo "<br />";
		
		exec("afconvert -f mp4f -d aac " . $filepath . $filename . ".mp4 ". $filepath . $filename . ".mp3" , $output, $return);
		//exec("rm ".$filepath.$filename.".mp4");
		unlink($filepath.$filename.".mp4");
		//echo "Output = <br />";
		//var_dump( $output);
		//echo "<br />";
		//echo "Return = <br />";
		//var_dump( $return);
		//echo "<br />";

		exec("ffmpeg -y -i " . $filepath.$filename.".mp3 ".$filepath.$filename.".ogg",$output, $return);
		unlink($filepath.$filename.".mp3");
		//exec("rm ".$filepath.$filename.".mp3");
		//echo "Output = <br />";
		//var_dump( $output);
		//echo "<br />";
		//echo "Return = <br />";
		//var_dump( $return);
		//echo "<br />";

		echo "<a href='downloads/" . $filename . ".ogg'>" . $filename . ".ogg" . "</a> &nbsp;";
		echo "<audio id='audiotest'><source src='downloads/".$filename.".ogg' type='audio/ogg'></audio>";
		
	
	break;
	
	case "speakaudio":
		//echo "I am here";
		$output = [];
		$textstring = urldecode($_GET["textstring"]);
		$filename = "tempaudiofile";
		exec("say -v Allison --file-format=mp4f -o ".$filepath.$filename . ".mp4 " . $textstring);
		//$filepath =  "/Library/WebServer/Documents/embx/downloads/";
		//unlink($filepath.$filename.".mp3");
		exec("afconvert -f mp4f -d aac " . $filepath . $filename . ".mp4 ". $filepath . $filename . ".mp3" , $output, $return);
		unlink($filepath.$filename.".mp4");
		exec("ffmpeg -y -i " . $filepath.$filename.".mp3 ".$filepath.$filename.".ogg",$output, $return);
		unlink($filepath.$filename.".mp3");
		//unlink($filepath.$filename.".mp4");
		//echo "Output = <br />";
		//var_dump( $output);
		//echo "<br />";
		//echo "Return = <br />";
		//var_dump( $return);
		//echo "<br />";

		$audiobase64 = base64_encode_audio($filepath.$filename.".ogg","ogg");
		echo "<source src='" . $audiobase64 . "'>";
		//echo "Base64 = <br />";
		//var_dump($audiobase64);	
		//echo "<br />";
		//echo get_include_path();
		//echo "<br />";
	
	break;
	
	
	
	case "processbatchaudio":
	$batchfilename = $_GET["batchfilename"];
	$static = $_GET["static"];
	
	// ffmpeg -y -f concat -i <(printf "file '$PWD/%s'\n" a.ogg; printf "file '$PWD/%s'\n" b.ogg) -c copy ab.ogg
	$lines = file("../uploads/" . $batchfilename);
	$i = 0;
	$zip = new ZipArchive();
	//$zipfilename = $filepath . basename($batchfilename,".csv") . "-Audio-OGG.zip";
	$zipfilename = "../downloads/" . basename($batchfilename,".csv") . "-Audio-OGG.zip";
	$zipfileshort = basename($batchfilename,".csv") . "-Audio-OGG.zip";
	
	$res = $zip->open($zipfilename, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
	
	$ogglist = "<p>Hover over the <i class='fi-play'></i> icon to play the sound. </p><ul class='no-bullet'>";
		
	foreach ($lines as $line) {
		$i = $i +1;	
		$arr = explode(",",$line);
		$arr[0] = trim($arr[0]);
		$filename = $filepath . $arr[0] . ".mp4";
		$filenameout = $filepath . $arr[0] . ".mp3";
		$filenameoutogg = $filepath . $arr[0] . ".ogg";
		$bondtext = $arr[1];
		//$ogglist = $ogglist .  "<li ><a class='playnow' href='downloads/".$arr[0].".ogg'>".$arr[0].".ogg</a> ". $bondtext ."</li>";
		$ogglist = $ogglist .  "<li id='li".$arr[0]."' ><a class='playnow' href='downloads/".$arr[0].".ogg'><i class='fi-play'></i></a> 
							<a  href='downloads/".$arr[0].".ogg'>".$arr[0].".ogg</a> 
		                   " .$arr[2]."<input class='audioupdate' id='bt" . $arr[0] . "' type='text' value='". $bondtext ."'/></li>";

		//echo "BondISIN = " . $arr[0] . " Text = " . $arr[1] . "<br />";
		exec("say -v Allison --file-format=mp4f -o " . $filename . " " . $bondtext);
		exec("afconvert -f mp4f -d aac " . $filename . " ". $filenameout  );
		unlink($filename);
		exec("ffmpeg -y -i " . $filenameout . " " . $filenameoutogg, $output, $return);
		unlink($filenameout);
		//unlink($filename);
		$zip->addFile($filenameoutogg, $arr[0] . ".ogg");
		//unlink($filenameoutogg);
	}
	$ogglist = $ogglist . "</ul>";
	$ogglist = $ogglist . "<input type='hidden' value='". $batchfilename ."' id='batchfilename' />";
	$ogglist = $ogglist . "<input type='hidden' value='". $zipfileshort ."' id='zipfilename' />";
	
	$zip->close();
	
	echo "<p>A total of " . $i . " mp3 files have been created. The zip archive can be downloaded below. </p>";
	echo "<a href='downloads/" .  $zipfileshort . "' class='button small'>" . $zipfileshort . "</a>";
	echo " <a href='uploads/" .  $batchfilename . "' class='button small secondary'>" . $batchfilename . "</a>";
	
	echo $ogglist;
	
	break;
	
	
	case "audioupdate":
		$zip = new ZipArchive();
		$bondtext = urldecode($_GET["bondtext"]);
		$isin = urldecode($_GET["isin"]);
		$zipfilename = urldecode($_GET["zipfilename"]);
		$batchfilename = urldecode($_GET["batchfilename"]);
		//$batchhandle = fopen($filepath.$batchfilename,"w+");
		$filename = $isin;
		$lines = file("../uploads/" . $batchfilename , FILE_SKIP_EMPTY_LINES);
		
		
		exec("say -v Allison --file-format=mp4f -o ".$filepath.$filename.".mp4 " . $bondtext,$output, $return);
		
		exec("afconvert -f mp4f -d aac " . $filepath . $filename . ".mp4 ". $filepath . $filename . ".mp3" , $output, $return);

		exec("ffmpeg -y -i " . $filepath.$filename.".mp3 ".$filepath.$filename.".ogg",$output, $return);

		//echo "<a href='downloads/" . $filename . ".ogg'>" . $filename . ".ogg" . "</a> &nbsp;";
		//echo "<audio id='audiotest'><source src='downloads/".$filename.".ogg' type='audio/ogg'></audio>";
		echo $filepath.$zipfilename;
		$zip->open($filepath.$zipfilename);
		$zip->deleteName($filename.".ogg");
		$zip->addFile("../downloads/".$filename.".ogg", $filename. ".ogg");
		$zip->close();
		$i=0;
		$handle = fopen("../uploads/" .$batchfilename,"r");
		
		
		while (($arr[$i] = fgetcsv($handle,0,",",'"')) !== FALSE){
			var_dump($arr[$i]);
			if ($arr[$i][0] == $filename){
				$arr[$i][1] = $bondtext;
			}
			$i = $i +1;
		}
		fclose($handle);
		$handle = fopen("../uploads/".$batchfilename,"w");
		foreach($arr as $arrline){
			var_dump($arrline);
			if (count($arrline) == 3){
				fputcsv($handle,$arrline,",",'"');
			}
			
		}
		fclose($handle);	
	
		
		
	break;

	
	
}





?>
