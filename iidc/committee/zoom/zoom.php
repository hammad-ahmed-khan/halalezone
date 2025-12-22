<?php
require '../../tools/firebase/vendor/autoload.php'; // Adjust the path based on your project structure

// Generate JWT token for Zoom authorization
function generateZoomJWT() {
    $api_key = "fcgT1tdETkuIL39hn3DWFg";
    $api_secret = "aNVO2fePnBpcCtel9QtM72WpiT20VMes";
    $token = array(
        "iss" => $api_key,
        "exp" => time() + 60 // Set expiry time to 60 seconds
    );
    return JWT::encode($token, $api_secret);
}
$jwtToken = generateZoomJWT();

$data = array(
    "topic" => "Your Meeting Topic",
    "type" => 2,
    "start_time" => "2020-09-15T13:00:00"
);

$payload = json_encode($data);

$ch = curl_init("https://api.zoom.us/v2/users/me/meetings");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwtToken
));
$response = curl_exec($ch);
curl_close($ch);
echo $response;
$resultData = json_decode($response);
echo $resultData->join_url;
?>