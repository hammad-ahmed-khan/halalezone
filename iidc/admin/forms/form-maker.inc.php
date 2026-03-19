<?php
if (!defined("__HQC__")) {
    exit();
}
$form_meta = array();
$act = 'insert';
$form_options = array();

if (isset($_GET['foid']) and trim($_GET['foid']) != "" and is_numeric($_GET['foid'])) {
    $theForm = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid='$_GET[foid]'");
    $theDocxForm['form_id'] = $theForm['form_id'];
    $theDocxForm['revision'] = $theForm['revision'];
    $theDocxForm['form_name'] = $theForm['form_name'];
    $theDocxForm['remarks'] = $theForm['remarks'];
    if ($theForm['category'] == '0' or isset($_GET['cat']))
        $_GET['sandbox'] = 1;

    if (!isset($_GET['sandbox'])) {
        if (is_array(json_decode($theForm['form_meta'], true)))
            $form_meta = decode_json($theForm['form_meta']);
        else
            $form_meta = array();
    }
    $act = 'update';

    if (isset($_GET['act']) and $_GET['act'] == 'copy') {
        $theDocxForm['remarks'] = 'Copy';
        $act = 'insert';
        $new_version = true;
    };

    $request_message = array();
    if (trim($theForm['form_options']) != '') {
        if (!$form_options = json_decode(str_replace(array("\n", "\r"), '', $theForm['form_options']), true))
            $form_options = unserialize($theForm['form_options']);

        if (isset($form_options['submit_message']))
            $submit_message = $form_options['submit_message'];

        if (isset($form_options['request_message']))
            $request_message = $form_options['request_message'];

        if (isset($form_options['approved_message']))
            $approved_message = $form_options['approved_message'];

        if (isset($form_options['disapproved_message']))
            $disapproved_message = $form_options['disapproved_message'];

        if (isset($form_options['general_message']))
            $general_message = $form_options['general_message'];
    }
} else {
    if (isset($_GET['cat'])) {
        $_GET['sandbox'] = 1;
    }
}
?>
<style>
    div#tinyMceFormTabs span {
        padding: 5px 10px;
        border: 1px solid var(--color100);
        margin-right: 5px;
        text-transform: uppercase;
        font-size: 11px !important;
        background: var(--color20);
        cursor: pointer;
    }

    iframe#tinyMceFormEditElementIframe {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    #editBodyElementBtn {
        height: 20px !important;
        float: left;
        background: var(--color20);
        border: 1px solid var(--color100);
        line-height: 10px;
        padding: 0px 10px !important;
        text-transform: capitalize;
    }
</style>
<!-- monaco code editor -->
<script src="/js-css/js/monaco-editor/min/vs/loader.js"></script>
<script>
    require.config({
        paths: {
            vs: '/js-css/js/monaco-editor/min/vs'
        }
    });
</script>
<script type="text/javascript" src="<?php echo this_url(); ?>/js-css/form-maker.js?vr=<?php echo time(); ?>"></script>

<script type="text/javascript">
    function postTheForm(form) {

        jQuery("#css_js").val(window.styleEditor.getValue());
        jQuery("#include_php").val(window.phpEditor.getValue());
        <?php if (!isset($_GET['sandbox'])) { ?>
            jQuery("#pdf_css_js").val(window.pdfStyleEditor.getValue());
            jQuery("#pdf_include_php").val(window.pdfPhpEditor.getValue());
        <?php }; ?>
        return post_this_form(form);
    }

    function doAct(act) {
        if (tinymce.activeEditor.getContent().trim() == '') {
            alert_message('Nothing to view');
            return false;
        }
        if (act == 'save') {
            jQuery("#form_maker").attr('action', '<?php echo this_url(); ?>/form_save.php');
            jQuery("#form_maker").attr('target', '')
        }
        if (act == 'view') {
            loadPdf();
            jQuery("#form_maker").attr('action', '<?php echo this_url(); ?>/view_form.php')
            jQuery("#form_maker").attr('target', 'pdfIframe')
        }

        if (act == 'viewProduction') {
            jQuery("#form_maker").attr('action', '/applications/?inc=form&clid=5510')
            jQuery("#form_maker").attr('target', '_new')
        }

        jQuery("#form_maker").submit();
    }

    function viewProduction(obj) {
        jQuery("div#myClients").before('<div id="myClientsList" style="display:inline-block"></div>');
        jQuery("div#myClients").css("display", "none")
        jQuery("div#myClientsList").load("clients.php");
    }

    function change_form_type(ft) {
        if (ft == '')
            return false;
        jQuery("#theFormDiv,#formTemplateDiv,#attachedFiles,#htmlScriptsTab,#pdfScriptsTab,#html_scripts,#pdf_scripts").css("display", "block");
        jQuery("#htmlScriptsTab").trigger("click");
        if (ft == 'download_document') {
            jQuery(".startMessages").trigger("click");
            jQuery("#theFormDiv,#formTemplateDiv,#htmlScriptsTab,#pdfScriptsTab,#html_scripts,#pdf_scripts").css("display", "none");
        } else if (ft == 'form') {
            jQuery("#attachedFiles").css("display", "none");
        }

    }
    var table_columns = '';

    function formInserted(foid) {
        jQuery("#form_maker").prepend('<input type="hidden" name="foid" value="'+foid+'" />')
        jQuery("#form_maker input[name=act]").val('update')
    }
</script>

