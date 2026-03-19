<?php
if (!defined("__HQC__")) {
    exit();
};
if (!isset($_REQUEST['foid'])) {
    exit();
};
include dirname(__FILE__) . "/forms.class.php";
?>
<div style="padding:20px" id="formHolder">
    <?php echo $amform->get_form($_REQUEST['foid'],isset($_GET['clid'])?array('clid'=>$_GET['clid']):null); ?>
</div>
<script>
    jQuery("input[type='button'],input[type='reset'],input[type='submit']").css("display", "none")
</script>

    <center><a href="/admin/forms/" class="button">Cancel</a></center>
