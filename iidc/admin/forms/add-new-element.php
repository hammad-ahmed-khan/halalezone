<?php
if (!session_id()) {
    session_start();
}
include($_SESSION['hqc_path'] . '/load.inc.php');
?>
<style>
    #formElementsButtons h5 {
        background: var(--color20);
        padding: 5px 10px;
        font-weight: bold;
        color: var(--color100) !important;
        /* margin-top: 20px; */
    }

    ul.fifty {
        margin: 0px;
        padding: 0px;
        border: 1px solid var(--color30);
        overflow: auto;
    }

    .fifty li {
        width: 48.9% !important;
        float: left !important;
        padding: 2px;
    }

    .fifty li input {
        margin: 0px !important;
    }

    ul.fifty select {
        width: 99%;
    }

    .insertElements {
        background: var(--color80);
        border: 1px solid var(--color100);
        color: white !important;
        margin: 5px 0px;
        position: relative;
    }

    .insertElements input {
        position: absolute !important;
        right: 0px !important;
        width: 60px !important;
    }

    .insertElements select {
        background: var(--color20);
        margin: 10px;
        height: 22px !important;
        padding: 0px !important;
    }

    #formElementsButtons input {
        display: block;
        width: 100%;
        background: var(--color80);
        border: 1px solid var(--color100);
        text-transform: capitalize;
        margin: 5px 0px;
        padding: 0px !important;
        padding-left: 10px !important;
    }
