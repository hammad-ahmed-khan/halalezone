<?php
require '../../../tools/firebase/vendor/autoload.php'; // Adjust the path based on your project structure

$apiKey = 'fcgT1tdETkuIL39hn3DWFg';
$apiSecret = 'aNVO2fePnBpcCtel9QtM72WpiT20VMes';

$meetingData = [
    'topic' => 'Zoom Meeting',
    'type' => 2, // 2 for scheduled meeting
    'start_time' => '2023-12-01T12:00:00Z', // Replace with your desired start time
    'duration' => 60, // Duration in minutes
];

$zoomEndpoint = 'https://api.zoom.us/v2/users/me/meetings';

$client = new \GuzzleHttp\Client();

try {
    $response = $client->request('POST', $zoomEndpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . generateZoomJWT($apiKey, $apiSecret),
            'Content-Type' => 'application/json',
        ],
        'json' => $meetingData,
    ]);

    $responseData = json_decode($response->getBody(), true);

    if (isset($responseData['join_url'])) {
        echo 'Zoom Meeting Link: ' . $responseData['join_url'];
    } else {
        echo 'Error creating Zoom meeting: ' . $responseData['message'];
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
function generateZoomJWT($apiKey, $apiSecret)
{
    $tokenPayload = [
        'iss' => $apiKey,
        'exp' => strtotime('+1 hour'),
    ];

    return \Firebase\JWT\JWT::encode($tokenPayload, $apiSecret, 'HS256');
}

function generateZoomToken($apiKey, $apiSecret)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://zoom.us/oauth/token?grant_type=client_credentials",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Basic " . base64_encode($apiKey . ":" . $apiSecret),
            "Content-Type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        if(isset(json_decode($response)->access_token)){
            return json_decode($response)->access_token;
        }else{
            return $response;
        }
        return $response;
    }
}
