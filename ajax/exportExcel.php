<?php
include_once('../../includes/func.php');

$db = acsessDb::singleton();
$dbo = $db->connect();

$clid = isset($_REQUEST['clid']) ? intval($_REQUEST['clid']) : 0;

if ($clid <= 0) {
    die('Invalid client ID');
}

// Get all production sites for the client
$sql = "SELECT site_name, street, city, zipcode, country, telephone, email, status, DATE_FORMAT(inserted_on, '%d-%b-%Y') as inserted_on 
        FROM companies_production_sites 
        WHERE clid = :clid 
        ORDER BY site_name ASC";

$stmt = $dbo->prepare($sql);
$stmt->execute([':clid' => $clid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="production_sites_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8"></head>';
echo '<body>';
echo '<table border="1">';

// Header row
echo '<tr>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Name</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Street</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">City</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Zip Code</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Country</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Telephone</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Email</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Status</th>';
echo '<th style="background-color: #4472C4; color: white; font-weight: bold;">Created On</th>';
echo '</tr>';

// Data rows
foreach ($rows as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['site_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['street']) . '</td>';
    echo '<td>' . htmlspecialchars($row['city']) . '</td>';
    echo '<td>' . htmlspecialchars($row['zipcode']) . '</td>';
    echo '<td>' . htmlspecialchars($row['country']) . '</td>';
    echo '<td>' . htmlspecialchars($row['telephone']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
    echo '<td>' . htmlspecialchars($row['inserted_on']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';
