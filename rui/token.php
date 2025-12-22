<?php
require_once('config.php');

$token_url = 'https://api.ebay.com/identity/v1/oauth2/token';

$client_id = EBAY_CLIENT_ID;
$client_secret = EBAY_CLIENT_SECRET;
$redirect_uri = EBAY_REDIRECT_URI;

$code = $_GET['code']; // Authorization code received from eBay

$data = [
    'grant_type' => 'authorization_code',
    'redirect_uri' => $redirect_uri,
    'code' => $code,
];

$headers = [
    'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
    'Content-Type: application/x-www-form-urlencoded',
];

$ch = curl_init($token_url);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_status === 200) {
    if ($response) {
        $token_data = json_decode($response, true);
        $access_token = $token_data['access_token'];
        $refresh_token = $token_data['refresh_token'];

        // Save the tokens to text files
        file_put_contents('access_token.txt', $access_token);
        file_put_contents('refresh_token.txt', $refresh_token);

        echo "Congratulations! You have successfully authorized the eBay API, and your access token and refresh token have been saved in access_token.txt and refresh_token.txt respectively.";


    } else {
        echo 'Error: Empty response from eBay';
    }
} else {
    echo '<p>Error: HTTP status ' . $http_status . '</p>';
    if (!empty($response)) {
        $error_response = json_decode($response, true);
        if (isset($error_response['error_description'])) {
            echo '<p>Error Description: ' . $error_response['error_description'] . '</p>';
        }
    }    
}

curl_close($ch);
?>