<?php

include_once "../notifications/notifyfuncs.php";
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);


//ini_set("display_startup_errors", 1);
//ini_set("display_errors", 1);
//error_reporting(E_ALL);

ini_set('memory_limit', '128M');
ini_set('upload_max_filesize', '64M');
ini_set('post_max_size', '32M');
ini_set('max_execution_time', 50000);
//ini_set('safe_mode', 'off');

require('UploadHandler.php');
require('GoogleDriveFunctions.php');
define('LOCAL_FILE_DIR','files');
define('DRIVE_FILE_DIR','CRM');

function getLocalFileDetails(){
	$dbo = &$GLOBALS['dbo'];
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
	case "tickets":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/tickets/";
		$hostUrl=$config['filesfolder']."/tickets/";
		if (!file_exists($options['upload_dir'])) {
			mkdir($options['upload_dir'], 0777, true);
		}
		break;	
	case "report":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/report/";
		$hostUrl=$config['filesfolder']."/".$config['clientsfolder']."/".$_POST['client']."/report/";
		if (!file_exists($options['upload_dir'])) {
			mkdir($options['upload_dir'], 0777, true);
		}
	
		break;		
	case "activity":
		$options['upload_dir'] =
		__DIR__ ."/../".$config['filesfolder']."/".$config['auditorsfolder']."/".$_POST['auditor']."/activity/";
		$hostUrl=$config['filesfolder']."/".$config['auditorsfolder']."/".$_POST['auditor']."/activity/";
		if (!file_exists($options['upload_dir'])) {
			mkdir($options['upload_dir'], 0777, true);
		}
	
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
	case "tickets":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/tickets/";
		break;
	case "report":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['clientsfolder']."/".$_POST['client']."/report/";
		break;

	case "activity":
		$fileDetails["uploadDir"]=DRIVE_FILE_DIR."/".$config['auditorsfolder']."/".$_POST['auditor']."/activity/";
		break;
				default:
		{
			$fileDetails["uploadDir"]=DRIVE_FILE_DIR;
		}
	};
	try {
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$body['body'] = "";
	// REmove when add Google Drive upload !!!!!!!!!!!!!!!!!
	foreach($fileDetails["files"] as $file){
		$file->{"folderType"} = $_POST['folderType'];
		// path to write/delete file
		$file->{"url"} = rawurldecode(str_replace("\\","/",$file->{"url"}));
		// path to open the file
		$file->{"hostUrl"} = $hostUrl.rawurldecode($file->{"name"});
		$body['body'] = $body['body']."<br/>". $file->{"name"};

		if ($_POST['infoType'] == "report") {

 			
				// Prepare the SQL query to fetch the existing Documents field from the database
				$query = "SELECT Documents
						FROM tauditreport
						WHERE idclient = :idclient AND idapp = :idapp AND id = :id";
	
				// Prepare the statement
				$stmt = $dbo->prepare($query);
	
				// Bind parameters
				$stmt->bindParam(':idclient', $_POST["idclient"]);
				$stmt->bindParam(':idapp', $_POST["idapp"]);
				$stmt->bindParam(':id', $_POST["id"]);
	
				// Execute the query
				$stmt->execute();
	
				// Fetch the result (assuming only one row is expected)
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
				// Check if a result was found
				if ($result) {
					// Decode the existing JSON string into an associative array
					$existingDocuments = json_decode($result['Documents'], true);
					
					// Initialize an empty array if Documents field is null or empty
					if (!$existingDocuments) {
						$existingDocuments = [];
					}
					
					// Example of adding a new document (adjust as per your actual data)
					$newDocument = [
						"name" => $file->{"name"},
						"hostpath" => $file->{"hostUrl"},
						"hostUrl" => $file->{"url"},
						"deleted" => "0"
					];
					
					// Add the new document to the existing array
					$existingDocuments[] = $newDocument;
					
					// Convert the updated array back to JSON
					$updatedDocuments = json_encode($existingDocuments);
					
					// Update the database with the updated Documents field
					$updateQuery = "UPDATE tauditreport
									SET Documents = :documents
									WHERE idclient = :idclient AND idapp = :idapp AND id = :id";
					
					// Prepare the update statement
					$updateStmt = $dbo->prepare($updateQuery);
					
					// Bind parameters
					$updateStmt->bindParam(':documents', $updatedDocuments);
					$updateStmt->bindParam(':idclient', $_POST["idclient"]);
					$updateStmt->bindParam(':idapp', $_POST["idapp"]);
					$updateStmt->bindParam(':id', $_POST["id"]);
					
					// Execute the update statement
					$updateStmt->execute();

					insertActivityLog($_POST['idclient'], $_POST['idapp'], $myuser->userdata['id'], $myuser->userdata['name'], 'Client uploaded a document to Corrective Actions');
				}
	
			 			
		}
	}
}
catch (PDOException $e) {
	echo 'Database error: '.$e->getMessage();
}		
  // send notification
	//if($_POST['infoType'] == 'product' || $_POST['infoType'] == 'ingredient' ||
  //    $_POST['infoType'] == 'QM' || $_POST['infoType'] == 'application')
	//	sendEmail($body);

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

function uploadToGoogleDrive($fileDetails) {
    // Google drive file upload client
    //$client = gfGetClient();
    //$service = new Google_Service_Drive($client);
    foreach($fileDetails["files"] as $file) { 
        //$fileInfo = gfUploadFile($client, $service, substr($fileDetails["files"][0]->{"url"}, 0, strrpos($fileDetails["files"][0]->{"url"}, '/',0)), $file->name, $file->type, $fileDetails["uploadDir"]);
		$file->{"folderType"} = $_POST['folderType'];
        //$file->{"googleDriveUrl"} = $fileInfo ["alternateLink"];
        //$file->{"googleDriveId"} = $fileInfo ["id"];
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
$db = acsessDb :: singleton();
$dbo =  $db->connect();
switch ($_SERVER['REQUEST_METHOD']) {

    case "POST":
		// sendResponse(getLocalFileDetails());
		sendResponse( uploadToGoogleDrive( getLocalFileDetails() ) );
       break;
    case "GET":
		//sendResponse(deleteFromGoogleDrive($_GET["deleteId"]));
		//unlink(rawurldecode($_REQUEST["deleteName"]));
}
?>
