jQuery("style").append("<style>.tinymce_template{visibility:hidden};</style>");
var fomr_content_type = "html";
var oldCount = 0;
var selectedElForEdit, selectedBlock;
var blockEditingEnabled = true;
var tableBlockEditable = false;
function count_elements(tiny, el) {
  newDate = new Date();
  return (
    newDate.getHours() + "" + newDate.getMinutes() + "" + newDate.getSeconds()
  );
}

function optionToJson(theType, theOptions) {
  if (theOptions.indexOf("[?") >= 0 || theType == "TEXTAREA") {
    return theOptions;
  } else {
    var jsn = [];
    jQuery(theOptions + " option").each(function (index, element) {
      opEl = "{";
      opEl += '"' + jQuery(this).val() + '":"' + jQuery(this).text() + '"';
      if (jQuery(this).attr("selected")) opEl += ',"selected":"selected"';
      opEl += "}";
      jsn += opEl;
    });
    return "[\n" + jsn.replace(/}{/g, "},\n{") + "\n]";
  }
}

function get_type(el) {
  elName = jQuery(el.target).prop("tagName");
  if ((elType = jQuery(el.target).attr("type"))) {
    return elType.replace("-input", "").replace("-start", "").toUpperCase();
  } else {
    return elName;
  }
}
function fixElName(elName, len) {
  if (typeof len == "undefined") len = 1;

  elName = elName.replace(/-/g, "_").replace(/ /g, "_").replace(/__/g, "_");
  if (elName.indexOf("[") != -1) {
    elNameBase = elName.substring(0, elName.indexOf("[")).toLowerCase();
    elNameToShorten = elName.substring(elName.indexOf("["), elName.length);
    if (
      elNameToShorten.length > 40 &&
      (elNameToShorten.indexOf(" ") ||
        elNameToShorten.indexOf("-") != -1 ||
        elNameToShorten.indexOf("_") != -1)
    ) {
      elNameToShortens = elNameToShorten.replace(/\]\[/g, "]_[").split("_");
      if (elNameToShortens.length > 1) {
        // console.log(elNameToShortens);
        elNameToShortens = elNameToShortens.map(function (el) {
          if (el[0] == "[" && el[el.length - 1] == "]") {
            if (el.length > 22) return el.substring(0, len + 2) + "]";
            else return el;
          } else if (el[0] == "[") return el.substring(0, len + 2);
          else if (el[el.length - 1] == "]" && el.length > len + 1)
            return el.substring(0, len + 1) + "]";
          else return el.substring(0, len + 1);
        });
        elNameToShortens = elNameToShortens
          .join(" ")
          .replace(/(?:^|\s)\w/g, function (match) {
            return match.toUpperCase();
          });
        elName = elNameBase + elNameToShortens.replace(/ /g, "");
        //jQuery("#editElId").val("");
      }
    } else {
      elName = elNameBase + elNameToShorten;
    }
  } else {
    elName = elName.toLowerCase();
  }
  return elName;
}

function updateFormSource(editor, id) {
  content = window.tinyMceContent.getValue();
  jQuery("#tinyMceContentDiv").remove();
  jQuery(id).toggle();
  tinymce.get(editor.id).setContent(content);
  jQuery("div#tinyMceContentDiv").removeClass("tinyMceContentDiv");
  jQuery("#saveButtonsTools").css("display", "block");
  jQuery("body").css("overflow", "auto");
}

function cancelEditForm(id) {
  jQuery("#tinyMceContentDiv").remove();
  jQuery(id).toggle();
  jQuery("div#tinyMceContentDiv").removeClass("tinyMceContentDiv");
  jQuery("#saveButtonsTools").css("display", "block");
  jQuery("body").css("overflow", "auto");
}

function editFormSource(content, obj) {
  id = jQuery("#" + obj.id)
    .parent()
    .find(".mce-tinymce")
    .attr("id");
  jQuery("div#" + id).toggle();
  jQuery("#" + obj.id).before(
    '<div id="tinyMceContentDiv"><div style="overflow:hidden"><span class="footer"><input type="button" onclick="updateFormSource(' +
      obj.id +
      "," +
      id +
      ')" value="Update source"/><input type="button" onclick="cancelEditForm(' +
      id +
      ')" value="Cancel"/></span><h4 class="title" class="margin:0px;padding:0px">Edit form content</h4></div><div id="tinyMceContent" class="tinyMceContent"></div></div>'
  );
  window.tinyMceContent = monaco.editor.create(
    document.getElementById("tinyMceContent"),
    {
      language: "html",
      automaticLayout: true,
      value: content,
      wordWrap: "on",
      bracketPairColorization: true,
      minimap: {
        enabled: false,
      },
    }
  );
  jQuery("#saveButtonsTools").css("display", "none");
  jQuery("div#tinyMceContentDiv").addClass("tinyMceContentDiv");
  jQuery("body").css("overflow", "hidden");
}

