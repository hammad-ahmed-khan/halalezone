<?php
define("__HQC__", true);
if (!isset($_REQUEST['nr']) || !isset($_REQUEST['act'])) {
    exit;
}

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_REQUEST['act'] == "internalMemo" and isset($_REQUEST['nr'])) {
    if ($cert = $amdb->get_row("SELECT * FROM invoices WHERE nr = '$_REQUEST[nr]'")) {
        $memo = $cert['memo'];
    }
?>
    <script>
        function createMemo() {
            memoText = jQuery("#memo_text").val().replace(/\n/g, "<br>");
            nr = jQuery("#memo_nr").val();

            if (jQuery("#memo_" + nr).length != 0)
                jQuery("#memo_" + nr).remove();

            memo = '<div class="remarks" id="memo_' + nr + '"><i class = "fa fa-trash-alt" onclick = "deleteMemo(' + nr + ')" ></i><span>' + memoText + '</span></div>';
            jQuery("#status_" + nr).append(memo);
            closeDialog();

        }
    </script>
    <form action="/invoices/memo_save.php" id="internalMemo" method="post" onsubmit="return post_this_form(this);">
        <input type="hidden" name="act" value="saveInternalMemo" />
        <input type="hidden" name="nr" id="memo_nr" value="<?php echo $_REQUEST['nr']; ?>" />
        <input type="hidden" name="saveBtn" id="saveBtn" value="Save Memo" />
        <div style="padding:10px">
            <textarea style="width:400px;height:100px" id="memo_text" name="memo"><?php echo $memo; ?></textarea>
        </div>
    </form>
<?php
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "saveInternalMemo" and isset($_REQUEST['nr'])) {
    if ($amdb->update("invoices", array("memo" => $_POST['memo']), "nr = '$_POST[nr]'")) {
        $_POST['memo'] = str_replace("\n", "<br>", $_POST['memo']);
        echo "<script>
		window.parent.createMemo();
	</script>";
    };
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "delete_memo" and isset($_REQUEST['nr'])) {
    if ($amdb->update("invoices", array("memo" => ''), "nr = '$_POST[nr]'")) {
        echo 'done';
    };
    exit();
}
