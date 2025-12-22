<?php
include "../../check_user.inc.php";
if (!isset($_GET['fl']))
    exit();

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

global $prog_path;

// Extend the TCPDF class to create custom Header and Footer
if (strstr($_GET['fl'], '/client_data/certificates/'))
    $file = str_replace('\\', '/', $prog_path . $_GET['fl']);
else $file = str_replace('\\', '/', $prog_path . '/client_data/certificates/' . $_GET['fl']);

if (!file_exists($file))
    return;

$time = time();
if (isset($_GET['dir']))
    $tempDir = $prog_path . '/data/temp/certificates/' . $_GET['dir'];
else
    $tempDir = $prog_path . '/data/temp/certificates/' . $time;

if (isset($_GET['dir']) && isset($_GET['act']) && $_GET['act'] == 'download') {
    downloadzip();
    exit();
}

if (!is_dir($tempDir))
    mkdir($tempDir, 0777, true);

if ($certificate = $amdb->get_row("SELECT products FROM $tbl[prefix]_halal_certificates WHERE url='$_GET[fl]'")) {
    $certificate_products = explode(',', $certificate['products']);
    //turn value into keys
    $products = [0];
    if ($products_all = $amdb->get_results("SELECT * FROM acms_hdcs_products WHERE prdid IN ($certificate[products])")) {
        foreach ($products_all as $prdKey => $prdValue) {
            //remove all not universal characters and replace with spaces
            $prdValue['product_name'] = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $prdValue['article_nr'].' '.$prdValue['product_name']);
            //remove all double spaces
            $prdValue['product_name'] = trim(preg_replace('/\s+/', ' ', $prdValue['product_name']));
            //replace space with _
            $prdValue['product_name'] = str_replace(' ', '-', $prdValue['product_name']);

            $products[] = $prdValue['product_name'];
        }
    }
}

if ($protected_pdf = get_option('protected_pdf')) {
    $protected = json_decode($protected_pdf, true);
}

$start = 1;
$end = 1;
$step = 1;

if ($_GET['pgs'] == 'major') {
    $start = 2;
    $end = 2;
} else if ($_GET['pgs'] == 'preceded') {
    $end = 2;
    $step = 2;
}

use setasign\Fpdi\Tcpdf\Fpdi;

require_once $prog_path . '/pdf/tcpdf/tcpdf.php';
require_once $prog_path . '/pdf/fpdi/autoload.php';
$pageCount = (new Fpdi())->setSourceFile($file);

generatePdfPages($start, $end, 1);
function generatePdfPages($start = 1, $end = 1, $pg = 1)
{
    global $tempDir, $step, $file, $protected, $pdf, $pageCount, $products,$time;
    $pdf = new Fpdi();
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    if (isset($protected) && isset($protected['annual']) && isset($protected['protect']) && trim($protected['password']) != '') {
        $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected['password'], 0, null);
    }

    $pdf->setSourceFile($file);

    if ($_GET['pgs'] == 'major') {
        $pdf->addPage();
        $fpIdx = $pdf->importPage(1);
        $pdf->useTemplate($fpIdx);
    }

    for ($i = $start; $i <= $end; $i++) {
        $pdf->addPage();
        $tplIdx = $pdf->importPage($i);
        $pdf->useTemplate($tplIdx);
        if ($i >= $pageCount)
            break;
    }

    $fileFinal = $tempDir . '/' . $products[$pg] . '.pdf';

    $pdf->Output($fileFinal, 'F');
    if ($end < $pageCount) {
        generatePdfPages($start + $step, $end + $step, $pg + 1);
    } else {
        header('location: download.php?act=download&dir=' . $time. '&fl=' . $_GET['fl']);
        exit();
    }
}

function downloadzip()
{
    global $tempDir, $file;

    //download the files as zip
    $zip = new ZipArchive;
    $zipFile = str_replace('.pdf', '.zip', $file);

    if (file_exists($zipFile))
        unlink($zipFile);
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $files = glob($tempDir . '/*.pdf');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($zipFile));
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
    };

    //delete all pdf files with the temp folder
    $files = glob($tempDir . '/*.pdf');
    foreach ($files as $file) {
        unlink($file);
    }
    rmdir($tempDir);
}
