<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once "../notifications/notifyfuncs.php";
include_once '../includes/func.php';

header('Content-Type: application/json');

// Check if user is logged in
$myuser = cuser::singleton();
$myuser->getUserData();
$user_id = $myuser->userdata['id'];

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    // Get form data
    $id = $_POST['id'] ?? '';
    $company_name = trim($_POST['company_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email_address = trim($_POST['email_address'] ?? '');
    $language = $_POST['language'] ?? '';
    $other_language = trim($_POST['other_language'] ?? '');
    $preferred_date_1 = $_POST['preferred_date_1'] ?? null;
    $preferred_date_2 = $_POST['preferred_date_2'] ?? null;
    $preferred_date_3 = $_POST['preferred_date_3'] ?? null;
    $num_participants = intval($_POST['num_participants'] ?? 0);
    $training_type = $_POST['training_type'] ?? '';
    $training_cost = floatval($_POST['training_cost'] ?? 0);
    $acceptance_company = trim($_POST['acceptance_company'] ?? '');
    $acceptance_name_position = trim($_POST['acceptance_name_position'] ?? '');
    $acceptance_place_date = trim($_POST['acceptance_place_date'] ?? '');
    $signature_data = $_POST['signature_data'] ?? '';

    // Validation
    $errors = [];

    if (empty($company_name)) {
        $errors['company_name'] = 'Company name is required';
    }

    if (empty($address)) {
        $errors['address'] = 'Address is required';
    }

    if (empty($contact_person)) {
        $errors['contact_person'] = 'Contact person is required';
    }

    if (empty($phone_number)) {
        $errors['phone_number'] = 'Phone number is required';
    }

    if (empty($email_address)) {
        $errors['email_address'] = 'Email address is required';
    } elseif (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        $errors['email_address'] = 'Invalid email address format';
    }

    if (empty($language)) {
        $errors['language'] = 'At least one language must be selected';
    }

    if (empty($preferred_date_1)) {
        $errors['preferred_date_1'] = 'At least one preferred date is required';
    }

    if ($num_participants < 1) {
        $errors['num_participants'] = 'Number of participants must be at least 1';
    }

    if (empty($training_type)) {
        $errors['training_type'] = 'Training type is required';
    } elseif (!in_array($training_type, ['Online', 'On-site'])) {
        $errors['training_type'] = 'Invalid training type';
    }

    if ($training_cost <= 0) {
        $errors['training_cost'] = 'Training cost must be greater than 0';
    }

    if (empty($acceptance_company)) {
        $errors['acceptance_company'] = 'Company name in acceptance section is required';
    }

    if (empty($acceptance_name_position)) {
        $errors['acceptance_name_position'] = 'Name and position are required';
    }

    if (empty($acceptance_place_date)) {
        $errors['acceptance_place_date'] = 'Place and date are required';
    }

    if (empty($signature_data)) {
        $errors['signature_data'] = 'Signature is required';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }

    // Convert empty dates to NULL
    $preferred_date_1 = !empty($preferred_date_1) ? $preferred_date_1 : null;
    $preferred_date_2 = !empty($preferred_date_2) ? $preferred_date_2 : null;
    $preferred_date_3 = !empty($preferred_date_3) ? $preferred_date_3 : null;

    // Prepare SQL statement
    if (empty($id)) {
        // Insert new record
        $query = "INSERT INTO training_requests 
                  (".($user_id != "" ? "user_id," : "")." company_name, address, contact_person, phone_number, email_address, 
                   language, other_language, preferred_date_1, preferred_date_2, preferred_date_3, 
                   num_participants, training_type, training_cost, acceptance_company, 
                   acceptance_name_position, acceptance_place_date, signature_data) 
                  VALUES 
                  (".($user_id != "" ? ":user_id," : "")." :company_name, :address, :contact_person, :phone_number, :email_address, 
                   :language, :other_language, :preferred_date_1, :preferred_date_2, :preferred_date_3, 
                   :num_participants, :training_type, :training_cost, :acceptance_company, 
                   :acceptance_name_position, :acceptance_place_date, :signature_data)";
        
        $stmt = $dbo->prepare($query);
        if ($user_id != "") {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(':company_name', $company_name, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':contact_person', $contact_person, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindParam(':email_address', $email_address, PDO::PARAM_STR);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->bindParam(':other_language', $other_language, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_1', $preferred_date_1, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_2', $preferred_date_2, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_3', $preferred_date_3, PDO::PARAM_STR);
        $stmt->bindParam(':num_participants', $num_participants, PDO::PARAM_INT);
        $stmt->bindParam(':training_type', $training_type, PDO::PARAM_STR);
        $stmt->bindParam(':training_cost', $training_cost, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_company', $acceptance_company, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_name_position', $acceptance_name_position, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_place_date', $acceptance_place_date, PDO::PARAM_STR);
        $stmt->bindParam(':signature_data', $signature_data, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $insertedId = $dbo->lastInsertId();
            
            // Generate PDF
            $pdfData = [
                'id' => $insertedId,
                'company_name' => $company_name,
                'address' => $address,
                'contact_person' => $contact_person,
                'phone_number' => $phone_number,
                'email_address' => $email_address,
                'language' => $language,
                'other_language' => $other_language,
                'preferred_date_1' => $preferred_date_1,
                'preferred_date_2' => $preferred_date_2,
                'preferred_date_3' => $preferred_date_3,
                'num_participants' => $num_participants,
                'training_type' => $training_type,
                'training_cost' => $training_cost,
                'acceptance_company' => $acceptance_company,
                'acceptance_name_position' => $acceptance_name_position,
                'acceptance_place_date' => $acceptance_place_date,
                'signature_data' => $signature_data,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Create directory for PDFs
            $pdfDir = __DIR__ . '/../files/training_requests/';
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            
            $pdfFilename = 'Training_Request_' . $insertedId . '.pdf';
            $pdfPath = $pdfDir . $pdfFilename;
            
            // Generate PDF
            include_once(__DIR__ . '/../includes/training_pdf.php');
            generateTrainingRequestPDF($pdfData, $pdfPath, false);
            
            // Send email notification with PDF attachment
            sendTrainingRequestEmail($insertedId, $company_name, $email_address, $contact_person, $num_participants, $training_type, $pdfPath, $pdfFilename);
            
            echo json_encode([
                'success' => true, 
                'id' => $insertedId,
                'message' => 'Training request submitted successfully'
            ]);
        } else {
            throw new Exception('Failed to insert training request');
        }
    } else {
        // Update existing record
        $query = "UPDATE training_requests 
                  SET company_name = :company_name, 
                      address = :address, 
                      contact_person = :contact_person, 
                      phone_number = :phone_number, 
                      email_address = :email_address, 
                      language = :language, 
                      other_language = :other_language, 
                      preferred_date_1 = :preferred_date_1, 
                      preferred_date_2 = :preferred_date_2, 
                      preferred_date_3 = :preferred_date_3, 
                      num_participants = :num_participants, 
                      training_type = :training_type, 
                      training_cost = :training_cost, 
                      acceptance_company = :acceptance_company, 
                      acceptance_name_position = :acceptance_name_position, 
                      acceptance_place_date = :acceptance_place_date, 
                      signature_data = :signature_data 
                  WHERE id = :id AND deleted = 0";
        
        $stmt = $dbo->prepare($query);
        $stmt->bindParam(':company_name', $company_name, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':contact_person', $contact_person, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindParam(':email_address', $email_address, PDO::PARAM_STR);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->bindParam(':other_language', $other_language, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_1', $preferred_date_1, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_2', $preferred_date_2, PDO::PARAM_STR);
        $stmt->bindParam(':preferred_date_3', $preferred_date_3, PDO::PARAM_STR);
        $stmt->bindParam(':num_participants', $num_participants, PDO::PARAM_INT);
        $stmt->bindParam(':training_type', $training_type, PDO::PARAM_STR);
        $stmt->bindParam(':training_cost', $training_cost, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_company', $acceptance_company, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_name_position', $acceptance_name_position, PDO::PARAM_STR);
        $stmt->bindParam(':acceptance_place_date', $acceptance_place_date, PDO::PARAM_STR);
        $stmt->bindParam(':signature_data', $signature_data, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'id' => $id,
                'message' => 'Training request updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update training request');
        }
    }

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

/**
 * Send email notification for training request with PDF attachment
 */
function sendTrainingRequestEmail($requestId, $companyName, $email, $contactPerson, $numParticipants, $trainingType, $pdfPath, $pdfFilename) {
    // This function should use your existing email system
    // Example implementation:
    global $supportEmailAddress;

    $fromEmailAddress = "noreply@halal-digital.net";
    
    // Send to admin
    $body = [];
    $body['name'] = 'Halal Digital';
    $body['email'] = $fromEmailAddress;
    //$body['to'] = 'office@iidc.at';
    $body['to'] = $supportEmailAddress;
    $body['subject'] = "New Halal Training Request - " . $companyName;
    $body['header'] = "";
    $body['message'] = '<p>Dear Team,</p>
        <p>A new Halal training request has been submitted:</p>
        <p><strong>Request ID:</strong> ' . $requestId . '<br/>
        <strong>Company:</strong> ' . $companyName . '<br/>
        <strong>Contact Person:</strong> ' . $contactPerson . '<br/>
        <strong>Email:</strong> ' . $email . '<br/>
        <strong>Number of Participants:</strong> ' . $numParticipants . '<br/>
        <strong>Training Type:</strong> ' . $trainingType . '</p>
        <p>Please find the complete training request attached as a PDF.</p>
        <p>Best regards,<br/>
        IIDC Halal Digital System</p>';
    
    $body['attachhostpath'] = $pdfPath;
    $body['attach'] = $pdfFilename;
    
    // Use your existing sendEmailWithAttach function
    sendEmailWithAttach($body);
     
    // Send confirmation email to client
    $clientBody = [];
    $clientBody['name'] = 'Halal Digital';
    $clientBody['email'] = $fromEmailAddress;
    $clientBody['to'] = $email;
    $clientBody['subject'] = "Training Request Confirmation - " . $companyName;
    $clientBody['header'] = "";
    $clientBody['message'] = '<p>Dear ' . $contactPerson . ',</p>
        <p>Thank you for submitting your Halal training request.</p>
        <p><strong>Request ID:</strong> ' . $requestId . '<br/>
        <strong>Company:</strong> ' . $companyName . '<br/>
        <strong>Number of Participants:</strong> ' . $numParticipants . '<br/>
        <strong>Training Type:</strong> ' . $trainingType . '</p>
        <p>Our team will contact you shortly with further details and scheduling options.</p>
        <p>Please find your training request details attached as a PDF for your records.</p>
        <p>If you have any questions, please contact us at office@iidc.at</p>
        <p>Best regards,<br/>
        IIDC Halal Digital Team</p>';
    
    $clientBody['attachhostpath'] = $pdfPath;
    $clientBody['attach'] = $pdfFilename;
    
    // Use your existing sendEmailWithAttach function
    sendEmailWithAttach($clientBody);
}
?>