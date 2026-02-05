<?php
if (!isset($_SESSION["username"]) && !isset($_GET["clid"]) && !isset($_GET["act"])) {
    exit();
};
include "../../config/paths.inc.php";
include "../../config/mysql_ftp.inc.php";
include "../../config/connect.inc.php";
$reminder = array('company_name' => 'Update reminders for one or more company(ies)', 'first_reminder' => 21, 'second_reminder' => 7, 'telephone_call' => 7, 'status' => 'off');
if ($_GET['act'] == 'edit') {
    if (!$reminder = $amdb->get_row("SELECT hqc_default_invoice_reminders.*,companies.company_name FROM `hqc_default_invoice_reminders` JOIN companies ON hqc_default_invoice_reminders.clid = companies.clid WHERE hqc_default_invoice_reminders.clid = '" . $_GET['clid'] . "'")) {
        $company = $amdb->get_row("SELECT company_name FROM companies WHERE clid = '" . $_GET['clid'] . "'");
        $reminder = array('company_name' => $company['company_name'], 'first_reminder' => 21, 'second_reminder' => 7, 'telephone_call' => 7, 'status' => 'off');
    };
}
?>
<script>
    var act = '<?php echo $_GET['act']; ?>';

    function reminderUpdated(clid, posted) {
        var data = JSON.parse(posted);
        $('.com_' + clid).find('.center').eq(0).html(data.first_reminder + ' Days');
        $('.com_' + clid).find('.center').eq(1).html(data.second_reminder + ' Days');
        $('.com_' + clid).find('.center').eq(2).html(data.telephone_call + ' Days');
        $('.com_' + clid).find('.status').html('<i class="fas fa-toggle-' + data.status + '"></i>');
        closeDialog();
    }
</script>
<form id="reminder_edit" class="form" action="reminder_save.php" method="post" onsubmit="return post_this_form(this)" target="" style="min-width:450px;padding:10px;">
    <input type="hidden" name="act" value="<?php echo $_GET['act'] == 'edit' ? 'updateReminder' : 'massUpdateReminders'; ?>">
    <input type="hidden" name="clid" id="clid" value="<?php echo $_GET['clid']; ?>">
    <table class="table table-striped table-bordered" style="width:100%;margin:0">
        <tr>
            <th colspan="2" style="text-align:center;"><?php echo $reminder['company_name']; ?></th>
        </tr>
        <tr>
            <th style="width:100px">Term/First reminder:</th>
            <td>
                After <input type="number" name="first_reminder" id="first_reminder" value="<?php echo $reminder['first_reminder']; ?>" style="width:60px"> Days
            </td>
        </tr>
        <tr>
            <th style="width:100px">Second reminder:</th>
            <td>
                After <input type="number" name="second_reminder" id="second_reminder" value="<?php echo $reminder['second_reminder']; ?>" style="width:60px"> Days
            </td>
        </tr>
        <tr>
            <th style="width:100px">Telephone call:</th>
            <td>
                After <input type="number" name="telephone_call" id="telephone_call" value="<?php echo $reminder['telephone_call']; ?>" style="width:60px"> Days
            </td>
        </tr>
        <tr>
            <th>Auto reminder</th>
            <td>
                <?php  $status = $reminder['status'] != '' ? $reminder['status'] : 'off';?>
                <select name="status" id="status">
                    <option value="off" <?php echo $status == 'off' ? 'selected' : ''; ?>>Off</option>
                    <option value="on" <?php echo $status == 'on' ? 'selected' : ''; ?>>On</option>
                </select>
            </td>
        </tr>
    </table>
    <script>
        if (act == 'massEdit') {
            clids = [];
            $('.clidCheckbox:checked').each(function() {
                clids.push($(this).val());
            });
            jQuery('#clid').val(clids.join(','));
        } else {
            jQuery('#clid').val('<?php echo $_GET['clid']; ?>');
        }
    </script>