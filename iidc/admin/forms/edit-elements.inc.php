<?php
if (!session_id()) {
    session_start();
}
include($_SESSION['hqc_path'] . '/load.inc.php');
?>
<!DOCTYPE html>

<head>
    <?php include hqc_path . "/head.inc.php"; ?>
    <style>
        label {
            float: left;
            width: 90px;
            white-space: nowrap;
            border-bottom: 1px dashed var(--color20);

        }

        div {
            clear: both;
            margin: 10px 0px;
        }

        input[type=text],
        textarea,
        select {
            border: 1px solid var(--color40) !important;
            width: 66% !important;
            margin: 2px;
        }

        textarea {
            clear: both;
            width: 95% !important;
        }

        body::before {
            content: none;
        }

        #checkBoxRadioUlElements li {
            background: var(--color20);
            margin: 2px;
            border: 1px solid var(--color50);
            padding: 5px;
            cursor: move;
            position: relative;
        }

        #checkBoxRadioUlElements li span {
            position: absolute;
            right: 10px;
            top: 15px;
        }

        #checkBoxRadioUlElements li input[type=text] {
            width: 75% !important
        }
    </style>
</head>

<body style="background-image: none !important;background-color:#fff !important;padding-top:20px">
    <form id="updateEditTagForm" onsubmit="return updateEditTag();" style="display: none;">
        <h4>selected Tag: <span id="tagTagName" style="color:red"></span></h4>
        <div>
            <label for="editTagId">ID</label><input type="text" id="editTagId">
        </div>
        <div>
            <label for="editTagClass">Notes</label><input type="text" id="editTagClass">
        </div>
        <div>
            <label for="editTagStyle">Style</label>
            <textarea id="editTagStyle" size="100"></textarea>
        </div>
        <div style="text-align:center;margin-top:40px">
            <label style="width: auto;"><input type="checkbox" onclick="saveAfterUpdate = this.checked" style="width:20px !important;margin:0px">Save the form</label>
            <input type="submit" value="update" style="text-align: center;background: var(--color20);color:var(--color100);width:100px !important" />
        </div>
    </form>
    <div style="text-align: center;margin-bottom: 20px;" id="editElementsTabs">
        <input type="button" value="Default settings" style="width:45% !important; background:green" class="default"><input type="button" value="Advanced settings" style="width:45% !important" class="advanced" />
    </div>
    <script>
        jQuery("#editElementsTabs input").on("click", function() {
            jQuery("#editElementsTabs input").css("background", "")
            jQuery(this).css("background", "green");
            if (jQuery(this).hasClass("advanced"))
                jQuery(".advancedSettings").css("display", "block")
            else
                jQuery(".advancedSettings").css("display", "none")

        })

        function selectedTag(el) {
            jQuery("#updateEditElementForm").css("display", "none")
            jQuery("#updateEditElementH2").css("display", "none")
            jQuery("#updateEditTagForm").css("display", "block");
            jQuery("#updateEditTagForm").trigger("reset");
            tagName = jQuery(el).prop("tagName").toLowerCase();
            jQuery("#tagTagName").html(tagName);

            if (jQuery(el).attr("id"))
                jQuery("#editTagId").val(jQuery(el).attr("id"));

            if (jQuery(el).attr("class"))
                jQuery("#editTagClass").val(jQuery(el).attr("class"));

            if (jQuery(el).attr("style"))
                jQuery("#editTagStyle").val(jQuery(el).attr("style"));
        }

        function updateEditTag() {
            el = parent.selectedElForEdit;
            editor = parent.tinymce.activeEditor;
            editor.dom.setAttrib(el, "id", jQuery("#editTagId").val());
            editor.dom.setAttrib(el, "class", jQuery("#editTagClass").val());
            editor.dom.setAttrib(el, "style", jQuery("#editTagStyle").val());
            //  console.log(el)
            return false;
        }

        function getElIdNumber() {
            newDate = new Date();
            return newDate.getHours() + '' + newDate.getMinutes() + '' + newDate.getSeconds();
        }
    </script>

    <form id="updateEditElementForm" onsubmit="return updateEditElement();" style="display: none;">
        <input id="tagElName" type="hidden">
        <div id="editElNameDiv">
            <label id="editElName-l" for="editElName">Name</label><input type="text" id="editElName">
        </div>
        <div class="advancedSettings" style="display: none;">
            <div>
                <label id="editElId-l" for="editElId">ID</label><input type="text" id="editElId">
            </div>
            <div>
                <label id="editElClass-l" for="editElClass">Class</label><input type="text" id="editElClass">
            </div>
            <div>
                <label id="editElStyle-l" for="editElStyle">Style</label><input type="text" id="editElStyle">
            </div>
            <div id="editTextInputElements" style="display:none">
                <div id="editTextInputTypes" style="display:none">
                    <label id="tagElType-l" for="tagElType">Input Type</label>
                    <select id="tagElType" size="1" style="text-transform:capitalize">
                        <option value="text">Text</option>
                        <option value="button">button</option>
                        <option value="checkbox">checkbox</option>
                        <option value="color">color</option>
                        <option value="date">date</option>
                        <option value="datetime-local">datetime-local</option>
                        <option value="email">email</option>
                        <option value="file">file</option>
                        <option value="hidden">hidden</option>
                        <option value="month">month</option>
                        <option value="number">number</option>
                        <option value="password">password</option>
                        <option value="radio">radio</option>
                        <option value="reset">reset</option>
                        <option value="submit">submit</option>
                        <option value="time">time</option>
                        <option value="week">week</option>
                    </select>
                </div>
                <div>
                    <label id="editElValue-l" for="editElValue" style="width: auto;">Value / Options</label><br style="clear: left;"><textarea id="editElValue" size="100"></textarea>
                </div>
            </div>
            <div>
                <label id="editElPlaceholder-l" for="editElPlaceholder">Placeholder</label><input type="text" id="editElPlaceholder">
            </div>
        </div>
        <div id="radioCheckBox"></div>
        <div style="display: none;" id="editElInfoDiv">
            <label id="editElInfo-l" for="editElInfo">Class</label><textarea style="min-height:100px" type="text" id="editElInfo"></textarea>
        </div>
        <div>
            <label id="editElDataRequired-l" for="editElDataRequired">
                <input id="editElDataRequired" type="checkbox" style="width: initial !important; margin:0px 5px 0 0">Required</label>
            </input>
        </div>
        <div class="advancedSettings" style="display: none;">
            <div>
                <label id="editElTabindex-l" for="editElTabindex">Tabindex</label><input type="text" id="editElTabindex">
            </div>

            <div>
                <label id="editElOnclick-l" for="editElOnclick">Onclick</label><input type="text" id="editElOnclick">
            </div>
            <div>
                <label id="editElActionType-l"><input type="checkbox" id="editElActionType" onclick="getListOfForms()"> Switch to form</label>
                <span id="formsList"></span>
            </div>
            <div>
                <label id="editElUserType-l" for="editElUserType">User type</label><input type="text" id="editElUserType" placeholder="One or more separated by comma(admin,super_admin,office,client)">
            </div>
            <div>
                <label id="editElDataAvailable-l" for="editElDataAvailable">
                    <input id="editElDataAvailable" type="checkbox" style="width: initial !important;margin:0px 5px 0px 0px">Always available</label>
            </div>
        </div>
        <div id="EditFormElements" style="display:none">
            <div>
                <label id="editElAction-l" for="editElAction">Action</label><input type="text" id="editElAction">
            </div>
            <div>
                <label id="editElEnctype-l" for="editElEnctype">Enctype</label><input type="text" id="editElEnctype">
            </div>
            <div>
                <label id="editElMethod-l" for="editElMethod">Method</label><input type="text" id="editElMethod">
            </div>
            <div>
                <label id="editElOnsubmit-l" for="editElOnsubmit">Onsubmit</label><input type="text" id="editElOnsubmit">
            </div>
            <div>
                <label id="editElOnreset-l" for="editElOnreset">Onreset</label><input type="text" id="editElOnreset">
            </div>
            <div>
                <label id="editElData-error-l" for="editElData-error">Data-error</label><input type="text" id="editElData-error">
            </div>
        </div>
        <div style="text-align:center;margin-top:40px">
            <label style="width: auto;"><input type="checkbox" onclick="saveAfterUpdate = this.checked" style="width:20px !important;margin:0px">Save the form</label>
            <input type="submit" value="update" style="text-align: center;background: var(--color80);color:var(--color100);width:100px !important" />
        </div>
    </form>
    <h3 id="updateEditElementH2" style="text-align:center;margin-top:50px;text-transform: inherit;">No Element to Edit</h3>
