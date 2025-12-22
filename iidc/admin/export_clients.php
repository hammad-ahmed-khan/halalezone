<?php
if (!isset($_POST['act']) or !isset($_POST['clids'])) {
	exit();
}
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if (!isset($_SESSION['username']) or $_SESSION['user_type'] == 'client')
	exit();
require_once $prog_path . '/tools/phpexcel/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$objPHPExcel = new Spreadsheet();
$sheetTitle = 'Companies list';
$objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC")
	->setLastModifiedBy("HQC")
	->setTitle("Companies list")
	->setSubject("Companies")
	->setDescription("Companies list created on: " . date("d/m/Y"))
	->setKeywords("")
	->setCategory("Companies");
$objPHPExcel->setActiveSheetIndex(0);
//getting the list of clients
$srchFor = '';
$prefix = 'NL105105';
if(isset($_SESSION['offid']) and $_SESSION['offid']!='0') {
	$office = $amdb->get_row("SELECT * FROM offices WHERE offid='$_SESSION[offid]'");
	$srchFor .= " AND FIND_IN_SET(companies.clid,'" . $office['clients'] . "')";
	$prefix =  $office['reference_prefix'] . $office['certificate_prefix'];
}
$sql = "SELECT * FROM companies
		JOIN users ON  companies.clid=users.clid
		WHERE companies.clof='0' and users.active='y' $srchFor
		ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC";
if (!$companies = $amdb->get_results($sql)) {
	echo "Error: No company found";
	exit();
}
$xcel['company_id'] = 'Company ID';
$xcel['company_name'] = 'Company name';
$xcel['contact_person'] = 'Contact person';
$xcel['function'] = 'Function';
$xcel['street'] = 'Street';
$xcel['zip'] = 'zipcode';
$xcel['city'] = 'City';
$xcel['country'] = 'Country';
$xcel['tel'] = 'Telephone';
$xcel['mobile'] = 'Mobile';
$xcel['email'] = 'Email';
$xcel['web'] = 'Website';
$xcel['scope_of_activities'] = 'Scope of activities';
$xcel['cocNr'] = 'Chamber of commerce';
$xcel['vatNr'] = 'VAT number';
$xcel['date'] = 'Registration date';
$objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A1');
$objPHPExcel->getActiveSheet()->getStyle("A1:P1")->getFont()->setBold(true);
$xcelRow = 2;
$clids = explode(',', $_POST['clids']);
foreach ($companies as $row) {
	if (in_array($row['clid'], $clids) or $_POST['act'] == 'all') {
		$user = array();
		$xcel['company_id'] = $prefix . str_pad($row['clid'], 5, '0', STR_PAD_LEFT);
		$xcel['company_name'] = trim($row['company_name']);
		$xcel['contact_person'] =  $row['contact_title1'] . ' ' . $row['contact_name1'] . ' ' . $row['contact_surname1'];
		$xcel['function'] = $row['function1'];
		$xcel['street'] = $row['street1'];
		$xcel['zip'] = $row['zip1'];
		$xcel['city'] = $row['city1'];
		$xcel['country'] = $row['country1'];
		$xcel['tel'] = $row['tel1'];
		$xcel['mobile'] = $row['mobile'];
		$xcel['email'] = $row['email1'];
		$xcel['web'] = $row['web'];
		$xcel['scope_of_activities'] = trim($row['scope_of_activities']);
		$xcel['cocNr'] = $row['cocNr'];
		$xcel['vatNr'] = $row['vatNr'];
		$xcel['date'] = $row['date'];
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
