<?php
session_start();
if (!isset($_SESSION['username']))
    exit();
include "../config/paths.inc.php";

if ($_REQUEST['act'] == 'getForm') {
    if ($client = get_client($_REQUEST['clid'])) {

        if (trim($client['client_extra']) != '' && is_array(json_decode($client['client_extra'], true)))
            $extra_data = json_decode($client['client_extra'], true);
        else
            $extra_data = array();
?>
        <script>
            clIcon = '#lock_<?php echo $_REQUEST['clid']; ?>';

            function saveApproval() {
                //check if the checkbox approval-require is checked
                if (approval_required.checked) {
                    val = 'yes'
                } else {
                    val = '';
                }
                $.post("/admin/client_save.php", {
                    "act": "saveApproval",
                    "clid": <?php echo $_REQUEST['clid']; ?>,
                    "shipment-approval": val
                }, function(data) {
                    if (data == 'ok') {
                        if (val == 'yes')
                            jQuery(clIcon).css('color', 'red');
                        else
                            jQuery(clIcon).css('color', '');
                        top.closePopup();
                    } else {
                        alert("Error: " + data);
                    }
                });
                return false;
            }
        </script>
        <form method="post" action="/admin/client_save.php" id="client_form" onsubmit="return saveApproval()">
            <input type="hidden" name="act" value="setApproval" />
            <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
            <input type="hidden" name="saveBtn" value="Save" />

            <div style="padding:10px;width:400px">
                <h3><b>Company: </b><span style="color:green"><?php echo $client['company_name']; ?></span></h3><label><input type="checkbox" id="approval_required" name="approval-require" value="yes" <?php echo (isset($extra_data['shipment_approval']) && $extra_data['shipment_approval'] == 'yes') ? 'checked' : ''; ?> /> Approval by wasim is required for issuing shipment certificates</label>
            </div>
        </form>
    <?php };
    exit();
};
if ($_REQUEST['act'] == 'saveApproval') {
    if (isset($_REQUEST['shipment-approval']))
        $shipmentApproval = $_REQUEST['shipment-approval'];
    else
        $shipmentApproval = 'no';
    $client_extra = json_encode(array('shipment_approval' => $shipmentApproval));
    if ($amdb->query("UPDATE companies SET client_extra = '$client_extra' WHERE clid = $_REQUEST[clid]")) {
        foreach (array('a', 'b') as $type) {
            $amdb->update("certificates_{$type}", array("approval_required" => $shipmentApproval), "TRIM(hcd_process)='' AND status= 'active' AND clid='$_REQUEST[clid]'");
        }
        echo 'ok';
    } else {
        echo 'Error';
    }
    exit();
};
if ($_REQUEST['act'] == 'subsidiaries') {
    if ($main_client = get_client($_REQUEST['clid'])) {
        $subs = explode(',', $main_client['subs']);
    ?>
        <script>
            function searchClients(val) {
                $('#subsClients li').each(function() {
                    if ($(this).text().toLowerCase().indexOf(val.toLowerCase()) == -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            }
        </script>
        <form method="post" action="/admin/client_save.php" id="client_form" onsubmit="return post_this_form(this)" target="_new">
            <input type="hidden" name="act" value="save_subsidiaries" />
            <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
            <input type="hidden" name="saveBtn" value="Save" />
            <div style="padding:10px;width:600px;border:1px solid #eee" class="table table-striped table-bordered">
                <h3><b>Company: </b><span style="color:green"><?php echo $main_client['company_name']; ?></span></h3>
                <div>
                    <input type="text" id="search" onkeyup="searchClients(this.value);" class="search" style="width:90% !important" placeholder="Search fo clients"/>
                </div>
                <ol style="height:300px;overflow:auto;" id="subsClients">
                    <?php
                    if ($clients = get_clients("company_name,companies.clid,subs")) { ?>
                        <?php foreach ($clients as $client) {
                            if ($client['clid'] != $_REQUEST['clid']) { ?>
                                <li><label><input type="checkbox" name="subs[]" value="<?php echo $client['clid']; ?>" <?php echo (in_array($client['clid'], $subs)) ? 'checked' : ''; ?> /> <?php echo $client['company_name']; ?></label></li>
                        <?php }
                        } ?>
                    <?php } ?>
                </ol>

            </div>
        </form>
<?php
    }
}

if ($_REQUEST['act'] == 'save_subsidiaries' && isset($_REQUEST['clid'])) {
    if (isset($_REQUEST['subs'])) {
        $subs = implode(',', $_REQUEST['subs']);
    } else {
        $subs = '';
    }
    if ($amdb->query("UPDATE companies SET subs = '$subs' WHERE clid = $_REQUEST[clid]")) {
        $amdb->post_results("Subsidiaries saved", "reload");
    } else {
        $amdb->post_results("Error saving subsidiaries");
    }
    exit();
}
?>