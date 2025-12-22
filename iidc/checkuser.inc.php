<?php
if (count($_REQUEST)>0) {
foreach ($_REQUEST as $key => $value) {
$$key = str_replace("'","\'",$value);
}
}
date_default_timezone_set(date_default_timezone_get());
//date_default_timezone_get();

if ($_SERVER['REMOTE_ADDR']=='::1')
return;

?>
<script type="text/javascript" language="JavaScript">
<!--
// if (top == self)
//     top.location.href = 'index.php';
//-->
</script>
