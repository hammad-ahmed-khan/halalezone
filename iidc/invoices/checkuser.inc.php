<?php
if (count($_REQUEST)>0) {
foreach ($_REQUEST as $key => $value) {
$$key = $value;
}
}
date_default_timezone_get('Europe/Amsterdam');
if(!isset($user))
{
?>
<script type="text/javascript" language="JavaScript">
<!--
// if (top == self)
//     top.location.href = '../index.php';
//-->
</script>
<?php }?>
