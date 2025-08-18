<?php

include_once "../notifications/notifyfuncs.php";

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(E_ALL);

ini_set('memory_limit', '128M');
ini_set('upload_max_filesize', '64M');
ini_set('post_max_size', '32M');
ini_set('max_execution_time', 50000);
ini_set('safe_mode', 'off');

require('UploadHandler.php');
require('GoogleDriveFunctions.php');
define('LOCAL_FILE_DIR','files');
define('DRIVE_FILE_DIR','CRM');


function getLocalFileDetails(){
	// Initializing normal file upload handler
	// Whether client or audit
	$decode = file_get_contents( __DIR__ ."/../config.json");
	$config=json_decode($decode, TRUE);
	// make path for saving file on hosting
	switch ($_POST['infoType']){
	case "audit":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/Audit/".$_POST['auditorid']."/".$_POST['subFolder']."/";
		$hostUrl=$config['filesfolder']."/Audit/".$_POST['auditorid']."/".$_POST['subFolder']."/";
		break;
    case "application":
            $options['upload_dir'] =
                __DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/application/".$_POST['cycle']."/".$_POST['subcycle']."/";
            $hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/application/".$_POST['cycle']."/".$_POST['subcycle']."/";
            $body['subject'] = "New Document notification";
            $body['header'] = "New ".$_POST['docType']." has been uploaded to ".$_POST['cycle']." / ".$_POST['subcycle'].", client ".$_POST['client'];
            break;
    case "product":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/products/".$_POST['product']."/";
		$hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/products/".$_POST['product']."/";
		$body['subject'] = "New Document notification";
		$body['header'] = "Client ".$_POST['client']." added a new document for the product ".$_POST['product'];
		break;
    case "ingredient":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/ingredients/".$_POST['ingredient']."/";
		$hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/ingredients/".$_POST['ingredient']."/";
		$body['subject'] = "New Document notification";
		$body['header'] = "Client ".$_POST['client']." added a new document for the ingredient ".$_POST['ingredient'];
		break;
	case "QM":
		$options['upload_dir'] =__DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/QM documents/".$_POST['year']."/".$_POST['subFolder']."/";
		$hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/QM documents/".$_POST['year']."/".$_POST['subFolder']."/";

		$body['subject'] = "New Document notification";
		$body['header'] = "Client ".$_POST['client']." added a new ".$_POST['subFolder']." document for year ".$_POST['year'];
		break;
    case "certificate":
        $options['upload_dir'] =
         __DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/certificates/";
        $hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/certificates/";
        break;
    case "file":
        $options['upload_dir'] =
        __DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/files/";
        $hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/files/";
        break;
	default:	
		{	
			$options['upload_dir'] =__DIR__ ."/../".$config['filesfolder']."/";
			$hostUrl=$config['filesfolder']."/";
		}
	};
	$options['upload_url'] = $options['upload_dir'];
	$upload_handler = new UploadHandler($options);
	$fileDetails = $upload_handler->post(false);
	
	// make path for saving file on Google Drive
	switch ($_POST['infoType']){
	case "audit":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/Audit/".$_POST['auditorid']."/".$_POST['subFolder']."/";
		break;
    case "product":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/products/".$_POST['product']."/";
		break;
    case "ingredient":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/ingredients/".$_POST['ingredient']."/";
		break;
	case "QM":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/QM documents/".$_POST['year']."/".$_POST['subFolder']."/";
		break;
    case "application":
        $fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/application/".$_POST['cycle']."/".$_POST['subcycle']."/";
        break;
    case "certificate":
        $fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/certificates/";
        break;
    case "file":
        $fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/files/";
        break;
	default:
		{
			$fileDetails["uploadDir"]=DRIVE_FILE_DIR;
		}
	};

	$body['body'] = "";
	// REmove when add Google Drive upload !!!!!!!!!!!!!!!!!
	foreach($fileDetails["files"] as $file){
		$file->{"folderType"} = $_POST['folderType'];
		// path to write/delete file
		$file->{"url"} = str_replace("\\","/",$file->{"url"});
		// path to open the file
		$file->{"hostUrl"} = $hostUrl.rawurlencode($file->{"name"});
		$body['body'] = $body['body']."<br/>". $file->{"name"};
	}

    // send notification
	if($_POST['infoType'] == 'product' || $_POST['infoType'] == 'ingredient' ||
        $_POST['infoType'] == 'QM' || $_POST['infoType'] == 'application')
		sendEmail($body);

   return $fileDetails;
}

function removeProcessedFiles($fileDetails){
    foreach($fileDetails["files"] as $file){
        unlink(LOCAL_FILE_DIR . "/" . $file->name);
        if(file_exists(LOCAL_FILE_DIR . "/thumbnail/" . $file->name)){
            unlink(LOCAL_FILE_DIR . "/thumbnail/" . $file->name);
        }
    }
}

function uploadToGoogleDrive($fileDetails){
    // Google drive file upload client
    $client = gfGetClient();
    $service = new Google_Service_Drive($client);
    foreach($fileDetails["files"] as $file){
        $fileInfo = gfUploadFile($client, $service, substr($fileDetails["files"][0]->{"url"}, 0, strrpos($fileDetails["files"][0]->{"url"}, '/',0)), $file->name, $file->type, $fileDetails["uploadDir"]);
		$file->{"folderType"} = $_POST['folderType'];
        $file->{"googleDriveUrl"} = $fileInfo ["alternateLink"];
        $file->{"googleDriveId"} = $fileInfo ["id"];
		$file->{"hostUrl"} = $fileDetails["files"][0]->{"hostUrl"};
    } 
    return $fileDetails;
}

function sendResponse($response){
    echo json_encode($response);  
    return $response;
}

function deleteFromGoogleDrive($id){
    $client = gfGetClient();
    $service = new Google_Service_Drive($client);
    return gfDeleteFile($service, $id);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case "POST":
		//sendResponse( getLocalFileDetails());
		sendResponse( uploadToGoogleDrive( getLocalFileDetails() ) ); 
       break;
    case "GET":
        sendResponse(deleteFromGoogleDrive($_GET["deleteId"]));
		unlink(urldecode($_GET["deleteName"]));
}
?>

