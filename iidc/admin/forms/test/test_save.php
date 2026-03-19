<?php
if (!session_id()) {session_start();}
if(!isset($_POST['act']) or !isset($_SESSION['username'])){exit();};
include $_SESSION['hqc_path'].'/load.inc.php';

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'change_status' and isset($_POST['id'])){
if ($hqcdb->query("UPDATE hqc_forms_test SET status = '$_REQUEST[status]' WHERE tstid = '$_REQUEST[id]'"))
    _e('changed');
    else
    _e('error:Status not changed');
    exit();
}

if($_POST['act'] == 'delete' and isset($_POST['id'])){
    $hqcdb->update("hqc_forms_test",array('status'=>'deleted'),"tstid = '$_POST[id]'");
    echo "deleted";
    exit();
}

if($_POST['act'] == 'deleteFile'){
    if(!isset($_POST['url'])){
        echo "error: Nothing to delete";
    } else {
    $fileToDelete = hqc_path .$_POST['url'];
    if(file_exists($fileToDelete)){
        unlink($fileToDelete);
        echo "deleted";
        } else {
            echo "error:File not found.";
        }
    }
exit();
}

if(!isset($_POST['foid']))
exit();

$form = array();
$form['foid'] = $_POST['foid'];

if($theForm = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid = '$_POST[foid]'")){

    $form['content_name'] = $theForm['form_name'];
    if(trim($theForm['records_listing']) != '' and is_array(json_decode($theForm['records_listing'],true))){
        $records_listing = json_decode($theForm['records_listing'],true);
        if(isset($records_listing['forms_test']))
        $form['content_name'] = $_POST[$records_listing['forms_test']['column']];
    }


    $form['form_content'] = json_encode($_POST,JSON_UNESCAPED_UNICODE);



function uploadFiles($tstid){

    if(isset($_FILES) and count($_FILES)>0){
        $uploadDir = hqc_path ."/data/temp/forms/$tstid";

        foreach($_FILES as $key=>$file){

        $upload_dir = $uploadDir."/".$key;

        if(!is_dir($upload_dir))
        mkdir($upload_dir,0777,true);

            foreach($file['tmp_name'] as $kFile=>$vFile){

                if(is_array($vFile)){
                    $fileItem = array_key_first($vFile);
                    $name = $file['name'][$kFile][$fileItem];
                    $tmp_name = $file['tmp_name'][$kFile][$fileItem];
                } else {
                    $name = $file['name'][$kFile];
                    $tmp_name = $file['tmp_name'][$kFile];
                }

                if(trim($name) != '' and is_uploaded_file($tmp_name)){
                    move_uploaded_file($tmp_name, $upload_dir."/".$name);
                }
            };
        };
    }
}


$goBack = dirname($_SESSION['user_url']);
    if($_POST['act'] == 'insert'){
        $tstid = $hqcdb->insert("hqc_forms_test",$form);
        uploadFiles($tstid);
        post_results('url',$goBack.'/?inc=test&foid = '.$_POST['foid']);
        exit();
    }

    if($_POST['act'] == 'update' and isset($_POST['tstid'])){
        $hqcdb->update("hqc_forms_test",$form,"tstid = '$_POST[tstid]'");
        uploadFiles($_POST['tstid']);
        post_results('url',$goBack.'/?inc=test&foid='.$_POST['foid']);
        exit();
    }
}