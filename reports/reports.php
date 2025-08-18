<?php
require_once(dirname(__FILE__).'/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet;

function getIngredientsExcelReport($data){
    $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/ingredients.xls");

    $baseRowIndex = 3;
    foreach ($data as $work) {
        $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.($baseRowIndex), $work['time']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.($baseRowIndex), $work['rmid']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.($baseRowIndex), $work['name']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.($baseRowIndex), $work['supplier']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.($baseRowIndex), $work['producer']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.($baseRowIndex), str_ireplace("<br/>", "", $work['deviation']));
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.($baseRowIndex), str_ireplace("<br/>", "", $work['measure']));
        $baseRowIndex ++;
    }

    $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
    $folder =  __DIR__."/../files/reports/";
    $result['name'] = 'ingredients.xls';
    $result['path'] = 'ingredients'.time().".xls";
    $result['url'] = "files/reports/".$result['path'];
    $result['path'] = $folder.$result['path'];
    $spreadsheet->save($result['path']);
    return $result;
}

function getAllIngredientsExcelReport($data){
  $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/ingredients_all.xls");

  $baseRowIndex = 3;
  foreach ($data as $work) {
      $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.($baseRowIndex), $work['rmid']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.($baseRowIndex), $work['rmcode']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.($baseRowIndex), $work['name']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.($baseRowIndex), $work['conf'] == 1 ? 'Yes' : 'No');
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.($baseRowIndex), $work['supplier']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.($baseRowIndex), $work['producer']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.($baseRowIndex), $work['halalcert'] == 1 ? 'Yes' : 'No');
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('I'.($baseRowIndex), $work['cb']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('J'.($baseRowIndex), $work['time']);
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('K'.($baseRowIndex), str_ireplace("<br/>", "", $work['deviation']));
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('L'.($baseRowIndex), str_ireplace("<br/>", "", $work['measure']));
      $baseRowIndex ++;
  }

  $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
  $folder =  __DIR__."/../files/reports/";
  $result['name'] = 'ingredients.xls';
  $result['path'] = 'ingredients'.time().".xls";
  $result['url'] = "files/reports/".$result['path'];
  $result['path'] = $folder.$result['path'];
  $spreadsheet->save($result['path']);
  return $result;
}

function getProductsExcelReport($data) {
    $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/products.xls");

    $baseRowIndex = 3;
    foreach ($data as $work) {
        $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
        
        // Handle hcpid as text
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValueExplicit(
                'B'.($baseRowIndex),
                $work['hcpid'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.($baseRowIndex), $work['product']);
        
        // Handle EAN as text
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValueExplicit(
                'D'.($baseRowIndex),
                $work['ean'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            
        $baseRowIndex++;
    }

    // Optional: Set columns B and D to text format for all rows
    $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
    $spreadsheet->getActiveSheet()
        ->getStyle('B3:B'.$highestRow)
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        
    $spreadsheet->getActiveSheet()
        ->getStyle('D3:D'.$highestRow)
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

    $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
    $folder = __DIR__."/../files/reports/";
    $result['name'] = 'products.xls';
    $result['path'] = 'products_'.time().".xls";
    $result['url'] = "files/reports/".$result['path'];
    $result['path'] = $folder.$result['path'];
    $spreadsheet->save($result['path']);
    return $result;
}

function getTasksExcelReport($data){

    $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/tasks.xls");

    $baseRowIndex = 3;
    foreach ($data as $work) {
        $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.($baseRowIndex), "RMC_".$work['idi']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.($baseRowIndex), $work['deviation']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.($baseRowIndex), $work['measure']);
        $baseRowIndex ++;
    }

    $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
    $folder =  __DIR__."/../files/reports/";
    $result['name'] = 'tasks.xls';
    $result['path'] = 'tasks'.time().".xls";
    $result['url'] = "files/reports/".$result['path'];
    $result['path'] = $folder.$result['path'];
    $spreadsheet->save($result['path']);
    return $result;
}

function getConfirmedProductsExcelReport($data, $file_name = 'products_confirmed') {
    $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/products_confirmed.xlsx");

    $baseRowIndex = 2;
    foreach ($data as $work) {
        $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
        
        // Handle EAN code as text to prevent scientific notation
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValueExplicit(
                'A'.($baseRowIndex),
                $work['ean'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.($baseRowIndex), $work['product']);
        $baseRowIndex++;
    }

    // Optional: Set the entire column A to text format to be extra safe
    $spreadsheet->getActiveSheet()
        ->getStyle('A2:A'.$spreadsheet->getActiveSheet()->getHighestRow())
        ->getNumberFormat()
        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

    $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
    $folder = __DIR__."/../files/reports/";
    $result['name'] = $file_name.'.xls';
    $result['path'] = $file_name.'_'.time().".xls";
    $result['url'] = "files/reports/".$result['path'];
    $result['path'] = $folder.$result['path'];
    $spreadsheet->save($result['path']);
    return $result;
}

function getAllClientsExcelReport($data, $file_name = 'clients'){
    $spreadsheet = PhpSpreadsheet\IOFactory::load(dirname(__FILE__)."/xlstemplates/clients.xlsx");

    $baseRowIndex = 2;
    foreach ($data as $work) {
        $spreadsheet->setActiveSheetIndex(0)->insertNewRowBefore($baseRowIndex, 1);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.($baseRowIndex), $work['name']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.($baseRowIndex), $work['email']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.($baseRowIndex), $work['address']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.($baseRowIndex), $work['city']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.($baseRowIndex), $work['zip']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.($baseRowIndex), $work['country']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.($baseRowIndex), $work['industry']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.($baseRowIndex), $work['category']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('I'.($baseRowIndex), $work['vat']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('J'.($baseRowIndex), $work['contact_person']);
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('K'.($baseRowIndex), $work['phone']);
        $baseRowIndex ++;
    }

    $spreadsheet = PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
    $folder =  __DIR__."/../files/reports/";
    $result['name'] = $file_name.'.xls';
    $result['path'] = $file_name.'_'.time().".xls";
    $result['url'] = "files/reports/".$result['path'];
    $result['path'] = $folder.$result['path'];
    $spreadsheet->save($result['path']);
    return $result;
}
