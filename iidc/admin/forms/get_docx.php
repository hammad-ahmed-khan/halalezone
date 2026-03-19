<?php
if (!session_id()) {
	session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';

if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'open-file') {
?>
	<link rel="stylesheet" type="text/css" href="/js-css/css/style.css?ver=<?php echo time(); ?>">
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script>
	</script>
		<style>
			div {
				padding: 20px;
				text-align: center;
				background: #fff;
				position: absolute;
				width: 100%;
				height: 100%;
			}
		</style>
		<div>
			<form action="<?php echo this_url();?>/get_docx.php" enctype="multipart/form-data" method="post">
			<input type="file" name="docx_file" id="chooseDocxFile" style="position:fixed;top:-5000px" accept=".docx" onchange="jQuery('#selectedFile').html(this.value)"/>
			<input type="button" value="Choose Docx document" onclick="jQuery('#chooseDocxFile').click()"/>
			<input type="submit" value="Get Content" /><br/>
			<span id="selectedFile" class="info"></span>
			</form>
		</div>
	<?php
	exit();
}

if (isset($_FILES["docx_file"]) and trim($_FILES["docx_file"]['name']) != '') {

	$ext = strtolower(pathinfo($_FILES["docx_file"]['name'], PATHINFO_EXTENSION));
	if ($ext = "docx" or $ext == "dotx") {
		$tmp_name = $_FILES["docx_file"]["tmp_name"];
		$name = time() . '-' . str_replace(' ', '-', basename($_FILES["docx_file"]["name"]));
		if (move_uploaded_file($tmp_name, temp_path . "/$name")) {
			$file_name = temp_path . "/$name";
		} else {
			post_results('alert', __('Could not upload the file'));
		}
	} else {
		post_results('alert', __('File is not Docx'));
		exit();
	};
}

if (isset($file_name) and file_exists($file_name)) {
	include tools_path . "/load-docx.inc.php";
	$docx = new hqcDocx;
	$theDocxElements = $docx->get_docx_html($file_name, true);
	post_results('tinymce', $theDocxElements);
	unlink($file_name);
	post_results('closePopup');
}
