[Server side debug] <br />
Your re-ordered row order is: <br/>
<?php
$result = $_REQUEST["table-3"];
foreach($result as $value) {
	echo "$value<br/>";
}
?>
