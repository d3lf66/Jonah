<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 $formattedtime=date("F j, Y, g:i a");

	$config = array();
	$config['voddrive'] = $_POST['voddrive'];
	$config['voduploaddir'] = $_POST['voduploaddir'];
	$config['pathtoinspect'] = $_POST['pathtoinspect'];
	$config['pathtoqueue'] = $_POST['pathtoqueue'];
	$config['pathtoxml'] = $_POST['pathtoxml'];
	$config['allowedvideotypes'] = $_POST['allowedvideotypes'];
	$config['allowedxmltypes'] = $_POST['allowedxmltypes'];
	$config['minstowait'] = $_POST['minstowait'];
	$config['orphanpath'] = $_POST['orphanpath'];
	$config['logpath'] = $_POST['logpath'];
	$config['parallel'] = $_POST['parallel'];
	$config['passes'] = $_POST['passes'];	
	$config['9'] = $_POST['var9'];

//to decode
// $config = json_decode($configString, true);	 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Jonah - TVPlayer ingest monitor</title>
	<link href="/jonah/css/main.css" type="text/css" rel="stylesheet">
	<META HTTP-EQUIV="MSThemeCompatible" Content="Yes">

</head>

<body>
<table cellpadding="0" cellspacing="0" class="jonahtable" width="95%">
<tr valign="top">	
<td><img height="1" src="../images/spacer.gif" width="200" /><br /><img src="/jonah/images/jonah.png" /><br />
</td>
<td>
<?
echo ("Updating at ".$formattedtime." (by ".$config[9].")<br />");	
$configString = json_encode($config);
echo ("<table><tr><td class='code'>Writing admin.json file as: <br />".$configString."</td></tr></table>");

// sanity check variables here



// write admin.json file

file_put_contents('/var/www/jonah/admin/config.json', $configString); //write
$verified=file_get_contents('/var/www/jonah/admin/config.json'); //read

echo ("<table><tr><td class='code'>Verifying saved file as:<br /> ".$verified."</td></tr></table>");

?>

<br /><br />
[ <a href="/jonah/admin/read.php">View file contents</a> ]
<!-- // -->
</td><!-- end of content cell in main table // -->
</tr>

<tr><td></td><td align="center">
<? include ('/var/www/jonah/inc/adminfooter.php'); ?>
</td></tr>
</table>

</body>
</html>	
