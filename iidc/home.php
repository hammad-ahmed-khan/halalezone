<?php
//show php errors
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
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
include_once dirname(__FILE__) .'/../config/config.php';
include_once dirname(__FILE__) .'/../classes/users.php';
include_once dirname(__FILE__) .'/../includes/func.php';

extract($_REQUEST);
$cur_www = str_replace($_SERVER['DOCUMENT_ROOT'], "", getcwd());
$cur_path = getcwd();
if (isset($_GET['ref'])) {
	$_SESSION['ref'] = $_SERVER['REQUEST_URI'];
}

$is_login_page = false;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>IIDC</title>
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<link rel="icon" type="image/png" href="/iidc/images/small-logo.png">
	<link href="<?php echo $prog_www; ?>/fonts/google-fonts.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/style.css?vr=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $prog_www; ?>/fonts/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="/iidc//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
	 
	 
	    <?php include_once( dirname(__FILE__) .'/../pages/header.php');?>


</head>
<body>
<?php 
$currentNav = "certs";
include_once( dirname(__FILE__) .'/../pages/navigation.php');?>
 <nav class="navbar navbar-secondary navbar-light navbar-static-top" role="navigation">
    <div class="container-fluid">
        <!-- HQC Brand on Left -->
  <div class="container-fluid">
       

        <!-- Centered Menu -->
        <div class="collapse navbar-collapse" id="hqxMenu-collapse">
            <ul class="nav navbar-nav" id="hqxMenu">
                
                <!-- Home -->

                <!-- DMC Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        DMC <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/iidc/admin/committee/?inc=committee">Decision committee members</a></li>
                        <li><a href="/iidc/committee/index.php?inc=schedule_committee">Schedule a meeting</a></li>
                        <li><a href="/iidc/committee/">Scheduled meetings</a></li>
                        <li><a href="/iidc/committee/?status=approved">Decision history</a></li>
                        <li><a href="/iidc/guidelines/">Guidelines</a></li>
                    </ul>
                </li>

                <!-- Certificates Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Certificates <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/iidc/admin/?inc=certificates&amp;tp=a&amp;offid=0">Slaughtering Certificate</a></li>
                        <li><a href="/iidc/certificates/annual/?inc=certificates">Annual Certificates</a></li>
                    </ul>
                </li>

                <!-- Setups Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Setups <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                         <li class="dropdown-header">Invoice Settings</li>
                        <li><a href="/iidc/invoices/?inc=predefined_prices">Predefined prices</a></li>
                        <li><a href="/iidc/invoices/reminders/?inc=reminders">Payment terms &amp; reminders</a></li>
                        <li><a href="/iidc/admin/?inc=invoice_template">Email-messages &amp; Templates</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">System Settings</li>
                         <li><a href="/iidc/admin/?inc=pdf_protection">PDF files protection</a></li>
                        <li><a href="/iidc/offices/admin/?inc=offices">Our offices</a></li>
                        <li><a href="/iidc/offices/admin/signatories/?inc=signatories">Certificates signatories</a></li>
                        <li><a href="/iidc/offices/admin/index.php?inc=halal_standards&amp;offid=0">Halal standards</a></li>
                    </ul>
                </li>

                <!-- Invoices Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Invoices <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">View Invoices</li>
                        <li><a href="/iidc/invoices/?show=all">All Invoices</a></li>
                        <li><a href="/iidc/invoices/?show=paid">Paid Invoices</a></li>
                        <li><a href="/iidc/invoices/?show=unpaid">Unpaid Invoices</a></li>
                        <li><a href="/iidc/invoices/?show=overdue">Over due</a></li>
                        <li><a href="/iidc/invoices/?show=credit">Credit notes</a></li>
                        <li><a href="/iidc/invoices/?show=credited">Credited invoices</a></li>
                        <li><a href="/iidc/invoices/export/index.php">Export invoices</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">Create Invoices</li>
                        <li><a href="/iidc/invoices/index.php?inc=create_general_invoice">Create invoice</a></li>
                        <li><a href="/iidc/invoices/index.php?inc=create_credit_note">Create credit note</a></li>
                    </ul>
                </li>
 

            </ul>
        </div>
    </div>
</nav>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
				<?php
				$cur_dir = basename(getcwd());
				if (isset($_SESSION['updatpasswordx']) && $_SESSION['user_role'] != 'super_admin' && !isset($_SESSION['logedAsClient']) && !isset($_SESSION['suid']) && !isset($_SESSION['passwordUpdated'])) {
					//include $prog_path . '/user/user_password.inc.php';
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
	</div>
</div>
 <?php include_once(dirname(__FILE__) .'/../pages/footer.php');?>
                 <script src="/js/bootstrap-datepicker.min.js"></script>

</body>

</html>