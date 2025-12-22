<?php
require __DIR__ . '/vendor/autoload.php';

define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('CREDENTIALS_PATH', 'credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE)
));
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (php_sapi_name() != 'cli') {
//  throw new Exception('This application must be run on the command line.');
}

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
    /*
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->authenticate($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, $accessToken);
    printf("Credentials saved to %s\n", $credentialsPath);
    */
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


function getParentDirectoryId($service, $folderName) {
        // List all user files (and folders) at Drive root
        $files = $service->files->listFiles();
 	if (array_key_exists($folderName, $files)) {
		return $files[$folderName];
	} else {
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


function uploadFile($service, $filePath, $fileName, $mimeType, $destinationFolder) {
  $file = new Google_Service_Drive_DriveFile();
  $file->setTitle($fileName);
  $file->setDescription($fileName);
  $file->setMimeType($fileName);
  $parent = new Google_Service_Drive_ParentReference();
  $parent->setId(getParentDirectoryId($service, $destinationFolder));
  $file->setParents(array($parent));
  try {
    $data = file_get_contents($filePath ."/". $fileName);
    $response = $service->files->insert($file, array(
                        'data' => $data,
                        'mimeType' => $mimeType,
                        'uploadType'=> 'multipart'
              		 ));
    return $response;
    } catch(Exception $e) {
	throw new Exception("Error: " . $e->getMessage() );
    } 
	
}
// Get the API client and construct the service object.

$client = getClient();
$service = new Google_Service_Drive($client);


// Print the names and IDs for up to 10 files.
 $optParams = array(
  'maxResults' => 10,
);
$results = $service->files->listFiles($optParams);

if (count($results->getItems()) == 0) {
  print "No files found.\n";
} else {
  print "Files:\n";
  foreach ($results->getItems() as $file) {
    printf("%s (%s)\n", $file->getTitle(), $file->getId());
  }
}

/*
// Print the names and IDs of folders.
$optParams = array(
  'q' => "mimeType='application/vnd.google-apps.folder'",
  'maxResults' => 10, // You can adjust the number of results displayed
);
echo expandHomeDirectory(CREDENTIALS_PATH);
//$results = $service->files->listFiles($optParams);
*/

if (count($results->getItems()) == 0) {
  print "No folders found.\n";
} else {
  print "Folders:\n";
  foreach ($results->getItems() as $folder) {
    printf("%s (%s)\n", $folder->getTitle(), $folder->getId());
  }
}