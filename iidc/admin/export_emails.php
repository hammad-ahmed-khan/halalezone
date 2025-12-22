<?php
if (!isset($_POST['act']) or !isset($_POST['clids'])) {
    exit();
};

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if (!isset($_SESSION['username']) or $_SESSION['user_type'] == 'client')
    exit();
require_once $prog_path . '/tools/phpexcel/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$objPHPExcel = new Spreadsheet();
$sheetTitle = 'Emails list';
$objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC")
    ->setLastModifiedBy("HQC")
    ->setTitle("Companies Email list")
    ->setSubject("Email Lists")
    ->setDescription("Companies emails list created on: " . date("d/m/Y"))
    ->setKeywords("")
    ->setCategory("Emails");
$objPHPExcel->setActiveSheetIndex(0);

//getting the list of clients
if (isset($_GET['s']) and isset($_GET['q']) and trim($_GET['q']) != '')
    $srchFor = " and companies.$_GET[s] like '%$_GET[q]%'";
else
    $srchFor = '';
$prefix = 'NL105105';
if(isset($_SESSION['offid']) and $_SESSION['offid']!='0') {
    $office = $amdb->get_row("SELECT * FROM offices WHERE offid='$_SESSION[offid]'");
    $srchFor .= " AND FIND_IN_SET(companies.clid,'" . $office['clients'] . "')";
    $prefix =  $office['reference_prefix'] . $office['certificate_prefix'];
}
$sql = "SELECT * FROM companies
		JOIN users ON  companies.clid = users.clid
		WHERE companies.clof = '0' and users.active = 'y' $srchFor
		ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC";

if (!$companies = $amdb->get_results($sql)) {
    echo "Error: No company found";
    exit();
}
$xcelRow = 1;
$emails = array();
$xcel = array();
$clids = explode(',', $_POST['clids']);

foreach ($companies as $row) {
    $theEmail = '';
    if (in_array($row['clid'], $clids) or $_POST['act'] == 'all') {
        if (!strstr($row['email1'], '@iidc.eu') && validateEmail($row['email1'])) {
            $theEmail = $row['email1'];
        } else if (trim($row['email2'])!='' && !strstr($row['email2'], '@iidc.eu') && validateEmail($row['email2'])) {
            $theEmail = $row['email2'];
        }
        if (trim($theEmail) != '' && !in_array($theEmail, $emails)) {

            $xcel['company'] = trim($row['company_name']);
            $xcel['contact_person'] =  $row['contact_title1'] . ' ' . $row['contact_name1'] . ' ' . $row['contact_surname1'];
            $xcel['email'] = $row['email1'];
            $xcel['country'] = $row['country1'];
            $objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A' . $xcelRow);
            $xcelRow = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;
            $emails[] = $theEmail;
        }
    }
}

$col = 'A';
while (true) {
    $tempCol = $col++;
    $objPHPExcel->getActiveSheet()->getColumnDimension($tempCol)->setAutoSize(true);
    if ($tempCol == $objPHPExcel->getActiveSheet()->getHighestDataColumn()) {
        break;
    }
}
if ($_REQUEST['act'] == 'exportEmailsCsv')
    $ext = '.csv';
else
    $ext = '.xlsx';

// Redirect output to a client’s web browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . str_replace(' ', '-', $sheetTitle) . $ext . '"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0
if ($_REQUEST['act'] == 'exportEmailsCsv')
    $objWriter = new CSV($objPHPExcel);
else
    $objWriter = new Xlsx($objPHPExcel);
$objWriter->save('php://output');
exit();
