<?php
//show php errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_GET['ifr'])) {
	include dirname(__FILE__) . '/iframe.php';
	exit();
}
if (isset($_COOKIE['predefined'])) {
	setcookie('predefined', true, time() + (30 * 60), '/');
}
if (!session_id())
	session_start();
header('Content-Type: text/html; charset=utf-8');
if (!defined('_HQC_'))
	define("_HQC_", 1);
if (!defined('__HQC__'))
	define("__HQC__", 1);

include dirname(__FILE__) . "/config/paths.inc.php";
include "$prog_path/config/date_conv.inc.php";
if ($amdb->get_row("SELECT * FROM users where ip='$_SERVER[REMOTE_ADDR]' and active='b'")) {
	echo "You are not allowed to use this site";
	return;
}

extract($_REQUEST);
$cur_www = str_replace($_SERVER['DOCUMENT_ROOT'], "", getcwd());
$cur_path = getcwd();
if (isset($_GET['ref'])) {
	$_SESSION['ref'] = $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>HQC</title>
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<link rel="icon" type="image/png" href="/images/small-logo.png">
	<link href="<?php echo $prog_www; ?>/fonts/google-fonts.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/style.css?vr=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/fonts/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script type="text/javascript">
		var prog_www = "<?php echo $prog_www ?>";
		var hqc = "<?php echo $hqc; ?>";
		var cur_dir = "<?php echo basename(getcwd()) ?>";
		var act = "<?php echo isset($_GET['act']) ? $_GET['act'] : ''; ?>";
		var inc = "<?php echo isset($_GET['inc']) ? $_GET['inc'] : ''; ?>";
		var curUrl = '<?php echo isset($_GET['act']) ? json_encode($_GET) : ''; ?>';
		var userType = "<?php echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : ''; ?>";
	</script>
	<script src="<?php echo $prog_www; ?>/scripts/jquery.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/home.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/tools.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/post-form.js?vr=<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $prog_www; ?>/scripts/tinymce/tinymce.min.js"></script>
	<script>
		jQuery(document).ready(function(e) {
			if (jQuery("#adminInfo").length > 0 && jQuery("#adminInfo").html().trim() != '')
				jQuery("#adminInfo").css("display", "block");
			jQuery(".maninMenu a").each(function() {
				if (typeof jQuery(this).attr("href") == 'undefined') {
					jQuery(this).css("cursor", "default")
				}
			})
		});
		<?php if (isset($_SESSION["username"]) && $_SESSION["username"] == 'admin') { ?>

			function logMeInAsClient(username) {
				alert(username);
				if (username != '') {
					jQuery.post('/login_out.php', {
						act: 'logInAsClient',
						username: username
					}).done(function(data) {
						if (data.trim() != '') {
							document.location.href = data;
						}
					});
				}
			}
		<?php }; ?>
	</script>
	<?php if (isset($_SESSION["username"])) { ?>
		<style>
			body {
				background: url('<?php echo $prog_www ?>/images/bg-1.jpg') center;
			}

			<?php if (!isset($underConstruction['user'])) { ?>body:before {
				content: '';
				background: #fff;
				position: fixed;
				width: 1400px;
				height: 100%;
				left: 50%;
				transform: translate(-50%, 0%);
			}

			<?php }; ?><?php if (is_mobile()) { ?>.nowrap {
				white-space: inherit;
			}

			<?php }; ?>.searchOptionsList {
				position: fixed;
				max-width: 300px;
			}
		</style>
	<?php } else { ?>
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<?php }; ?>
</head>

<body>
	<?php if (isset($_SESSION["username"])) { ?><div id="contentBG"></div><?php }; ?>
	<?php
	if (isset($underConstruction['user']) and isset($underConstruction['under_construction_message'])) { ?>
		<div class="underConstruction">
			<div id="logingLogo"><img src="/images/small-logo.png" /></div>
			<div><?php echo $underConstruction['under_construction_message']; ?></div>
			<div style="margin-top:20px;text-align:center;"><a href="<?php echo $prog_www ?>/logout.php" target=_top>Log-out</a>
			</div>
			<?php } else {
			if (!isset($ttl)) $ttl = 'Home';
			if (isset($_SESSION["username"]) or (isset($inc) and $inc == 'register')) {
			?>

				<div style="position:fixed;top:0px;left:0px;right:0px;z-index:10;min-width:1045px">
					<div id="menuContainer" style="border: 1px solid #C0C0C0;max-width:1400px;height:80px; margin:0 auto;overflow:hidden;background:#f0f5e5 ;">
						<img src="<?php echo $prog_www ?>/images/iidc-logo.png" style="position:absolute;overflow:hidden;margin:5px 0px 0px 20px;max-width:120px" />
						<div class="sub_title menu-top" style="text-align:left;color:#000;margin-left:140px;border-bottom:2px solid <?php echo (isset($local)) ? 'red' : '#555'; ?>">
							<span style="float: right;" id="logOutLink"></span>
							<b> <?php echo (isset($_SESSION['hqc_title'])) ? $_SESSION['hqc_title'] : 'IIDC GmbH - Halal Certification'; ?></b> <?php echo (isset($local)) ? '<b style="color:red">LOCAL</b>' : ''; ?>
						</div>
						<div class="sub_title maninMenu" style="margin-left:140px">
							<?php
							$user_type = $_SESSION['user_type'] ?? '';
							if (isset($_SESSION["username"])) {
								$username = $_SESSION["username"];
								if (isset($_SESSION["offid"]) && $_SESSION["offid"] != '0' && $_SESSION['user_type'] == 'hqc_office') {
									$offid = $_SESSION["offid"];
									$user_type = "hqc_office";
									$user_permissions = array('clients_actions');
									$user_clients = array();
									$user_options = array();
									if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='$offid'")) {
										$user_clients = explode(',', $office['clients']);
										if (trim($office['options']) != '' && is_array(json_decode($office['options'], true))) {
											$user_options = json_decode($office['options'], true);
										}
									}
								} else {
									if (isset($_SESSION["comemid"])) {
										$clid = $_SESSION["comemid"];
										$user_type = "committee_member";
									} elseif (isset($_SESSION["clid"])) {
										$clid = $_SESSION["clid"];
										$user_type = "client";
									} else {
										$offid = 0;
										$uid = $_SESSION["uid"];
									}
									if ($user_type != "committee_member") {
										if (file_exists("$prog_path/hqc_users/$username.usr.php")) {
											include "$prog_path/hqc_users/$username.usr.php";
										}
										if (file_exists("$prog_path/hqc_users/admin_users.inc.php")) {
											include "$prog_path/hqc_users/admin_users.inc.php";
											$admin_users = json_decode($admin_users, true);
										}
									}
								}
								include "$prog_path/menu.inc.php";
								if (function_exists('logOutLink')) { ?>
									<script>
										jQuery("span#logOutLink").html('<?php logOutLink(); ?>');
									</script>
							<?php };
							} else {
								$ttl = ""; // "Register a company";
							}
							?>
						</div>
						<div>
							<div style="float:right;margin:10px 20px;display:<?php echo isset($_SESSION['username']) && $_SESSION['user_type'] == 'admin' ? '' : 'none'; ?>" class="info" id="adminInfo">
								<?php
								if (isset($inc) && $inc != 'register') {
									echo '<span style="color:green;padding:5px 10px"></span>';
								}
								?>
							</div>
							<?php if (isset($_SESSION['username'])) { ?>
								<div class="page_title" id="page_title" style="padding-left:140px;"><?php echo $ttl; ?></div>
							<?php }; ?>
						</div>
					</div>
				</div>
				<div id="container" style="position:relative; max-width:1400px; margin:0 auto;overflow:hidden;background:#FFF;margin-top:80px">
					<div style="padding: 1px;" class="pageContent">
						<div class="pageInclude" style="overflow:auto;<?php echo is_mobile() ? 'padding:40px 10px' : ''; ?>">
							<?php if ($isAdmin == true or $_SERVER['REMOTE_ADDR'] == '94.157.181.17') {
								echo '<span style="color:grey">'. __DIR__.$_SERVER['REQUEST_URI'] .'</span>' ;
							}; ?>
							<?php if (strstr($_SERVER['HTTP_HOST'], 'test.iidc.eu')) { ?>
								<h2 style="margin: 0px;color: brown;">TEST SITE</h2>
							<?php }; ?>
							<div id="pageDataHolder">
								<?php
								$cur_dir = basename(getcwd());
								if (isset($_SESSION['updatpasswordx']) && $_SESSION['user_role'] != 'super_admin' && !isset($_SESSION['logedAsClient']) && !isset($_SESSION['suid']) && !isset($_SESSION['passwordUpdated'])) {
									include $prog_path . '/user/user_password.inc.php';
								} else {
									if (isset($inc) and trim($inc) != '' && file_exists(getcwd() . "/$inc.inc.php")) {
										include getcwd() . "/$inc.inc.php";
									} elseif (file_exists(getcwd() . '/' . basename(getcwd()) . ".inc.php")) {
										include(getcwd() . '/' . basename(getcwd()) . ".inc.php");
									} elseif (isset($inc) and trim($inc) != '') {
										echo 'file ' . $inc . '.inc.php not found';
									} else {
										echo ' file not found';
									}
								}
								?>
							</div>
						</div>
						<div style="position:fixed;bottom:0px;width:100%">
							<div id="footer">&copy <?php echo date("Y"); ?> IIDC GmbH all rights reserved</div>
						</div>
						<script type="text/javascript">
							function set_session_url(act, title, url) {
								tabKey = 'user_url';
								if (act == 'new')
									tabUrl = '/admin/'
								else if (typeof url != 'undefined')
									tabUrl = url
								else
									tabUrl = ('<?php echo $_SERVER['REQUEST_URI']; ?>');
								tabNumber = Date.now();
								if (top.location.search && top.location.search.indexOf('?tb=') > -1)
									tabID = top.location.search.replace('?tb=', '')
								else
									tabID = '';
								if (act == 'new' || act == 'url')
									tabKey = 'tb_url' + tabNumber
								else if (act == 'goBack')
									tabKey = 'goBack_url' + tabID
								else if (tabID != '')
									tabKey = 'tb_url' + top.location.search.replace('?tb=', '')
								if (typeof title != 'undefined')
									tabUrl += '&ttl=' + title
								jQuery.post('/session.php', {
									'act': 'set',
									key: tabKey,
									url: tabUrl
								}).done(function(data) {
									if (data.trim() != '') {
										if (data.indexOf('error') == -1 && (act == 'new' || act == 'url')) {
											window.open("/?tb=" + tabNumber);
										}
									}
								})
							}
							set_session_url('set');
						</script>
					<?php
					if (isset($_SESSION['user_type']))
						$_SESSION["username"]	= $username;
					if (isset($clid) and $user_type == "client")
						$_SESSION["clid"]	= $clid;
					elseif (isset($uid))
						$_SESSION["uid"]	= $uid;
				} else {
					include "login.inc.php";
				}
					?>
					<script>
						function reload_content() {
							<?php
							$vars = array();
							foreach ($_GET as $key => $value) {
								if ($key != 'inc')
									$vars[] = $key . ':' . '"' . $value . '"';
							};
							?>
							<?php if (isset($inc) and is_array($vars)) { ?>
								jQuery.post('<?php echo $prog_www; ?>/load_content.php', {
									inc: '<?php echo str_replace('\\', '/', getcwd()) . "/$inc.inc.php"; ?>',
									<?php echo implode(', ', $vars); ?>
								}).done(function(data) {
									if (data.trim().length > 0) {
										jQuery("#pageDataHolder").html(data);
										do_document_ready();
										$(".ui-dialog-content").dialog("close");
									}
								});
							<?php }; ?>
						}
						<?php if (isset($_SESSION["username"])) { ?>
							//checkEmails();
							get_notifications();
						<?php }; ?>
						//reload_content();
					</script>
				<?php }; ?>
</body>

</html>