<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 // temp storing on 37.188.116.67 (rackspace)
	 $debug=1;															// debug = show working paths, variables etc (not currently implemented)
	 $refreshtime=60;	 	 											// determines how often watchfolders get prodded
	 $currenttime=date("U");											// get local time for use in detecting age of files
	 // $formattedtime=date("F j, Y, g:i a");
	 $formattedtime=date("g:i a")." GMT<br />".date("F j");
	// load below parameters from admin.json file						// suggest using mySql DB later
 
	$adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file (use mySql db later)
	$config = json_decode($adminVar, true);								// json decode array
	
	$voddrive = $config['voddrive'];									// set disk array used for vod storage
	$voduploaddir = $config['voduploaddir'];							// set disk path used for vod upload
	$pathtoinspect = $config['pathtoinspect'];							// ffmpeg inspection text files go here (temp folder
	$pathtoqueue = $config['pathtoqueue'];								// place files to be transcoded here
	$pathtoxml = $config['pathtoxml'];									// place associated xml here
	$allowed_video_types = $config['allowedvideotypes'];				// set allowed video types to process
	$allowed_xml_types = $config['allowedxmltypes'];					// set allowed xml suffixes
	$minstowait = $config['minstowait'];								// how long to wait for xml before queuing for transcode
	$orphanpath = $config['orphanpath'];								// path to orphans (non-media, non-xml)
	$duration='unknown';
	
	if (!$voddrive) {echo ("error reading config.json<br />ending script to protect file structure"); break;}	// if config.json is corrupt, terminate script
																
	$diskfree=round((disk_free_space($voddrive)/1000000000),1);			// get disk-unused in Gbytes
	$disktotal=round((disk_total_space($voddrive)/1000000000),1);		// get disk-size in Gbytes
	$diskpercent = round(($diskfree/$disktotal*100));
	$diskpercentused=100-$diskpercent;
	$diskimagetouse=intval($diskpercentused/10)*10;						// use closest disk image to capacity used (ie under 10%, 10-19%, 20-29% etc)	 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Jonah - file ingest engine (now in GitHub)</title>
    <link href="/jonah/css/tablednd.css" type="text/css" rel="stylesheet">
	<link href="/jonah/css/main.css" type="text/css" rel="stylesheet">
    <script src="/jonah/scripts/libraries.js" type="text/javascript"></script>
    <META HTTP-EQUIV="MSThemeCompatible" Content="Yes">
    <meta http-equiv="refresh" content="<? echo($refreshtime); ?>">
</head>

<body>
<table cellpadding="0" cellspacing="0" class="jonahtable" width="95%">
<tr valign="top">	
<td>

<!-- // sidebar -->
<? include ('/var/www/jonah/inc/sidebar.php'); ?>

</td>
<td>

<!-- // mockup for ftp queue -->
<? include ('/var/www/jonah/inc/ftp-view.php'); ?>

<br /><br />

<!-- // mockup for transcode queue view only -->
<? include ('/var/www/jonah/inc/queue-view.php'); ?>

<!-- // -->
</td><!-- end of content cell in main table // -->
</tr>

<tr><td align="left" valign="bottom"><form valign="bottom"><INPUT class="softbutton" onClick="showDebug()" value="showDebug()" type="button" disabled="disabled"></form></td><td align="center">
<? include ('/var/www/jonah/inc/adminfooter.php'); ?>
</td></tr>
</table>

</body>
</html>	