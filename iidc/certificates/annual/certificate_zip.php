<?php
include "../../check_user.inc.php";
global $prog_path;
require_once($prog_path.'/pdf/tcpdf/tcpdf.php');
require_once ($prog_path."/pdf/tcpdf/fpdi/fpdi.php");
require_once($prog_path.'/pdf/tcpdf/config/tcpdf_config.php');
// Extend the TCPDF class to create custom Header and Footer
generatePdfPages($_GET['fl']);
function generatePdfPages($certFile,$start=1,$end=2,$pg=1){
    $pdf = new FPDI(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Ayoub Media');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
	$pageCount = $pdf->setSourceFile($certFile);
		for ($i = 1; $i <= $pageCount; $i++) {
			if($i >= $start and $i<=$end){
            $pdf->addPage();
            $pdf->SetAutoPageBreak(false, 0);
			$tplIdx = $pdf->importPage($i,'/MediaBox');
			$pdf->useTemplate($tplIdx,0,0);
			} else {
            echo $pageCount."-$i:$start>$end<br/>";
		    $fileFinal = str_replace('.pdf','-'.$pg.'.pdf',$certFile);
		    $pdf->Output($fileFinal,'F');
        if($end<$pageCount)
        generatePdfPages($_GET['fl'],$start+2,$end+2,$pg+1);
        else
        exit();
        }
		}
	}