<?php
if (!session_id()) {
	session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';

if (isset($_FILES['attached_documents']) && isset($_FILES['attached_documents']['name'])) {
	$form_options = $_POST['form_options'];
	if (isset($form_options['documents_location']) and trim($form_options['documents_location']) != '') {
		$dirid = $form_options['documents_location'];
		if ($dir = $hqcdb->get_row("SELECT * FROM hqc_files_directories WHERE dirid=$dirid")) {
			$containerName = 'general-data';

			$files = _Files($_FILES['attached_documents']);
			require(tools_path . "/azure-storage/azure-connection.php");

			foreach ($files as $file) {
				if (isset($file["tmp_name"]) and trim($file["tmp_name"]) != '') {
					$file_name = $dir['dir_path'] . '/' . str_replace(' ', '-', $file['name']);

					$data['file_name'] = $file_name;
					$data['description'] = $_REQUEST['form_id'] . ' - ' . $_REQUEST['form_name'];
					$data['uid'] = $_USER['uid'];
					$data['user_type'] = $_USER['type'];

					if (isset($_REQUEST['foid'])) {
						$data['parent'] = "form";
						$data['parent_id'] = $_REQUEST['foid'];
					} else {
						$data['parent'] = '';
						$data['parent_id'] = '';
					}

					$data['status'] = json_encode(array(
						"status" => "new",
						"uid" => $_USER['uid'],
						"user_type" => $_USER['type'],
						"date" => date("Y-m-d H:i:s")
					));

					if ($hqcdb->get_row("SELECT * FROM hqc_filestore WHERE file_name='$file_name' AND parent = '$data[parent]' AND parent_id = '$data[parent_id]")) {
						if (!isset($form_options['replace_old_documents'])) {
							post_results('alert', 'Error: the file (' . $file["name"] . ') already exists.');
							exit();
						}
						$hqcdb->update("hqc_filestore", $data, "file_name='$file_name'");
					} else {
						$hqcdb->insert("hqc_filestore", $data);
					}
					$file_tmp = $file["tmp_name"];
					$content = fopen($file_tmp, 'r');
					insert_blob($containerName, $file_name, $content);
				}
			}
		}
	}
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'get_columns' && isset($_REQUEST['table'])) {
	if ($_REQUEST['table'] == '') {
		echo '';
		exit();
	}
	if ($columns = $hqcdb->get_columns(trim($_REQUEST['table']))) { ?>
		<select size="1" name="column" id="tableColumns">
			<?php foreach ($columns as $column => $name) { ?>
				<option value="<?php echo $column; ?>"><?php echo $column; ?></option>
			<?php } ?>
		</select><i class="far fa-copy" onclick="copyText('#tableColumns',this)"></i>
<?php }
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'change_status') {
	if ($hqcdb->query("UPDATE hqc_forms SET status = '$_REQUEST[status]' WHERE foid = '$_REQUEST[id]'"))
		_e('changed');
	else
		_e('error:Status not changed');
	exit();
}

if ($_REQUEST['act'] == 'delete' and isset($_REQUEST['id'])) {
	if ($hqcdb->query("UPDATE hqc_forms SET status = 'deleted' WHERE foid = '$_REQUEST[id]'"))
		post_results('url', '?inc=forms');
	else
		post_results('alert', __('error:Process not deleted'));
	exit();
}

//change change_published_status
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "change_published_status") {
	if ($_REQUEST['published'] == 'yes') {
		$published = 'no';
		$color = '#eee';
	} else {
		$published = 'yes';
		$color = "green";
	}

	if ($hqcdb->query("UPDATE hqc_forms SET published = '$published' WHERE foid = '$_REQUEST[foid]'"))
		echo $color;
	else
		_e('error:Published Status not changed');
	exit();
}

//change document utv
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "change_utv") {
	if ($hqcdb->query("UPDATE hqc_forms SET utv = 'yes' WHERE foid = '$_REQUEST[id]'"))
		_e('changed');
	else
		_e('error:Status not changed');
	exit();
}

if (isset($_POST['save']))
	unset($_POST['save']);

if (isset($_POST['doExample']) and $_POST['doExample'] == 'yes') {
	if ($amRow = $hqcdb->get_row("SELECT foid FROM hqc_forms WHERE status = 'example'")) {
		$_POST['act'] = 'update_template';
		$_POST['foid'] = $amRow['foid'];
	} else {
		$_POST['act'] = 'save_template';
		$_POST['status'] = 'example';
	}
	$_POST['form_date'] = date("Y-m-d H:i:s");
}

if (isset($_POST['inc'])) {
	$_POST['inc'] = json_encode($_POST['inc']);
} else {
	$_POST['inc'] = '';
}

$uploads_dir = hqc_path . '/data/offices/' . $_POST['offid'] . '/templates';

$form_options = $_POST['form_options'];
$form_options['submit_message'] = $_POST['submit_message'];
$form_options['request_message'] = $_POST['request_message'];
$form_options['approved_message'] = $_POST['approved_message'];
$form_options['disapproved_message'] = $_POST['disapproved_message'];
$form_options['general_message'] = $_POST['general_message'];

$_POST['form_options'] = serialize($form_options);
$_POST['form_meta'] = json_encode($_POST['form_meta'], JSON_UNESCAPED_UNICODE);

if (isset($_POST['usePdfTemplate']) and trim($_POST['usePdfTemplate']) != '')
	$_POST['pdf_template'] = $_POST['usePdfTemplate'];

