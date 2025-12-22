<?php
session_start();
$username = $_SESSION["username"];
// include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
include "../config/get_ip.inc.php";
include "../config/defaults.inc.php";
//TODO: update new system(serialize products)
//fix double address add_HQC_address
extract($_POST);
if (isset($tp))
	$tbl = "certificates_$tp";

if ($_POST['act'] == 'draft') {
	$_POST['act'] = 'add';
	$_POST['status'] = 'draft';
}
if ($_POST['act'] == 'saveDraft') {
	$_POST['act'] = 'edit';
	$_POST['status'] = 'draft';
}

if ($_POST['act'] == 'edit') {
	$_POST['status'] = 'active';
}

if (isset($act) and $act == 'delete_file' && isset($file) && isset($_POST['nr'])) {
	if (trim($_POST['nr']) == '')
		return;

	if (file_exists($hcp_path . $file))
		unlink($hcp_path . $file);

	if ($certificate = $amdb->get_row("SELECT attachments FROM $tbl WHERE nr='$_POST[nr]'")) {
		$attachments = array_values(json_decode($certificate['attachments'], true));

		if (in_array($file, $attachments)) {
			//remove from array
			$file = array_search($file, $attachments);
			unset($attachments[$file]);
			$amdb->update($tbl, array('attachments' => json_encode($attachments)), "nr='$_POST[nr]'");
			echo "ok";
		}
	}
	exit();
};

if (isset($_POST['nr']))
	$crtNr = $_POST['nr'];

if (isset($_POST['option'])) {
	$toCheck = array('producer', 'importer', 'exporter');
	foreach ($toCheck as $check) {
		if (isset($_POST['option'][$check])) {
			$_POST['option'][$check] = trim($_POST['option'][$check]);
			if ($_POST[$check] == '0' && $_POST['option'][$check] == '') {
				$amdb->post_results($check . ' is required');
				exit();
			}
		}
	}
	if (isset($_POST['options']))
		$_POST['options'] = $_POST['options'] + $_POST['option'];
	else
		$_POST['options'] = $_POST['option'];
}

if (isset($_POST['extra']))
	$_POST['options'] = $_POST['options'] + array("extra" => $_POST['extra']);

if (isset($_POST['options']))
	$_POST['options'] = json_encode($_POST['options'], JSON_UNESCAPED_UNICODE);

if (!isset($_POST['print_flag']))
	$_POST['print_flag'] = 0;

if (!isset($_POST['eiaci']))
	$_POST['print_eiaci'] = 0;
else
	$_POST['print_eiaci'] = 1;

if (!isset($_POST['shc']))
	$_POST['print_shc'] = 0;
else
	$_POST['print_shc'] = 1;

if (!isset($_POST['hak']))
	$_POST['print_hak'] = 0;
else
	$_POST['print_hak'] = 1;

if (isset($_POST['products'])) {
	$_POST['products'] = serialize($_POST['products']);
}

if (!isset($_POST['tmplid']))
	$_POST['tmplid'] = 0;

//TODO: update new system weight gross and net
if (trim($_POST['weight_gross_gram']) == '')
	$_POST['weight_gross_gram'] = '0';

if (isset($_POST['weight_gross']) && trim($_POST['weight_gross_gram']) != '')
	$_POST['weight_gross'] .= '.' . $_POST['weight_gross_gram'];

if (trim($_POST['weight_net_gram']) == '')
	$_POST['weight_net_gram'] = '0';

if (isset($_POST['weight_net']) && trim($_POST['weight_net_gram']) != '')
	$_POST['weight_net'] .= '.' . $_POST['weight_net_gram'];

if (isset($_POST['act'])) {
	if ($_POST['act'] == 'add' && check_nonce()) {
		$_POST['date'] = date("d/m/Y");
		$_POST['requested_by'] = json_encode($_SESSION['user']);
		if (isset($_POST['do']) && $_POST['do'] == 'print')
			$_POST['hcd_process'] = 'printed on: ' . date("d/m/Y H:i:s");
		$crtNr = $amdb->insert($tbl, $_POST);
		$_POST['nr'] = $crtNr;
	}
	if ($_POST['act'] == 'edit') {
		$amdb->update($tbl, $_POST, "nr='$_POST[nr]'");
	}

	if (isset($_POST['importer']) && $_POST['importer'] != '' && isset($_POST['CRN']) && trim($_POST['CRN']) != '') {
		$amdb->update('companies', array('CRN' => $_POST['CRN']), "clid='$_POST[importer]'");
	}
}
if (isset($_FILES) && isset($_FILES['attachment']) && count($_FILES['attachment']) > 0) {
	$path = "/client_data/certificates/attachments/" . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT) . "/" . $crtNr;
	if ($attachments = upload_files($_FILES['attachment'], $path, true)) {

		if ($certificate = $amdb->get_row("SELECT attachments FROM $tbl WHERE nr='$crtNr'")) {
			if (is_array(json_decode($certificate['attachments'], true)))
				$attachments = array_merge(json_decode($certificate['attachments'], true), $attachments);
		}

		$amdb->update($tbl, array('attachments' => json_encode($attachments)), "nr='$crtNr'");
	};
}

if (isset($act) and $act == 'del') {
	MYSQL_QUERY("DELETE from $tbl where nr='$nr'");
}

if (isset($_POST['do']) && $_POST['do'] == 'print') {

	$urlQuery = '';

	if (isset($_POST['keepOldCrtNumber']) && isset($_POST['certificate_nr']) && trim($_POST['certificate_nr']) != '')
		$urlQuery .= '&keepOldCrtNumber=1&certificate_nr=' . $_POST['certificate_nr'];

	if (isset($_POST['print_flag']) && $_POST['print_flag'] == 1)
		$urlQuery .= '&flag=1';

	if (isset($_POST['eiaci']) && $_POST['eiaci'] == 1)
		$urlQuery .= '&eiaci=1';

	if (isset($_POST['shc']) && $_POST['shc'] == 1)
		$urlQuery .= '&shc=1';

	if (isset($_POST['hak']) && $_POST['hak'] == 1)
		$urlQuery .= '&hak=1';

	//$amdb->post_results('../offices/home/', 'url');
?>
	<script>
		window.parent.document.certificateForm.action = 'pdf_certificate.php';
		window.parent.document.certificateForm.target = '_blank';
		window.parent.document.getElementById('act').value = 'print';
		window.parent.document.getElementById('nr').value = '<?php echo $_POST['nr']; ?>';
		window.parent.document.certificateForm.submit();
	</script>
<?php
	if ($_SESSION['user_type'] == "admin")
		$amdb->post_results("/admin/?inc=certificates&tp=$_POST[tp]&offid=$_POST[offid]", 'url');
	elseif ($_SESSION['user_type'] == "hqc_office")
		$amdb->post_results('../offices/home/', 'url');
}

if ($_SESSION['user_type'] == "admin")
	$amdb->post_results('../admin/', 'url');
elseif ($_SESSION['user_type'] == "hqc_office")
	$amdb->post_results('../offices/home/', 'url');
else
	$amdb->post_results('../company/', 'url');
