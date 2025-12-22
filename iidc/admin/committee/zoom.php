<?php
$clientId = 'fcgT1tdETkuIL39hn3DWFg'; // Replace with your Client ID
$clientSecret = 'aNVO2fePnBpcCtel9QtM72WpiT20VMes'; // Replace with your Client Secret
$redirectUri = 'your_redirect_uri'; // Replace with your Redirect URI
//$authorizationCode = $_GET['code']; // The authorization code from Zoom

$tokenRequestData = [
    'grant_type' => 'authorization_code',
    'code' => $authorizationCode,
    'redirect_uri' => $redirectUri,
];

$tokenRequestHeaders = [
    'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
];

$ch = curl_init('https://zoom.us/oauth/token?grant_type=account_credentials&account_id=Z3yCj3McSWWx6IlCuLqk_A');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenRequestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $tokenRequestHeaders);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

if (isset($responseData['access_token'])) {
    echo 'Access Token: ' . $responseData['access_token'];
} else {
    echo 'Error getting access token: ' . $responseData['message'];
}