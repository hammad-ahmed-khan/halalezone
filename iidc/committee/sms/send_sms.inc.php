<?php if (!defined("_HQC_")) {
    exit();
};

require_once(__DIR__ . '/vendor/autoload.php');

use Spryng\SpryngRestApi\Objects\Message;
use Spryng\SpryngRestApi\Spryng;

$sms['API_key'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiODBlNzcwZDMwZDBhOWNiYjE3ZGU1NjEzYzlmMWViNWM2Y2RhYWM1MzQwNzhhNWRjYzAzMGY1YWJhNjljNTc0MWE1Yzc2MThjYmIxOTExZDciLCJpYXQiOiIxNjc4ODk0NjM3LjcwOTI4MSIsIm5iZiI6IjE2Nzg4OTQ2MzcuNzA5Mjg0IiwiZXhwIjoiNDgzNDU2ODIzNy43MDQyNzMiLCJzdWIiOiIxMDQ0MTEiLCJzY29wZXMiOltdfQ.HCDSrkrNwRFPjS7--2rekuk4SIAzTLDxEWZVVmaZ0RJ51CNr-h1l3kzmVcv7mjQ-x1b7PxYGOKi79ac0I8D0m4ublGAf3pEgfpVhEy9NdttKlTkN8LSLsQoievk3tdobr8Ha0_LDt1XevFQuai7yB0vv1hgYuqPKd4a4rtWoNqXoDf62-HSj5tVot322KAW7DqcFAl8mR6NbKJ4KU5y0nScFE9D0btKR62I1zIMe8AoqhKfgxX95_kSrnMGb3rYD-v-67IlAMetcFl12-ottYI1iZaVH0pkAvCBrPTCcqFetPOQZCbJal6XLbXRVCeP0G6jdT-_oy7O2ZOdduMlycsYzNq87d7RlITDKuMehuCvH1n9ZS_oToEIJEK8JNuUtbcYLhBtAJekep0DJRl-GJMsJT24CEU2S1nhE3fwQwHne-sZXBjWlCr6QhjjP-0xCoLf-yKcvifJ0Pnyi3UqcbvHelSA4gWfd0yADyo3H6EXzj0e_FdfjPQFnQ3K6UfVKuUSDyflEyZXzbt93LDC6KuRxv-Qk_krv1QQGG5Ie3BpuDYjNfCkvm9dtY2HHJyyOBWEZUqb8hyz16s9Z3QCkHxQDBx-teumslvY2ExtdwzTRrGqt5R4GLLN-qdg-U3hsbsac58ogN7UL1RuuqI52mPj5fL4cAUYRtWdJROykF50';
$sms['sender_name'] = 'HQC';

function sendSMS($sendTo, $messageBody)
{
    global $sms;
    $spryng = new Spryng($sms['API_key']);
    $message = new Message();
    $message->setBody($messageBody);
    $message->setRecipients([$sendTo]);
    $message->setOriginator($sms['sender_name']);
    $response = $spryng->message->send($message);
    if ($response->wasSuccessful()) {
        $message = $response->toObject();
        return true;
    } else if ($response->serverError()) {
        return false;
    } else {
        return "Message could not be send. Response code: " . $response->getResponseCode() . "\n";
    }
}
