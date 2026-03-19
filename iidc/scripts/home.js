//ayoub media js stuff
var dialogPopupBox =
  '<div id="dialogCover">' +
  '<div id="dialog-message">' +
  '<div style="position:absolute;top:0px;bottom:0px;background:#eee;padding:20px"><i style="color:orangered;font-size:60px !important" aria-hidden="true" class="fa fa-question-circle"></i></div>' +
  '<div style="margin-left:100px;padding:20px;overflow:hidden">' +
  '<div id="dialogContent" style="padding-bottom:20px;font-size:14px"></div>' +
  '<div id="popupDialogBtns" style="text-align:right"></div>' +
  "</div></div></div>";
function open_alert() {
  jQuery("body").css("overflow", "hidden");
  jQuery("div#dialogCover,div#dialog-message").css("display", "block");
}
function close_alert() {
	if (jQuery("#dialogCover").length != 0) {
    jQuery("#dialogCover").remove();
    jQuery("body").css("overflow", "auto");
  }
}
function alert_message(msg) {
  jQuery("body").append(dialogPopupBox);
  jQuery("#dialog-message #dialogContent").html(msg);
  jQuery("#popupDialogBtns").html(
    '<button onclick="close_alert()">OK</button>'
  );
  open_alert();
  return false;
}

function working_alert(msg) {
  jQuery("body").append(
    '<div id="dialogCover" style="display:block"><span class="loading-icon"><i class="fas fa-sync fa-spin" style="font-size:40px !important;color:brown"></i></span></div>'
  );
  jQuery("body").css("overflow", "hidden");
}

function alert_confirm(msg) {
  if (jQuery("#dialogCover").length == 0) jQuery("body").append(dialogPopupBox);
  jQuery("#dialog-message #dialogContent").html(msg);
  jQuery("#popupDialogBtns").html(
    '<button id="alertYesBtn">Yes</button><button onclick="close_alert()">Cancel</button>'
  );
  open_alert();
  return false;
}

async function confirm_message(msg,yesButton = 'yes',cancelButton = 'cancel') {
  if (jQuery("#dialogCover").length == 0) jQuery("body").append(dialogPopupBox);
  jQuery("#dialog-message #dialogContent").html(msg);
  jQuery("#popupDialogBtns").html(
    '<button id="alertYesBtn">Yes</button><button onclick="close_alert()">Cancel</button>'
  );
  open_alert();
  if(yesButton != 'yes') jQuery("#alertYesBtn").html(yesButton);
  await new Promise((resolve) => {
    jQuery("button#alertYesBtn").click(function () {
      resolve();
      close_alert();
      return true;
    });
  });
}
