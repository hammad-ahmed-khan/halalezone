<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';

header('Content-Type: application/json');

// Check if user is logged in
$myuser = cuser::singleton();
$myuser->getUserData();

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid training request ID');
    }

    $query = "SELECT * FROM training_requests WHERE id = :id AND deleted = 0";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Training request not found');
    }

  // Build HTML content
$html = '
<style>
    .section-title {
        background-color: #5a8a3c;
        color: #ffffff;
        font-weight: bold;
        padding: 8px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .row-label {
        font-weight: bold;
        color: #333;
        width: 40%;
    }
    .row-value {
        color: #000;
        width: 60%;
        text-align: left;
    }
    .note-box {
        background-color: #e8f4f8;
        border: 1px solid #5a8a3c;
        padding: 10px;
        margin: 10px 0;
    }
</style>

<h2 style="text-align:center; color:#5a8a3c;">Halal Training Request</h2>
<p style="text-align:center; font-size:12px;">Request ID: <strong>'.$data['id'].'</strong></p>
<p style="text-align:center; font-size:11px;">Submitted on: '.date('d/m/Y H:i', strtotime($data['created_at'])).'</p>

<div class="section-title">COMPANY DETAILS</div>
<div class="info-row"><div class="row-label">Company Name:</div><div class="row-value">'.$data['company_name'].'</div></div>
<div class="info-row"><div class="row-label">Address:</div><div class="row-value">'.nl2br($data['address']).'</div></div>
<div class="info-row"><div class="row-label">Contact Person:</div><div class="row-value">'.$data['contact_person'].'</div></div>
<div class="info-row"><div class="row-label">Phone Number:</div><div class="row-value">'.$data['phone_number'].'</div></div>
<div class="info-row"><div class="row-label">Email Address:</div><div class="row-value">'.$data['email_address'].'</div></div>

<div class="section-title">TRAINING CONFIGURATION</div>
<div class="info-row"><div class="row-label">Languages:</div><div class="row-value">'.$data['language'].'</div></div>';

if (!empty($data['other_language'])) {
    $html .= '
    <div class="info-row"><div class="row-label">Other Language:</div><div class="row-value">'.$data['other_language'].' <em>(€399 additional fee)</em></div></div>';
}

$html .= '
<div class="info-row"><div class="row-label">Preferred Training Dates:</div><div class="row-value">'.$datesString.'</div></div>
<div class="info-row"><div class="row-label">Number of Participants:</div><div class="row-value"><strong>'.$data['num_participants'].'</strong></div></div>
<div class="info-row"><div class="row-label">Training Type:</div><div class="row-value"><strong>'.$data['training_type'].'</strong></div></div>
<div class="info-row"><div class="row-label">Training Cost:</div><div class="row-value"><strong style="color:#5a8a3c;">€'.number_format($data['training_cost'], 2).'</strong> + VAT if applicable</div></div>

<div class="note-box">
    <span style="font-size:10px; margin:0;"><strong>Important:</strong></span>
    <ul style="font-size:9px; margin:5px;">
        <li>For in-house training, the cost remains the same, with additional charges for travel and accommodation.</li>
        <li>All offers are non-binding unless specifically agreed otherwise in writing.</li>
        <li>Once we receive your completed request, our team will contact you with further details and scheduling options.</li>
        <li>From the 11th participant onward, the fee is €99 per person.</li>
    </ul>
</div>

<div class="section-title">ACCEPTANCE OF TERMS</div>
<div class="info-row"><div class="row-label">Company:</div><div class="row-value">'.$data['acceptance_company'].'</div></div>
<div class="info-row"><div class="row-label">Name and Position:</div><div class="row-value">'.$data['acceptance_name_position'].'</div></div>
<div class="info-row"><div class="row-label">Place, Date:</div><div class="row-value">'.$data['acceptance_place_date'].'</div></div>

<div class="section-title">SIGNATURE AND STAMP</div>
<div class="info-row" style="align-items: flex-start;">
    <div class="row-label">Signature and Stamp:</div>
    <div class="row-value">';

if (!empty($data['signature_data'])) {
    $html .= '<img src="'.$data['signature_data'].'" style="max-width:200px; max-height:100px; border:1px solid #ddd;" />';
} else {
    $html .= '<em>No signature available</em>';
}

$html .= '
    </div>
</div>

<p style="font-size:9px; color:#666; text-align:center; margin-top:20px;">
    This document was generated automatically by the Halal Digital system on '.date('d/m/Y H:i:s').'
</p>';


    echo json_encode([
        'success' => true,
        'data' => $html
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>