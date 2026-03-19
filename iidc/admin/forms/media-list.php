<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
if ($_REQUEST['type'] == 'imgs')
    $dir = data_path . '/offices/' . $_SESSION['offid'] . '/images/uploads';
else
    $dir = data_path . '/offices/' . $_SESSION['offid'] . '/documents/uploads';

if (!is_dir($dir))
    mkdir($dir, 0777, true);

if ($files = get_dir_contents($dir, 'file') and count($files) > 0) {
    $mediaSr = 0;
    $docSrnr = 1;  ?>
    <style>
        #mediaHolder {
            list-style: none;
            margin: 0;
            padding: 0;
            height: 220px;
            overflow: auto;
        }

        .imgContainer {
            display: inline-block;
            width: 125px;
            height: 125px;
            overflow: hidden;
            margin: 1px;
            border: 1px solid #ccc;
            padding: 5px;
            position: relative;
        }

        .imgContainer img {
            width: 100%;
            height: 100%;
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
    </style>
    <script>
        function doRemoveThisFile(id, file) {
            $.post('media-save.php', {
                act: 'deleteFile',
                file: file
            }, function(data) {
                if (data == 'deleted') {
                    jQuery(id).remove();
                } else {
                    alert_message(data);
                }
            });
        }

        function removeThisFile(obj, file, file_name) {
            id = jQuery(obj).parents('li').prop('id');
            alert_confirm('Delete' + file_name + '?', "doRemoveThisFile('#" + id + "','" + file + "')")
        }

        function insertThisDoc(doc, type) {
            editor = tinymce.activeEditor;
            var alt = doc.split('/').pop().split('.')[0].replace(/_|-/g,' ');
            if (type == 'img') {
                //create image element
                var img = document.createElement("IMG");
                img.setAttribute("src", doc);
                imgWidth = img.width;
                imgHeight = img.height;
                editor.insertContent('<img src="' + doc + '" alt="' + alt + '" width="' + imgWidth + '" height="' + imgHeight + '"/>');

            } else {
                editor.insertContent('<a href="' + doc + '" target="_blank">' + alt + '</a>');
            }
            closePopup();
        }
    </script>
    <ol id="mediaHolder" class="<?php echo ($_REQUEST['type'] == 'docs') ? 'alternateOn' : ''; ?>">
        <?php foreach ($files as $file) { ?>
            <li <?php echo ($_REQUEST['type'] == 'imgs') ? 'class="imgContainer"' : 'style="padding:5px"'; ?> id="<?php echo $_REQUEST['type'] . $mediaSr++; ?>">
                <?php if ($_REQUEST['type'] == 'imgs') { ?>
                    <img src="/images/blank.png" title="<?php echo $file['file_name']; ?>" style="background:url(<?php echo $file['url']; ?>)" />
                    <span style="position:absolute;bottom:5px;right:15px;">
                        <i class="far fa-trash-alt" onclick="removeThisFile(this,'<?php echo $file['file']; ?>','<?php echo $file['file_name']; ?>');"></i> </span>

                    <span style="position:absolute;bottom:5px;left:15px;">
                        <i class="fas fa-angle-double-down" onclick="insertThisDoc('<?php echo $file['url']; ?>','img');"></i> </span>
                <?php } else { ?>
                    <span style="position:absolute;right:25px;">
                        <i class="far fa-trash-alt" onclick="removeThisFile(this,'<?php echo $file['file']; ?>','<?php echo $file['file_name']; ?>');"></i> </span>
                    <span style="position:absolute;right:65px;">
                        <i class="fas fa-angle-double-down" onclick="insertThisDoc('<?php echo $file['url']; ?>','doc');"></i> </span>
                    <?php echo $docSrnr++; ?> - <?php echo $file['file_name']; ?>
                <?php } ?>
            </li>
        <?php }; ?>
    </ol>
<?php
}
?>