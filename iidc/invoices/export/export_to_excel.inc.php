<?php
require_once $prog_path . '/tools/phpexcel/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
$objPHPExcel = new Spreadsheet();
function format_cell($xcelRow,$ft='number'){
	global $objPHPExcel;
	if ($ft=='number'){
	$objPHPExcel->getActiveSheet()->getStyle('F'.$xcelRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('G'.$xcelRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('H'.$xcelRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	} else {
	$objPHPExcel->getActiveSheet()->getStyle("A$xcelRow:H$xcelRow")->getFont()->setBold(true);
	}
}
$objPHPExcel->setActiveSheetIndex(0);
if($_POST['show']=='credit')
$sheetTitle = 'credit notes '.$_POST['year'];
else
$sheetTitle = $_POST['show']." invoices ".$_POST['year'];
if($_POST['period']=='month')
$sheetTitle .= ' month '.$_POST['month'].'';
if($_POST['period']=='quarter')
$sheetTitle .= ' quarter '.$_POST['quarter'];
if($_POST['period']=='date')
$sheetTitle = str_replace('-20','-','invoices '.str_replace('/','-',$_POST['date_from']).' to '.str_replace('/','-',$_POST['date_to']));
$objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC")
							 ->setLastModifiedBy("HQC")
							 ->setTitle("Invoices")
							 ->setSubject("PInvoices")
							 ->setDescription("Invoices created on: ".date("d/m/Y"))
							 ->setKeywords("")
							 ->setCategory("Invoices");
$subtotal =0;
$vat =0;
$total =0;
$objPHPExcel->setActiveSheetIndex(0);
	$xcel['company_id']= 'Company ID';
	$xcel['company_name'] = 'Company Name';
	$xcel['service_type'] = 'Service type';
	$xcel['invoice_number'] = 'Invoice Number';
	$xcel['date'] = 'Date';
	$xcel['subtotal'] = 'Subtotal';
	$xcel['vat'] = 'VAT';
	$xcel['total'] = 'Total';
	$objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A1');
	format_cell(1,'bold');
$xcelRow = 2;
foreach ($invoices as $row){
	$user= array();
	$subtotal=$subtotal + $row['subtotal'];
	$vat=$vat + $row['vat'];
	$total=$total + $row['total'];
	$st =  $row['service_type'];
	if($row['invoice_type'] == 'batch')
	$row['invoice_type'] = 'a';
	$xcel['company_id']= 'NL105105'. str_pad($row['clid'],5,'0',STR_PAD_LEFT);
	$xcel['company_name'] = $row['company_name'].' ('.$row['country1'].')';
	$xcel['service_type'] = $service_types[$row['invoice_type']];
	$xcel['invoice_number'] = $row['invoice_nr'];
	$xcel['date'] = date("d/m/Y", strtotime($row['inserted_on']));
	$xcel['subtotal'] = $row['subtotal'];
	$xcel['vat'] = $row['vat'];
	$xcel['total'] = $row['total'];
	if (trim($row['invoice_type'])=='credit_note'){
		if(!strstr($xcel['subtotal'],'-'))
		$xcel['subtotal'] 	= '-' . $xcel['subtotal'];
		if(!strstr($xcel['vat'],'-'))
		$xcel['vat'] 		= '-' . $xcel['vat'];
		if(!strstr($xcel['total'],'-'))
		$xcel['total'] 		= '-' . $xcel['total'];
	}
	$objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A'.$xcelRow);
	format_cell($xcelRow,'number');
	$xcelRow = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
}
$objPHPExcel->getActiveSheet()->setCellValue('F'.$xcelRow,'=SUM(F2:F' . ($xcelRow - 1) .')');
$objPHPExcel->getActiveSheet()->setCellValue('G'.$xcelRow,'=SUM(G2:G' . ($xcelRow - 1) .')');
$objPHPExcel->getActiveSheet()->setCellValue('H'.$xcelRow,'=SUM(H2:H' . ($xcelRow - 1) .')');
format_cell($xcelRow,'number');
format_cell($xcelRow,'bold');
$col = 'A';
while(true){
    $tempCol = $col++;
    $objPHPExcel->getActiveSheet()->getColumnDimension($tempCol)->setAutoSize(true);
    if($tempCol == $objPHPExcel->getActiveSheet()->getHighestDataColumn()){
        break;
    }
}
// Redirect output to a client’s web browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$sheetTitle.'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
$objWriter = new Xlsx($objPHPExcel);
$objWriter->save('php://output');
exit();