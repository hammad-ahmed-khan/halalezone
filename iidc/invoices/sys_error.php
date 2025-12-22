<?php
if (!isset($_REQUEST['act']) && !isset($_REQUEST['nr'])) {
    exit();
}

//show php errors
error_reporting(E_ALL);


include "../config/paths.inc.php";
include "../config/connect.inc.php";
$invnr = $_REQUEST['nr'];
if (!$invoice = $amdb->get_row("SELECT * FROM invoices WHERE nr = '$invnr'")) {
    echo "Invoice not found";
    exit();
}

if (isset($_POST['remove_error_message'])) {
    $invFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].pdf";
    $orgFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].org.pdf";

    if (file_exists($orgFile)) {
        copy($orgFile, $invFile);
        unlink($orgFile);
    }

    if (file_exists($invFile)) {
        $sql = "UPDATE invoices SET sys_error = false WHERE nr = '$invnr'";
        $amdb->query($sql);
    }
    exit();
}

if ($_GET['act'] == 'sys_error') { ?>
    <script>
        function updateSysError() {
            invNr = '#inv_<?php echo $invnr; ?>';
            if (jQuery('#remove_error_message').is(':checked')) {
                jQuery(invNr).find('.sys_error').css('color', '');
            } else {
                jQuery(invNr).find('.sys_error').css('color', 'red');
            }
            $(".ui-dialog-content").dialog("close");
            return post_this_form(sys_error_form);
        }
    </script>
    <form method="post" action="/invoices/sys_error.php" target="" style="padding:20px" name="sys_error_form" id="sys_error_form" onsubmit="return updateSysError();">
        <input type="hidden" name="act" value="update_sys_error">
        <input type="hidden" name="nr" value="<?php echo $invnr; ?>">
        <strong>Error message to be printed on the <?php echo $invoice['invoice_type'] == 'credit_note' ? 'credit note' : 'invoice'; ?></strong><br />
        <textarea name="error_message" style="width:100%;height:100px;width:350px"><?php echo "System Error:\nDuplicated"; ?> <?php echo $invoice['invoice_type'] == 'credit_note' ? 'credit note' : 'invoice'; ?>.</textarea><br />
        <?php if ($invoice['sys_error'] == true) { ?>
        <label><input type="checkbox" name="remove_error_message" id="remove_error_message" value="1">Remove error message from invoice</label>
        <?php };?>
    </form>
<?php
    exit();
}

if ($_POST['act'] == 'update_sys_error') {
    $error_message = $_REQUEST['error_message'];
    $invFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].pdf";
    $orgFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].org.pdf";

    if (file_exists($orgFile)) {
        copy($orgFile, $invFile);
    } else {
        copy($invFile, $orgFile);
    }

    if (file_exists($invFile)) {
        $sql = "UPDATE invoices SET sys_error = true WHERE nr = '$invnr'";
        $amdb->query($sql);
        //include fpdi.php
        require_once('../pdf/tcpdf/tcpdf.php');
        require_once("../pdf/tcpdf/fpdi/fpdi.php");
        $pdf = new FPDI();
        //set headers and footers false
        $pdf->setPrintHeader(false);
        $pages = $pdf->setSourceFile($invFile);
        //add all pages
        for ($i = 1; $i <= $pages; $i++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0, 210);
            if ($i == 1) {
                $pdf->SetFont('Times', '', 14);
                $pdf->SetTextColor(255, 0, 0);
                $pdf->SetXY(20, 10);
                //write multiCell keeping the text aligned to left
                $pdf->MultiCell(170, 10, $error_message, 0, 'L', 0, 0, '', '', true, 0, false, true, 0, 'T');
            }
        }
        $pdf->Output($invFile, 'F');
    }

    exit();
}
