<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';
include_once "../notifications/notifyfuncs.php";

header('Content-Type: application/json');

// Check if user is logged in and is admin
$myuser = cuser::singleton();
$myuser->getUserData();

// Only admin users can assign auditors
if ($myuser->userdata['isclient'] != '0') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get_auditors':
            // Get list of auditors for dropdown
            $sql = "SELECT id, name, email 
                   FROM tusers 
                   WHERE isclient = 2 AND deleted = 0 
                   ORDER BY name ASC";
            
            $stmt = $dbo->prepare($sql);
            $stmt->execute();
            $auditors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'auditors' => $auditors
            ]);
            break;

        case 'assign_auditor':
            $trainingId = intval($_POST['training_id'] ?? 0);
            $auditorId = intval($_POST['auditor_id'] ?? 0);

            if ($trainingId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid training request ID.']);
                exit;
            }

            // If auditorId is 0, we're removing the assignment
            $auditorId = $auditorId > 0 ? $auditorId : null;

            // Get training request data
            $trainingSql = "SELECT tr.*, u.name as creator_name, u.email as creator_email 
                           FROM training_requests tr 
                           LEFT JOIN tusers u ON u.id = tr.user_id 
                           WHERE tr.id = :training_id AND tr.deleted = 0";
            $trainingStmt = $dbo->prepare($trainingSql);
            $trainingStmt->bindValue(':training_id', $trainingId, PDO::PARAM_INT);
            $trainingStmt->execute();
            $training = $trainingStmt->fetch(PDO::FETCH_ASSOC);

            if (!$training) {
                echo json_encode(['success' => false, 'message' => 'Training request not found.']);
                exit;
            }

            // If assigning an auditor, verify auditor exists and get auditor details
            $auditor = null;
            if ($auditorId) {
                $auditorCheckSql = "SELECT id, name, email FROM tusers WHERE id = :auditor_id AND isclient = 2 AND deleted = 0";
                $auditorCheckStmt = $dbo->prepare($auditorCheckSql);
                $auditorCheckStmt->bindValue(':auditor_id', $auditorId, PDO::PARAM_INT);
                $auditorCheckStmt->execute();
                $auditor = $auditorCheckStmt->fetch(PDO::FETCH_ASSOC);

                if (!$auditor) {
                    echo json_encode(['success' => false, 'message' => 'Auditor not found.']);
                    exit;
                }
            }

            // Start transaction
            $dbo->beginTransaction();

            try {
                // Update the assignment in training_requests
                $updateSql = "UPDATE training_requests 
                             SET idauditor = :auditor_id, updated_at = CURRENT_TIMESTAMP 
                             WHERE id = :training_id";
                
                $updateStmt = $dbo->prepare($updateSql);
                $updateStmt->bindValue(':auditor_id', $auditorId, PDO::PARAM_INT);
                $updateStmt->bindValue(':training_id', $trainingId, PDO::PARAM_INT);
                $updateStmt->execute();

                // If assigning an auditor (not removing), create trainer activity record
                if ($auditorId) {
                    // PDF should already exist from when training request was created
                    $pdfFilename = 'Training_Request_' . $trainingId . '.pdf';
                    $pdfPath = __DIR__ . '/../files/training_requests/' . $pdfFilename;
                    
                    // Prepare PDF file info JSON in required format
                    $pdfFileInfo = json_encode([
                        'name' => $pdfFilename,
                        'glink' => 'undefined',
                        'hostpath' => realpath($pdfPath) ?: $pdfPath,
                        'hostUrl' => 'files/training_requests/' . $pdfFilename
                    ]);

                    // Check if trainer activity already exists for this training request
                    $existingActivitySql = "SELECT id FROM ttrainer_activities 
                                           WHERE idauditor = :auditor_id 
                                           AND company_name = :company_name 
                                           AND deleted = 0
                                           AND request_id = :request_id";
                    
                    $existingStmt = $dbo->prepare($existingActivitySql);
                    $existingStmt->bindValue(':auditor_id', $auditorId, PDO::PARAM_INT);
                    $existingStmt->bindValue(':company_name', $training['company_name'], PDO::PARAM_STR);
                    $existingStmt->bindValue(':request_id',  $trainingId, PDO::PARAM_INT);
                    $existingStmt->execute();
                    
                    if (!$existingStmt->fetch()) {
                        // Insert into ttrainer_activities
                        $activitySql = "INSERT INTO ttrainer_activities 
                                       (request_id, idauditor, company_name, 
                                        training_request_form, note, created_by, created_at) 
                                       VALUES 
                                       (:request_id, :idauditor, :company_name,
                                        :training_request_form, :note, :created_by, NOW())";
                        
                        $activityStmt = $dbo->prepare($activitySql);
                        $activityStmt->bindValue(':request_id', $trainingId, PDO::PARAM_INT);
                        $activityStmt->bindValue(':idauditor', $auditorId, PDO::PARAM_INT);
                        $activityStmt->bindValue(':company_name', $training['company_name'], PDO::PARAM_STR);
                        $activityStmt->bindValue(':training_request_form', $pdfFileInfo, PDO::PARAM_STR);
                        $activityStmt->bindValue(':note', 'Training assignment for ' . $training['num_participants'] . ' participants. Languages: ' . $training['language'] . ($training['other_language'] ? ', ' . $training['other_language'] : ''), PDO::PARAM_STR);
                        $activityStmt->bindValue(':created_by', $myuser->userdata['id'], PDO::PARAM_INT);
                        $activityStmt->execute();
                        
                        $activityId = $dbo->lastInsertId();
                    }

                    // Send notification email to auditor
                    sendAuditorAssignmentNotification($auditor, $training, $pdfPath, $pdfFilename);
                }

                // Commit transaction
                $dbo->commit();

                $message = $auditorId 
                    ? "Auditor '{$auditor['name']}' assigned to training request '{$training['company_name']}' successfully. Trainer activity created and notification sent."
                    : "Auditor assignment removed from training request '{$training['company_name']}' successfully.";
                
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'auditor_name' => $auditorId ? $auditor['name'] : null
                ]);

            } catch (Exception $e) {
                $dbo->rollback();
                throw $e;
            }
            break;

        case 'get_training_summary':
            // Get training request summary for the assignment panel
            $trainingId = intval($_GET['training_id'] ?? 0);
            
            if ($trainingId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid training request ID.']);
                exit;
            }

            $sql = "SELECT tr.id, tr.company_name, tr.contact_person, tr.idauditor,
                           aud.name as auditor_name
                    FROM training_requests tr
                    LEFT JOIN tusers aud ON aud.id = tr.idauditor AND aud.isclient = 2 AND aud.deleted = 0
                    WHERE tr.id = :training_id AND tr.deleted = 0";
            
            $stmt = $dbo->prepare($sql);
            $stmt->bindValue(':training_id', $trainingId, PDO::PARAM_INT);
            $stmt->execute();
            $training = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($training) {
                echo json_encode([
                    'success' => true,
                    'training' => $training
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Training request not found.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

/**
 * Send email notification to auditor when assigned to training request
 */
function sendAuditorAssignmentNotification($auditor, $training, $pdfPath, $pdfFilename) {
    global $supportEmailAddress;
    
    $fromEmailAddress = "noreply@halal-digital.net";
    
    // Format training dates
    $trainingDates = [];
    if (!empty($training['preferred_date_1'])) {
        $trainingDates[] = date('d/m/Y', strtotime($training['preferred_date_1']));
    }
    if (!empty($training['preferred_date_2'])) {
        $trainingDates[] = date('d/m/Y', strtotime($training['preferred_date_2']));
    }
    if (!empty($training['preferred_date_3'])) {
        $trainingDates[] = date('d/m/Y', strtotime($training['preferred_date_3']));
    }
    $datesString = implode(', ', $trainingDates);
    
    // Send email to assigned auditor
    $body = [];
    $body['name'] = 'Halal Digital';
    $body['email'] = $fromEmailAddress;
    $body['to'] = $auditor['email'];
    $body['subject'] = "Training Assignment - " . $training['company_name'];
    $body['header'] = "";
    $body['message'] = '<p>Dear ' . $auditor['name'] . ',</p>
        <p>You have been assigned a new Halal training request:</p>
        <p><strong>Company:</strong> ' . $training['company_name'] . '<br/>
        <strong>Contact Person:</strong> ' . $training['contact_person'] . '<br/>
        <strong>Email:</strong> ' . $training['email_address'] . '<br/>
        <strong>Phone:</strong> ' . $training['phone_number'] . '<br/>
        <strong>Number of Participants:</strong> ' . $training['num_participants'] . '<br/>
        <strong>Training Type:</strong> ' . $training['training_type'] . '<br/>
        <strong>Languages:</strong> ' . $training['language'] . ($training['other_language'] ? ', ' . $training['other_language'] : '') . '<br/>
        <strong>Preferred Dates:</strong> ' . $datesString . '<br/>
        <strong>Training Cost:</strong> €' . number_format($training['training_cost'], 2) . '</p>
        <p><strong>Company Address:</strong><br/>' . nl2br($training['address']) . '</p>
        <p>Please review the attached training request form and contact the client to arrange the training schedule.</p>
        <p>If you have any questions, please contact the administration team at ' . $supportEmailAddress . '</p>
        <p>Best regards,<br/>
        IIDC Halal Digital Team</p>';
    
    if (file_exists($pdfPath)) {
        $body['attachhostpath'] = $pdfPath;
        $body['attach'] = $pdfFilename;
        
        // Use existing sendEmailWithAttach function
        sendEmailWithAttach($body);
    } else {
        // Use regular sendEmail if PDF doesn't exist
        sendEmail($body);
    }
    
    // Send copy to admin
    $adminBody = [];
    $adminBody['name'] = 'Halal Digital';
    $adminBody['email'] = $fromEmailAddress;
    $adminBody['to'] = $supportEmailAddress;
    $adminBody['subject'] = "Training Assignment Notification - " . $training['company_name'];
    $adminBody['header'] = "";
    $adminBody['message'] = '<p>Dear Admin,</p>
        <p>Training request for <strong>' . $training['company_name'] . '</strong> has been assigned to <strong>' . $auditor['name'] . '</strong> (' . $auditor['email'] . ').</p>
        <p><strong>Training Details:</strong><br/>
        Company: ' . $training['company_name'] . '<br/>
        Contact: ' . $training['contact_person'] . '<br/>
        Participants: ' . $training['num_participants'] . '<br/>
        Type: ' . $training['training_type'] . '<br/>
        Preferred Dates: ' . $datesString . '</p>
        <p>The auditor has been notified and a trainer activity record has been created.</p>
        <p>Best regards,<br/>
        IIDC System</p>';
    
    sendEmail($adminBody);
}
?>