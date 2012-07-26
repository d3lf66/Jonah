<img height="1" src="/jonah/images/spacer.gif" width="200" /><br /><img src="/jonah/images/jonah.png" /><br />
<?php
	
	echo ("<img src='/jonah/images/".$diskimagetouse.".png' /><br />");									// use representative image of diskspace
	echo ("<span class='countdown'>".$diskfree." Gb FREE</span> / ".$disktotal." Gb ");				// show diskfreespace in Gb
	echo ("(".$diskpercent."%)<br />");																//show percentage free
	echo ("Last update at: ".$formattedtime);
?>
<br />Updating in <span id="countdown" class="countdown"><? echo($refreshtime); ?></span> secs<br /><br />