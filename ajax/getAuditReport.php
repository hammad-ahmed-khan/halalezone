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

	$s = trim($_POST["s"]);
	$idclient = $_POST["idclient"] == "" ? -1 : $_POST["idclient"] ;
	$idapp =  $_POST["idapp"] == "" ? -1 : $_POST["idapp"];
	
	$query = "SELECT *, date_format(Deadline, '%d/%m/%Y') as Deadline
	FROM tauditreport	
	WHERE idclient='".$idclient."' AND idapp='".$idapp."'";
	
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$counts = array(
				'Major' => 0,
				'Minor' => 0,
				'OBS' => 0,
				'Confirmed' => 0,
				'NotConfirmed' => 0);
	
	$data = array();
	foreach($docs as $d) {
		$data[] = $d;
	}
	
	$i = 0;
	foreach ($data as $key=>$val) {
		// add new button
		$id = $data[$i]['id'];
		$Type = $data[$i]['Type'];
		$Deviation = str_replace("<br/>", "\n", $data[$i]['Deviation']);
		$Reference = $data[$i]['Reference'];
		$counts[$Type]++;
		
		/*
		$data[$i]['Type'] = '<select id="Type_'.$id.'" name="Type_'.$id.'" class="selectpicker form-control hidden"  style="width:100%;" data-live-search="true" title="">
                      <option value="" '.($Type==''?' selected' :'').'>Select an Option</option>
                      <option value="1" '.($Type=='1'?' selected' :'').'>Major</option>
                      <option value="2" '.($Type=='2'?' selected' :'').'>Minor</option>
                      <option value="3" '.($Type=='3'?' selected' :'').'>OBS</option>
                    </select>';												
		$data[$i]['Deviation'] = '<textarea class="form-control" name="Deviation_'.$id.'" id="Deviation_'.$id.'">'.$Deviation.'</textarea>';				
		$data[$i]['Reference'] = '<input type="text" class="form-control" name="Reference_'.$id.'" id="Reference_'.$id.'" value="'.$Reference.'"/>';				
		*/
		//$data[$i]['Deadline'] = 'N/A';	
		$Status = $data[$i]['Status'];
		$Implemented = $data[$i]['Implemented'];
		
		if ($Type == 'Major') {
			if ($Status == '0' || $Implemented == '0') {
				$counts["NotConfirmed"]++;	
				$data[$i]['r_color'] = '#f2dede';
			}
			else if ($Status == '1' && $Implemented == '1') {
				$counts["Confirmed"]++;	
				$data[$i]['r_color'] = '#dff0d8';
			}
		}
		else { 
			if ($Status == '0') {
				$counts["NotConfirmed"]++;	
				$data[$i]['r_color'] = '#f2dede';
			}
			else {
				$counts["Confirmed"]++;	
				$data[$i]['r_color'] = '#dff0d8';
			}
		}
			
		if ($myuser->userdata['isclient']) { 
			  $data[$i]['button'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-primary btn-measure">
						   <span class="glyphicon glyphicon-edit" title="Add Measure" aria-hidden="true"></span>
						   </a>';

			$checked = ($Status == '1') ? 'checked' : ''; 

			if ($Status != '1') {
				$data[$i]['Status'] = '<div class="text-danger">
						<span class="fa fa-times-circle" aria-hidden="true"></span> Under Review
						</div>';
		   }
		   else { 
				$data[$i]['Status'] = '<div class="text-success">
						<span class="fa fa-circle-check" aria-hidden="true"></span> Accepted
						</div>';
		   }

		   if ($Implemented != '1') {
			$data[$i]['Status'] .= '<div class="text-danger" style="margin-top:5px;'.(($Status == '1') ? '' : 'display:none;').'">
					<span class="fa fa-times-circle" aria-hidden="true"></span> Not Implemented
					</div>';
	   }
	   else { 
			$data[$i]['Status'] .= '<div class="text-success" style="margin-top:5px;'.(($Status == '1') ? '' : 'display:none;').'">
					<span class="fa fa-circle-check" aria-hidden="true"></span> Implemented
					</div>';
	   }
						
			  /*
			  if ($Status == '0') {
				   $data[$i]['Status'] = '<span class="text-danger">
						   <span class="fa fa-times-circle" aria-hidden="true"></span> Not Confirmed
						   </span>';
			  }
			  else { 
				   $data[$i]['Status'] = '<span class="text-success">
						   <span class="fa fa-circle-check" aria-hidden="true"></span> Confirmed
						   </span>';
			  }
			  */
			  
		}
		$linkHtml = ""; 

		$existingDocuments = json_decode($data[$i]['Documents'], true);
    
    // Initialize an empty array if Documents field is null or empty
    if (!$existingDocuments) {
        $existingDocuments = [];
    }
    
 
    
    // Loop through each document
    foreach ($existingDocuments as $document) {
        // Generate HTML link for document
		if ($document['deleted'] != '1') { 
			$linkHtml .= '<span class="cvitem"><a target="_blank" href="' . htmlspecialchars($document['hostpath']) . '" title="' . htmlspecialchars($document['name']) . '">' . htmlspecialchars($document['name']) . '</a>';
			if ($myuser->userdata['isclient']) { 
				$linkHtml .= '<a class="delete-doc text-danger" document="' . $document['name'] . '" id="'.$data[$i]['id'].'"><i class="fa fa-trash"></i></a></span>';
			}
		} 
		else {
			$linkHtml .= '<span class="cvitem"><span '.($document['deleted'] == '1' ? 'style="text-decoration:line-through;"' : '').' >' . htmlspecialchars($document['name']) . '</span>';
		}
        // Generate delete link (assuming you have a FontAwesome delete icon)
      }
     
		if ($myuser->userdata['isclient']) { 
			$inputClass = "1";
			$folderType = 'report';
		$linkHtml .=
        '<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div></div>' .
        '<span class="fileinput-button fib-dropzone dropzone' .
        $inputClass .
        '"><span class="spinner-border spinner-border-sm"></span> <span class="dropzone-title"> <svg viewBox="0 0 16.00 16.00" xmlns="http://www.w3.org/2000/svg" fill="#69b0ce" class="bi bi-cloud-arrow-up-fill" transform="rotate(0)matrix(1, 0, 0, 1, 0, 0)" stroke="#69b0ce" stroke-width="0.00016"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.128"></g><g id="SVGRepo_iconCarrier"> <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2z"></path> </g></svg> <span class="drop-text">Drag files here<br>or click to select</span></span>' .
        '<input id="'.$data[$i]['id'].'" idclient="'.$idclient.'" idapp="'.$idapp.'" class="fileupload fileupload' .
        $inputClass .
        '" type="file" name="files[]"  foldertype="' .
        $folderType .
        '" infotype="report">' .
        "</span>" .
        '<ul class="ul' .
        $folderType .
        '"></ul>' .
        '<div class="alert-string"></div>';

			
			/*
			$data[$i]['Documents'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-info btn-upload">
						   <span class="fa-solid fa-upload" aria-hidden="true"></span>
						   </a>';
			*/
		}
		$data[$i]['Documents'] = $linkHtml;

		if ($myuser->userdata['isclient'] != '1') { 
			  /*
			  if ($Status == '0') {
				   $data[$i]['Status'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-danger btn-confirm">
						   <span class="fa fa-times-circle" aria-hidden="true"></span> Not Confirmed
						   </a>';
			  }
			  else { 
				   $data[$i]['Status'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-success btn-unconfirm">
						   <span class="fa fa-circle-check" aria-hidden="true"></span> Confirmed
						   </a>';
			  }
			*/
			
			$checked = ($Status == '1') ? 'checked' : ''; 
			$data[$i]['Status'] = '<div style="width:125px;margin:0 auto;" class="btn btn-'.($Status == '1' ? "success" : "danger").'"><input type="checkbox" id="confirm_'.$data[$i]['id'].'" class="confirm-checkbox" data-id="'.$data[$i]['id'].'" '.$checked.'>
			<label for="confirm_'.$data[$i]['id'].'"> Accepted</label></div>';

			$checked = ($Implemented == '1') ? 'checked' : ''; 
			$data[$i]['Status'] .= '<div class="btn btn-'.($Implemented == '1' ? "success" : "danger").'" style="'.(($Status == '1') ? '' : 'display:none;').'width:125px;margin:5px auto 0;"><input type="checkbox" id="implement_'.$data[$i]['id'].'" class="implement-checkbox" data-id="'.$data[$i]['id'].'" '.$checked.'>
			<label for="implement_'.$data[$i]['id'].'"> Implemented	</label></div>';

			
			  $data[$i]['button'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-danger btn-deldev">
						   <span class="glyphicon glyphicon-trash" title="Delete" aria-hidden="true"></span>
						   </a>';
		}
		
		$i++;
	}

	$datax = array(
	'counts' => $counts,
	'recordsTotal' => $TotalCount,
	'recordsFiltered' => $TotalCount,	
	'data' => $data);
	echo json_encode($datax);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>