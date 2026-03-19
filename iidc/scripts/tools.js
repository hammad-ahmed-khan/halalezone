var docLocation = "";
var iframeLocation = "";
var iframeHeight = 0;
var helpIsOn = false;
if (typeof $ == "undefined") {
  document.write(
    "<scr" + 'ipt src="' + prog_www + '/cms_js/jquery.js"></scri' + "pt>"
  );
}
var pageUrls = new Array();
function switchPage(url) {
  var target = pageUrls[url].split(";");
  if (target[1] == "_blank") window.open(target[0], "childWin", "");
  else document.location.href = target[0];
}
$(window).load(function () {
  /*var aTags=document.getElementsByTagName("a");
	if (aTags.length>0){
	for (b=0;b<=aTags.length-1;b++)
		{
			pageUrls[b] = aTags[b].href+";"+aTags[b].target;
			aTags[b].target="";
			aTags[b].href = "javascript:switchPage("+b+")";
		}
	}*/
  $(".pageContent").prepend(
    "<iframe src='' name='fIframe' id='fIframe' style='position:fixed;left:-10000px;'></iframe>"
  );
  $("body").on("focus", function (e) {
    $(this).css("overflow", "auto");
  });
});
function doFunction(fc) {
  vars = "";
  if (fc.indexOf("(")) {
    fcParts = fc.split("(");
    fc = fcParts[0];
    vars = fcParts[1].split(")")[0];
  }
  if (typeof window[fc] === "function") {
    setTimeout(function () {
      noError = window[fc](vars);
    }, 100);
  }
}
function do_menu() {
  if ((title = getCookie("page_title"))) jQuery("#page_title").html(title);
  jQuery("ul.hqcMenu li a").click(function () {
    //if (jQuery("ul.hqcMenu li.rootMenu"))
    //title = jQuery("ul.hqcMenu li.rootMenu").text()
    //else
    title = "Home";
    if (jQuery(this).parent().attr("title"))
      title = jQuery(this).parent().attr("title");
    else title = "" + jQuery(this).parent().text();
    setCookie("page_title", title, 1);
    alert(getCookie("page_title"));
  });
}
function do_date() {
  jQuery(".date").datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: dateFormat,
  });
  jQuery(".date").attr("placeholder", "dd.mm.yyyy");
  jQuery(".date").attr("autocomplete", "off");
}
function do_auto_number(obj) {
  if (typeof obj != "undefined") {
    var theNr = 1;
    jQuery(obj + " .aunr").each(function (index, element) {
      jQuery(this).html(theNr++);
    });
  }
}
function get_attrs(obj) {
  var data = {};
  jQuery(obj.attributes).each(function (index, attribute) {
    if (this.name.indexOf("data-") > -1) {
      data[this.name.replace("data-", "")] = attribute.value;
    }
  });
  return data;
}
//get data
function load_html(url, dataHolder) {
  if (url != "" && dataHolder != "") {
    jQuery(dataHolder).html("");
    jQuery.post(url).done(function (data) {
      if (data) {
        jQuery(dataHolder).html(data).css("display", "block");
        post_links();
        form_elements_required();
        load_content();
        iframe_links();
        add_content();
        edit_content();
        do_date();
      }
    });
  }
}

function doLoadPopup(url, attrs, title) {
  jQuery("#popupContent").html("");
  jQuery.post(url, attrs).done(function (data) {
    if (data.trim().length > 0) {
      jQuery("#popupContent").html(data.trim());
      tinymce.remove("textarea");
      do_tinymce_minimum("#popupContent .tinymce");
      setTimeout(function () {
        showDialog("#popupContent", title);
      }, 200);
    }
  });
}

