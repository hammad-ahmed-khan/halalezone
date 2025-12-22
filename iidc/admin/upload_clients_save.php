<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
include_once("$prog_path/make_query.inc.php");

if (isset($_POST['act']) and $_POST['act'] == "upload_clients") {
    if (!isset($_FILES['excelFile']['name']) or trim($_FILES['excelFile']['name']) == '') {
        $amdb->post_results("Excel file is missing");
        exit();
    } else {
        $attDir = "$prog_path/data/temp/uploads";
        if (!is_dir($attDir)) {
            mkdir($attDir, true);
            chmod($attDir, 0777);
        }
        $ext = pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION);
        if ($ext != 'xlsx' and $ext != 'xls') {
            $amdb->post_results("please upload excel file");
            exit();
        }
        $excelFile = "$attDir/" . time() . ".xlsx";
        if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $excelFile)) {
            require_once $prog_path . '/tools/phpexcel/hqc-excel.class.php';
            $excel = new hqcExcel;
            $excelData = array();
            $excelData = $excel->read_excel_content($excelFile);


            if (count($excelData) == 0) {
                $amdb->post_results("Excel file is empty");
                exit();
            }
            $table_columns = $amdb->get_columns('companies');
            // print_r($table_columns);
            // print_r($excelData[0]);
            $data_columns = array();

            $data_columns[0] = 'company_name';
            $data_columns[1] = 'street1';
            $data_columns[2] = 'city1';
            $data_columns[3] = 'zip1';
            $data_columns[4] = 'country1';
            $data_columns[5] = 'vatNr';
            $data_columns[6] = 'contact_name1';
            $data_columns[7] = 'tel1';
            $data_columns[8] = 'email1';
            $data_columns[9] = 'scope_of_activities';
            $clid = 1;
            unset($excelData[0]);
            foreach ($excelData as $company_data) {
                $client_extra = array();
                $extra = 1;
                $data = array();
                foreach ($company_data as $key => $value) {

                    if (isset($data_columns[$key])) {
                        $data[$data_columns[$key]] = $value;
                    } else {
                        $client_extra[$extra] = $value;
                        $extra++;
                    }
                }
                $data['client_extra'] = json_encode($client_extra);
                $data['active'] = 'y';
                $data['approved'] = 'y';
                $clid = $amdb->insert('companies', $data);
                $data['status'] = 'active';
                $data['email'] = isset($data['email1']) ? $data['email1'] : '';
                $data['username'] = 'CLN' . str_pad($clid, 3, '0', STR_PAD_LEFT);
                //create a random password
                $data['password'] = create_password();
                $data['clid'] = $clid;
                $amdb->insert('users', $data);
                //
            }

            if (file_exists($excelFile))
                unlink($excelFile);
        }
    }
    echo '<script>parent.location.reload();</script>';
    exit();
}
