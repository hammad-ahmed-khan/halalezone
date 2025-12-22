<?php
if (!session_id())
	session_start();
if (!isset($_SESSION["username"]))
	return;
header('Content-Type: text/html; charset=utf-8');
if (!defined('_HQC_'))
	define("_HQC_", 1);

include dirname(__FILE__) . "/config/paths.inc.php";
include "$prog_path/config/date_conv.inc.php";
include "$prog_path/config/countries.code.php";

if ($amdb->get_row("SELECT * FROM users where ip='$_SERVER[REMOTE_ADDR]' and active='b'")) {
	echo "You are not allowed to use this site";
	return;
}
extract($_REQUEST);
$cur_www = str_replace($_SERVER['DOCUMENT_ROOT'], "", getcwd());
$cur_path = getcwd();
$user_type = $_SESSION['user_type'] ?? '';
?>
<?php
if (isset($_SESSION["username"])) {
	$username = $_SESSION["username"];
	if (isset($_SESSION["offid"])) {
		$offid = $_SESSION["offid"];
		$user_permissions = array('clients_actions');
		$user_clients = array();
		if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='$offid'")) {
			$user_clients = explode(',', $office['clients']);
		}
	} else {
		if (isset($_SESSION["clid"])) {
			$clid = $_SESSION["clid"];
		} else {
			$office = $amdb->get_row("SELECT * FROM offices WHERE offid='0'");
			if (isset($_SESSION["uid"]))
				$uid = $_SESSION["uid"];
			elseif (isset($_SESSION['comemid']))
				$uid = $_SESSION['comemid'];
		}
		// include "$prog_path/hqc_users/$username.usr.php";
		// include "$prog_path/hqc_users/admin_users.inc.php";
		// $admin_users = json_decode($admin_users,true);
	}
} else {
	exit();
}

if (isset($office)) {
	$office['office_name'] = $office['company_name_english'];
	$office['office_address'] = $office['office_street'] . "<br/>"
		. $office['office_zipcode'] . ", " . $office['office_city'] . "<br/>"
		. $country[$office['office_country']] . "<br/>"
		. 'Tel.: ' . $office['office_telephone'] . "<br/>"
		. 'Email: ' . $office['office_email'] . "<br/>";
	if (trim($office['office_website']) != '')
		$office['office_address'] .= 'Website: ' . $office['office_website'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>HQC</title>
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<link rel="icon" type="image/png" href="/images/small-logo.png">
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/style.css?vr=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/fonts/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/home.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/tools.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/post-form.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/tinymce/tinymce.min.js"></script>
	<script src="https://terrylinooo.github.io/jquery.disableAutoFill/assets/js/jquery.disableAutoFill.min.js"></script>
	<script type="text/javascript">
		function closePopupDialog() {
			jQuery('.ui-icon-closethick', window.parent.document).click();
		}

		var prog_www = "<?php echo $prog_www ?>";
		var cur_dir = "<?php echo basename(getcwd()) ?>";
		jQuery(document).ready(function(e) {
			if (jQuery("body").find("[data-type='cancel']").length) {
				jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();
			}
			jQuery(".ui-draggable .ui-dialog-titlebar", window.parent.document).css({
				"color": "#FFF",
				"font-size": "14px",
				"background": "brown"
			});

			setTimeout(function() {
				jQuery("#PageContent").css("visibility", "visible");
				jQuery(".loading").css("display", "none");
				//find in parent window button with text cancel and add class btn-cancel
				jQuery('.ui-icon-closethick,.ui-dialog-buttonpane button:contains("Cancel")', window.parent.document).on('click', function() {
					jQuery('body', window.parent.document).css("overflow", "auto");
				});
				<?php if (!isset($_REQUEST['noResize'])) { ?>
					if (jQuery("body").height() > 100) {
						bodyHeight = jQuery("body").height();
						jQuery('#iframePageContent', window.parent.document).height(bodyHeight + 'px');
						if (window.parent.innerHeight < bodyHeight)
							jQuery("body").css("overflow", "auto");
						popupObj = jQuery('.ui-dialog', window.parent.document);
						popupBody = jQuery('html', window.parent.document).scrollTop();
						popupTop = popupBody + (window.parent.innerHeight - popupObj.height()) / 2;
						jQuery(popupObj).css("top",popupTop+"px");
					}
				<?php }; ?>
			}, 500);
			autocomplete_off();
		});
	</script>
	<style>
		.loading {
			width: 150px;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			position: absolute
		}
	</style>
</head>

<body style="background:none !important;background:url(<?php echo $prog_www ?>/images/loading.gif)">
	<img src="<?php echo $prog_www ?>/images/loading.gif" width="150px;" class="loading" />
	<div id="PageContent" style="visibility:hidden;">
		<?php if (isset($inc) and trim($inc) != '' && file_exists(getcwd() . "/$inc.inc.php")) {
			include getcwd() . "/$inc.inc.php";
		} else {
			echo 'file ' . $inc . '.inc.php not found';
		}
		?>
	</div>
</body>

</html>