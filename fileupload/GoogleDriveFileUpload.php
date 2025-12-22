<?php
require __DIR__ . '/vendor/autoload.php';

define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('CREDENTIALS_PATH', 'credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
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
function getParentDirectoryId($service, $folderName) {
        // List all user files (and folders) at Drive root
        $files = $service->files->listFiles();
        $found = false;
        // Go through each one to see if there is already a folder with the specified name
        foreach ($files['items'] as $item) {
                if ($item['title'] == $folderName) {
                        $found = true;
                        return $item['id'];
                        break;
                }
        }
        if ($found ==false ){
                $folder = new Google_Service_Drive_DriveFile();
                $folder->setTitle($folderName);
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

function uploadFile($service, $filePath, $fileName, $mimeType, $destinationFolder) {
  $file = new Google_Service_Drive_DriveFile();
  $file->setTitle($fileName);
  $file->setDescription($fileName);
  $file->setMimeType($fileName);
  if ($destinationFolder != "root") {
    $parent = new Google_Service_Drive_ParentReference();
    $parent->setId(getParentDirectoryId($service, $destinationFolder));
    $file->setParents(array($parent)); 
  }
  try {
    $data = file_get_contents($filePath ."/". $fileName);
    $response = $service->files->insert($file, array(
                        'data' => $data,
                        'mimeType' => $mimeType,
                        'uploadType'=> 'multipart'
              		 ));
    return $response;
    } catch(Exception $e) {
	throw new Exception("Error: " . $e->getMessage());
    } 
	
}