</style>
<div style="margin-top:20px;text-align:center"><input type="button" style="background:green;border:none" value="stop block editing" onclick="stopEnableBlockEditing(this)" /></div>
<div id="formElementsButtons" style="padding:10px">
    <h5>Single Line Input</h5>
    <ul class="fifty">
        <li><input type="button" data-type="text" data-text="Text Title" value="Text" /></li>
        <li><input type="button" data-type="date" data-text="Date" value="Date" /></li>
        <li><input type="button" data-type="number" data-text="Number" value="Number" /></li>
        <li><input type="button" data-type="email" data-text="Email Address" value="Email" /></li>
        <li><input type="button" data-type="password" data-text="Password" value="Password" /></li>
        <li><input type="button" data-type="tel" data-text="Telephone" value="Telephone" /></li>
        <li><input type="button" data-type="url" data-text="Website" value="Website URL" /></li>
        <li><input type="button" data-type="file" data-text="Upload document" value="Upload File Button"></li>
    </ul>
    <br style="clear: both;" />
    <input type="button" data-type="textarea" data-text="Text title" value="Multi lines text (Textarea)" />
    <br style="clear: both;" />
    <h5>Checkbox & radio buttons</h5>
    <ul class="fifty">
        <li><input type="button" data-type="checkbox" value="Checkbox" data-text="Checkbox title" /></li>
        <li><input type="button" data-type="radio" value="Radio Default" data-text="Please select one" /></li>
        <li><input type="button" data-type="radio-yesNo" data-text="Please select item" value="Yes/No" /></li>
        <li><input type="button" data-type="radio-yesNoOther" data-text="Please select item" value="Yes/No/other" /></li>
        <li><input type="button" data-type="radio-yesNoOtherInput" data-text="Please select item" value="Yes/No/other/Input" /></li>
    </ul>
    <br style="clear: both;" />
    <h5>Dropdown list</h5>
    <ul class="fifty">
        <li><input type="button" data-type="select" data-text="Please select" value="Default" /></li>
        <li><input type="button" data-type="select-title" data-text="Title" value="Title" /></li>
        <li><input type="button" data-type="select-countries" data-text="Country" value="Countries" /></li>
        <li><input type="button" data-type="select-languages" data-text="Languages" value="Languages" /></li>
        <li><input type="button" data-type="select-offices" data-text="Offices" value="HQC offices" /></li>
    </ul>
    <br style="clear: both;" />
    <h5>Predefined inputs</h5>
    <ul class="fifty">
        <li><input type="button" data-type="nameInputs" value="Insert name"></li>
        <li><input type="button" data-type="address" value="Insert address"></li>
        <li><input type="button" data-type="full-address" value="Insert full address"></li>
    </ul>
    <br style="clear: both;" />
    <h5>HQC Data</h5>
    <ul class="fifty">
        <li><select id="selectHQCData">
                <?php
                $hqcInputs = array("company_name_english", "company_name_arabic", "contact_person", "office_street", "office_zipcode", "office_city", "office_country", "office_vat", "office_coc", "office_telephone", "office_gsm", "office_whatsapp", "office_email", "office_website", "my_address");
                foreach ($hqcInputs as $offKey) {
                    echo '<option value="' . $offKey . '">Company ' . str_replace(array('company', 'my_', 'office_', '_'), array('', '', '', ' '), $offKey) . '</option>';
                };
                ?>
            </select></li>
        <li><input type="button" data-type="hqcData" value="insert" style="margin-right: 10px;border:0px"></li>
    </ul>
    <br style="clear: both;" />
    <h5>HQC shortcodes</h5>
    <ul class="fifty">
        <li><select id="selectHQCCodes">
                <?php
                $hqcInputs = array("my_company_name", "my_company_name_arabic", "my_company_contact_person", "my_company_street", "my_company_zipcode", "my_company_city", "my_company_country", "my_company_vat", "my_company_coc", "my_company_telephone", "my_company_gsm", "my_company_whatsapp", "my_company_email", "my_company_website", "my_company_address");
                foreach ($hqcInputs as $offKey) {
                    echo '<option value="' .  $offKey . '">' . str_replace(array('my_', '_'), array('', ' '), $offKey) . '</option>';
                };
                ?>
            </select></li>
        <li><input type="button" data-type="hqcCode" value="insert" style="margin-right: 10px;border:0px"></li>
    </ul>
    <br style="clear: both;" />
    <h5>Client shortcodes</h5>
    <ul class="fifty">
        <li><select id="selectClientCodes">
                <?php
                $clientInputs = array(
                    'company_name',
                    'company_id',
                    'company_contact_person',
                    'company_address',
                    'company_country',
                    'company_city',
                    'company_telephone',
                    'company_email',
                    'company_website'
                );
                foreach ($clientInputs as $clKey) {
                    echo '<option value="' . $clKey . '">' . str_replace('_', ' ', $clKey) . '</option>';
                };
                ?>
            </select></li>
        <li><input type="button" data-type="clientShort" value="insert" style="margin-right: 10px;border:0px"></li>
    </ul>
    <br style="clear: both;" />
    <h5>Action buttons</h5>
    <ul class="fifty">
        <li><input type="button" data-type="button" value="Action Button"></li>
        <li><input type="button" data-type="submit" value="Submit"></li>
        <li><input type="button" data-type="reset" value="Reset"></li>
        <li><input type="button" data-type="cancel" value="Cancel"></li>
    </ul>
    <br style="clear: both;">

    <h5>Form tags</h5>
    <ul class="fifty">
        <li><input type="button" data-type="formStart" value="Form tag"></li>
        <li><input type="button" data-type="formClose" value="Closing Form Tag"></li>
    </ul>
    <br style="clear: both;" />
    <h5>Special codes</h5>
    <ul class="fifty">
        <li><input type="button" data-type="phpCode" value="PHP code"></li>
        <li><input type="button" data-type="pdfCode" value="PDF code"></li>
    </ul>
    <br style="clear: both;">
    <h5>Field sets</h5>
    <ul class="fifty">
        <li><input type="button" data-type="fieldset" value="fieldset" title="Select text first"></li>
        <li><input type="button" data-type="infoText" value="information text" title="Select text first"></li>
        <li><input type="button" data-type="explanation" value="explanation text" title="Select text first"></li>
        <li><input type="button" data-type="instructions" value="instructions text for ICT" title="Select text first">
        </li>
    </ul>
    <script>
        selectedInput = null;
        $("#formElementsButtons").find("input").each(function() {
            $(this).attr('draggable', true);
        })

        $("#formElementsButtons input").on('mousedown', function(e) {
            selectedInput = this;
            //  console.log(selectedInput);
        })
        // Get a reference to the TinyMCE editor instance
        var editor = tinymce.activeEditor;

        // Get a reference to the drop zone in the editor
        var dropZone = editor.getBody();
        $(dropZone).sortable();
        //find first tag in dropzone using jquery
        $(dropZone).find(".ui-sortable-handle").first().addClass("selectedBlock");
        // Add a listener for the "drop" event on the drop zone
        dropZone.addEventListener('drop', function(event) {
            //   event.preventDefault();
            insertFormElement(selectedInput);
        });

        // Prevent the browser's default handling of drag-and-drop
        dropZone.addEventListener('dragover', function(event) {
            event.preventDefault();
        });

        function stopBlockEditing(obj) {
            blockEditingEnabled = false;
            //stop block editing,destroy sortable and remove sortable classes
            //remove tinymce classes
            $(dropZone).find("tbody").each(function() {
                if (jQuery(this).hasClass("ui-sortable"))
                    $(this).sortable("destroy");
                jQuery(this).parent("table").find("tr").css("background", "")
            })
            if (jQuery(dropZone).hasClass("ui-sortable"))
                $(dropZone).sortable("destroy");
            jQuery(dropZone).find(".ui-sortable-handle").removeClass("ui-sortable-handle");
            $(dropZone).find(".selectedBlock").removeClass("selectedBlock");
            if (typeof obj != 'undefined')
                $(obj).val("enable block editing").css("background", "");
            jQuery("#blockEditingEnabledDiv").css("display", "block");
            $("#formElementsButtons").find("input").each(function() {
                // $(this).removeAttr('draggable');
            })
            jQuery(editor.getBody()).find(".editBlockTools").remove();
        }

        function startBlockEditing(obj) {
            blockEditingEnabled = true;
            el = editor.selection.getNode();
            var tagName = jQuery(el).prop("tagName").toLowerCase();

            if (jQuery(el).parents("tbody").length > 0) {
                jQuery(el).parents('tbody').sortable();
                jQuery(el).parents("table").find("tr").css("background", "#ffe")
                tableBlockEditable = true;
            } else {
                $(dropZone).sortable();

            }
            if (selectedBlock != null)
                $(selectedBlock).addClass("selectedBlock");
            else
                $(el).addClass("selectedBlock");
            $(obj).val("stop block editing").css("background", "green");
            jQuery("#blockEditingEnabledDiv").css("display", "none");
            $("#formElementsButtons").find("input").each(function() {
                // $(this).attr('draggable', true);
            })
            jQuery(selectedBlock).append('<span class="editBlockTools"><i class="far fa-trash-alt" onclick="parent.removeThisBlock();"></i></span>')
        }

        function stopEnableBlockEditing(obj) {
            tableBlockEditable = false;
            if (blockEditingEnabled == true) {
                stopBlockEditing(obj)
            } else {
                startBlockEditing(obj)
            }
        }

        function getElIdNumber() {
            newDate = new Date();
            return newDate.getHours() + '' + newDate.getMinutes() + '' + newDate.getSeconds();
        }

        function getClosestRadio(el) {
            editorContent = '<div id="editorContentHolderTem" style="position:fixed;top:-10000px">' + tinymce.get(jQuery('textarea.tinymce_template').attr("id")).getContent() + '</div>';
            jQuery("body").append(editorContent);
            closest = jQuery("#editorContentHolderTem").find(el).parent().find('input[type=radio]').first().attr('name');
            jQuery("#editorContentHolderTem").remove();
            return closest;
        }

        function insertFormElement(el) {

            let elementType = $(el).attr("data-type") || "";
            if (!elementType)
                return false;

            count = getElIdNumber();
            textToBeInserted = '';
            if (jQuery(el).data("text"))
                textToBeInserted = jQuery(el).data("text");

            var textInputs = ["text", "file", "date", "url", "email", "number", "password", "telephone"];
            if (jQuery.inArray(elementType, textInputs) > -1) {
                if (elementType == 'file') {
                    name = elementType + '[' + count + '][]';
                    id = elementType + '' + count;
                    vars = 'multiple="" accept=".doc,.docx,pdf,.xls,.xlsx,image/*" value="Choose documents to upload"';
                } else {
                   // elementType = jQuery("#inputType").val();
                    //selected option
                    if (jQuery("#inputType option:selected").data("text")) {
                        textToBeInserted = jQuery("#inputType option:selected").data("text");
                    }

                    name = elementType + '' + count;
                    id = name;
                    if (elementType == 'number')
                        vars = 'style="width:100px"';
                    else
                        vars = '';
                }
                toBeInserted = '<input type="' + elementType + '" name="' + name + '" id="' + id + '" autocomplete="none" ' + vars + '/>';
            }


            if (elementType == 'textarea')
                toBeInserted = '<textarea name="textarea' + count + '" id="textarea' + count + '"></textarea>';

            if (elementType == 'hidden')
                toBeInserted = '<input type="hidden-input" name="hidden' + count + '" id="hidden' + count + '" value="hiddenValue' + count + '"/>';

            var selectInputs = ["select", "select-title", "select-countries", "select-languages", "select-offices"];

            if (jQuery.inArray(elementType, selectInputs) > -1) {
                if (jQuery("#selectType option:selected").data("text")) {
                    textToBeInserted = jQuery("#selectType option:selected").data("text");
                }
                options = '';
                elementName = elementType.replace('select-', '');
                jQuery.ajax({
                    url: 'get_select_options.php',
                    type: 'POST',
                    data: {
                        elementName: elementName
                    },
                    async: false,
                    success: function(data) {
                        options = data;
                    }
                });
                toBeInserted = '<select name="' + elementName + '_' + count + '" id="' + elementName + '_' + count + '">' + options + '</select>';
            }

            if (elementType == 'address' || elementType == 'full-address' ||
                elementType == 'nameInputs') {
                jQuery.ajax({
                    url: 'get_select_options.php',
                    type: 'POST',
                    data: {
                        elementName: 'address',
                        get: elementType
                    },
                    async: false,
                    success: function(data) {
                        toBeInserted = data;
                    }
                });
            }

            if (elementType == 'hqcData') {
                dataKey = jQuery("#selectHQCData").val();
                jQuery.ajax({
                    url: 'get_select_options.php',
                    type: 'POST',
                    data: {
                        getHQCData: dataKey,
                        get: 'data'
                    },
                    async: false,
                    success: function(data) {
                        toBeInserted = data;
                    }
                });
            }

            if (elementType == 'hqcCode') {
                dataKey = jQuery("#selectHQCCodes").val();
                toBeInserted = '[' + dataKey + ']';
            }

            if (elementType == 'clientShort') {
                dataKey = jQuery("#selectClientCodes").val();
                toBeInserted = '[' + dataKey + ']';
            }

            if (elementType == 'radio-yesNo' || elementType == 'radio-yesNoOther' || elementType == 'radio-yesNoOtherInput') {
                textToBeInserted = 'Please select one';
                elId = 'radio_' + count;

                if (elementType == "radio-yesNo") {
                    toBeInserted = '<label><input type="radio" name="' + elId + '" id="' + elId + '_yes"  value="yes" data-required="yes" /> Yes</label> ' +
                        '<label><input type="radio" name="' + elId + '" id="' + elId + '_no"  value="no"/> No</label> ';
                } else if (elementType == "radio-yesNoOther") {
                    toBeInserted = '<label><input type="radio" name="' + elId + '" id="' + elId + '_yes"  value="yes" data-required="yes"/> Yes</label> <br/>' +
                        '<label><input type="radio" name="' + elId + '" id="' + elId + '_no"  value="no"/> No</label> <br/>' +
                        '<label><input type="radio" name="' + elId + '" id="' + elId + '_other"  value="other"/> Other</label> ';
                } else if (elementType == "radio-yesNoOtherInput") {
                    toBeInserted = '<label><input type="radio" name="' + elId + '" id="' + elId + '_yes"  value="yes" data-required="yes"/> Yes</label> <br/>' +
                        '<label><input type="radio" name="' + elId + '" id="' + elId + '_no"  value="no"/> No</label> <br/>' +
                        '<label><input type="radio" name="' + elId + '" id="' + elId + '_other"  value="other"/> Other</label> ' +
                        '<input type="text" name="' + elId + '_other" id="' + elId + '_other_info"/>';
                }
            }


            if (elementType == 'checkbox' || elementType == 'radio') {
                label = editor.selection.getContent();
                if (label.trim() == "") {
                    label = elementType;
                    elName = elementType + '_' + count;
                    elId = elementType + '_' + count;
                    elValue = count;
                } else {
                    elName = fixElName(elementType + '[' + label + ']');
                    elId = elName.replace(/ /g, "_")
                        .replace(/\]/g, "")
                        .replace(/\[/g, "_");
                    elValue = label;
                }
                toBeInserted = '<label id="label_' + elId + '"><input type="' + elementType + '" name="' + elName + '" id="' + elId + '"  value="' + elValue + '"/>' + label + "</label> ";
            }

            if (elementType == 'button')
                toBeInserted = '<input type="button" name="button' + count + '" id="button' + count + '" value="Action button"/>';

            if (elementType == 'submit')
                toBeInserted = '<input type="submit" name="submit' + count + '" id="submit' + count + '" value="Submit"/>';

            if (elementType == 'cancel')
                toBeInserted = '<input type="button" name="cancel' + count + '" id="cancel' + count + '" value="Cancel" onclick="history.go(-1)"/>';

            if (elementType == 'reset')
                toBeInserted = '<input type="reset" name="reset' + count + '" id="reset' + count + '" value="Reset"/>';

            if (elementType == 'formStart')
                toBeInserted = '<input type="form-start" placeholder="form" action="form_save.php" enctype="multipart/form-data" method="post" name="form' + count + '"  id="form' + count + '" onsubmit="return post_this_form(this)" data-error="Fields with * are required." autocomplete="off">';

            if (elementType == 'formClose')
                toBeInserted = '<input type="form-end" placeholder="/form">';

            if (elementType == 'phpCode')
                toBeInserted = '[? /*type here php codes*/ ?]';

            if (elementType == 'pdfCode')
                toBeInserted = '[pdf /*type here tcpdf codes*/ ]';

            if (elementType == 'fieldset') {
                content = editor.selection.getContent();
                if (content.trim() == '')
                    content = 'Fieldset text'
                toBeInserted = '<fieldset><legend>Fieldset title</legend>' + content + '</fieldset>';
            }
            if (elementType == 'infoText') {
                content = editor.selection.getContent();
                if (content.trim() == '')
                    content = 'Information text'
                toBeInserted = "<info>" + content + "</info>";
            }
            if (elementType == 'explanation') {
                content = editor.selection.getContent();
                if (content.trim() == '')
                    content = 'Explanation text'
                toBeInserted = '<fieldset data-type="inline-explanation"><legend>For explanation click here</legend>' +
                    content + '</fieldset>';
            }
            if (elementType == 'instructions') {
                content = editor.selection.getContent();
                if (content.trim() == '')
                    content = 'Instructions text'
                toBeInserted = '<fieldset data-type="instructions"><legend>Instructions</legend>' +
                    content + '</fieldset>';
            }
            if (blockEditingEnabled == true) {
                if (textToBeInserted.trim() != '')
                    textToBeInserted = '<strong>' + textToBeInserted + ':</strong> ';
                // tinymce insert table row
                var selectedNode = $(dropZone).find(".selectedBlock");
                if (selectedNode.prop("tagName").toLowerCase() == 'tr') {
                    if (elementType == 'address' || elementType == 'full-address' || elementType == 'nameInputs') {
                        selectedNode.after(toBeInserted);
                    } else {
                        selectedNode.after('<tr class="ui-sortable-handle" id="insertedBlock"><td>' + textToBeInserted + '</td><td>' + toBeInserted + '</td></tr>');
                    }
                } else {
                    if (elementType == 'address' || elementType == 'full-address' || elementType == 'nameInputs')
                        toBeInserted = '<table>' + toBeInserted + '</table>';
                    selectedNode.after('<div id="insertedBlock" class="ui-sortable-handle" style="border:1px solid #EEE;padding:5px;margin:5px 0px">' + textToBeInserted + toBeInserted + '</div>');
                }
                $(dropZone).find(".selectedBlock").removeClass("selectedBlock");
                $(dropZone).find("#insertedBlock").addClass("selectedBlock").removeAttr("id");
            } else {
                editor.insertContent(toBeInserted);
            }
            if (elementType == 'radio' && typeof elId != 'undefined') {
                closestRadio = getClosestRadio("#label_" + elId);
                editor.dom.setAttrib(elId, "name", closestRadio);
            }
            setTimeout(function() {}, 1000);

        }
        $("#formElementsButtons input").on('click', function(e) {

            // if (blockEditingEnabled == false)
            // insertFormElement(this);
        })
    </script>