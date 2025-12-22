<?php
if (!defined("_HQC_")) {
    exit();
};
?>
<style>
    #documentsList li {
        list-style: decimal;
        padding: 5px;
        margin: 0;
    }
</style>
<script>
    $("#page_title").html("Documentation");

    //function to view file using Google Docs Viewer
    function view_file(file) {
        window.open("view_document.php?file=" + file, "_blank");
    }

    async function deleteDocument(file, obj) {
        await confirm_message("Are you sure you want to delete this document?<br/><br/><span style='color:red'>" + file + '</span>');
        $.post("documents_save.php", {
            act: 'delete_document',
            file: file
        }, function(data) {
            if (data == 'success') {
                location.reload();
            } else {
                alert(data);
            }
        });
    }
</script>
<h2 style="text-align: center;">Documentation</h2>
<?php
//function to remove special characters from file name
function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\.\-]/', '', $string); // Removes special chars.
    $string = str_replace('-', ' ', $string); // Replaces all spaces with hyphens.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

$filesDir = $prog_path . "/data/DMC/documents";
if (!is_dir($filesDir)) {
    mkdir($filesDir, 0777, true);
};
$files = scandir($filesDir);
$files = array_diff($files, array('.', '..'));
if (count($files) > 0) { ?>
    <ul class='alternateOn' id='documentsList'>
        <?php foreach ($files as $file) { ?>
            <li style="position: relative;">
                <span style="position: absolute;right: 10px;top: 5px;">
                <i class="fas fa-download" style="color:blue" onclick="window.open('download_document.php?file=<?php echo trim($file); ?>','_blank')"></i>
                    <?php if ($_SESSION['super_admin'] == 'yes') { ?>
                        <i class="fa fa-trash-alt" style="color:red" onclick="deleteDocument('<?php echo trim($file); ?>')"></i>
                    <?php } ?>
                </span>
                <a href="javascript:view_file('<?php echo trim($file); ?>',this)"><?php echo clean($file); ?></a>
            </li>
        <?php } ?>
    </ul>
<?php }; ?>

<?php if ($_SESSION['super_admin'] == 'yes') { ?>
    <div style="text-align: center;">
        <form id="upload_form" action="documents_save.php" enctype="multipart/form-data" method="post" onsubmit="return post_this_form(this)">
            <input type="hidden" name="act" value="upload_files" />
            <b>Upload document(s):* </b>
            <input type="file" name="files[]" multiple data-required="yes" /> <input type="submit" value="Upload" />
        </form>
    </div>
<?php }; ?>