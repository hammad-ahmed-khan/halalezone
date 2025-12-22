<?php
//getting accessToken
function getZoomAccessToken()
{
    $accountId = 'keKvv1KUSfG0soSA0jbntQ'; // Replace with your Account ID
    $clientId = '3_I41AWSQtm_Zv3hxrqBBg'; // Replace with your Client ID
    $clientSecret = 'oyyfW8lQdkpI0ovq1l3FY1Yz8DLwf6bi'; // Replace with your Client Secret
    $data = array(
        "grant_type" => "account_credentials",
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "account_id" => $accountId
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://zoom.us/oauth/token");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));

    $response = curl_exec($curl);
    curl_close($curl);

    $decodedResponse = json_decode($response, true);
    if (isset($decodedResponse["access_token"])) {
        return $decodedResponse["access_token"];
    } else {
        return "Error";
    }
}
// end of getting accessToken

//getting meeting link
function make_meeting_link($meetingData)
{
    $accessToken = getZoomAccessToken();
    if ($accessToken == "Error") {
        echo "Error getting access token";
        return;
    }

    $data = array(
        'topic' => $meetingData['topic'],
        'type' => 2, // 2 for scheduled meeting
        'start_time' => $meetingData['date'] . 'T' . $meetingData['time'] . ':00', // Replace with your desired start time in UTC format
        'duration' => 30, // Meeting duration in minutes
        'timezone' => 'Europe/Amsterdam', // Replace with your desired timezone
        'settings' => array(
            'join_before_host' => true,
            'mute_upon_entry' => false,
            'approval_type' => 2, // Automatically approve participants
            'registration_type' => 1, // Attendees register once and can attend multiple times

        ),
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.zoom.us/v2/users/me/meetings");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $accessToken,
        "Content-Type: application/json"
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $decodedResponse = json_decode($response, true);

    if (isset($decodedResponse["join_url"])) {
        return $decodedResponse["join_url"];
    } else {
        return "Error";
    }
}
//end of getting meeting link

//getting all meeting links
function get_meeting_links()
{
    $accessToken = getZoomAccessToken();
    if ($accessToken == "Error") {
        echo "Error getting access token";
        return;
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.zoom.us/v2/users/me/meetings");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $accessToken
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $decodedResponse = json_decode($response, true);
    print_r($decodedResponse);
}
//end of getting all meeting links


// $meetingData = array(
//     'topic' => 'Test Meeting',
//     'date' => '2024-12-19',
//     'time' => '12:00'
// );
//   $join_link = make_meeting_link($meetingData);
//   echo $join_link;
// $meetings = get_meeting_links();
// print_r($meetings);
