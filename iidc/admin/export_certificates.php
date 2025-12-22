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
$objPHPExcel = new Spreadsheet();
$sheetTitle = 'Certificates list';
$objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC")
    ->setLastModifiedBy("HQC")
    ->setTitle("Certificates list")
    ->setSubject("Certificates")
    ->setDescription("Certificates list created on: " . date("d/m/Y"))
    ->setKeywords("")
    ->setCategory("Certificates");
$objPHPExcel->setActiveSheetIndex(0);
//getting the list of clients
    $srchFor = '';
$prefix = 'NL105105';
if(isset($_SESSION['offid']) and $_SESSION['offid']!='0') {
    $office = $amdb->get_row("SELECT * FROM offices WHERE offid='$_SESSION[offid]'");
    $srchFor .= " AND FIND_IN_SET(companies.clid,'" . $office['clients'] . "')";
    $prefix =  $office['reference_prefix'] . $office['certificate_prefix'];
}
$sql = "SELECT companies.clid,companies.company_name,companies.country1,users.clid, acms_halal_certificates.* FROM companies
		JOIN users ON  companies.clid=users.clid
        JOIN acms_halal_certificates ON companies.clid = acms_halal_certificates.clid
		WHERE companies.clof='0' and users.active='y' $srchFor
		ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC, acms_halal_certificates.date_of_expiry DESC";
if (!$companies_found = $amdb->get_results($sql)) {
    echo "Error: No company found";
    exit();
}
$companies = array();
foreach ($companies_found as $row) {
    if (!isset($companies[$row['clid']]))
        $companies[$row['clid']] = $row;
}
$sql = "SELECT companies.clid,companies.company_name,companies.country1,users.clid FROM companies
		JOIN users ON  companies.clid=users.clid
		WHERE companies.clof='0' and users.active='y' $srchFor
		ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC";
if (!$companies_found = $amdb->get_results($sql)) {
    echo "Error: No company found";
    exit();
}
foreach ($companies_found as $row) {
    if (!isset($companies[$row['clid']]))
        $companies[$row['clid']] = $row;
}
$xcel['company_id'] = 'Company ID';
$xcel['company_name'] = 'Company name';
$xcel['category'] = 'Category';
$xcel['scope_of_certification'] = 'Scope(s)';
$xcel['country'] = 'Location';
$xcel['date_of_issue'] = 'Date of issue';
$xcel['date_of_expiry'] = 'Date of expiry';
$xcel['certificate_nr'] = 'Certificate Nr.';
$xcel['scope_of_certification'] = 'Scope(s)';
$xcel['reference_standards'] = 'Reference standards';
$xcel['status'] = 'Status';
$objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A1');
$objPHPExcel->getActiveSheet()->getStyle("A1:P1")->getFont()->setBold(true);
$xcelRow = 2;
$clids =explode(',',$_POST['clids']);
foreach ($companies as $row) {
    if(in_array($row['clid'],$clids) or $_POST['act']=='all') {
    $user = array();
    $xcel['company_id'] = $prefix . str_pad($row['clid'], 5, '0', STR_PAD_LEFT);
    $xcel['company_name'] = trim($row['company_name']);
    if (isset($row['category'])) {
        $xcel['category'] = $row['category'];
        $xcel['scope_of_certification'] = $row['scope_of_certification'];
        $xcel['country'] = $row['country1'];
        $xcel['date_of_issue'] = date("d/m/Y", $row['date_of_issue']);
        $xcel['date_of_expiry'] = date("d/m/Y", $row['date_of_expiry']);
        $xcel['certificate_nr'] = $row['certificate_nr'];
        $xcel['scope_of_certification'] = trim($row['scope_of_certification']);
        $xcel['reference_standards'] = $row['reference_standards'];
        $xcel['status'] = ($row['date_of_expiry'] < time()) ? 'Expired' : 'Valid';
    } else {
        $na = 'N/A';
        $xcel['category'] = $na;
        $xcel['scope_of_certification'] = $na;
        $xcel['country'] = $na;
        $xcel['date_of_issue'] = $na;
        $xcel['date_of_expiry'] = $na;
        $xcel['certificate_nr'] = $na;
        $xcel['scope_of_certification'] = $na;
        $xcel['reference_standards'] = $na;
        $xcel['status'] = $na;
    }
    $objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A' . $xcelRow);
    $xcelRow = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;
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
// Redirect output to a client’s web browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $sheetTitle . '.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0
$objWriter = new Xlsx($objPHPExcel);
$objWriter->save('php://output');
exit();
