<?php
require_once('config.php');

$client_id = EBAY_CLIENT_ID;
$redirect_uri = EBAY_REDIRECT_URI;
$scopes = EBAY_REQUESTED_SCOPES;

$auth_url = 'https://auth.ebay.com/oauth2/authorize' .
    '?client_id=' . $client_id .
    '&redirect_uri=' . urlencode($redirect_uri) .
    '&response_type=code' .
    '&scope=' . urlencode($scopes);

// Redirect the user to eBay for authorization
header('Location: ' . $auth_url);
?>