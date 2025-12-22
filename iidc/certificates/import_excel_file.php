<?php
if (!isset($_FILES) && !isset($_FILES['file']) && !isset($_FILES['file']['name']))
    return;
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if ($ext != 'xls' and $ext != 'xlsx') {
    echo "error:Please select excel file (xlsx or xls)";
    exit();
}

$excel_file = $hcp_path . '/data/temp/' . time() . '.' . $ext;

if (move_uploaded_file($_FILES['file']['tmp_name'], $excel_file)) {
    require_once $prog_path . '/tools/phpexcel/hqc-excel.class.php';
    $excel = new hqcExcel;
    $excelData = $excel->read_excel_content($excel_file);
    unlink($excel_file);
    if (isset($excelData) and is_array($excelData) and count($excelData) > 0) {
        unset($excelData[0]); // Remove the first row if it contains headers
        if (count($excelData) > 0) {
            echo json_encode($excelData, JSON_UNESCAPED_UNICODE);
        } else {
            echo 'error:The file is empty or not valid.';
        }
    } else {
        echo 'error:The file is empty.';
    }
}
