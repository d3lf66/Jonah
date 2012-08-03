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
	echo("<b>fullqueue: </b>".$fullQueue."<br />");
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
		echo ("i: ".$i."<br />");
		
		$filename 		= $queue['filename'];	
		$tempinfo		= pathinfo($filename);												
    	$videofilename	= basename($filename,'.'.$tempinfo['extension']);			// find filename without path and extension (e.g. 'Shakira Liar')		// strip out 
		$transcodeState = $queue['transcodeState'];
		$queueid 		= $queue['queue-id'];
		$cat 			= $queue['category'];
		$client 		= $queue['client'];
		$epgid			= $queue['epg-id'];
		
		// get epg-id ?	
		if (!$epgid)
		  {
			// assign 'orphan' epgid
			// generate unique value
			$randVal=date("ymdHis");
			$randVal=$randVal.rand(0,999);
			echo ($randVal."<br />");
			$epgid=$randVal;
		  }
		
		echo ("queue-id: ".$queueid."<br />");
		echo ("videofilename: ".$videofilename."<br />");
		
		// load preset for $client
		$presettoget = $presetpath.$client."-preset.json";
		$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 						// 	replace by mySql record update (add item to bottom of queue)
		
		// get bitrate ie $passBitrate
		$passBitrate = "280";																	// read bitrate from preset? use '280' to test
		$suffix = "mp4";																		// work out suffix from preset? use 'mp4' to test
		
		$videofilename=$passBitrate."/".$videofilename;											// add bitrate suffix ie 'shakira_liar-280.mp4' or '/280/shakira_liar.mp4'
		$inputVOD = "'".$pathtoqueue.$filename."'";
		echo ("inputVod: ".$inputVOD."<br />");
		$outputVOD = strtolower("'".$outputpath.$cat."/".$client."/".$videofilename.".".$suffix."'");	// compile full output path, converting to lowercase
		$outputVOD = str_replace(" ","_",$outputVOD);											// strip spaces from output paths
		echo ("outputVod: ".$outputVOD."<br />");
		
		// 
 		switch ($transcodeState) 
			{
// 			case 0: 	// nothing started
//      		check for encoding profile based on category
//		  		async call to exec (ffmpeg) pass 1 // kick off encoding pass1
//				$transcodeState=1; // ie process started
//			break;	
    	
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
		}		// end if queueVod
	} 			// end foreach queueVod
?>