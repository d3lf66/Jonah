<?php
	 ini_set ("display_errors", "1");
	 error_reporting(E_ALL);
	 $formattedtime=date("F j, Y, g:i a");
	 
// open file for reading only

	 $adminVar=file_get_contents('/var/www/jonah/admin/config.json'); 	//read admin variable from local json file
	 $config = json_decode($adminVar, true);							// json decode array
 
$voddrive = $config['voddrive'];
$voduploaddir = $config['voduploaddir'];
$pathtoinspect = $config['pathtoinspect'];
$pathtoqueue = $config['pathtoqueue'];
$pathtoxml = $config['pathtoxml'];
$allowed_video_types = $config['allowedvideotypes'];
$allowed_xml_types = $config['allowedxmltypes'];
$minstowait = $config['minstowait'];
$lastupdatedby = $config['9'];
$orphanpath =  $config['orphanpath'];
$logpath =  $config['logpath'];
$parallel =  $config['parallel'];
$passes =  $config['passes'];

echo ($voddrive." <br />");
echo ($voduploaddir." <br />");
echo ($pathtoinspect." <br />");
echo ($pathtoqueue." <br />");
echo ($pathtoxml." <br />");
echo ($allowed_video_types." <br />");
echo ($allowed_xml_types." <br />");
echo ($minstowait." <br />");
echo ($orphanpath." <br />");
echo ($logpath." <br />");
echo ($parallel." <br />");
echo ($passes." <br />");
echo ($lastupdatedby." <br />");
?>
<a href="/jonah/admin/admin.php">return to admin</a>
