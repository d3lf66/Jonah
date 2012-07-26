<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 $formattedtime=date("F j, Y, g:i a");
	 $userip=$_SERVER['REMOTE_ADDR'];
	 
	 $adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file
	 $config = json_decode($adminVar, true);							// json decode array
	 
	$voddrive = $config['voddrive'];								// set disk array used for vod storage
	$voduploaddir = $config['voduploaddir'];						// set disk path used for vod upload
	$pathtoinspect = $config['pathtoinspect'];						// ffmpeg inspection text files go here (temp folder
	$pathtoqueue = $config['pathtoqueue'];							// place files to be transcoded here
	$pathtoxml = $config['pathtoxml'];								// place associated xml here
	$allowed_video_types = $config['allowedvideotypes'];			// set allowed video types to process
	$allowed_xml_types = $config['allowedxmltypes'];				// set allowed xml suffixes
	$minstowait = $config['minstowait'];							// how long to wait for xml before queuing for transcode
	$lastupdatedby = $config['9'];									// read IP address of last person to modify admin config file
	$orphanpath = $config['orphanpath'];							// set allowed video types to process
	$logpath = $config['logpath'];									// set allowed xml suffixes
	$parallel = $config['parallel'];								// how long to wait for xml before queuing for transcode
	$passes = $config['passes'];									// read IP address of last person to modify admin config file
	 
	$diskfree=round((disk_free_space($voddrive)/1000000000),1);		// get disk-unused in Gbytes
	$disktotal=round((disk_total_space($voddrive)/1000000000),1);	// get disk-size in Gbytes
	$diskpercent = round(($diskfree/$disktotal*100));
	$diskpercentused=100-$diskpercent;
	$diskimagetouse=intval($diskpercentused/10)*10;					// use closest disk image to capacity used (ie under 10%, 10-19%, 20-29% etc)	 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Jonah - TVPlayer ingest monitor</title>
	<link href="/jonah/css/main.css" type="text/css" rel="stylesheet">
	<META HTTP-EQUIV="MSThemeCompatible" Content="Yes">
	<script type="text/javascript" src="/jonah/scripts/libraries.js"></script>

</head>

<body>
<table cellpadding="0" cellspacing="0" class="jonahtable" width="95%">
<tr valign="top">	
<td><img height="1" src="../images/spacer.gif" width="200" /><br /><img src="../images/jonah.png" /><br />
<?php
	
	echo ("<img src='/jonah/images/".$diskimagetouse.".png' /><br />");									// use representative image of diskspace
	echo ("<span class='countdown'>".$diskfree." Gb FREE</span> / ".$disktotal." Gb ");				// show diskfreespace in Gb
	echo ("(".$diskpercent."%)<br />");																//show percentage free
	echo ("Last update at: ".$formattedtime." GMT<br /><br />");

// check memory usage 

    //function echo_memory_usage() 
	//{ 
        $mem_usage = memory_get_usage(true); 
        
        if ($mem_usage < 1024) 
            echo $mem_usage." bytes used by script"; 
        elseif ($mem_usage < 1048576) 
            echo round($mem_usage/1024,2)." kilobytes"; 
        else 
            echo round($mem_usage/1048576,2)." megabytes"; // convert to Mbytes
        echo "<br/>"; 
    //}
	
?>

</td>
<td>
<?
//file_put_contents('/var/www/jonah/admin/admin.json', $filestobestored);
?>

<span class='countdown'>Jonah Administrative Page</span>
<br /><br />
<!-- outer container hidden-->

<form class='adminini' action="/jonah/admin/update.php" method="POST" name="configForm">
<table class='jonahtable'>
<tr class='tabhead-admin'><td>DESCRIPTION </td><td>&nbsp; </td><td> VALUE </td><td>&nbsp; </td><td> [debug] variable name </td><td> LOCKED </td></tr>

<tr><td colspan='6'><span class='admintitle'>-- General --</span></td></tr>

