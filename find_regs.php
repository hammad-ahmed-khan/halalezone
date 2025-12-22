<?php
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'pages/patterns.php';
include_once 'includes/func.php';
require_once 'notifications/notifyfuncs.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$decode = file_get_contents(__DIR__ . "/config.json");
$config = json_decode($decode, TRUE);

$db = acsessDb::singleton();
$dbo = $db->connect();

// Fetch all applications
$sqlApps = "SELECT * FROM tapplications WHERE 1";
$stmtApps = $dbo->prepare($sqlApps);
$stmtApps->execute();
$applications = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

foreach ($applications as $app) {
    $id = $app["id"];
    $idclient = $app["idclient"];

    // Fetch the client's name from the database
    $sqlClient = "SELECT * FROM tusers WHERE id = :idclient";
    $stmtClient = $dbo->prepare($sqlClient);
    $stmtClient->bindParam(':idclient', $idclient, PDO::PARAM_INT);
    $stmtClient->execute();
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $clientName = $client["name"] . ' - '.  $client["id"];

        // Define the base directory to search
        $baseDir = __DIR__ . '/files/clients';

        // Open the base directory
        if (is_dir($baseDir)) {
            $dirs = scandir($baseDir);

            foreach ($dirs as $dir) {
                // Skip "." and ".." and non-directories
                if ($dir === '.' || $dir === '..' || !is_dir($baseDir . '/' . $dir)) {
                    continue;
                }

                // Check if the folder name contains the client's name
                //if (strpos($dir, $idclient) !== false) {
                    // Define the file path
                    $filePath = $baseDir . '/' . $dir . '/application/F0-01 new customer registration_' . $id . '.pdf';

                    // Check if the file exists
                    if (file_exists($filePath)) {
                        $destinationFile = __DIR__ . '/files/Regs/' . $clientName . '.pdf';

						if (!file_exists($destinationFile)) {

							// Copy the file to the files/Regs directory and rename it
							if (copy($filePath, $destinationFile)) {
								echo "File copied and renamed successfully for application ID: $id\n";
							} else {
								//echo "Failed to copy file for application ID: $filePath\n";
							}
						}
                        break; // Exit the loop once the file is found and copied
                    }
                //}
            }
        } else {
            echo "Base directory not found: $baseDir\n";
        }
    } else {
        echo "Client not found for application ID: $id\n";
    }
}
?>