if (isset($_FILES["pdf_template"]) and trim($_FILES["pdf_template"]['name']) != '') {
	if (!is_dir($uploads_dir))
		mkdir($uploads_dir, 0777, true);

	if (strtolower(pathinfo($_FILES["pdf_template"]['name'], PATHINFO_EXTENSION)) != "pdf") {
		post_results('alert', 'Use PDF file Please');
		exit();
	}
	$tmp_name = $_FILES["pdf_template"]["tmp_name"];
	$name = basename($_FILES["pdf_template"]["name"]);
	move_uploaded_file($tmp_name, "$uploads_dir/$name");
	$_POST['pdf_template'] = $name;
}


if (isset($_POST['the_form']) and trim($_POST['the_form']) != '') {
	if (preg_match_all("/\<input (.*?)\>/is", $_POST['the_form'], $matches, PREG_SET_ORDER)) {
		foreach ($matches as $key => $shortMatch) {
			$codeContent = $shortMatch[0];
			if (strstr($codeContent, 'type="form-start"')) {
				$theInput = str_replace(array('<input', 'type="form-start"', 'placeholder="form"', '  '), array('<form', '', '', ' '), $codeContent);
				$_POST['the_form'] = str_replace($codeContent, $theInput, $_POST['the_form']);
			}
			if (strstr($codeContent, 'type="form-end"')) {
				$_POST['the_form'] = str_replace($codeContent, '</form>', $_POST['the_form']);
			}
		}
	}

	if (preg_match_all("/\<span (.*?)\>(.*?)\<\/span>/is", $_POST['the_form'], $spans, PREG_SET_ORDER)) {
		foreach ($spans as $span) {;
			if (preg_match('/class="phpcode"|class="pdfcode"/', $span[1])) {
				$_POST['the_form'] = str_replace($span[0], $span[2], $_POST['the_form']);
			}
		}
	}

	if (preg_match_all('/<p>\[pdf(.*)\]<\/p>/U', $_POST['the_form'], $pdfs)) {
		foreach ($pdfs[0] as $macth) {
			preg_match('/\[pdf(.*)\]/U', $macth, $pdfTag);
			if (str_replace($pdfTag[0], '', $macth) == '<p></p>')
				$_POST['the_form'] = str_replace($macth, $pdfTag[0], $_POST['the_form']);
		};
	}

	if (preg_match('/<p>(.*)\<\/p>/', $_POST['the_form'], $form)) {
		$_POST['the_form'] = str_replace($form, $form[1], $_POST['the_form']);
	}

	$_POST['the_form'] = str_replace("&nbsp;", "", $_POST['the_form']);

	$_POST['the_form'] = str_replace(
		array(
			'type="hidden-input"',
			'type="file-input"',
			'onclick="void;',
			'<div></div>',
			'<p></p>',
			'[info]',
			'[/info]',
			' autocomplete="none"',
			'&nbsp;',
			'selectedInputForEdit',
			'selectedRadioCheckboxForEdit',
			'selectedElEdited',
			'class=""'
		),
		array(
			'type="hidden"',
			'type="file"',
			'onclick="',
			'<br>',
			'<br>',
			'<info>',
			'</info>',
			'',
			'--',
			'',
			'',
			'',
			'',
			''
		),
		$_POST['the_form']
	);
}

$scripts = array('css_js', 'include_php', 'pdf_css_js', 'pdf_include_php');

foreach ($scripts as $script) {
	if (isset($_POST[$script])) {
		$_POST[$script] = preg_replace('/=\s+=/', '==', $_POST[$script]);
		$_POST[$script] = preg_replace('/!\s+=/', '!=', $_POST[$script]);
		$_POST[$script] = preg_replace('/=\s+>/', '=>', $_POST[$script]);
		$_POST[$script] = preg_replace('/>\s+=/', '>=', $_POST[$script]);
		$_POST[$script] = preg_replace('/<\s+=/', '<=', $_POST[$script]);
		$_POST[$script] = preg_replace('/-\s+>/', '->', $_POST[$script]);
		$_POST[$script] = preg_replace('/\s+=/', ' =', $_POST[$script]);
	}
}

if (isset($_POST['form_tables']))
	$_POST['form_tables'] = encode_json($_POST['form_tables']);
else
	$_POST['form_tables'] = "";

if ($_POST['save_action'] == 'clean_and_save')
	$_POST['the_form'] = remove_attr($_POST['the_form'], 'width,height', 'table,th,td', true);


if (@!$doc = load_DOMDocument($_POST['the_form']))
	return;

if ($tables = $doc->getElementsByTagName('table')) {
	for ($i = $tables->length - 1; $i >= 0; $i--) {
		$table = $tables[$i];
		if ($trs = $table->getElementsByTagName('tr')) {
			$trs[0]->setAttribute('class', 'firstTr');
			$trs[count($trs) - 1]->setAttribute('class', 'lastTr');
		}
	}
}

$_POST['the_form'] = unload_DOMDocument($doc);

if ($_POST['act'] == "insert") {
	$_POST['status'] = 'active';
	$_POST['utv'] = 'yes';
	//	define('showParsed', true);
	$_POST['foid'] = $hqcdb->insert("hqc_forms", $_POST);
	post_results('function', 'formInserted('. $_POST['foid'].')');

}


if ($_POST['act'] == "update") {
	//define('showParsed',true);
	$hqcdb->update("hqc_forms", $_POST);
}



if (isset($_POST['doExample']) and $_POST['doExample'] == 'yes')
	post_results('ifUrl', '?inc=admin-form&pg=iframe&foid=' . $_POST['foid']);
elseif ($_POST['save_action'] == 'save_and_add')
	post_results('url', dirname($_SESSION['user_url']) . '?inc=form-maker');
elseif ($_POST['save_action'] == 'save')
	post_results('url', '/admin/forms/');
elseif ($_POST['save_action'] == 'save_and_reload')
	post_results('reload');
else
	post_results('alert', 'Form is saved');
