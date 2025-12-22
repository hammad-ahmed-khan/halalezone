<?php
session_start();
if (!isset($_SESSION['username']) or !isset($_REQUEST['act'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

define("__HQC__", true);
global $tokenFile, $division;

$tokenFile = "HQC-exact.json";
$division = '1225847'; //chamanco bv
// $division = '1225837'; //COHS and HQC BV Division
// $division = '3171749'; //zuivel B.V.
// $division = '3171805'; //vleesverwerking B.V.
// $division = '3171811'; //HQC France B.V.
// $division = '3171835'; //COHS Pluimvee B.V.
// $division = '3171853'; //Audit B.V.


$tokenFile = "ayoub-exact.json";
$division = '3808213'; //Ayoub media division
// $tokenFile = "ayoubMedia-exact.json";
// $division = '2702962'; //Ayoub media division
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/exact/exact.inc.php';
function getUnpaidInvoices($accessToken, $division)
{
    //  $division = '3171853';
    // Exact Online API endpoint for unpaid sales invoices
    $url = "https://start.exactonline.nl/api/v1/$division/salesinvoice/SalesInvoices?\$filter=Status ne null and Status eq 20&\$select=InvoiceID,InvoiceNumber,DeliverToName,AmountDC,Description,InvoiceDate,StatusDescription,Status &\$orderby=InvoiceNumber desc";
      $url = "https://start.exactonline.nl/api/v1/$division/salesinvoice/SalesInvoices";
    // Set up the HTTP request options
    $options = [
        'http' => [
            'header' => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"  // Request JSON response
            ],
            'method' => 'GET',
            'ignore_errors' => true,  // To capture even non-2xx responses
        ],
    ];

    $context = stream_context_create($options);

    // Make the API request
    $result = file_get_contents($url, false, $context);

    // Check for errors in the response
    if ($result === FALSE) {
        throw new Exception('Error retrieving unpaid invoices');
    }

    // Decode the JSON response
    $response = json_decode($result, true);
    // echo "<pre>";
    // print_r($response);
    return $response['d']['results'];
}

if ($_REQUEST['act'] == 'getUnpaidInvoices') {
    try {
        $invoices = getUnpaidInvoices($accessToken, $division);
?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice ID</th>
                    <th>Client</th>
                    <th>Invoice Date</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $NO = 1;
                foreach ($invoices as $invoice) {
                    preg_match('/Date\((.*)\)/s', $invoice['InvoiceDate'], $invoice_date);

                    $invoice_date = date("d/m/Y", ($invoice_date[1] / 1000)); ?>
                    <tr>
                        <th><?php echo $NO++; ?></th>
                        <td data-id="<?php echo $invoice['InvoiceID']; ?>"><?php echo $invoice['InvoiceNumber']; ?></td>
                        <td><?php echo $invoice['DeliverToName']; ?></td>
                        <td><?php echo $invoice_date; ?></td>
                        <td><?php echo $invoice['AmountDC']; ?></td>
                        <td><?php echo $invoice['Description']; ?></td>
                        <td><?php echo $invoice['Status']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
<?php
        // Handle the invoices as needed
        //echo json_encode($invoices['d']['results']);
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