function cleanText(editor, obj) {
  content = tinymce
    .get(editor.id)
    .getContent()
    .replace(/\s|&nbsp;/g, " ")
    .replace(/<strong> <\/strong>|<\/strong><strong>|<p><\/p>/g, "");

  tinymce.get(editor.id).setContent(content);
  content = tinymce.get(editor.id).getContent();
  content = "<div id='cleanTextDiv'>" + content + "</div>";

  jQuery(content)
    .find("p,strong,span")
    .each(function () {
      if (jQuery(this).text().trim() == "") {
        thisEl = jQuery(this).prop("outerHTML");
        content = content.replace(thisEl, "");
      }
    });

  jQuery(content)
    .find("table,th,td")
    .each(function () {
      content = content.replace(
        jQuery(this).prop("outerHTML"),
        jQuery(this)
          .removeAttr("width")
          .removeAttr("height")
          .removeAttr("style")
          .prop("outerHTML")
      );
    });

  jQuery(content)
    .find("td")
    .each(function () {
      content = content.replace(
        jQuery(this).prop("outerHTML"),
        jQuery(this)
          .prop("outerHTML")
          .replace(/<p>|<\/p>/, "")
          .replace(/<p>/g, "<br>")
          .replace(/<\/p>/g, "")
      );
    });
  content = jQuery(content).html();

  tinymce.get(editor.id).setContent(content);
}

function uploadMedia() {
  loadPopup("upload-media.php", "Upload documents & images");
}
function finishElementEdit() {
  jQuery("#tinyMceFormButtonsTab").trigger("click");
  jQuery("#tinyMceFormTabs span").css("display", "");
  jQuery("#tinyMceFormTabs h3").css("display", "none");
  jQuery("#editBodyElementBtn").css("display", "");
}

function doRemoveThisBlock() {
  jQuery(selectedBlock).remove();
}

function removeThisBlock() {
  alert_confirm("Delete selected block", "doRemoveThisBlock();");
}

function getElIdNumber() {
  newDate = new Date();
  return (
    newDate.getHours() + "" + newDate.getMinutes() + "" + newDate.getSeconds()
  );
}


