<?php
include "../../check_user.inc.php";
if (!isset($_SESSION['username']) or !isset($_REQUEST['clid']))
    exit();
include "$prog_path/config/connect.inc.php";
require_once $prog_path . '/tools/phpexcel/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$objPHPExcel = new Spreadsheet();
function format_cell($xcelRow)
{
    global $objPHPExcel;
    $objPHPExcel->getActiveSheet()->getStyle("A$xcelRow:H$xcelRow")->getFont()->setBold(true);
}

function color_cell($xcelRow)
{
    global $objPHPExcel;
    //color row
    $objPHPExcel->getActiveSheet()->getStyle("A$xcelRow:H$xcelRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEEEE');
}
$xcel = array();
$company_name = array();
if ($company = $amdb->get_row("SELECT company_name FROM companies where  clid = '$_REQUEST[clid]'")) {
    $company_name['company'] = 'Company name';
    $company_name['company_name'] = $company['company_name'];
}

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('Products list');
// Set document properties
$objPHPExcel->getProperties()->setCreator("HQC Netherlands")
    ->setLastModifiedBy("HQC Netherlands")
    ->setTitle("Certificate Products list")
    ->setSubject("Products")
    ->setDescription("Products list created on: " . date("d/m/Y"))
    ->setKeywords("")
    ->setCategory("Products");
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->fromArray($company_name, null, 'A1');
format_cell(1, 'bold');

$xcel['article_nr'] = 'Article Code';
$xcel['product_name'] = 'Product name';
$xcel['description'] = 'Product description';
$xcel['brand_name'] = 'Brand name';
$xcel['production_site'] = 'Production site';
$xcel['status'] = 'Status';
$objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A2');

format_cell(2);
color_cell(2);
$xcelRow = 3;
if (trim($_POST['order_by']) != '') {
    if ($_REQUEST['order_by'] == 'article_nr')
        $order_by = 'ORDER BY TRIM(article_nr)+0 ASC, TRIM(product_name) ASC';
    else
        $order_by = 'ORDER BY TRIM(product_name) ASC';
} else {
    $order_by = '';
}

$sites = array();

if ($sitesData = $amdb->get_results("SELECT * FROM companies_production_sites where  clid = '$_REQUEST[clid]'")) {
    if (isset($sitesData) and count($sitesData) > 0) {
        foreach ($sitesData as $site) {
            if (trim($site['site_name']) != '') {
                $sites[$site['stid']] = $site['site_name'];
            } else {
                if (is_array(json_decode($site['site_address'], true))) {
                    $site_address = json_decode($site['site_address'], true);
                    $sites[$site['stid']] = $site_address['street'];
                }
            }
        }
    }
}

$products_list = array();
if (!$certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates where  crtNr = '$_REQUEST[crtNr]'"))
    exit();
$certificate_number = $certificate['certificate_nr'];
if ($result = $amdb->get_results("SELECT * FROM acms_hdcs_products where  FIND_IN_SET(prdid,'$certificate[products]') and clid='$_REQUEST[clid]' $order_by")) {
    foreach ($result as $row) {
        $xcel['article_nr'] = $row['article_nr'];
        $xcel['product_name'] = $row['product_name'];
        $xcel['description'] = $row['description'];
        $xcel['brand_name'] = $row['brand_name'];
        if (isset($sites[$row['site']]))
            $xcel['production_site'] = $sites[$row['site']];
        else
            $xcel['production_site'] = '';
        if ($row['approved'] == 'y')
            $xcel['status'] = 'Approved';
        elseif ($row['approved'] == 'n')
            $xcel['status'] = 'Not approved';
        else
            $xcel['status'] = 'Pending';
        $objPHPExcel->getActiveSheet()->fromArray($xcel, null, 'A' . $xcelRow);
        $xcelRow = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;
    }
}

$col = 'A';
while (true) {
    $tempCol = $col++;
    $objPHPExcel->getActiveSheet()->getColumnDimension($tempCol)->setAutoSize(true);
    if ($tempCol == 'E') {
        break;
    }
}

$products_path = $prog_path . '/client_data/products/' . $_REQUEST['clid'];
if (!is_dir($products_path))
    mkdir($products_path, 0777, true);
$file_location = $products_path . '/' . $certificate_number . '-products.xlsx';

if (isset($_REQUEST['excel_url'])) {
    $objWriter = new Xlsx($objPHPExcel);
    $objWriter->save($file_location);
    $objPHPExcel->disconnectWorksheets();
    unset($objPHPExcel);
    unset($objWriter);
    $url = str_replace($prog_path, $prog_url, $file_location);
?>
    <script>
        top.closePopup();
    </script>
<?php
    $amdb->post_results('<a href="' . $website . $url . '">' . $website . $url . '</a> <button onclick="copyToClipboard(\'' . $website . $url . '\');">Copy link</button>');
    exit();
}

// Redirect output to a client’s web browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $certificate_number . '-products.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0
$objWriter = new Xlsx($objPHPExcel);
$objWriter->save($file_location);
$objWriter->save('php://output');
exit();
