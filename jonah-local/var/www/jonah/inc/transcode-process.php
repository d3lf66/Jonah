<?
//	reference exec(ffmpeg -i $input $profile $pass-specific $output $fireandforget)

$adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file
$config = json_decode($adminVar, true);								// json decode array

$pathtoqueue= $config['pathtoqueue'];		// where queued files sit
$pathtoxml	= $config['pathtoxml'];			// where xml (category) sits (to catalog after process)
$logpath 	= $config['logpath'];			// where to log 
$parallel	= $config['parallel'];			// how many concurrent transcodes
$passes 	= $config['passes'];			// how many passes for each transcode
$outputpath	= $config['outputpath'];		// output path for FMS vod directory 
$presetpath	= $config['presetpath'];		// where to pick up encoding profiles (presets)

$fireandforget = "> /dev/null 2>/dev/null &" ; // add to exec (ffmpeg) call to make asynchronous (mmmm!)

	$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 				// 	replace by mySql record update (add item to bottom of queue)
	//echo("<b>fullqueue: </b>".$fullQueue."<br />");
	$explodeID="*";																	// 	used to separate json-encoded arrays
	$explodeIDcount=substr_count($fullQueue,$explodeID);							//  count number of explodeIDs in $fullQueue
	
	$queueExplode=explode($explodeID,$fullQueue);									// explode queue.json by '*' into arraye
	//for each $queueExplode as $queueVod
	$i=0;
	
	foreach($queueExplode as $queueVod)												//	only up to $passes
	{
		if ($queueVod)
		{
		
		$i++;	
		$queue = json_decode($queueVod, true);										// json decode array
		//echo ("i: ".$i."<br />");
		
		$filename 		= $queue['filename'];	
		$tempinfo		= pathinfo($filename);												
    	$videofilename	= basename($filename,'.'.$tempinfo['extension']);			// find filename without path and extension (e.g. 'Shakira Liar')		// strip out
		$videofilename  = str_replace(" ","_",$videofilename); 						// for early compatibility, converts spaces to underscores
		$transcodeState = $queue['transcodeState'];
		$queueid 		= $queue['queue-id'];
		$cat 			= $queue['category'];
		$client 		= $queue['client'];
		$epgid			= $queue['epg-id'];
		
		if ($i<=$parallel)																		// check if queueID is supposed to be processed or not
		{
				// get epg-id (if xml exists, get it from there, if not, generate random?)	
			if (!$epgid)
		  		{
				// generate unique value
				$randVal=date("ymdHis");			// random epg starts with date/time stamp eg '120801142059' ie 2012, Aug, 1st, 14:20 GMT, 59 seconds
				$randVal=$randVal.rand(0,999);		// append random number from 0-999 in case more than 1 item being processed at the same timestamp
				//echo ($randVal."<br />");
				$epgid=$randVal;
				// assign 'orphan' epgid			// how to open file and add epgid?
		  		} // end if !$epgid
		  
		//echo ($i." isnt greater than ".$parallel." so process ffmpeg pass<br />");	
		//echo ("queue-id: ".$queueid."<br />");
		//echo ("videofilename: ".$videofilename."<br />");
		
		// load preset for $client
		$presettoget = strtolower($presetpath.$cat."-preset.json");
		//echo ("<br /><b>presettoget:</b> ".$presettoget."<br />");
		$fullPreset=file_get_contents($presettoget); 											// 	replace by mySql record update (add item to bottom of queue)
		//echo ("<br /><b>fullPreset:</b> ".$fullPreset."<br />");
		$presetExplode=explode($explodeID,$fullPreset);											// explode preset by '*'
		
		$tempjsonProfile = $presetExplode[1];
		//echo ("<br /><b>tempjsonProfile:</b> ".$tempjsonProfile."<br />");					// [debug: get first profile, '1' will vary depending on transcodeState]
		$jsonProfile = 	json_decode($tempjsonProfile, true);
		//echo ("<br /><b>jsonProfile:</b> ".$jsonProfile."<br />");
		//echo ("<br /><b>jsonProfile['-c:v']</b>: ".$jsonProfile['-c:v']."<br /><br />");	
		
		// put together ffmpeg parameters from preset profile
		
		if ($jsonProfile['-c:v']=='none')															// if no video, ie audio-only (usually for iOS requirement) 
		{
			$part1='';
		}
		else 
		{
			$part1=' -c:v '.$jsonProfile['-c:v'].' -b:v '.$jsonProfile['-b:v'].' -s '.$jsonProfile['-s'].' -r '.$jsonProfile['-r'].' -g '.$jsonProfile['-g'].' '.$jsonProfile['addition1'];
		}
		
		if ($jsonProfile['-c:a']=='none')							// if no audio, ie video only (usually for analyse pass)
		{
			$part2=' -an';
		}
		else 
		{
			$part2=' -c:a '.$jsonProfile['-c:a'].' -ac '.$jsonProfile['-ac'].' -ar '.$jsonProfile['-ar'].' -b:a '.$jsonProfile['-b:a'].' '.$jsonProfile['addition2'];
		}
			
		// get bitrate ie $passBitrate
		$passBitrate = $jsonProfile['-b:v'];													// read bitrate from preset? use '280' to test
		$suffix = $jsonProfile['container'];													// e.g. mp4
		
		$videofilename=$passBitrate."/".$videofilename;											// add bitrate suffix ie 'shakira_liar-280.mp4' or '/280/shakira_liar.mp4'
		$inputVOD = "'".$pathtoqueue.$filename."'";
		$inputVOD  = str_replace(" ","_",$inputVOD);											// converts spaces to underscores (this problem only exists for legacy test files)
		//echo ("inputVod: ".$inputVOD."<br />");
		$outputDir = strtolower($outputpath.$cat."/".$client."/".$passBitrate."/");
		//echo "<b>outputDir</b>: ".$outputDir."<br />";
		$outputVOD = strtolower("'".$outputpath.$cat."/".$client."/".$videofilename.".".$suffix."'");	// compile full output path, converting to lowercase
		$outputVOD = str_replace(" ","_",$outputVOD);											// strip spaces from output paths
		//echo ("outputVod: ".$outputVOD."<br />");
		
		// 
 		switch ($transcodeState) 
			{
 			case 0: 	// nothing started
						// check for encoding profile based on category
//		  		async call to exec (ffmpeg) pass 1 // kick off encoding pass1

 				$ffmpegCall = "ffmpeg -i ".$inputVOD." ".$part1." ".$part2." ".$outputVOD." ".$fireandforget;
				//echo ("<span class='countdown'>exec(".$ffmpegCall.")</span><br /><br />");
				
				if(!file_exists($outputDir)) 				// check if directory exists, if not make it
					{mkdir($outputDir, 0777, true);}
				
				exec($ffmpegCall);							// run ffmpeg command line
				$transcodeState=1; 							// ie process started
				// echo ("updating transcodeState to 1 on queue-id '".$i."'"); 						// update transcodeState in queue.json
				
				if (file_exists($outputVOD))				// check if file exists for debugging (it is assumed an asynch call will return a '0' 
				{
				//	echo ("transcode OK - ".$outputVOD);
				}
				else 
				{
				//	echo ("no VOD output");
				}

			break;	
    	
//  		case 1: 	// first pass started
//        		if (!logfile entry) // ie check if first pass is complete
//      		{
//					do nothing; (or somehow work out percentage done)
//				}
//       		else
//				{
//					$transcodeState=2;	// ie first pass complete
//					update status; ie 1/2 or 1/$passes
//				}
//		 	break;

// 			case 2: 	// first pass complete
//				if ($passes>1)
//				{
//		     		check for encoding profile based on category
//					async call to exec (ffmpeg) pass 2 // kick off encoding pass2
//					$transcodeState=3; // ie process started
//				} 
//				else
//				{}
//			break;
	
//			case 3: 	// second pass started
//				if (!logfile entry)
//				{
//			 		do nothing; (or somehow work out percentage done)
//				}
//				else 
//				{
//					log trancode as success (or restart process for multiple profiles)
//					remove item from queue
//					shuffle queue up one
//				}		
//	 		break;
			
			} 	// end case/switch
			
			}	// end if queue-ID<$parallel 
		}		// end if queueVod
	} 			// end foreach queueVod
?>