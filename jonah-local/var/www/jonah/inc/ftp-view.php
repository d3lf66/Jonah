<?php

foreach(glob($voduploaddir.'*', GLOB_ONLYDIR) as $i)						// for each folder found in the 'voduploaddir' root ($i = '/home/sony' )
	{ 
	$watchfolder=$i."/";													// add trailing slash to folder
	$client = strtoupper(str_replace($voduploaddir,"",$i));   
	if (glob($watchfolder."*.*") != false)									// if files in upload folder
		 {
			echo ("<br /><table class='jonahtable' width='100%'><tr class='tabhead-upload'><td class='td-path'> Client </td><td class='td-name'> Filename </td><td class='td-size'> Size </td><td class='td-timestamp'> Timestamp </td><td class='td-duration'> Duration </td><!--<td class='td-ffmpeg'>[debug]</td>--><td class='td-video'> Video </td><td class='td-audio'> Audio </td><td class='td-status'> Status </td><td class='td-icon'></td></tr>");	 
		 
 			$filecount = count(glob($watchfolder."*.*"));
 			$filesfound = glob($watchfolder."*.*"); 						// get all files under watchfolder with a wildcard extension. (change for specific filetypes)

 	foreach($filesfound as $filefound)										// repeat for each file found in the watchfolder
	{
	// check for media detection 
	
	if (preg_match("/\.".$allowed_video_types."$/i",$filefound)) 			// does the file have an allowed video suffix? 
		{
		// inspect with ffmpeg
		$tempinfo=pathinfo($filefound);											// start with fullpath to file found in watchfolder (e.g. '/home/sony/Shakira Liar.mpg')
    	$videofilename=basename($filefound,'.'.$tempinfo['extension']);			// find filename without path and extension (e.g. 'Shakira Liar')
		$tempinspect_file=$pathtoinspect.$videofilename.".txt";					// full path path to 'ffmpeg -i' output (e.g. '/var/www/jonah/txtout/shakira liar.txt')
		$inspect_file=strtolower(str_replace(" ","_",$tempinspect_file));		// replace filename spaces with underscores and convert to lowercase (e.g. 'shakira_liar')
		$probe_file=$inspect_file."_probe";										// ffprobe
		$videofilesize=intval((filesize($filefound)/1000000));					// get uploaded video size in approx Mb
		$videouploaded=date("F d", filemtime($filefound)).", ".date("H:i", filemtime($filefound)); // formatted for column display
		$videoagefull=date("U",filemtime($filefound));
		$videoage=intval((($currenttime-$videoagefull)/60));					// how many full minutes difference between video uploaded and current time (e.g '2')
		$videotype=mime_content_type($filefound);								// get mime-type
   	 	$ffmpeg_inspect="ffmpeg -i '".$filefound."' 2> ".$inspect_file;			// ffmpeg command (e.g. 'ffmpeg -i '/home/sony/Shakira Liar.mpg' '/var/www/jonah/txtout/sony-shakira_liar.txt')
		$ffprobe_inspect="ffprobe -v quiet -print_format json -show_format -show_streams -i '".$filefound."'";
		$ffinfo=""; // starting point for ffmpeg array

	// get file info (size, data modified)
	
		if ($videoage<1) 												// ie video is still uploading...
			{
			$howlongago=" (in progress)";
			} 
		else 
			{
			$howlongago=" (".$videoage." mins ago)";
			}

		exec ($ffmpeg_inspect); 										// RUN FFMPEG COMMAND to get info and output info into media info txt
		$ffprobe = exec($ffprobe_inspect);								// comparison with ffprobe

	 {																	//
	 $ffmpeg_array = file($inspect_file);								// read media info txt into array
	 $howmany=count($ffmpeg_array);										// count no of items in ffmpeg file
	 
	 foreach ($ffmpeg_array as $ffmpeg_key) 	
	{ 
		if (strpos($ffmpeg_key,"Duration")) 
		    {
			$durationtemp=$ffmpeg_key;
			$durationtemp=str_replace("Duration:","",$durationtemp);
			$duration=stristr($durationtemp,", start",True);
			}   //debug video details
	}
	 
		for ($ffi = 11; $ffi <= $howmany-2; $ffi++) 									// for every item in the ffmpeg array, from 11 up to last 						
		{
		$ffinfo=$ffinfo.$ffi." > ".$ffmpeg_array[$ffi]."<br />";						// read ffmpeg array into single string, only for debug purposes
		} 
	} 																	

	// read back 'ffmpeg -i' results
	$fileinfolong = file_get_contents($inspect_file);									
	$fileinfo=stristr($fileinfolong,"Duration"); 										// nuke everything before 'Duration'
	$fileinfo2=stristr($fileinfo,"At least one output file must be specified",True); 	// nuke everything after 'output file not specified'
	$fileaudio=stristr($fileinfo2,"Stream #0:1");										//
    $fileinfo3=stristr($fileinfo2,"Stream #0:1",True); 									//
	$filevideo=stristr($fileinfo3,"Stream #0:0");										//
	
	 if (strpos($filevideo,"Audio")) 	 						// oops, swap video/audio variables if you find 'Audio' in the video variable
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
	 
	$tempfiletobemoved=strtolower(str_replace($voduploaddir,"",$filefound));		// remove everything up to channel specific directory and convert to lowercase
	$tempfile2tobemoved=str_replace("/","-",$tempfiletobemoved);					// convert filename to be channel-filename
	$pathtoqueuedfile=$pathtoqueue.$tempfile2tobemoved;								// "/var/www/jonah/queue/" + corrected filename
	
	if ($videoage<1)																// check if file is either still uploading or just finished
	{
		$filestatus=" UPLOADING... - ".$videofilesize."Mb<br /> as of ".$videouploaded." GMT "; // status is uploading
		$filestatus2="uploading";
	}
	else
	{
		if ($videoage<$minstowait)														// check if video is in waiting period
		{
			$filestatus=" WAITING FOR XML<br />waiting ".$videoage." mins "; 			// status is queued
			$filestatus2="waiting";
		} 
		else 
		{
			$loc1=$filefound;
			$loc2=str_replace(" ","_",$pathtoqueuedfile);								// remove spaces from target filename
		
			rename($loc1,$loc2); 	  													// MOVE UPLOADED FILE TO QUEUE DIRECTORY
			if (file_exists($loc2)) {$success=1;} else {$success=0;}					// check for existance of file, success=1 if file created, success=0 if file create error

		 		$add2queue = array();
		// 		populate with 	category 	client	 	filename 			filesize 		uploaded 	video 		audio 		duration
		//						"tvp"		$client		$tempfile2tobemoved $videofilesize	$timestamp	$filevideo	$fileaudio	$duration
		
				$add2queue['category'] 	= "TVP";
				$add2queue['client'] 	= $client;
				$add2queue['filename'] 	= str_replace(" ","_",$tempfile2tobemoved);		// filename (not suffix), in lower case
				$add2queue['filesize'] 	= $videofilesize;		
				$add2queue['timestamp'] = $videouploaded; 			
				$add2queue['video'] 	= $filevideo;
				$add2queue['audio'] 	= $fileaudio;
				$add2queue['duration'] 	= $duration;
				$add2queue['epgid'] 	= '';
				$add2queue['transcodeState'] 	= 0;
				
		//		tidy up audio and video
		
		$detectHD=$add2queue['video'];
		$detectAudio=$add2queue['audio'];		
		$comma=",";
		// work out vcodec
		$codec1 = stristr($detectHD,"Video: ");				// everything after ' '
		$codec1 = str_replace("Video: ","",$codec1);		// everything after ' '
	    $codec = stristr($codec1," ",True); 				// everything before space
		
		// work out acodec
		$acodec1 = stristr($detectAudio,"Audio: ");			// everything after ' '
		$acodec = str_replace("Audio: ","",$acodec1);		// everything after ' '
	
		// work out if SD or HD
		$whereisX = strrpos($detectHD,"x");					// find last x
		$resolution = substr($detectHD,$whereisX-4,9);
		$vodHeight=stristr($resolution,"x");
		$resolution=str_replace($comma," ",$resolution);	// strip trailing commas
	
		// work out fps
		$whereisX = strrpos($detectHD,"fps");				// find last fps
		$framerate1 = substr($detectHD,$whereisX-7,10);		//
		$framerate = stristr($framerate1," ");				// everything after ' '
		$framerate=str_replace($comma," ",$framerate);		// strip trailing comma	
	
		// work out video bitrate
		$whereisX = strrpos($detectHD,"kb/s");				// find last kb/s
		$bitrate1 = substr($detectHD,$whereisX-10,14);		//
		$bitrate = stristr($bitrate1," ");					// everything after ' ' 
		$bitrate=str_replace($comma," ",$bitrate);		    // strip trailing comma	
		
		$add2queue['duration'] = substr($add2queue['duration'],0,-3);						// remove 100ths/sec from duration ie ':00'		
		$add2queue['audio']	= $acodec;														// removed "Audio: "
		$add2queue['video'] = $codec.", ".$resolution.", ".$framerate.", ".$bitrate;		// neater video element
					
		//		open file, read array															//	open file, read array
		//		below happens very quickly - still, is there scope to lock the file in case another script modifies it between reading, appending and writing?
	
				$explodeID="*";																	// 	used to separate json-encoded arrays
				$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 				// 	replace by mySql record update (add item to bottom of queue)
				$explodeIDcount=substr_count($fullQueue,$explodeID);							//  count number of explodeIDs in $fullQueue
				$add2queue['queue-id'] = $explodeIDcount+1;										// 	add new $add2queue['queue-id'] item with value of explodeIDcount+1
				// generate uniqueID? 
				$queueString = json_encode($add2queue);											
				$newQueue=$fullQueue.$explodeID.$queueString;									//	ADD ITEM $queue to QUEUE json (one explodeID per item)
				file_put_contents('/var/www/jonah/queue/queue.json', $newQueue); 				// 	replace by mySql record update (add item to bottom of queue)

				$filestatus=" UPLOAD COMPLETE"; // status is queued
				$filestatus2="success";
		} 	// end videoage<5

	} 		// end videoage<1
	
	// show upload of media file in a table row
	echo ("<tr>");																																		// cleanup
	echo ("<td class='td-name'><span class='countdown'>".$client."</span></td>");					// isolating video filename							// cleanup
	echo ("<td class='td-path'><span class='countdown'>".$videofilename."</span></td>");																// cleanup
	echo ("<td class='td-size'><span class='countdown'>".$videofilesize." Mb</span></td>"); 		// filesize 										// cleanup
	echo ("<td class='td-timestamp'><span class='countdown'>".$videouploaded." GMT</span><br />".$howlongago."</td>"); 	// time last modified GMT 		// cleanup
	echo ("<td class='td-duration'><span class='countdown'>".$duration."</span></td>");
	//echo ("<td class='td-ffmpeg'>[debug array]</td>");
	// echo ("<td class='td-ffmpeg'>debug array of ".$ffinfo."</td>"); 			// ffmpeg																// cleanup
	// echo ("<td class='td-ffmpeg'>debug array of ".$ffprobe."</td>"); 		// ffprobe																	
  	echo ("<td class='td-video'>".$filevideo."</td>"); 												// ffmpeg output relevant to videofile 				// cleanup
	echo ("<td class='td-audio'>".$fileaudio."</td>"); 												// ffmpeg output relevant to videofile 				// cleanup
	echo ("<td class='td-status'><span class='countdown'>".$filestatus."</span></td>");							// show filestatus text
	echo ("<td class='td-icon'><img class='ftpstatus' src='/jonah/images/".$filestatus2.".png' /></td>"); 		// show filestatus icon
	echo ("</tr>");
	
	} // end of media file detected
	else
		{
		// process non-media file

		$fileagefull=date("U",filemtime($filefound));
		$fileage=intval((($currenttime-$fileagefull)/60));
		$filetarget=str_replace($watchfolder,"",$filefound);
		$client=str_replace(" ","",$client);								// strip leading space from client
		$loc3=strtolower(str_replace(" ","_",$client."-".$filetarget));		// convert to lower case, add client for orphan files ie '/home/mtv/FRED BLOggs.XML' becomes 'mtv-fred_bloggs.xml'
		echo ("<tr><td><span class='countdown'>".$client."</span></td><td colspan='8'> ".$filefound." (not valid media type - ".$fileage." mins ago)</td><td><img src='/jonah/images/unknown.png' /></td></tr>");
		
			if ((preg_match("/\.".$allowed_xml_types."$/i",$filefound)) && ($fileage>$minstowait)) // check for allowed xml types that have been 5 mins
			{
				rename($filefound,$pathtoxml.$loc3);				// move xml to '/var/www/jonah/xml/'
			}
			else 
			{
				if ($fileage>$minstowait) 
				{
				rename($filefound,$orphanpath.$loc3);		// move non media, non-xml to '/var/www/jonah/orphans/'
				}
			} // end xml type
		}
	}
	echo ("<tr><td colspan='10'>end of client</td></tr>");			// end of client directory
	echo ("</table><br />");
 } // end for 'if file found'
 
 else
 	{
 	// echo ("No new files detected <br />");						// nothing in watch folder, do we need to echo anything?
 	} 

}	// end for each folder
?>