function load_popup() {
  var popupContainer =
    '<div id="popupContent" style="display:none;box-sizing: border-box;padding: 0px !important;height:auto !important"></div>';
  if (!jQuery("#popupContent").length) jQuery("body").append(popupContainer);
  jQuery(".load_popup").click(function () {
    event.preventDefault();
    if (jQuery(this).data("url")) url = jQuery(this).data("url");
    else if (jQuery(this).prop("href")) url = jQuery(this).prop("href");
    else if (typeof actionUrl != "undefined") url = actionUrl;
    else return false;

    if (jQuery(this).prop("title").length > 0)
      title = jQuery(this).prop("title");
    else title = "";
    attrs = get_attrs(this);
    doLoadPopup(url, attrs, title);
  });
}
/*Get form data for edit a record*/
function do_get_data(id, formId, act) {
  var url = jQuery("#" + formId).attr("action");
  if (typeof act == "undefined") act = "get_data";
  jQuery.post(url, { act: act, id: id }).done(function (data) {
    if (data) {
      if (data.indexOf("error:") > -1) {
        alert_message(data.replace("error:", ""));
        return false;
      }
      jQuery("#" + formId + " [type=checkbox]").attr("checked", false);
      elIndex = 0;
      jsonObjs = jQuery.parseJSON("[" + data + "]");
      jQuery.each(jsonObjs, function (objkey, fields) {
        jQuery.each(fields, function (key, val) {
          if (jQuery("#" + formId + " [name=" + key + "]").attr("name")) {
            fieldInput = jQuery("#" + formId + " [name=" + key + "]");
            if (
              jQuery(fieldInput).attr("type") &&
              (jQuery(fieldInput).attr("type") == "checkbox" ||
                jQuery(fieldInput).attr("type") == "radio")
            ) {
              jQuery(
                "#" +
                  formId +
                  " input[name='" +
                  key +
                  "'][type='checkbox'][value='" +
                  val +
                  "']"
              ).prop("checked", true);
              jQuery("#" + formId)
                .find(
                  "input[name='" + key + "'][type='radio'][value='" + val + "']"
                )
                .prop("checked", true);
            } else {
              if (jQuery(fieldInput).attr("data-replace"))
                jQuery(fieldInput)
                  .parent()
                  .html(
                    '<input type="text" name="' +
                      key +
                      '" data-replace="yes" style="display:none"/>' +
                      val
                  );
              else jQuery("#" + formId + " [name=" + key + "]").val(val);
            }
          }
          if (typeof val === "object") {
            if (val.length > 0) {
              jQuery.each(val, function (subkey, subval) {
                if (
                  jQuery("#" + formId + " [name='" + key + "[]']").attr("name")
                ) {
                  fieldInput = jQuery("#" + formId + " [name='" + key + "[]']");
                  if (
                    jQuery(fieldInput).attr("type") &&
                    (jQuery(fieldInput).attr("type") == "checkbox" ||
                      jQuery(fieldInput).attr("type") == "radio")
                  ) {
                    jQuery(
                      "#" +
                        formId +
                        " [name='" +
                        key +
                        "[]'][type='checkbox'][value='" +
                        subval +
                        "']"
                    ).attr("checked", true);
                    jQuery(
                      "#" +
                        formId +
                        " [name='" +
                        key +
                        "[]'][type='radio'][value='" +
                        subval +
                        "']"
                    ).attr("checked", true);
                  } else {
                    jQuery(fieldInput).val(subval);
                  }
                }
              });
            }
            jQuery.each(val, function (subkey, subval) {
              if (
                jQuery(
                  "#" + formId + " [name='" + key + "[" + subkey + "]']"
                ).attr("name")
              ) {
                fieldInput = jQuery(
                  "#" + formId + " [name='" + key + "[" + subkey + "]']"
                );
                if (
                  jQuery(fieldInput).attr("type") &&
                  (jQuery(fieldInput).attr("type") == "checkbox" ||
                    jQuery(fieldInput).attr("type") == "radio")
                ) {
                  jQuery(
                    "#" +
                      formId +
                      " [name='" +
                      key +
                      "[" +
                      subkey +
                      "]'][type='checkbox'][value='" +
                      subval +
                      "']"
                  ).attr("checked", true);
                  jQuery(
                    "#" +
                      formId +
                      " [name='" +
                      key +
                      "[" +
                      subkey +
                      "]'][type='radio'][value='" +
                      subval +
                      "']"
                  ).attr("checked", true);
                } else {
                  jQuery(fieldInput).val(subval);
                }
              }
            });
          }
          if (elIndex == 0) {
            jQuery("#" + formId).prepend(
              '<input type="hidden" class="formIdInput" name="' +
                key +
                '" value="' +
                val +
                '"/>'
            );
          }
          elIndex++;
        });
      });
      if (!jQuery("#" + formId + " [name=act]").length)
        jQuery("#" + formId).prepend(
          '<input type="hidden" name="act" value="update"/>'
        );
    }
  });
}
function replace_values(obj) {
  jQuery(obj + " textarea,input[type=text],select").each(function () {
    jQuery(this).replaceWith(jQuery(this).val());
  });
}
//using this instead of windows poup
/*
function alert_message(msg) {
	top.do_alert_message(msg);
}
  */
function get_el_title(obj) {
  ttl = "";
  if (jQuery(obj).data("title")) ttl = jQuery(obj).data("title");
  else if (jQuery(obj).attr("title")) ttl = jQuery(obj).attr("title");
  else if (
    jQuery(obj).attr("type") &&
    jQuery(obj).attr("type") == "button" &&
    jQuery(obj).val()
  )
    ttl = jQuery(obj).val();
  else if (jQuery(obj).text().trim() != "") ttl = jQuery(obj).text();
  return ttl;
}
function doIframe(obj) {
  url = "";
  tp = "iframe";
  ttl = get_el_title(obj);
  jQuery("#popupContent").html("");
  if (jQuery(obj).attr("data-url")) url = jQuery(obj).attr("data-url");
  else if (jQuery(obj).attr("data-href")) url = jQuery(obj).attr("data-href");
  else if (jQuery(obj).attr("href")) url = jQuery(obj).attr("href");
  if (url.trim() == "") return false;
  if (jQuery(obj).attr("data-width"))
    thisWidth = jQuery(obj).attr("data-width");
  else thisWidth = "800";
  if (jQuery(obj).attr("data-height"))
    thisHeight = jQuery(obj).attr("data-height");
  else thisHeight = "500";
  if (jQuery(obj).data("type") && jQuery(obj).data("type") == "audio") {
    ifrm =
      '<audio controls style="height:40px"><source src="' +
      url +
      '" type="audio/mpeg"></audio>';
    tp = "audio";
  } else if (jQuery(obj).data("type") && jQuery(obj).data("type") == "video") {
    if (url.indexOf("youtube.com") > 0 || url.indexOf("youtu.be") > 0) {
      if (url.indexOf("youtube.com") > 0)
        url = url.replace("watch?v=", "embed/");
      else url = url.replace("youtu.be/", "www.youtube.com/embed/");
      ifrm =
        '<iframe width="560" height="315" src="' +
        url +
        '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    } else {
      ifrm =
        '<video width="320" height="240" controls><source src="' +
        url +
        '" type="video/mp4"></video>';
    }
    tp = "video";
  } else if (jQuery(obj).data("type") && jQuery(obj).data("type") == "image") {
    ifrm =
      '<img src="' +
      url +
      '" style="max-width:' +
      pw +
      ";max-height:" +
      ph +
      '"/>';
    tp = "image";
  } else {
    if (url.indexOf("?") > -1) url += "&ifr=1";
    else url += "?ifr=1";
    url += "&tm=" + new Date().getTime();
    if (jQuery(obj).attr("data-resize")) {
      url += "&noResize=1";
    }
    ifrm =
      '<iframe id="iframePageContent" style="width:' +
      thisWidth +
      "px;height:" +
      thisHeight +
      'px;left: 0px;top: 0px;bottom: 0px;" src="' +
      url +
      '"></iframe>';
  }
  jQuery("#popupContent").html(ifrm);
  if (tp == "iframe") {
    if (jQuery(obj).attr("data-resize")) {
      thisWidth = jQuery("#container").width()-10;
      thisHeight = jQuery(window).height() - 115;
      jQuery("body").css("overflow", "hidden");
    }
  } else if (tp == "image") {
    jQuery("#popupContent img").bind("load", function () {
      if (jQuery(obj).attr("data-resize")) {
        ifObj = jQuery("#popupContentt");
        thisWidth = ifObj.width() + 36;
        thisHeight = ifObj.height() + 52;
      }
    });
  }
  setTimeout(function () {
    jQuery("#iframePageContent").attr(
      "style",
      "width:" + thisWidth + "px;height:" + thisHeight + "px;border:0px;"
    );
    showDialog("#popupContent", ttl);
  }, 500);
  jQuery(".ui-dialog").position({
    my: "center",
    at: "center",
    of: window,
  });
}
function showIframe(he) {
  showDialog("#popupContent");
}

