<?php
if (!session_id()) {
    session_start();
}

include $_SESSION['hqc_path'] . '/load.inc.php';


//TODO: continue save the signature according to the user type
$users_type = array('admin' => '/offices/0/signatures/', 'office' => '/offid/' . $_POST['uid'] . '/signatures/', 'client' => '/clients/' . $_POST['uid'] . '/signatures/', 'auditor' => '/offices/0/signatures/');

if (isset($_POST['save_signature']) && $_POST['save_signature'] == 'yes')
    $image_file = $users_type[$_POST['user_type']] . $_POST['uid'] . '_signature';
else
    $image_file = $users_type[$_POST['user_type']] . $_POST['uid'] . '_' . time() . '_signature';

if (!is_dir(dirname(data_path . $image_file)))
    mkdir(dirname(data_path . $image_file), 0777, true);

if (isset($_POST) && isset($_POST['act']) && $_POST['act'] == 'upload_signature') {
    if (isset($_FILES) && isset($_FILES["signature"]) and trim($_FILES["signature"]['name']) != '') {
        $imgExt = strtolower(pathinfo($_FILES["signature"]['name'], PATHINFO_EXTENSION));
        $image_file .= '.' . $imgExt;
        $tmp_name = $_FILES["signature"]["tmp_name"];
        if (move_uploaded_file($tmp_name, data_path . "/$image_file")) {
?>
            <script>
                parent.document.getElementById('<?php echo $_POST['signature_input']; ?>').value = '<?php echo $image_file; ?>';
                parent.document.getElementById('<?php echo $_POST['signature_holder']; ?>').innerHTML = '<img src="<?php echo data_url . $image_file . '?tm=' . time(); ?>" style="max-width: 250px; max-height: 250px;"/>';
                parent.document.getElementById('<?php echo $_POST['signature_holder']; ?>').style.border = '';
                parent.closePopup();
            </script>
<?php
        }
    }

    exit();
}

$image_file .= '.svg';

$image = fopen(data_path . $image_file, "w");
fwrite($image, $_POST['image']);
fclose($image);
echo "$image_file";
