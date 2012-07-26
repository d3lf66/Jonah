<?php

// show all files
									
foreach(glob($voduploaddir.'*', GLOB_ONLYDIR) as $i)						// for each folder found in the 'voduploaddir' root ($i = '/home/sony' )
{ 

	//echo $i."/ - ";
	$watchfolder=$i."/";													// add trailing slash to folder
	$client = strtoupper(str_replace($voduploaddir," ",$i));   
	// echo ("<br /><b>".$watchfolder." - </b>");							// debug: show $watchfolder
	echo ("&nbsp;<br />");
 	if (glob($watchfolder."*.*") != false)									// if files in upload folder
		 {
			echo ("<table class='jonahtable' width='100%'><tr class='tabhead-upload'><td class='td-path'> Client </td><td class='td-name'> Filename </td><td class='td-size'> Size </td><td class='td-timestamp'> Timestamp </td><td class='td-duration'> Duration </td><td class='td-ffmpeg'> FFMPEG Inspect [debug]</td><td class='td-video'> Video </td><td class='td-audio'> Audio </td><td class='td-status'> Status </td><td class='td-icon'></td></tr>");	 
		 
 		$filecount = count(glob($watchfolder."*.*"));
		 // echo ($filecount." files detected <br />"); 					// debug: count number of files
 		$filesfound = glob($watchfolder."*.*"); 							// get all files under watchfolder with a wildcard extension. (change for specific filetypes)

 foreach($filesfound as $filefound)											// repeat for each file found in the watchfolder
	{
	// check for media detection 
	
	if (preg_match("/\.".$allowed_video_types."$/i",$filefound)) 			// does the file have an allowed video suffix? 
	{
	// inspect with ffmpeg
	echo ("<tr>");															// cleanup
	$tempinfo=pathinfo($filefound);											// start with fullpath to file found in watchfolder (e.g. '/home/sony/Shakira Liar.mpg')
    $videofilename=basename($filefound,'.'.$tempinfo['extension']);			// find filename without path and extension (e.g. 'Shakira Liar')
	$tempinspect_file=$pathtoinspect.$videofilename.".txt";					// full path path to 'ffmpeg -i' output (e.g. '/var/www/jonah/txtout/shakira liar.txt')
	$inspect_file=strtolower(str_replace(" ","_",$tempinspect_file));		// replace filename spaces with underscores and convert to lowercase (e.g. 'shakira_liar')
	$videofilesize=intval((filesize($filefound)/1000000));					// get uploaded video size in approx Mb
	$videouploaded=date("F d", filemtime($filefound)).", ".date("H:i", filemtime($filefound)); // formatted for column display
	$videoagefull=date("U",filemtime($filefound));
	$videoage=intval((($currenttime-$videoagefull)/60));					// how many full minutes difference between video uploaded and current time (e.g '2')
	$videotype=mime_content_type($filefound);								// get mime-type
    $ffmpeg_inspect="ffmpeg -i '".$filefound."' 2> ".$inspect_file;			// ffmpeg command (e.g. 'ffmpeg -i '/home/sony/Shakira Liar.mpg' '/var/www/jonah/txtout/sony-shakira_liar.txt')

	echo ("<td class='td-path'><span class='countdown'>".$client."</span></td>");							// cleanup
	// get file info (size, data modified)
	
	echo ("<td class='td-name'><span class='countdown'>".$videofilename."</span></td>");					// isolating video filename	// cleanup
	echo ("<td class='td-size'><span class='countdown'>".$videofilesize." Mb</span></td>"); 				// filesize 				// cleanup
	
	if ($videoage<1) // ie video is still uploading...
		{
		$howlongago=" (in progress)";
		} 
	else 
		{
		$howlongago=" (".$videoage." mins ago)";
		}
	
	echo ("<td class='td-timestamp'><span class='countdown'>".$videouploaded." GMT</span><br />".$howlongago."</td>"); 	// time last modified GMT // cleanup

	exec ($ffmpeg_inspect); 											// run ffmpeg command to get info and output info into media info txt

	 {																	//
	 $ffmpeg_array = file($inspect_file);								// read media info txt into array
	 $howmany=count($ffmpeg_array);										// count no of items in ffmpeg file
	 
	 echo ("<td class='td-duration'>");	//cleanup
	 foreach ($ffmpeg_array as $ffmpeg_key) 	
	{ 
		if (strpos($ffmpeg_key,"Duration")) 
		    {
			$durationtemp=$ffmpeg_key;
			$durationtemp=str_replace("Duration:","",$durationtemp);
			$duration=stristr($durationtemp,", start",True);
			echo("<span class='countdown'>".$duration."</span>");
			
			}   //debug video details
	}
	echo ("</td>"); // cleanup
	 
	 	// write out ffmpeg inspect as array items
	echo ("<td class='td-ffmpeg'>[debug array of ".$howmany."] "); // cleanup
	for ($ffi = 11; $ffi <= $howmany-2; $ffi++) 
	{
	echo ($ffi." > ".$ffmpeg_array[$ffi]."<br>");					// too much info - pushes table row too tall - comment out to tidy tableview //cleanup
	} 
	echo ("</td>");	//cleanup
	 																	
	 } 																	
	// debug ffmpeg command to be executed
	$fileinfolong = file_get_contents($inspect_file);					// read ffmpeg output back into string

	$fileinfo=stristr($fileinfolong,"Duration"); 						// nuke everything before 'Duration'
	$fileinfo2=stristr($fileinfo,"At least one output file must be specified",True); // nuke everything after 'output file not specified'
	$fileaudio=stristr($fileinfo2,"Stream #0:1");			//
    $fileinfo3=stristr($fileinfo2,"Stream #0:1",True); 		//
	$filevideo=stristr($fileinfo3,"Stream #0:0");			//
	
	 if (strpos($filevideo,"Audio")) 	 // oops, swap video/audio variables if you find 'Audio' in the video variable
	 {
	 	$temp=$filevideo; 
	 	$filevideo=$fileaudio; 
	 	$fileaudio=$temp;
	 } 	
	 
	 $filevideo = stristr($filevideo,"Video"); 					// everything in video between 'video' and 'fps'
	 $filevideo2 = stristr($filevideo,"fps",True)."fps";
	 if ($filevideo2="fps") {} else {$filevideo=$filevideo2;}	// just in case 'fps' isnt present
	  														
	 $fileaudio = stristr($fileaudio,"Audio");					// everything in audio between 'audio' and next 'Stream'
	 $fileaudiotemp = stristr($fileaudio,"kb/s",True);
	 if  (!$fileaudiotemp)	 
	 	{
		$fileaudio = stristr($fileaudio,"Audio");
		 }	
	 	else
	 	{
		 $fileaudio = stristr($fileaudio,"kb/s",True)."kb/s";		
		} 
	 
	 // everything in video between 'video' and 'kb/s'
  	 
	 echo ("<td class='td-video'>".$filevideo."</td>"); 		// ffmpeg output relevant to videofile // cleanup
	 echo ("<td class='td-audio'>".$fileaudio."</td>"); 		// ffmpeg output relevant to videofile // cleanup

	$tempfiletobemoved=strtolower(str_replace($voduploaddir,"",$filefound));		// remove everything up to channel specific directory and convert to lowercase
	$tempfile2tobemoved=str_replace("/","-",$tempfiletobemoved);					// convert filename to be channel-filename
	$pathtoqueuedfile=$pathtoqueue.$tempfile2tobemoved;								// "/var/www/jonah/queue/" + corrected filename
	
	// make note in queue of filename and customer 									// flat file to begin with, move to mySql
	// add file to bottom of queue
	
	if ($videoage<1)																// check if file is either still uploading or just finished
{
	$filestatus=" UPLOADING... - ".$videofilesize."Mb</span><br /> as of ".$videouploaded." GMT </td><td class='td-icon'><img class='ftpstatus' src='images/uploading.png' /></td></tr>"; // status is uploading 
}
else
{
	if ($videoage<$minstowait)														// check if video is in waiting period
	{
		$filestatus=" WAITING FOR XML</span><br />waiting ".$videoage." mins </td><td class='td-icon'><img class='ftpstatus' src='images/waiting.png' /></td></tr>"; // status is queued
	} 
	else 
	{
		$loc1=$filefound;
		$loc2=str_replace(" ","_",$pathtoqueuedfile);								// remove spaces from target filename
		//echo ("<td class='td-status'>[debug path] moving file from <b>".$loc1."</b> to <b>".$loc2."</b></td>"); // debug rename
		
		
		rename($loc1,$loc2); 	  													// move file to queuing directory
		if (file_exists($loc2)) {$success=1;}										// check for existance of file, success=1 if file created, success=0 if file create error

		 		$add2queue = array();
		// 		populate with 	category 	client	 	filename 			filesize 		uploaded 	video 		audio 		duration
		//						"tvp"		$client		$tempfile2tobemoved $videofilesize	$timestamp	$filevideo	$fileaudio	$duration
				$add2queue['category'] 	= "TVP";
				$add2queue['client'] 	= $client;
				$add2queue['filename'] 	= $tempfile2tobemoved;
				$add2queue['filesize'] 	= $videofilesize;		
				$add2queue['timestamp'] = $videouploaded; 			
				$add2queue['video'] 	= $filevideo;
				$add2queue['audio'] 	= $fileaudio;
				$add2queue['duration'] 	= $duration;												
		//		open file, read array												//	open file, read array

		 		$queueString = json_encode($add2queue);								//	write $queue to json
				file_put_contents('/var/www/jonah/queue/queue.json', $queueString); // 	replace by mySql record update (add item to bottom of queue)

		$filestatus=" UPLOAD COMPLETE </span></td><td class='td-icon'><img class='ftpstatus' src='images/success.png' /></td></tr>"; // status is queued
	} // end videoage<5

} // end videoage<2
	
	echo ("<td class='td-status'><span class='countdown'>".$filestatus.""); 		// show filestatus (uploading, waiting for xml or queued)		// cleanup
	} 
	else
		{
		// non-media file
		echo ("<td><span class='countdown'>".$client."</span></td><td colspan='8'> ".$filefound." (not valid media type)</td><td><img src='images/unknown.png' /></td></tr>"); 			// file exists, but no match to allowed_video_type suffixes
		}
	// echo ("<tr><td colspan='10'>end of item [debug] ".$client.$videofilename.$videofilesize.$duration.$filevideo.$fileaudio."</td></tr>");	// end of individual item found	
	// put /tr here ?
	}
	echo ("<tr><td colspan='10'>end of client</td></tr>");	// end of client directory
 } // end for 'if file found'
 
 else
 	{
 	// echo ("No new files detected <br />");					// nothing in watch folder, do we need to echo anything?
 	} 

}	// end for each folder
	echo ("</table>");


?>
