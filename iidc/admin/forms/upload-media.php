<?php
if (!defined("__HQC__")) {
    exit();
};
?>
<style>
    .tabs,
    .uploadDocsFiles {
        width: 600px !important;
    }

    .tab-content {
        height: 300px;
    }

    .uploadDocFileHolder {
        position: absolute;
        right: 10px;
        top: 20px;
    }

    .uploadDocFile {
        width: 180px;
        font-size: 14px;
        padding: 2px !important;
    }
</style>
<script>
    function uploadMediaFiles(obj) {
        if (obj.value.trim() != '') {
            var type = jQuery(obj).attr('name');
            var form_data = new FormData();
            var totalFiles = obj.files.length;
            for (var index = 0; index < totalFiles; index++) {
                form_data.append("files[]", obj.files[index]);
            }
            form_data.append('act', 'uploadFile');
            form_data.append('type', type);
            if (type == 'docs') {
                if (jQuery("#docsCheckbox").is(":checked")) {
                    form_data.append('replace', '1');
                } else {
                    form_data.append('replace', '0');
                }
            } else if (type == 'imgs') {
                if (jQuery("#imgsCheckbox").is(":checked")) {
                    form_data.append('replace', '1');
                } else {
                    form_data.append('replace', '0');
                }
            }
            jQuery.ajax({
                url: 'media-save.php', // point to server-side PHP script
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(data) {
                    jQuery(obj).val('');
                    if (data == "success") {
                        jQuery("#documentsListHolder").load("media-list.php?type=docs");
                        jQuery("#imagesListHolder").load("media-list.php?type=imgs");
                    } else {
                        alert_message(data);
                    }
                }
            });
        }
    }
</script>
<div style="padding:0px 20px;">

    <ul class="tabs" data-tabs="uploadDocsFiles">
        <li data-id="uploadedDocuments" class="active">Documents</li>
        <li data-id="uploadedImages">Images</li>
    </ul>
    <div style="width:500px;position:relative;">
        <div class="uploadDocsFiles tab-content active" id="uploadedDocuments">
            <div><span class="uploadDocFileHolder"><label><input type="checkbox" id="docsCheckbox" />Replace document(s)</label> <input multiple type="file" name="docs" onchange="uploadMediaFiles(this)" accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint,text/plain, application/pdf" class="uploadDocFile" /> </span>
                <h4>Uploaded Documents</h4>
            </div>
            <div id="documentsListHolder"></div>
        </div>
        <div class="uploadDocsFiles tab-content" id="uploadedImages">
            <div><span class="uploadDocFileHolder"><label><input type="checkbox" id="imgsCheckbox" />Replace image(s)</label> <input multiple type="file" name="imgs" onchange="uploadMediaFiles(this)" accept="image/*" class="uploadDocFile" /></span>
                <h4>Uploaded Images</h4>
            </div>
            <div id="imagesListHolder"></div>
        </div>
    </div>
</div>
<script>
    jQuery("#documentsListHolder").load("media-list.php?type=docs");
    jQuery("#imagesListHolder").load("media-list.php?type=imgs");
</script>