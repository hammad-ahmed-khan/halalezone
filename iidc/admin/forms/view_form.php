<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halal Quality Control | Control Office of Halal Slaughtering</title>
    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <link rel="icon" type="image/png" href="/images/small-logo.png">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo css_url; ?>/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script type="text/javascript" src="<?php echo hqc_url; ?>/js-css/js/tinymce/tinymce.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo css_url; ?>/style.css?tm=<?php echo time(); ?>">
    <script type="text/javascript" src="<?php echo hqc_url; ?>/js-css/js/tools.js"></script>
    <script type="text/javascript" src="<?php echo hqc_url; ?>/js-css/js/post-form.js"></script>
    <script type="text/javascript" src="<?php echo shared_url; ?>/documents/pre-uploaded.js"></script>
    <?php
    if (isset($_SESSION['admin'])) {
        $theme_colors_off = true;
    }

    function get_theme_color($menuFile)
    {
        $menuJson = trim(preg_replace('/\<?(.*?)\?>/s', '', file_get_contents($menuFile)));
        if (trim($menuJson) != '' && is_array(json_decode($menuJson, true))) {
            $thisMenu = json_decode($menuJson, true);
            if (isset($thisMenu['theme-color']))
                return $thisMenu['theme-color'];
        }
    }
    if (!isset($theme_colors_off) and isset($_USER['color']) and trim($_USER['color']) != '')
        $colors[cur_dir] = $_USER['color'];

    if (!isset($colors[cur_dir])) {
        $menuFile = cur_path . '/' . cur_dir . '.menu.php';
        if (file_exists($menuFile)) {
            $colors[cur_dir] = get_theme_color($menuFile);
        }
    }

    if (!isset($colors[cur_dir]) && defined('user_path')) {
        $menuFile = user_path . '/' . basename(user_path) . '.menu.php';
        if (file_exists($menuFile)) {
            $colors[cur_dir] = get_theme_color($menuFile);
        }
    }

    $theme_color = (isset($colors[cur_dir])) ? $colors[cur_dir] : '#02524f';

    function convert_color($prc = 1)
    {
        global $theme_color;
        $theme_color = str_replace('#', '', $theme_color);
        $split = str_split($theme_color, 2);
        $r = round(((255 - hexdec($split[0])) * $prc) + hexdec($split[0]));
        $g = (((255 - hexdec($split[1])) * $prc) + hexdec($split[1]));
        $b = (((255 - hexdec($split[2])) * $prc) + hexdec($split[2]));

        return "rgb(" . $r . ", " . $g . ", " . $b . ")";
    }
    ?>
    <style>
        :root {
            --color100: <?php echo $theme_color . ";\n";
                        $per = 0;
                        while ($per < .9) {
                            echo "--color" . (90 - ($per * 100)) . ":" . convert_color($per * 1.25) . ";\n";
                            $per = $per + 0.05;
                        }
                        ?>
        }

        span.preUploadedDocs {
            background: beige;
            padding: 10px;
            margin: auto 10px;
            cursor: pointer;
        }
    </style>
    <?php if (isset($_SESSION["username"])) { ?>
        <script type="text/javascript">
            var hqc_url = "<?php echo hqc_url  ?>";
            var hqc_path = "<?php echo hqc_path; ?>";
            var cur_dir = "<?php echo cur_dir; ?>";
            var cur_url = '<?php echo cur_url; ?>';
            var cur_path = '<?php echo cur_path; ?>';
            var act = "<?php echo isset($_GET['act']) ? $_GET['act'] : ''; ?>";
            <?php if (isset($_GET['inc'])) { ?>
                var inc = "<?php echo $_GET['inc']; ?>";
            <?php }; ?>
            var request_url = "<?php echo strstr($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'] : cur_url . (cur_url != '/' ? '/' : ''); ?>";
            <?php if (isset($_SESSION['user_type'])) { ?>
                var userType = "<?php echo $_SESSION['user_type']; ?>";
            <?php }; ?>
            <?php if (isset($_SESSION['clid'])) { ?>
                var clid = "<?php echo $_SESSION['clid']; ?>";
            <?php }; ?>

            function showThemeColors() {
                jQuery("#themeColor").toggle();
            }

            function clearScreen() {
                jQuery("#themeColor").css("display", "none");
            }
        </script>
    <?php }; ?>
</head>
<body style="background: white !important;">
    <form>
        <input type="hidden" value="insert" name="act" />
        <?php
        include dirname(__FILE__) . "/forms.class.php";
        if (isset($_REQUEST['the_form'])) {
            $_REQUEST['the_form'] = str_replace('<div></div>', '<br/>', $_REQUEST['the_form']);
            if (isset($_REQUEST['foid']))
                unset($_REQUEST['foid'])
        ?>
            <div style="padding:20px" id="formHolder">
                <?php
                if (isset($_USER['clid']))
                    $clid = $_USER['clid'];
                else
                    $clid = 5510;
                if ($client = get_client($clid))
                    $_REQUEST = $_REQUEST + $client;
                ?>
                <?php echo $amform->get_form(null, $_REQUEST, 'pdf'); ?>
            </div>
        <?php
        } elseif (isset($_REQUEST['foid'])) {
        ?>
            <div style="padding:20px" id="formHolder">
                <?php echo $amform->get_form($_REQUEST['foid'], isset($_GET['clid']) ? array('clid' => $_GET['clid']) : null, 'pdf'); ?>
            </div>
        <?php }; ?>
    </form>
</body>