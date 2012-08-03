<!-- queue view -->

<table id="table-3" class='jonahtable' width='100%'>
<tr class='tabhead-transcode nodrop nodrag'><td>&nbsp; <!-- drag icon --></td><td> Queue </td><td> EPG ID </td><td> Cat </td><td> Client </td><td> File </td><td> Size </td><td> Duration </td><td> Uploaded </td><td>&nbsp;</td><td> Source </td><td> Video </td><td> Audio </td><td> Status </td><td> State </td><td>&nbsp; <!-- encode state icon --></td></tr>

<!-- sample row -->

<!--<tr class='#transcoding nodrop nodrag'><td>&nbsp;</td><td> 0</td><td>pending</td><td>TVP</td><td> MTV-ASIA </td><td>playboy-catfight_dallas.mpg</td><td>300 Mb</td><td>01:20:00</td><td>July 23, 12:01</td><td>HD</td><td>1280x720</td><td>h264, 480x288, 997 kb/s, 24.99 fps</td><td>aac, 48 Khz, mono, s16, 63 kb/s</td><td>transcoding - 80%<br /><img src='/jonah/images/upload.png' /></td><td> 8 </td><td><img src='/jonah/images/transcode-live.png' /></td></tr>-->

<?
	$explodeID="*";																	// 	used to separate json-encoded arrays
	$progressBar=0;																
	$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 				// 	replace by mySql record update (add item to bottom of queue)
	//echo ("<span class='troubleshoot'>fullQueue: </span> ".$fullQueue."<br />");
	
	$queueExplode=explode($explodeID,$fullQueue);
	//for each $queueExplode as $queueVod
	
	foreach($queueExplode as $queueVod)												//	
	{
	if ($queueVod) 
		{
		// echo ("<span class='troubleshoot'>queueVod: </span>".$queueVod."<br />");
		// $queueVod=file_get_contents('/var/www/jonah/queue/queue.json'); 			//	read vod item variable from local json file
	 	$queue = json_decode($queueVod, true);										// 	json decode array
	
		// work out if SD or HD
		$vodHeight=0;
		$detectHD = $queue['video'];
		$whereisX = strrpos($detectHD,"x");											// find last x
		$resolution = substr($detectHD,$whereisX-4,9);
		$resolution = str_replace(",","",$resolution);								// strip x from height	
		$vodHeight = stristr($resolution,"x");
		$vodHeight = str_replace("x","",$vodHeight);								// strip x from height	
		//echo ("vodHeight: ".$vodHeight."<br />");
		//echo ("resolution: ".$resolution."<br />");
		
		if ($vodHeight>576) 
			{$sdorhd="HD";} 
		else 
			{$sdorhd="SD";}
			
		if (!$queue['epgid'])
		{
			$queue['epgid']='pending';
		}	
		
		// check if ffmpeg is processing item 
		// work out percentage done

?>
<tr id="<? echo $queue['queue-id']; ?>"><td>&nbsp;</td><td><? echo $queue['queue-id']; ?></td><td><? echo $queue['epgid']; ?></td><td><? echo $queue['category']; ?></td><td><? echo $queue['client']; ?></td><td><? echo $queue['filename']; ?></td><td><? echo $queue['filesize']; ?> Mb</td><td><? echo $queue['duration']; ?></td><td><? echo $queue['timestamp']; ?></td><td><? echo($sdorhd); ?></td><td><? echo ($resolution); ?></td><td><? echo ($queue['video']); ?></td><td><? echo ($queue['audio']); ?></td><td>
<? 
	// work out progress indicator based on encodeStatus
	if ($queue['transcodeState']>0) 
	{
	// work out $progressBar
	echo ("transcoding - ".$progressBar."% <br />"); 
	echo ("<img src='/jonah/images/transcode-live.png' />");
	}
	else 
	{
	echo ("queued<br /><img src='/jonah/images/queued.png' />");	
	}
?>
</td><td>
<? echo $queue['transcodeState']; ?></td><td>
<? 
	if ($queue['transcodeState']>0) 
	{ 
	echo ("<img src='/jonah/images/transcode-live.png' />");
	}
	else 
	{
	echo ("<img src='/jonah/images/transcode-q.png' />");	
	}
?>
</td></tr>
<?
		}	
		else
		{}		// if queueVOD=''
	}			// end foreach $queue

?>
</table>