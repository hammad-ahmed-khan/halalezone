<?php
require __DIR__ . '/vendor/autoload.php';

define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('CREDENTIALS_PATH', __DIR__ .'/credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function gfGetClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = file_get_contents($credentialsPath);
  } else { 
      echo $credentialsPath;
      die("Error: User is not authenticated.");
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

/**
 * Returns the directory ID where the files need to be stored on Google drive.
 * Directory will be created if it is not already exists.
 * @return Remote directory ID
 */
function getParentDirectoryId($service, $destinationFolder){
    $directoryInfo = explode("/", rtrim($destinationFolder, "/"));
    $firstChildDir = array_shift($directoryInfo);
    $parentDirIdOfNextChild = getDirectoryId($service, $firstChildDir);
    foreach($directoryInfo as $directory){
        $parentDirIdOfNextChild = getDirectoryIdInParent($service, $directory, $parentDirIdOfNextChild);
    }
    return $parentDirIdOfNextChild;
}


function getDirectoryId($service, $directoryName) {                  
        $files = $service->files->listFiles(array('q' => "title='".$directoryName."'", 'maxResults'=>1));
        if (count($files["items"])>0){
            return $files["items"][0]['id'];
        } else {
            return createDirectory($service, $directoryName);          
        }
}

function getDirectoryIdInParent($service, $directoryName, $parentDirId) {    
    $files = $service->files->listFiles(array('q' => "title='".$directoryName."' and '".$parentDirId."' in parents"));    
    if (count($files["items"])> 0){
        return $files["items"][0]['id'];
    } else {
        return createDirectory($service, $directoryName, $parentDirId);          
    }
}

function createDirectory($service, $directoryName, $parentDirectoryId = null){
    $folder = new Google_Service_Drive_DriveFile();
    if($parentDirectoryId != null) {
        $parent = new Google_Service_Drive_ParentReference();
        $parent->setId($parentDirectoryId);
        $folder->setParents(array($parent));
    }
    $folder->setTitle($directoryName);
    $folder->setMimeType('application/vnd.google-apps.folder');
    try {
        $newfolder = $service->files->insert($folder, array(
        'mimeType' => 'application/vnd.google-apps.folder',
        ));
        return $newfolder->id;
    } catch (Exception $e) {
        Throw new Exception("Error: ". $e->getMessage());
    }  
}


/**
 * Uploads files to the Google drive.
 * @param $service the Google drive service.
 * @param string $filePath the directory location where to look for the files.
 * @param string $fileName the name of the file to be uploaded.
 * @param string $mimeType the mimeType of the file.
 * @param string $destinationFolder the directory on the Google drive.
 * @return string the expanded path.
 */

 /*
  function gfUploadFile($client, $service, $filePath, $fileName, $mimeType, $destinationFolder) {
  $file = new Google_Service_Drive_DriveFile();
  $file->setTitle($fileName);
  $file->setDescription($fileName);
  $file->setMimeType($fileName);
  if ($destinationFolder != "root") {
    $parent = new Google_Service_Drive_ParentReference();
    $parent->setId(getParentDirectoryId($service, $destinationFolder));
    $file->setParents(array($parent)); 
  }
  $client->setDefer(true);
  $chunkSizeBytes = 1 * 1024 * 1024;
  $request = $service->files->insert($file);
  // Create a media file upload to represent our upload process.
  $media = new Google_Http_MediaFileUpload($client, $request, $mimeType, null, true, $chunkSizeBytes);
  $media->setFileSize(filesize($filePath ."/". $fileName));
  // Upload the various chunks. $status will be false until the process is
  // complete.
  $status = false;
  $handle = fopen($filePath ."/". $fileName, "rb");
  while (!$status && !feof($handle)) {
  $chunk = fread($handle, $chunkSizeBytes);
  $status = $media->nextChunk($chunk);
  }
  
  // The final value of $status will be the data from the API for the object
  // that has been uploaded.
  $result = false;
  if($status != false) {
    $result = $status;
  }
  fclose($handle);
  // Reset to the client to execute requests immediately in the future.
  $client->setDefer(false);
  return $result;
 } 
*/

function gfUploadFile($client, $service, $filePath, $fileName, $mimeType, $destinationFolder) {
  $file = new Google_Service_Drive_DriveFile();
  $file->setTitle($fileName);
  $file->setDescription($fileName);
  $file->setMimeType($mimeType);

  // Check if the destination folder exists
  $folders = explode("/", $destinationFolder);
  $parentFolderId = "root"; // Root folder
 
  // Loop through each directory in the path
  foreach ($folders as $folderName) {
      // Check if the folder already exists
      $folderName = str_replace('{slash}', '/', $folderName);
      $folderExists = false;
      $folderList = $service->files->listFiles(array('q' => "title = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '$parentFolderId' in parents"));
      foreach ($folderList->getItems() as $folder) {
          $folderExists = true;
          $parentFolderId = $folder->getId(); // Update the parent folder ID for the next iteration
          break;
      }
      
      // If the folder doesn't exist, create it
      if (!$folderExists) {
          $newFolder = new Google_Service_Drive_DriveFile();
          $newFolder->setTitle($folderName);
          $newFolder->setMimeType('application/vnd.google-apps.folder');
          $newFolder->setParents(array(array('id' => $parentFolderId)));
          $folder = $service->files->insert($newFolder);
          $parentFolderId = $folder->getId(); // Update the parent folder ID for the next iteration
      }
  }
  
  if ($parentFolderId != "root") {
      $parent = new Google_Service_Drive_ParentReference();
      $parent->setId($parentFolderId);
      $file->setParents(array($parent)); 
  }
  
  
  $client->setDefer(true);
  $chunkSizeBytes = 1 * 1024 * 1024;
  $request = $service->files->insert($file);
  
  // Create a media file upload to represent our upload process.
  $media = new Google_Http_MediaFileUpload($client, $request, mime_content_type($filePath ."/". $fileName), null, true, $chunkSizeBytes);
  $media->setFileSize(filesize($filePath ."/". $fileName));
  
  // Upload the various chunks. $status will be false until the process is complete.
  $status = false;
  $handle = fopen($filePath ."/". $fileName, "rb");
  while (!$status && !feof($handle)) {
      $chunk = fread($handle, $chunkSizeBytes);
      $status = $media->nextChunk($chunk);
  }
  
  // The final value of $status will be the data from the API for the object that has been uploaded.
  $result = false;
  if($status != false) {
      $result = $status;
  }
  fclose($handle);
  // Reset to the client to execute requests immediately in the future.
  $client->setDefer(false);
  return $result;
}



/**
 * Deletes the files from Google drive
 * @param $service the Google drive service
 * @param $id ID of the file
 * @return response
 */
function gfDeleteFile ($service, $id) {
    try{
    $response = $service->files->delete($id);
    if ($response == NULL){
        $response = array();
        $response["response"]="success";
    } else {
        $response["response"]="error";
    }
    return $response;
    
    } catch (Exception $e) {
        throw new Exception( $e->getMessage());
    }
}
