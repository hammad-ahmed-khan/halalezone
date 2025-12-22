<?php
if (!isset($_POST['act'])) {
    exit();
}

$meetingData = array(
    'topic' => 'DCM Meeting',
    'date' => date('Y-m-d'),
    'time' => date('H:i', strtotime('+2 hours')),/*now time + 2 hours*/

);

// include "../zoom.inc.php";
// $joinLink =  make_meeting_link($meetingData);

// if ($joinLink != 'Error')
//     $_POST['event_details']['zoom-link'] = $joinLink;
// else
//     $_POST['event_details']['zoom-link'] = '';

$data['companies_list'] = $_POST['company_name'];
$data['proposed_date'] = date("d/m/Y", strtotime($meetingData['date']));
$data['proposed_time'] = $meetingData['time'];
$data['proposed_location'] = "online video meeting";

if ($email = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='decision_committee'")) {
    $email['email_body'] = str_replace('<br /><br /><br />', '<br /><br />', $email['email_body']);

    foreach ($data as $key => $value) {
        $email['email_body'] = str_replace('[' . $key . ']', $value, $email['email_body']);
    }

    if ($commebers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE comemid IN ($decision[comemids]) order by member_name ASC")) {
        foreach ($commebers as $comemid => $comem) {
            //$_POST['bcc_email'][] = $comem['member_email'];
        }
    }

    include $prog_path . "/tools/mail/hqc_mail.inc.php";
    $email['to_email'] = 'dmc@halalqualitycontrol.com';
  //  $email['to_email'] = 'ayoub@halaloffice.com';
    $email['to_name'] = 'Decision Committee Members';
    $email['subject'] = $email['email_subject'];
    $email['message'] = $email['email_body'];
    $email['from_email'] = 'info@halaloffice.com';
    $email['from_name'] = 'HQ - The Netherlands';
    if (!hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'])) {
        echo "error:Email sending failed!";
        exit();
    } else {
        $amdb->update('hqc_committee_decision', array('email_message' => serialize($email)), "decid = $decid");
    }
}