<form action="<?php echo this_url(); ?>/form_save.php" method="post" enctype="multipart/form-data" onsubmit="return postTheForm(this)" name="form_maker" id="form_maker" target="_new" data-error="Fields with (*) are quired." autocomplete="off" role="presentation">
    <input type="hidden" name="act" value="<?php echo $act; ?>" />
    <?php if (isset($theForm)) { ?>
        <?php if (!isset($new_version)) { ?>
            <input type="hidden" name="foid" value="<?php echo $theForm['foid']; ?>" />
    <?php };
    } ?>
    <?php if (isset($_GET['sandbox'])) { ?>
        <input type="hidden" name="category" value="0" />
    <?php }; ?>
    <input type="hidden" name="offid" value="<?php echo isset($_SESSION['offid']) ? $_SESSION['offid'] : 0; ?>" />
    <input type="hidden" name="doExample" id="doExample" value="no" />
    <div style="overflow:hidden">
        <h4 style="margin:0px"><?php _e('Add/Edit Form'); ?> <?php if (isset($new_version)) {
                                                                    _e(' - New Revision');
                                                                }; ?></h4>

        <div id="formDiv" class="formMetas" style="clear:both">
            <input type="text" placeholder="<?php _e('Form name'); ?>*" name="form_name" value="<?php echo isset($theDocxForm) ? $theDocxForm['form_name'] : ''; ?>" data-required='yes' style="width:30%" />
            <?php if (!isset($_GET['sandbox'])) { ?>
                <input type="text" name="form_id" style="width:80px;" value="<?php echo isset($theDocxForm) ? $theDocxForm['form_id'] : ''; ?>" placeholder="<?php _e('Form ID'); ?>*" data-required='yes' />
            <?php }; ?>

            <?php
            if ($categories = get_hqc_options('form_categories')) {
                if (is_array(json_decode($categories, true))) {
                    $categories = json_decode($categories, true);
                    $categories['0'] =  'Form';
                }
            }
            ?>
            <select name="category" size="1" data-required="yes">
                <option value="">Select category</option>
                <?php foreach ($categories as $key => $value) { ?>
                    <option value="<?php echo $key; ?>" <?php echo (isset($_GET['cat']) && $_GET['cat'] == $key or isset($theForm['category']) && $theForm['category'] == $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                <?php }; ?>
            </select>
            <?php if (!isset($_GET['sandbox'])) { ?>
                <a href="<?php echo this_url(); ?>/form-category.php" class="dropdown"><i class="fas fa-cog" title="Application / form category"></i></a>
                <input type="text" name="revision" value="<?php echo isset($theDocxForm) ? $theDocxForm['revision'] : ''; ?>" placeholder="<?php _e('Revision Nr.'); ?>*" data-required='yes' style="width:40px" />
                <input type="text" class="date" name="form_meta[revision_date]" value="<?php echo isset($form_meta['revision_date']) ? $form_meta['revision_date'] : date('d/m/Y'); ?>" placeholder="<?php _e('Revision Date'); ?>*" data-required='yes' style="width:90px" />
                <input type="text" class="date" name="form_meta[modification_date]" value="<?php echo isset($form_meta['modification_date']) ? $form_meta['modification_date'] : date('d/m/Y'); ?>" placeholder="<?php _e('Modification Date'); ?>*" data-required='yes' style="width:90px" />
                <input type="text" placeholder="<?php _e('Remarks'); ?>" name="remarks" value="<?php echo isset($theDocxForm) ? $theDocxForm['remarks'] : ''; ?>" style="width:190px" />
                <select size="1" name="form_type" style="width:130px" data-required="" onchange="change_form_type(this.value)">
                    <option value="">Select Form Type</option>
                    <option value="form" <?php echo (isset($theForm['form_type']) && $theForm['form_type'] == 'form') ? 'selected' : ''; ?>>Fill the form</option>
                    <option value="download_document" <?php echo (isset($theForm['form_type']) && $theForm['form_type'] == 'download_document') ? 'selected' : ''; ?>>Download document(s)</option>
                    <option value="both" <?php echo (isset($theForm['form_type']) && $theForm['form_type'] == 'both') ? 'selected' : ''; ?>>Fill & download document(s)</option>
                </select>
            <?php }; ?>
        </div>
    </div>
    <div>
        <div style="padding:2px;position:relative;height:620px;box-sizing:border-box" id="theFormDiv">
            <textarea class="tinymce_template" id="the_form" name="the_form" style="height:500px;width:100%" autocomplete="false">
<?php
if (isset($theForm)) {
    $theForm['the_form']  = str_replace(
        array(
            'type="hidden"',
            '<form ',
            '</form>',
            '<input',
            'selectedElForEdit',
            'selectedElEdited',
            'class=""'
        ),
        array(
            'type="hidden-input"',
            '<input type="form-start" placeholder="form" ',
            '<input type="form-end" placeholder="/form">',
            '<input autocomplete="none"',
            '',
            '',
            ''
        ),
        $theForm['the_form']
    );

    if (preg_match_all("/\<input (.*?)\>|\<select (.*?)\/select>/is", $theForm['the_form'], $matches, PREG_SET_ORDER)) {
        foreach ($matches as $key => $shortMatch) {
            $codeContent = $shortMatch[0];
            $theInput = str_replace(array('[span class="phpcode"]', "[/span]"), '', $codeContent);
            $theForm['the_form'] = str_replace($codeContent, $theInput, $theForm['the_form']);
        }
    }
    $theForm['the_form'] = str_replace(array('[span class="phpcode"]', "[/span]"), array('<span class="phpcode">', "</span>"), $theForm['the_form']);

    echo str_replace('</textarea>', htmlspecialchars('</textarea>'), $theForm['the_form']);
} else {
    echo "";
}
?>
</textarea>
        </div>
        <?php if (!isset($_GET['sandbox'])) { ?>

            <div id="formTemplateDiv">
                <h3 style="background: var(--color30); padding: 5px 20px;margin: 0px;"><label>
                        <input type="checkbox" onclick="jQuery('#formTemplate').toggle()" <?php echo (isset($form_options['form_template']) && $form_options['form_template'] != '') ? 'checked' : ''; ?> name="form_options[form_template]"> Form Template</label></h3>
                <div id="formTemplate" style="display:<?php echo (isset($form_options['form_template']) && $form_options['form_template'] != '') ? 'block' : 'none'; ?>">
                    <div style="padding:5px 20px;background:var(--color20);overflow:hidden">
                        <span style="float:right"><b>PDF template:</b> <input type="file" name="pdf_template" accept=".pdf" /></span>
                        <b>Template:</b> <select name="form_options[use_pdf_template]" size="1">
                            <option value="">Select template type</option>
                            <option value="pdf" <?php echo (isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == 'pdf') ? 'selected' : ''; ?> /> PDF template</option>

                            <option value="custom" <?php echo (isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == 'custom') ? 'selected' : ''; ?> /> Custom template</option>
                        </select>
                    </div>
                    <strong>Template header</strong><br />
                    <textarea name="form_header" id="form_header" class="tiny" style="height:200px"><?php echo isset($theForm) ? $theForm['form_header'] : ''; ?></textarea>
                    <strong>Template footer</strong><br />
                    <textarea name="form_footer" id="form_footer" class="tiny" style="height:200px"><?php echo isset($theForm) ? $theForm['form_footer'] : ''; ?></textarea>
                </div>
            </div>
            <div id="subFormMetas" style="background:var(--color20);padding:10px;box-sizing:border-box;overflow:hidden">
                <input type="text" name="form_meta[created_by]" value="<?php echo isset($form_meta['created_by']) ? $form_meta['created_by'] : ''; ?>" placeholder="<?php _e('Created by'); ?>" />
                <input type="text" name="form_meta[reviewed_by]" value="<?php echo isset($form_meta['reviewed_by']) ? $form_meta['reviewed_by'] : ''; ?>" placeholder="<?php _e('Reviewed by'); ?>" />
                <input type="text" name="form_meta[approved_by]" value="<?php echo isset($form_meta['approved_by']) ? $form_meta['approved_by'] : ''; ?>" placeholder="<?php _e('Approved by'); ?>" />
                <input type="text" name="form_meta[retention_period]" value="<?php echo isset($form_meta['retention_period']) ? $form_meta['retention_period'] : ''; ?>" placeholder="<?php _e('Retention Period'); ?>" />
            </div>
            <div id="attachedFiles" style="background:var(--color20);padding:10px;box-sizing:border-box;overflow:hidden;margin-top:20px">
                <div style="float:right;width:280px">
                    <strong> Application Documents location:</strong><br />
                    <select name="form_options[documents_location]" style="text-transform:capitalize;width:100%">
                        <option value="">Select location</option>
                        <?php
                        if ($doc_locations = $hqcdb->get_results("SELECT * FROM `hqc_files_directories` WHERE status = 'active' AND user_types='admin'")) {
                            foreach ($doc_locations as $location) { ?>
                                <option value="<?php echo $location['dirid']; ?>" <?php echo (isset($form_options['documents_location']) && $form_options['documents_location'] == $location['dirid']) ? 'selected' : ''; ?>><?php echo str_replace('-', ' ', $location['dir_path']); ?></option>
                        <?php }
                        }
                        ?>
                    </select><br />
                    <strong style="margin:10px 0px 0px;display:block">Client's Uploaded Documents location:</strong>
                    <select name="form_options[client_documents_location]" style="text-transform:capitalize;width:100%">
                        <option value="">Select location</option>
                        <?php
                        if ($doc_locations = $hqcdb->get_results("SELECT * FROM `hqc_files_directories` WHERE status = 'active' AND FIND_IN_SET('client',user_types)")) {
                            foreach ($doc_locations as $location) { ?>
                                <option value="<?php echo $location['dirid']; ?>" <?php echo (isset($form_options['client_documents_location']) && $form_options['client_documents_location'] == $location['dirid']) ? 'selected' : ''; ?>><?php echo str_replace('-', ' ', $location['dir_path']); ?></option>
                        <?php }
                        }
                        ?>
                    </select>
                </div>
                <div style="float:left">
                    <strong>Attach documents: </strong> <input type="file" value="choose documents" name="attached_documents[]" multiple accept=".doc,.docx,pdf,.xls,.xlsx" /><br />
                    <label><input type="checkbox" value="yes" name="form_options[replace_old_documents]" />Replace old documents</label>
                </div>
                <div>
                    <strong>Attached documents:</strong><br />
                    <?php if ($documents = $hqcdb->get_results("SELECT * FROM hqc_filestore WHERE parent = 'form' AND parent_id = '$_GET[foid]'")) { ?>
                        <ul>
                            <?php
                            foreach ($documents as $document) { ?>
                                <li><a href="/clients/documents/view_document.php?location=general-data&file=<?php echo $document['file_name']; ?>" target="pdfIframe"><?php echo $document['description']; ?></a></li>
                            <?php };
                            ?>
                        </ul>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            $default_message['from_name'] = '[office_name]';
            $default_message['from_email'] = '[office_email]';
            $default_message['subject'] = 'Message from [office_name]';
            $default_message['message'] = 'Dear Sir/Madam<br /><br />TO WASIM: write your message here<br /><br /><strong>[office_name]</strong><br />[office_address]<br /><br /><hr /><p>[email_footer]</p>';
            $default_message['bcc_email'] = 'info@' . str_replace(array('hqc.', 'hqc-loc.'), '', $_SERVER['HTTP_HOST']);
            ?>

            <!-- menu tabs-->
            <div id="optionTabs">
                <ul style="padding:0px">
                    <li data-tab="html_scripts" id="htmlScriptsTab" class="title active"><i class="fas fa-code"></i><?php _e('HTML'); ?></li>
                    <li data-tab="pdf_scripts" id="pdfScriptsTab"><i class="far fa-file-pdf"></i><?php _e('PDF'); ?></li>
                    <li data-tab="form_request" class="startMessages"><i class="far fa-envelope"></i><?php _e('Request to fill out the form'); ?></li>
                    <li data-tab="submit_message"><i class="far fa-envelope"></i><?php _e('After submitting / updating the form'); ?></li>
                    <li data-tab="approving_the_form"><i class="far fa-envelope"></i><?php _e('After approving the form'); ?></li>
                    <li data-tab="disapproving_the_form"><i class="far fa-envelope"></i><?php _e('After disapproving the form'); ?></li>
                    <li data-tab="general_message"><i class="far fa-envelope"></i><?php _e('General email message'); ?></li>
                </ul>
            </div>

            <script>
                jQuery("#optionTabs li").on("click", function() {
                    jQuery('#toolTabs li').css('display', 'none');
                    jQuery("#optionTabs li").removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery("#" + jQuery(this).attr('data-tab')).css('display', 'block')
                });

                function changeHtmlPhpWidth(dv) {
                    if (jQuery("." + dv).width() < 800) {
                        jQuery("." + dv).css({
                            'width': '100%'
                        });

                    } else {
                        jQuery("." + dv).css({
                            'width': '50%'
                        });
                    }
                }

                function copyText(id, obj) {
                    jQuery("#tablesAndColumns i").css('color', 'initial');
                    if (jQuery(id + " option:selected").val() == '')
                        return false;
                    var textarea = jQuery('<textarea />');
                    textarea.val(jQuery(id + " option:selected").text()).css({
                        visibility: 'none'
                    }).appendTo('body');
                    textarea.focus().select();
                    if (document.execCommand('copy')) {
                        textarea.remove();
                        jQuery(obj).css('color', 'green')
                        return true;
                    }
                }
            </script>
        <?php }; ?>
        <ul style="padding:0px;margin:0px;clear:both" id="toolTabs">
            <li id="html_scripts" style="position:relative">
                <div style="width:50%;float:left;padding:10px;" class="scrHolder">
                    <b class="title"><i class="fas fa-code"></i>Java scripts or styles</b>
                    <div style="position:relative;height:480px;overflow: hidden;border:1px solid var(--color50)" class="scrContent">
                        <div id="style_editor" style="height: 100%;"></div>
                    </div>
                    <textarea id="css_js" name="css_js" style="display:none"><?php echo isset($theForm) ? $theForm['css_js'] : ''; ?></textarea>
                    <div style="padding:0px 10px;clear:both">Javascript, style and php codes will be inserted before generating HTML online form</div>
                </div>
                <div style="width:50%;float:left;padding:10px" id="include_php_div" class="scrHolder">
                    <b class="title"><i class="fas fa-code"></i>PHP codes</b>
                    <div id="tablesAndColumns">
                        <select size="1" id="tablesTable" onchange="jQuery('#tableColumns').load('<?php echo this_url(); ?>/form_save.php?act=get_columns&table='+this.value)">
                            <option value="">Select table</option>
                            <?php
                            $tables = $hqcdb->get_tables();
                            foreach ($tables as $table) { ?>
                                <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
                            <?php } ?>
                        </select><i class="far fa-copy" onclick="copyText('#tablesTable',this)"></i>
                        <span id="tableColumns"></span>
                    </div>
                    <div style="position:relative;height:450px;overflow: hidden;border:1px solid var(--color50)" class="scrContent">
                        <pre id="php_editor"></pre>
                    </div>
                    <textarea id="include_php" name="include_php" style="display:none"><?php echo isset($theForm) ? $theForm['include_php'] : ''; ?></textarea>
                </div>
                <?php if (!isset($_GET['sandbox'])) { ?>
                    <i class="fas fa-expand-arrows-alt" onclick="changeHtmlPhpWidth('scrHolder')" style="margin: 10px;"></i>
                    <div style="padding:10px 10px 0 10px;clear:both">
                        <b><?php _e('Insert action'); ?></b><br />
                        Insert actions in list items, such as tables td. The action is json format and should contain page, id (jquery format), element as text (any html element) and or a function to be called ones. eg: <span style="color:#319331">{"page":"/clients/?ref=true","id":"#clientsList tr td:nth-child(5)","element":"&lt;a href='/clients/applications/application.inc.php?foid=\[foid]&clid=\[clid]&act=insert'>form name&lt;/a>","action":"get_status()"}</span><br />
                        <div>
                            <textarea style="width:49%;height: 100px;overflow:auto;padding:10px;margin-top:10px;" name="insert_actions" id="form_options_insert_actions" placeholder="Actions (JSON)"><?php echo isset($theForm['insert_actions']) ? $theForm['insert_actions'] : ''; ?></textarea>

                            <textarea style="width:49%;height: 100px;overflow:auto;padding:10px;margin-top:10px;" name="insert_functions" id="form_options_insert_functions" placeholder="Functions (PHP)"><?php echo isset($theForm['insert_functions']) ? $theForm['insert_functions'] : ''; ?></textarea>
                        </div>
                    </div>
                <?php }; ?>
            </li>
            <?php if (isset($_GET['sandbox'])) { ?>
        </ul>
    <?php } else { ?>
        <li id="pdf_scripts" style="display:none;position:relative">
            <div style="width:50%;float:left;padding:10px" class="pdfHolder">
                <b class="title"><i class="far fa-file-pdf"></i>PDF - style</b>
                <div style="position:relative;height:500px;overflow: hidden;border:1px solid var(--color50)">
                    <pre id="pdf_style_editor"></pre>
                </div>
                <textarea id="pdf_css_js" name="pdf_css_js" style="display:none"><?php echo isset($theForm) ? $theForm['pdf_css_js'] : ''; ?></textarea>
            </div>

            <div style="width:50%;float:left;padding:10px" class="pdfHolder">
                <b class="title"><i class="far fa-file-pdf"></i>PDF - PHP codes</b>
                <div style="position:relative;height:500px;overflow: hidden;border:1px solid var(--color50)">
                    <pre id="pdf_php_editor"></pre>
                </div>
                <textarea id="pdf_include_php" name="pdf_include_php" style="display:none"><?php echo (isset($theForm) and trim($theForm['pdf_include_php']) != '') ? $theForm['pdf_include_php'] : ''; ?></textarea>
            </div>
            <div style="padding:10px;clear:both">Style and php codes will be inserted before the form when generating the PDF file</div>
            <div style="padding:10px;">
                <label><input type="checkbox" name="form_options[download_as_pdf]" value="yes" <?php echo (isset($form_options['download_as_pdf'])) ? 'checked' : ''; ?> /> Save as PDF file.</label>
                <label><input type="checkbox" name="form_options[upload_files_enabled]" value="yes" <?php echo (isset($form_options['upload_files_enabled'])) ? 'checked' : ''; ?> /> Enable file(s) upload</label> <strong>Files location:</strong>
                <select name="form_options[upload_location]">
                    <option value="">Select location</option>
                    <?php
                    if ($locations = $hqcdb->get_results("SELECT * FROM `hqc_files_directories` WHERE status = 'active'")) {
                        foreach ($locations as $location) { ?>
                            <option value="<?php echo $location['dirid']; ?>" <?php echo (isset($form_options['upload_location']) && $form_options['upload_location'] == $location['dirid']) ? 'selected' : ''; ?>><?php echo $location['dir_path'] ?></option>
                    <?php }
                    }
                    ?>
                </select>
            </div>
            <i class="fas fa-expand-arrows-alt" onclick="changeHtmlPhpWidth('pdfHolder')"></i>
        </li>

        <!--request filling the form-->
        <li id="form_request" style="display:none">
            <b class="title"><i class="far fa-envelope"></i><?php _e('Request to fill out the form'); ?></b>
            <div style="width:40%;float:left;padding:10px;box-sizing: border-box;height: 550px;">
                <label style="margin:10px;"><input type="checkbox" name="request_message[online_message]" value="yes" <?php echo (isset($request_message['online_message'])) ? 'checked' : ''; ?> /> <b>Document process / history message</b></label>
                <textarea name="request_message[online_message_body]" id="request_message_online_message_body" style="height: 200px;" class="tiny"><?php echo (isset($request_message['online_message_body'])) ? $request_message['online_message_body'] : ''; ?></textarea>

                <label style="margin: 10px;"><input type="checkbox" name="request_message[online_notification]" value="yes" <?php echo (isset($request_message['online_notification'])) ? 'checked' : ''; ?> /> <b>Slide up notification message</b></label>
                <textarea name="request_message[online_notification_body]" id="online_notification_body" style="height: 160px;width:100%"><?php echo (isset($request_message['online_notification_body'])) ? $request_message['online_notification_body'] : ''; ?></textarea>
            </div>

            <div style="width:60%;float:left;">
                <div style="padding: 10px;box-sizing: border-box;">
                    <label style="margin:10px;"><input type="checkbox" name="request_message[send_email]" value="yes" <?php echo (isset($request_message['send_email'])) ? 'checked' : ''; ?> /> <b>Send Email message</b></label><br />
                    <table>
                        <tr>
                            <td style="white-space:nowrap"><?php _e('Sender name'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="request_message[from_name]" id="from_name" value="<?php echo (isset($request_message['from_name'])) ? $request_message['from_name'] : '[office_name]'; ?>" /></td>
                            <td style="white-space:nowrap"><?php _e('Reply address'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="request_message[from_email]" value="<?php echo (isset($request_message['from_email'])) ? $request_message['from_email'] : '[office_email]'; ?>" /></td>
                        </tr>
                        <tr>
                            <td style="white-space:nowrap">Email subject:</td>
                            <td colspan="3"> <input type="text" name="request_message[subject]" value="<?php echo (isset($request_message['subject'])) ? $request_message['subject'] : ''; ?>" /></td>
                        </tr>
                    </table>
                    <div>
                        <textarea style="width:100%;height:300px" name="request_message[message]" id="request_message_message" class="tiny"><?php echo (isset($request_message['message'])) ? $request_message['message'] : ''; ?></textarea>
                        <br />
                        <label><input type="checkbox" name="request_message[emailmeacopy]" value="yes" <?php echo (isset($request_message['emailmeacopy'])) ? 'checked' : ''; ?> /><b>Email copy to me to:</b></label> <input type="text" name="request_message[bcc_email]" style="width:50%" value="<?php echo (isset($request_message['bcc_email']) and trim($request_message['bcc_email']) != '') ? $request_message['bcc_email'] : $default_message['bcc_email']; ?>" />
                    </div>
                </div>
            </div>
        </li>
        <!-- // end request -->

        <!-- Email Message -->
        <?php
                if (!isset($submit_message)) {
                    $submit_message = array();
                    $submit_message['msgid'] = '';
                    $submit_message['email_type'] = 'custom';
                    $submit_message['message'] = '<p>Dear [contact_name]</p>
<p>your message here</p>
<p>kind regards</p>
<p>On behalf of [my_company]&nbsp;<br />[my_contact]</p>
<p>[my_address_full]</p>
<hr />
<p>[email_footer]</p>';
                }
        ?>
        <li id="submit_message" style="display:none">
            <b class="title"><i class="far fa-envelope"></i><?php _e('After submitting / updating the form'); ?></b>
            <div style="width:40%;float:left;padding:10px;box-sizing: border-box;height: 550px;">
                <label><input type="checkbox" name="submit_message[online_message]" value="yes" <?php echo (isset($submit_message['online_message'])) ? 'checked' : ''; ?> /> <b>Document process / history message</b></label>
                <div style="padding:10px 0px"><textarea name="submit_message[online_message_body]" class="tiny" id="submit_message_online_message_body" style="height:150px"><?php echo (isset($submit_message['online_message_body'])) ? $submit_message['online_message_body'] : ''; ?></textarea></div>

                <label><input type="checkbox" name="submit_message[online_notification]" value="yes" <?php echo (isset($submit_message['online_notification'])) ? 'checked' : ''; ?> /> <b>Slide up notification message</b></label>
                <div style="padding:10px 0px">
                    <textarea name="submit_message[online_notification_body]" id="submit_message_online_notification_body" style="height:160px;width:100%"><?php echo (isset($submit_message['online_notification_body'])) ? $submit_message['online_notification_body'] : ''; ?></textarea>
                </div>
                <div>
                    <strong>After submit load the form:</strong>
                    <?php //after submit load next form
                    $next_form = isset($form_options['next_form']) ? $form_options['next_form'] : '';

                    //forms list

                    if ($forms = $hqcdb->get_results("SELECT foid,form_id,form_name FROM hqc_forms WHERE status = 'active' ORDER BY form_id ASC")) { ?>
                        <select name="form_options[next_form]" style="width:300px;">
                            <option value="">Select form</option>
                            <?php foreach ($forms as $form) {
                                if ($form['foid'] != $_GET['foid']) { ?> <option value="<?php echo $form['foid']; ?>" <?php echo trim($next_form) != '' && $next_form == $form['foid'] ? 'selected' : ''; ?>><?php echo $form['form_id'] . ' - ' . $form['form_name']; ?></option>
                            <?php
                                };
                            };
                            ?>
                        </select><br />
                        <label><input type="checkbox" value="yes" name='form_options[post_to_next_form]' <?php echo isset($form_options['post_to_next_form']) ? 'checked' : ''; ?>> Post data to the next form?</label>
                    <?php
                    };
                    ?>
                </div>
            </div>

            <div style="width:60%;float:left;">
                <div style="padding: 10px;box-sizing: border-box;">
                    <label><input type="checkbox" name="submit_message[send_email]" value="yes" <?php echo (isset($submit_message['send_email'])) ? 'checked' : ''; ?> /><b>Send email message</b></label><br />
                    <input type="hidden" name="submit_message[email_type]" value="custom" checked="checked" />
                    <table>
                        <tr>
                            <td style="white-space:nowrap"><?php _e('Sender name'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="submit_message[from_name]" value="<?php echo (isset($submit_message['from_name'])) ? $submit_message['from_name'] : $default_message['from_name']; ?>" /></td>
                            <td style="white-space:nowrap"><?php _e('Reply address'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="submit_message[from_email]" value="<?php echo (isset($submit_message['from_email'])) ? $submit_message['from_email'] : $default_message['from_email']; ?>" /></td>
                        </tr>
                        <tr>
                            <td style="white-space:nowrap">Email subject:</td>
                            <td colspan="3"> <input type="text" name="submit_message[subject]" value="<?php echo (isset($submit_message['subject'])) ? $submit_message['subject'] : $default_message['subject']; ?>" /></td>
                        </tr>
                    </table>
                    <div>
                        <textarea style="width:100%;height:300px" name="submit_message[message]" id="submit_message_message" class="tiny"><?php echo (isset($submit_message['message'])) ? $submit_message['message'] : $default_message['message']; ?></textarea>
                        <br /><label><input type="checkbox" name="submit_message[attach_form]" value="yes" <?php echo (isset($submit_message['attach_form'])) ? 'checked' : ''; ?> />Attach the form as PDF file</label>
                        <br />
                        <label><input type="checkbox" name="submit_message[emailmeacopy]" value="yes" <?php echo (isset($submit_message['emailmeacopy'])) ? 'checked' : ''; ?> /><b>Email copy to me to:</b></label> <input type="text" name="submit_message[bcc_email]" style="width:50%" value="<?php echo (isset($submit_message['bcc_email']) and trim($submit_message['bcc_email']) != '') ? $submit_message['bcc_email'] : $default_message['bcc_email']; ?>" />
                    </div>
                </div>
            </div>
        </li>
        <!--after approving the form message-->
        <li id="approving_the_form" style="display:none">
            <b class="title"><i class="far fa-envelope"></i><?php _e('After approving the form'); ?></b>
            <div style="width:40%;float:left;padding:10px;box-sizing: border-box;height: 550px;margin-bottom:10px;">
                <label><input type="checkbox" name="approved_message[online_message]" value="yes" <?php echo (isset($approved_message['online_message'])) ? 'checked' : ''; ?> /> <b>Document process / history message</b></label>
                <div style="margin:10px 0px 10px;"><textarea name="approved_message[online_message_body]" id="approved_message_online_message_body" class="tiny" style="height:150px"><?php echo (isset($approved_message['online_message_body'])) ? $approved_message['online_message_body'] : ''; ?></textarea></div>

                <label><input type="checkbox" name="approved_message[online_notification]" value="yes" <?php echo (isset($approved_message['online_notification'])) ? 'checked' : ''; ?> /> <b>Slide up notification message</b></label>
                <div style="margin-bottom:10px">
                    <textarea name="approved_message[online_notification_body]" id="approved_online_notification_body" style="height:160px;width:100%;margin-top:10px"><?php echo (isset($approved_message['online_notification_body'])) ? $approved_message['online_notification_body'] : ''; ?></textarea>
                </div>
            </div>

            <div style="width:60%;float:left;">
                <div style="padding: 10px;box-sizing: border-box;">
                    <label><input type="checkbox" name="approved_message[send_email]" value="yes" <?php echo (isset($approved_message['send_email'])) ? 'checked' : ''; ?> /><b>Send email message</b></label><br />
                    <table>
                        <tr>
                            <td style="white-space:nowrap"><?php _e('Sender name'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="approved_message[from_name]" value="<?php echo (isset($approved_message['from_name'])) ? $approved_message['from_name'] : $default_message['from_name']; ?>" /></td>
                            <td style="white-space:nowrap"><?php _e('Reply address'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="approved_message[from_email]" value="<?php echo (isset($approved_message['from_email'])) ? $approved_message['from_email'] : $default_message['from_email']; ?>" /></td>
                        </tr>
                        <tr>
                            <td style="white-space:nowrap">Email subject:</td>
                            <td colspan="3"> <input type="text" name="approved_message[subject]" value="<?php echo (isset($approved_message['subject'])) ? $approved_message['subject'] : $default_message['subject']; ?>" /></td>
                        </tr>
                    </table>
                    <div>
                        <textarea style="width:100%;height:300px" name="approved_message[message]" id="approved_message_message" class="tiny"><?php echo (isset($approved_message['message'])) ? $approved_message['message'] : $default_message['message']; ?></textarea>
                        <br />
                        <label><input type="checkbox" name="approved_message[attach_form]" value="yes" <?php echo (isset($approved_message['attach_form'])) ? 'checked' : ''; ?> />Attach the form as PDF file</label>
                        <br />
                        <label><input type="checkbox" name="approved_message[emailmeacopy]" value="yes" <?php echo (isset($approved_message['emailmeacopy'])) ? 'checked' : ''; ?> /><b>Email copy to me to:</b></label> <input type="text" name="approved_message[bcc_email]" style="width:50%" value="<?php echo (isset($approved_message['bcc_email']) and trim($approved_message['bcc_email']) != '') ? $approved_message['bcc_email'] : $default_message['bcc_email']; ?>" />
                    </div>
                </div>
            </div>
        </li>
        <!-- // end after disapproving the form message-->
        <!--after disapproving the form message-->
        <li id="disapproving_the_form" style="display:none">
            <b class="title"><i class="far fa-envelope"></i><?php _e('After disapproving the form'); ?></b>
            <div style="width:40%;float:left;padding:10px;box-sizing: border-box;height: 550px;">
                <label><input type="checkbox" name="disapproved_message[online_message]" value="yes" <?php echo (isset($disapproved_message['online_message'])) ? 'checked' : ''; ?> /> <b>Document process / history message</b></label>
                <div style="margin:10px 0px 10px;"><textarea name="disapproved_message[online_message_body]" class="tiny" id="disapproved_message_online_message_body" style="height:150px"><?php echo (isset($disapproved_message['online_message_body'])) ? $disapproved_message['online_message_body'] : ''; ?></textarea></div>

                <label><input type="checkbox" name="disapproved_message[online_notification]" value="yes" <?php echo (isset($disapproved_message['online_notification'])) ? 'checked' : ''; ?> /> <b>Slide up notification message</b></label>
                <textarea name="disapproved_message[online_notification_body]" id="disapproved_message_online_notification_body" style="height:160px;width:100%;margin-top:10px"><?php echo (isset($disapproved_message['online_notification_body'])) ? $disapproved_message['online_notification_body'] : ''; ?></textarea>
            </div>

            <div style="width:60%;float:left;">
                <div style="padding: 10px;box-sizing: border-box;">
                    <b>Email message</b><br />
                    <table>
                        <tr>
                            <td style="white-space:nowrap"><?php _e('Sender name'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="disapproved_message[from_name]" value="<?php echo (isset($disapproved_message['from_name'])) ? $disapproved_message['from_name'] : $default_message['from_name']; ?>" /></td>
                            <td style="white-space:nowrap"><?php _e('Reply address'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="disapproved_message[from_email]" value="<?php echo (isset($disapproved_message['from_email'])) ? $disapproved_message['from_email'] : $default_message['from_email']; ?>" /></td>
                        </tr>
                        <tr>
                            <td style="white-space:nowrap">Email subject:</td>
                            <td colspan="3"> <input type="text" name="disapproved_message[subject]" value="<?php echo (isset($disapproved_message['subject'])) ? $disapproved_message['subject'] : $default_message['subject']; ?>" /></td>
                        </tr>
                    </table>
                    <div>
                        <textarea style="width:100%;height:300px" name="disapproved_message[message]" id="disapproved_message_message" class="tiny"><?php echo (isset($disapproved_message['message'])) ? $disapproved_message['message'] : $default_message['message']; ?></textarea>
                        <br />
                        <label><input type="checkbox" name="disapproved_message[emailmeacopy]" value="yes" <?php echo (isset($disapproved_message['emailmeacopy'])) ? 'checked' : ''; ?> /><b>Email copy to me to:</b></label> <input type="text" name="disapproved_message[bcc_email]" style="width:50%" value="<?php echo (isset($disapproved_message['bcc_email']) and trim($disapproved_message['bcc_email']) != '') ? $disapproved_message['bcc_email'] : $default_message['bcc_email']; ?>" />
                    </div>
                </div>
            </div>
        </li>
        <!-- // end after disapproving the form message-->
        <!--general message-->
        <li id="general_message" style="display:none">
            <b class="title"><i class="far fa-envelope"></i><?php _e('General email message'); ?></b>
            <div style="width:40%;float:left;padding:10px;box-sizing: border-box;height: 550px;">
                <label><input type="checkbox" name="general_message[online_notification]" value="yes" <?php echo (isset($general_message['online_notification'])) ? 'checked' : ''; ?> /> <b>Slide up notification message</b></label>
                <div style="margin-top:10px;"><textarea name="general_message[online_notification_body]" id="general_message_online_notification_body" class="tiny" style="height:150px"><?php echo (isset($general_message['online_notification_body'])) ? $general_message['online_notification_body'] : ''; ?></textarea></div>
            </div>

            <div style="width:60%;float:left;">
                <div style="padding: 10px;box-sizing: border-box;">
                    <b>Email message</b><br />
                    <table>
                        <tr>
                            <td style="white-space:nowrap"><?php _e('Sender name'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="general_message[from_name]" value="<?php echo (isset($general_message['from_name'])) ? $general_message['from_name'] : $default_message['from_name']; ?>" /></td>
                            <td style="white-space:nowrap"><?php _e('Reply address'); ?>:</td>
                            <td style="width:50%"> <input type="text" name="general_message[from_email]" value="<?php echo (isset($general_message['from_email'])) ? $general_message['from_email'] : $default_message['from_email']; ?>" /></td>
                        </tr>
                        <tr>
                            <td style="white-space:nowrap">Email subject:</td>
                            <td colspan="3"> <input type="text" name="general_message[subject]" value="<?php echo (isset($general_message['subject'])) ? $general_message['subject'] : $default_message['subject']; ?>" /></td>
                        </tr>
                    </table>
                    <div>
                        <textarea style="width:100%;height:300px" name="general_message[message]" id="general_message_message" class="tiny"><?php echo (isset($general_message['message'])) ? $general_message['message'] : $default_message['message']; ?></textarea>
                        <br />
                        <label><input type="checkbox" name="general_message[emailmeacopy]" value="yes" <?php echo (isset($general_message['emailmeacopy'])) ? 'checked' : ''; ?> /><b>Email copy to me to:</b></label> <input type="text" name="general_message[bcc_email]" style="width:50%" value="<?php echo (isset($general_message['bcc_email']) and trim($default_message['bcc_email']) != '') ? $general_message['bcc_email'] : $default_message['bcc_email']; ?>" />
                    </div>
                </div>
            </div>
        </li>
        <!-- // end general message-->

        </ul>

        <br style="clear:both" />
        <?php
                $use_password = array();

                $approval_type = '';
                $approval_password = '';

                if (isset($form_options['approval_required']['type'])) {
                    $approval_type = $form_options['approval_required']['type'];

                    if (isset($form_options['approval_required']['password']))
                        $approval_password = $form_options['approval_required']['password'];
                }

        ?>
        <div style="padding:20px">
            <div style="float:right">
                <?php if (isset($form_options['approval_required_by_client']))
                    $approval_required_by_client = $form_options['approval_required_by_client'];
                else
                    $approval_required_by_client = 'no'; ?>
                <strong>Approval required by client:</strong>
                <label><input type="radio" name="form_options[approval_required_by_client]" value="no" <?php echo $approval_required_by_client == 'no' ? 'checked' : ''; ?> />No </label>
                <label><input type="radio" name="form_options[approval_required_by_client]" value="yes" <?php echo $approval_required_by_client == 'yes' ? 'checked' : ''; ?> />Yes </label>
            </div>
            <div>
                <strong>Approval required to process the form:</strong>
                <select name="form_options[approval_required][type]" id="approval_required" onchange="checkApprovedType(this.value)">
                    <option value="">No approval required</option>
                    <option value="password">Use password</option>
                    <option value="sms">Use SMS verification</option>
                    <option value="email">Use Email address</option>
                </select>
                <span id="approval_password" style="margin-right:20px">
                    <b>The Password: </b><input type="text" name="form_options[approval_required][password]" value="<?php echo trim($approval_password) != '' ? $approval_password : ''; ?>" />
                </span>
            </div>
        </div>

        <script>
            function checkApprovedType(tp) {
                if (tp == 'password') {
                    jQuery('#approval_password').css('display', 'inline-block')
                } else {
                    jQuery('#approval_password').css('display', 'none')
                }
            }
            jQuery("#approval_required").val("<?php echo $approval_type; ?>");
            checkApprovedType("<?php echo $approval_type; ?>");
        </script>
    <?php }; ?>
    <div style="text-align:center;padding:20px;width:100%;position: fixed;bottom:0px;left:0px;" id="saveButtonsTools">
        <div style="background:white;padding:5px 100px;max-width:1400px;margin:0 auto;">
            <script>
                function actExample(tp) {
                    jQuery("#doExample").val(tp);
                    jQuery("#form_maker").submit();
                }
            </script>
            <select size="1" name="save_action">
                <option value="save_and_stay"><?php _e('Save and stay'); ?></option>
                <option value="save"><?php _e('Save'); ?></option>
                <option value="save_and_reload"><?php _e('Save and reload'); ?></option>
            </select>
            <input type="button" value="<?php _e('Save the form'); ?>" onclick="doAct('save')" />
            <input type="button" value="<?php _e('View the form'); ?>" onclick="doAct('view')" />
            <a class="button" href="/admin/forms/<?php echo (isset($_GET['cat'])) ? '?cat=0' : ''; ?>">Cancel</a>
        </div>
    </div>
    </div>
</form>
<script>
    var formType = "<?php echo isset($theForm['form_type']) ? $theForm['form_type'] : 'form'; ?>";
    change_form_type(formType);

    require(['vs/editor/editor.main'], function() {

        window.styleEditor = monaco.editor.create(document.getElementById('style_editor'), {
            language: 'html',
            automaticLayout: true,
            value: document.getElementById('css_js').value,
            wordWrap: 'on',
            bracketPairColorization: true,
            minimap: {
                enabled: false
            }
        });

        window.phpEditor = monaco.editor.create(document.getElementById('php_editor'), {
            language: 'php',
            automaticLayout: true,
            value: document.getElementById('include_php').value,
            wordWrap: 'on',
            bracketPairColorization: true,
            minimap: {
                enabled: false
            }
        });
        <?php if (!isset($_GET['sandbox'])) { ?>
            window.pdfStyleEditor = monaco.editor.create(document.getElementById('pdf_style_editor'), {
                language: 'html',
                automaticLayout: true,
                value: document.getElementById('pdf_css_js').value,
                wordWrap: 'on',
                bracketPairColorization: true,
                minimap: {
                    enabled: false
                }
            });

            window.pdfPhpEditor = monaco.editor.create(document.getElementById('pdf_php_editor'), {
                language: 'php',
                automaticLayout: true,
                value: document.getElementById('pdf_include_php').value,
                wordWrap: 'on',
                bracketPairColorization: true,
                minimap: {
                    enabled: false
                }
            });
        <?php }; ?>
    });

    jQuery('.tiny').each(function() {
        do_tinymce_minimum('#' + jQuery(this).attr('id'));
    })
</script>