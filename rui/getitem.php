<?php
require_once('config.php');

$api_url = 'https://api.ebay.com/buy/browse/v1/item';
$item_id = $_GET["item_id"];

$client_id = EBAY_CLIENT_ID;
$client_secret = EBAY_CLIENT_SECRET;

// Load the access token and refresh token from text files
$access_token = file_get_contents('access_token.txt');
$refresh_token = file_get_contents('refresh_token.txt');

// Build the request URL with the item ID
$request_url = $api_url . '/' . urlencode($item_id);

// Check if the access token is still valid
$ch = curl_init($request_url);

$headers = [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_status === 401) {
    // Token expired, try to refresh it
    $new_access_token = refreshAccessToken($refresh_token);
    
    if ($new_access_token) {
        // Token refreshed, update the access token
        $access_token = $new_access_token;
        
        // Retry the API call with the refreshed token
        $headers[0] = 'Authorization: Bearer ' . $access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
}

if ($http_status === 200) {
    
    // API call was successful, handle the response as needed
    $item_data = json_decode($response, true);

    echo '<pre>';
    print_r($item_data);
    echo '</pre>';

} else {
    
    echo '==';
    echo $response;
    echo '==';

    // Handle other HTTP status codes as needed
    echo 'API call failed with HTTP status ' . $http_status;
}

curl_close($ch);

// Function to refresh the access token
function refreshAccessToken($refresh_token) {
    global $client_id, $client_secret;

    $token_url = 'https://api.ebay.com/identity/v1/oauth2/token';

    $data = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
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

    if ($response) {
        $token_data = json_decode($response, true);
        if (isset($token_data['access_token'])) {
            // Update the access and refresh tokens
            file_put_contents('access_token.txt', $token_data['access_token']);
            return $token_data['access_token'];
        }
    }

    return false;
}
?>