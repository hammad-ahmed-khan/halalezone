<?php
session_start();
if (!isset($_SESSION['username']))
    exit();
include "../config/paths.inc.php";

if ($_REQUEST['act'] == 'sus') {
    $amdb->query("update users set active='n' where clid = '$_REQUEST[id]'");
    $amdb->query("update companies set active='n' where clid = '$_REQUEST[id]'");
    echo "ok";
    exit();
}

if ($_REQUEST['act'] == 'reins') {
    $amdb->query("update users set active='y' where clid = '$_REQUEST[id]'");
    $amdb->query("update companies set active='y' where clid = '$_REQUEST[id]'");
    echo "ok";
    exit();
}

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
    if ($client = get_client($_REQUEST['clid'])) {
        if (isset($_REQUEST['shipment-approval']) && trim($_REQUEST['shipment-approval']) != '')
            $shipmentApproval = $_REQUEST['shipment-approval'];
        else
            $shipmentApproval = 'no';

        if (trim($client['client_extra']) != '' && is_array(json_decode($client['client_extra'], true)))
            $extra_data = json_decode($client['client_extra'], true);
        else
            $extra_data = array();

        $extra_data['shipment-approval'] = $shipmentApproval;
        $client_extra = json_encode($extra_data);

        if ($amdb->query("UPDATE companies SET client_extra = '$client_extra' WHERE clid = $_REQUEST[clid]")) {
            foreach (array('a', 'b') as $type) {
                $amdb->update("certificates_{$type}", array("approval_required" => $shipmentApproval), "TRIM(hcd_process)='' AND status= 'active' AND clid='$_REQUEST[clid]'");
            }
            echo 'ok';
        } else {
            echo 'Error';
        }
    }
    exit();
};


if ($_REQUEST['act'] == 'getDraftForm') {
    if ($client = get_client($_REQUEST['clid'])) {

        if (trim($client['client_extra']) != '' && is_array(json_decode($client['client_extra'], true)))
            $extra_data = json_decode($client['client_extra'], true);
        else
            $extra_data = array();
    ?>
        <script>
            clIcon = '#lock_<?php echo $_REQUEST['clid']; ?>';

            function saveDraftShipment() {
                //check if the checkbox approval-require is checked
                if (draft_shipment_certificates.checked) {
                    val = 'yes'
                } else {
                    val = '';
                }
                $.post("/admin/client_save.php", {
                    "act": "saveDraftShipment",
                    "clid": <?php echo $_REQUEST['clid']; ?>,
                    "printDraftShipment": val
                }, function(data) {
                    if (data == 'ok') {
                        if (val == 'yes')
                            jQuery(clIcon).css('color', 'green');
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
        <form method="post" action="/admin/client_save.php" id="client_form" onsubmit="return saveDraftShipment()">
            <input type="hidden" name="act" value="setApproval" />
            <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
            <input type="hidden" name="saveBtn" value="Save" />

            <div style="padding:10px;width:400px;font-size:14px;">
                <h3><b>Company: </b><span style="color:green"><?php echo $client['company_name']; ?></span></h3><label><input type="checkbox" id="draft_shipment_certificates" name="draft_shipment_certificates" value="yes" <?php echo (isset($extra_data['draft_shipment_certificates']) && $extra_data['draft_shipment_certificates'] == 'yes') ? 'checked' : ''; ?> /> Allow the client to print draft shipment certificates</label>
            </div>
        </form>
<?php };
    exit();
};

//draft_shipment_certificates
//Im here finish this
if ($_REQUEST['act'] == 'saveDraftShipment') {
    if ($client = get_client($_REQUEST['clid'])) {
        if (isset($_REQUEST['printDraftShipment']) && trim($_REQUEST['printDraftShipment']) != '')
            $printDraftShipment = $_REQUEST['printDraftShipment'];
        else
            $printDraftShipment = 'no';

        if (trim($client['client_extra']) != '' && is_array(json_decode($client['client_extra'], true)))
            $extra_data = json_decode($client['client_extra'], true);
        else
            $extra_data = array();

        $extra_data['draft_shipment_certificates'] = $printDraftShipment;

        $client_extra = json_encode($extra_data);

        if ($amdb->query("UPDATE companies SET client_extra = '$client_extra' WHERE clid = $_REQUEST[clid]")) {
            echo 'ok';
        } else {
            echo 'Error';
        }
    }
    exit();
};
?>