<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // ??????? ?????? ??????????? ? ??

    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];

    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];
    
    /*
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];
    */
    
    $id = getPostParam('id');

    $decode = file_get_contents( __DIR__ ."/../config.json");
	$config=json_decode($decode, TRUE);
    
    /*
     <div class="row">
        <div class="col-md-8">
            <h5 class="mb-1">John Doe <small>(john.doe@example.com)</small></h5>
        </div>
        <div class="col-md-4 text-right">
            <small>2024-05-27 10:30 AM</small>
        </div>
      </div>
      <p class="mb-1">This is a reply to the ticket.</p>
    */
       
    $filter = " WHERE t.id='".$id."'";
  
    $sql = '
    SELECT t.id, 
           t.user_id, 
           t.username, 
           t.idauditor, 
           t.idclient,
           t.email, 
           t.status, 
           t.issue_type, 
           t.issue_description, 
           t.current_url, 
           t.attachments, 
           date_format(t.created_at, \'%d/%m/%Y %h:%i %p\') AS date_created, 
           date_format(t.updated_at, \'%d/%m/%Y %h:%i %p\') AS last_updated, 
           t.last_updated_by_name, 
           t.last_updated_by_email,
           auditor.name AS auditorname, 
           auditor.email AS auditoremail, 
           client.name AS clientname, 
           client.prefix AS clientprefix
    FROM ttasks t
    LEFT JOIN tusers AS auditor ON auditor.id = t.idauditor AND auditor.isclient = 2
    LEFT JOIN tusers AS client ON client.id = t.idclient AND client.isclient = 1
    ' . $filter;
    $data = [];
    $i=0;
	$res = $dbo->prepare($sql);
	if (!$res->execute()) die($sql);
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {

        if ($user_id == $row["user_id"]){
            $row['iscreator'] = "1";
        }
        else {
            $row['iscreator'] = "0";
            
        }

        $row['login_id'] = $user_id;


        if ($row['last_updated_by_id'] != $user_id) {
            $updateSql = "UPDATE ttickets SET viewed = 1 WHERE id = '".$row["id"]."'" ;
            $updateRes = $dbo->prepare($updateSql);
            if (!$updateRes->execute()) {
                die($updateSql);
            }
        }

        if ($row['last_updated_by_name'] == "") {
            $row['last_updated'] =  "";
        }
        else {
            $row['last_updated'] =  $row['last_updated'] . ' by ' . $row['last_updated_by_name'];
        }        

        if ($row['status'] == '1') {
            //$row['status'] = '<span class="badge badge-success">Open</span>';
        } else {
            //$row['status'] = '<span class="badge badge-danger">Closed</span>';
        }

        $attachments = $row['attachments'];

        if (!empty($attachments)) {
            $attachmentLinks = explode(',', $attachments);
            $attachmentsHtml = '';
            /*
            foreach ($attachmentLinks as $link) {
                $link = trim($link); // Remove any extra spaces
                $thumb = $config['filesfolder']."/".$config['clientsfolder']."/".$row['username']." (".$row['user_id'].")/tickets/thumbnail/".$link;
                $large = $config['filesfolder']."/".$config['clientsfolder']."/".$row['username']." (".$row['user_id'].")/tickets/".$link;
                if (!empty($link)) {
                    $attachmentsHtml .= '<a href="' . $large . '" download><img src="' . $thumb . '" alt="Attachment" style="width:100px; height:auto; margin:5px;"></a>';
                }
            }
            */
            foreach ($attachmentLinks as $link) {
                $link = trim($link); // Remove any extra spaces
                $thumb = $config['filesfolder']."/tickets/thumbnail/".$link;
                $large = $config['filesfolder']."/tickets/".$link;
                if (!empty($link)) {
                    $fileExtension = pathinfo($link, PATHINFO_EXTENSION);
                    if (in_array($fileExtension, ['xls', 'xlsx'])) {
                        // Use Excel icon for Excel files
                        $icon = '<img src="/img/ms-excel.svg" alt="Excel File" style="width:50px; height:auto; margin:5px;">';
                        $attachmentsHtml .= '<a href="' . $large . '" download>' . $icon . '</a>';
                    } else if (in_array($fileExtension, ['pdf'])) {
                        // Use Excel icon for Excel files
                        $icon = '<img src="/img/pdf.png" alt="PDF File" style="width:50px; height:auto; margin:5px;">';
                        $attachmentsHtml .= '<a href="' . $large . '" download>' . $icon . '</a>';
                    } else {
                        // Use thumbnail for other files
                        $attachmentsHtml .= '<a href="' . $large . '" download><img src="' . $thumb . '" alt="Attachment" style="width:100px; height:auto; margin:5px;"></a>';
                    }
                }
            }
            
        } else {
            $attachmentsHtml = 'No attachments';
        }

        $row['attachments'] = $attachmentsHtml;

        $row['clientname'] = $row["clientname"] . ' - ' .  $row["clientprefix"] . $row["idclient"];

        // Query to get replies for the current ticket
        $replies_sql = 'SELECT r.username, r.email, r.message, r.attachments, date_format(r.created_at, \'%d/%m/%Y %h:%i %p\') AS reply_date
        FROM treplies r WHERE r.task_id = :task_id ORDER BY r.created_at ASC';

        $replies_res = $dbo->prepare($replies_sql);
        $replies_res->bindParam(':task_id', $id, PDO::PARAM_INT);
        if (!$replies_res->execute()) die($replies_sql);

        $replies_html = '';
        while($reply = $replies_res->fetch(PDO::FETCH_ASSOC)) {
            $attachments = $reply['attachments'];
            $attachmentsHtml = '';

            if (!empty($attachments)) {
                $attachmentLinks = explode(',', $attachments);
    
                foreach ($attachmentLinks as $link) {
                    $link = trim($link); // Remove any extra spaces
                    $thumb = $config['filesfolder']."/thumbnail/".$link;
                    $large = $config['filesfolder']."/tickets/".$link;
                    if (!empty($link)) {
                        $fileExtension = pathinfo($link, PATHINFO_EXTENSION);
                        if (in_array($fileExtension, ['xls', 'xlsx'])) {
                            // Use Excel icon for Excel files
                            $icon = '<img src="/img/ms-excel.svg" alt="Excel File" style="width:50px; height:auto; margin:5px;">';
                            $attachmentsHtml .= '<a href="' . $large . '" download>' . $icon . '</a>';
                        } else if (in_array($fileExtension, ['pdf'])) {
                            // Use Excel icon for Excel files
                            $icon = '<img src="/img/pdf.png" alt="PDF File" style="width:50px; height:auto; margin:5px;">';
                            $attachmentsHtml .= '<a href="' . $large . '" download>' . $icon . '</a>';
                        } else {
                            // Use thumbnail for other files
                            $attachmentsHtml .= '<a href="' . $large . '" download><img src="' . $thumb . '" alt="Attachment" style="width:100px; height:auto; margin:5px;"></a>';
                        }
                    }
                }
            } 
                       
            $replies_html .= '
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-1">' . htmlspecialchars($reply['username']) . ' <small>(' . htmlspecialchars($reply['email']) . ')</small></h5>
                        </div>
                        <div class="col-md-4 text-right">
                            <small>' . htmlspecialchars($reply['reply_date']) . '</small>
                        </div>
                    </div>
                    <p class="mb-1">' . nl2br(htmlspecialchars($reply['message'])) . '</p>
                    <p class="mb-1">' . $attachmentsHtml . '</p>
                </div>';
        }

        $row["replies"] = $replies_html; 

        $data = $row;
    }

    echo json_encode($data);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>