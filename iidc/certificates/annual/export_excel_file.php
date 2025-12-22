<?php
if (!isset($_POST['items']))
    return;
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if (!isset($_SESSION['username']))
    exit();

require_once $prog_path . '/tools/phpexcel/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;

$objPHPExcel = new Spreadsheet();

$sheetTitle = $_REQUEST['tp']=='annual'? 'Annual Certificates':'Certificates type '.$_REQUEST['tp'];
$objPHPExcel->getActiveSheet()->setTitle($sheetTitle);

// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC")
    ->setLastModifiedBy("HQC")
    ->setTitle($sheetTitle)
    ->setSubject($sheetTitle)
    ->setDescription($sheetTitle." created on: " . date("d/m/Y H:i:s"))
    ->setKeywords("")
    ->setCategory("Certificates");

$objPHPExcel->setActiveSheetIndex(0);

$xcelRow = 1;
$chars = range('A', 'Z');
$lastCol = 'Z';
if (is_array(json_decode($_POST['items'], true))) {
    $items = json_decode($_POST['items'], true);
    $lastCol = $chars[count($items[0]) - 1];
    foreach ($items as $item) {
        $objPHPExcel->getActiveSheet()->fromArray($item, null, 'A' . $xcelRow);
        $xcelRow = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;
    }
}

$objPHPExcel->getActiveSheet()->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

$col = 'A';
while (true) {
    $tempCol = $col++;
    $objPHPExcel->getActiveSheet()->getColumnDimension($tempCol)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($tempCol)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle($tempCol)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    if ($tempCol == $lastCol) {
        break;
    }
}

// Redirect output to a client’s web browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. str_replace(' ','-',$sheetTitle).'-'. date("d-m-Y.H_i_s") . '.xlsx"');
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
