<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 // temp storing on 37.188.116.67 (rackspace)
	 $formattedtime=date("F j, Y, g:i a");
	 $userip=$_SERVER['REMOTE_ADDR'];
	 $debug=1;															// debug = show working paths, variables etc (not currently implemented)						
	 $explodeID='*';													// used to explode json cos I cant be arsed to do multi-array
	 $i=0;	
	 $refreshtime=60;	 	 											// determines how often watchfolders get prodded
	 $currenttime=date("U");											// get local time for use in detecting age of files
	 // $formattedtime=date("F j, Y, g:i a");
	 $formattedtime=date("F j")."<br />".date("g:i a");;
	// load below parameters from admin.json file						// suggest using mySql DB later
 
	$adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file (use mySql db later)
	$config = json_decode($adminVar, true);								// json decode array
	
	$voddrive = $config['voddrive'];								// set disk array used for vod storage
	$voduploaddir = $config['voduploaddir'];						// set disk path used for vod upload
	$presetpath = $config['presetpath'];						// set disk path used for vod upload
															
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
<? include ('/var/www/jonah/inc/sidebar2.php'); ?>

</td>
<td>
<!--
[debug: profile editor goes here]
<br /><br />
ffmpeg -i $input <br /><br />
$profile=(" -r 25 -b 600k -s 640x360 -c:v libx264 -flags +loop -me_method hex -g 100 -keyint_min 100 -sc-threshold 0 -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -bf 0 -b_strategy 1 -i_qfactor 0.71 -cmp +chroma -subq 8 -me_range 16 -coder 0 -sc_threshold 40 -flags2 +bpyramid +wpred+mixed_refs-dct8x8+fastpskip -keyint_min 25 -refs 3 -trellis 1 -level 30 -directpred 1 -partitions -parti8x8-parti4x4-partp8x8- partp4x4-partb8x8 -threads 0 -acodec libfaac -ar 44100 -ab 96k -y")
<br /><br />
exec (ffmpeg -i $input $profile $pass-specific $output $fireandforget)
-->

<br />

<form action='#' method="post">
<select><option value='tvp'>TVP</option></select>&nbsp;<input type='submit' value=' select preset ' class='softbutton' /></form>
</form>
<br /><br />

<table class='jonahtable profileeditor'>
<tr><td colspan='13'>Preset: TVP</td></tr>
<tr class='tabhead-admin'>
<td>&nbsp;&nbsp; </td>
<td>&nbsp;&nbsp; Video Codec &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Framerate &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Video Bitrate &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Resolution &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Keyframe &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Advanced Parameters 1 &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Audio Library &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Channels &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Sampling Freq &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Audio bitrate &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Advanced Parameters 2 &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Output Container &nbsp;&nbsp;</td>
<td>&nbsp;&nbsp; Multipass &nbsp;&nbsp;</td>
</tr>

<?
	$fullPreset=file_get_contents('/var/www/jonah/profiles/tvp-preset.json'); 				// 	replace by mySql record update (add item to bottom of queue)
	$presetExplode=explode($explodeID,$fullPreset);
	//echo ("fullPreset: ".$fullPreset."<br />");	

	foreach($presetExplode as $presetPass)
	{	
		if (!$presetPass) 
		{
		// dont show before *
		} 
		else 
		{
			//echo ("Preset: ".$presetPass."<br />");	
			$preset = json_decode($presetPass, true);
 			$i++ ;		// increase $i counter

?>
<form action='' name='line<? echo ($i); ?>' id='line<? echo ($i); ?>'>
<tr>
<td>&nbsp;<? echo ($i); ?>&nbsp;</td>
<td><select class='prinput' name='-c:v'>
<? 
echo ("<option class='selected' value='".$preset['-c:v']."' selected>".$preset['-c:v']."</option>");
?>
<option value='libx264'>libx264</option><option value='none'>none</option></select></td>
<td><select class='prinput' name='-r'>
<? 
echo ("<option class='selected value='".$preset['-r']."' selected>".$preset['-r']."</option>");
?>
<option value='8.33'>8.33</option><option value='12.5'>12.5</option><option value='15'>15</option><option value='24'>24</option><option value='25'>25</option><option value='29.97'>29.7</option></select></td>
<td><input size='6' class='prinput' name='-b:v' value='<? echo ($preset['-b:v']); ?>'></input></td>
<td><input size='10' class='prinput' name='-s' value='<? echo ($preset['-s']); ?>'></input></td>
<td><input size='6' class='prinput' name='-g' value='<? echo ($preset['-g']); ?>'></input></td>
<td><input class='prinput-long' type='text' name='addition1' value='<? echo ($preset['addition1']); ?>' /></td>
<td><select class='prinput' name='-c:a'>
<? 
echo ("<option class='selected' value='".$preset['-c:a']."' selected>".$preset['-c:a']."</option>");
?>
<option value='libvo_aacenc'>libvo_aacenc</option><option value='libfaac'>libfaac</option><option value='none'>none</option></select></td>
<td><select class='prinput' name='-ac'>
<? 
echo ("<option class='selected' value='".$preset['-ac']."' selected>".$preset['-ac']."</option>");
?>
<option value='1'>mono</option><option value='2'>stereo</option><option value='4'>4.0 surround</option><option value='5.1'>5.1 surround</option></select></td>
<td><select class='prinput' name='-ar'>
<? 
echo ("<option class='selected' value='".$preset['-ar']."' selected>".$preset['-ar']."</option>");
?>
<option value='11000'>11000</option><option value='22050'>22050</option><option value='44100'>44100</option><option value='48000'>48000</option></select></td>
<td><input size='6' class='prinput' value='<? echo ($preset['-b:a']); ?>' name='-b:a'></input></td>
<td><input class='prinput-med' type='text' name='addition2' value='<? echo ($preset['addition2']); ?>' /></td>
<td><select class='prinput' name='container'>
<? 
echo ("<option class='selected' value='".$preset['container']."' selected>".$preset['container']."</option>");
?>
<option value='mp4'>mp4</option><option value='aac'>aac</option><option value='f4v'>f4v</option></select></td>
<td><input class='prinput' type='checkbox' name='2pass' value='<? echo ($preset['2pass']); ?>'<? if ($preset['2pass']) {echo " checked='yes'";} ?> /></td>
</tr>
</form>

<? } // end if !presetPass
	 } // end for each
	 
	 
	 // now we need to add multiple forms into one string to submit
?>

<tr><td colspan='13'>&nbsp;</td></tr>
<tr><td colspan='8'>&nbsp;</td><td><input type='submit' value='add profile' class='softbutton' disabled='disabled' /></td><td><input type='submit' value='update preset' class='softbutton' onClick='processProfileData()' /></td><td></td><td></td><td></td></tr>
</table>

<!-- document.forms["configForm"].submit(); -->
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