function do_form_tinymce(tiny) {
  tinymce.init({
    selector: tiny,
    init_instance_callback: function (editor) {
      // Shortcuts and useful things go here.
    },
    branding: false,
    cleanup: false,
    body_id: "form_tinymce_body",
    entity_encoding: "name",
    forced_root_block: "div",
    force_br_newlines: false,
    force_p_newlines: true,
    paste_auto_cleanup_on_paste: true,
    table_tab_navigation: true,
    keep_styles: true,
    remove_script_host: true,
    relative_urls: false,
    element_format: "html",
    table_toolbar: "",
    table_resize_bars: false,
    table_style_by_css: false,
    table_sizing_mode: false,
    object_resizing: false,
    table_default_attributes: {},
    table_default_styles: {},
    table_appearance_options: true,
    image_title: true,
    automatic_uploads: true,
    remove_redundant_brs: true,
    file_picker_types: "image file",
    image_list: "/admin/forms/images-list.php",
    link_list: "/admin/forms/documents-list.php",
    // images_upload_url: "/admin/forms/image_save.php",
    // file_picker_callback: function (cb, value, meta) {
    //   var input = document.createElement("input");
    //   input.setAttribute("type", "file");
    //   input.setAttribute("accept", "image/*");

    //   input.onchange = function () {
    //     var file = this.files[0];
    //     var reader = new FileReader();
    //     reader.onload = function () {
    //       var id = "blobid" + new Date().getTime();
    //       var blobCache = tinymce.activeEditor.editorUpload.blobCache;
    //       var base64 = reader.result.split(",")[1];
    //       var blobInfo = blobCache.create(id, file, base64);
    //       blobCache.add(blobInfo);

    //       cb(blobInfo.blobUri(), { title: file.name });
    //     };
    //     reader.readAsDataURL(file);
    //   };
    //   input.click();
    // },
    table_class_list: [
      { title: "None", value: "" },
      { title: "TD, TH underline", value: "alternateOff" },
      { title: "Alternate", value: "alternate" },
      { title: "Alternate with Hover", value: "alternateOn" },
    ],
    table_cell_class_list: [
      { title: "None", value: "" },
      { title: "Arabic", value: "arabic" },
      { title: "English", value: "english" },
      { title: "Title", value: "title" },
      { title: "TH", value: "th" },
      { title: "TH SNR", value: "th srn" },
      { title: "TD", value: "td" },
      { title: "SRN", value: "srn" },
    ],
    table_class: [{ title: "Class", value: "" }],
    keep_styles: false,
    valid_elements: "*[*]",
    extended_valid_elements: "fieldset, legend",
    code_dialog_width: 1000,
    plugin_preview_width: 1000,
    content_css: [
      "./js-css/tinymce.css",
      "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css",
    ],
    height: 500,
    plugins: [
      "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak",
      "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
      "table contextmenu directionality emoticons template textcolor paste colorpicker textpattern",
    ],

    toolbar1:
      "fullscreen bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote insertfile| styleselect formatselect fontselect fontsizeselect cut copy paste | searchreplace | undo redo | uploadMedia link unlink anchor image media",
    toolbar2:
      "forecolor backcolor | removeformat hr subscript superscript charmap | ltr rtl | visualchars visualblocks nonbreaking pagebreak table | mybutton editFormSource getShortCodes getDocLinks getDocx cleanText viewTheForm viewTheProductionForm saveTheForm ",
    menubar: false,
    toolbar_items_size: "small",
    setup: function (editor) {
      editor.on("click", function (e) {
        jQuery(editor.getBody()).find(".editBlockTools").remove();
        if (jQuery("#tinyMceFormTools").length > 0) {
          e.stopPropagation();
          var el = e.target;
          var elName = e.target.nodeName.toLowerCase();
          var els = ["input", "select", "button", "textarea"];

          jQuery(selectedElForEdit)
            .removeClass("selectedInputForEdit")
            .removeClass("selectedRadioCheckboxForEdit")
            .removeClass("selectedElEdited");

          if (
            jQuery(selectedElForEdit).attr("class") &&
            jQuery(selectedElForEdit).attr("class").trim() == ""
          )
            editor.dom.setAttrib(selectedElForEdit, "class", "");

          if (jQuery.inArray(elName, els) > -1) {
            if (
              jQuery(el).attr("type") &&
              (jQuery(el).attr("type") == "radio" ||
                jQuery(el).attr("type") == "checkbox")
            )
              jQuery(el).addClass("selectedRadioCheckboxForEdit");
            else jQuery(el).addClass("selectedInputForEdit");
            selectedElForEdit = el;
            document
              .getElementById("tinyMceFormEditElementIframe")
              .contentWindow.selectedEl(el);
            jQuery("#tinyMceFormEditElementTab").trigger("click");
            jQuery("#tinyMceFormTabs span").css("display", "none");
            jQuery("#tinyMceFormTabs h3").css("display", "block");
            jQuery("#editBodyElementBtn").css("display", "none");
            return false;
          } else {
            jQuery("#tinyMceFormButtonsTab").trigger("click");
            jQuery("#tinyMceFormTabs span").css("display", "");
            jQuery("#tinyMceFormTabs h3").css("display", "none");
            jQuery("#editBodyElementBtn").css("display", "");
          }
          var blocks = ["h1", "h2", "h3", "h4", "h5", "h6", "p", "div"];
          //remove class from all tinymce elements
          jQuery(editor.getBody())
            .find(blocks.join(",") + ",tr,.selectedEl")
            .each(function () {
              jQuery(this)
                .removeClass("selectedBlock")
                .removeClass("selectedEl");
            });

          if (jQuery.inArray(elName, blocks) > -1) {
            if (elName == "td" || elName == "th") {
              selectedBlock = jQuery(el).parents("tr");
            } else {
              selectedBlock = el;
            }
            // console.log(selectedBlock);
          } else {
            //find the closest block
            if (jQuery(el).parents("tr").length > 0) {
              var closestBlock = jQuery(el).parents("tr");
              selectedBlock = closestBlock;
            } else {
              var closestBlock = jQuery(el).parents(blocks.join(","));
              if (closestBlock.length > 0) {
                selectedBlock = closestBlock;
              }
            }
          }

          if (
            typeof blockEditingEnabled != "undefined" &&
            blockEditingEnabled == true
          ) {
            jQuery(selectedBlock).addClass("selectedBlock");
            jQuery(selectedBlock).append(
              '<span class="editBlockTools"><i class="far fa-trash-alt" onclick="parent.removeThisBlock();"></i></span>'
            );
          }
        }
      });

      editor.on("DblClick", function (e) {
        if (jQuery("#tinyMceFormTools").length > 0) return false;
        e.stopPropagation();
        var el = e.target;
        var els = ["input", "select", "button", "label", "textarea"];
        var elName = e.target.nodeName.toLowerCase();

        var tableColumns = "";
        if (table_columns.length > 0) {
          totOptions = [
            { text: "Default name", value: jQuery(e.target).attr("name") },
          ];

          tableColumns = table_columns.split(",");

          jQuery(tableColumns).each(function (index, element) {
            totOptions.push({ text: element, value: element });
          });

          tableColumns = {
            type: "listbox",
            name: "table_columns",
            id: "table_columns",
            label: "Table columns",
            onselect: function (e) {
              jQuery("#EditName").val(this.value());
            },
            values: totOptions,
          };
        }

        if (get_type(e) == "FORM") {
          var inputs = [
            tableColumns,
            {
              type: "textbox",
              name: "tagName",
              id: "tagName",
              label: "Tag Type",
              value: "FORM",
              size: "50",
              style: "border:0px;",
              disabled: "disabled",
            },
            {
              type: "textbox",
              name: "name",
              id: "editName",
              label: "Name",
              value: jQuery(e.target).attr("name"),
              size: "50",
            },
            {
              type: "textbox",
              name: "id",
              id: "editId",
              label: "Id",
              value: jQuery(e.target).attr("id"),
              size: "50",
            },
            {
              type: "textbox",
              name: "class",
              id: "editClass",
              label: "Class",
              value: jQuery(e.target).attr("class"),
              size: "50",
            },
            {
              type: "textbox",
              name: "action",
              id: "editAction",
              label: "Action",
              value: jQuery(e.target).attr("action"),
              size: "50",
            },
            {
              type: "textbox",
              name: "enctype",
              id: "editEnctype",
              label: "Enctype",
              value: jQuery(e.target).attr("enctype"),
              size: "50",
            },
            {
              type: "textbox",
              name: "method",
              id: "editMethod",
              label: "Method",
              value: jQuery(e.target).attr("method"),
              size: "50",
            },
            {
              type: "textbox",
              name: "onsubmit",
              id: "editOnsubmit",
              label: "Onsubmit",
              value: jQuery(e.target).attr("onsubmit"),
              size: "50",
            },
            {
              type: "textbox",
              name: "onreset",
              id: "editOnreset",
              label: "Onreset",
              value: jQuery(e.target).attr("onreset"),
              size: "50",
            },
            {
              type: "textbox",
              name: "data-error",
              id: "editData-error",
              label: "Data-error",
              value: jQuery(e.target).attr("data-error"),
              size: "50",
            },
          ];
        } else {
          var inputs = [
            tableColumns,
            {
              type: "textbox",
              name: "tagName",
              id: "tagName",
              label: "Tag Type",
              value: get_type(e),
              size: "50",
              style: "border:0px;",
              disabled: "disabled",
            },
            {
              type: "textbox",
              name: "name",
              id: "EditName",
              label: "Element name",
              value: jQuery(e.target).attr("name"),
              size: "50",
            },
            {
              type: "textbox",
              name: "value",
              id: "EditValue",
              label: "Value / Options (json)",
              value:
                jQuery(e.target).prop("tagName") == "SELECT" ||
                jQuery(e.target).prop("tagName") == "TEXTAREA"
                  ? optionToJson(
                      jQuery(e.target).prop("tagName"),
                      jQuery(e.target).html()
                    )
                  : jQuery(e.target).attr("value"),
              size: "100",
              multiline: true,
              style: "height:100px;width:450px",
            },
            {
              type: "textbox",
              name: "id",
              id: "EditId",
              label: "ID",
              value: jQuery(e.target).attr("id"),
              size: "50",
            },
            {
              type: "textbox",
              name: "placeholder",
              id: "EditPlaceholder",
              label: "Placeholder",
              value: jQuery(e.target).attr("placeholder"),
              size: "50",
            },
            {
              type: "textbox",
              name: "class",
              id: "EditClass",
              label: "Class",
              value: jQuery(e.target).attr("class"),
              size: "50",
            },
            {
              type: "textbox",
              name: "style",
              id: "EditStyle",
              label: "Style",
              value: jQuery(e.target).attr("Style"),
              size: "50",
            },
            {
              type: "textbox",
              name: "data-required",
              type: "radio",
              id: "EditDataRequired",
              label: "Required",
              value: "yes",
              checked: jQuery(e.target).attr("data-required"),
            },
            {
              type: "textbox",
              name: "data-error",
              id: "editData-error",
              label: "Data-error",
              value: jQuery(e.target).attr("data-error"),
              size: "50",
            },
            {
              type: "textbox",
              name: "tabindex",
              id: "editTabindex",
              label: "Tabindex",
              value: jQuery(e.target).attr("data-mce-tabindex"),
              size: "50",
            },
            {
              type: "textbox",
              name: "onclick",
              id: "EditOnclick",
              label: "Onclick",
              value: jQuery(e.target).attr("Onclick"),
            },
            {
              type: "textbox",
              name: "user-type",
              id: "EditUserType",
              label: "User type",
              placeholder:
                "One or more separated by comma(admin,super_admin,office,client)",
              value: jQuery(e.target).attr("data-usertype"),
            },
            {
              type: "textbox",
              name: "data-available",
              type: "radio",
              id: "EditDataAvailable",
              label: "Always available",
              value: "yes",
              checked: jQuery(e.target).attr("data-available"),
            },
          ];

          if (
            jQuery(e.target).prop("type") &&
            (jQuery(e.target).prop("type") == "radio" ||
              jQuery(e.target).prop("type") == "checkbox")
          ) {
            newEl = {
              type: "textbox",
              name: "Checked",
              type: "radio",
              id: "editChecked",
              label: " Checked",
              value: "Checked",
              checked: jQuery(e.target).attr("Checked"),
            };
            inputs.push(newEl);
          }
        }

        var theTable = "";
        if (jQuery.inArray(elName, els) > -1) {
          /* if (jQuery(e.target).closest("table").length > 0) {
            tbl = jQuery(e.target).closest("table");
            if (jQuery(tbl).prop("id").length > 0) {
              theTable =
                " table(" +
                jQuery(e.target)
                  .closest("table")
                  .attr("id")
                  .replace("mce-item-table", "")
                  .replace(/_/g, " ")
                  .trim() +
                ")";
            } else if (jQuery(tbl).prop("class").length > 0) {
              var theTable =
                " table(" +
                jQuery(e.target)
                  .closest("table")
                  .attr("class")
                  .replace("mce-item-table", "")
                  .replace(/_/g, " ")
                  .trim() +
                ")";
            }
          } */

          editor.windowManager.open({
            title: "Edit " + jQuery(e.target).prop("name") + theTable,
            body: inputs,
            onClose: function (e) {
              tinymce.activeEditor.selection.select(el);
            },
            onsubmit: function (e) {
              if (jQuery("#tagName").val() == "FORM") {
                editor.dom.setAttrib(el, "name", jQuery("#editName").val());
                editor.dom.setAttrib(el, "id", jQuery("#editName").val());
                editor.dom.setAttrib(el, "class", jQuery("#editClass").val());
                editor.dom.setAttrib(el, "action", jQuery("#editAction").val());
                editor.dom.setAttrib(
                  el,
                  "enctype",
                  jQuery("#editEnctype").val()
                );
                editor.dom.setAttrib(el, "method", jQuery("#editMethod").val());
                editor.dom.setAttrib(
                  el,
                  "onsubmit",
                  jQuery("#editOnsubmit").val()
                );
                editor.dom.setAttrib(
                  el,
                  "onreset",
                  jQuery("#editOnreset").val()
                );
                editor.dom.setAttrib(
                  el,
                  "data-error",
                  jQuery("#editData-error").val()
                );
                tinymce.activeEditor.selection.select(el);
              } else {
                var elRequired =
                  jQuery("#EditDataRequired").attr("aria-checked");

                // Insert content when the window form is submitted
                if (elRequired == "true")
                  editor.dom.setAttrib(el, "data-required", "yes");
                else editor.dom.setAttrib(el, "data-required", "");

                var elAvailable =
                  jQuery("#EditDataAvailable").attr("aria-checked");

                // Insert content when the window form is submitted
                if (elAvailable == "true")
                  editor.dom.setAttrib(el, "data-available", "yes");
                else editor.dom.setAttrib(el, "data-available", "");

                editor.dom.setAttrib(
                  el,
                  "name",
                  jQuery("#EditName")
                    .val()
                    .trim()
                    .replace(/ /g, "_")
                    .toLowerCase()
                );
                if (jQuery("#tagName").val() == "TEXTAREA") {
                  el.innerHTML = jQuery("#EditValue").val();
                } else if (jQuery("#tagName").val() == "SELECT") {
                  len = el.childElementCount;
                  for (var i = len - 1; i >= 0; i--) {
                    el.removeChild(el.childNodes[i]);
                  }

                  if (jQuery("#EditValue").val().indexOf("[?") >= 0) {
                    el.innerHTML = jQuery("#EditValue").val();
                  } else {
                    var obj = jQuery.parseJSON(jQuery("#EditValue").val());

                    jQuery(obj).each(function (objkey, fields) {
                      var opt = document.createElement("option");
                      jQuery.each(fields, function (key, val) {
                        if (key === "selected") {
                          opt.setAttribute("selected", true);
                        } else {
                          opt.value = key;
                          opt.text = val;
                        }
                      });
                      editor.dom.add(el, opt);
                    });
                  }
                } else {
                  editor.dom.setAttrib(el, "value", jQuery("#EditValue").val());
                }

                if (jQuery("#EditId").val().trim() == "")
                  elID = jQuery("#EditName")
                    .val()
                    .trim()
                    .replace(/ /g, "_")
                    .replace(/\]/g, "")
                    .replace(/\[/g, "_");
                else
                  elID = jQuery("#EditId")
                    .val()
                    .trim()
                    .replace(/ /g, "_")
                    .replace(/\]/g, "")
                    .replace(/\[/g, "_");

                if (jQuery("#tagName").val() == "RADIO")
                  elID += "_" + jQuery("#EditValue").val();

                editor.dom.setAttrib(el, "id", elID);

                editor.dom.setAttrib(
                  el,
                  "placeholder",
                  jQuery("#EditPlaceholder").val()
                );

                editor.dom.setAttrib(
                  el,
                  "data-userType",
                  jQuery("#EditUserType").val()
                );

                editor.dom.setAttrib(
                  el,
                  "data-mce-tabindex",
                  jQuery("#editTabindex").val()
                );

                if (jQuery("#tagName").val() == "BUTTON") {
                  editor.dom.setAttrib(
                    el,
                    "onclick",
                    jQuery("#EditOnclick").val()
                  );
                }

                editor.dom.setAttrib(el, "class", jQuery("#EditClass").val());
                editor.dom.setAttrib(el, "style", jQuery("#EditStyle").val());
                tinymce.activeEditor.selection.select(el);
              }
            },
          });
        }

        if (e.target.nodeName == "IMG") {
          tinyMCE.activeEditor.execCommand("mceImage");
        }
      }),
        editor.addButton("uploadMedia", {
          type: "button",
          title: "Upload documents & images",
          icon: "upload",
          onclick: function () {
            uploadMedia();
          },
        }),
        editor.addButton("editFormSource", {
          type: "button",
          text: "Edit code",
          icon: "code",
          onclick: function () {
            editFormSource(editor.getContent(), editor);
          },
        }),
        editor.addButton("cleanText", {
          type: "button",
          text: "Clean text",
          icon: "codesample",
          onclick: function () {
            cleanText(editor, this);
          },
        }),
        editor.addButton("viewTheForm", {
          type: "button",
          text: "Preview",
          icon: "preview",
          onclick: function () {
            doAct("view");
          },
        }),
        editor.addButton("viewTheProductionForm", {
          type: "button",
          text: "Production Preview",
          id:"myClients",
          onclick: function () {
           doAct('viewProduction');
          },
        }),
        editor.addButton("mybutton", {
          type: "menubutton",
          text: "Form Element",
          icon: false,
          menu: [
            {
              text: "One Line Text Input",
              onclick: function () {
                count = count_elements(tiny, "text");
                editor.insertContent(
                  '<input type="text" name="text' +
                    count +
                    '" data-required="yes" autocomplete="none"/>'
                );
              },
            },
            {
              text: "Multi lines text (Textarea)",
              onclick: function () {
                count = count_elements(tiny, "textarea");
                editor.insertContent(
                  '<textarea name="textarea' +
                    count +
                    '" data-required="yes"></textarea>'
                );
              },
            },
            {
              text: "Date Input",
              onclick: function () {
                count = count_elements(tiny, "text");
                editor.insertContent(
                  '<input type="date" name="date' +
                    count +
                    '" data-required="yes" autocomplete="none"/>'
                );
              },
            },
            {
              text: "Number Input",
              onclick: function () {
                count = count_elements(tiny, "text");
                editor.insertContent(
                  '<input type="number" name="number' +
                    count +
                    '" data-required="yes" autocomplete="none"/>'
                );
              },
            },
            {
              text: "Hidden Input",
              onclick: function () {
                count = count_elements(tiny, "hidden");
                editor.insertContent(
                  '<input type="hidden-input" name="hidden' +
                    count +
                    '" value="Hiddenvalue' +
                    count +
                    '"/>'
                );
              },
            },
            {
              text: "Dropdown Select Input",
              onclick: function () {
                count = count_elements(tiny, "select");
                editor.insertContent(
                  '<select name="select' +
                    count +
                    '" data-required="yes"><option value="">Please Select</option></select>'
                );
              },
            },
            {
              text: "Upload File Button",
              onclick: function () {
                count = count_elements(tiny, "file");
                editor.insertContent(
                  '<input type="file" name="file[file' +
                    count +
                    '][]" multiple placeholder="file"/>'
                );
              },
            },
            {
              text: "Checkbox Button",
              onclick: function () {
                count = count_elements(tiny, "checkbox");
                label = editor.selection.getContent();
                if (label.trim() == "") label = "CheckBox " + count;
                editor.insertContent(
                  '<label><input type="checkbox" name="checkbox' +
                    count +
                    '" value="' +
                    label +
                    '"/>' +
                    label +
                    "</label>"
                );
              },
            },

            {
              text: "Radio Button",
              onclick: function () {
                label = editor.selection.getContent();
                count = count_elements(tiny, "radio");
                if (label.indexOf("+") == -1) oldCount = count;
                if (label.indexOf("*") != -1) required = ' data-required="yes"';
                else required = "";

                if (label.trim() == "") label = "Radio " + count;

                editor.insertContent(
                  '<label><input type="radio" name="radio' +
                    oldCount +
                    '" value="' +
                    label +
                    '"' +
                    required +
                    "/>" +
                    label.replace("+", "").replace("*", "") +
                    "</label>"
                );
              },
            },
            {
              text: "Action Button",
              onclick: function () {
                count = count_elements(tiny, "button");
                editor.insertContent(
                  '<input type="button" name="button' +
                    count +
                    '" value="Button ' +
                    count +
                    '" onclick=""/>'
                );
              },
            },
            {
              text: "Submit Button",
              onclick: function () {
                editor.insertContent('<input type="submit" value="Submit"/>');
              },
            },
            {
              text: "Reset Button",
              onclick: function () {
                editor.insertContent('<input type="reset" value="Reset"/>');
              },
            },
            {
              text: "Cancel Button",
              onclick: function () {
                editor.insertContent(
                  '<input type="button" value="Cancel" onclick="history.go(-1)"/>'
                );
              },
            },
            {
              text: "Form tag",
              onclick: function () {
                count = count_elements(tiny, "form");
                editor.insertContent(
                  '<input type="form-start" placeholder="form" action="form_save.php" enctype="multipart/form-data" method="post" name="form' +
                    count +
                    '" onsubmit="return post_this_form(this)" data-error="Fields with * are required." autocomplete="off">'
                );
              },
            },
            {
              text: "Closing Form Tag",
              onclick: function () {
                editor.insertContent(
                  '<input type="form-end" placeholder="/form">'
                );
              },
            },
            {
              text: "PHP code",
              onclick: function () {
                editor.insertContent("[? /*type here php codes*/ ?]");
              },
            },
            {
              text: "PDF code",
              onclick: function () {
                editor.insertContent("[pdf /*type here tcpdf codes*/ ]");
              },
            },
          ],
        }),
        editor.addButton("saveTheForm", {
          type: "button",
          text: "save",
          icon: "save",
          onclick: function () {
            doAct("save");
          },
        });
      editor.on("FullscreenStateChanged", function () {
        if (
          jQuery(".mce-fullscreen").length > 0 &&
          jQuery(".mce-edit-area").length != 0
        ) {
          if (jQuery("div#tinyMceFormTools").length > 0) return false;
          jQuery("#the_form_ifr")
            .parent("div")
            .prepend(
              '<div style="float:left;width:28%;overflow:auto" id="tinyMceFormTools">' +
                '<div style="padding:10px 20px;text-align:center;visibility:hidden;position:fixed;top:-1000px" id="tinyMceFormTabs"><h3 style="display:none;text-align:center;font-weight:bold;color:var(--color100) !important">Edit Element</h3><span data-tab="tinyMceFormButtons" class="active" id="tinyMceFormButtonsTab">Add Element</span><span data-tab="tinyMceFormEditElement" id="tinyMceFormEditElementTab" style="position:fixed;top:-10000px">edit</span><span data-tab="tinyMceFormDatabase" id="tinyMceFormDatabaseTab">from data base</span></div>' +
                '<ul style="padding:0px 10px;margin:0px">' +
                '<li id="tinyMceFormButtons" class="tinyMceTab"></li>' +
                '<li id="tinyMceFormEditElement" class="tinyMceTab" style="position:fixed;top:-10000px;height:100%"><iframe id="tinyMceFormEditElementIframe" src="edit-elements.inc.php"/></li>' +
                '<li id="tinyMceFormDatabase" class="tinyMceTab" style="position:fixed;top:-10000px"></li>' +
                '</ul>' +
                '</div><div><div id="MyClientList" style="padding:10px;background:#eee;display:none"></div></div>'
            );
          jQuery("#the_form_ifr").css({
            width: "71.9%",
            float: "left",
            "border-left": "1px solid var(--color20)",
          });
          jQuery("#MyClientList").css("margin-left", "28%");
          // jQuery("#mceu_44-open").click();
          jQuery("#tinyMceFormButtons").load("add-new-element.php");

          jQuery("#tinyMceFormEditElement").height(
            jQuery("#the_form_ifr").height()
          );

          jQuery("#tinyMceFormTabs span").on("click", function () {
            jQuery("#tinyMceFormTabs span").removeClass("active");
            jQuery(this).addClass("active");
            jQuery(".tinyMceTab").css({ position: "fixed", top: "-10000px" });
            jQuery("#" + jQuery(this).data("tab")).css({
              position: "relative",
              top: "0px",
            });
          });
          jQuery("#mceu_44").css("display", "none");
          jQuery("#tinyMceFormTools").height(jQuery("#the_form_ifr").height());
          statusBarID =
            "mceu_" +
            parseInt(
              parseInt(
                jQuery("#tinyMceFormTools")
                  .parent("div")
                  .prop("id")
                  .split("_")[1]
              ) + 1
            );
          jQuery("#" + statusBarID).prepend(
            '<input type="button" style="background:var(--color50)" value="Edit" id="editBodyElementBtn" onclick="editBodyElement()"/>'
          );
        } else {
          stopBlockEditing();
          if (jQuery("#tinyMceFormTools").length > 0) {
            jQuery("#MyClientList").remove();
            jQuery("#tinyMceFormTools").remove();
            jQuery("#editBodyElementBtn").remove();
            jQuery("#the_form_ifr").css({
              width: "100%",
              float: "",
              height: "510px",
              "border-left": "none",
            });
          }
          if (
            jQuery(selectedElForEdit).attr("class") &&
            jQuery(selectedElForEdit).attr("class") == "selectedInputForEdit"
          )
            editor.dom.setAttrib(selectedElForEdit, "class", "");
          else jQuery(selectedElForEdit).removeClass("selectedInputForEdit");
          jQuery("#mceu_44").css("display", "");
        }
      });
    },
    init_instance_callback: function () {
      tinymce.activeEditor.on("ObjectResizeStart", function (e) {});
      window.setTimeout(function () {
        jQuery("#div").show();
      }, 1000);
    },
  });

  jQuery.widget("ui.dialog", jQuery.ui.dialog, {
    _allowInteraction: function (event) {
      return jQuery(".mce-panel:visible").length > 0;
    },
  });
}

jQuery(document).ready(function (e) {
  if (jQuery("textarea.tinymce_template").length) {
    do_form_tinymce("textarea.tinymce_template");
  }
});

function editBodyElement() {
  el = tinymce.activeEditor.selection.getNode();
  selectedElForEdit = el;
  var tagName = jQuery(el).prop("tagName").toLowerCase();
  if (tagName != "body") {
    jQuery("#tinyMceFormEditElementTab").trigger("click");
    document
      .getElementById("tinyMceFormEditElementIframe")
      .contentWindow.selectedTag(el);
  }
}
