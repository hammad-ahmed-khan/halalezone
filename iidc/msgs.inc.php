<?php
if (isset($msg) and $msg!='')
{
include "../config/msgs.inc.php";
echo "<table width=400><tr><td><p>";
echo $msgs[$msg];
echo"</td></tr></table>";
}
?>