</body>
<script>
    editor = parent.tinymce.activeEditor;
    var saveAfterUpdate = false;
    var oldClass = '';
    var textInputs = ["text", "file", "date", "url", "email", "number", "password", "telephone", "textarea"];

    function get_type(el) {
        elName = jQuery(el).prop("tagName");
        if ((elType = jQuery(el).attr("type"))) {
            return elType.replace("-input", "").replace("-start", "").toLowerCase();
        } else {
            return elName;
        }
    }

    function optionToJson(theType, theOptions) {
        if (theOptions.indexOf("[") >= 0 || theType == "TEXTAREA") {
            return theOptions;
        } else {
            opEl = "";
            jQuery(theOptions + " option").each(function(index, element) {
                opEl += jQuery(this).val() + '|' + jQuery(this).text();
                if (jQuery(this).attr("selected")) opEl += ',"selected":"selected"';
                opEl += "\n";
            });
            return opEl;
        }
    }

    function removeThis(obj) {
        jQuery(obj).parents('li').remove();
    }

    function addAfterThis(obj) {
        type = jQuery(obj).parents('li').find('input').first().attr("type")
        if (type == 'checkbox')
            inputEl = '<input type="text" style="width:250px !important" name="name" placeholder="Name"/><br/>'
        else
            inputEl = '';
        //time in seconds
        timeInSec = count = getElIdNumber();

        jQuery(obj).parents('li').after('<li id="' + type + '_' + (timeInSec++) + '"><input type="' + type + '" name"radio">' + inputEl + '<input name="text" type="text" placeholder="Text"><span><i class="fas fa-plus-circle" onclick="addAfterThis(this)"></i><i class="fas fa-minus-circle" onclick="removeThis(this)"></i></span></li></li>');
    }

    function selectedEl(el) {
        jQuery(editor.getBody()).find('.selectedEl').each(function() {
            jQuery(this).removeClass('selectedEl');
        })

        jQuery("#updateEditTagForm").css("display", "none")
        jQuery("#radioCheckBox").html('')
        oldClass = '';
        tagElName = get_type(el);
        if (tagElName == 'form-end' || tagElName.trim() == '') {
            jQuery("#updateEditElementForm").css("display", "none")
            jQuery("#updateEditElementH2").css("display", "block")
            return false;
        }
        if (jQuery(el).hasClass('selectedInputForEdit'))
            oldClass = 'selectedInputForEdit';
        if (jQuery(el).hasClass('selectedRadioCheckboxForEdit'))
            oldClass = 'selectedRadioCheckboxForEdit';

        elName = jQuery(el).prop("tagName").toLowerCase();

        jQuery("#updateEditElementH2").css("display", "none")
        jQuery("#updateEditElementForm").css("display", "block")
        //jQuery reset a form
        jQuery("#updateEditElementForm").trigger("reset");

        jQuery("#tagElName").val(tagElName);
        if (tagElName == "form") {
            jQuery("#editTextInputElements").css("display", "none");
            jQuery("#EditFormElements").css("display", "block");
        } else {
            jQuery("#EditFormElements").css("display", "none");
            jQuery("#editTextInputElements").css("display", "block");
            if (elName == "input") {
                jQuery("#editTextInputTypes").css("display", "block");
                if (jQuery(el).attr("type"))
                    elSelectedType = jQuery(el).attr("type").replace('-input', '');
                else elSelectedType = "text";
                jQuery("#tagElType").val(elSelectedType);
            } else {
                jQuery("#editTextInputTypes").css("display", "none");
            }
        }

        jQuery("#editElName").val(jQuery(el).attr("name"));

        if (tagElName == "radio" || tagElName == "checkbox") {
            if (tagElName == 'checkbox')
                jQuery("#editElNameDiv").css("display", "none")
            else
                jQuery("#editElNameDiv").css("display", "block")

            var prevEl = $(el).parent().prev();
            var nextEl = $(el).parent().next();
            if (prevEl.is('br') || nextEl.is('br')) {
                multiLinesCheckBoxes = 'checked';
            } else {
                multiLinesCheckBoxes = '';
            }


            if (jQuery(el).parents(':eq(1)').find('input[type=' + tagElName + ']').length) {
                var ulRadio = '<ul id="checkBoxRadioUlElements">';

                if (parent.blockEditingEnabled == false) {
                    if (tagElName == 'checkbox')
                        inputEl = '<input type="text" name="name" style="width:250px !important" placeholder="Name" value="' + jQuery(el).prop('name') + '"/><br/>'
                    else
                        inputEl = '';
                    radioId = jQuery(el).attr('id');
                    ulRadio += '<li id="' + radioId + '"><input type="' + tagElName + '" name="' + tagElName + '" ' + (jQuery(this).attr("checked") ? 'checked' : '') + '>' + inputEl + '<input name="text" type="text" placeholder="Text" value="' + jQuery(el).parent().text() + '"> <span><i class="fas fa-minus-circle" onclick="removeThis(this)"></i></span></li>';
                    jQuery(el).parent().addClass('selectedEl')
                } else {
                    jQuery(el).parents(':eq(1)').find('input[type=' + tagElName + ']').each(function() {
                        jQuery(this).parent().addClass('selectedEl')
                        if (tagElName == 'checkbox')
                            inputEl = '<input type="text" name="name" style="width:250px !important" placeholder="Name" value="' + jQuery(this).prop('name') + '"/><br/>'
                        else
                            inputEl = '';
                        radioId = jQuery(this).attr('id');
                        ulRadio += '<li id="' + radioId + '"><input type="' + tagElName + '" name="' + tagElName + '" ' + (jQuery(this).attr("checked") ? 'checked' : '') + '>' + inputEl + '<input name="text" type="text" placeholder="Text" value="' + jQuery(this).parent().text() + '"> <span><i class="fas fa-plus-circle" onclick="addAfterThis(this)"></i><i class="fas fa-minus-circle" onclick="removeThis(this)"></i></span></li>';
                    })
                }
                ulRadio += '</ul>';
                ulRadio += '<label style="float:none;width:auto !important"><input type="checkbox" value="yes" name="multiLinesCheckBoxes" id="multiLinesCheckBoxes" ' + multiLinesCheckBoxes + '>Multi lines checkboxes.</label>';
                //ulRadio += '<br/><label style="float:none;width:auto !important"><input type="checkbox" value="yes" name="keepText">Keep text?</label>';
                jQuery("#radioCheckBox").html(ulRadio);
                jQuery("#checkBoxRadioUlElements").sortable();
            }
            jQuery("#editElValue").val(jQuery(el).val());
        } else {
            jQuery("#editElValue").val(
                jQuery(el).prop("tagName") == "select" ||
                jQuery(el).prop("tagName") == "textarea" ?
                optionToJson(
                    jQuery(el).prop("tagName"),
                    jQuery(el).html()
                ) :
                jQuery(el).attr("value")
            );
        }

        if (jQuery.inArray(tagElName.toLowerCase(), textInputs) > -1) {
            jQuery('#editElInfoDiv').css("display", "block");
            jQuery("#editElInfo").val('')
            if (jQuery(el).nextAll('info').length) {
                info = jQuery(el).nextAll('info').text();
                jQuery("#editElInfo").val(info);
            }
        } else {
            jQuery('#editElInfoDiv').css("display", "none");
        }

        jQuery("#editElId").val(jQuery(el).attr("id"));
        jQuery("#editElPlaceholder").val(
            jQuery(el).attr("placeholder")
        );

        if (jQuery(el).attr("class")) {
            jQuery("#editElClass").val(
                jQuery(el)
                .attr("class")
                .replace(/selectedInputForEdit/, "")
                .replace(/selectedRadioCheckboxForEdit/, "")
                .replace(/selectedElEdited/, "")
                .trim()
            );
        }

        jQuery("#editElStyle").val(jQuery(el).attr("Style"));

        if (tagElName == "form") {
            jQuery("#editElData-error").val(
                jQuery(el).attr("data-error")
            );
            jQuery("#editElAction").val(jQuery(el).attr("action"));
            jQuery("#editElEnctype").val(jQuery(el).attr("enctype"));
            jQuery("#editElMethod").val(jQuery(el).attr("method"));
            jQuery("#editElOnsubmit").val(jQuery(el).attr("onsubmit"));
            jQuery("#editElOnreset").val(jQuery(el).attr("onreset"));
        } else {
            if (jQuery(el).attr("data-required"))
                jQuery("#editElDataRequired").prop("checked", true);
            else jQuery("#editElDataRequired").prop("checked", false);

            if (jQuery(el).attr("checked"))
                jQuery("#editChecked").prop("checked", true);
            else jQuery("#editChecked").prop("checked", false);

            jQuery("#editElTabindex").val(
                jQuery(el).attr("data-mce-tabindex")
            );
            jQuery("#editElOnclick").val(jQuery(el).attr("Onclick"));
            jQuery("#editElUserType").val(
                jQuery(el).attr("data-usertype")
            );
            if (jQuery(el).attr("data-available"))
                jQuery("#editElDataAvailable").prop("checked", true);
            else jQuery("#editElDataAvailable").prop("checked", false);

        }
    }

    function updateEditElement() {
        el = parent.selectedElForEdit;
        var elName = jQuery("#editElName").val();
        var tagElName = jQuery("#tagElName").val();
        fixedName = parent.fixElName(elName, 1);

        if (jQuery("#tagElType") && (jQuery("#tagElType").val() == 'radio' || jQuery("#tagElType").val() == 'checkbox') && jQuery("#editElValue").val().trim() == '') {
            jQuery("#editElValue").val(jQuery(el).parent().text().replace(/:/g, '').trim());
        }

        jQuery("#editElName").val(fixedName);
        editor.dom.setAttrib(el, "name", jQuery("#editElName").val());

        if (jQuery("#editElId").val().trim() != '')
            elID = jQuery("#editElId").val()
        else
            elID = jQuery("#editElName").val().trim();
        if (jQuery("#tagElType").val() == 'radio') {
            elID += "_" + jQuery("#editElValue").val().trim()
        }

        elID = elID.replace(/  /g, " ")
            .replace(/\]/g, " ")
            .replace(/\[/g, " ")
            .replace(/-/g, " ")
            .replace(/_/g, " ");

        elID = elID.replace(/(?:^|\s)\w/g, function(match) {
            return match.toUpperCase();
        });
        elID = elID.replace(/ /g, '-');
        jQuery("#editElId").val(elID);


        editor.dom.setAttrib(el, "id", jQuery("#editElId").val());
        editor.dom.setAttrib(el, "class", jQuery("#editElClass").val());
        editor.dom.setAttrib(el, "style", jQuery("#editElStyle").val());

        if (tagElName == "form") {
            jQuery("#EditFormElements")
                .find("input")
                .each(function() {
                    attrName = jQuery(this).attr("id").replace("editEl", "").toLowerCase();
                    editor.dom.setAttrib(el, attrName, jQuery(this).val());
                });
        } else {
            editor.dom.setAttrib(el, "placeholder", jQuery("#editElPlaceholder").val());
            editor.dom.setAttrib(
                el,
                "data-mce-tabindex",
                jQuery("#editElTabindex").val()
            );
            editor.dom.setAttrib(el, "onclick", jQuery("#editElOnclick").val());
            editor.dom.setAttrib(el, "data-userType", jQuery("#editElUserType").val());
            editor.dom.setAttrib(
                el,
                "data-available",
                jQuery("#editElDataAvailable").prop("checked") ? "yes" : ""
            );
            editor.dom.setAttrib(
                el,
                "data-required",
                jQuery("#editElDataRequired").prop("checked") ? "yes" : ""
            );

            if (el.nodeName == "INPUT") {
                elType = jQuery("#tagElType").val() == 'hidden' ? 'hidden-input' : jQuery("#tagElType").val();
                editor.dom.setAttrib(el, "type", elType);
            }
            if (tagElName == "radio" || tagElName == "checkbox") {
                editor.dom.setAttrib(
                    el,
                    "checked",
                    jQuery("#editChecked").prop("checked") ? "yes" : ""
                );
            }


            if (tagElName == "textarea") {
                el.innerHTML = jQuery("#editElValue").val();
            } else if (tagElName == "select") {
                len = el.childElementCount;
                for (var i = len - 1; i >= 0; i--) {
                    el.removeChild(el.childNodes[i]);
                }

                if (jQuery("#editElValue").val().indexOf("[?") >= 0 || jQuery("#editElValue").val().indexOf("<option") >= 0) {
                    el.innerHTML = jQuery("#editElValue").val();
                } else {
                    var options = jQuery("#editElValue").val().trim().split("\n");
                    jQuery(options).each(function(op) {
                        if (this.indexOf("[") >= 0 || this.indexOf("<option") >= 0) {
                            el.append(this);
                        } else {
                            var opt = document.createElement("option");
                            var fields = this.split('|');
                            if (fields.length == 1) {
                                key = '';
                                val = fields[0];
                            } else {
                                key = fields[0];
                                val = fields[1];
                            }
                            if (key === "selected") {
                                opt.setAttribute("selected", true);
                            } else {
                                opt.value = key;
                                opt.text = val;
                            }
                            editor.dom.add(el, opt);
                        }
                    });
                    return false;
                }
            } else {
                if (tagElName == "radio" || tagElName == "checkbox" && jQuery("#checkBoxRadioUlElements").length > 0) {
                    var srNr = 0;
                    selectedActive = editor.getBody();
                    if (parent.blockEditingEnabled == false) {
                        if (jQuery("#checkBoxRadioUlElements li").length == 0) {
                            if (jQuery(el).parent('label').length != 0)
                                jQuery(el).parent('label').remove();
                            else
                                jQuery(el).remove();
                        } else {
                            //first li in #selectedRadioCheckboxForEdit
                            var checkRadioLi = jQuery("#checkBoxRadioUlElements li").first();
                            var text = jQuery(checkRadioLi).find('input[name=text]').val().trim();
                            var checked = jQuery(checkRadioLi).find('input[type=' + tagElName + ']').prop('checked') ? 'checked' : '';
                            var val = jQuery(checkRadioLi).find('input[type=' + tagElName + ']').val().trim();
                            if (jQuery(checkRadioLi).find('input[name=name]').length > 0)
                                var name = jQuery(checkRadioLi).find('input[name=name]').val()
                            else
                                var name = jQuery("#editElName").val();

                            srNr++;
                            if (srNr == 1)
                                required = jQuery("#editElDataRequired").prop("checked") ? ' data-required="yes"' : '';
                            brk = jQuery("#multiLinesCheckBoxes").prop("checked") ? '<br>' : '';

                            updateElID = jQuery(checkRadioLi).prop('id');
                            if (jQuery("#editElValue").val().trim() == '')
                                updateEditElementValue = tagElName + '_' + srNr
                            else
                                updateEditElementValue = jQuery("#editElValue").val().trim();


                            radioCheckBoxNewElement = '<input type="' + tagElName + '" id="' + updateElID + '" name="' + name + '" value="' + updateEditElementValue + '" ' + checked + required + '/> ' + text;
                            jQuery(el).parent().replaceWith('<label>' + radioCheckBoxNewElement + '</label>' + brk);
                        }
                    } else {
                        if (jQuery(el).parents(':eq(1)').find('input[type=' + tagElName + ']').length) {
                            jQuery(el).parents(':eq(1)').find('br').remove();
                            jQuery(el).parents(':eq(1)').find('input[type=' + tagElName + ']').parent().first().before('<div id="temRadioCheckBox"></div>');
                            jQuery(el).parents(':eq(1)').find('input[type=' + tagElName + ']').each(function() {
                                jQuery(this).parent().remove();
                            })
                            brk = jQuery("#multiLinesCheckBoxes").prop("checked") ? '<br>' : ' ';
                        };
                        jQuery("#checkBoxRadioUlElements li").each(function() {

                            var text = jQuery(this).find('input[name=text]').val().trim();
                            var checked = jQuery(this).find('input[type=' + tagElName + ']').prop('checked') ? 'checked' : '';
                            var val = jQuery(this).find('input[type=' + tagElName + ']').val().trim();
                            if (jQuery(this).find('input[name=name]').length > 0)
                                var name = jQuery(this).find('input[name=name]').val()
                            else
                                var name = jQuery("#editElName").val();

                            srNr++;
                            if (srNr == 1)
                                required = jQuery("#editElDataRequired").prop("checked") ? ' data-required="yes"' : '';

                            updateElID = jQuery(this).prop('id');
                            radioCheckBoxNewElement = '<input type="' + tagElName + '" id="' + updateElID + '" name="' + name + '" value="' + tagElName + '_' + srNr + '" ' + checked + required + '/> ' + text;
                            jQuery(selectedActive).find("#temRadioCheckBox").before(brk + '<label>' + radioCheckBoxNewElement + '</label>');
                        })
                    }
                    jQuery(selectedActive).find("#temRadioCheckBox").before(brk);
                    jQuery(selectedActive).find("#temRadioCheckBox").remove();

                } else {
                    editor.dom.setAttrib(el, "value", jQuery("#editElValue").val());
                }

                if (jQuery.inArray(tagElName.toLowerCase(), textInputs) > -1) {
                    info = jQuery("#editElInfo").val().replace(/\n/g, "<br>");
                    if (info.trim() != '') {
                        if (jQuery(el).nextAll('info').length > 0)
                            jQuery(el).nextAll('info').html(info);
                        else
                            jQuery(el).after('<br><info>' + info + '</info>');
                    } else {
                        jQuery(el).nextAll('br').remove();
                        jQuery(el).nextAll('info').remove();
                    }
                }
            }
        }

        if (oldClass.trim() != '')
            jQuery(el).addClass(oldClass);
        jQuery(el).addClass("selectedElEdited");
        elName = jQuery("#editElName").val();
        jQuery("#updateEditElementForm").trigger("reset");

        if (saveAfterUpdate == true)
            parent.doAct("save")

        saveAfterUpdate = false;
        parent.finishElementEdit();
        return false;
    }

    function insertFormAction(obj) {
        jQuery("#editElOnclick").val("")
        val = obj.value;
        //get text of the selected option
        text = jQuery("#formList option:selected").text();
        if (val != '') {
            jQuery("#editElValue").val(text);
            jQuery("#editElOnclick").val('location="/applications/?inc=form&foid=' + val + '"');
        }
    }

    function getListOfForms() {
        jQuery("#formsList").html('');
        if (jQuery("#editElActionType").prop("checked")) {
            //get list of form from applications & forms list using ajax
            jQuery.ajax({
                url: 'forms-list.php?act=get-form-list',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.success) {
                        jQuery("#editElActionType").prop("checked", true);
                        jQuery("#formsList").html('<select id="formList" size="1" style="text-transform:capitalize" onchange="insertFormAction(this)"></select>');
                        jQuery("#formList").html('<option value="">Select a form</option>');
                        jQuery.each(data.data, function(key, value) {
                            jQuery("#formList").append('<option value="' + value.foid + '">' + value.form_name + '</option>');
                        });
                    } else {
                        jQuery("#editElActionType").prop("checked", false);
                        jQuery("#formsList").html('');
                        alert(data.message);
                    }
                },
                error: function(data) {
                    jQuery("#editElActionType").prop("checked", false);
                    jQuery("#formsList").html('');
                    alert(data.message);
                }
            });
        }
    }
</script>