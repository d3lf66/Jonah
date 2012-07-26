<?
$adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file
$config = json_decode($adminVar, true);								// json decode array

$pathtoqueue= $config['pathtoqueue'];		// where queued files sit
$pathtoxml	= $config['pathtoxml'];			// where xml (category) sits (to catalog after process)
$logpath 	= $config['logpath'];			// where to log 
$parallel	= $config['parallel'];			// how many concurrent transcodes
$passes 	= $config['passes'];			// how many passes for each transcode  

$fireandforget = "> /dev/null 2>/dev/null &" ; // add to exec (ffmpeg) call to make asynchronous (mmmm!)

	$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 				// 	replace by mySql record update (add item to bottom of queue)
	
	$queueExplode=explode($explodeID,$fullQueue);
	//for each $queueExplode as $queueVod
	
	foreach($queueExplode as $queueVod)												//	
	{
		$queue = json_decode($queueVod, true);								// json decode array

// 		for every item in transcode queue up to number of $parallel transcodes
// 		get category		// ie 'TVP'
//		get encoding profile of category // (question: how do we do multiple profiles?)
// 		switch ($transcodeState) 
//			{
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
}
?>