<tr><td>root path to disk array used for vod storage </td><td>&nbsp; </td><td><input name="voddrive" class='adinput' type='text'  value='<? echo ($voddrive);?>' /></td><td>&nbsp; </td><td> $voddrive</td><td><img src='/jonah/images/locked.png' onClick='showVod();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to vod upload parent directory </td><td>&nbsp; </td><td><input name="voduploaddir" class='adinput' type='text'  value='<? echo ($voduploaddir);?>' /></td><td>&nbsp; </td><td> $voduploaddir</td><td><img src='/jonah/images/locked.png' onClick='showUpload();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to ffmpeg debug files </td><td>&nbsp; </td><td><input name="pathtoinspect" class='adinput' type='text'  value='<? echo ($pathtoinspect);?>' /></td><td>&nbsp; </td><td> $pathtoinspect</td><td><img src='/jonah/images/locked.png' onClick='showInspect();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to transcode queue folder </td><td>&nbsp; </td><td><input name="pathtoqueue" class='adinput' type='text'  value='<? echo ($pathtoqueue);?>' /></td><td>&nbsp; </td><td> $pathtoqueue</td><td><img src='/jonah/images/locked.png' onClick='showQueue();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to associated xml </td><td>&nbsp; </td><td><input name="pathtoxml" type='text'  class='adinput' value='<? echo ($pathtoxml);?>' /></td><td>&nbsp; </td><td> $pathtoxml</td><td><img src='/jonah/images/locked.png' onClick='showXml();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to orphans </td><td>&nbsp; </td><td><input name="orphanpath" type='text' class='adinput' value='<? echo ($orphanpath);?>' /></td><td>&nbsp; </td><td> $orphanpath</td><td><img src='/jonah/images/locked.png' onClick='this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>root path to transcode log </td><td>&nbsp; </td><td><input name="logpath" type='text' class='adinput' value='<? echo ($logpath);?>' /></td><td>&nbsp; </td><td> $logpath</td><td><img src='/jonah/images/locked.png' onClick='this.src="/jonah/images/unlocked.png"' /></td></tr>

<tr><td colspan='6'><span class='admintitle'>-- Config --</span></td></tr>

<tr><td>allowed video types by suffix</td><td>&nbsp; </td><td><input name="allowedvideotypes" type='text'  class='adinput' value='<? echo ($allowed_video_types);?>' /></td><td>&nbsp; </td><td> $allowed_video_types</td><td><img src='/jonah/images/locked.png' onClick='showVideotypes();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>allowed xml type by suffix </td><td>&nbsp; </td><td><input name="allowedxmltypes" type='text'  class='adinput' value='<? echo ($allowed_xml_types);?>' /></td><td>&nbsp; </td><td> $allowed_xml_types</td><td><img src='/jonah/images/locked.png' onClick='showXmlTypes();this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>minutes to wait for associated xml </td><td>&nbsp; </td><td><input name="minstowait" type='text'  class='adinput' value='<? echo ($minstowait);?>' /> <input name="var9" type='hidden' value="<? echo ($userip); ?>" /></td><td>&nbsp; </td><td> $minstowait</td><td><img src='/jonah/images/locked.png' onClick='showMins();this.src="/jonah/images/unlocked.png"' /></tr>

<tr><td colspan='6'><span class='admintitle'>-- Transcode --</span></td></tr>

<tr><td>no. of parallel transcodes </td><td>&nbsp; </td><td><input name="parallel" type='text' class='adinput' value='<? echo ($parallel);?>' /></td><td>&nbsp; </td><td> $parallel</td><td><img src='/jonah/images/locked.png' onClick='this.src="/jonah/images/unlocked.png"' /></td></tr>
<tr><td>no. of transcode passes </td><td>&nbsp; </td><td><input name="passes" type='text' class='adinput' value='<? echo ($passes);?>' /></td><td>&nbsp; </td><td> $passes</td><td><img src='/jonah/images/locked.png' onClick='this.src="/jonah/images/unlocked.png"' /></td></tr>

<tr><td colspan='6'><br /><br />Last updated by: <? echo ($config['9']); ?></td></tr>
<tr><td>&nbsp; </td><td>&nbsp; </td><td align="right">check changes before submitting >> </td><td>&nbsp; </td><td> <INPUT class="softbutton" 
onClick="showVod();showUpload();showInspect();showQueue();showXml();showVideotypes();showXmlTypes();showMins();document.forms["configForm"].submit();" value="submit changes" type="submit" action="/jonah/admin/update.php" /></td><td> </td></tr></table>

</form>

<!-- end of content cell in main table // -->
</td></tr>

<tr><td></td><td align="center">
<? include ('/var/www/jonah/inc/adminfooter.php'); ?>
</td></tr>
</table>

</body>
</html>	
