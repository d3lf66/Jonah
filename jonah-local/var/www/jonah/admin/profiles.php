<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 // temp storing on 37.188.116.67 (rackspace)
	 $debug=1;															// debug = show working paths, variables etc (not currently implemented)
	 $refreshtime=60;	 	 											// determines how often watchfolders get prodded
	 $currenttime=date("U");											// get local time for use in detecting age of files
	 // $formattedtime=date("F j, Y, g:i a");
	 $formattedtime=date("F j")."<br />".date("g:i a");;
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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Jonah - file ingest engine (now in GitHub)</title>
	<link href="/jonah/css/main.css" type="text/css" rel="stylesheet">
    <script src="/jonah/scripts/libraries.js" type="text/javascript"></script>
    <META HTTP-EQUIV="MSThemeCompatible" Content="Yes">
</head>

<body>
<table cellpadding="0" cellspacing="0" class="jonahtable" width="95%">
<tr valign="top">	
<td>

<!-- // sidebar -->
<? include ('/var/www/jonah/inc/sidebar.php'); ?>

</td>
<td>

[debug: profile editor goes here]
<br /><br />
ffmpeg -i $input <br /><br />
$profile=(" -r 25 -b 600k -s 640x360 -c:v libx264 -flags +loop -me_method hex -g 100 -keyint_min 100 -sc-threshold 0 -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -bf 0 -b_strategy 1 -i_qfactor 0.71 -cmp +chroma -subq 8 -me_range 16 -coder 0 -sc_threshold 40 -flags2 +bpyramid +wpred+mixed_refs-dct8x8+fastpskip -keyint_min 25 -refs 3 -trellis 1 -level 30 -directpred 1 -partitions -parti8x8-parti4x4-partp8x8- partp4x4-partb8x8 -threads 0 -acodec libfaac -ar 44100 -ab 96k -y")
<br /><br />

exec (ffmpeg -i $input $profile $output)<br />

<br /><br />

<form action='/jonah/admin/profilesub.php' method="post">
<table class='jonahtable profileeditor'>
<tr><td colspan='10'>Preset: TVP</td></tr>
<!--
<tr><td>&nbsp;</td><td>
<textarea rows='10' cols='80' wrap='soft' class='profileeditor'> -r 25 -b 600k -s 640x360 -c:v libx264 -flags +loop -me_method hex -g 100 -keyint_min 100 -sc-threshold 0 -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -bf 0 -b_strategy 1 -i_qfactor 0.71 -cmp +chroma -subq 8 -me_range 16 -coder 0 -sc_threshold 40 -flags2 -flags2 +bpyramid +wpred+mixed_refs-dct8x8+fastpskip -keyint_min 25 -refs 3 -trellis 1 -level 30 -directpred 1 -partitions -parti8x8-parti4x4-partp8x8- partp4x4-partb8x8 -threads 0 -c:a libvo_aacenc -ar 44100 -ab 96k -y
</textarea></td></tr>-->

<tr><td>Audio only</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" class="adinput" name="addition" /></td></tr>
<tr><td>Profile1</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" name="addition1" class="adinput" /></td></tr>
<tr><td>Profile2</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" name="addition2" class="adinput" /></td></tr>
<tr><td>Profile3</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" name="addition3" class="adinput" /></td></tr>
<tr><td>Profile4</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" name="addition4" class="adinput" /></td></tr>
<tr><td>Profile5</td>
<td><select name="video library">
<option value="x264">x264</option>
</select></td><td><select name="resolution">
<option value="n/a">n/a</option>
<option value="320x180">320x180</option>
<option value="480x270">480x270</option>
<option value="640x360">640x360</option>
<option value="960x540">960x540</option>
<option value="1280x720">1280x720</option>
</select></td><td><select name="bitrate">
<option value="n/a">n/a</option>
<option value="280k">280K</option>
<option value="400k">400K</option>
<option value="600k">600K</option>
<option value="800k">800K</option>
<option value="1400k">1400K</option>
</select></td><td><select name="profile">
<option value="3.0">3.0 baseline</option>
<option value="3.1">3.1 main</option>
</select></td><td><select name="framerate">
<option value="8.3">8.33</option>
<option value="12.5">12.5</option>
<option value="15">15</option>
<option value="24">24</option>
<option value="25">25</option>
</select></td><td><select name="keyframe">
<option value="100">4s</option>
<option value="125">5s</option>
<option value="250">10s</option>
</select></td><td><select name="audiolib">
<option value="libfaac">libfaac</option>
<option value="libvo">libvo_aacenc</option>
</select></td><td><select name="abitrate">
<option value="32000">32k</option>
<option value="56000">56k</option>
<option value="96000">96k</option>
<option value="128000">128k</option>
</select></td><td><select name="sampling">
<option value="22">22 Khz</option>
<option value="44.1">44.1 Khz</option>
<option value="48">48 Khz</option>
</select></td><td><input type="text" width="300" name="addition5" class="adinput" /></td></tr>
<tr><td>&nbsp;</td><td colspan="9"><input type='submit' value='save profile' /></td></tr>
</table>

</form>

<!-- // mockup for transcode queue -->
<? // include ('/var/www/jonah/inc/queue-view.php'); ?>

<!-- // -->
</td><!-- end of content cell in main table // -->
</tr>

<tr><td align="left" valign="bottom"><form valign="bottom"><INPUT class="softbutton" onClick="showDebug()" value="showDebug()" type="button" disabled="disabled"></form></td><td align="center">
<? include ('/var/www/jonah/inc/adminfooter.php'); ?>
</td></tr>
</table>

</body>
</html>	