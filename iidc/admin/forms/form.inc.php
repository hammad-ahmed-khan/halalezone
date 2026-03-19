<?php
if (!defined("__HQC__")){exit();};
error_reporting(E_ALL);
ini_set('display_errors', 1);
if(!isset($_GET['foid']) and !isset($_GET['coid'])){exit();};
include dirname(__FILE__)."/forms.class.php";
?>
<div id="formHolder">
<?php if (isset($_GET['foid'])){ ?>
<form action="form_save.php" method="post" onsubmit="return post_this_form(this)" name="form_maker" id="form_maker" data-error="All fields are rquired.">
<h4 style="float:left;margin:0px">Fill-in Form</h4>
<div style="padding:20px"><?php echo $amform->get_form($_GET['foid']); ?></div>
<div style="text-align:center;padding:20px"><input type="submit" value="save"/></div>
</form>
<?php exit();};?>

<?php if (isset($_GET['coid']) && isset($_GET['act']) and $_GET['act']=="edit"){ ?>
<form action="form_save.php" method="post" onsubmit="return post_this_form(this)" name="form_maker" id="form_maker" data-error="All fields are rquired.">
<h4 style="float:left;margin:0px">Edit form</h4>
<div style="padding:20px"><?php echo $amform->edit_form($_GET['coid']); ?></div>
<div style="text-align:center;padding:20px"><input type="submit" value="save"/></div>
</form>
<?php exit();};?>

<?php if (isset($_GET['coid'])){ ?>
<h4 style="float:left;margin:0px">View Form</h4>
<div style="padding:20px"><?php echo $amform->view_form($_GET['coid']); ?>
<center><a href="<?php echo $_SERVER['REQUEST_URI'];?>&act=edit">Edit Form</a> | <a href="pdf-maker.php?content_id=<?php echo $_GET['coid'];?>" target="_blank">Make PDF File</a></center>
</div>
<?php exit();};?>
</div>