function textareaAutoHeight() {
  jQuery("textarea.auto-height").each(function () {
    this.style.height = "40px";
    if (this.value.trim != "") this.style.height = this.scrollHeight + "px";
  });

  $("textarea.auto-height").on("input", function () {
    this.style.height = "";
    this.style.height = this.scrollHeight + "px";
  });
}

function do_document_ready() {
  //do_menu();
  jQuery("a.button").each(function (index, element) {
    //  jQuery(this).replaceWith('<input type="button" onclick="location=\''+jQuery(this).prop("href")+'\'"  value="'+jQuery(this).text()+'"/>');
  });
  post_links();
  iframe_links();
  jQuery("a[target=iframe],input[data-target=iframe],.iframe").click(function (
    e
  ) {
    doIframe(this);
    return false;
  });
  do_date();
  load_popup();
  do_searable_select();
  jQuery("#pageInclude").css("visibility", "visible");
  // Prevent jQuery UI dialog from blocking focusin
  jQuery(document).on("focusin", function (e) {
    if (
      jQuery(e.target).closest(
        ".mce-container, .moxman-container,.mce-window,.ui-dialog-content "
      ).length
    ) {
      e.stopImmediatePropagation();
    }
  });
  jQuery("form").each(function () {
    jQuery(jQuery(this).prop("elements")).each(function (el) {
      if (jQuery(this).attr("placeholder")) {
        jQuery(this).attr("title", jQuery(this).attr("placeholder"));
      }
    });
  });
  //TODO: add this to the new system
  jQuery(".number").on("keyup", function () {
    jQuery(this).val(
      jQuery(this)
        .val()
        .replace(/[^0-9]/g, "")
        .replace(/(\..*?)\..*/g, "$1")
    );
  });

  /*load html content*/
  jQuery(".load_html").click(function (e) {
    load_html(jQuery(this).attr("data-url"), jQuery(this).attr("data-holder"));
  });
  /*delete record*/
  jQuery(".fa-trash, .fa-trash-alt, .confirm-action,.do-action").click(
    function (e) {
      if (jQuery(this).hasClass("post_this_link")) return false;
      obj = jQuery(this);
      var id = jQuery(this).attr("data-id");
      if (!id) var id = jQuery(this).parent().attr("data-id");
      if (!id) return false;
      if (jQuery(this).attr("data-parent"))
        parent = jQuery(this).closest(jQuery(this).attr("data-parent"));
      else var parent = jQuery(this).closest("tr");
      if (jQuery(this).attr("data-confirm"))
        confirm_action = jQuery(this).attr("data-confirm");
      else confirm_action = "Delete selected item, are you sure?";
      if (jQuery(this).attr("data-save")) url = jQuery(this).attr("data-save");
      if (typeof url == "undefined") {
        if (inc.trim() != "") url = inc;
        if (cur_dir.trim() != "") url = cur_dir;
      }
      if (url.indexOf(".php") == -1) url += "_save.php";
      if (jQuery(this).attr("data-act")) act = jQuery(this).attr("data-act");
      else act = "delete";
      if (jQuery(this).attr("data-vars"))
        url = url + "?" + jQuery(this).attr("data-vars");
      alert_confirm(confirm_action);
      jQuery("button#alertYesBtn").click(function () {
        close_alert();
        if (jQuery(obj).data("test")) {
          url =
            url +
            (url.indexOf("?") == -1 ? "?" : "&") +
            "act=" +
            act +
            "&id=" +
            id;
          window.open(url);
          return false;
        }
        jQuery.post(url, { act: act, id: id }).done(function (data) {
          if (data.trim().length > 0) {
            if (data.indexOf("error:") > -1) {
              alert_message(data.replace("error:", ""));
            } else if (data.indexOf("reload") > -1) {
              location.reload();
            } else {
              jQuery(parent).remove();
            }
          }
        });
      });
    }
  );
  /*change status of a record*/
  jQuery("i.status").click(function (e) {
    obj = jQuery(this);
    var id = jQuery(this).attr("data-id");
    if (!id) var id = jQuery(this).parent().attr("data-id");
    if (!id) return false;
    if (jQuery(this).attr("data-save"))
      cur_dir = jQuery(this).attr("data-save");
    var url = cur_dir + "_save.php";
    if (jQuery(this).attr("data-vars"))
      url = url + "?" + jQuery(this).attr("data-vars");
    if (jQuery(this).attr("data-act")) act = jQuery(this).attr("data-act");
    else act = "change_status";
    if (jQuery(this).hasClass("status")) {
      if (jQuery(this).hasClass("fa-toggle-off")) {
        var status = "active";
        var newClass = "fa fa-toggle-on status-on status";
      } else {
        var status = "not-active";
        var newClass = "fa fa-toggle-off status-on status";
      }
    }
    jQuery
      .post(url, { act: act, status: status, id: id })
      .done(function (data) {
        if (data.trim().length > 0) {
          if (data.indexOf("error:") > -1) {
            alert(data.replace("error:", ""));
          } else {
            if (data.indexOf("class:") > -1) {
              newClass = data.replace("class:", "");
            }
            jQuery(obj).attr("class", newClass);
          }
        }
      });
    return false;
  });
  // jQuery(".fa-arrows-alt")
  //   .closest("table")
  //   .find("td,th")
  //   .each(function (index, element) {
  //     jQuery(element).width(jQuery(element).width());
  //   });
  // jQuery(".fa-arrows-alt").mousedown(function (e) {
  //   if (jQuery(this).attr("data-save"))
  //     cur_dir = jQuery(this).attr("data-save");
  //   var url = cur_dir + "_save.php";
  //   jQuery(jQuery(this).closest("table").find("tbody")).sortable({
  //     stop: function (event, ui) {
  //       var pos = new Array();
  //       jQuery(jQuery(this).closest("table").find(".fa-arrows-alt")).each(
  //         function (index, element) {
  //           pos[jQuery(this).attr("data-id")] = index;
  //         }
  //       );
  //       jQuery.post(url, { act: "reorder", pos: pos }).done(function (data) {
  //         if (data.trim().length > 0) {
  //           if (data.indexOf("error:") > -1) {
  //             alert(data.replace("error:", ""));
  //           } else {
  //             window.location.reload();
  //           }
  //         }
  //       });
  //     },
  //   });
  // });
  jQuery(".popup,.fa-pencil-square-o,.fa-file-text-o").click(function (
    index,
    element
  ) {
    if (jQuery(this).hasClass("edit_content")) return false;
    if (jQuery(this).hasClass("load_popup")) return false;
    if (
      jQuery(this).attr("data-popup-id") ||
      jQuery(this).attr("data-content-id")
    ) {
      var noError = true;
      if (jQuery(this).attr("data-popup-id"))
        obj = "#" + jQuery(this).attr("data-popup-id");
      else obj = "#" + jQuery(this).attr("data-content-id");
      jQuery(obj).css("visibility", "hidden");
      if (jQuery(obj).find("form").attr("id"))
        var formId = jQuery(obj).find("form").attr("id");
      if (jQuery(this).attr("data-id"))
        var thisId = jQuery(this).attr("data-id");
      if (jQuery(this).attr("data-function")) {
        doFunction(jQuery(this).attr("data-function"));
      }
      if (formId) {
        jQuery("#" + formId + " .formIdInput").remove();
        jQuery("#" + formId)[0].reset();
        jQuery(jQuery("#" + formId).prop("elements")).each(function () {
          if (jQuery(this).attr("data-color"))
            jQuery(this).css(
              "background-color",
              jQuery(this).attr("data-color")
            );
        });
      }
      /*Check if edit icon is clicked*/
      if (
        (jQuery(this).hasClass("fa-pencil-square-o") ||
          jQuery(this).attr("data-fillForm")) &&
        thisId &&
        formId
      ) {
        if (jQuery(this).attr("data-act")) act = jQuery(this).attr("data-act");
        else act = "get_data";
        do_get_data(thisId, formId, act);
      }
      if (noError === false) return false;
      var ttl = "popup";
      if (jQuery(this).attr("data-info")) ttl = jQuery(this).attr("data-info");
      else if (jQuery(this).attr("title")) ttl = jQuery(this).attr("title");
      else if (jQuery(this).attr("value")) ttl = jQuery(this).attr("value");
      if (jQuery(obj + " .tinymce-popup").length) {
        do_tinymce(obj + " .tinymce-popup");
      }
      if (jQuery(this).attr("data-content-id")) {
        jQuery("#processContentHolder").css("display", "none");
        jQuery(obj).css("display", "block");
        jQuery(obj).css("visibility", "visible");
        do_tinymce_minimum("#contentHolder textarea.tinymce-popup");
      } else {
        setTimeout(function () {
          jQuery(obj).css("visibility", "visible");
          showDialog(obj, ttl);
        }, 200);
      }
    } else if (jQuery(this).attr("data-url")) {
      url = jQuery(this).attr("data-url");
      document.location = url;
    }
  });
  /*turning textarea to do_tinymce*/
  if (jQuery("textarea.tinymce").length) {
    do_tinymce("textarea.tinymce");
  }
  if (jQuery("textarea.tinymce_minimum").length) {
    do_tinymce_minimum("textarea.tinymce_minimum");
  }
  jQuery("form").each(function (index, element) {
    if (
      jQuery(this).attr("target") &&
      jQuery(this).attr("target") == "pdfIframe"
    ) {
      jQuery(this).submit(function () {
        jQuery("#loading").css("display", "block");
        showPdfFrame();
      });
    }
  });
  jQuery("a").each(function (index, element) {
    if (
      jQuery(this).attr("href") &&
      jQuery(this).attr("href").indexOf("pg=iframe") > -1
    ) {
      jQuery(this).attr("target", "pdfIframe");
      jQuery(this).click(function () {
        jQuery("#loading").css("display", "block");
        showPdfFrame();
      });
    }
    if (
      jQuery(this).attr("target") &&
      jQuery(this).attr("target") == "pdfIframe"
    ) {
      jQuery(this).click(function () {
        jQuery("#loading").css("display", "block");
        showPdfFrame();
      });
    }
  });
  jQuery(".location,.url").each(function (index, element) {
    var url = "";
    if (jQuery(this).attr("data-url") || jQuery(this).attr("data-location")) {
      jQuery(this).click(function () {
        if (jQuery(this).attr("data-url")) url = jQuery(this).attr("data-url");
        else if (jQuery(this).attr("data-location"))
          url = jQuery(this).attr("data-location");
        if (url.trim != "") document.location = url;
      });
    }
  });

  if (
    jQuery("input.search").length &&
    jQuery("input.search").attr("data-list")
  ) {
    jQuery("input.search").keyup(function (e) {
      var list = "#" + jQuery(this).attr("data-list");
      var search = jQuery(this).val();
      jQuery(list + " li").each(function (index, element) {
        if (jQuery(this).text().toLowerCase().indexOf(search) > -1)
          jQuery(this).css("display", "block");
        else jQuery(this).css("display", "none");
      });
    });
  }
  textareaAutoHeight();
}
jQuery(document).ready(function (e) {
  do_document_ready();
});
function do_tinymce(tiny) {
  tinyMCE.remove();
  jQuery(tiny).parent(this).css({
    backgroundColor: "#fff",
    border: "0px",
    padding: "0px",
    visibility: "hidden",
  });
  jQuery(tiny).css("visibility", "hidden");
  setTimeout(function () {
    tinymce.init({
      selector: tiny,
      mode: "textareas",
      branding: false,
      remove_script_host: true,
      relative_urls: false,
      force_br_newlines: true,
      force_p_newlines: false,
      relative_urls: 0,
      remove_script_host: 0,
      forced_root_block: "",
      verify_html: false,
      height: jQuery(tiny).height(),
      plugins: [
        "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons template textcolor paste textcolor colorpicker textpattern",
      ],
      toolbar1:
        "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect fullscreen",
      toolbar2:
        "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
      toolbar3:
        "table | hr removeformat | subscript superscript | charmap emoticons | print | ltr rtl | visualchars visualblocks nonbreaking template pagebreak restoredraft mybutton myFormView",
      menubar: false,
      toolbar_items_size: "small",
      init_instance_callback: function () {
        window.setTimeout(function () {
          jQuery(tiny).css("visibility", "visible");
          jQuery(tiny).parent(this).css("visibility", "visible");
        }, 1000);
      },
    });
  }, 250);
}
function do_tinymce_minimum(tiny) {
  if (typeof tiny == "undefined") tiny = ".tinymce_minimum";
  jQuery(tiny).css("visibility", "hidden");
  setTimeout(function () {
    tinymce.init({
      selector: tiny,
      mode: "textareas",
      branding: false,
      remove_script_host: false,
      relative_urls: false,
      force_br_newlines: true,
      force_p_newlines: false,
      forced_root_block: "",
      verify_html: false,
      plugins: [
        "advlist autolink lists link image charmap print preview anchor textcolor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste code help wordcount",
      ],
      toolbar:
        "insert | undo redo | table formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat code",
      menubar: false,
      toolbar_items_size: "small",
      init_instance_callback: function () {
        window.setTimeout(function () {
          jQuery(tiny).css("visibility", "visible");
          jQuery(tiny).parent(this).css({ visibility: "visible" });
        }, 1000);
      },
    });
  }, 300);
}
function showPopupDialog(theObj, ttl) {
  theObj = "#" + theObj;
  if (jQuery(theObj).dialog()) jQuery(theObj).dialog("destroy");
  jQuery(theObj).attr({ title: ttl });
  jQuery(theObj).css({ visibility: "hidden", display: "block" });
  window.setTimeout(function () {
    jQuery(theObj).dialog({
      resizable: false,
      autoOpen: true,
      //height:jQuery(theObj).height(),
      //width:jQuery(theObj).width()+25,
      modal: true,
      buttons: {
        Cancel: function () {
          jQuery(this).dialog("close");
        },
      },
      position: { my: "center", at: "center" },
    });
  }, 100);
  jQuery(theObj).css({ visibility: "visible" });
  jQuery(".ui-widget-content").css("height", "auto !important");
}
var dateFormat = "dd.mm.yy";
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function deleteCookie(cname) {
  document.cookie = cname + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}
