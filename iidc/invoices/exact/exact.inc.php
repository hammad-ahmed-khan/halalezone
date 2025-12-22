<?php
if (isset($_GET['login'])) {
    session_unset();
    session_destroy();
    // Optionally, you can also clear cookies related to the session
    setcookie("PHPSESSID", "", time() - 3600, "/");
    header("Location: exact.inc.php");
    exit();
}
//show php errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define("__HQC__", true);
if(!isset($_GET['act']))
$_GET['act'] = 'getUnpaidInvoices';
?>
<!DOCTYPE html>

<head>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<?php
global $tokenFile, $division;

$tokenFile = "HQC-exact.json";
$division = '1225847'; //chamanco bv
// $division = '1225837'; //COHS and HQC BV Division
// $division = '3171749'; //zuivel B.V.
// $division = '3171805'; //vleesverwerking B.V.
// $division = '3171811'; //HQC France B.V.
// $division = '3171835'; //COHS Pluimvee B.V.
// $division = '3171853'; //Audit B.V.


// $tokenFile = "ayoub-exact.json";
// $division = '3808213'; //Ayoub media division
$tokenFile = "ayoubMedia-exact.json";
$division = '2702962'; //Ayoub media division

echo "
<pre>";
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/exact/exact.inc.php';
$exactAPIUrl = 'https://start.exactonline.nl/api/v1/';
function getUserRoles($accessToken) {
    global $exactAPIUrl, $division;
    $url = $exactAPIUrl . "current/Me";
    //$url = $exactAPIUrl . "$division/hrm/UserRoles";

    $options = [
        'http' => [
            'header' => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"
            ],
            'method' => 'GET',
            'ignore_errors' => true,
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        throw new Exception('Error retrieving user roles');
    }

    $response = json_decode($result, true);
print_r($response);
    return $response['d']['results'][0]['Roles'] ?? [];
}
if($_GET['act']=='getUserRoles'){
    try {
        $roles = getUserRoles($accessToken);
        print_r($roles);  // Check the roles assigned to the user
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    exit();
}


function getDivisionId($accessToken,$division)
{
    // Exact Online API endpoint to get the current user details
    $url = "https://start.exactonline.nl/api/v1/$division/system/AllDivisions";

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
        throw new Exception('Error retrieving division ID');
    }
    // Decode the JSON response
    $response = json_decode($result, true);
    print_r($response);
    exit();
    // Extract the division ID
    if (isset($response['d']['results'][0]['CurrentDivision'])) {
        return $response['d']['results'][0]['CurrentDivision'];
    } else {
        throw new Exception('Division ID not found in the response');
    }
}

// // Example usage:
if($_GET['act']=='getDivisionId'){
    try {
        $divisionId = getDivisionId($accessToken,$division);
        echo "Your Division ID: " . $divisionId . "\n";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    exit();
}

function getUnpaidInvoices($accessToken, $division)
{
  //  $division = '3171853';
    // Exact Online API endpoint for unpaid sales invoices
    $url = "https://start.exactonline.nl/api/v1/$division/salesinvoice/SalesInvoices?\$filter=Status ne null and Status eq 50&\$select=InvoiceID,AmountDC,Created,Description,InvoiceDate,StatusDescription,Status";
echo "9 - $division\n";

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
    return $response;
}

// Example usage:
if($_GET['act']=='getUnpaidInvoices'){
    try {
        $invoices = getUnpaidInvoices($accessToken, $division);
        // Handle the invoices as needed
        print_r($invoices);
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    exit();
}
?>
</body>
</html>