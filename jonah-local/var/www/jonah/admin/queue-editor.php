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
	$lastupdatedby = $config['9'];										// read IP address of last person to modify admin config file
	
	if (!$voddrive) {echo ("error reading config.json<br />ending script to protect file structure"); break;}	// if config.json is corrupt, terminate script
																
	$diskfree=round((disk_free_space($voddrive)/1000000000),1);			// get disk-unused in Gbytes
	$disktotal=round((disk_total_space($voddrive)/1000000000),1);		// get disk-size in Gbytes
	$diskpercent = round(($diskfree/$disktotal*100));
	$diskpercentused=100-$diskpercent;
	$diskimagetouse=intval($diskpercentused/10)*10;						// use closest disk image to capacity used (ie under 10%, 10-19%, 20-29% etc)	 

		// use this for single array item
	 $vodHeight=0;
	 $queueVod=file_get_contents('/var/www/jonah/queue/queue.json'); 	//read vod item variable from local json file
	 $queue = json_decode($queueVod, true);								// json decode array
	// check for absence of queue (or array of 0 items), populate with dummy data
	// repeat for each item in queueArray until done
	// work out resolution (XXXXxYYYY ie 720x576 OR 1280x720)
	
	$detectHD=$queue['video'];
	$comma=",";
	
	// work out if SD or HD
	$whereisX = strrpos($detectHD,"x");				// find last x
	$resolution = substr($detectHD,$whereisX-4,9);
	$vodHeight=stristr($resolution,"x");
	$resolution=str_replace($comma," ",$resolution);	// strip trailing commas
	//echo ("resolution: ".$resolution."<br />");

	if ($vodHeight>576) 
	{$sdorhd="HD";} 
	else 
	{$sdorhd="SD";}
	// check if ffmpeg is processing item 
	// work out percentage done
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Jonah - file ingest engine (now in GitHub)</title>

    <link href="/jonah/css/tablednd.css" type="text/css" rel="stylesheet">
 	<link href="/jonah/css/main.css" type="text/css" rel="stylesheet">   
	<script src="/jonah/scripts/libraries.js" type="text/javascript"></script>
    <? include ('/var/www/jonah/inc/jquery.htm'); ?>
    <script src="/jonah/js/jquery.tablednd.js" type="text/javascript"></script>

	<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
	// Core Code by Denis Howlett
	// http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/

		$('#table-3').tableDnD({
		onDrop: function(table, row) {
		// alert("Re-ordering on server");
		// alert("Result of $.tableDnD.serialise() is "+$.tableDnD.serialize());		    
		$('#AjaxResult').load("/jonah/server/ajaxTest.php?"+$.tableDnD.serialize());        
		}
	});
	// Make a nice striped effect on the table
	$("#table-3 tr:even").addClass("alt");
 	// Initialise the second table specifying a dragClass and an onDrop function that will display an alert   
	$("#table-3 tr").hover(function() {
          $(this.cells[0]).addClass('showDragHandle');
    }, function() {
          $(this.cells[0]).removeClass('showDragHandle');
    }); 
})		
	</script>
    <META HTTP-EQUIV="MSThemeCompatible" Content="Yes">
</head>

<body>
<table cellpadding="0" cellspacing="0" class="jonahtable" width="95%">
<tr valign="top">	
<td>

<!-- // sidebar -->
<? include ('/var/www/jonah/inc/sidebar2.php'); ?>
<div id="" style="border:1px solid silver">
<table><tr><td>No. of Parallel Transcodes</td></tr>
<tr><td><img src='/jonah/images/full.png' /><img src='/jonah/images/blank.png' /><img src='/jonah/images/blank.png' /><img src='/jonah/images/blank.png' /><img src='/jonah/images/blank.png' /><img src='/jonah/images/blank.png' /></td></tr></table>
</div>
<br />
<div id="" style="border:1px solid silver">
<table><tr><td>No. of Passes per Transcode</td></tr>
<tr><td><img src='/jonah/images/full.png' /><img src='/jonah/images/full.png' /><img src='/jonah/images/blank.png' /></td></tr></table>
</div>
<br /><br />
<div id="AjaxResult" style="float: left; width: 250px; border: 1px solid silver; padding: 4px; font-size: 90%">
<!-- server side realignment goes here for debugging -->
</div>
</td>
<td>
<br />

<!-- // mockup for transcode queue -->
<? include ('/var/www/jonah/inc/queue-view.php'); ?>

<!-- // click to step through transcode process -->
<form action='/jonah/inc/transcode-debug.php' method='post'><input type='submit' class='softbutton' value=' click to step thru transcode process >> ' /></form>
<div id="TranscodeResult" style="float: left; width: 100%; border: 1px solid silver; padding: 4px; font-size: 90%">
<!-- server side realignment goes here for debugging -->
</div>



<!-- // -->
</td><!-- end of content cell in main table // -->
</tr>

<tr><td align="left" valign="bottom">&nbsp;</td><td align="center">
<? include ('/var/www/jonah/inc/adminfooter.php'); ?>
</td></tr>
</table>

</body>
</html>	