function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
var docLocation = "";
function checUrlkCookie() {
  if (window.parent.hcp_dir == undefined) return false;
  if (window.parent.hcp_dir != getCookie("hcp_dir")) {
    setCookie("hcp_dir", window.parent.hcp_dir, 365);
  }
}
jQuery(document).mouseenter(function (e) {
  checUrlkCookie();
});
jQuery(window).on("beforeunload", function () {
  checUrlkCookie();
});
function showPdfFrame() {
  jQuery(".contentCover").css("display", "none");
  jQuery("#pageContent").css("display", "none");
  jQuery("#loading").css("display", "block");
  jQuery("#pdfIframe").css("display", "block");
  jQuery("#pageInclude").css("display", "none");
  jQuery("#hideIframe").css("display", "block");
  resizePdfIframe();
}
function hideIframe() {
  jQuery("#hideIframe").css("display", "none");
  jQuery("#pageInclude").css("display", "block");
  jQuery("#pdfIframe").css("display", "none");
  document.pdfIframe.location = "about:blank";
  deleteCookie("iframeSrc");
  if (docLocation != "") document.location = docLocation;
  iframeHeight = 0;
  jQuery("#pageContent").css("display", "block");
  jQuery(".contentCover").css("display", "block");
}
function get_content_height() {
  return (
    window.innerHeight -
    jQuery("#pageHeader").innerHeight() -
    jQuery("#pageFooter").height() +
    5
  );
}
function contentResize() {
  jQuery("#pageContent").css("height", "auto");
}
function resizePdfIframe(h) {
  docHeight = get_content_height();
  if (typeof h != "undefined" && h > docHeight) docHeight = h;
  //jQuery('#pageContent').css('height', docHeight+'px')
  jQuery("#pdfIframe").height(docHeight);
  iframeHeight = docHeight;
  jQuery("#loading").css("display", "none");
}

