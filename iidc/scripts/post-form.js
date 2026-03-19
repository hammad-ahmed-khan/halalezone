/*
post-forms
ayoub media scripts
version: 1.4
*/

var thObj;
var clickedItem;
function do_function(fc) {
  var fcBtn = '<button style="display:none" id="doFunctionBtn" />';
  jQuery("body").append(fcBtn);
  jQuery("#doFunctionBtn").attr("onclick", fc);
  jQuery("#doFunctionBtn").trigger("click");
  jQuery("#doFunctionBtn").remove();
}

function post_links() {
  jQuery(".post_this_link").click(function (event) {
    event.preventDefault();
    clickedItem = jQuery(this);
    if (jQuery(this).attr("data-url")) url = jQuery(this).attr("data-url");
    else if (jQuery(this).attr("href")) url = jQuery(this).attr("href");
    else if (typeof ajaxurl != 'undefined') url = ajaxurl;
    else {
      alert('Action url not nound');
      return false;
    }
    if (jQuery(this).attr("data-vars") && url == ajaxurl)
      url += "?action=nuzb_ajax&" + jQuery(this).attr("data-vars");
    if (jQuery(this).attr("data-confirm"))
      conf = jQuery(this).attr("data-confirm");
    else conf = "";
    thObj = jQuery(this);
    jQuery(".post_form_frame").remove();
    ifrm =
      '<iframe class="post_form_frame" style="position:fixed;left:-50000px" src="' +
      url +
      '"></iframe>';
    if (conf != "") {
      alert_confirm(conf);
      jQuery("button#alertYesBtn").click(function () {
        if (
          jQuery(this).attr("data-target") &&
          jQuery(this).attr("data-target") == "_new"
        )
          window.open(url);
        else jQuery("body").append(ifrm);
        close_alert();
      });
    } else {
      if (
        jQuery(this).attr("data-target") &&
        jQuery(this).attr("data-target") == "_new"
      )
        window.open(url);
      else jQuery("body").append(ifrm);
    }
  });
}
function get_parent(obj) {
  if (jQuery(obj).attr("data-parent")) {
    parent = jQuery(obj).attr("data-parent");
    if (parent[0] != "#" && parent[0] != ".")
      parent = jQuery(obj).closest(parent);
  } else if (jQuery(obj).parents("label"))
    parent = jQuery(obj).parents("label");
  else if (jQuery(obj).parents("span")) parent = jQuery(obj).parents("span");
  else if (jQuery(obj).parents("p")) parent = jQuery(obj).parents("p");
  else if (jQuery(obj).parents("div")) parent = jQuery(obj).parents("div");
  else if (jQuery(obj).parents("td")) parent = jQuery(obj).parents("td");
  else if (jQuery(obj).parents("th")) parent = jQuery(obj).parents("th");
  else if (jQuery(obj).parents("h1")) parent = jQuery(obj).parents("h1");
  else if (jQuery(obj).parents("h2")) parent = jQuery(obj).parents("h2");
  else if (jQuery(obj).parents("h3")) parent = jQuery(obj).parents("h3");
  else if (jQuery(obj).parents("h4")) parent = jQuery(obj).parents("h4");
  else if (jQuery(obj).parents("h5")) parent = jQuery(obj).parents("h5");
  else if (jQuery(obj).parents("h6")) parent = jQuery(obj).parents("h6");
  return parent;
}
function form_elements_required() {
  jQuery("form").each(function (index, element) {
    if (jQuery(element).attr("data-rquiredFields")) {
      el = jQuery(element).attr("data-rquiredFields");
      jQuery(jQuery(element).prop("elements")).each(function () {
        if (jQuery(this).attr("type") && jQuery(this).attr("type") == el) {
          jQuery(this).attr("data-required", "yes");
        }
      });
    }
  });
}
function load_content() {
  jQuery(".load_content,.load_popup").click(function (event) {
    event.preventDefault();
    if (jQuery(this).attr("data-confirm")) {
      alert_confirm(jQuery(this).attr("data-confirm"));
      jQuery("button#alertYesBtn").click(function () {
        do_load_content(this);
      });
      return false;
    } else {
      do_load_content(this);
    }
  });
}
function do_load_content(obj) {
  noError = true;
  if (jQuery(obj).attr("data-url")) url = jQuery(obj).attr("data-url");
  else url = jQuery(obj).attr("href");
  if (jQuery(obj).attr("data-content-holder"))
    contentHolder = jQuery(this).attr("data-content-holder");
  else if (jQuery("#contentHolder")) contentHolder = jQuery("#contentHolder");
  else contentHolder = jQuery(obj).parent();
  if (typeof url === undefined || typeof contentHolder === undefined)
    return false;
  if (jQuery(obj).attr("data-function")) {
    beforeLoadContent = jQuery(obj).attr("data-function");
    vars = "";
    if (beforeLoadContent.indexOf("(")) {
      beforeLoadContentParts = beforeLoadContent.split("(");
      vars = beforeLoadContentParts[1].split(")")[0];
      beforeLoadContent = beforeLoadContentParts[0];
    }
    if (typeof window[beforeLoadContent] === "function") {
      noError = window[beforeLoadContent](vars);
    }
  }
  if (noError === false) return false;
  get_this_data(url, contentHolder);
  if (jQuery(obj).hasClass("")) {
    if (jQuery(obj).attr("data-info")) ttl = jQuery(obj).attr("data-info");
    else if (jQuery(obj).attr("title")) ttl = jQuery(obj).attr("title");
    else if (jQuery(obj).attr("value")) ttl = jQuery(obj).attr("value");
    else ttl = "popup";
    showDateDialog(contentHolder, ttl);
    if (jQuery(contentHolder + " .tinymce-popup").length) {
      do_tinymce(contentHolder + " .tinymce-popup");
    }
  }
  noError = true;
  if (jQuery(this).attr("data-function-after")) {
    afterLoadContent = jQuery(obj).attr("data-function-after");
    vars = "";
    if (afterLoadContent.indexOf("(")) {
      afterLoadContentParts = afterLoadContent.split("(");
      afterVars = afterLoadContentParts[1].split(")")[0];
      afterLoadContent = afterLoadContentParts[0];
    }
    if (typeof window[afterLoadContent] === "function") {
      noError = window[afterLoadContent](afterVars);
    }
  }
  if (noError === false) return false;
}
function iframe_links() {
  jQuery(".iframe_link").click(function (event) {
    event.preventDefault();
    if (jQuery(this).attr("data-url")) url = jQuery(this).attr("data-url");
    else url = jQuery(this).attr("href");
    if (typeof url === undefined) return false;
    jQuery("#pdfIframe").attr("src", "");
    jQuery("#pdfIframe").attr("src", url + "&pg=iframe");
    showPdfFrame();
  });
}
function hideShow_content_btns(dowhat) {
  if (dowhat == "hide")
    jQuery(".add_content,.edit_content").css("display", "none");
  else jQuery(".add_content,.edit_content").css("display", "inline-block");
}
function add_content() {
  if (typeof obj === undefined) return false;
  jQuery(".add_content").click(function (event) {
    event.preventDefault();
    noError = true;
    if (jQuery(this).attr("data-url")) url = jQuery(this).attr("data-url");
    else url = jQuery(this).attr("href");
    parent = get_parent(jQuery(this));
    jQuery.post(url, {}, function (data) {
      if (data) {
        jQuery(parent).append(data);
        cancel_content();
        save_content();
        hideShow_content_btns("hide");
      }
    });
  });
}
function edit_content() {
  if (typeof obj === undefined) return false;
  jQuery(".edit_content").click(function (event) {
    event.preventDefault();
    noError = true;
    if (jQuery(this).attr("data-url")) url = jQuery(this).attr("data-url");
    else url = jQuery(this).attr("href");
    parent = get_parent(jQuery(this));
    jQuery.post(url, {}, function (data) {
      if (data) {
        jQuery(parent).before(data);
        jQuery(parent).css({
          visibility: "hidden",
          position: "fixed",
          left: "10000px",
        });
        cancel_content();
        save_content();
        hideShow_content_btns("hide");
      }
    });
  });
}
function cancel_content() {
  jQuery(".cancel_content").click(function (event) {
    event.preventDefault();
    noError = true;
    parent = get_parent(jQuery(this));
    jQuery(parent)
      .next()
      .css({ visibility: "visible", position: "relative", left: "0" });
    jQuery(parent).remove();
    hideShow_content_btns("show");
  });
}
function save_content() {
  jQuery(".save_content").click(function (event) {
    event.preventDefault();
    noError = true;
    jQuery(get_parent(jQuery(this)))
      .find("form")
      .submit();
  });
}
jQuery(document).ready(function (e) {
  post_links();
  form_elements_required();
  load_content();
  iframe_links();
  add_content();
  edit_content();
});
function get_this_data(theURL, resContainter) {
  if (typeof theURL === undefined || typeof resContainter === undefined)
    return false;
  jQuery(resContainter).html(
    '<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>'
  );
  jQuery(resContainter).css("display", "block");
  jQuery.post(theURL, {}, function (data) {
    if (data) {
      jQuery(resContainter).html(data);
    }
  });
}
var formPass = false;
function post_this_form(obj) {
  formPass = false;
  clickedItem = obj;
  if (jQuery(obj).attr("data-confirm")) {
    confirmTxt = jQuery(obj).attr("data-confirm");
    alert_confirm(confirmTxt);
    jQuery(obj).attr("data-confirmed", confirmTxt);
    jQuery(obj).removeAttr("data-confirm");
    jQuery("button#alertYesBtn").click(function () {
      jQuery(obj).submit();
      close_alert();
    });
    jQuery("button#alertNoBtn").click(function () {
      jQuery(obj).attr("data-confirm", jQuery(obj).attr("data-confirmed"));
      jQuery(obj).removeAttr("data-confirmed");
      close_alert();
    });
    return false;
  } else {
	  do_post_this_form(obj);
      return formPass;
  }
}
//TODO: update new system js file
function checkInputs(obj) {
  var error = false;
  jQuery
    .when(
      jQuery(obj)
        .find("input[data-check]")
        .each(function (e) {
          checkEl = jQuery(this);
          if (
            $(jQuery(checkEl).data("check") + ":checked").length <
            jQuery(checkEl).attr("data-min")
          ) {
            error = true;
            top.alert_message(jQuery(checkEl).attr("data-error"));
            return false;
          }
        })
    )
    .done(function () {
      if (error == false) {
        do_post_this_form(obj, "checkBox");
      }
    });
}
function do_post_this_form(obj, checkBox) {
  var error = false;
  thObj = obj;
  var check = "";
  var numberError = "Please use digits (0-9).";
  var radioError = '<span style="color:red">**</span>';
  fields_required = jQuery(obj).find("input[name='error-required']").val();
  if (typeof fields_required == "undefined")
    fields_required = jQuery(obj).attr("data-error");
  if (typeof fields_required == "undefined")
    fields_required = "All fields with (*) are required.";
  if (jQuery(obj).attr("data-error-color"))
    error_color = jQuery(obj).attr("data-error-color");
  else error_color = "#fee";
  if (
    typeof checkBox == "undefined" &&
    jQuery(obj).find("input[data-check]").length > 0
  ) {
    return checkInputs(obj);
  }
  jQuery(jQuery(obj).prop("elements")).each(function () {
    if (jQuery(this).attr("data-required")) {
      theTtlId = "#ttl_" + jQuery(this).attr("id");
      if (jQuery(theTtlId).length == 0) {
        theTtlId = get_parent(jQuery(this));
      }
      if (jQuery(this).attr("data-color"))
        jQuery(this).css("background-color", jQuery(this).attr("data-color"));
      else
        jQuery(this).attr("data-color", jQuery(this).css("background-color"));
      if (
        jQuery(this).hasClass("tinymce") ||
        jQuery(this).hasClass("tinymce-popup") ||
        jQuery(this).hasClass("tinymce_minimum")
      ) {
        jQuery(jQuery(this).parent()).css("border", "inherit");
        var content = tinymce
          .get(jQuery(this).attr("id"))
          .getContent({ format: "text" });
        if (jQuery.trim(content) != "") {
          jQuery(this).val(tinymce.get(jQuery(this).attr("id")).getContent());
        }
      }
      if (jQuery(this).attr("data-required") && jQuery(this).val() == "") {
        jQuery(this).css("background-color", error_color);
        if (
          jQuery(this).hasClass("tinymce") ||
          jQuery(this).hasClass("tinymce-popup") ||
          jQuery(this).hasClass("tinymce_minimum")
        ) {
          jQuery(jQuery(this).parent()).css("border", "1px solid red");
        }
        error = true;
      }
      if (
        jQuery(this).attr("data-required") &&
        jQuery(this).attr("type") == "radio"
      ) {
        jQuery(theTtlId).find("span").remove();
        var radioName = jQuery(this).attr("name");
        if (!jQuery("input[name='" + radioName + "']:checked").val()) {
          jQuery(theTtlId).prepend(radioError);
          error = true;
        }
      }
      if (
        jQuery(this).attr("data-required") &&
        jQuery(this).attr("type") == "checkbox" &&
        !jQuery(this).is(":checked")
      ) {
        jQuery(theTtlId).css("color", "red");
        error = true;
      } else {
        jQuery(theTtlId).css("color", "");
      }
    }
  });
  if (error == true) {
    top.alert_message(fields_required);
    error = false;
    return false;
  }
  jQuery(jQuery(obj).prop("elements")).each(function () {
    theTtlId = "#ttl_" + jQuery(this).attr("id");
    if (jQuery(this).attr("data-color"))
      jQuery(this).css("background-color", jQuery(this).attr("data-color"));
    else
      jQuery(this).attr("data-color", jQuery(theTtlId).css("background-color"));
    if (
      jQuery(this).attr("data-number") &&
      !jQuery.isNumeric(jQuery(this).val())
    ) {
      jQuery(this).css("background-color", error_color);
      if (jQuery(this).attr("data-numberError"))
        top.alert_message(jQuery(this).attr("data-numberError"));
      else top.alert_message(numberError);
      error = true;
      return false;
    }
    if (
      jQuery(this).attr("data-equal") &&
      jQuery(this).val() != jQuery("#" + jQuery(this).attr("data-equal")).val()
    ) {
      jQuery(this).css("border-color", "#f00");
      jQuery(jQuery(this).attr("data-equal")).css("background-color", "#f00");
      top.alert_message(jQuery(this).attr("data-equalError"));
      error = true;
      return false;
    }
    if (
      jQuery(this).attr("data-requiredOr") &&
      jQuery(this).val() == "" &&
      jQuery("#" + jQuery(this).attr("data-requiredOr")).val() == ""
    ) {
      jQuery(theTtlId).css("color", "#f00");
      jQuery("#ttl_" + jQuery(this).attr("data-requiredOr")).css(
        "color",
        "#f00"
      );
      top.alert_message(jQuery(this).attr("data-requiredOrError"));
      error = true;
      return false;
    }
    if (
      jQuery(this).attr("type") == "text" &&
      jQuery(this).attr("data-min") &&
      jQuery(this).val().length < jQuery(this).attr("data-min")
    ) {
      jQuery(this).css("backgrond-color", error_color);
      if (jQuery(this).attr("data-minMaxError"))
        top.alert_message(jQuery(this).attr("data-minMaxError"));
      else top.alert_message(jQuery(this).attr("placeholder"));
      error = true;
      return false;
    }
  });
  /*checking email field*/
  jQuery(jQuery(obj).prop("elements")).each(function () {
    if (
      jQuery(this).attr("data-required") &&
      ((jQuery(this).attr("class") &&
        jQuery(this).attr("class").indexOf("email") >= 0) ||
        jQuery(this).attr("name") == "email" ||
        jQuery(this).attr("type") == "email")
    ) {
      if (
        jQuery(this).val().indexOf("@") <= 0 ||
        jQuery(this).val().indexOf(".") <= 0
      ) {
        jQuery(this).css("background-color", error_color);
        if (jQuery(this).attr("data-error"))
          top.alert_message(jQuery(this).attr("data-error"));
        else top.alert_message("Please use a valid email address!");
        error = true;
        return false;
      }
    }
  });
  jQuery(jQuery(obj).prop("elements")).each(function () {
    if (jQuery(this).attr("name") == "before_submit") {
      beforeSubmit = jQuery(this).val();
      doFunction(beforeSubmit);
    }
  });
  if (error == true) {
    error = false;
    return false;
  }
  if (!jQuery(obj).attr("action") && typeof ajaxurl != 'undefined' )
    jQuery(obj).attr("action", ajaxurl);
  if (!jQuery(obj).attr("target") || jQuery(obj).attr("target") == "") {
    ifrname =
      "frame_" +
      jQuery(obj).attr("name") +
      "_" +
      Math.floor(Math.random() * 100 + 1);
    jQuery(".post_form_frame").remove();
    jQuery("body").append(
      '<iframe style="position:fixed;left:-5000px" name="' +
        ifrname +
        '" class="post_form_frame"></iframe>'
    );
    jQuery(obj).attr("target", ifrname);
  }
  jQuery(obj).attr("method", "post");
  //jQuery(obj).find("input[type='submit']").replaceWith("<img src='"+hcp_url+"/images/busy.gif'/>");
  formPass = true;
  return true;
}
function postResults(data, htmlID) {

  if (jQuery(clickedItem).attr("data-confirmed")) {
    jQuery(clickedItem).attr(
      "data-confirm",
      jQuery(clickedItem).attr("data-confirmed")
    );
    jQuery(clickedItem).removeAttr("data-confirmed");
  }
  if (data.trim().length > 0) {
    if (data.indexOf("alert:") > -1) {
      top.alert_message(data.replace("alert:", ""));
    } else if (data.indexOf("reload:") > -1) {
      location.reload();
    } else if (data.indexOf("resizeIframe:") > -1) {
      resizePdfIframe();
    } else if (data.indexOf("topReload:") > -1) {
      top.location.reload();
    } else if (data.indexOf("url:") > -1) {
      document.location = data.replace("url:", "");
    } else if (data.indexOf("urlReplace:") > -1) {
      document.location.replace(data.replace("urlReplace:", ""));
    } else if (data.indexOf("docLocation:") > -1) {
      docLocation = data.replace("docLocation:", "");
    } else if (data.indexOf("topUrl:") > -1) {
      top.location = data.replace("topUrl:", "");
    } else if (data.indexOf("openUrl:") > -1) {
      window.open(data.replace("openUrl:", ""));
    } else if (data.indexOf("ifUrl:") > -1) {
      jQuery("#pdfIframe").attr("src", data.replace("ifUrl:", ""));
    } else if (data.indexOf("hideIframe:") > -1) {
      hideIframe();
    } else if (data.indexOf("function:") > -1) {
      do_function(data.replace("function:", ""));
    } else if (
      data.indexOf("closeDialog:") > -1 ||
      data.indexOf("closePopup:") > -1
    ) {
      $(".ui-dialog-content").dialog("close");
    } else if (data.indexOf("hide:") > -1) {
      jQuery(data.replace("hide:", "")).css("display", "none");
    } else if (data.indexOf("show:") > -1) {
      jQuery(data.replace("show:", "")).css("display", "block");
    } else if (data.indexOf("remove:") > -1) {
      closestObj = data.replace("remove:", "");
      if (closestObj.length > 0) jQuery(thObj).closest(closestObj).remove();
      else jQuery(thObj).closest("div,p,li,tr").remove();
    } else if (data.indexOf("class:") > -1) {
      oldClass = jQuery(thObj).attr("class");
      classes = data.replace("class:", "");
      if (classes.indexOf(",") > -1) {
        newClasses = classes.split(",");
        jQuery(thObj).attr(
          "class",
          oldClass.replace(newClasses[0], newClasses[1])
        );
      } else {
        jQuery(thObj).attr("class", classes);
      }
    } else if (data.indexOf("html:") > -1) {
      script = "";
      if (data.indexOf("<do-script>") > -1) {
        str = data.indexOf("<do-script>");
        end = data.indexOf("</do-script>") + 12;
        script = data.substr(str, end - str);
        data = data.replace(script, "");
        script = script.replace(/do-script/g, "script");
        data = data.replace(/do-script/g, "script");
      }
      if (htmlID) {
        jQuery("#" + htmlID).html(data.replace("html:", ""));
        if (script) jQuery("#" + htmlID).append(script);
        if (htmlID == "contentHolder") showContentHolder();
      } else {
        jQuery(thObj).closest("td,th,div,p,li").html(data.replace("html:", ""));
        if (script) jQuery(thObj).closest("td,th,div,p,li").append(script);
      }
    } else if (data.indexOf("append:") > -1) {
      if (htmlID) jQuery("#" + htmlID).append(data.replace("append:", ""));
    } else if (data.indexOf("prepend:") > -1) {
      if (htmlID) jQuery("#" + htmlID).prepend(data.replace("append:", ""));
    } else if (data.indexOf("submit:") > -1) {
      if (htmlID) jQuery("#" + htmlID).submit();
    } else if (data.indexOf("replace:") > -1) {
      if (htmlID)
        jQuery("#" + htmlID).replaceWith(data.replace("replace:", ""));
    } else if (data.indexOf("reset:") > -1) {
      if (htmlID)
        jQuery("#" + htmlID + ", form[name='" + htmlID + "']").trigger("reset");
    } else if (data.indexOf("val:") > -1) {
      if (htmlID) jQuery("#" + htmlID).val(data.replace("val:", ""));
    } else if (data.indexOf("saved:") > -1) {
      if (jQuery(thObj).closest("form").find("span#saved").length == 0) {
        var savedSpan =
          '<span id="saved" style="display:none;color:green;padding:5px"><i class="far fa-save" style="color:green"></i> SAVED</span>';
        if (jQuery(thObj).closest("form").find("input[type=submit]"))
          jQuery(thObj)
            .closest("form")
            .find("input[type=submit]")
            .before(savedSpan);
        else jQuery(thObj).closest("form").append(savedSpan);
      }
      jQuery(thObj)
        .closest("form")
        .find("span#saved")
        .fadeIn("slow")
        .fadeOut("slow");
    } else if (data.indexOf("tinymce:") > -1) {
      if (htmlID)
        tinyMCE.get("#" + htmlID).setContent(data.replace("tinymce:", ""));
      else tinyMCE.activeEditor.setContent(data.replace("tinymce:", ""));
    }
  } else {
    top.alert_message("Something went wrong with the log-in, please try again.");
  }
}
/*switching pages*/
function switch_this_page(obj) {
  var objName = jQuery(obj).attr("name");
  var objVal = jQuery(obj).val();
  var url = location.search.substr(1);
  var getItems = {};
  var urlGets = url.split("&");
  for (i = 0; i < urlGets.length; i++) {
    var gItems = urlGets[i].split("=");
    if (i == 0) getItems[gItems[0]] = ["?" + urlGets[i]];
    else getItems[gItems[0]] = ["&" + urlGets[i]];
  }
  if (getItems[objName]) url = "?" + url.replace(getItems[objName], "");
  if (objVal != "") url = "?" + url + "&" + objName + "=" + objVal;
  if (objVal == "reset") url = getItems["inc"] + getItems["act"];
  window.location = url;
}
function fill_the_form(obj) {
  jQuery(jQuery(obj).prop("elements")).each(function () {
    if (
      jQuery(this).is("textarea") ||
      (jQuery(this).is(":text") &&
        jQuery(this).attr("type") &&
        jQuery(this).attr("type") == "text")
    ) {
      jQuery(this).val(jQuery(this).attr("name"));
    }
    if (jQuery(this).is("select")) {
      $(this)[0].selectedIndex = 1;
    }
  });
}
