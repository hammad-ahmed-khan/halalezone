<?php
define("__HQC__", true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($prog_path))
    include "../config/paths.inc.php";
date_default_timezone_set('Europe/Amsterdam');

function get_diff($start, $end = false)
{
    $start = new DateTime($start);
    if (!isset($end))
        $end = date("Y-m-d", time());
    $end = new DateTime($end);
    $interval = $end->diff($start);
    return $interval->days;
}

$clients = array();
if ($invoices = $amdb->get_results("SELECT invoices.nr,invoices.clid,invoices.reminded_on,invoices.inserted_on AS invoice_date,invoices.invoice_nr,hqc_default_invoice_reminders.* FROM invoices
JOIN hqc_default_invoice_reminders ON hqc_default_invoice_reminders.clid = invoices.clid
WHERE hqc_default_invoice_reminders.status='on' AND invoices.clid != '0' AND invoices.invoice_type != 'credit_note' AND invoices.status != 'credited' AND invoices.paid_on = '' AND invoices.invoice_nr!='draft' AND invoices.invoice_nr != 'scheduled' AND invoices.invoice_nr != '' AND DATE(invoices.inserted_on)>='2020-07-01' ORDER BY invoices.clid,invoices.inserted_on DESC")) {
    foreach ($invoices as $invoice) {
        $invFile = "/client_data/invoices/$invoice[invoice_nr].pdf";

        if (file_exists($prog_path . $invFile)) {
            $invoice['invoice_url'] = $prog_path . $invFile;
            if (trim($invoice['reminded_on']) == '') {
                $daysAgo = get_diff($invoice['invoice_date']);
                if ($daysAgo > $invoice['first_reminder'])
                    $invoice['reminder'] = 'first_reminder';
            } else {
                $reminders = explode(',', $invoice['reminded_on']);
                if (count($reminders) == 1) {

                    $daysAgo = get_diff(fix_date($reminders[0]));
                    $invoice['daysAgo-first'] = $daysAgo;
                    if ($daysAgo > $invoice['second_reminder'])
                        $invoice['reminder'] = 'second_reminder';
                    else
                        $invoice['daysAgo'] = $daysAgo;
                } else  if (count($reminders) > 1) {
                    $daysAgo = get_diff(fix_date($reminders[1]));
                    if ($daysAgo > $invoice['telephone_call'])
                        $invoice['reminder'] = 'telephone_call';
                }
            }
            if (!isset($clients[$invoice['clid']]))
                $clients[$invoice['clid']] = array();
            $clients[$invoice['clid']][] = $invoice;
        }
    }
}

$data = array();
$messages = array();

if ($invoice_templates = $amdb->get_results("SELECT * FROM invoice_templates WHERE template_name='reminder' OR template_name='final_reminder'")) {
    foreach ($invoice_templates as $template) {
        if ($template['template_name'] == 'reminder')
            $messages['first_reminder'] = $template;
        else
            $messages['second_reminder'] = $template;
    }
}

foreach ($clients as $clientKey => $client) {
    if (!isset($data[$clientKey])) {
        $cla = invoice_address($clientKey);
        $data[$clientKey]['client_address'] = $cla['address'];
        $data[$clientKey]['company_name'] = $cla['company_name'];
        $data[$clientKey]['client_name'] = $cla['client_name'];
        $data[$clientKey]['client_email'] = $cla['client_email'];
    };

    foreach ($client as $invoice) {
        if (isset($invoice['reminder']) && ($invoice['reminder'] == 'first_reminder' || $invoice['reminder'] == 'second_reminder')) {
            $data[$clientKey][$invoice['reminder']]['invoice_nr'][$invoice['nr']] = $invoice['invoice_nr'];
            $data[$clientKey][$invoice['reminder']]['invoice_url'][$invoice['nr']] = $invoice['invoice_url'];
            $data[$clientKey][$invoice['reminder']]['message'] = $invoice['reminder'];
            $data[$clientKey][$invoice['reminder']]['reminded_on'][$invoice['nr']] = $invoice['reminded_on'];
        }
    }
}

foreach ($clients as $clientKey => $client) {
    $email = array();
    $email_data = $data[$clientKey];

    if (isset($email_data['first_reminder'])) {
        email_message($data[$clientKey], $email_data['first_reminder']);
    }
    if (isset($email_data['second_reminder'])) {
        email_message($data[$clientKey], $email_data['second_reminder']);
    }
};
/*
?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>First Reminder</th>
            <th>Second Reminder</th>
            <th>Telephone Call</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $invNr = 0;
        foreach ($clients as $clientKey => $client) {
            $email_data = $data[$clientKey];
            print_r($email_data);
            $invNr++;
            $invoice_urls_first = array();
            if (isset($email_data['first_reminder'])) {
                $invoice_urls_first = $email_data['first_reminder']['invoice_url'];
                $reminded_on_first = $email_data['first_reminder']['reminded_on'];
            }
            $invoice_urls_second = array();
            if (isset($data['second_reminder'])) {
                $invoice_urls_second = $email_data['second_reminder']['invoice_url'];
                $reminded_on_second = $email_data['second_reminder']['reminded_on'];
            } ?>
            <tr>
                <td><?php echo $invNr++; ?></td>
                <td><?php echo $email_data['company_name']; ?></td>
                <td><?php print_r($invoice_urls_first); ?></td>
                <td><?php print_r($invoice_urls_second); ?></td>
                <td><?php ?></td>
            </tr>
        <?php }; ?>
    </tbody>
</table>
<?php
*/
function email_message($data, $email_data)
{
    global $amdb, $messages, $prog_path;

    $email = array();
    $reminded_on = array();
    $email['invoice_nr'] = implode(', ', $email_data['invoice_nr']);
    $email = $messages[$email_data['message']];
    foreach ($data as $key => $value) {
        if (!is_array($value)) {
            $email['email_subject'] = str_replace('[' . $key . ']', $value, $email['email_subject']);
            $email['email_body'] = str_replace('[' . $key . ']', $value, $email['email_body']);
        }
    }

    //$data['client_email'] = 'test@local.com';
    $email['to_email'] = $data['client_email'];
    $email['to_name'] = $data['client_name'];
    $email['from_email'] = $email['email_reply_address'];
    $email['from_name'] = $email['email_sender_name'];
    $email['emailMeACopy'] = false;
    $email['attachments'] = $email_data['invoice_url'];

    foreach ($email_data['reminded_on'] as $key => $value) {
        if (trim($value) == '')
            $reminded_on[$key] = date('d/m/Y', time());
        else
            $reminded_on[$key] = $value . ',' . date('d/m/Y', time());
    }
    include $prog_path . "/tools/mail/hqc_mail.inc.php";
    if (hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['email_subject'], $email['email_body'], $email['attachments'], $email['emailMeACopy'], $seen_id = array('type' => 'invoice', 'nr' => time()))) {
        foreach ($reminded_on as $key => $value) {
              $amdb->update("invoices",array('reminded_on' => $value),"nr = $key");
        }
    } else {
        $amdb->post_results('Error: Could not send the Reminder. Please contact SYS Admin');
    }
}
