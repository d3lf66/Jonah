	
<?php
	$currenttime=date("U");												// get timestamp
	$result = $_REQUEST["table-3"];

	$adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	// read admin variable from local json file (use mySql db later)
	$config = json_decode($adminVar, true);								// json decode array
	
	$fullQueue=file_get_contents('/var/www/jonah/queue/queue.json'); 	// 	replace by mySql record update (add item to bottom of queue)
	$queueAgefull=date("U",filemtime('/var/www/jonah/queue/queue.json'));
	$queueAge=intval((($currenttime-$queueAgefull)/60));					// how many full minutes difference between video uploaded and current time (e.g '2')
	
	if ($queueAge<1) 													// for safety, dont allow queue-ordering if queue has been updated less than 60 secs ago
	{
		echo ("Failed to update - queue recently updated");
		break;
	}

	$explodeID="*";														// 	used to separate json-encoded arrays
	$explodeIDcount=substr_count($fullQueue,$explodeID);				//  count number of explodeIDs in $fullQueue
	
	$queueExplode=explode($explodeID,$fullQueue);						// explode queue.json by '*' into array
	$i=0;
	$newQueue="";
	
	// re-order lines according to $result
	
	foreach($result as $value) 
	{

		if (!$queueExplode[$value])
		{}
		else 
		{
			$i++;
			// echo ("<b>$i</b> ".$queueExplode[$value]."<br />");		// debug
			$tmp = $queueExplode[$value];
			$jQueue=json_decode($tmp,true);			
			$jQueue['queue-id']=$i;										// changes queue-id
			$correctedQueueItem = json_encode($jQueue,true);
			$newQueue=$newQueue."*".$correctedQueueItem;
		}
	//$i=$value;
	}
	
	
	//echo ("<b>Saving</b>: ".$newQueue);
	file_put_contents('/var/www/jonah/queue/queue.json', $newQueue); 
	
	if ($queueAge<1) 													// for safety, dont allow queue-ordering if queue has been updated less than 60 secs ago
	{
		echo ("Failed to update - please wait 60 seconds");
		break;
	}
	else
	{	// only refresh page if update was successful
?>
<script>
self.location.reload(true);
</script>
<?
	}
?>