function closeDialog() {
  jQuery(".ui-icon-closethick").click();
  jQuery(this).closest(".ui-dialog-content").dialog("close");
}

function showDialog(obj, ttl, h) {
  if (jQuery(obj).dialog()) jQuery(obj).dialog("destroy");
  if (h == null) h = jQuery(obj).height();
  w = jQuery(obj).width();
  jQuery(obj).attr({ title: ttl });
  jQuery(obj).dialog({
    resizable: false,
    width: w,
    modal: true,
  });
  if (jQuery(obj).find("form").attr("id")) {
    var formId = jQuery(obj).find("form").attr("id");
    cancelBtn = "Cancel";
    saveBtn = "Save";
    if (jQuery("#" + formId + " input[name=saveBtn]").length)
      saveBtn = jQuery("#" + formId + " input[name=saveBtn]").val();
    if (jQuery("#" + formId + " input[name=cancelBtn]").length)
      cancelBtn = jQuery("#" + formId + " input[name=cancelBtn]").val();
    jQuery(obj).dialog({
      buttons: [
        {
          id: cancelBtn,
          text: cancelBtn,
          click: function () {
            jQuery(this).dialog("close");
          },
        },
        {
          id: saveBtn.replace(" ", "_"),
          text: saveBtn,
          click: function () {
            jQuery("#" + formId).submit();
          },
        },
      ],
    });
  } else {
    jQuery(obj).dialog({
      buttons: {
        Cancel: function () {
          jQuery(this).dialog("close");
        },
      },
    });
  }
  jQuery(".ui-dialog").position({
    my: "center",
    at: "center",
    of: window,
  });
}
//check if hcp is online
function is_online() {
  jQuery.post(hcp_url + "/is_online.php", function (data) {
    if (data == "offline") {
      window.top.location = window.top.location.href;
    }
  });
}
function doSearch(input, list, hideItems) {
  jQuery(input + " input").addClass("search");
  if (typeof input == "undefined" || typeof list == "undefined") return false;
  jQuery(input + " input").focus(function (e) {
    if (typeof hideItems != "undefined")
      jQuery(hideItems).css("display", "none");
    if (jQuery(this).val().length < 2)
      jQuery(list + " tr").css("display", "table-row");
    inputId = jQuery(this).attr("id");
    jQuery(input + " input").each(function (index, element) {
      if (jQuery(this).attr("id") != inputId) jQuery(this).val("");
    });
  });
  jQuery(input + " input").keyup(function (e) {
    srchWht = jQuery(this).val().toLowerCase();
    srch = jQuery(this).attr("id");
    if (srchWht.length > 0) {
      jQuery(list + " tr").css("display", "none");
      srNr = 1;
      jQuery(list + " [data-id=" + srch + "]").each(function (index, element) {
        if (
          jQuery(this)
            .text()
            .toLowerCase()
            .indexOf("" + srchWht + "") != -1
        ) {
          jQuery("span.nr").css("display", "none");
          jQuery(this).closest("[data-sNr]").html(0);
          jQuery(this).closest("tr").find(".aunr").html(srNr++);
          jQuery(this).closest("tr").css({ display: "table-row" });
        }
      });
    } else {
      jQuery(list + " tr").css("display", "table-row");
    }
  });
}
function sortList(list, id, ascDsc) {
  var trs = "";
  var vals = [];
  var sortType = "id";
  jQuery(list + " [data-id=" + id + "]").each(function (index, element) {
    if (jQuery(this).data("sort")) {
      sortType = "sort";
      val = jQuery(this).data("sort");
    } else {
      val = jQuery(this).text().trim().toLowerCase();
    }
    if (vals.includes(val) == false) vals.push(val);
  });
  vals.sort();
  if (ascDsc == "DESC") vals.reverse();
  jQuery.extend(jQuery.expr[":"], {
    containsIN: function (elem, i, match, array) {
      return (
        (elem.textContent || elem.innerText || "")
          .toLowerCase()
          .indexOf((match[3] || "").toLowerCase()) >= 0
      );
    },
  });
  jQuery.each(vals, function (key, value) {
    if (sortType == "sort") {
      jQuery(list + " [data-sort=" + value + "]")
        .closest("tr")
        .each(function (index, element) {
          trs += this.outerHTML;
        });
    } else {
      if (value == "") emOrNot = "empty";
      else emOrNot = "containsIN(" + value + ")";
      jQuery(list + " [data-id=" + id + "]:" + emOrNot)
        .closest("tr")
        .each(function (index, element) {
          trs += this.outerHTML;
        });
    }
  });
  jQuery("#certificateItems").html(trs);
}
/*searchable select element*/
function doSelectSearch(input) {
  if (typeof input == "undefined") return false;
  list = doSearchList = jQuery(input)
    .closest("div")
    .find(".searchOptionsList li");
  srchWht = jQuery(input).val().toLowerCase();
  if (srchWht.length > 1) {
    jQuery(list).css("display", "none");
    jQuery(list).each(function (index, element) {
      if (jQuery(this).text().toLowerCase().indexOf(srchWht) != -1) {
        jQuery(this).css({ display: "block" });
      }
    });
  } else {
    jQuery(list).css("display", "block");
  }
}
function do_searable_select() {
  jQuery("select.searchable").each(function (index, element) {
    var selectName = jQuery(this).prop("name");
    var offset = jQuery(this).offset();
    var width = jQuery(this).width();
    var placeholder = jQuery(this).children("option").filter(":first").text();
    var optionsList = "";
    var inputValue = "";
    if (jQuery(this).attr("class").replace("searable", "").trim() != "")
      inputClass = " " + jQuery(this).attr("class").replace("searable", "");
    else inputClass = "";
    if (jQuery(this).attr("style")) inputStyle = jQuery(this).attr("style");
    else inputStyle = "";
    if (jQuery(this).attr("data-required"))
      inputRequired = 'data-required="yes"';
    else inputRequired = "";
    jQuery(this)
      .children("option")
      .each(function (index, element) {
        liClass = "";
        if (jQuery(this).attr("class")) liClass = jQuery(this).attr("class");
        if (jQuery(this).attr("selected")) {
          liClass += " selected";
          inputValue = jQuery(this).text().replace(/"/g, '');
        } else {
          jQuery(this).remove();
        }
        if (liClass != "") liClass = 'class="' + liClass + '"';
        if (jQuery(this).text() != placeholder)
          optionsList +=
            '<li data-value="' +
            jQuery(this).val() +
            '" ' +
            liClass +
            ">" +
            jQuery(this).text() +
            "</li>";
      });
    var input =
      '<div class="searchInput" style="width:' +
      width +
      'px;position:relative;" data-name="' +
      selectName +
      '" id="searchDiv_' +
      selectName +
      '">' +
      '<input class="searchSelectInput' +
      inputClass +
      '" type="text" value="' +
      inputValue +
      '" style="width:100% !important;' +
      inputStyle +
      '" ' +
      inputRequired +
      ' placeholder="' +
      placeholder +
      '"/>' +
      '<ul class="searchOptionsList alternateOff" style="width:100%;padding:0px;margin:0px">' +
      optionsList +
      "</ul>";
    ("</div>");
    jQuery(this).css({ position: "fixed", top: "-10000" });
    if (jQuery("#searchDiv_" + selectName).length == 0)
      jQuery(this).after(input);
  });
  jQuery(".searchSelectInput").click(function (e) {
    jQuery(this).closest("div").find("ul").css("display", "block");
    jQuery("div.searchInput").css("z-index", "1");
    event.stopPropagation();
  });
  jQuery(".searchSelectInput").keyup(function (e) {
    doSelectSearch(this);
  });
  jQuery("body").click(function (e) {
    jQuery("ul.searchOptionsList").css("display", "none");
    jQuery("ul.searchOptionsList li").css("display", "block");
    jQuery("div.searchInput").css("z-index", "0");
  });
  jQuery(".searchOptionsList li").click(function (e) {
    jQuery(this)
      .closest("div")
      .find("input.searchSelectInput")
      .val(jQuery(this).text());
    objName = jQuery(this).closest("div").data("name");
    selectedObj = jQuery("select[name='" + objName + "']");
    selectedObj.html(
      '<option value="' +
        jQuery(this).data("value") +
        '" selected>' +
        jQuery(this).text() +
        "</option>"
    );
    selectedObj.val(jQuery(this).data("value"));
    jQuery(this).parent("ul").css("display", "none");
    jQuery(this).parent("ul").find("li").removeClass("selected");
    jQuery(this).addClass("selected");
    if (jQuery(selectedObj).prop("onchange"))
      jQuery(selectedObj).trigger("onchange");
  });
}
/*end searchable select element*/
/* Top btn */
function onOffScroll() {
  if (jQuery(document).height() > jQuery(window).height()) {
    jQuery("#toBottom").fadeIn();
  } else {
    jQuery("#toBottom").fadeOut();
  }
  if (jQuery(document).scrollTop() > 50) {
    jQuery("#toTop").fadeIn();
  } else {
    jQuery("#toTop").fadeOut();
  }
}
function gotoTop() {
  jQuery("html, body").animate({ scrollTop: 0 }, 500);
}
function gotoBottom() {
  jQuery("html, body").animate({ scrollTop: jQuery(document).height() }, 500);
}
function resizeWebmail() {
  if (jQuery("#fIframe").is(":visible")) {
    jQuery("#fIframe").css({ height: jQuery(window).height() - 115 });
  }
}
jQuery(window).scroll(function () {
  onOffScroll();
});
jQuery(window).resize(function () {
  onOffScroll();
  resizeWebmail();
});
var top_btn_html =
  "<div id='scrollTopBottom'><topbtn id='toTop' onclick='gotoTop()'><i class='fas fa-arrow-circle-up'></i></topbtn><topbtn id='toBottom' onclick='gotoBottom()'><i class='fas fa-arrow-circle-down'></i></topbtn></div>";
jQuery("document").ready(function () {
  jQuery("div#footer").append(top_btn_html);
  onOffScroll();
});
function showWebmail(obj) {
  jQuery("#adminInfo").html("");
  if (jQuery("#fIframe").is(":visible")) {
    jQuery("#fIframe").css({ display: "none" });
    jQuery(".pageInclude").css("display", "block");
    jQuery(obj).css("color", "inherit");
  } else {
    if (jQuery("#fIframe").prop("src").indexOf("/webmail") == -1) {
      jQuery("#fIframe").prop("src", "/webmail/");
    }
    jQuery("#fIframe").css({
      height: jQuery(window).height() - 115,
      width: "100%",
      border: "0px",
      display: "block",
    });
    jQuery(".pageInclude").css("display", "none");
  }
}
/* Top btn */
function closeNotifications() {
  id = jQuery("#notificationContent").data("id");
  var notificationsUrl = "/system/notifications.php?act=dismiss&notid=" + id;
  jQuery.post(notificationsUrl, function (data) {
    if (data.trim().length > 0) {
      jQuery("#notificationsLive").slideUp(500, function () {
        jQuery("#notificationsMessage").html("").height(0);
        jQuery("#notificationsCount").html("").css("display", "none");
      });
    }
  });
}
function insertNotificationsBox() {
  var theBox =
    '<div style="position:fixed;bottom:0px;width:100%;"><div style="max-width:1366px;margin:0 auto;position:relative;"><div id="notificationsLive" style="display:none"><div class="header"><button type="button" style="padding: 2px 15px;border-radius:0px" onClick="closeNotifications()">Dismiss</button><span>Important Notification</span><span class="cover"></span></div><div id="notificationsMessage"></div></div></div></div>';
  jQuery("body").append(theBox);
}

function get_notifications() {
  var notificationsBox = "#notificationsMessage";
  var notificationsUrl = "/system/notifications.php";
  jQuery.post(notificationsUrl, function (data) {
    if (data.trim().length > 0) {
      insertNotificationsBox();
      jQuery(notificationsBox).html(data);
      if (jQuery(notificationsBox).is(":hidden")) {
        jQuery(notificationsLive).slideDown(500);
      }
    }
  });
}

function checkEmails() {
  var notificationsUrl = "/tools/mail/check-emails.php";
  jQuery.post(notificationsUrl, function (data) {
    if (data.trim().length > 0) {
      var infoDiv = document.getElementById("adminInfo");
      infoDiv.style.display = "block";
      infoDiv.innerHTML = data;
    }
  });
}

function autocomplete_off() {
  if (jQuery("form").length) {
    jQuery("form,input[type=text],input[type=password]").attr(
      "autocomplete",
      "none"
    );
  }
}
function selectListVisible(list, obj) {
  jQuery(list + " li:visible input:checkbox")
    .not(obj)
    .prop("checked", obj.checked);
}
function showSelectedItems(list, obj) {
  itemsList = list + " li";
  jQuery(itemsList).css("display", "list-item");
  if (obj.checked == true) {
    jQuery(itemsList).css("display", "none");
    jQuery(itemsList + " input[type='checkbox']:checked")
      .parents("li")
      .css("display", "list-item");
  }
}
function searchList(input, list, reset, tools) {
  if (typeof input == "undefined" || typeof list == "undefined") return false;
  if (typeof reset !== "undefined" && reset == "reset") {
    jQuery(input).val("");
    jQuery(list + " li").css("display", "list-item");
    jQuery("#inputSelectVisible").prop("checked", false);
    return false;
  }
  inputs =
    '<i class="fas fa-eraser" onclick="searchList(\'' +
    input +
    "','" +
    list +
    "','reset')\"></i> " +
    '<label><input type="checkbox" id="inputSelectVisible" onclick="selectListVisible(\'' +
    list +
    "',this)\" /><b>Select all</b></label>  " +
    '<label><input type="checkbox" id="showSelectedListItems" onclick="showSelectedItems(\'' +
    list +
    "',this)\" /><b>Show selected</b></label>";
  if (typeof tools == "undefined" || tools == true) jQuery(input).after(inputs);
  jQuery(input).keyup(function (e) {
    srchWht = jQuery(this).val().toLowerCase();
    if (srchWht.length > 0) {
      jQuery(list + " li").css("display", "none");
      jQuery(list + " li").each(function (index, element) {
        if (
          jQuery(this)
            .text()
            .toLowerCase()
            .indexOf("" + srchWht + "") != -1
        ) {
          jQuery(this).css({ display: "list-item" });
        }
      });
    } else {
      jQuery(list + " li").css("display", "list-item");
    }
  });
}
function liveSearch(input, submitForm) {
  var typingTimer; //timer identifier
  var doneTypingInterval = 500; //time in ms, 1 second for example
  var $searchInput = jQuery(input);
  //on keyup, start the countdown
  $searchInput.on("keyup", function (e) {
    if (e.which === 13) {
      return false;
    }
    clearTimeout(typingTimer);
    if (jQuery(input).val().length > 3 || jQuery(input).val().length == 0)
      typingTimer = setTimeout(
        jQuery(submitForm).trigger("submit"),
        doneTypingInterval
      );
  });
  //on keydown, clear the countdown
  $searchInput.on("keydown", function (e) {
    if (e.which === 13) {
      return false;
    }
    clearTimeout(typingTimer);
  });
}

function searchList(input, list, reset, tools) {
  if (typeof input == "undefined" || typeof list == "undefined") return false;

  if (typeof reset !== "undefined" && reset == true) {
    jQuery(input).val("");
    jQuery(list + " li").css("display", "list-item");
    jQuery("#inputSelectVisible").prop("checked", false);
    return false;
  }

  inputs =
    '<label><input type="checkbox" id="inputSelectVisible" onclick="selectListVisible(\'' +
    list +
    "',this)\" />Select all</label>  " +
    '<label><input type="checkbox" id="showSelectedListItems" onclick="showSelectedItems(\'' +
    list +
    "',this)\" />Show selected</label></span>";

  if (typeof tools == "undefined" || tools == true) jQuery(input).after(inputs);

  jQuery(input).keyup(function (e) {
    srchWht = jQuery(this).val().toLowerCase();
    if (srchWht.length > 0) {
      jQuery(list + " li").css("display", "none");
      jQuery(list + " li").each(function (index, element) {
        if (
          jQuery(this)
            .text()
            .toLowerCase()
            .indexOf("" + srchWht + "") != -1
        ) {
          jQuery(this).css({ display: "list-item" });
        }
      });
    } else {
      jQuery(list + " li").css("display", "list-item");
    }
  });
}

function copyToClipboard(text) {
  // Create a temporary input element
  const tempInput = document.createElement("input");
  tempInput.value = text;

  // Append the input element to the document body
  document.body.appendChild(tempInput);

  // Select the text inside the input element
  tempInput.select();

  // Copy the selected text to the clipboard
  document.execCommand("copy");

  // Remove the temporary input element
  document.body.removeChild(tempInput);
  close_alert();
  alert_message("Copied to clipboard.");
}

function serializeTable(table) {
  if (typeof table == "undefined") return false;
  serNr = 1;

  if (jQuery(table + " tbody").length != 0) table = jQuery(table + " tbody");

  jQuery(table)
    .find("tr")
    .each(function (e) {
      if (jQuery(this).find("th").length != 0)
        jQuery(this).find("th").first().html(serNr++);
      else if (jQuery(this).find("td").length != 0)
        jQuery(this).find("td").first().html(serNr++);
    });
}

function addNYears(dateStr, n) {
  n = parseInt(n, 10); // Make sure n is an integer
  // Split input "25/03/2025" into parts
  let parts = dateStr.split("/");
  let day = parseInt(parts[0], 10);
  let month = parseInt(parts[1], 10) - 1; // JavaScript months are 0-based
  let year = parseInt(parts[2], 10);

  // Create Date object
  let date = new Date(year, month, day);

  // Add 1 year
  date.setFullYear(date.getFullYear() + n);

  // Format back to "DD/MM/YYYY"
  let newDay = String(date.getDate()).padStart(2, "0");
  let newMonth = String(date.getMonth() + 1).padStart(2, "0");
  let newYear = date.getFullYear();

  return [
    `${newDay}/${newMonth}/${newYear}`,
    `${newYear}-${newMonth}-${newDay}`,
  ];
}
