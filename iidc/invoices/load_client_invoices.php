<?php
define("__HQC__", true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$yearInvoices = array();
$creditInvoices = array();
$creditInvoicesNotFound = array();
if ($invoices = $amdb->get_results("SELECT invoice_nr, total,inserted_on,YEAR(inserted_on) AS year,invoice_type,invoice_items,credit_invnr,paid_on,status FROM `invoices` WHERE clid='$_GET[clid]' AND status != 'hidden' AND status != 'deleted' ORDER BY inserted_on $_GET[ascDesc]")) {
    foreach ($invoices as $invKey => $invoice) {
        if ($invoice['invoice_type'] == "credit_note") {

            if (trim($invoice['credit_invnr']) != "") {
                $creditInvoices[] = $invoice['credit_invnr'];
            } else {
                if ($items = json_decode($invoice['invoice_items'], true)) {
                    $description = $items[1]['description'];
                    //find the invoice number or HQC-number using preg_match
                    if (preg_match('/\bHQC-\d+|\b\d+/', $description, $match)) {
                        $creditInvoices[] = $match[0];
                    } else {
                        $creditInvoicesNotFound[] = $invoice;
                    };
                } elseif (preg_match_all('/\bHQC-\d+|\b\d+/', $invoice['invoice_items'], $matches)) {
                    foreach ($matches[0] as $match) {
                        if (strlen($match) > 8) {
                            $creditInvoices[] = $match;
                        }
                    }
                } else {
                    $creditInvoicesNotFound[] = $invoice;
                    unset($invoices[$invKey]);
                }
            }
            unset($invoices[$invKey]);
        }
    }

    foreach ($invoices as $creditKey => $creditValue) {
        if (in_array($creditValue['invoice_nr'], $creditInvoices)) {
            unset($invoices[$creditKey]);
        }
    }

    // print_r($invoices);
    // print_r($creditInvoices);

    foreach ($invoices as $invoice) {
        if ($invoice['invoice_type'] != "credit_note") {
            if (!isset($yearInvoices[$invoice['year']])) {
                $yearInvoices[$invoice['year']] = array();
                $yearInvoices[$invoice['year']]['total'] = 0;
                $yearInvoices[$invoice['year']]['paid'] = 0;
                $yearInvoices[$invoice['year']]['unpaid'] = 0;
            }

            $yearInvoices[$invoice['year']]['total'] = $yearInvoices[$invoice['year']]['total'] + $invoice['total'];
            if ($invoice['paid_on'] != "") {
                $yearInvoices[$invoice['year']]['paid'] = $yearInvoices[$invoice['year']]['paid'] + $invoice['total'];
            } else {
                $yearInvoices[$invoice['year']]['unpaid'] = $yearInvoices[$invoice['year']]['unpaid'] + $invoice['total'];
            }
        }
    }
    //print_r($invoices);
    if (count($invoices) > 0 && count($yearInvoices) > 0) {
        $paid = 0;
        $unpaid = 0;
        $total = 0;
        if (isset($_REQUEST['tvh']) && $_REQUEST['tvh'] == 'v') {
            echo "<table class='alternateOn' id=\"client_invoices_table\" style='width:auto;min-width:350px;'>";
            echo "<thead><tr><th style='width:100px'>Year</th><th>Paid</th><th>Unpaid</th><th>Total</th></tr></thead>";
            echo "<tbody>";
            foreach ($yearInvoices as $year => $invoice) {
                if ($invoice['total'] == '0') continue; //skip empty years (no invoices
                $paid = $paid + $invoice['paid'];
                $unpaid = $unpaid + $invoice['unpaid'];
                $total = $total + $invoice['total'];
                echo "<tr>";
                echo "<th>$year</th>";
                echo "<td>€ " . number_format($invoice['paid'], 2, ',', '.') . "</td>";
                echo "<td class='unpaid'>€ " . number_format($invoice['unpaid'], 2, ',', '.') . "</td>";
                echo "<th>€ " . number_format($invoice['total'], 2, ',', '.') . "</th>";
                echo "</tr>";
            };
            echo "</tbody>";
            echo "</table>";
        } else {
            $table = array();
            foreach ($yearInvoices as $year => $invoice) {
                if ($invoice['total'] == '0') continue; //skip empty years (no invoices
                $paid = $paid + $invoice['paid'];
                $unpaid = $unpaid + $invoice['unpaid'];
                $total = $total + $invoice['total'];
                $table['title'][] = $year;
                $table['total'][] = number_format($invoice['total'], 2, ',', '.');
                $table['paid'][] = number_format($invoice['paid'], 2, ',', '.');
                $table['unpaid'][] = number_format($invoice['unpaid'], 2, ',', '.');
            };
            echo "<table class='alternateOn' id=\"client_invoices_table\">";
            echo "<thead><tr><th style='width:100px'>Year</th><th>" . implode('</th><th>', $table['title']) . "</th></tr></thead>";
            echo "<tbody><tr class='paid'><th>Paid</th><td>€ " . implode('</td><td>€ ', $table['paid']) . "</td></tr>";
            echo "<tr class='unpaid'><th>Unpaid</th><td>€ " . implode('</td><td>€ ', $table['unpaid']) . "</td></tr></tbody>";
            echo "<tfoot><tr class='total'><th>Total</th><th>€ " . implode('</th><th>€ ', $table['total']) . "</th></tr></tfoot>";
        }
        echo "</table>";
        echo "<br/><br/>
        <table class='alternateOn' id='allInvoices' style='width:auto;min-width:350px'><thead><tr><th colspan='2' style='text-align:center'>Totals</th></tr></thead>
        <tr><th style='width:80px'>Paid</th><td>€ " . number_format($paid, 2, ',', '.') . "</td></tr>
        <tr class='unpaid'><th>Unpaid</th><td>€ " . number_format($unpaid, 2, ',', '.') . "</td></tr>
        <tr><th>Total</th><th>€ " . number_format($total, 2, ',', '.') . "</th></tr>
        </table>";
?>
        <script>
            jQuery(document).ready(function() {
                jQuery('.unpaid > td').each(function() {
                    if (jQuery(this).text() == '€ 0,00') {
                        jQuery(this).text('€ 0,00').css('color', 'green');
                    } else {
                        jQuery(this).css({
                            'color': 'red',
                            'cursor': 'pointer'
                        }).click(function() {
                            loadUnpaidInvoices(this);
                        })
                    }
                });
            });
        </script>
<?php
        return;
    }
}
echo "<table class='alternateOn'><tr><th>No invoices found</th></tr></table>";
