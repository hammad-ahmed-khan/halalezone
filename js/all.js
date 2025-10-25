function diffInMonths(dateFrom, dateTo) {
  return (
    dateTo.getMonth() -
    dateFrom.getMonth() +
    12 * (dateTo.getFullYear() - dateFrom.getFullYear())
  );
}

function downloadURI(url, name) {
  var link = document.createElement("a");
  link.download = name;
  link.href = url;
  document.body.appendChild(link);
  link.click();
}

function removeFileFromHostAndGdrive(hostpath, gdrivepath) {
  $.ajax({
    type: "GET",
    url: "fileupload/ProcessFiles.php",
    cache: false,
    data: {
      deleteId: gdrivepath.substring(
        gdrivepath.indexOf("file/d/") + 7,
        gdrivepath.indexOf("/view")
      ),
      deleteName: hostpath,
    },
    success: function (result) {},
    error: function (jqXHR, status, message) {
      alert(
        "Error deleting the file (" + message + ").\nIt probably doesn't exist"
      );
    },
  });
}

// Function to create list of colored ingredients names in the table cell from data from DB
function formatInglist(cellValue, options, rowObject) {
  var linkHtml = "";
  var allGreen = true; // Всі інгредієнти зелені до доведення протилежного
  var showMoreText = " more ingredients..."; // Текст для кнопки

  if (cellValue) {
    var celval;
    try {
      celval = JSON.parse(cellValue);
    } catch (error) {
      console.log(cellValue);
      return false;
    }

    // Перевіряємо кожен інгредієнт, чи він зелений
    celval.forEach(function (a) {
      if (a.status !== "0") {
        // Припускаємо, що "1" - це зелений
        allGreen = false;
      }
    });

    // Виводимо перші чотири інгредієнти
    celval.slice(0, 4).forEach(function (a) {
      linkHtml += createIngrHtml(a, rowObject);
    });

    // Якщо інгредієнтів більше, ніж чотири
    if (celval.length > 4) {
      linkHtml += '<div class="additional-ingredients" style="display:none;">';
      celval.slice(4).forEach(function (a) {
        linkHtml += createIngrHtml(a, rowObject);
      });
      linkHtml += "</div>";

      // Клас кнопки залежить від того, чи всі інгредієнти зелені
      var buttonClass = allGreen ? "green" : "red";
      linkHtml +=
        '<button class="show-more ' +
        buttonClass +
        '" onclick="toggleIngredients(this);">' +
        (celval.length - 4) +
        showMoreText +
        "</button>";
    }
  }

  return linkHtml;
}

function toggleIngredients(button) {
  var additionalIngredients = button.previousElementSibling;
  var isVisible = additionalIngredients.style.display !== "none";

  // Показуємо або ховаємо додаткові інгредієнти
  additionalIngredients.style.display = isVisible ? "none" : "";

  // Змінюємо текст кнопки
  button.textContent = isVisible ? button.dataset.moreText : "Show less";
}

// Create HTML for an ingredient
function createIngrHtml(a, rowObject) {
  var conf = "";
  var status = "";
  switch (
    a.status
    // ваш switch statement тут
  ) {
  }
  if (a.status == "4") {
    a.conf = "0";
  }
  if (a.conf == "0") conf = " warning";
  return (
    '<p class="ingred-item ' +
    conf +
    " " +
    status +
    '" data-id="' +
    a.id +
    '">' +
    '<a href="ingredients?id=' +
    a.id +
    "&idclient=" +
    rowObject[11] +
    '" target="_blank">' +
    a.name +
    "</a></p>"
  );
}

function showMore(button, celval, rowObject) {
  button.remove();

  // Showing the rest of products
  var parent = button.parentElement;
  celval.slice(4).forEach(function (a) {
    parent.innerHTML += createIngrHtml(a, rowObject);
  });
}

// function to create data for form drop down from the cell data
function unformatInglist(cellValue, options, cellObject) {
  var s = [];
  $("p", $(cellObject)).each(function () {
    s.push($(this).data("id"));
  });
  return s.toString();
}

// formatter for cv link

function formatDoclink(cellValue, options, rowObject) {
  var linkHtml = "";
  try {
    if (cellValue != null && cellValue != "") {
      if (cellValue.length != 0) {
        var arr = "[" + cellValue + "]";
        arr = JSON.parse(arr);
        console.log(options);
        arr.forEach(function (a) {
          if (a.name.length > 35) ell = a.name.substr(0, 30) + "...";
          else ell = a.name;
          // use link to host file version instead of a.glink
          linkHtml +=
            '<a class="cvitem" ' +
            (a.deleted && a.deleted == "1"
              ? 'style="text-decoration:line-through;"'
              : "") +
            ' target="_blank" href="' +
            a.hostUrl +
            '" originalname="' +
            encodeURI(JSON.stringify(a)) +
            '" title="' +
            a.name +
            '">' +
            ell +
            "</a>";
        });
      }
    }

    // link to add files
    const inputClass = {
      cert: 4,
      spec: 1,
      quest: 2,
      statement: 3,
      addoc: 5,
      addocs: 6,
      policy: 7,
      haccp: 8,
      team: 9,
      training: 10,
      purchasing: 11,
      cleaning: 12,
      production: 12,
      handling: 13,
      storage: 14,
      traceability: 15,
      audit: 16,
      analysis: 17,
      flowchart: 18,
      qcertificate: 19,
      label: 20,
      training_request_form: 12,
      attendance_list: 13,
      customer_feedback_form: 14,
      attendance_certificates: 15,
      invoice_inbound: 7,
      travel_invoices: 9,
    }[options.colModel.index];

    const folderType = {
      cert: "cert",
      spec: "spec",
      quest: "quest",
      statement: "state",
      addoc: "add",
      addocs: "add",
      policy: "policy",
      haccp: "haccp",
      team: "team",
      training: "training",
      purchasing: "purchasing",
      cleaning: "cleaning",
      production: "production",
      handling: "handling",
      storage: "storage",
      traceability: "traceability",
      audit: "audit",
      analysis: "analysis",
      flowchart: "flowchart",
      qcertificate: "qcertificate",
      label: "label",
      training_request_form: "training_request_form",
      attendance_list: "attendance_list",
      customer_feedback_form: "customer_feedback_form",
      attendance_certificates: "attendance_certificates",
      invoice_inbound: "invoice_inbound",
      travel_invoices: "travel_invoices",
    }[options.colModel.index];

    if (folderType == "cert") {
      linkHtml +=
        '<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div></div>' +
        '<span class="fileinput-button fib-dropzone dropzone' +
        inputClass +
        '"><span class="spinner-border spinner-border-sm"></span> <span class="dropzone-title"> <svg viewBox="0 0 16.00 16.00" xmlns="http://www.w3.org/2000/svg" fill="#69b0ce" class="bi bi-cloud-arrow-up-fill" transform="rotate(0)matrix(1, 0, 0, 1, 0, 0)" stroke="#69b0ce" stroke-width="0.00016"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.128"></g><g id="SVGRepo_iconCarrier"> <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2z"></path> </g></svg> <span class="drop-text">Drag files here<br>or click to select</span></span>' +
        '<input class="fileupload fileupload' +
        inputClass +
        '" type="file" name="files[]"  foldertype="' +
        folderType +
        '" infotype="ingredient">' +
        "</span>" +
        '<ul class="ul' +
        folderType +
        '"></ul>' +
        '<div class="alert-string"></div>';
    } else {
      linkHtml +=
        '<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div></div>' +
        '<span class="fileinput-button fib-dropzone dropzone' +
        inputClass +
        '"><span class="spinner-border spinner-border-sm"></span> <span class="dropzone-title"> <svg viewBox="0 0 16.00 16.00" xmlns="http://www.w3.org/2000/svg" fill="#69b0ce" class="bi bi-cloud-arrow-up-fill" transform="rotate(0)matrix(1, 0, 0, 1, 0, 0)" stroke="#69b0ce" stroke-width="0.00016"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.128"></g><g id="SVGRepo_iconCarrier"> <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2z"></path> </g></svg> <span class="drop-text">Drag files here<br>or click to select</span></span>' +
        '<input class="fileupload fileupload' +
        inputClass +
        '" type="file" name="files[]" multiple  foldertype="' +
        folderType +
        '" infotype="ingredient">' +
        "</span>" +
        '<ul class="ul' +
        folderType +
        '"></ul>' +
        '<div class="alert-string"></div>';
    }
  } catch (e) {
    console.log(e);
  }
  return linkHtml;
}

function unformatDoclink(cellValue, options, cellObject) {
  var s = [];
  $("a", $(cellObject)).each(function () {
    s.push(decodeURI($(this).attr("originalname")));
  });
  //  remove last comma
  var arr = "[" + s.toString() + "]";
  // send correct JASON to the form field
  return arr;
}

// formtter for assigned tasks flag column
function formatTasksFlag(cellValue, options, rowObject) {
  var idi = rowObject[1].replace("RMC_", "");
  if (cellValue == 1) {
    return (
      '<div class="action-buttons center"><i class="fa fa-flag red bigger-130 ingred-tooltip" data-idi="' +
      idi +
      '"></i>' +
      '<br><a href="#" class="add-task-inline-link" onclick="IP.onSetTasksForIngredient(' +
      idi +
      '); return false">Add task</a></div>'
    );
    //'<button type="button" class="btn btn-xs btn-success confirm-task" data-data="' + cellValue + '" title="Press to confirm the task"><i class="ace-icon fa fa-check"></i>Confirm</button>';';
  } else {
    return (
      '<div class="center"><a href="#" class="add-task-inline-link" onclick="IP.onSetTasksForIngredient(' +
      idi +
      '); return false">Add task</a></div>'
    );
  }
}

// formatter for cv link
function formatApplink(cellValue, options, rowObject) {
  if (options.pos == "1") {
    linkHtml = "<p>" + cellValue + "</p>";
    if (rowObject[32] == "1")
      // cycle is active
      linkHtml +=
        '<button class="btn btn-success" data-grid="' +
        options.gid +
        '" data-id="' +
        options.rowId +
        '" onclick=APP.onEditApplication(event)>Edit</button>';
    if (
      rowObject[33] < "3" &&
      rowObject[32] == "1" &&
      rowObject[29] != "" &&
      !options.colModel.editoptions.isClient
    )
      linkHtml +=
        '<button class="btn btn-warning" data-grid="' +
        options.gid +
        '" data-id="' +
        options.rowId +
        '" onclick=APP.onStopNotification(event)>Stop notification</button>';
    if (rowObject[32] == "1")
      // cycle is active
      linkHtml +=
        '<button class="btn btn-info" data-grid="' +
        options.gid +
        '" data-id="' +
        options.rowId +
        '" onclick=APP.onSkipApplication(event) title="Skip this subcycle">Skip</button>';
    return linkHtml;
  }
  var stateCol = rowObject[options.pos + 1];
  var itemClass = "";
  if (
    (stateCol == "2" && !options.colModel.editoptions.needConfirm) ||
    stateCol == "3"
  )
    itemClass = " confirmed";
  else if (stateCol != "1") itemClass = " warning";
  var linkHtml = "";
  if (cellValue != null && cellValue != "") {
    if (cellValue.length != 0) {
      var arr = "[" + cellValue + "]";
      arr = JSON.parse(arr);
      arr.forEach(function (a) {
        if (a.invalid === "undefined") a.invalid = 0;
        if (a.name.length > 40) ell = a.name.substr(0, 35) + "...";
        else ell = a.name;

        if (a.invalid == 1) cl = " invalid-file";
        else cl = " ";
        // use link to host file version instead of a.glink
        linkHtml +=
          '<a class="ingred-item ' +
          itemClass +
          cl +
          '" target="_blank" href="' +
          a.hostUrl +
          '" originalname="' +
          encodeURI(JSON.stringify(a)) +
          '" title="' +
          a.name +
          '">' +
          ell +
          "</a>";
      });
    }
    // if group and group field and first certifiated was not issued
    if (rowObject[23] * 1 < 2 && options.pos * 1 < 22 && stateCol * 1 > 1)
      if (
        !options.colModel.editoptions.isClient &&
        !options.colModel.editoptions.isClientField
      ) {
        // if not active and something was filled in, put cancel button
        linkHtml +=
          '<button class="cancel-app-btn btn btn-primary" data-grid="' +
          options.gid +
          '" data-id="' +
          options.rowId +
          '" data-name="' +
          options.colModel.index +
          '" onclick=APP.onConcelApp(event)>Cancel from here</button>';
      }
  }
  // add buttons for active survey only
  if (rowObject[32] == "1" && stateCol != "3" && linkHtml != "") {
    var params = {};
    params.name = options.colModel.index + "state";
    if (
      (stateCol == "1" &&
        !options.colModel.editoptions.isClient &&
        options.colModel.editoptions.isClientField) ||
      (stateCol == "2" &&
        options.colModel.editoptions.isClient &&
        !options.colModel.editoptions.isClientField &&
        options.colModel.editoptions.needConfirm)
    ) {
      params.nextname = options.colModel.editoptions.nextStateCol;
      linkHtml +=
        '<button class="btn btn-info" id="bootbox-confirm" data-grid="' +
        options.gid +
        '" data-id="' +
        options.rowId +
        '" data-name="' +
        params.name +
        '" data-nextname="' +
        params.nextname +
        '" onclick=APP.onConfirmApp(event);>Confirm</button>';
    } else if (
      stateCol == "1" &&
      !options.colModel.editoptions.isClient &&
      !options.colModel.editoptions.isClientField
    ) {
      if (!options.colModel.editoptions.needConfirm)
        // if complete non-confirmed  then send next state col name
        params.nextname = options.colModel.editoptions.nextStateCol;
      else params.nextname = "";
      linkHtml +=
        '<button class="btn btn-success" id="bootbox-confirm" data-grid="' +
        options.gid +
        '" data-id="' +
        options.rowId +
        '" data-name="' +
        params.name +
        '" data-nextname="' +
        params.nextname +
        '" onclick=APP.onCompleteApp(event)>Complete</button>';
    }
  }
  return linkHtml;
}

function unformatApplink(cellValue, options, cellObject) {
  var s = [];
  $("a", $(cellObject)).each(function () {
    s.push(decodeURI($(this).attr("originalname")));
  });
  //  remove last comma
  var arr = "[" + s.toString() + "]";
  // send correct JASON to the form field
  return arr;
}

function unformatFirstColumn(cellValue, options, cellObject) {
  return cellValue.replace("Edit", "").replace("Stop notification", "");
}

// --------------- Terms -------------------------
function delDocClick(e) {
  var el = $(e.target);
  var date = new Date();
  let day = date.getDate();
  if (day <= 9) day = "0" + day;
  let month = date.getMonth() + 1;
  if (month <= 9) month = "0" + month;
  let year = date.getFullYear();
  if (!el.parent().hasClass("deleted")) {
    el.parent().addClass("deleted");
    el.parent().append(
      " <strong class='text-danger' style='float:right'>(" +
        day +
        "/" +
        month +
        "/" +
        year +
        " by " +
        $("#navUserName").html() +
        ")</strong> "
    );
    el.hide();
  } else {
    el.parent().removeClass("deleted");
    el.show();
  }
  return false;
}

function formatCat1(cellValue, options, rowObject) {
  if (cellValue == null) return "";
  if (cellValue.length != 0) {
    return cellValue.replace(/,/g, ", ");
  } else return cellValue;
}

// formatter for conformed button
function formatButton(cellValue, options, rowObject) {
  var val = cellValue == 1 ? "Yes" : "No";

  if (rowObject[25] > 0) {
    return val;
  }

  if ($("select#ingred-clientid").length == 0) return val;
  else
    return (
      val +
      "&nbsp;&nbsp;&nbsp;<span class=\"ace-icon fa fa-unsorted cell-button set-conf\" title='Change conformity'></span>"
    );
}

// formatter for conformed button
function formatAdminButton(cellValue, options, rowObject) {
  var val = "";
  if (cellValue == 0) {
    val = "Admin";
  } else if (cellValue == 2) {
    val = "Auditor";
  } else {
    val = "Client";
  }

  return val;
  /*
  return (
    val +
    '&nbsp;&nbsp;&nbsp;<span class="ace-icon fa fa-unsorted cell-button ' +
    options.colModel.name +
    "\" title='Click to change value to opposite'></span>"
  );
  */
}

function formatCompanyButton(cellValue, options, rowObject) {
  var val = "";
  if (cellValue == 0) {
    val = "In-Active";
  } else {
    val = "Active";
  }
  return val;
  /*
  return (
    val +
    '&nbsp;&nbsp;&nbsp;<span class="ace-icon fa fa-unsorted cell-button ' +
    options.colModel.name +
    "\" title='Click to change value to opposite'></span>"
  );
  */
}

function unformatButton(cellValue, options, cellObject) {
  return cellValue.trim() === "Yes" ? 1 : 0;
}

// formatter for resore (undelete) button
function formatIngredRestoreButton(cellValue, options, rowObject) {
  return cellValue == 0
    ? ""
    : '<button class="btn btn-success" data-grid="' +
        '" data-id="' +
        options.rowId +
        '"onclick=IP.onRestoreIngred(event)>Restore</button>';
}

// formatter for resore (undelete) button
function formatProductRestoreButton(cellValue, options, rowObject) {
  return cellValue == 0
    ? ""
    : '<button class="btn btn-success" data-grid="' +
        '" data-id="' +
        options.rowId +
        '"onclick=PP.onRestoreProd(event)>Restore</button>';
}

// formatter for unblock button
function formatAdminUnblockButton(cellValue, options, rowObject) {
  return cellValue == 0
    ? ""
    : '<button class="btn btn-success" data-grid="' +
        '" data-id="' +
        options.rowId +
        '" onclick=SP.onUnblockUser(event)>Unblock</button>';
}

// formattor for file name link to download
function formatFileNameToOpenInNewWindow(cellValue, options, cellObject) {
  return '<a href="' + cellObject[2] + '" target=_blank>' + cellValue + "</a>";
}

// formattor for file name link to download
function formatFileNameToDownload(cellValue, options, cellObject) {
  return '<a href="' + cellObject[2] + '" download>' + cellValue + "</a>";
}

function unformatFileNameToDownload(cellValue, options, cellObject) {
  return cellValue;
}

// formattor for shared status
function formatShareStatus(cellValue, options, cellObject) {
  if (options.colModel.isClient) {
    if (cellValue == 1)
      // unshare button
      return '<span class="label label-success label-white"><i class="ace-icon fa fa-check"></i>Shared</span>';
    else return "";
  } else {
    if (cellValue == 1)
      // unshare button
      return (
        '<button type="button" class="btn btn-xs btn-success share-file" data-data="' +
        cellValue +
        '" aria-pressed="true" title="Press to unshare the file"><i class="ace-icon fa fa-check"></i>Shared</button>'
      );
    else
      return (
        '<button type="button" class="btn btn-xs btn-yellow share-file" data-data="' +
        cellValue +
        '" aria-pressed="true" title="Press to share the file">Not shared</button>'
      );
  }
}

// formattor for expiry status column
function formatStatusFromExpDate(cellValue, options, rowObject) {
  if (cellValue == 4 || rowObject[8] < 0)
    // expired
    return '<span class="label label-danger">Expired</span>';
  else if (cellValue == 3)
    // expired within 1 mnth
    return (
      '<span class="label label-danger label-white">Expires in ' +
      rowObject[8] +
      " day(s)</span>"
    );
  else if (cellValue == 2)
    // expired within 2 mnth
    return (
      '<span class="label label-warning label-white">Expires in ' +
      rowObject[8] +
      " day(s)</span>"
    );
  else if (cellValue == 1)
    // expired within 3 mnth
    return (
      '<span class="label label-yellow label-white">Expires in ' +
      rowObject[8] +
      " day(s)</span>"
    );
  else return '<span class="label label-success label-white">Ok</span>';
}

// formatter for action column with edit and delete buttons
function formatEditDelete(cellValue, options, rowObject) {
  return (
    '<div class="action-buttons"><a href="#" class="edit-certificate blue">' +
    '<i class="edit-certificate ace-icon fa fa-pencil bigger-130"></i></a><span class="vbar"></span>' +
    '<a href="#" class="red"><i class="remove-certificate ace-icon fa fa-trash-o bigger-130"></i></a></div>'
  );
}

function formatShareEmail(cellValue, options, rowObject) {
  return (
    '<div class="action-buttons"><a href="#" class="edit-certificate blue" title="Email the certificate">' +
    '<i class="email-certificate ace-icon fa  fa-envelope-o bigger-130"></i></a></div>'
  );
}

// formattor for tasks list
// formattor for shared status
function formatTaskStatus(cellValue, options, cellObject) {
  if (cellValue == 1)
    //
    return (
      '<button type="button" class="btn btn-xs btn-success assign-task" data-data="' +
      cellValue +
      '" aria-pressed="true" title="Press to unassign the task"><i class="ace-icon fa fa-check"></i>Assigned</button>'
    );
  else
    return (
      '<button type="button" class="btn btn-xs btn-yellow btn-white assign-task" data-data="' +
      cellValue +
      '" aria-pressed="true" title="Press to assign the task">Not assigned</button>'
    );
}

// formattor for tasks list
// formattor for shared status
function formatTaskDelete(cellValue, options, cellObject) {
  return '<button type="button" class="btn btn-xs btn-success edit-task" aria-pressed="true" style="width:49%;float:left;padding:10px 10px;" title="Press to edit the task">Edit</button> <button style="width:49%;float:right;padding:10px 10px;" type="button" class="btn btn-xs btn-danger delete-task" aria-pressed="true" title="Press to delete the task">Delete</button>';
}

// formattor for done/undone button for active tasks grid
function formatActiveTaskStatus(cellValue, options, cellObject) {
  if (cellValue == 0) {
    return (
      '<button type="button" class="btn btn-xs btn-danger complete-task" data-data="' +
      cellValue +
      '" title="Press to complete the task"><i class="ace-icon fa fa-exclamation-triangle"></i>Complete</button>'
    );
  } else if (cellValue == 1) {
    return (
      '<button type="button" class="btn btn-xs btn-success undone-task" data-data="' +
      cellValue +
      '" title="Press to undone the task"><i class="ace-icon fa fa-check"></i>Done</button>'
    );
  } else {
    return '<span class="label label-warning label-white"><i class="ace-icon fa fa-check bigger-120"></i>Confirmed</span>';
  }
}

function formatLinkToIngredient(cellValue, options, cellObject) {
  return (
    '<a href="ingredients?id=' +
    cellObject[1] +
    "&idclient=" +
    cellObject[2] +
    '" target="_blank">' +
    cellValue +
    "</a>"
  );
}

function formatLinkToItem(cellValue, options, cellObject) {
  return (
    '<a href="' +
    cellObject[2] +
    "?id=" +
    cellObject[1] +
    "&idclient=" +
    cellObject[3] +
    '" target="_blank">' +
    cellValue +
    "</a>"
  );
}

// formattor client Action status
function formatAuditReportStatus(cellValue, options, cellObject) {
  if (cellValue == 0) {
    return (
      '<button type="button" class="btn btn-xs btn-danger confirm-action" data-data="' +
      cellValue +
      '" title="Press to confirm the task"><i class="ace-icon fa fa-exclamation-triangle"></i>Confirm</button>'
    );
  } else
    return '<span class="label label-warning label-white"><i class="ace-icon fa fa-check bigger-120"></i>Confirmed</span>';
}

function formatProcessStatus(cellValue, options, cellObject) {
  if (cellValue == 0) {
    return (
      '<button type="button" class="btn btn-xs btn-danger confirm-action" data-data="' +
      cellValue +
      '" title="Press to confirm the task"><i class="ace-icon fa fa-exclamation-triangle"></i>Confirm</button>'
    );
  } else
    return '<span class="label label-warning label-white"><i class="ace-icon fa fa-check bigger-120"></i>Confirmed</span>';
}

function formatClientActionStatus(cellValue, options, cellObject) {
  if (cellValue == 0) {
    return (
      '<button type="button" class="btn btn-xs btn-danger confirm-action" data-data="' +
      cellValue +
      '" title="Press to confirm the task"><i class="ace-icon fa fa-exclamation-triangle"></i>Confirm</button>'
    );
  } else
    return '<span class="label label-warning label-white"><i class="ace-icon fa fa-check bigger-120"></i>Confirmed</span>';
}

function formatActiveTaskStatusForAdmin(cellValue, options, cellObject) {
  if (cellValue == 0) {
    return '<span class="label label-danger label-white"><i class="ace-icon fa fa-exclamation-triangle"></i>Complete</span>';
  } else if (cellValue == 1) {
    return (
      '<button type="button" class="btn btn-xs btn-success confirm-task" data-data="' +
      cellValue +
      '" title="Press to confirm the task"><i class="ace-icon fa fa-check"></i>Confirm</button>'
    );
  } else {
    return '<span class="label label-warning label-white"><i class="ace-icon fa fa-check bigger-120"></i>Confirmed</span>';
  }
}

function validateEmail(email) {
  var re =
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

function validateEmailsList(email) {
  var re =
    /^[\W]*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]+[\W]*,{1}[\W]*)*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]+)[\W]*$/;
  return re.test(email);
}

function validatePassword(pass) {
  if (pass.length < 6) return false;
  return true;
}

/***** FILE UPLOAD RELATED STUFF *****/

const DefaultFileUploadSuccessHandler = function (
  e,
  data,
  progressSelector,
  afterSuccess
) {
  // hide loader and add new li with new file info
  $(e.target).parent().siblings(progressSelector).hide();
  $(e.target).parent().show();
  $.each(data.result.files, function (index, file) {
    var jsonstring =
      '{"name":"' +
      file.name +
      '","glink":"' +
      file.googleDriveUrl +
      '","hostpath":"' +
      file.url +
      '","hostUrl":"' +
      file.hostUrl +
      '"}';
    var ell;
    if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
    else ell = file.name;
    var filename = $(
      '<li class="uploaded-file-name" originalname="' +
        encodeURI(jsonstring) +
        '"></li>'
    );
    filename.append($("<span>", { text: ell }));
    filename.append(
      $(
        '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
          "fileid=" +
          file.googleDriveId +
          " hostpath=" +
          encodeURI(file.url) +
          ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
      ).bind("click", function (e) {
        delDocClick(e);
      })
    );
    // add li to the list of the appropriate ul - class from folderType
    $("#ul" + file.folderType).append(filename);

    if ("function" === typeof afterSuccess) {
      afterSuccess(e, file);
    }
  });
};
const DefaultFileUploadStartHandler = function (
  e,
  progressSelector = ".progress"
) {
  $(e.target)
    .parent()
    .siblings(progressSelector)
    .find(".progress-bar")
    .css("width", "0%");
  $(e.target).parent().siblings(progressSelector).show();
  $(e.target).parent().hide();
};
const DefaultFileUploadFailHandler = function (
  e,
  data,
  progressSelector = ".progress"
) {
  // kill all progress bars awaiting for showing
  $(e.target).parent().siblings(progressSelector).hide();
  $(e.target).parent().show();
  alert("Error uploading file (" + data.errorThrown + ")");
};
const DefaultFileUploadProgressHandler = function (
  e,
  data,
  progressSelector = ".progress"
) {
  $(e.target)
    .parent()
    .siblings(progressSelector)
    .find(".progress-bar")
    .css("width", (data.loaded / data.total) * 100 + "%");
};
const DefaultFileUploadAddHandler = function (
  e,
  data,
  dataValidator,
  dataModifier
) {
  const validationResult = dataValidator(e, data);

  if (true !== validationResult) {
    alert(validationResult);
    return;
  }

  if ("function" === typeof dataModifier) {
    dataModifier(e, data);
  }

  data.submit();
};

// Returns true if file is valid , error message as String otherwise
const defaultFileValidator = function (e, data) {
  const uploadFile = data.files[0];

  if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
    return "You can upload PDF file(s) only";
  }

  return true;
};

let activeDropArea = null;

function handleDragOverDocument(event) {
  event.preventDefault();

  if (!activeDropArea) {
    const uploadAreas = document.querySelectorAll(".upload-area");
    uploadAreas.forEach(function (area) {
      area.classList.add("dragged-over");
    });
  }
}

function handleDragLeaveDocument(event) {
  event.preventDefault();

  var uploadAreas = document.querySelectorAll(".upload-area");
  uploadAreas.forEach(function (area) {
    area.classList.remove("dragged-over");
  });
}

function handleDropDocument(event) {
  event.preventDefault();
}

function handleDragOver(event) {
  const e = event.target;
  let ua = e.classList.contains(".upload-area") ? e : e.closest(".upload-area");

  activeDropArea = ua;
  event.preventDefault();
  var uploadAreas = document.querySelectorAll(".upload-area");
  uploadAreas.forEach(function (area) {
    area.classList.remove("dragged-over");
  });
  ua.classList.add("dragged-over");
}

function handleDragLeave(event) {
  event.preventDefault();
  event.target.classList.remove("dragged-over");
  activeDropArea = null;
}

function handleDrop(event) {
  event.preventDefault();
  var uploadAreas = document.querySelectorAll(".upload-area");
  uploadAreas.forEach(function (area) {
    area.classList.remove("dragged-over");
  });
  $(event.target)
    .closest(".upload-area")
    .find(".fileupload")
    .fileupload("add", { files: event.dataTransfer.files });
}

// Initializes all file upload controls on the page
function initFileUploader(options) {
  const defaultOptions = {
    progressSelector: ".progress",
    fileUploadSelector: ".fileupload",
    dropzoneSelector: ".dropzone",
    dataModifier: null,
    fileValidator: defaultFileValidator,
    afterSuccess: null,
    onAdd: null,
    onSuccess: null,
    onStart: null,
    onFail: null,
    onProgress: null,
    url: "fileupload/ProcessFiles.php",
  };

  options = Object.assign(defaultOptions, options);

  if ("function" !== typeof options.onAdd) {
    options.onAdd = function (e, data) {
      DefaultFileUploadAddHandler(
        e,
        data,
        options.fileValidator,
        options.dataModifier
      );
    };
  }

  if ("function" !== typeof options.onSuccess) {
    options.onSuccess = function (e, data) {
      DefaultFileUploadSuccessHandler(
        e,
        data,
        options.progressSelector,
        options.afterSuccess
      );
    };
  }

  if ("function" !== typeof options.onStart) {
    options.onStart = function (e, data) {
      DefaultFileUploadStartHandler(e, options.progressSelector);
    };
  }

  if ("function" !== typeof options.onFail) {
    options.onFail = function (e, data) {
      DefaultFileUploadFailHandler(e, data, options.progressSelector);
    };
  }

  if ("function" !== typeof options.onProgress) {
    options.onProgress = function (e, data) {
      DefaultFileUploadProgressHandler(e, data, options.progressSelector);
    };
  }

  $(options.progressSelector).hide();

  $(options.fileUploadSelector)
    .fileupload({
      url: options.url,
      dataType: "json",
      dropZone: $(options.dropzoneSelector),
      add: options.onAdd,
      done: options.onSuccess,
      start: options.onStart,
      fail: options.onFail,
      progress: options.onProgress,
    })
    .prop("disabled", !$.support.fileInput)
    .parent()
    .addClass($.support.fileInput ? undefined : "disabled");
}

/***** END FILE UPLOAD RELATED STUFF *****/

var Utils = {
  notify: function (type, msg) {
    if (type == "error") alert(msg);
  },

  notifyInput: function (element, msg) {
    element.focus();
    element.next().html(msg);
  },

  // From uploader widget to AJAX
  filesToJSON: function (elementName) {
    var arr = [];
    $("ul#" + elementName)
      .children("li")
      .each(function () {
        var a = JSON.parse(decodeURI($(this).attr("originalname")));
        if ($(this).hasClass("deleted")) {
          var date = new Date();
          let day = date.getDate();
          if (day <= 9) day = "0" + day;
          let month = date.getMonth() + 1;
          if (month <= 9) month = "0" + month;
          let year = date.getFullYear();
          a.deleted = 1;
          a.deleted_at = day + "/" + month + "/" + year;
          a.deleted_by = $("#navUserName").html();
        }
        arr.push(JSON.stringify(a));
      });
    return arr.toString();
  },

  // From data cell to the uploader widget
  filesToList: function (elementName, gridName, columnName) {
    $("#" + elementName).empty();
    var value = jQuery("#" + gridName).jqGrid(
      "getCell",
      jQuery("#" + gridName).jqGrid("getGridParam", "selrow"),
      columnName
    );
    if (value.length > 0) {
      var arr = JSON.parse(value);
      var filename, start, end;
      arr.forEach(function (a) {
        console.log(a);
        if (a.invalid === "undefined") a.invalid = 0;
        if (a.name.length > 40) ell = a.name.substr(0, 35) + "...";
        else ell = a.name;

        var cl = "uploaded-file-name " + (a.deleted ? "deleted" : "");
        if (a.invalid == 1) cl += " invalid-file";
        filename = $(
          '<li class="' +
            cl +
            '" originalname="' +
            encodeURI(JSON.stringify(a)) +
            '"></li>'
        );
        filename.append($("<span>", { text: ell }));

        if (a.deleted && a.deleted_at) {
          filename.append(
            ' <strong class="text-danger" style="float:right">(' +
              a.deleted_at +
              " by " +
              a.deleted_by +
              ")</strong> "
          );
        } else {
          //if (a.glink) {
          if (1) {
            start = a.glink ? a.glink.indexOf("file/d/") + 7 : 0;
            end = a.glink ? a.glink.indexOf("/view") : 0;
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  (a.glink ? a.glink.substring(start, end) : "") +
                  " hostpath=" +
                  encodeURI(a.hostpath) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
          }
        }
        // add li to the list of the appropriate ul - class from folderType
        $("#" + elementName).append(filename);
      });
    }
  },

  // From data cell to the uploader widget
  filesToListForApplication: function (
    elementName,
    grid,
    columnName,
    stateColName,
    dropzone,
    isClientField,
    isClient
  ) {
    $("#" + elementName).empty();
    var value = grid.jqGrid(
      "getCell",
      grid.jqGrid("getGridParam", "selrow"),
      columnName
    );
    var state = grid.jqGrid(
      "getCell",
      grid.jqGrid("getGridParam", "selrow"),
      stateColName
    );
    if (state === "0") {
      $("#" + dropzone).hide();
      return;
    }
    if (value.length > 0) {
      var arr = JSON.parse(value);
      var filename, start, end;
      arr.forEach(function (a) {
        if (a.invalid === "undefined") a.invalid = 0;
        if (a.name.length > 40) ell = a.name.substr(0, 35) + "...";
        else ell = a.name;

        var cl = "uploaded-file-name";
        if (a.invalid == 1) cl += " invalid-file";
        filename = $(
          '<li class="' +
            cl +
            '" originalname="' +
            encodeURI(JSON.stringify(a)) +
            '"></li>'
        );
        filename.append($("<span>", { text: ell, title: a.name }));
        if (state === "1" && ((isClient && isClientField) || !isClient)) {
          // active state, add Delete button
          start = a.glink.indexOf("file/d/") + 7;
          end = a.glink.indexOf("/view");
          filename.append(
            $(
              '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                "fileid=" +
                a.glink.substring(start, end) +
                " hostpath=" +
                encodeURI(a.hostpath) +
                ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
            ).bind("click", function (e) {
              delDocClick(e);
            })
          );
        }
        $("#" + elementName).append(filename);
      });
    }
    if (state === "1") {
      // field is active
      if (isClient && !isClientField) {
        // for client and halal field hide dropzone. otherwise show everything
        $("#" + dropzone).hide();
      }
    } else {
      // confirmed
      $("#" + dropzone).hide();
    }
  },
};

var Common = {
  onDocumentReady: function () {
    $(document).bind("drop dragover", function (e) {
      e.preventDefault();
    });
  },

  setMainMenuItem: function (itemName) {
    $("#" + itemName).addClass("active");
  },

  updatePagerIcons: function (table) {
    var replacement = {
      "ui-icon-seek-first": "ace-icon fa fa-angle-double-left bigger-140",
      "ui-icon-seek-prev": "ace-icon fa fa-angle-left bigger-140",
      "ui-icon-seek-next": "ace-icon fa fa-angle-right bigger-140",
      "ui-icon-seek-end": "ace-icon fa fa-angle-double-right bigger-140",
    };
    $(
      ".ui-pg-table:not(.navtable) > tbody > tr > .ui-pg-button > .ui-icon"
    ).each(function () {
      var icon = $(this);
      var $class = $.trim(icon.attr("class").replace("ui-icon", ""));

      if ($class in replacement)
        icon.attr("class", "ui-icon " + replacement[$class]);
    });
  },

  loadClientsData: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "clients" }).done(callback);
  },

  populateClients: function (data) {
    var response = JSON.parse(data);
    $(".clientslist").empty();
    $(".clientslist").append(
      $("<option>", { text: "Select client", value: "-1", selected: true })
    );
    response.data.clients.forEach(function (cl) {
      $(".clientslist").append(
        $("<option>", {
          value: cl.id,
          "data-clientname": cl.name + " (" + cl.prefix + cl.id + ")",
          text: cl.name + " - " + cl.prefix + cl.id,
        })
      );
    });
    if ($("#filter-idclient").length && $("#filter-idclient").val().length)
      $(".clientslist").val($("#filter-idclient").val());

    // for Prod and Ingred pages, refresh grid
    //if ( $("#filter-conformed").length )
    //    $("#filter-conformed").trigger('change');
  },

  sendAddActionRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "addClientAction",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Action was added");
    });
  },
};

// DASHBOARD PAGE
var DP = {
  onDocumentReady: function () {
    DP.hideLoaderById("#dropzone1-loader");

    Common.setMainMenuItem("dashItem");
    this.initCharts();
    this.initCertificatesGrid();
    this.initFilesGrid();
    if (DP.isAdminSession()) this.initProcessStatusGrid();
    if (DP.isAdminSession()) this.initClientActionsGrid();
    if (DP.isAdminSession()) this.initAuditReportGrid();
    this.initActiveTasksGrid();
    DP.initFileUploader();

    // to make grids responsive
    $(window).on("resize.jqGrid", function () {
      $("#certificatesGrid").jqGrid(
        "setGridWidth",
        $("#certificates-container").width()
      );
      $("#filesGrid").jqGrid("setGridWidth", $("#files-container").width());
      $("#clientActionsGrid").jqGrid(
        "setGridWidth",
        $("#clientactions-container").width()
      );
      $("#activeTasksGrid").jqGrid(
        "setGridWidth",
        $("#activetasks-container").width()
      );
    });

    if (DP.isAdminSession()) {
      //Common.loadClientsData(Common.populateClients);
      $("#prod-clientid").on("change", function () {
        DP.loadData(DP.populateData);
        $("#prod-clientid").data(
          "clientname",
          $("#prod-clientid option:selected").data("clientname")
        );
        // refresh certificates grid
        $("#certificatesGrid").jqGrid("setGridParam", {
          url: "ajax/getCertificates.php?idclient=" + this.value,
        });
        $("#certificatesGrid").jqGrid().trigger("reloadGrid");
        jQuery("#clientActionsGrid").jqGrid("setGridParam", {
          url:
            "ajax/getClientActions.php?idclient=" +
            this.value +
            (this.value == -1 ? "&all=" + DP.isAdminSession() : "") +
            "&confirmed=" +
            ($("#filter-actions-confirmed").prop("checked") ? 1 : 0),
        });
        jQuery("#clientActionsGrid")
          .jqGrid()
          .setGridParam({ page: 1 })
          .trigger("reloadGrid", [{ page: 0 }]);

        jQuery("#processStatusGrid").jqGrid("setGridParam", {
          url:
            "ajax/getProcessStatusDP.php?idclient=" +
            $("#prod-clientid").val() +
            "&all=" +
            DP.isAdminSession() +
            "&conformed=" +
            ($("#filter-process-confirmed").prop("checked") ? 1 : 0),
        });
        jQuery("#processStatusGrid")
          .jqGrid()
          .setGridParam({ page: 1 })
          .trigger("reloadGrid", [{ page: 0 }]);

        jQuery("#auditReportGrid").jqGrid("setGridParam", {
          url:
            "ajax/getAuditReportDP.php?idclient=" +
            this.value +
            (this.value == -1 ? "&all=" + DP.isAdminSession() : "") +
            "&confirmed=" +
            ($("#filter-auditreport-confirmed").prop("checked") ? 1 : 0),
        });
        jQuery("#auditReportGrid")
          .jqGrid()
          .setGridParam({ page: 1 })
          .trigger("reloadGrid", [{ page: 0 }]);

        jQuery("#activeTasksGrid").jqGrid("setGridParam", {
          url:
            "ajax/getActiveTasks.php?idclient=" +
            $("#prod-clientid").val() +
            (this.value == -1 ? "&all=" + DP.isAdminSession() : "") +
            "&confirmed=" +
            ($("#filter-confirmed").prop("checked") ? 1 : 0),
        });
        jQuery("#activeTasksGrid")
          .jqGrid()
          .trigger("reloadGrid", [{ page: 1 }]);

        // show or hide dropzones
        if (this.value > 0) $(".fileinput-button").show();
        else $(".fileinput-button").hide();
      });
      // Drop zone hover handler
      $(document).bind("drag-over", function (e) {
        var dropZones = $(".fileinput-button"),
          timeout = window.dropZoneTimeout;
        if (timeout) {
          clearTimeout(timeout);
        } else {
          dropZones.addClass("in");
        }
        var hoveredDropZone = $(e.target).closest(dropZones);
        dropZones.not(hoveredDropZone).removeClass("hover");
        hoveredDropZone.addClass("hover");
        window.dropZoneTimeout = setTimeout(function () {
          window.dropZoneTimeout = null;
          dropZones.removeClass("in hover");
        }, 100);
      });
    } else {
      $(".filinfo-button").remove();
      $(".fileinput-button").hide();
      DP.loadData(DP.populateData);
    }

    $("#filter-actions-confirmed").on("change", function (e) {
      jQuery("#clientActionsGrid").jqGrid("setGridParam", {
        url:
          "ajax/getClientActions.php?idclient=" +
          $("#prod-clientid").val() +
          "&all=" +
          DP.isAdminSession() +
          "&confirmed=" +
          ($("#filter-actions-confirmed").prop("checked") ? 1 : 0),
      });
      jQuery("#clientActionsGrid")
        .jqGrid()
        .setGridParam({ page: 1 })
        .trigger("reloadGrid", [{ page: 0 }]);
    });

    $("#filter-process-confirmed").on("change", function (e) {
      jQuery("#processStatusGrid").jqGrid("setGridParam", {
        url:
          "ajax/getProcessStatusDP.php?idclient=" +
          $("#prod-clientid").val() +
          "&all=" +
          DP.isAdminSession() +
          "&confirmed=" +
          ($("#filter-process-confirmed").prop("checked") ? 1 : 0),
      });
      jQuery("#processStatusGrid")
        .jqGrid()
        .setGridParam({ page: 1 })
        .trigger("reloadGrid", [{ page: 0 }]);
    });

    $("#filter-auditreport-confirmed").on("change", function (e) {
      jQuery("#auditReportGrid").jqGrid("setGridParam", {
        url:
          "ajax/getAuditReportDP.php?idclient=" +
          $("#prod-clientid").val() +
          "&all=" +
          DP.isAdminSession() +
          "&confirmed=" +
          ($("#filter-auditreport-confirmed").prop("checked") ? 1 : 0),
      });
      jQuery("#auditReportGrid")
        .jqGrid()
        .setGridParam({ page: 1 })
        .trigger("reloadGrid", [{ page: 0 }]);
    });

    $("#filter-confirmed").on("change", function (e) {
      jQuery("#activeTasksGrid").jqGrid("setGridParam", {
        url:
          "ajax/getActiveTasks.php?idclient=" +
          $("#prod-clientid").val() +
          "&all=" +
          DP.isAdminSession() +
          "&confirmed=" +
          ($("#filter-confirmed").prop("checked") ? 1 : 0),
      });
      jQuery("#activeTasksGrid")
        .jqGrid()
        .trigger("reloadGrid", [{ page: 1 }]);
    });

    $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });

    $(".datepicker")
      .datepicker()
      .on("changeDate", function (e) {
        DP.clearAlerts();
      });
    // Modal Close handler
    $("#certificateModal").on("hide.bs.modal", function (e) {
      if ($(document.activeElement).is("[data-save]")) return;
      if ($(document.activeElement).is("[data-dismiss]"));
      removeFileFromHostAndGdrive(
        $("#certificateModal .hostpath").val(),
        $("#certificateModal .gdrivepath").val()
      );
    });
  },

  isAdminSession: function () {
    return $("select#prod-clientid").length > 0;
  },

  initCharts: function () {
    $(".easy-pie-chart.percentage").each(function () {
      var $box = $(this).parent();
      var barColor =
        $(this).data("color") ||
        (!$box.hasClass("infobox-dark")
          ? $box.css("color")
          : "rgba(255,255,255,0.95)");
      var trackColor =
        barColor == "rgba(255,255,255,0.95)"
          ? "rgba(255,255,255,0.25)"
          : "#E2E2E2";
      var size = parseInt($(this).data("size")) || 50;
      $(this).easyPieChart({
        barColor: barColor,
        trackColor: trackColor,
        scaleColor: false,
        lineCap: "butt",
        lineWidth: parseInt(size / 10),
        animate: ace.vars["old_ie"] ? false : 1000,
        size: size,
      });
    });
  },

  initFileUploader: function () {
    $("#fileupload1")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone1"),
        add: function (e, data) {
          alert("test1");
          // if client not selected, cancel uploading
          if ($("#prod-clientid").val() == -1) {
            alert("Please select client");
            return;
          }
          data.formData = {
            folderType: $(this).attr("folderType"), // for certificates
            infoType: $(this).attr("infoType"),
            client: $("#prod-clientid").data("clientname"),
          };
          data.submit();
        },
        start: function (e) {
          $(this).parent().hide();
          DP.showLoaderById("#dropzone1-loader");
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          DP.hideLoaderById("#dropzone1-loader");
          $(this).parent().show();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          DP.hideLoaderById("#dropzone1-loader");
          $(this).parent().show();
          // put the file (there will be only one) info into the inputs of the modal
          $.each(data.result.files, function (index, file) {
            DP.clearCertificateModalForm();
            DP.setFileNameToModal(file.name);
            DP.setUrlToModal(file.hostUrl);
            DP.setHostPathToModal(file.url);
            DP.setGdrivePathToModal(file.googleDriveUrl);
            DP.setExpdateToModal(new Date());
          });
          $("#certificateModal").modal("show");
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
  },

  initActiveTasksGrid: function () {
    var h = 300;
    $("#activeTasksGrid").jqGrid({
      url:
        "ajax/getActiveTasks.php?idclient=" +
        $("#prod-clientid").val() +
        "&all=" +
        ($("#prod-clientid").val() == "" && DP.isAdminSession()) +
        "&conformed=" +
        ($("#filter-confirmed").prop("checked") ? 1 : 0),
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", hidden: true, key: true },
        { index: "idingredient", name: "idingredient", hidden: true },
        { index: "idclient", name: "idclient", hidden: true },
        {
          label: "Creation time",
          name: "created_at",
          index: "created_at",
          align: "center",
          width: 60,
          sortable: true,
          search: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          label: "Client",
          name: "client",
          index: "client",
          width: 70,
          align: "left",
          hidden: !DP.isAdminSession(),
        },
        {
          label: "RMC_ID",
          name: "rmid",
          index: "rmid",
          width: 40,
          align: "left",
          formatter: formatLinkToIngredient,
        },
        {
          label: "RM Code",
          name: "rmcode",
          index: "rmcode",
          width: 90,
          sortable: false,
          formatter: formatLinkToIngredient,
        },
        {
          label: "Name",
          name: "name",
          index: "name",
          width: 90,
          sortable: false,
          formatter: formatLinkToIngredient,
        },
        {
          label: "Supplier",
          name: "supplier",
          index: "supplier",
          width: 70,
          search: false,
          sortable: false,
        },
        {
          label: "Producer",
          name: "producer",
          index: "producer",
          width: 70,
          search: false,
          sortable: false,
        },
        {
          label: "Deviation",
          name: "deviation",
          index: "deviation",
          width: 140,
          search: false,
          align: "left",
          sortable: false,
        },
        {
          label: "Measure",
          name: "measure",
          index: "measure",
          width: 140,
          search: false,
          sortable: false,
        },
        {
          label: "Status",
          name: "status",
          index: "status",
          width: 55,
          align: "center",
          search: false,
          formatter: DP.isAdminSession()
            ? formatActiveTaskStatusForAdmin
            : formatActiveTaskStatus,
        },
        {
          label: "Completed time",
          name: "completed_at",
          index: "completed_at",
          align: "center",
          width: 60,
          sortable: true,
          search: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#activeTasksPager",
      sortname: "created_at",
      sortorder: "desc",
      viewrecords: true,
      shrinkToFit: true,
      gridComplete: function () {
        $("#activeTasksGrid").jqGrid(
          "setGridWidth",
          $("#activetasks-container").width()
        );
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.complete-task")) {
          e.preventDefault();
          IP.onCompleteActiveTask(rowid);
          return true;
        } else if ($(e.target).is("button.undone-task")) {
          e.preventDefault();
          IP.onUndoneActiveTask(rowid);
          return true;
        } else if ($(e.target).is("button.confirm-task")) {
          e.preventDefault();
          IP.onConfirmTaskActiveTask(rowid);
          return true;
        }
        return true; // select the row
      },
    });
    $("#activeTasksGrid").jqGrid("filterToolbar", { enableClear: false });
  },

  initClientActionsGrid: function () {
    var h = 300;
    $("#clientActionsGrid").jqGrid({
      url:
        "ajax/getClientActions.php?idclient=" +
        $("#prod-clientid").val() +
        "&all=" +
        DP.isAdminSession() +
        "&conformed=" +
        ($("#filter-actions-confirmed").prop("checked") ? 1 : 0), // not necessary since clients are for group only
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", hidden: true, key: true },
        { index: "itemid", name: "itemid", hidden: true },
        { index: "itemtype", name: "itemtype", hidden: true },
        { index: "idclient", name: "idclient", hidden: true },
        {
          label: "Creation time",
          name: "created_at",
          index: "created_at",
          align: "center",
          width: 30,
          sortable: true,
          search: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          label: "Client",
          name: "client",
          index: "client",
          width: 70,
          align: "left",
          hidden: !DP.isAdminSession(),
        },
        {
          label: "Item code",
          name: "itemcode",
          index: "itemcode",
          width: 30,
          align: "left",
          formatter: formatLinkToItem,
        },
        {
          label: "Name",
          name: "name",
          index: "name",
          width: 100,
          sortable: false,
          formatter: formatLinkToItem,
        },
        {
          label: "Action",
          name: "supplier",
          index: "supplier",
          width: 120,
          search: false,
          sortable: false,
        },
        {
          label: "Status",
          name: "status",
          index: "status",
          width: 30,
          align: "center",
          search: false,
          formatter: formatClientActionStatus,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#clientActionsPager",
      sortname: "created_at",
      sortorder: "desc",
      viewrecords: true,
      shrinkToFit: true,
      gridComplete: function () {
        $("#clientActionsGrid").jqGrid(
          "setGridWidth",
          $("#clientactions-container").width()
        );
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.confirm-action")) {
          e.preventDefault();
          DP.onConfirmClientAction(rowid);
          return true;
        }
        return true; // select the row
      },
    });
    // $("#clientActionsGrid").jqGrid("filterToolbar", { enableClear: false });
  },

  initProcessStatusGrid: function () {
    var h = 300;
    $("#processStatusGrid").jqGrid({
      url:
        "ajax/getProcessStatusDP.php?idclient=" +
        $("#prod-clientid").val() +
        "&all=" +
        DP.isAdminSession() +
        "&conformed=" +
        ($("#filter-process-confirmed").prop("checked") ? 1 : 0), // not necessary since clients are for group only
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", hidden: true, key: true },
        {
          label: "Creation time",
          name: "created_at",
          index: "created_at",
          align: "center",
          width: 30,
          sortable: true,
          search: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          label: "Client",
          name: "client",
          index: "client",
          width: 70,
          align: "left",
          hidden: !DP.isAdminSession(),
        },
        {
          label: "Last Action",
          name: "username",
          index: "username",
          width: 170,
          align: "left",
        },
        {
          label: "Current Status",
          name: "state",
          index: "state",
          width: 50,
          search: false,
        },
        {
          label: "Status",
          name: "status",
          index: "status",
          width: 30,
          align: "center",
          search: false,
          formatter: formatProcessStatus,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#processStatusPager",
      sortname: "created_at",
      sortorder: "desc",
      viewrecords: true,
      shrinkToFit: true,
      gridComplete: function () {
        $("#processStatusGrid").jqGrid(
          "setGridWidth",
          $("#clientactions-container").width()
        );
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.confirm-action")) {
          e.preventDefault();
          DP.onConfirmProcessStatus(rowid);
          return true;
        }
        return true; // select the row
      },
    });
    // $("#clientActionsGrid").jqGrid("filterToolbar", { enableClear: false });
  },

  initAuditReportGrid: function () {
    var h = 300;
    $("#auditReportGrid").jqGrid({
      url:
        "ajax/getAuditReportDP.php?idclient=" +
        $("#prod-clientid").val() +
        "&all=" +
        DP.isAdminSession() +
        "&conformed=" +
        ($("#filter-auditreport-confirmed").prop("checked") ? 1 : 0), // not necessary since clients are for group only
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", hidden: true, key: true },
        { index: "idclient", name: "idclient", hidden: true },
        {
          label: "Creation time",
          name: "created_at",
          index: "created_at",
          align: "center",
          width: 70,
          sortable: true,
          search: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          label: "Client",
          name: "client",
          index: "client",
          width: 100,
          align: "left",
          hidden: !DP.isAdminSession(),
        },
        {
          label: "Type",
          name: "Type",
          index: "Type",
          width: 30,
          align: "left",
          //formatter: formatLinkToItem,
        },
        {
          label: "Deviation",
          name: "Deviation",
          index: "Deviation",
          width: 120,
          sortable: false,
          //formatter: formatLinkToItem,
        },
        {
          label: "Reference",
          name: "Reference",
          index: "Reference",
          width: 70,
          search: false,
          sortable: false,
        },
        {
          label: "RootCause",
          name: "RootCause",
          index: "RootCause",
          width: 120,
          align: "center",
          search: false,
        },
        {
          label: "Measure",
          name: "Measure",
          index: "Measure",
          width: 120,
          search: false,
          sortable: false,
        },
        {
          label: "Deadline",
          name: "Deadline",
          index: "Deadline",
          width: 70,
          search: false,
          sortable: false,
        },
        {
          label: "Status",
          name: "Status",
          index: "Status",
          width: 50,
          align: "center",
          search: false,
          formatter: formatAuditReportStatus,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#auditReportPager",
      sortname: "created_at",
      sortorder: "desc",
      viewrecords: true,
      shrinkToFit: true,
      gridComplete: function () {
        $("#auditReportGrid").jqGrid(
          "setGridWidth",
          $("#auditreport-container").width()
        );
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.confirm-action")) {
          e.preventDefault();
          DP.onConfirmAuditReport(rowid);
          return true;
        }
        return true; // select the row
      },
    });
    // $("#clientActionsGrid").jqGrid("filterToolbar", { enableClear: false });
  },

  initCertificatesGrid: function () {
    var h = 300;
    $("#certificatesGrid").jqGrid({
      url: "ajax/getCertificates.php?idclient=" + $("#prod-clientid").val(),
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "Certificate",
          name: "filename",
          index: "filename",
          align: "left",
          formatter: formatFileNameToDownload,
          unformat: unformatFileNameToDownload,
        },
        { label: "url", name: "url", index: "url", hidden: true },
        {
          label: "hostpath",
          name: "hostpath",
          index: "hostpath",
          hidden: true,
        },
        {
          label: "gdrivepath",
          name: "gdrivepath",
          index: "gdrivepath",
          hidden: true,
        },
        {
          label: "Exp. Date",
          name: "expdate",
          index: "expdate",
          align: "center",
          width: 60,
          sorttype: "date",
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          label: "Status",
          name: "status",
          index: "status",
          align: "center",
          width: 100,
          sortable: false,
          formatter: formatStatusFromExpDate,
        },
        {
          label: " ",
          name: "actions",
          index: "actions",
          align: "center",
          width: 40,
          sortable: false,
          formatter: DP.isAdminSession() ? formatEditDelete : formatShareEmail,
        },
        { name: "datediff", index: "datediff", hidden: true },
      ],
      rowNum: 0,
      sortname: "expdate",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
        $("#certificatesGrid").jqGrid(
          "setGridWidth",
          $("#certificates-container").width()
        );
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("i.edit-certificate")) {
          e.preventDefault();
          DP.onEditCertificateExpDate(rowid);
          return true;
        } else if ($(e.target).is("i.remove-certificate")) {
          e.preventDefault();
          if (confirm("Remove certificate?")) DP.onRemoveCertificate(rowid);
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("i.email-certificate")) {
          e.preventDefault();
          DP.onEmailCertificate(rowid);
          return false; // don't select the row on click on the button
        }
        return true; // select the row
      },
    });
  },

  initFilesGrid: function () {
    var h = 300;
    $("#filesGrid").jqGrid({
      url: "ajax/getFiles.php",
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "File name",
          name: "filename",
          index: "filename",
          align: "left",
          formatter: formatFileNameToOpenInNewWindow,
          unformat: unformatFileNameToDownload,
        },
        {
          label: "gdrivepath",
          name: "gdrivepath",
          index: "gdrivepath",
          hidden: true,
        },
        {
          label: "Upload Date",
          name: "uploaddate",
          index: "uploaddate",
          align: "center",
          width: 60,
          sorttype: "date",
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
      ],
      rowNum: 0,
      sortname: "filename",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
        $("#filesGrid").jqGrid("setGridWidth", $("#files-container").width());
      },
    });
  },

  showLoaderById: function (id) {
    $(id).css("display", "inline-block");
  },

  hideLoaderById: function (id) {
    $(id).css("display", "none");
  },

  validateForm: function () {
    if ($("#certificateModal .expdate").val().trim() == "") {
      Utils.notifyInput(
        $("#certificateModal .expdate"),
        "No Expiry Date specified"
      );
      return false;
    }

    return true;
  },

  // ---- CERTIFICATE MODAL ------
  clearAlerts: function () {
    $(".alert-string").text("");
  },

  clearCertificateModalForm: function () {
    $("#certificate-form .id").val("");
    $("#certificate-form .filename").val("");
    $("#certificate-form .url").val("");
    $("#certificate-form .hostpath").val("");
    $("#certificate-form .gdrivepath").val("");
    $("#certificate-form .exptdate").val("");
  },

  setIdToModal: function (f) {
    $("#certificate-form .id").val(f);
  },

  setFileNameToModal: function (f) {
    $("#certificate-form input.filename").val(f);
  },

  setUrlToModal: function (f) {
    $("#certificate-form .url").val(f);
  },

  setHostPathToModal: function (f) {
    $("#certificate-form .hostpath").val(f);
  },

  setGdrivePathToModal: function (f) {
    $("#certificate-form .gdrivepath").val(f);
  },

  setExpdateToModal: function (f) {
    $("#certificate-form .expdate").val(f);
    $("#certificate-form .expdate").datepicker("setDate", f);
  },

  onConfirmProcessStatus: function (rowid) {
    var d = {};
    d.id = $("#processStatusGrid").jqGrid("getCell", rowid, "id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "confirmProcessStatus",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#processStatusGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onConfirmClientAction: function (rowid) {
    var d = {};
    d.id = $("#clientActionsGrid").jqGrid("getCell", rowid, "id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "confirmClientAction",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#clientActionsGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onConfirmAuditReport: function (rowid) {
    var d = {};
    d.id = $("#auditReportGrid").jqGrid("getCell", rowid, "id");
    d.Status = "1";
    $.post("ajax/ajaxHandler.php", {
      rtype: "updateDeviationStatus",
      uid: 0,
      data: d,
    }).done(function (data) {
      /*
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      */
      $("#auditReportGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onSaveCertificateFromModal: function (e) {
    if (!DP.validateForm()) return;
    else if (DP.isNewCertificate()) DP.sendAddCertificateRequest();
    else DP.sendModifyCertificateRequest();
  },

  // ---- EMAIL MODAL ------
  clearEmailAlerts: function () {
    $("#email-form .alert-string").text("");
  },

  clearEmailModal: function () {
    DP.clearEmailAlerts();
    $("#email-from").val("");
    $("#email-email").val("");
    $("#email-to").val("");
    $("#email-cc").val("");
    $("#email-subject").val("");
    $("#email-message").val("");
    $("#email-attach").text("");
    $("#email-attach-hostpath").val("");
  },

  setEmailEmailModal: function (f) {
    $("#email-email").val(f);
  },

  setEmailAttachModal: function (f) {
    $("#email-attach").val(f);
  },

  setEmailAttachHostPathModal: function (f) {
    $("#email-attach-hostpath").val(f);
  },

  showEmailLoader: function () {
    $("#email-loader").show();
  },

  hideEmailLoader: function () {
    $("#email-loader").hide();
  },

  // -----------------------------------

  loadData: function (callback) {
    var data = {};
    data.id = $("#prod-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      rtype: "dashboardData",
      data: data,
    }).done(callback);
  },

  populateData: function (data) {
    var response = JSON.parse(data);
    DP.clearDashboard();
    DP.populteStatistics(response.data.statistics);
  },

  clearDashboard: function () {
    DP.setIngredPublished(0, 0);
    DP.setIngredConfirmed(0, 0);
    DP.setIngredRemained(0, 0);
    DP.setIngredExceeded(0, 0);
    DP.setProdPublished(0, 0);
    DP.setProdConfirmed(0, 0);
    DP.setProdRemained(0, 0);
    DP.setProdExceeded(0, 0);
  },

  setIngredNumber: function (n) {
    $("#ingredNumber").text(n);
  },

  setIngredPublished: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#ingredPublished").text(Math.abs(n));
    $("#ingredPublishedPercent").text(p);
    $("#ingredPublishedChart").data("easyPieChart").update(p);
  },

  setIngredConfirmed: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#ingredConfirmed").text(Math.abs(n));
    $("#ingredConfirmedPercent").text(p);
    $("#ingredConfirmedChart").data("easyPieChart").update(p);
  },

  setIngredRemained: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#ingredRemained").text(Math.abs(n));
    $("#ingredRemainedPercent").text(p);
    $("#ingredRemainedChart").data("easyPieChart").update(p);
  },

  setIngredExceeded: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#ingredExceeded").text(Math.abs(n));
    $("#ingredExceededPercent").text(p);
    $("#ingredExceededChart").data("easyPieChart").update(p);
  },

  setProdNumber: function (n) {
    $("#prodNumber").text(n);
  },

  setProdPublished: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#prodPublished").text(Math.abs(n));
    $("#prodPublishedPercent").text(p);
    $("#prodPublishedChart").data("easyPieChart").update(p);
  },

  setProdConfirmed: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#prodConfirmed").text(Math.abs(n));
    $("#prodConfirmedPercent").text(p);
    $("#prodConfirmedChart").data("easyPieChart").update(p);
  },

  setProdRemained: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#prodRemained").text(Math.abs(n));
    $("#prodRemainedPercent").text(p);
    $("#prodRemainedChart").data("easyPieChart").update(p);
  },

  setProdExceeded: function (n, p) {
    p = p >= 100 ? 100 : p < 0 ? 0 : p;
    $("#prodExceeded").text(Math.abs(n));
    $("#prodExceededPercent").text(p);
    $("#prodExceededChart").data("easyPieChart").update(p);
  },

  isNewCertificate: function () {
    return $("#certificateModal .id").val() == "";
  },

  populteStatistics: function (data) {
    DP.setIngredNumber(data.ingredNumber);
    DP.setIngredConfirmed(
      data.ingredConfirmed,
      Math.floor(
        (100 * data.ingredConfirmed) /
          (data.ingredPublished == 0 ? 1 : data.ingredPublished)
      )
    );
    if (data.ingredPublished > data.ingredNumber) {
      DP.setIngredPublished(data.ingredPublished, 100);
      DP.setIngredRemained(0, 0);
      DP.setIngredExceeded(
        data.ingredPublished - data.ingredNumber,
        Math.floor(
          (100 * (data.ingredPublished - data.ingredNumber)) /
            (data.ingredNumber == 0 ? 1 : data.ingredNumber)
        )
      );
    } else {
      DP.setIngredPublished(
        data.ingredPublished,
        Math.floor(
          (100 * data.ingredPublished) /
            (data.ingredNumber == 0 ? 1 : data.ingredNumber)
        )
      );
      DP.setIngredRemained(
        data.ingredNumber - data.ingredPublished,
        Math.floor(
          (100 * (data.ingredNumber - data.ingredPublished)) /
            (data.ingredNumber == 0 ? 1 : data.ingredNumber)
        )
      );
      DP.setIngredExceeded(0, 0);
    }
    DP.setProdNumber(data.prodNumber);
    DP.setProdConfirmed(
      data.prodConfirmed,
      Math.floor(
        (100 * data.prodConfirmed) /
          (data.prodPublished == 0 ? 1 : data.prodPublished)
      )
    );
    if (data.prodPublished > data.prodNumber) {
      DP.setProdPublished(data.prodPublished, 100);
      DP.setProdRemained(0, 0);
      DP.setProdExceeded(
        data.prodPublished - data.prodNumber,
        Math.floor(
          (100 * (data.prodPublished - data.prodNumber)) /
            (data.prodNumber == 0 ? 1 : data.prodNumber)
        )
      );
    } else {
      DP.setProdPublished(
        data.prodPublished,
        Math.floor(
          (100 * data.prodPublished) /
            (data.prodNumber == 0 ? 1 : data.prodNumber)
        )
      );
      DP.setProdRemained(
        data.prodNumber - data.prodPublished,
        Math.floor(
          (100 * (data.prodNumber - data.prodPublished)) /
            (data.prodNumber == 0 ? 1 : data.prodNumber)
        )
      );
      DP.setProdExceeded(0, 0);
    }
  },

  onRefreshCertificates: function (e) {
    e.preventDefault();
    $("#certificatesGrid").jqGrid().trigger("reloadGrid");
  },

  onRefreshFiles: function (e) {
    e.preventDefault();
    $("#filesGrid").jqGrid().trigger("reloadGrid");
  },

  onRefreshTasks: function (e) {
    e.preventDefault();
    $("#activeTasksGrid").jqGrid().trigger("reloadGrid");
  },

  onRefreshClientActions: function (e) {
    e.preventDefault();
    $("#clientActionsGrid").jqGrid().trigger("reloadGrid");
  },

  onRefreshProcessStatus: function (e) {
    e.preventDefault();
    $("#processStatusGrid").jqGrid().trigger("reloadGrid");
  },

  onEditCertificateExpDate: function (rowid) {
    DP.clearCertificateModalForm();
    DP.setFileNameToModal(
      $("#certificatesGrid").jqGrid("getCell", rowid, "filename")
    );
    DP.setIdToModal($("#certificatesGrid").jqGrid("getCell", rowid, "id"));
    DP.setExpdateToModal(
      new Date($("#certificatesGrid").jqGrid("getCell", rowid, "expdate"))
    );
    $("#certificateModal").modal("show");
  },

  onEmailCertificate: function (rowid) {
    DP.clearEmailModal();
    DP.hideEmailLoader();
    DP.setEmailEmailModal($("#prod-clientid").data("email"));
    DP.setEmailAttachModal(
      $("#certificatesGrid").jqGrid("getCell", rowid, "filename")
    );
    DP.setEmailAttachHostPathModal(
      $("#certificatesGrid").jqGrid("getCell", rowid, "hostpath")
    );
    $("#emailModal").modal("show");
  },

  onRemoveCertificate: function (rowid) {
    var doc = {};
    doc.id = $("#certificatesGrid").jqGrid("getCell", rowid, "id");

    // remove from the file GDrive and host
    removeFileFromHostAndGdrive(
      $("#certificatesGrid").jqGrid("getCell", rowid, "hostpath"),
      $("#certificatesGrid").jqGrid("getCell", rowid, "gdrivepath")
    );

    $.post("ajax/ajaxHandler.php", {
      rtype: "removeCertificate",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      jQuery("#certificatesGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Certificate was deleted successfully");
    });
  },

  sendAddCertificateRequest: function () {
    var d = {};
    d.filename = $("#certificateModal .filename").val();
    d.idclient = $("#prod-clientid").val();
    d.url = $("#certificateModal .url").val();
    d.hostpath = $("#certificateModal .hostpath").val();
    d.gdrivepath = $("#certificateModal .gdrivepath").val();
    d.expdate = $("#certificateModal .expdate").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "addCertificate",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "New certificate was added successfully");
      $("#certificatesGrid").jqGrid().trigger("reloadGrid");
      $("#certificateModal").modal("hide");
    });
  },

  sendModifyCertificateRequest: function () {
    var d = {};
    d.id = $("#certificateModal .id").val();
    d.expdate = $("#certificateModal .expdate").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "editCertificate",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Expiry date was added successfully");
      $("#certificatesGrid").jqGrid().trigger("reloadGrid");
      $("#certificateModal").modal("hide");
    });
  },

  onAddFileInfoFailed: function (s) {
    $("#files-container .alert-string").text(s);
    setTimeout(function () {
      $("#files-container .alert-string").text("");
    }, 3000);
  },

  onAddFileInfoSuccess: function () {
    $("#file-name").val("");
    $("#file-link").val("");
    $("#files-container .success-string").text(
      "The file info successfully added"
    );
    setTimeout(function () {
      $("#files-container .success-string").text("");
    }, 2000);
  },

  onAddFileInfo: function () {
    if (!($("#file-name").val() && $("#file-link").val())) {
      DP.onAddFileInfoFailed("Please specify the information");
      return;
    }
    DP.saveFile();
  },

  saveFile: function () {
    var d = {};
    d.filename = $("#file-name").val();
    d.gdrivepath = $("#file-link").val();
    DP.showLoaderById("#file-loader");
    DP.hideLoaderById("#file-add");
    $.post("ajax/ajaxHandler.php", { rtype: "addFile", uid: 0, data: d }).done(
      function (data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
          DP.onAddFileInfoFailed(response.statusDescription);
        } else {
          DP.onAddFileInfoSuccess();
          $("#filesGrid").jqGrid().trigger("reloadGrid");
        }
        DP.showLoaderById("#file-add");
        DP.hideLoaderById("#file-loader");
      }
    );
  },

  validateEmailForm: function () {
    if ($("#email-from").val().trim() == "") {
      Utils.notifyInput($("#email-from"), "Incorrect sender's name specified");
      return false;
    }
    if (!validateEmailsList($("#email-to").val().trim())) {
      Utils.notifyInput($("#email-to"), "Incorrect email(s) specified");
      return false;
    }
    if ($("#email-cc").val().trim() !== "")
      if (!validateEmailsList($("#email-cc").val().trim())) {
        Utils.notifyInput($("#email-cc"), "Incorrect email(s) specified");
        return false;
      }
    if ($("#email-subject").val().trim() == "") {
      Utils.notifyInput($("#email-subject"), "No subject specified");
      return false;
    }
    if ($("#email-message").val().trim() == "") {
      Utils.notifyInput($("#email-message"), "No information provided");
      return false;
    }
    return true;
  },

  onSendEmail: function () {
    DP.clearEmailAlerts();
    if (!DP.validateEmailForm()) return;
    DP.showEmailLoader();
    var email = {};
    email.name = $("#email-from").val().trim();
    email.email = "noreply@halal-e.zone";
    email.to = $("#email-to").val().trim();
    email.cc = $("#email-cc").val().trim();
    email.subject = $("#email-subject").val().trim();
    email.message = $("#email-message").val().trim();
    email.attachhostpath = $("#email-attach-hostpath").val();
    email.attach = $("#email-attach").val();
    email.header = "";
    $.post("ajax/ajaxHandler.php", {
      uid: userId,
      rtype: "sendEmailMessage",
      data: email,
    }).done(function (data) {
      var response = JSON.parse(data);
      DP.hideEmailLoader();
      if (response.status === 0) {
        $("#email-formerror").html(response.statusDescription);
        return;
      }
      $("#emailModal").modal("hide");
      Utils.notify("error", "Your message been sent successfully");
    });
  },
};

// PRODUCT PAGE
var PP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("prodItem");

    PP.multiselect = true;
    (PP.enableMultiselect = function (isEnable) {
      jQuery("#prodGrid").jqGrid("setGridParam", {
        multiselect: isEnable ? true : false,
      });
    }),
      (PP.events = null);
    PP.originalReloadGrid = null;

    $("input").focus(function () {
      // PP.clearAlerts();
    });
    $("select").change(function () {
      //PP.clearAlerts();
    });

    $("#additionalItemsCycleModal").on("shown.bs.modal", function () {
      // Clear existing dropdown options
      $("#additionalItemsCycleId")
        .empty()
        .append('<option value="">Loading...</option>');

      $.ajax({
        url: "ajax/getCycles.php",
        method: "POST",
        data: {
          idclient: $("#prod-clientid").val(),
          page: 1,
          rows: 100,
          sidx: "id",
          sord: "asc",
        },
        dataType: "json",
        success: function (response) {
          var options =
            '<option value="">-- Select Certification Cycle --</option>';

          if (response.rows && response.rows.length > 0) {
            $.each(response.rows, function (index, item) {
              const id = item.id;
              const name = item.cell[1]; // Assuming cell[1] = name
              options += `<option value="${id}">${name}</option>`;
            });
          } else {
            options += '<option value="">No cycles found</option>';
          }

          $("#additionalItemsCycleId").html(options);
        },
        error: function () {
          $("#additionalItemsCycleId").html(
            '<option value="">Error loading data</option>'
          );
        },
      });
    });

    $('[data-toggle="tooltip"]').tooltip();

    $("#filter-conformed").on("change", function (e) {
      jQuery("#prodGrid").jqGrid("setGridParam", {
        url:
          "ajax/getProd.php?displaymode=" +
          PP.gridMode +
          "&idclient=" +
          $("#prod-clientid").val() +
          "&conformed=" +
          ($("#filter-conformed").prop("checked") ? 1 : 0),
      });
      jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
    });

    $("#prod-clientid").on("change", function () {
      PP.loadIngredientsForProductData(PP.populateIngredientsForProduct);
      $("#prod-clientid").data(
        "clientname",
        $("#prod-clientid option:selected").data("clientname")
      );
      $("#filter-conformed").trigger("change");
    });

    PP.loadClientsList();

    $("#prodModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") === 0) {
        PP.sendRemoveProductRequest();
      } else jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
      PP.loadIngredientsForProductData(PP.populateIngredientsForProduct);
    });

    PP.gridMode = 0; // removed records mode. if 1 - show removed records only;

    $("#prod-form #ingredients").select2({
      closeOnSelect: false,
    });

    // initialize file uploaders in product modal
    initFileUploader({
      fileUploadSelector: "#prodModal .fileupload",
      dropzoneSelector: "#prodModal .dropzone",
      progressSelector: "#prodModal .progress",

      dataModifier: function (e, data) {
        data.formData = {
          folderType: $(e.target).attr("folderType"), // for audit
          infoType: $(e.target).attr("infoType"),
          client: $("#prod-clientid").data("clientname"),
          product: $("#prod-form #hcpid").val(),
          idclient: $("#prod-clientid").val(),
          idproduct: $("#prod-form #id").val(),
        };
      },

      afterSuccess: function (e, file) {
        PP.filesUploaded.push({ file: file.name });
      },
    });

    $(document).on("keyup", "#prod-form #item", function (e) {
      if (PP.checkBannedWords()) {
        Utils.notifyInput(
          $("#prod-form #item"),
          "Item contains forbidden words. Please review and correct."
        );
        return;
      } else {
        Utils.notifyInput($("#prod-form #item"), "");
      }
    });

    PP.loadIngredientsForProductData(PP.populateIngredientsForProduct);
  },

  loadClientsList: function () {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "clients" }).done(function (
      data
    ) {
      new Promise(function (resolve) {
        /*
        var response = JSON.parse(data);        
        $(".clientslist").empty();
        $(".clientslist").append(
          $("<option>", { text: "Select client", value: "", selected: true })
        );
        response.data.clients.forEach(function (cl) {
          $(".clientslist").append(
            $("<option>", {
              value: cl.id,
              "data-clientname": cl.name + " (" + cl.prefix + cl.id + ")",
              text: cl.name + " - " + cl.prefix + cl.id,
            })
          );
        });
        if ($("#filter-idclient").length && $("#filter-idclient").val().length)
          $(".clientslist").val($("#filter-idclient").val());
        */
        resolve("resolve loaded");
      }).then(function (res) {
        PP.initGrid();
      });
    });
  },

  isAdminSession: function () {
    return $("#prod-clientid").length > 0;
  },

  moveTableColumn: function (rows, iCol, className) {
    var rowsCount = rows.length,
      iRow,
      row,
      $row;
    for (iRow = 0; iRow < rowsCount; iRow += 1) {
      row = rows[iRow];
      $row = $(row);
      if (!className || $row.hasClass(className)) {
        $row.append(row.cells[iCol]);
      }
    }
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 350;
    new Promise(function (resolve) {
      $("#prodGrid").jqGrid({
        url:
          "ajax/getProd.php?displaymode=" +
          PP.gridMode +
          "&idclient=" +
          $("#prod-clientid").val() +
          "&conformed=" +
          ($("#filter-conformed").prop("checked") ? 1 : 0),
        datatype: "json",
        mtype: "POST",
        width: $("#prodGrid").parent().width(),
        height: h,
        colModel: [
          { index: "id", name: "id", align: "left", hidden: true, key: true },
          {
            label: "HCP_ID",
            name: "hcpid",
            index: "hcpid",
            align: "left",
            width: 60,
          },
          { name: "Item", index: "item", align: "left", width: 100 },
          {
            label: "Item №/EAN code",
            name: "EAN",
            index: "ean",
            align: "left",
            width: 120,
          },
          {
            label: "Ingredients",
            name: "ingred",
            index: "ingred",
            align: "left",
            classes: "plain_bg",
            width: 120,
            formatter: formatInglist,
            unformat: unformatInglist,
          },
          {
            name: "Specification",
            index: "spec",
            align: "left",
            formatter: formatDoclink,
            width: 120,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title="';
            },
          },
          {
            label: "Additional Documents",
            name: "Addocs",
            index: "addocs",
            align: "left",
            width: 120,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            label: "Label",
            name: "Label",
            index: "label",
            align: "left",
            width: 120,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          { name: "conformed", index: "conformed", hidden: true },
          { name: "status", index: "status", hidden: true },
          { name: "deleted", index: "deleted", hidden: true },
          { name: "Creation date", index: "created_at" },
          { name: "idclient", index: "idclient", hidden: true },
          {
            label: "Deleted",
            name: "deleted",
            index: "deleted",
            formatter: formatProductRestoreButton,
            editable: false,
            hidden: !PP.isAdminSession(),
          },
          {
            label: "Deletion Date",
            name: "deleted_at",
            index: "deleted_at",
            editable: false,
            hidden: !PP.isAdminSession(),
          },
        ],
        rowNum: -1,
        rowList: [], // disable page size dropdown
        pgbuttons: false, // disable page control like next, back button
        pgtext: null, // disable pager text like 'Page 0 of 10'
        pager: "#prodPager",
        sortname: "item",
        sortorder: "asc",
        shrinkToFit: true,
        viewrecords: true,
        multiselect: true,
        toppager: true,
        subGrid: true,
        gridComplete: function () {
          initFileUploader({
            fileUploadSelector: "#gbox_prodGrid .fileupload",
            dropzoneSelector: "#gbox_prodGrid .p-dropzone",
            progressSelector: "#gbox_prodGrid .progress",

            dataModifier: function (e, data) {
              data.formData = {
                folderType: $(e.target).attr("folderType"), // for audit
                infoType: $(e.target).attr("infoType"),
                client: $("#prod-clientid").data("clientname"),
                product: $("#prod-form #hcpid").val(),
                idclient: $("#prod-clientid").val(),
                idproduct: $("#prod-form #id").val(),
              };
            },

            onSuccess: function (e, data) {
              // attach the uploaded files to product and reload grid
              $(e.target).parent().siblings(".progress").hide();
              $(e.target).parent().show();

              if (!data.result.files.length) {
                return;
              }

              const fileData = {
                name: data.result.files[0].name,
                glink: data.result.files[0].googleDriveUrl,
                hostpath: data.result.files[0].url,
                hostUrl: data.result.files[0].hostUrl,
              };

              const FD = new FormData();
              FD.append("id", $(e.target).closest("tr").attr("id"));
              FD.append("rtype", "addProductFiles");

              const colName = {
                spec: "spec",
                add: "addoc",
                label: "label",
              }[data.result.files[0].folderType];

              FD.append(colName, JSON.stringify(fileData));

              fetch("/ajax/ajaxHandler.php", {
                method: "POST",
                credentials: "include",
                body: FD,
              })
                .then((r) => r.json())
                .then((j) => {
                  if (j.status != "1") {
                    alert("There was an error attaching the files.");
                    return;
                  }

                  // Finally reload the grid to show the new files in the cell
                  $("#prodGrid").jqGrid().trigger("reloadGrid");
                });

              PP.filesUploaded?.push({ file: data.result.files[0].name });
            },
          });
        },
        subGridOptions: {
          plusicon: "ace-icon fa fa-plus center bigger-110 blue",
          minusicon: "ace-icon fa fa-minus center bigger-110 blue",
          openicon: "ace-icon fa fa-chevron-right center orange",
        },
        subGridRowExpanded: function (subgrid_id, row_id) {
          var subgridTableId = subgrid_id + "_t";
          $("#" + subgrid_id).html(
            "<table id='" + subgridTableId + "' class='scroll'></table>"
          );
          $("#" + subgridTableId).jqGrid({
            datatype: "json",

            url: "ajax/getIngredientsForProduct.php?idproduct=" + row_id,
            rowNum: 20,
            altRows: true,
            shrinkToFit: true,
            sortname: "rmcode",
            sortorder: "asc",
            rowattr: function (rd) {
              var rowclass = "";
              if (rd.deleted === "1") rowclass += "deleted ";
              else {
                if (rd.Conformed === "1") {
                  rowclass += " highlighted-conformed ";
                } else {
                  rowclass += " highlighted-nonconformed ";
                }
                switch (rd.status) {
                  case "1":
                    rowclass += " highlighted-8week ";
                    break; // 8 weeks
                  case "2":
                    rowclass += " highlighted-4week ";
                    break; // 4 weeks
                  case "3":
                    rowclass += " highlighted-week ";
                    break; // 1 week
                  case "4":
                    rowclass += " highlighted-expired ";
                    break; // 1 week
                  // case '0' :
                  //  rowclass = {'class': 'highlighted-week'};
                  //  break;
                }
              }
              rowclass = { class: rowclass };
              return rowclass;
            },

            colModel: [
              {
                index: "id",
                name: "id",
                align: "left",
                hidden: true,
                key: true,
              },
              { name: "RMC ID", index: "rmid", align: "left", width: 100 },
              {
                label: "RM Code",
                name: "rmcode",
                index: "rmcode",
                align: "left",
                width: 200,
              },
              { name: "Name", index: "name", align: "left", width: 220 },
              {
                label: "Supplier",
                name: "Supplier",
                index: "supplier",
                align: "left",
                width: 220,
              },
              {
                label: "Source of raw material",
                name: "material",
                index: "material",
                width: 100,
                align: "left",
              },
              {
                label: "Halal Certified",
                name: "Certified",
                index: "halalcert",
                width: 90,
                align: "center",
                stype: "select",
                editoptions: { value: "1:Yes;0:No" },
                formatter: "select",
              },
              {
                label: "Halal Certificate",
                name: "Certificate",
                index: "cert",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              {
                label: "Name of Halal certification body",
                name: "CB",
                index: "cb",
                align: "left",
                width: 120,
                editable: true,
                edittype: "text",
                editoptions: { maxlength: 100 },
                editrules: { required: true },
                formoptions: { colpos: 2, rowpos: 4 },
                search: true,
              },
              {
                label: "Cert. Exp. Date",
                name: "Date",
                index: "halalexp",
                align: "center",
                width: 120,
                sorttype: "date",
                formatter: "date",
                formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
                dataInit: function (element) {
                  $(element).datepicker({
                    autoUpdateInput: false,
                    autoclose: true,
                    format: "dd M yyyy",
                    orientation: "bottom",
                  });
                },
              },
              {
                label: "Halal Conformed",
                name: "Conformed",
                index: "conf",
                width: 90,
                align: "center",
                stype: "select",
                editoptions: { value: "1:Yes;0:No" },
                formatter: "select",
              },
              { name: "status", index: "status", hidden: true },
              {
                label: "Ingredients",
                name: "ingred",
                index: "ingred",
                align: "left",
                search: false,
                width: 120,
                editable: true,
                edittype: "custom",
                formatter: formatCat1,
              },
              {
                label: "Product specification",
                name: "Specification",
                index: "spec",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              {
                label: "Supplier questionnaire",
                name: "Questionnaire",
                index: "quest",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              {
                label: "Supplier Statement",
                name: "Statement",
                index: "statement",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              {
                label: "Additional Documents",
                name: "Addocs",
                index: "addoc",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              {
                label: "Label",
                name: "Label",
                index: "label",
                align: "left",
                width: 220,
                formatter: formatDoclink,
              },
              { name: "Note", index: "note", align: "left", width: 300 },
            ],
          });
        },
        rowattr: function (rd) {
          var rowclass = "";
          if (rd.deleted === "1") rowclass += " deleted ";
          else {
            if (rd.conformed === 0) rowclass += " highlighted-nonconformed ";
            else {
              rowclass += " highlighted-conformed ";
            }
            switch (rd.status) {
              case "1":
                rowclass += " highlighted-8week ";
                break; // 8 weeks
              case "2":
                rowclass += " highlighted-4week ";
                break; // 4 weeks
              case "3":
                rowclass += " highlighted-week ";
                break; // 1 week
              case "4":
                rowclass += " highlighted-expired ";
                break; // 1 week

              /*
                case "0":
                  if (rd.conformed === 1)
                    rowclass = { class: "highlighted-conformed" };
                  break;
          */
            }
            //}
          }
          rowclass = { class: rowclass };
          return rowclass;
        },
        caption: "",
        loadComplete: function (data) {
          // add event listeners to upload areas to change their appearance when a file is dragged
          document
            .querySelectorAll(".upload-area")
            .forEach((area) =>
              area.addEventListener("dragover", handleDragOver)
            );
          document
            .querySelectorAll(".upload-area")
            .forEach((area) => area.removeAttribute("title"));
          document
            .querySelectorAll(".upload-area")
            .forEach((area) =>
              area.addEventListener("dragleave", handleDragLeave)
            );
          document
            .querySelectorAll(".upload-area")
            .forEach((area) => area.addEventListener("drop", handleDrop));

          Common.updatePagerIcons(this);
          //$(this).jqGrid("hideCol", "cb");
          PP.enableMultiselect.call(this, PP.multiselect);
        },
        onPaging: function () {
          PP.enableMultiselect.call(this, PP.multiselect);
        },
        onSortCol: function () {
          PP.enableMultiselect.call(this, PP.multiselect);
        },
      });

      $("#prodGrid").jqGrid("navGrid", "#prodPager", {
        cloneToTop: true,
        edit: true,
        add: true,
        del: true,
        search: false,
        refresh: true,
        view: false,
        addfunc: function () {
          PP.newProduct();
        },
        editfunc: function () {
          PP.editProduct();
        },
        delfunc: function () {
          PP.deleteProduct();
        },
      });
      $("#prodGrid").jqGrid("filterToolbar", { enableClear: false });
      $("#prodGrid").navButtonAdd("#prodPager", {
        caption: "",
        title: "Export confirmed products to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function () {
          PP.onExportConfirmedProductsToExcel();
        },
      });
      $("#prodGrid").navButtonAdd("#prodPager", {
        caption: "",
        title: "Export all products to Excel",
        buttonicon: "ace-icon fa fa-list",
        onClickButton: function () {
          PP.onExportAllProductsToExcel();
        },
      });
      $("#prodGrid").navButtonAdd("#prodPager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          PP.onToggleRemovedRecordsMode(event);
        },
      });

      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Export grid to Excel",
        buttonicon: "ace-icon fa fa-download",
        onClickButton: function () {
          PP.onExportGridToExcel();
        },
      });
      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Export confirmed products to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function () {
          PP.onExportConfirmedProductsToExcel();
        },
      });
      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Export all products to Excel",
        buttonicon: "ace-icon fa fa-list",
        onClickButton: function () {
          PP.onExportAllProductsToExcel();
        },
      });
      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          PP.onToggleRemovedRecordsMode(event);
        },
      });
      /*
      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Toggle multiselect mode",
        buttonicon: "ace-icon fa fa-check-square-o",
        onClickButton: function () {
          PP.onResetSelection();
        },
      });
    */
      $("#prodGrid").navButtonAdd("#prodPager", {
        caption: "",
        title: "Add to Additional Items Application",
        buttonicon: "ace-icon fa fa-file",
        onClickButton: function () {
          PP.onExportGridToAdditionalItems("pdf");
        },
      });
      $("#prodGrid").navButtonAdd("#prodGrid_toppager", {
        caption: "",
        title: "Add to Additional Items Application",
        buttonicon: "ace-icon fa fa-file",
        onClickButton: function () {
          PP.onExportGridToAdditionalItems("pdf");
        },
      });
      /*
      $('#prodGrid').navButtonAdd('#prodPager', {
        caption: '',
        title: 'Add to Additional Items Application Excel',
        buttonicon: 'ace-icon fa fa-file',
        onClickButton: function () {
          PP.onExportGridToAdditionalItems('xls');
        },
      });
      $('#prodGrid').navButtonAdd('#prodGrid_toppager', {
        caption: '',
        title: 'Add to Additional Items Application Excel',
        buttonicon: 'ace-icon fa fa-file',
        onClickButton: function () {
          PP.onExportGridToAdditionalItems('xls');
        },
      });
      */

      resolve("grid inited");
    }).then(function (res) {
      // initialize the reactivity of drop areas in the grid
      document
        .querySelector("body")
        .addEventListener("dragover", handleDragOverDocument);
      document
        .querySelector("body")
        .addEventListener("dragleave", handleDragLeaveDocument);
      document
        .querySelector("body")
        .addEventListener("drop", handleDropDocument);

      setTimeout(function () {
        $("input#gs_hcpid").val($("input#filter-hcpid").val());
        PP.filterGrid();
      }, 1);
    });
    PP.events = $("#prodGrid").data("events"); // read all events bound to
    // Verify that one reloadGrid event hanler is set. It should be set
    if (
      PP.events &&
      PP.events.reloadGrid &&
      PP.events.reloadGrid.length === 1
    ) {
      originalReloadGrid = PP.events.reloadGrid[0].handler; // save old
      $("#prodGrid").unbind("reloadGrid");
      $("#prodGrid").bind("reloadGrid", function (e, opts) {
        PP.enableMultiselect.call($("#prodGrid"), true);
        originalReloadGrid.call($("#prodGrid"), e, opts);
      });
    }
  },

  filterGrid: function () {
    new Promise(function (resolve) {
      $("#prodGrid").jqGrid("setGridParam", { search: true });
      $("#prodGrid").jqGrid("setGridParam", {
        postData: {
          hcpid: $("input#filter-hcpid").val(),
          //displaymode: PP.gridMode,
          //idclient: $('.clientslist').val(),
          //conformed: ($('#filter-conformed').prop('checked') ? 1 : 0)
        },
      });
      $("#filter-idclient").val("");
      $("#filter-hcpid").val("");
      resolve("params done");
    }).then(function (res) {
      jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onToggleRemovedRecordsMode: function (e) {
    if (PP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      PP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      PP.gridMode = 1;
    }
    $("#filter-conformed").trigger("change");
  },

  onResetSelection: function (e) {
    if (PP.multiselect) {
      $(".fa-check-square-o").removeClass("red");
    } else {
      $(".fa-check-square-o").addClass("red");
    }
    PP.multiselect = !PP.multiselect;
    PP.enableMultiselect.call(jQuery("#prodGrid"), PP.multiselect);
    jQuery("#prodGrid").trigger("reloadGrid");
  },

  loadIngredientsForProductData: function (callback) {
    var prod = {};
    prod.idclient = $("#prod-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      data: prod,
      rtype: "ingredientsForProduct",
    }).done(callback);
  },

  populateIngredientsForProduct: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }

    var filteredIngredients = response.data.ingredients.filter(function (item) {
      return item.text.trim() !== "";
    });

    // $("#prod-form #ingredients").select2().empty();
    // $("#prod-form #ingredients").select2("open");
    $("#prod-form #ingredients")
      .select2({
        dropdownParent: $("#prodModal .modal-content"),
        scrollAfterSelect: false,
        closeOnSelect: false,
        data: filteredIngredients,
      })
      .on("select2:select", function (e) {
        // Hide the selected item in the dropdown
        var selectedOption = $(this).find('[aria-selected="true"]');
        selectedOption.css("display", "none");
      })
      .on("select2:unselect", function (e) {
        // Show the previously selected item when unselecting
        var selectedOption = $(this).find('[aria-selected="true"]');
        selectedOption.css("display", "");
      })

      // .on('select2:select', function () {
      //     $(this).select2('close').select2('open');
      // })
      .trigger("change");
  },

  onExportGridToAdditionalItems: function (format = "xls") {
    // Show the year selection modal first
    $("#additionalItemsCycleModal").modal("show");

    // Attach a one-time event listener to detect user selection and confirmation
    $("#additionalItemsCycleModal")
      .off("click.confirm")
      .on("click.confirm", "#confirmYearSelection", function () {
        var selectedYear = $("#additionalItemsCycleId").val();

        if (!selectedYear) {
          Utils.notify("error", "Please select certification cycle.");
          return;
        }

        // Hide the modal
        $("#additionalItemsCycleModal").modal("hide");

        // Proceed with the export logic
        $("#infoModal").modal("show");

        var doc = {};
        doc.idclient = $("#prod-clientid").val();
        doc.conformed = $("#filter-conformed").prop("checked") ? 1 : 0;
        doc.displaymode = PP.gridMode;
        doc.ids = $("#prodGrid").getGridParam("selarrrow");
        doc.format = format;
        doc.idcycle = selectedYear; // Pass selected year to server

        $.post("ajax/ajaxHandler.php", {
          rtype: "sendAdditionalItemsApplicationRequest",
          uid: 0,
          data: doc,
        }).done(function (data) {
          var response = JSON.parse(data);
          $("#infoModal").modal("hide");

          if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
          }

          downloadURI(response.data.url, response.data.name);
        });
      });
  },

  onExportGridToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#prod-clientid").val();
    doc.conformed = $("#filter-conformed").prop("checked") ? 1 : 0;
    doc.displaymode = PP.gridMode;
    doc.ids = $("#prodGrid").getGridParam("selarrrow");

    $.post("ajax/ajaxHandler.php", {
      rtype: "sendProductsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onExportAllProductsToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#prod-clientid").val();
    doc.conformed = $("#filter-conformed").prop("checked") ? 1 : 0;
    doc.displaymode = PP.gridMode;
    doc.ids = $("#prodGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendAllProductsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      $("#infoModal").modal("hide");
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onExportConfirmedProductsToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#prod-clientid").val();
    doc.conformed = $("#filter-conformed").prop("checked") ? 1 : 0;
    doc.displaymode = PP.gridMode;
    doc.ids = $("#prodGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendConfirmedProductsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      $("#infoModal").modal("hide");
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  clearForm: function () {
    PP.clearAlerts();
    $("#ulspec").empty();
    $("#uladd").empty();
    $("#ullabel").empty();
    $("#prod-form input").val("");
    $("#prod-form select").val(null).trigger("change");
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (!response.data.product) {
      $("#prod-form #hcpid").val("HCP_" + response.data.id);
      $("#prod-form #hcpid").attr("data-id", response.data.id);
      $("#prod-form #hcpid").attr("data-new", 1);
    } else {
      var prod = response.data.product;
      $("#prod-form #hcpid").val("HCP_" + prod.id);
      $("#prod-form #hcpid").attr("data-id", prod.id);
      $("#prod-form #hcpid").attr("data-new", 0);
      $("#prod-form #item").val(prod.item);
      $("#prod-form #ean").val(prod.ean);
      $("#prod-form #ingredients").val(prod.ingredients);
    }
    $("#prodModal").prop("submit", 0);
    PP.filesUploaded = [];
    $("#prodModal").modal("show");
  },

  getNextProdId: function (callback) {
    var prod = {};
    prod.idclient = $("#prod-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      data: prod,
      rtype: "nextProdId",
    }).done(callback);
  },

  newProduct: function () {
    if ($("#prod-clientid").val() == "") {
      alert("Please select client");
      return;
    }
    PP.clearForm();

    $("#prodModal-label").text("New product");
    PP.getNextProdId(PP.fillForm);
  },

  editProduct: function () {
    if (
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select product");
      return;
    }
    PP.clearForm();

    $("#prodModal-label").text("Edit Product");
    $("#prod-form #hcpid").val(
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "hcpid"
      )
    );
    $("#prod-form #hcpid").attr(
      "data-id",
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "id"
      )
    );
    $("#prod-form #hcpid").attr("data-new", 0);
    $("#prod-form #item").val(
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "Item"
      )
    );
    $("#prod-form #ean").val(
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "EAN"
      )
    );
    $("#prod-form #ingredients").val(
      jQuery("#prodGrid")
        .jqGrid(
          "getCell",
          jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
          "ingred"
        )
        .replace(", ", ",")
        .split(",")
    );
    $("#prod-form #ingredients").trigger("change");
    Utils.filesToList("ulspec", "prodGrid", "Specification");
    Utils.filesToList("uladd", "prodGrid", "Addocs");
    Utils.filesToList("ullabel", "prodGrid", "Label");
    $("#prodModal").prop("submit", 1);
    PP.filesUploaded = [];
    $("#prodModal").modal("show");
  },

  deleteProduct: function () {
    if (
      jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select product");
      return;
    }
    if (confirm("Delete the product?")) {
      PP.sendDeleteProductRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.idclient = $("#prod-clientid").val();
    doc.id = $("#prod-form #hcpid").attr("data-id");
    doc.item = $("#prod-form #item").val().trim();
    doc.ean = $("#prod-form #ean").val().trim();
    doc.ingredients = $("#prod-form #ingredients").val();
    doc.spec = Utils.filesToJSON("ulspec");
    doc.addoc = Utils.filesToJSON("uladd");
    doc.label = Utils.filesToJSON("ullabel");
    return doc;
  },

  validateForm: function () {
    if ($("#prod-form #item").val().trim() == "") {
      Utils.notifyInput($("#prod-form #item"), "No Item specified");
      return;
    }
    if (PP.checkBannedWords()) {
      Utils.notifyInput(
        $("#prod-form #item"),
        "Item contains forbidden words. Please review and correct."
      );
      return;
    }
    if ($("#prod-form #ean").val().trim() == "") {
      Utils.notifyInput($("#prod-form #ean"), "No EAN Code specified");
      return;
    }
    if ($("#prod-form #ean").val().trim() == "") {
      Utils.notifyInput($("#prod-form #ean"), "No EAN Code specified");
      return;
    }
    if ($("#prod-form #fileupload1").get(0).files.length === 0) {
      // Check if there are elements marked as selected but not deleted
      var selectedNotDeleted = $("#prod-form .uploaded-file-name").not(
        ".deleted"
      );
      if (selectedNotDeleted.length === 0) {
        $("#prod-form #dropzone1")
          .nextAll(".alert-string")
          .first()
          .text("Specification is mandatory field.");
        return;
      } else {
        // Handle the case where there are selected elements that are not deleted
        // You can perform actions here based on your requirements
      }
    } else {
      $("#prod-form #dropzone1").nextAll(".alert-string").first().empty();
    }

    return true;
  },

  checkBannedWords: function () {
    var ret = false;
    $.ajax({
      type: "GET",
      url: "ajax/CheckBannedWords.php",
      cache: false,
      async: false,
      data: { text: $("#prod-form #item").val() },
      success: function (result) {
        if (result == "1") {
          ret = true;
        } else {
          ret = false;
        }
      },
      error: function (jqXHR, status, message) {},
    });
    return ret;
  },

  sendModifyProductRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveProduct",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      // add actions about new ingredient and new docs
      if (PP.filesUploaded.length > 0) {
        var d = {};
        d.itemid = doc.id;
        d.idclient = doc.idclient;
        d.itemcode = $("#prod-form #hcpid").val();
        d.itemtype = "products";
        d.itemname = doc.item;
        d.action = "New document";
        d.documents = JSON.stringify(PP.filesUploaded);
        Common.sendAddActionRequest(d);
      }
      $("#prodModal").prop("submit", 1);
      $("#prodModal").modal("hide");
    });
  },

  sendAddProductRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "addProduct",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "New product data was added");
      var d = {};
      d.itemid = doc.id;
      d.idclient = doc.idclient;
      d.itemcode = $("#prod-form #hcpid").val();
      d.itemtype = "products";
      d.itemname = doc.item;
      d.action = "New product added";
      Common.sendAddActionRequest(d);

      if (PP.filesUploaded.length > 0) {
        d.action = "New document";
        d.documents = JSON.stringify(PP.filesUploaded);
        Common.sendAddActionRequest(d);
      }

      $("#prodModal").prop("submit", 1);
      $("#prodModal").modal("hide");
    });
  },

  sendRemoveProductRequest: function () {
    var doc = { id: $("#prod-form #hcpid").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeProduct",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New product data was added");
    });
  },

  sendDeleteProductRequest: function () {
    var doc = {
      id: jQuery("#prodGrid").jqGrid(
        "getCell",
        jQuery("#prodGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "markDeletedProduct",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Product was deleted");
    });
  },

  onRestoreProd: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "restoreProd",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#prodGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onSave: function () {
    PP.clearAlerts();
    if (!PP.validateForm()) {
      return;
    }
    var doc = PP.createDocFromInputData();
    if ($("#prod-form #hcpid").attr("data-new") == 1)
      PP.sendAddProductRequest(doc);
    else PP.sendModifyProductRequest(doc);
  },
};

function userDetails() {
  var idingredient = $(this).data("idi");
  var d = {};
  d.idingredient = idingredient;
  var tooltiptext = "";
  $.ajax({
    type: "POST",
    url: "ajax/ajaxHandler.php",
    data: {
      rtype: "sendAllTasksToolTipRequest",
      uid: 0,
      data: d,
    },
    success: function (data) {
      var response = JSON.parse(data);
      tooltiptext = response.data;
    },
    async: false,
  });
  return tooltiptext;
}
var dynamic = false;
let jqGridRequest;

var IP = {
  onDocumentReady: function () {
    $("#processBulkCertBtn").on("click", function () {
      IP.processBulkHalalCertUpdate();
    });

    // Initialize datepicker for bulk expiry date
    $("#bulkExpiryDate").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
      todayHighlight: true,
    });

    $("#logModal").on("shown.bs.modal", function () {
      var table = $("#table_log").DataTable();
      table.ajax.reload(null, false);
    });

    $("#paModal").on("shown.bs.modal", function () {
      var table = $("#table_tank").DataTable();
      table.ajax.reload(null, false);
    });

    Common.setMainMenuItem("ingredItem");

    IP.gridMode = 0;

    $('[data-toggle="tooltip"]').tooltip();

    $("input").focus(function () {
      IP.clearAlerts();
    });

    $("select").change(function () {
      IP.clearAlerts();
    });

    $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });

    $(".datepicker")
      .datepicker()
      .on("changeDate", function (e) {
        IP.clearAlerts();
      });

    $("#ingred-clientid").on("change", function () {
      if (jqGridRequest) {
        jqGridRequest.abort();
      }
      const gridParams = {
        url:
          "ajax/getIngred.php?displaymode=" +
          IP.gridMode +
          "&idclient=" +
          this.value,
        // If we are viewing single client's records - disable pagination
        rowNum: isNaN(parseInt(this.value)) ? 20 : 1000000,
      };

      $(".ui-paging-pager").toggle(isNaN(parseInt(this.value)));

      IP.loadIngredientsForIngredientData(IP.populateIngredientsForIngredient);

      $("#ingred-clientid").data(
        "clientname",
        $("#ingred-clientid option:selected").data("clientname")
      );

      jQuery("#ingredGrid").jqGrid("setGridParam", gridParams);
      jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
    });

    IP.loadClientsList();

    // Initialize file upload logic for all "add ingredient" popup fields, except certificate
    initFileUploader({
      fileUploadSelector: "#ingred-form .fileupload",
      dropzoneSelector: "#ingred-form .dropzone",
      progressSelector: "#ingred-form .progress",

      dataModifier: function (e, data) {
        data.formData = {
          folderType: $(e.target).attr("folderType"), // for audit
          infoType: "ingredient",
          client: $("#ingred-clientid").data("clientname"),
          ingredient: $("#ingred-form #rmid").val(),
          idclient: $("#ingred-clientid").val(),
          idingredient: $("#ingred-form #id").val(),
        };
      },

      afterSuccess: function (e, file) {
        IP.filesUploaded.push({ file: file.name });
      },
    });

    // Initialize file upload logic for the certificate field in the "add ingredient" popup
    initFileUploader({
      fileUploadSelector: "#ingred-form .cert-fileupload",
      dropzoneSelector: "#ingred-form .cert-dropzone",
      progressSelector: "#ingred-form .cert-progress",

      dataModifier: function (e, data) {
        data.formData = {
          folderType: $(e.target).attr("folderType"), // for audit
          infoType: "ingredient",
          client: $("#ingred-clientid").data("clientname"),
          ingredient: $("#ingred-form #rmid").val(),
          idclient: $("#ingred-clientid").val(),
          idingredient: $("#ingred-form #id").val(),
        };
      },

      afterSuccess: function (e, file) {
        IP.filesUploaded.push({ file: file.name });
      },

      fileValidator: function (e, data) {
        const uploadFile = data.files[0];

        if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
          return "You can upload PDF file(s) only";
        }

        if (!$("#certified").prop("checked")) {
          return "Please set certified checkbox first";
        }

        return true;
      },
    });

    $(document).on("keyup", "#ingred-form #name", function (e) {
      if (IP.checkBannedWords()) {
        Utils.notifyInput(
          $("#ingred-form #name"),
          "Name contains forbidden words. Please review and correct."
        );
        return;
      } else {
        Utils.notifyInput($("#ingred-form #name"), "");
      }
    });

    $("#ingredModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        IP.sendRemoveIngredientRequest();
      } else jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
      IP.loadIngredientsForIngredientData(IP.populateIngredientsForIngredient);
    });

    $("#ingredModal").on("show.bs.modal", function (e) {
      IP.loadIngredientsForIngredientData(IP.populateIngredientsForIngredient);
    });

    if (IP.isAdminSession()) {
      IP.initTasksGrid();

      $(window).on("resize.jqGrid", function () {
        $("#tasksGrid").jqGrid(
          "setGridWidth",
          $("#tasksModal .tasks-container").width()
        );
      });

      $("#tasksModal").on("shown.bs.modal", function (e) {
        $("#tasksGrid").jqGrid(
          "setGridWidth",
          $("#tasksModal .tasks-container").width()
        );
      });
    } else {
      IP.initActiveTasksGrid();

      $("#ingredModal").on("shown.bs.modal", function (e) {
        $("#activeTasksGrid").jqGrid(
          "setGridWidth",
          $("#activetasks-container").width()
        );
      });
    }

    $("#ingred-form #conformed").prop(
      "disabled",
      $("select#ingred-clientid").length == 0
    );

    $("#ingred-form #certified").on("change", function (e) {
      $("#ingred-form #cert-filegroup input").prop(
        "disabled",
        !$(e.target).prop("checked")
      );
      $("#ingred-form #cert-filegroup").attr(
        "disabled",
        !$(e.target).prop("checked")
      );
      $("#ingred-form #cert-filegroup *").attr(
        "disabled",
        !$(e.target).prop("checked")
      );
      $("#ingred-form #cb").prop("disabled", !$(e.target).prop("checked"));
      $("#ingred-form #date").prop("disabled", !$(e.target).prop("checked"));
      $("#ingred-form #rmposition").prop(
        "disabled",
        !$(e.target).prop("checked")
      );
    });

    // subingredient switch
    $("#ingred-form #subingredient").on("change", function (e) {
      if ($("#ingred-form #subingredient").prop("checked")) {
        $("#ingred-form #ingredients").val("").trigger("change");
        $("#ingred-form #ingredients").prop("disabled", true);
      } else $("#ingred-form #ingredients").prop("disabled", false);
    });

    $("#ingred-form #ingredients").select2({
      dropdownParent: $("#ingredModal .modal-content"),
      scrollAfterSelect: false,
      closeOnSelect: false,
    });
  },

  isAdminSession: function () {
    return $("select#ingred-clientid").length > 0;
  },

  loadClientsList: function () {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "clients" }).done(function (
      data
    ) {
      new Promise(function (resolve) {
        /*
        var response = JSON.parse(data);
        $(".clientslist").empty();

        // "Please select" option
        $(".clientslist").append(
          $("<option>", { text: "Please select", value: "-2", selected: true })
        );

        // "All clients" option
        $(".clientslist").append(
          $("<option>", { text: "All Clients", value: "" })
        );

        response.data.clients.forEach(function (cl) {
          $(".clientslist").append(
            $("<option>", {
              value: cl.id,
              "data-clientname": cl.name + " (" + cl.prefix + cl.id + ")",
              text: cl.name + " - " + cl.prefix + cl.id,
            })
          );
        });
        if ($("#filter-idclient").length && $("#filter-idclient").val().length)
          $(".clientslist").val($("#filter-idclient").val());
        */
        resolve("resolve loaded");
      }).then(function (res) {
        IP.initGrid();
      });
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 350;
    $("#tasksModal").on("hidden.bs.modal", function () {
      // put your default event here
      $("#ingredGrid").jqGrid().trigger("reloadGrid");
    });

    new Promise(function (resolve) {
      $("#ingredGrid").jqGrid({
        url:
          "ajax/getIngred.php?displaymode=" +
          IP.gridMode +
          "&idclient=" +
          $("#ingred-clientid").val(),
        datatype: "json",
        mtype: "POST",
        width: $("#ingredGrid").parent().width(),
        height: h,
        colModel: [
          {
            index: "id",
            name: "id",
            align: "left",
            hidden: true,
            key: true,
            frozen: true,
          },
          {
            label: "RMC_ID",
            name: "rmid",
            index: "rmid",
            align: "left",
            search: true,
            width: 100,
            frozen: true,
          },
          {
            label: "RM Code",
            name: "rmcode",
            index: "rmcode",
            search: true,
            align: "left",
            frozen: true,
          },
          {
            name: "Name",
            index: "name",
            align: "left",
            width: 200,
            search: true,
            frozen: true,
          },
          {
            label: "Tasks",
            name: "tasks",
            index: "tasks",
            align: "left",
            width: 100,
            search: true,
            stype: "select",
            searchoptions: { value: ":[All];1:Assigned;0:No tasks" },
            frozen: true,
            formatter: formatTasksFlag,
          },
          {
            label: "Halal Conformed",
            name: "Conformed",
            index: "conf",
            width: 90,
            align: "center",
            stype: "select",
            searchoptions: { value: ":[All];1:Yes;0:No" },
            frozen: true,
            formatter: formatButton,
            unformat: unformatButton,
          },
          { name: "tasksnumber", index: "tasksnumber", hidden: true },
          {
            label: "Subingredient",
            name: "sub",
            index: "sub",
            width: 90,
            align: "center",
            stype: "select",
            editoptions: { value: ":[All];1:Yes;0:No" },
            searchoptions: { value: ":[All];1:Yes;0:No" },
            formatter: "select",
          },
          {
            label: "Supplier",
            name: "Supplier",
            index: "supplier",
            align: "left",
            width: 200,
          },
          {
            label: "Producer",
            name: "producer",
            index: "producer",
            align: "left",
            width: 200,
          },
          {
            label: "Raw Material",
            name: "material",
            index: "material",
            width: 100,
            align: "left",
            stype: "select",
            editoptions: {
              value:
                ":[All];Animal:Animal;Plant:Plant;Synthetic:Synthetic;Mineral:Mineral;Cleaning agents:Cleaning agents;Packaging Material:Packaging Material;Others:Others",
            },
            searchoptions: {
              value:
                ":[All];Animal:Animal;Plant:Plant;Synthetic:Synthetic;Mineral:Mineral;Cleaning agents:Cleaning agents;Packaging Material:Packaging Material;Others:Others",
            },
            formatter: "select",
          },
          {
            label: "Halal Certified",
            name: "Certified",
            index: "halalcert",
            width: 90,
            align: "center",
            stype: "select",
            search: true,
            edittype: "select",
            searchoptions: { value: ":[All];1:Yes;0:No" },
            editoptions: { value: ":[All];1:Yes;0:No" },
            formatter: "select",
          },
          {
            label: "Halal Certificate",
            name: "Certificate",
            index: "cert",
            align: "left",
            width: 220,
            search: false,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            label: "Halal Certification Body",
            name: "CB",
            index: "cb",
            align: "left",
            width: 120,
            editable: true,
          },
          {
            label: "Cert. Exp. Date",
            name: "Date",
            index: "halalexp",
            align: "center",
            width: 120,
            sorttype: "date",
            formatter: "date",
            formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
            searchoptions: {
              dataInit: function (element) {
                $(element).datepicker({
                  autoUpdateInput: false,
                  autoclose: true,
                  format: "dd M yyyy",
                  orientation: "bottom",
                });
              },
            },
          },
          {
            label:
              'RM Position <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Raw material position in the certificate"></sup>',
            name: "rmposition",
            index: "rmposition",
            align: "left",
            width: 120,
            search: false,
          },
          {
            label: "Ingredients",
            name: "ingred",
            index: "ingred",
            align: "left",
            search: false,
            classes: "plain_bg",
            width: 120,
            formatter: formatInglist,
            unformat: unformatInglist,
          },
          {
            label: "Product Specification",
            name: "Specification",
            index: "spec",
            align: "left",
            width: 220,
            search: false,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            label: "Supplier Questionnaire",
            name: "Questionnaire",
            index: "quest",
            align: "left",
            width: 220,
            search: false,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            label: "Supplier Statement",
            name: "Statement",
            index: "statement",
            align: "left",
            width: 220,
            search: false,
            editable: true,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            label: "Additional Documents",
            name: "Addocs",
            index: "addoc",
            align: "left",
            width: 220,
            search: false,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            cellattr: function (rowId, val, rawObject, cm, rdata) {
              return 'class="upload-area" title=""';
            },
          },
          {
            name: "Note",
            index: "note",
            align: "left",
            width: 300,
            search: false,
          },
          { name: "status", index: "status", editable: false, hidden: true },
          { name: "Creation date", index: "created_at" },
          {
            label: "Deleted",
            name: "deleted",
            index: "deleted",
            formatter: formatIngredRestoreButton,
            editable: false,
            hidden: !IP.isAdminSession(),
          },
          {
            index: "id_paingred",
            name: "id_paingred",
            hidden: true,
          },
        ],
        rowNum: 20,
        rowList: [20, 60, 100, 500, 1000, 1500, 3000],
        pager: "#ingredPager",
        sortname: "tasks",
        viewrecords: true,
        sortorder: "desc",
        shrinkToFit: false,
        toppager: true,
        hoverrows: false,
        gridview: true,
        multiselect: true,
        loadBeforeSend: function (jqXHR) {
          // Store the jqXHR object so you can abort it later if needed
          jqGridRequest = jqXHR;
        },
        gridComplete: function () {
          // Add 5 file uploaders to each row of the grid, one per file column
          initFileUploader({
            fileUploadSelector: "#gbox_ingredGrid .fileupload",
            dropzoneSelector: "#gbox_ingredGrid .dropzone",
            progressSelector: "#gbox_ingredGrid .progress",

            onAdd: function (e, data) {
              if (data.originalFiles.length > 1) {
                alert("Error! You can upload only 1 file for this field.");
                return false;
              }

              data.formData = {
                folderType: $(e.target).attr("folderType"), // for audit
                infoType: "ingredient",
                client: $("#ingred-clientid").data("clientname"),
                ingredient: "RMC_" + $(e.target).closest("tr").attr("id"),
                idclient: $("#ingred-clientid").val(),
                idingredient: $(e.target).closest("tr").attr("id"),
              };

              var uploadFile = data.files[0];
              var foldertype = $(e.target).attr("foldertype");

              if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
                alert("You can upload PDF file(s) only");
                return false;
              }

              if (foldertype !== "cert") {
                data.submit();
                return;
              }

              // If they are trying to upload a certificate, request additional data via a popup
              function validateField(fieldId, errorMessage, isDateField) {
                var value = $(fieldId).val().trim();
                // Adding date check format if necessary
                if (isDateField) {
                  var dateRegex = /^[0-9]{2} [A-Za-z]{3} [0-9]{4}$/; // "30 Nov 2023"
                  if (!dateRegex.test(value)) {
                    $(fieldId)
                      .next(".alert-string")
                      .text("Please enter date in '30 Nov 2023' format")
                      .show();
                    return false;
                  }
                }
                if (!value) {
                  $(fieldId).next(".alert-string").text(errorMessage).show();
                  return false;
                } else {
                  $(fieldId).next(".alert-string").hide();
                  return true;
                }
              }

              $("#myModal").modal("show");
              $("#myModal button.btn.btn-default.submit")
                .off("click")
                .on("click", function (e) {
                  var isCbValid = validateField(
                    "#ingred-forms #cb",
                    "Please enter HC Body Name"
                  );
                  var isDateValid = validateField(
                    "#ingred-forms #date",
                    "Please enter HC Expiry Date",
                    true
                  ); // true to activate date check
                  var isRmPositionValid = validateField(
                    "#ingred-forms #rmposition",
                    "Please enter RM Position"
                  );
                  if (!isCbValid || !isDateValid || !isRmPositionValid) {
                    // Do not close the modal window if there are errors
                    e.preventDefault();
                    return;
                  }

                  // If the validation was successful, save the data
                  var cb = $("#ingred-forms #cb").val();
                  localStorage.setItem("cb", cb);
                  var date = $("#ingred-forms #date").val();
                  localStorage.setItem("date", date);
                  var rmposition = $("#ingred-forms #rmposition").val();
                  localStorage.setItem("rmposition", rmposition);
                  $("#myModal").modal("hide");

                  data.submit();
                });
            },
            onSuccess: function (e, data) {
              $(e.target).parent().siblings(".progress").hide();
              $(e.target).parent().show();

              if (!data.result.files.length) {
                return;
              }

              var cbValue = localStorage.getItem("cb");
              var dateValue = localStorage.getItem("date");
              var rmpositionValue = localStorage.getItem("rmposition");

              const fileData = {
                name: data.result.files[0].name,
                glink: data.result.files[0].googleDriveUrl,
                hostpath: data.result.files[0].url,
                hostUrl: data.result.files[0].hostUrl,
              };

              const FD = new FormData();
              FD.append("id", $(e.target).closest("tr").attr("id"));
              FD.append("rtype", "addIngredientFiles");
              if (cbValue) FD.append("cb", cbValue);
              if (dateValue) FD.append("date", dateValue);
              if (rmpositionValue) FD.append("rmposition", rmpositionValue);
              const colName = {
                cert: "cert",
                spec: "spec",
                quest: "quest",
                state: "statement",
                add: "addoc",
              }[data.result.files[0].folderType];

              FD.append(colName, JSON.stringify(fileData));

              fetch("/ajax/ajaxHandler.php", {
                method: "POST",
                credentials: "include",
                body: FD,
              })
                .then((r) => r.json())
                .then((j) => {
                  if (j.status != "1") {
                    alert("There was an error attaching the files.");
                    return;
                  }

                  // Finally reload the grid to show the new files in the cell
                  $("#ingredGrid").jqGrid().trigger("reloadGrid");
                });

              IP.filesUploaded?.push({ file: data.result.files[0].name });
            },
          });
        },

        beforeSelectRow: function (rowid, e) {
          if ($(e.target).is("span.set-conf")) {
            IP.onChangeConformity($(e.target).closest("tr.jqgrow").attr("id"));
            return false; // don't select the row on click on the button
          }
          return true; // select the row
        },

        loadComplete: function (data) {
          Common.updatePagerIcons(this);
          setTimeout(function () {
            $("input#gs_rmid").val($("input#filter-rmid").val());
            if (!dynamic && $("input#gs_rmid").val() != "") {
              IP.filterGrid();
            }
            $(".ingred-tooltip").tooltip({
              container: ".page-content",
              placement: "left",
              title: userDetails,
              offset: { top: 50 },
              html: true,
            });
          }, 500);

          // add event listeners to upload areas to change their appearance when a file is dragged
          document
            .querySelectorAll(".upload-area")
            .forEach((area) =>
              area.addEventListener("dragover", handleDragOver)
            );
          document
            .querySelectorAll(".upload-area")
            .forEach((area) =>
              area.addEventListener("dragleave", handleDragLeave)
            );
          document
            .querySelectorAll(".upload-area")
            .forEach((area) => area.addEventListener("drop", handleDrop));
        },

        beforeProcessing: function (data) {
          $("#ingredGrid").jqGrid("setGridParam", {
            recordtext:
              "View {0} - {1} of {2}, raw material codes total: " +
              data.rmcrecords,
          });
        },

        // Ingredient row color
        rowattr: function (rd) {
          //console.log(rd.name+"="+rd.Conformed+"\n");
          var rowclass = "";
          if (rd.deleted === "1") rowclass += "deleted ";
          else {
            if (rd.id_paingred && rd.id_paingred > 0) {
              rowclass += " highlighted-preconformed ";
            } else {
              if (rd.Conformed === "1") rowclass += " highlighted-conformed ";
              else rowclass += " highlighted-nonconformed ";
              switch (rd.status) {
                case "1":
                  rowclass += " highlighted-8week ";
                  break; // 8 weeks
                case "2":
                  rowclass += " highlighted-4week ";
                  break; // 4 weeks
                case "3":
                  rowclass += " highlighted-week ";
                  break; // 1 week
                case "4":
                  rowclass += " highlighted-expired ";
                  break; // 1 week
                // case '0' :
                //  rowclass = {'class': 'highlighted-week'};
                //  break;
              }
            }
          }
          rowclass = { class: rowclass };
          return rowclass;
        },
      });

      $("#ingredGrid").jqGrid("navGrid", "#ingredPager", {
        cloneToTop: true,
        edit: true,
        add: true,
        del: true,
        search: false,
        refresh: true,
        view: false,
        addfunc: function () {
          IP.newIngredient();
        },
        editfunc: function () {
          IP.editIngredient();
        },
        delfunc: function () {
          IP.deleteIngredient();
        },
      });
      $("#ingredGrid").jqGrid("filterToolbar", { enableClear: false });
      $("#ingredGrid")
        .jqGrid("setFrozenColumns")
        .trigger("reloadGrid", [{ current: true }]);
      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Add a new ingredient based on the selected",
        buttonicon: "ui-icon fa-plus",
        onClickButton: function () {
          IP.newCopiedIngredient();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Add a new ingredient based on the selected",
        buttonicon: "ui-icon fa-plus",
        onClickButton: function () {
          IP.newCopiedIngredient();
        },
      });
      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Download selected certificates.",
        buttonicon: "ace-icon fa fa-certificate",
        onClickButton: function () {
          IP.onExportCertificates();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Download selected certificates.",
        buttonicon: "ace-icon fa fa-certificate",
        onClickButton: function () {
          IP.onExportCertificates();
        },
      });
      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Download selected supplier questions.",
        buttonicon: "ace-icon fa fa-question-circle",
        onClickButton: function () {
          IP.onExportSupplierQuestions();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Download selected supplier questions.",
        buttonicon: "ace-icon fa fa-question-circle",
        onClickButton: function () {
          IP.onExportSupplierQuestions();
        },
      });
      //-------------------------------
      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Export ingredients with tasks to Excel",
        buttonicon: "ace-icon fa fa-download",
        onClickButton: function () {
          IP.onExportGridToExcel();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Export ingredients with tasks to Excel",
        buttonicon: "ace-icon fa fa-download",
        onClickButton: function () {
          IP.onExportGridToExcel();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Export all ingredients to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function () {
          IP.onExportAllGridToExcel();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Export all ingredients to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function () {
          IP.onExportAllGridToExcel();
        },
      });
      if (IP.isAdminSession()) {
        $("#ingredGrid").navButtonAdd("#ingredPager", {
          caption: "",
          title: "Set tasks for the ingredient",
          buttonicon: "ace-icon fa fa-tasks",
          onClickButton: function () {
            IP.onSetTasksForIngredient();
          },
        });
        $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
          caption: "",
          title: "Set tasks for the ingredient",
          buttonicon: "ace-icon fa fa-tasks",
          onClickButton: function () {
            IP.onSetTasksForIngredient();
          },
        });
      }

      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Download all tasks for the ingredient",
        buttonicon: "ace-icon fa fa-flag",
        onClickButton: function () {
          IP.onDownloadTasksForIngredient();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Download all tasks for the ingredient",
        buttonicon: "ace-icon fa fa-flag",
        onClickButton: function () {
          IP.onDownloadTasksForIngredient();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          IP.onToggleRemovedRecordsMode(event);
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          IP.onToggleRemovedRecordsMode(event);
        },
      });

      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Ingredient History Log.",
        buttonicon: "ace-icon fa fa-history",
        onClickButton: function () {
          IP.onIngredientHistoryLog();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Ingredient History Log.",
        buttonicon: "ace-icon fa fa-history",
        onClickButton: function () {
          IP.onIngredientHistoryLog();
        },
      });

      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Pre-Approved Ingredients.",
        buttonicon: "ace-icon fa fa-check-circle",
        onClickButton: function () {
          IP.onPreApprovedIngredients();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Pre-Approved Ingredients.",
        buttonicon: "ace-icon fa fa-check-circle",
        onClickButton: function () {
          IP.onPreApprovedIngredients();
        },
      });

      //-------------------------------
      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Bulk Upload Ingredients.",
        buttonicon: "ace-icon fa fa-upload",
        onClickButton: function () {
          IP.onBulkIngredients();
        },
      });
      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Bulk Upload Ingredients.",
        buttonicon: "ace-icon fa fa-upload",
        onClickButton: function () {
          IP.onBulkIngredients();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredPager", {
        caption: "",
        title: "Bulk Update Halal Certificates for Selected Ingredients",
        buttonicon: "ace-icon fa fa-edit",
        onClickButton: function () {
          IP.onBulkHalalCertUpdate();
        },
      });

      $("#ingredGrid").navButtonAdd("#ingredGrid_toppager", {
        caption: "",
        title: "Bulk Update Halal Certificates for Selected Ingredients",
        buttonicon: "ace-icon fa fa-edit",
        onClickButton: function () {
          IP.onBulkHalalCertUpdate();
        },
      });

      resolve("grid inited");
    }).then(function () {
      document
        .querySelector("body")
        .addEventListener("dragover", handleDragOverDocument);
      document
        .querySelector("body")
        .addEventListener("dragleave", handleDragLeaveDocument);
      document
        .querySelector("body")
        .addEventListener("drop", handleDropDocument);
    });
  },

  onBulkHalalCertUpdate: function () {
    // Get selected ingredients using multiselect checkboxes
    var selectedRows = $("#ingredGrid").jqGrid("getGridParam", "selarrrow");

    if (!selectedRows || selectedRows.length === 0) {
      alert(
        "Please select one or more ingredients using the checkboxes to update their halal certificates."
      );
      return;
    }

    // Show count of selected ingredients
    $("#selected-count").text(selectedRows.length);

    // Reset and show modal
    IP.resetBulkCertModal();
    $("#bulkHalalCertUpdate").modal("show");

    // Initialize file upload
    IP.initBulkHalalCertUpload();
  },

  resetBulkCertModal: function () {
    // Clear all form fields
    $("#bulkProducerName").val("");
    $("#bulkSupplierName").val("");
    $("#bulkCertificationBody").val("");
    $("#bulkExpiryDate").val("");
    $("#bulkRmPosition").val("");
    $("#halalCertificateFile").val("");

    // Reset file upload area
    $("#bulk-halal-cert-upload-box .uploaded-files").empty().hide();
    $("#bulk-halal-cert-upload-box .progress").hide();

    // Hide progress and results
    $("#bulk-cert-progress").hide();
    $("#bulk-cert-results").hide();
    $("#error-details").hide();

    // Enable the process button
    $("#processBulkCertBtn").prop("disabled", false).show();
  },

  initBulkHalalCertUpload: function () {
    // Initialize file upload similar to existing bulk upload functionality
    // This follows the same pattern as the existing bulk ingredient upload

    var uploadUrl = "fileupload/ProcessFiles.php";
    var clientId = $("#ingred-clientid").val();

    $("#bulk-halal-cert-fileupload").fileupload({
      url: uploadUrl,
      dataType: "json",
      formData: {
        infoType: "ingredient",
        client: clientId,
        ingredient: "bulk_cert_update",
      },
      done: function (e, data) {
        if (data.result && data.result.files && data.result.files.length > 0) {
          var file = data.result.files[0];
          $("#halalCertificateFile").val(JSON.stringify(file));

          // Show uploaded file
          var fileHtml =
            '<li class="list-group-item">' +
            '<i class="fa fa-file-pdf-o"></i> ' +
            file.name +
            ' <span class="badge">' +
            IP.formatFileSize(file.size) +
            "</span>" +
            "</li>";
          $("#bulk-halal-cert-upload-box .uploaded-files")
            .html(fileHtml)
            .show();
        }
      },
      progressall: function (e, data) {
        var progress = parseInt((data.loaded / data.total) * 100, 10);
        $("#bulk-halal-cert-upload-box .progress").show();
        $("#bulk-halal-cert-upload-box .progress-bar").css(
          "width",
          progress + "%"
        );
      },
      fail: function (e, data) {
        alert("File upload failed. Please try again.");
      },
    });
  },

  // Process bulk certificate update
  processBulkHalalCertUpdate: function () {
    // Validate required fields
    if (!$("#bulkProducerName").val().trim()) {
      alert("Producer name is required.");
      return;
    }
    if (!$("#bulkCertificationBody").val().trim()) {
      alert("Certification body name is required.");
      return;
    }
    if (!$("#bulkExpiryDate").val().trim()) {
      alert("Expiry date is required.");
      return;
    }
    if (!$("#halalCertificateFile").val()) {
      alert("Please upload a certificate file.");
      return;
    }

    // Get selected ingredients
    var selectedRows = $("#ingredGrid").jqGrid("getGridParam", "selarrrow");
    if (!selectedRows || selectedRows.length === 0) {
      alert("No ingredients selected.");
      return;
    }

    // Prepare data
    var updateData = {
      ingredientIds: selectedRows,
      certificateFile: $("#halalCertificateFile").val(),
      producerName: $("#bulkProducerName").val().trim(),
      supplierName:
        $("#bulkSupplierName").val().trim() ||
        $("#bulkProducerName").val().trim(),
      certificationBodyName: $("#bulkCertificationBody").val().trim(),
      expiryDate: $("#bulkExpiryDate").val().trim(),
      clientId: $("#ingred-clientid").val(),
    };

    // Show progress
    $("#processBulkCertBtn").prop("disabled", true);
    $("#bulk-cert-progress").show();

    // Process the update
    IP.sendBulkCertUpdateRequest(updateData);
  },

  sendBulkCertUpdateRequest: function (data) {
    $.ajax({
      url: "ajax/bulkHalalCertUpdate.php",
      type: "POST",
      data: data,
      dataType: "json",
      success: function (response) {
        IP.handleBulkCertUpdateResponse(response);
      },
      error: function (xhr, status, error) {
        alert("An error occurred: " + error);
        $("#processBulkCertBtn").prop("disabled", false);
        $("#bulk-cert-progress").hide();
      },
    });
  },

  handleBulkCertUpdateResponse: function (response) {
    $("#bulk-cert-progress").hide();

    if (response.status === 0) {
      alert("Error: " + response.statusDescription);
      $("#processBulkCertBtn").prop("disabled", false);
      return;
    }

    // Show results
    $("#result-total").text(response.data.total || 0);
    $("#result-success").text(response.data.success || 0);
    $("#result-failed").text(response.data.failed || 0);

    // Show error details if any
    if (response.data.failed > 0 && response.data.failed_rows) {
      $("#error-list").empty();
      response.data.failed_rows.forEach(function (row) {
        $("#error-list").append(
          "<tr><td>" + row.name + "</td><td>" + row.error + "</td></tr>"
        );
      });
      $("#error-details").show();
    }

    $("#bulk-cert-results").show();
    $("#processBulkCertBtn").hide();

    // Refresh the grid to show updated data
    $("#ingredGrid").jqGrid().trigger("reloadGrid");

    // Show notification
    if (response.data.success > 0) {
      Utils.notify(
        "success",
        response.data.success + " ingredients updated successfully!"
      );
    }
  },

  formatFileSize: function (bytes) {
    if (bytes === 0) return "0 Bytes";
    var k = 1024;
    var sizes = ["Bytes", "KB", "MB", "GB"];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  },

  onRestoreIngred: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "restoreIngred",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onToggleRemovedRecordsMode: function (e) {
    if (IP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      IP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      IP.gridMode = 1;
    }
    $("#ingred-clientid").trigger("change");
  },

  filterGrid: function () {
    new Promise(function (resolve) {
      $("#ingredGrid").jqGrid("setGridParam", { search: true });
      $("#ingredGrid").jqGrid("setGridParam", {
        postData: { rmid: $("input#filter-rmid").val() },
      });
      resolve("params done");
    }).then(function (res) {
      console.log("refresh grid filtersed");
      jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
      dynamic = true;
    });
  },

  initTasksGrid: function () {
    var h = 300;
    $("#tasksGrid").jqGrid({
      url: "ajax/getIngredTasks.php",
      datatype: "json",
      mtype: "POST",
      width: "100%",
      postData: {
        idingredient: function () {
          return $("#ingredGrid").getGridParam("selarrrow");
          /*
          return $("#ingredGrid").jqGrid(
            "getCell",
            $("#ingredGrid").jqGrid("getGridParam", "selrow"),
            "id"
          );
          */
        },
        s: function () {
          return jQuery(".tasks-container #s").val();
        },
      },
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "Status",
          name: "flag",
          width: 40,
          align: "center",
          formatter: formatTaskStatus,
        },
        {
          label: "Deviation",
          name: "deviation",
          width: 100,
          index: "deviation",
          align: "left",
          sortable: true,
        },
        {
          label: "Measure",
          name: "measure",
          width: 100,
          index: "measure",
          sortable: true,
        },
        {
          label: " ",
          name: "delete",
          width: 40,
          align: "center",
          formatter: formatTaskDelete,
        },
      ],
      rowNum: 0,
      sortname: "flag",
      viewrecords: true,
      sortorder: "desc",
      shrinkToFit: true,
      gridComplete: function () {},
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.assign-task")) {
          e.preventDefault();
          IP.onAssignTask(rowid);
          return true;
        }
        if ($(e.target).is("button.delete-task")) {
          e.preventDefault();
          IP.onDeleteTask(rowid);
          return true;
        }
        if ($(e.target).is("button.edit-task")) {
          e.preventDefault();
          var d = {};
          d.id = $("#tasksGrid").jqGrid("getCell", rowid, "id");
          $.ajax({
            url: "ajax/ajaxHandler.php",
            type: "POST",
            data: {
              rtype: "getTaskDetails",
              uid: 0,
              data: d,
            },
            beforeSend: function () {
              //$.blockUI();
            },
            success: function (data) {
              var response = JSON.parse(data);
              if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
              }
              var task = response.data.task;
              $("#task-id").val(task.id);
              $("#task-deviation").val(task.deviation);
              $("#task-measure").val(task.measure);
              $("#task-add").hide();
              $("#task-edit").show();
              $("#task-cancel").show();
              //$.unblockUI();
            },
          });

          return true;
        }
        return true; // select the row
      },
    });
  },

  initActiveTasksGrid: function () {
    var h = 150;
    $("#activeTasksGrid").jqGrid({
      url: "ajax/getIngredActiveTasks.php",
      datatype: "json",
      mtype: "POST",
      width: "100%",
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "Status",
          name: "status",
          width: 40,
          align: "center",
          formatter: formatActiveTaskStatus,
        },
        {
          label: "Deviation",
          name: "deviation",
          width: 100,
          index: "deviation",
          align: "left",
          sortable: false,
        },
        {
          label: "Measure",
          name: "measure",
          width: 100,
          index: "measure",
          sortable: false,
        },
      ],
      rowNum: 0,
      sortname: "status",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: true,
      gridComplete: function () {},
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("button.complete-task")) {
          e.preventDefault();
          IP.onCompleteActiveTask(rowid);
          return true;
        } else if ($(e.target).is("button.undone-task")) {
          e.preventDefault();
          IP.onUndoneActiveTask(rowid);
          return true;
        }
        return true; // select the row
      },
    });
  },

  onSearchTasks: function () {
    //if ($("#s").val().length >= 3) {
    $("#tasksGrid").jqGrid().trigger("reloadGrid");
    //}
  },

  onCompleteActiveTask: function (rowid) {
    var d = {};
    d.id = $("#activeTasksGrid").jqGrid("getCell", rowid, "id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "completeTask",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#activeTasksGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onUndoneActiveTask: function (rowid) {
    var d = {};
    d.id = $("#activeTasksGrid").jqGrid("getCell", rowid, "id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "undoneTask",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#activeTasksGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onConfirmTaskActiveTask: function (rowid) {
    var d = {};
    d.id = $("#activeTasksGrid").jqGrid("getCell", rowid, "id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "confirmTask",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#activeTasksGrid").jqGrid().trigger("reloadGrid");
    });
  },

  loadIngredientsForIngredientData: function (callback) {
    var prod = {};
    prod.idclient = $("#ingred-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      data: prod,
      rtype: "ingredientsForIngredient",
    }).done(callback);
  },

  populateIngredientsForIngredient: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    $("#ingred-form #ingredients").empty();
    $("#ingred-form #ingredients").select2("destroy").select2();
    $("#ingred-form #ingredients")
      .select2({
        dropdownParent: $("#ingredModal .modal-content"),
        scrollAfterSelect: false,

        closeOnSelect: false,
        data: response.data.ingredients,
      })
      .trigger("change");
  },

  onIngredientHistoryLog: function () {
    $("#logModal").modal("show");
  },

  onPreApprovedIngredients: function () {
    if ($("#ingred-clientid").val() == "") {
      alert("Please select client");
      return;
    }
    $("#paModal").modal("show");
  },

  onBulkIngredients: function () {
    if (
      $("#ingred-clientid").val() == "" ||
      $("#ingred-clientid").val() == "-2"
    ) {
      alert("Please select a client");
      return;
    }

    let currentStep = 1;
    const $modal = $("#bulkingredient");

    resetModal($modal);
    $modal.modal("show");

    function resetModal($modal) {
      currentStep = 1;
      showStep(1);

      $modal
        .off("hidden.bs.modal")
        .on("hidden.bs.modal", () =>
          $("#ingredGrid").jqGrid().trigger("reloadGrid")
        );

      const $documentUploadBox = $("#group-document-upload-box");
      const $spreadsheetUploadBox = $("#ingredient-spreadsheet-upload-box");

      // reset the event listeners
      $("#bulkingredient .nextBtn")
        .off("click")
        .on("click", function () {
          validateStep(currentStep) && goToNextStep();
        });
      $("#bulkingredient .prevBtn").off("click").on("click", goToPreviousStep);
      $("#documentFile")
        .off("change")
        .on("change", () => {
          updateDropzone($documentUploadBox);
        });
      $("#spreadsheetFile")
        .off("change")
        .on("change", () => {
          updateDropzone($spreadsheetUploadBox);
        });
      $('#bulkingredient input[name="documentType"]')
        .off("change")
        .on("change", function () {
          var documentTypeValue = $(this).val();
          $(
            "#document-none, #document-certificate, #document-statement"
          ).hide();
          if (documentTypeValue === "certificate") {
            $("#document-certificate").show();
            $(".instruction-box").addClass("document-on");
            $(".instruction-box").removeClass("document-off");
          } else if (documentTypeValue === "statement") {
            $("#document-statement").show();
            $(".instruction-box").addClass("document-on");
            $(".instruction-box").removeClass("document-off");
          } else {
            $("#document-none").show();
            $(".instruction-box").addClass("document-off");
            $(".instruction-box").removeClass("document-on");
          }

          $("#group-document-upload-box").toggle($(this).val() !== "none");
          $("#bulkingredient .certificate-fields").toggle(
            $(this).val() === "certificate"
          );
          $("#bulkingredient .statement-fields").toggle(
            $(this).val() === "statement"
          );
        });
      $("#bulkingredient .resetBtn")
        .off("click")
        .on("click", () => resetModal($modal));
      $("#bulkingredient .finishBtn")
        .off("click")
        .on("click", () => $modal.modal("hide"));

      // select the certificate file type
      $modal
        .find('input[name="documentType"][value="certificate"]')
        .prop("checked", true)
        .trigger("change");

      // clear the document information
      $("#documentFile").val("").trigger("change");
      $("#spreadsheetFile").val("").trigger("change");

      // clear the input values
      [
        "producerName",
        "certificationBodyName",
        "expiryDate",
        "supplierName",
        "statementSupplierName",
      ].forEach((f) => {
        $modal.find(`input[name="${f}"]`).val("").trigger("change");
      });

      // clear the result displays
      $("#bulk-ingredient-upload-message").text("").hide();
      $("#bulk-ingredient-upload-result .total").text("");
      $("#bulk-ingredient-upload-result .failed").text("");
      $("#bulk-ingredient-upload-result .success").text("");
      $("#bulkingredient .import-error-list table tbody").empty();
      $("#bulkingredient .import-error-list").hide();
      $("#bulk-ingredient-upload-result").hide();
      $("#bulk-ingredient-upload-progress").hide();
    }

    function showStep(step) {
      $("#bulkingredient .step").hide();
      $("#bulkingredient #step" + step).show();

      if (step === 1) {
        $("#bulkingredient .prevBtn").hide();
      } else {
        $("#bulkingredient .prevBtn").show();
      }
    }

    function validateStep(step) {
      const $modal = $("#bulkingredient");
      let errorMessage = null;
      let docType;

      const docTypeNames = {
        certificate: "Halal certificate",
        statement: "Supplier statement file",
      };

      if (step === 1) {
        // Check if the PDF file is required
        docType = $modal.find('input[name="documentType"]:checked').val();
        if (docType !== "none" && !$modal.find("#documentFile").val()) {
          errorMessage = `Please upload the ${docTypeNames[docType]}.`;
        }

        // Check the required fields for a certificate
        if (!errorMessage && docType === "certificate") {
          ["producerName", "certificationBodyName", "expiryDate"].forEach(
            (f) => {
              if (!$modal.find(`#step1 input[name="${f}"]`).val().trim()) {
                errorMessage = "Please fill in all required fields.";
              }
            }
          );
        }

        // Check the required fields for a statement
        if (!errorMessage && docType === "statement") {
          ["statementSupplierName"].forEach((f) => {
            if (!$modal.find(`#step1 input[name="${f}"]`).val().trim()) {
              errorMessage = "Please fill in all required fields.";
            }
          });
        }
      }

      if (step === 2 && !$modal.find("#spreadsheetFile").val()) {
        errorMessage =
          "Please upload an Excel or CSV file with the list of ingredients.";
      }

      if (errorMessage) {
        alert(errorMessage);
      }

      return !errorMessage;
    }

    function processBulkUpload() {
      const FD = new FormData();
      FD.append("client_id", $("#ingred-clientid").val());
      FD.append(
        "document_type",
        $('#bulkingredient input[name="documentType"]:checked').val()
      );
      FD.append("document_file", $("#documentFile").val());
      FD.append("spreadsheet_file", $("#spreadsheetFile").val());
      [
        "producerName",
        "certificationBodyName",
        "expiryDate",
        "supplierName",
        "statementSupplierName",
      ].forEach((f) => {
        FD.append(f, $modal.find(`input[name="${f}"]`).val().trim());
      });

      $("#bulk-ingredient-upload-progress").show();

      fetch("/ajax/bulkIngredients.php", {
        method: "post",
        credentials: "include",
        body: FD,
      })
        .then((r) => r.json())
        .then(handleBulkUploadResult)
        .catch((e) =>
          handleBulkUploadResult({
            status: "error",
            message:
              "Something went wrong during the import. Please try again later.",
          })
        )
        .finally(() => $("#bulk-ingredient-upload-progress").hide());
    }

    function handleBulkUploadResult(result) {
      if (result.status === "error") {
        $("#bulk-ingredient-upload-message")
          .addClass("alert-danger")
          .text(result.message)
          .show();
      } else {
        $("#bulk-ingredient-upload-message")
          .removeClass("alert-danger")
          .text("")
          .hide();

        $("#bulk-ingredient-upload-result .total").text(result.total);
        $("#bulk-ingredient-upload-result .failed").text(result.failed);
        $("#bulk-ingredient-upload-result .success").text(result.success);

        if (result.failed_rows.length) {
          let $tr;

          result.failed_rows.forEach((r) => {
            $tr = $("<tr></tr>");
            $tr.append($(`<td>${r.number}</td>`));
            $tr.append($(`<td>${r.name}</td>`));
            $tr.append($(`<td>${r.error}</td>`));
            $("#bulkingredient .import-error-list table tbody").append($tr);
          });

          $("#bulkingredient .import-error-list").show();
        } else {
          $("#bulkingredient .import-error-list").hide();
        }

        $("#bulk-ingredient-upload-result").show();
      }
    }

    function goToNextStep() {
      switch (currentStep) {
        case 1:
          showStep(++currentStep);
          break;
        case 2:
          showStep(++currentStep);
          processBulkUpload();
          break;
      }
    }

    function goToPreviousStep() {
      if (currentStep > 1) {
        showStep(--currentStep);
      }
    }

    function validatePdf(file) {
      if (!/\.(pdf)$/i.test(file.name)) {
        alert("Invalid file format. Only PDF files are accepted.");
        return false;
      }

      return true;
    }

    function validateSpreadsheet(file) {
      if (!/\.(csv|xlsx)$/i.test(file.name)) {
        alert("Invalid file format. Only Excel or CSV files are accepted.");
        return false;
      }

      return true;
    }

    // methods related to file upload handling when file is dropped into the dropzone
    // or selected from user's device

    function handleFileAddEvent(e, data, validationFunction) {
      const folderType = {
        certificate: "cert",
        statement: "state",
        none: "",
      }[$('#bulkingredient input[name="documentType"]').val()];

      data.formData = {
        folderType,
        infoType: "ingredient",
        client: $("#ingred-clientid").data("clientname"),
        ingredient: $("#ingred-form #rmid").val(),
        idclient: $("#ingred-clientid").val(),
        idingredient: $("#ingred-form #id").val(),
      };

      if (!validationFunction.call(null, data.files[0])) {
        return false;
      }

      // upload permitted
      $(e.target).parent().hide();
      $(e.target).parent().siblings(".progress").show();
      data.submit();
      return true;
    }

    function createFileNameElement(filename, $uploadBox) {
      const $uploadedFilesInput = $uploadBox.find(
        ".uploaded-file-hidden-input"
      );

      const $li = $('<li class="uploaded-file-name"></li>');
      const $filename = $("<span>", { text: filename });
      const $deleteButton =
        $(`<span class="btn btn-danger delete uploaded-file-name-close remove-doc" title="Remove file">
            <i class="glyphicon glyphicon-remove"></i>&nbsp;Delete
        </span>`).bind("click", function (e) {
          $uploadedFilesInput.val("").trigger("change");
        });

      $li.append($filename);
      $li.append($deleteButton);

      return $li;
    }

    function updateDropzone($uploadBox) {
      const $uploadedFilesContainer = $uploadBox.find(".uploaded-files");
      const $uploadedFilesInput = $uploadBox.find(
        ".uploaded-file-hidden-input"
      );
      const $dropzoneElement = $uploadBox.find(".bulkingred-dropzone");

      if (!$uploadedFilesInput.val().trim().length) {
        $uploadedFilesContainer.empty().hide();
        $dropzoneElement.show();
      } else {
        const fileinfo = JSON.parse($uploadedFilesInput.val());
        const sanitizedFileName =
          fileinfo.name.length > 35
            ? fileinfo.name.substr(0, 30) + "..."
            : fileinfo.name;

        // show the list of uploaded files and hide the button that uploads new file
        $uploadedFilesContainer
          .append(createFileNameElement(sanitizedFileName, $uploadBox))
          .show();
        $dropzoneElement.hide();
      }
    }

    function handleFileUploadSuccess(e, data) {
      // hide loader and add new li with new file info
      $(e.target).parent().siblings(".progress").hide();

      const file = data.result.files[0];

      const fileinfo = {
        name: file.name,
        glink: file.googleDriveUrl,
        hostpath: file.url,
        hostUrl: file.hostUrl,
      };

      const $uploadBox = $(e.target).closest(".upload-box");
      const $uploadedFilesInput = $uploadBox.find(
        ".uploaded-file-hidden-input"
      );

      $uploadedFilesInput.val(JSON.stringify(fileinfo)).trigger("change");
    }

    function initFileUploadArea($uploadBox, validationFunction) {
      const $fileUploadElement = $uploadBox.find(".bulkingred-fileupload");
      const $dropzoneElement = $uploadBox.find(".bulkingred-dropzone");

      $fileUploadElement
        .fileupload({
          url: "fileupload/ProcessFiles.php",
          dataType: "json",
          dropZone: $dropzoneElement,
          add: function (e, data) {
            return handleFileAddEvent(e, data, validationFunction);
          },
          done: handleFileUploadSuccess,
          start: DefaultFileUploadStartHandler,
          fail: DefaultFileUploadFailHandler,
          progress: DefaultFileUploadProgressHandler,
        })
        .prop("disabled", !$.support.fileInput)
        .parent()
        .addClass($.support.fileInput ? undefined : "disabled");
    }

    // initialize file upload for certificate/statement
    initFileUploadArea($("#group-document-upload-box"), validatePdf);

    // initialize file upload for excel/csv
    initFileUploadArea(
      $("#ingredient-spreadsheet-upload-box"),
      validateSpreadsheet
    );
  },

  onExportCertificates: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    doc.displaymode = IP.gridMode;
    doc.ids = $("#ingredGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendIngredientsCertificatesRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onExportSupplierQuestions: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    doc.displaymode = IP.gridMode;
    doc.ids = $("#ingredGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendIngredientsSupplierQuestionsRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onExportGridToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    doc.displaymode = IP.gridMode;
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendIngredientsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onExportAllGridToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendAllIngredientsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onSetTasksForIngredient: function (ingredId) {
    if (!ingredId) {
      /*ingredId = jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      );*/
      ingredId = jQuery("#ingredGrid")
        .jqGrid("getGridParam", "selarrrow")
        .join(",");
    }

    if (!ingredId) {
      alert("Please select ingredient");
      return;
    }

    $("#tasksModal .id").val(ingredId);
    $("#tasksModal #tasksModal-label").text(
      "Tasks for the ingredient " + ingredId
    );
    $("#tasksGrid").jqGrid("setGridParam", {
      url: "ajax/getIngredTasks.php?idingredient=" + ingredId,
    });
    $("#tasksGrid").trigger("reloadGrid");
    $("#tasksModal").modal("show");
  },

  // Change required
  onDownloadTasksForIngredient: function () {
    if (
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select ingredient");
      return;
    }
    var doc = {};
    doc.ids = $("#ingredGrid").getGridParam("selarrrow");
    /*
    doc.idingredient = jQuery("#ingredGrid").jqGrid(
      "getCell",
      jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
      "id"
    );
  */
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendAllTasksExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      $("#infoModal").modal("hide");
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onAssignTask: function (rowid) {
    var d = {};
    d.idd = $("#tasksGrid").jqGrid("getCell", rowid, "id");
    //d.idi = $("#tasksModal .id").val();
    // d.idi = $("#ingredGrid").getGridParam("selarrrow");
    d.idi = $("input#task-id").val();

    d.status = $($("#tasksGrid").jqGrid("getCell", rowid, "flag")).data("data")
      ? 0
      : 1;
    $.post("ajax/ajaxHandler.php", {
      rtype: "assignTaskForIngredient",
      uid: 0,
      data: d,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#tasksGrid").jqGrid().trigger("reloadGrid");
      // $("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onDeleteTask: function (rowid) {
    if (confirm("Are you sure you want to delete?")) {
      var d = {};
      d.id = $("#tasksGrid").jqGrid("getCell", rowid, "id");
      d.idi = $("input#task-id").val();
      $.post("ajax/ajaxHandler.php", {
        rtype: "deleteTask",
        uid: 0,
        data: d,
      }).done(function (data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          return;
        }
        $("#tasksGrid").jqGrid().trigger("reloadGrid");
      });
    }
  },

  onUpdateTask: function () {
    var d = {};
    d.id = $("#task-id").val();
    d.deviation = $("#task-deviation").val();
    d.measure = $("#task-measure").val();
    IP.showLoaderById("#task-loader");
    IP.hideLoaderById("#task-add");
    $.post("ajax/ajaxHandler.php", {
      rtype: "updateTask",
      uid: 0,
      data: d,
    }).done(function (data) {
      try {
        var response = JSON.parse(data);
        if (response.status == 0) {
          IP.onAddTaskFailed(response.statusDescription);
        } else {
          $("#task-id").val("");
          $("#task-deviation").val("");
          $("#task-measure").val("");
          $("#task-add").show();
          $("#task-edit").hide();
          $("#task-cancel").hide();
          $("#tasksModal .success-string").text(
            "The task successfully updated"
          );
          setTimeout(function () {
            $("#tasksModal .success-string").text("");
          }, 2000);

          $("#tasksGrid").jqGrid().trigger("reloadGrid");
        }
      } catch (e) {
        IP.onAddTaskFailed("Error: ", e);
      } finally {
        IP.showLoaderById("#task-add");
        IP.hideLoaderById("#task-loader");
      }
    });
  },

  onCancelTask: function (rowid) {
    $("#task-id").val("");
    $("#task-deviation").val("");
    $("#task-measure").val("");
    $("#task-add").show();
    $("#task-edit").hide();
    $("#task-cancel").hide();
    return false;
  },

  clearForm: function () {
    IP.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#ulspec").empty();
    $("#ulquest").empty();
    $("#ulstate").empty();
    $("#ulcert").empty();
    $("#uladd").empty();
    $("#ingred-form input").val("");
    $("#ingred-form .ace-switch").prop("checked", false);
    $("#ingred-form select").val(null).trigger("change");
    $("#ingredModal .form-warning").hide();
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (!response.data.ingredient) {
      $("#ingred-form #rmid").val("RM_" + response.data.id);
      $("#ingred-form #rmid").attr("data-id", response.data.id);
      $("#ingred-form #rmid").attr("data-new", 1);
    }
    $("#ingredModal").prop("submit", 0);
    IP.filesUploaded = [];
    $("#ingredModal").modal("show");
  },

  getNextIngredId: function (callback) {
    var prod = {};
    prod.idclient = $("#ingred-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      data: prod,
      rtype: "nextIngredId",
    }).done(callback);
  },

  newIngredient: function () {
    if ($("#ingred-clientid").val() == "") {
      alert("Please select client");
      return;
    }
    IP.clearForm();
    $("#activeTasksGridBox").hide();

    $("#ingredModal-label").text("New Ingredient");
    IP.getNextIngredId(IP.fillForm);
  },

  editIngredient: function () {
    var id_paingred = jQuery("#ingredGrid").jqGrid(
      "getCell",
      jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
      "id_paingred"
    );
    if (id_paingred && id_paingred > 0) {
      alert("Pre-approved ingredients are not editable.");
      return false;
    }
    if (
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select ingredient");
      return;
    }
    IP.clearForm();

    $("#ingredModal-label").text("Edit Ingredient");
    $("#ingred-form #rmid").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "rmid"
      )
    );
    $("#ingred-form #rmid").attr(
      "data-id",
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      )
    );
    $("#ingred-form #rmid").attr("data-new", 0);
    $("#ingred-form #name").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Name"
      )
    );
    $("#ingred-form #code").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "rmcode"
      )
    );
    $("#ingred-form #supplier").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Supplier"
      )
    );
    $("#ingred-form #producer").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "producer"
      )
    );
    $("#ingred-form #material").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "material"
      )
    );
    $("#ingred-form #cb").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "CB"
      )
    );
    $("#ingred-form #date").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Date"
      )
    );
    $("#ingred-form #rmposition").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "rmposition"
      )
    );
    $("#ingred-form #note").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Note"
      )
    );
    $("#ingred-form #ingredients")
      .val(
        jQuery("#ingredGrid")
          .jqGrid(
            "getCell",
            jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
            "ingred"
          )
          .replace(", ", ",")
          .split(",")
      )
      .trigger("change");

    $("#ingred-form #certified").prop(
      "checked",
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Certified"
      ) == 1
    );
    $("#ingred-form #certified").trigger("change");

    $("#ingred-form #conformed").prop(
      "checked",
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Conformed"
      ) == 1
    );
    $("#ingred-form #conformed").trigger("change");

    $("#ingred-form #subingredient").prop(
      "checked",
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "sub"
      ) == 1
    );
    $("#ingred-form #subingredient").trigger("change");
    Utils.filesToList("ulspec", "ingredGrid", "Specification");
    Utils.filesToList("ulquest", "ingredGrid", "Questionnaire");
    Utils.filesToList("ulstate", "ingredGrid", "Statement");
    Utils.filesToList("ulcert", "ingredGrid", "Certificate");
    Utils.filesToList("uladd", "ingredGrid", "Addocs");
    // check number of active tasks, to show grid or not ONLY FOR CLIENT!!!
    if (!IP.isAdminSession()) {
      if (
        $("#ingredGrid").jqGrid(
          "getCell",
          $("#ingredGrid").jqGrid("getGridParam", "selrow"),
          "tasksnumber"
        ) > 0
      ) {
        $("#activeTasksGridBox").show();
        $("#activeTasksGrid").jqGrid("setGridParam", {
          url:
            "ajax/getIngredActiveTasks.php?idingredient=" +
            $("#ingredGrid").jqGrid(
              "getCell",
              $("#ingredGrid").jqGrid("getGridParam", "selrow"),
              "id"
            ),
        });
        $("#activeTasksGrid").jqGrid().trigger("reloadGrid");
      } else $("#activeTasksGridBox").hide();
    } else $("#activeTasksGridBox").hide();

    $("#ingredModal").prop("submit", 1); // edit
    IP.filesUploaded = [];
    $("#ingredModal").modal("show");
  },

  deleteIngredient: function () {
    if (
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select ingredient");
      return;
    }
    if (confirm("Delete the ingredient?")) {
      IP.sendDeleteIngredientRequest();
    }
  },

  newCopiedIngredient: function () {
    if (
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select ingredient");
      return;
    }
    if (
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Certified"
      ) == 0
    ) {
      alert("The selected ingredient does not have a certificate");
      return;
    }
    IP.clearForm();
    $("#ingred-form #certified").prop(
      "checked",
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Certified"
      ) == 1
    );
    $("#ingred-form #certified").trigger("change");
    $("#ingred-form #cb").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "CB"
      )
    );
    $("#ingred-form #date").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "Date"
      )
    );
    $("#ingred-form #rmposition").val(
      jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "rmposition"
      )
    );
    $("#activeTasksGridBox").hide();
    Utils.filesToList("ulcert", "ingredGrid", "Certificate");

    $("#ingredModal-label").text("New Ingredient");
    IP.getNextIngredId(IP.fillForm);
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    doc.id = $("#ingred-form #rmid").attr("data-id");
    doc.name = $("#ingred-form #name").val().trim();
    doc.code = $("#ingred-form #code").val().trim();
    doc.supplier = $("#ingred-form #supplier").val().trim();
    doc.producer = $("#ingred-form #producer").val().trim();
    doc.material = $("#ingred-form #material").val().trim();
    doc.sub = $("#ingred-form #subingredient").prop("checked") ? 1 : 0;
    if ($("#certified").prop("checked")) {
      doc.halalcert = 1;
      doc.cb = $("#ingred-form #cb").val().trim();
      doc.halalexp = $("#ingred-form #date").val().trim();
      doc.rmposition = $("#ingred-form #rmposition").val().trim();
    } else {
      doc.halalcert = 0;
      $("#ulcert").find("li").addClass("deleted");
      //$("#ulcert").empty();
    }

    doc.note = $("#ingred-form #note").val().trim();
    doc.conf = $("#ingred-form #conformed").prop("checked") ? 1 : 0;
    doc.ingred = $("#ingred-form #ingredients").val();
    doc.ingredtext = $("#ingred-form #ingredients option:selected")
      .map(function () {
        return $(this).text();
      })
      .get()
      .join(",");
    doc.spec = Utils.filesToJSON("ulspec");
    doc.addoc = Utils.filesToJSON("uladd");
    doc.quest = Utils.filesToJSON("ulquest");
    doc.state = Utils.filesToJSON("ulstate");
    doc.cert = Utils.filesToJSON("ulcert");
    return doc;
  },

  validateForm: function () {
    $("#ingredModal .form-warning").hide();
    setTimeout(function () {
      $("#ingredModal .form-warning").hide();
    }, 4000);

    if ($("#ingred-form #code").val().trim() == "") {
      Utils.notifyInput($("#ingred-form #code"), "No Code specified");
      $("#ingredModal .form-warning").show();
      return;
    }
    if ($("#ingred-form #name").val().trim() == "") {
      Utils.notifyInput($("#ingred-form #name"), "No Name specified");
      $("#ingredModal .form-warning").show();
      return;
    }
    if (IP.checkBannedWords()) {
      Utils.notifyInput(
        $("#ingred-form #name"),
        "Name contains forbidden words. Please review and correct."
      );
      $("#ingredModal .form-warning").show();
      return;
    }
    if ($("#ingred-form #supplier").val().trim() == "") {
      Utils.notifyInput($("#ingred-form #supplier"), "No Supplier specified");
      $("#ingredModal .form-warning").show();
      return;
    }
    if ($("#ingred-form #producer").val().trim() == "") {
      Utils.notifyInput($("#ingred-form #producer"), "No Producer specified");
      $("#ingredModal .form-warning").show();
      return;
    }
    if ($("#ingred-form #material").val().trim() == "") {
      Utils.notifyInput(
        $("#ingred-form #material"),
        "Source of Raw Material is required"
      );
      $("#ingredModal .form-warning").show();
      return;
    }
    if ($("#certified").prop("checked")) {
      if ($("#ingred-form #ulcert li").length == 0) {
        Utils.notifyInput($("#ingred-form #ulcert"), "No Certificate uploaded");
        $("#ingredModal .form-warning").show();
        return;
      }
      if ($("#ingred-form #cb").val().trim() == "") {
        Utils.notifyInput($("#ingred-form #cb"), "No Certificate specified");
        $("#ingredModal .form-warning").show();
        return;
      }
      if ($("#ingred-form #date").val().trim() == "") {
        Utils.notifyInput($("#ingred-form #date"), "No Date specified");
        $("#ingredModal .form-warning").show();
        return;
      }
      if ($("#ingred-form #rmposition").val().trim() == "") {
        Utils.notifyInput(
          $("#ingred-form #rmposition"),
          "No RM position specified"
        );
        $("#ingredModal .form-warning").show();
        return;
      }
    }
    /*
    if ($("#ingred-form #ulspec li").length == 0) {
      Utils.notifyInput($("#ingred-form #ulspec"), "No Specification uploaded");
      $("#ingredModal .form-warning").show();
      return;
    }
      */
    return true;
  },

  checkBannedWords: function () {
    var ret = false;
    $.ajax({
      type: "GET",
      url: "ajax/CheckBannedWords.php",
      cache: false,
      async: false,
      data: { text: $("#ingred-form #name").val() },
      success: function (result) {
        if (result == "1") {
          ret = true;
        } else {
          ret = false;
        }
      },
      error: function (jqXHR, status, message) {},
    });
    return ret;
  },

  onChangeConformity: function (id) {
    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: {
        rtype: "changeConformity",
        uid: 0,
        data: { id: id },
      },
      beforeSend: function () {
        $.blockUI();
      },
      success: function (data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          $.blockUI({
            message: '<span style="font-size:16px">Please wait...</span>',
          });

          return;
        }
        Utils.notify("success", "Changes were submitted");
        $("#ingredGrid").jqGrid().trigger("reloadGrid");
        $.unblockUI();
        //$('#ingredGrid').setSelection(id, true);
      },
    });
    /*
    $.post("ajax/ajaxHandler.php", {
      rtype: "changeConformity",
      uid: 0,
      data: { id: id },
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#ingredGrid").jqGrid().trigger("reloadGrid");
      //$('#ingredGrid').setSelection(id, true);
    });
  */
  },

  sendModifyIngredientRequest: function (doc) {
    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: {
        rtype: "saveIngredient",
        uid: 0,
        data: doc,
      },
      dataType: "json",
      beforeSend: function () {
        // Show loading indicator or disable the submit button
        Utils.notify("info", "Saving ingredient...");
        //$("#saveButton").prop("disabled", true);
        $.blockUI();
      },
      success: function (response) {
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          return;
        }
        Utils.notify("success", "Changes were submitted");

        // Add actions about new ingredient and new docs
        var d = {};
        d.itemid = doc.id;
        d.idclient = doc.idclient;
        d.itemcode = $("#ingred-form #rmid").val();
        d.itemtype = "ingredients";
        d.itemname = doc.name;
        d.action = "New ingredient added";

        // Uncomment if needed
        // if ($("#ingredModal").prop("submit") == 0) Common.sendAddActionRequest(d);

        if (IP.filesUploaded.length > 0) {
          d.action = "New document";
          d.documents = JSON.stringify(IP.filesUploaded);
          Common.sendAddActionRequest(d);
        }

        $("#ingredModal").prop("submit", 1);
        $("#ingredModal").modal("hide");
      },
      error: function (xhr, status, error) {
        Utils.notify("error", "An error occurred: " + error);
      },
      complete: function () {
        // Hide loading indicator or re-enable the submit button
        //$("#saveButton").prop("disabled", false);
        $.unblockUI();
      },
    });

    /*
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveIngredient",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      // add actions about new ingredient and new docs
      var d = {};
      d.itemid = doc.id;
      d.idclient = doc.idclient;
      d.itemcode = $("#ingred-form #rmid").val();
      d.itemtype = "ingredients";
      d.itemname = doc.name;
      d.action = "New ingredient added";
      //if ($("#ingredModal").prop("submit") == 0) Common.sendAddActionRequest(d);

      if (IP.filesUploaded.length > 0) {
        d.action = "New document";
        d.documents = JSON.stringify(IP.filesUploaded);
        Common.sendAddActionRequest(d);
      }

      $("#ingredModal").prop("submit", 1);
      $("#ingredModal").modal("hide");
    });
    */
  },

  sendRemoveIngredientRequest: function () {
    var doc = { id: $("#ingred-form #rmid").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeIngredient",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New ingredient data was added");
    });
  },

  sendDeleteIngredientRequest: function () {
    /*
    var doc = {
      id: jQuery("#ingredGrid").jqGrid(
        "getCell",
        jQuery("#ingredGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    */
    var doc = {};
    doc.ids = $("#ingredGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "markDeletedIngredient",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Product was deleted");
    });
  },

  onSave: function () {
    IP.clearAlerts();
    if (!IP.validateForm()) {
      return;
    }
    var doc = IP.createDocFromInputData();
    IP.sendModifyIngredientRequest(doc);
  },

  // Add new task to the tasks list
  showLoaderById: function (id) {
    $(id).css("display", "inline-block");
  },

  hideLoaderById: function (id) {
    $(id).css("display", "none");
  },

  onAddTaskFailed: function (s) {
    $("#tasksModal .alert-string").text(s);
    setTimeout(function () {
      $("#tasksModal .alert-string").text("");
    }, 3000);
  },

  onAddTaskSuccess: function () {
    $("#task-deviation").val("");
    $("#task-measure").val("");
    $("#tasksModal .success-string").text("The new task successfully added");
    setTimeout(function () {
      $("#tasksModal .success-string").text("");
    }, 2000);
  },

  onAddTask: function () {
    if (!($("#task-deviation").val() && $("#task-measure").val())) {
      IP.onAddTaskFailed("Please specify the information");
      return;
    }
    IP.saveTask();
  },

  saveTask: function () {
    var d = {};
    d.deviation = $("#task-deviation").val();
    d.measure = $("#task-measure").val();
    d.idingredient = $("input#task-id").val();
    IP.showLoaderById("#task-loader");
    IP.hideLoaderById("#task-add");
    $.post("ajax/ajaxHandler.php", { rtype: "addTask", uid: 0, data: d }).done(
      function (data) {
        try {
          var response = JSON.parse(data);
          if (response.status == 0) {
            IP.onAddTaskFailed(response.statusDescription);
          } else {
            IP.onAddTaskSuccess();
            $("#tasksGrid").jqGrid().trigger("reloadGrid");
          }
        } catch (e) {
          IP.onAddTaskFailed("Error: ", e);
        } finally {
          IP.showLoaderById("#task-add");
          IP.hideLoaderById("#task-loader");
        }
      }
    );
  },
};

// QM PAGE
var QP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("qmItem");

    QP.gridMode = 0;

    $('[data-toggle="tooltip"]').tooltip();

    $("select").change(function () {
      IP.clearAlerts();
    });

    $(".datepicker").datepicker({
      viewMode: "years",
      minViewMode: "years",
      autoclose: true,
      format: "yyyy",
      orientation: "bottom",
    });

    $(".datepicker")
      .datepicker()
      .on("changeDate", function (e) {
        QP.clearAlerts();
      });

    $("#filter-prevyears").on("change", function (e) {
      jQuery("#qmGrid").jqGrid("setGridParam", {
        url:
          "ajax/getQm.php?idclient=" +
          $("#qm-clientid").val() +
          "&prevyears=" +
          ($("#filter-prevyears").prop("checked") ? 1 : 0),
      });
      jQuery("#qmGrid")
        .jqGrid()
        .trigger("reloadGrid", [{ page: 1 }]);
    });

    QP.initGrid();

    //Common.loadClientsData(Common.populateClients);

    QP.initFileUploader();

    $("#qm-clientid").on("change", function () {
      $("#qm-clientid").data(
        "clientname",
        $("#qm-clientid option:selected").data("clientname")
      );
      jQuery("#qmGrid").jqGrid("setGridParam", {
        url:
          "ajax/getQm.php?displaymode=" +
          QP.gridMode +
          "&prevyears=" +
          ($("#filter-prevyears").prop("checked") ? 1 : 0) +
          "&idclient=" +
          this.value,
      });
      jQuery("#qmGrid").jqGrid().trigger("reloadGrid");
    });

    $("#qmModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        QP.sendRemoveQMRequest();
      } else jQuery("#qmGrid").jqGrid().trigger("reloadGrid");
    });

    $(document).bind("drag-over", function (e) {
      var dropZones = $(".fileinput-button"),
        timeout = window.dropZoneTimeout;
      if (timeout) {
        clearTimeout(timeout);
      } else {
        dropZones.addClass("in");
      }
      var hoveredDropZone = $(e.target).closest(dropZones);
      dropZones.not(hoveredDropZone).removeClass("hover");
      hoveredDropZone.addClass("hover");
      window.dropZoneTimeout = setTimeout(function () {
        window.dropZoneTimeout = null;
        dropZones.removeClass("in hover");
      }, 100);
    });
  },

  initFileUploader: function () {
    $("#fileupload1")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone1"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload2")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone2"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload3")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone3"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload4")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone4"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload5")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone5"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload6")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone6"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload7")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone7"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload8")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone8"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload9")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone9"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload10")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone10"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload11")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone11"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload12")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone12"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload13")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone13"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    // Flow Chart -------------------------
    $("#fileupload14")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone14"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    // Quality Certificate  -------------------------
    $("#fileupload15")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone15"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "QM",
            subFolder: $(this).attr("subfolder"),
            client: $("#qm-clientid").data("clientname"),
            year: $("#qm-form #dt").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#qmGrid").jqGrid({
      url:
        "ajax/getQm.php?displaymode=" +
        QP.gridMode +
        "&idclient=" +
        $("#qm-clientid").val(),
      datatype: "json",
      mtype: "POST",
      width: $("#qmGrid").parent().width(),
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        { label: "Year", name: "dt", index: "dt", align: "center", width: 100 },
        {
          label: "Halal policy",
          name: "policy",
          index: "policy",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Halal HACCP",
          name: "haccp",
          index: "haccp",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Management team",
          name: "team",
          index: "team",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Training",
          name: "training",
          index: "training",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Purchasing of Halal ingredients",
          name: "purchasing",
          index: "purchasing",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Cleaning plan for Halal",
          name: "cleaning",
          index: "cleaning",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Production plan for Halal",
          name: "production",
          index: "production",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Handling of non-conforming products",
          name: "handling",
          index: "handling",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Storage of Halal products",
          name: "storage",
          index: "storage",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Traceability",
          name: "traceability",
          index: "traceability",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Internal audits",
          name: "audit",
          index: "audit",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Laboranalysis",
          name: "analysis",
          index: "analysis",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Additional Documents",
          name: "addoc",
          index: "addoc",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Flow Chart",
          name: "flowchart",
          index: "flowchart",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Quality Certificate",
          name: "qcertificate",
          index: "qcertificate",
          align: "left",
          width: 220,
          search: false,
          formatter: formatDoclink,
          unformat: unformatDoclink,
          cellattr: function (rowId, val, rawObject, cm, rdata) {
            return 'class="upload-area" title=""';
          },
        },
        {
          label: "Note",
          name: "note",
          index: "note",
          align: "left",
          width: 300,
          search: false,
        },
        { name: "deleted", index: "deleted", editable: false, hidden: true },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#qmPager",
      sortname: "dt",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
        initFileUploader({
          fileUploadSelector: "#qmGrid .fileupload",
          dropzoneSelector: "#qmGrid .dropzone",
          progressSelector: "#qmGrid .progress",

          dataModifier: function (e, data) {
            const colName = {
              addoc: "add",
              policy: "policy",
              haccp: "haccp",
              team: "team",
              training: "training",
              purchasing: "purchasing",
              cleaning: "cleaning",
              production: "production",
              handling: "handling",
              storage: "storage",
              traceability: "traceability",
              audit: "audit",
              analysis: "analysis",
              flowchart: "flowchart",
              qcertificate: "qcertificate",
            }[$(e.target).attr("folderType")];

            data.formData = {
              folderType: $(e.target).attr("folderType"),
              infoType: "QM",
              subFolder: "Halal " + colName,
              client: $("#qm-clientid").data("clientname"),
              year: $(e.target).closest("tr").find("td:nth-child(2)").text(),
            };
          },

          onSuccess: function (e, data) {
            // attach the uploaded files to product and reload grid
            $(e.target).parent().siblings(".progress").hide();
            $(e.target).parent().show();

            if (!data.result.files.length) {
              return;
            }

            const fileData = {
              name: data.result.files[0].name,
              glink: data.result.files[0].googleDriveUrl,
              hostpath: data.result.files[0].url,
              hostUrl: data.result.files[0].hostUrl,
            };

            const FD = new FormData();

            FD.append("rtype", "saveQMOneCol");
            //FD.append('id', $(e.target).closest('tr').attr('id'));
            FD.append("uid", 0);
            FD.append("data[idclient]", $("#qm-clientid").val());
            FD.append(
              "data[id]",
              $(e.target).closest("tr").find("td:nth-child(1)").text()
            );
            FD.append(
              "data[dt]",
              $(e.target).closest("tr").find("td:nth-child(2)").text()
            );

            const colName = {
              add: "add",
              policy: "policy",
              haccp: "haccp",
              team: "team",
              training: "training",
              purchasing: "purchasing",
              cleaning: "cleaning",
              production: "production",
              handling: "handling",
              storage: "storage",
              traceability: "traceability",
              audit: "audit",
              analysis: "analysis",
              flowchart: "flowchart",
              qcertificate: "qcertificate",
            }[data.result.files[0].folderType];

            FD.append("data[" + colName + "]", JSON.stringify(fileData));

            fetch("/ajax/ajaxHandler.php", {
              method: "POST",
              credentials: "include",
              body: FD,
            })
              .then((r) => r.json())
              .then((j) => {
                if (j.status != "1") {
                  alert("There was an error attaching the files.");
                  return;
                }

                // Finally reload the grid to show the new files in the cell
                $("#qmGrid").jqGrid().trigger("reloadGrid");
              });
          },
        });
      },
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.deleted === "1") rowclass = { class: "deleted" };
        return rowclass;
      },

      loadComplete: function (data) {
        // add event listeners to upload areas to change their appearance when a file is dragged
        document
          .querySelectorAll(".upload-area")
          .forEach((area) => area.addEventListener("dragover", handleDragOver));
        document
          .querySelectorAll(".upload-area")
          .forEach((area) =>
            area.addEventListener("dragleave", handleDragLeave)
          );
        document
          .querySelectorAll(".upload-area")
          .forEach((area) => area.addEventListener("drop", handleDrop));
      },
    });
    $("#qmGrid").jqGrid("navGrid", "#qmPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: true,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        QP.newQM();
      },
      editfunc: function () {
        QP.editQM();
      },
      delfunc: function () {
        QP.deleteQM();
      },
    });

    $("#qmGrid").navButtonAdd("#qmPager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        QP.onToggleRemovedRecordsMode();
      },
    });
    $("#qmGrid").navButtonAdd("#qmGrid_toppager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        QP.onToggleRemovedRecordsMode();
      },
    });

    // initialize the reactivity of drop areas in the grid
    document
      .querySelector("body")
      .addEventListener("dragover", handleDragOverDocument);
    document
      .querySelector("body")
      .addEventListener("dragleave", handleDragLeaveDocument);
    document.querySelector("body").addEventListener("drop", handleDropDocument);
  },

  onToggleRemovedRecordsMode: function () {
    if (QP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      QP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      QP.gridMode = 1;
    }
    $("#qm-clientid").trigger("change");
  },

  clearForm: function () {
    QP.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#qm-form ul").empty();
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#qm-form #qmid").val(response.data.id);
    }
    $("#qmModal").prop("submit", 0);
    $("#qmModal").modal("show");
  },

  getNextQMId: function (callback) {
    var prod = {};
    prod.idclient = $("#qm-clientid").val();
    $.get("ajax/ajaxHandler.php", {
      uid: 0,
      data: prod,
      rtype: "nextQMId",
    }).done(callback);
  },

  newQM: function () {
    if ($("#qm-clientid").val() == "") {
      alert("Please select client");
      return;
    }
    QP.clearForm();

    $("#qmModal-label").text("New QM record");
    QP.getNextQMId(QP.fillForm);
  },

  editQM: function () {
    if (
      $("#qmGrid").jqGrid(
        "getCell",
        jQuery("#qmGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    QP.clearForm();

    $("#qmModal-label").text("Edit QM record");
    $("#qm-form #qmid").val(
      $("#qmGrid").jqGrid(
        "getCell",
        $("#qmGrid").jqGrid("getGridParam", "selrow"),
        "id"
      )
    );
    $("#qm-form #dt").val(
      $("#qmGrid").jqGrid(
        "getCell",
        $("#qmGrid").jqGrid("getGridParam", "selrow"),
        "dt"
      )
    );
    $("#qm-form #note").val(
      $("#qmGrid").jqGrid(
        "getCell",
        $("#qmGrid").jqGrid("getGridParam", "selrow"),
        "note"
      )
    );
    Utils.filesToList("ulpolicy", "qmGrid", "policy");
    Utils.filesToList("ulhaccp", "qmGrid", "haccp");
    Utils.filesToList("ulteam", "qmGrid", "team");
    Utils.filesToList("ultraining", "qmGrid", "training");
    Utils.filesToList("ulpurchasing", "qmGrid", "purchasing");
    Utils.filesToList("ulcleaning", "qmGrid", "cleaning");
    Utils.filesToList("ulproduction", "qmGrid", "production");
    Utils.filesToList("ulhandling", "qmGrid", "handling");
    Utils.filesToList("ulstorage", "qmGrid", "storage");
    Utils.filesToList("ultraceability", "qmGrid", "traceability");
    Utils.filesToList("ulaudit", "qmGrid", "audit");
    Utils.filesToList("ulanalysis", "qmGrid", "analysis");
    Utils.filesToList("uladdoc", "qmGrid", "addoc");
    Utils.filesToList("ulflowchart", "qmGrid", "flowchart");
    Utils.filesToList("ulqcertificate", "qmGrid", "qcertificate");
    $("#qmModal").prop("submit", 1);
    $("#qmModal").modal("show");
  },

  deleteQM: function () {
    if (
      $("#qmGrid").jqGrid(
        "getCell",
        $("#qmGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete the QM record?")) {
      QP.sendDeleteQMRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.idclient = $("#qm-clientid").val();
    doc.id = $("#qm-form #qmid").val();
    doc.dt = $("#qm-form #dt").val();
    doc.note = $("#qm-form #note").val();
    doc.policy = Utils.filesToJSON("ulpolicy");
    doc.haccp = Utils.filesToJSON("ulhaccp");
    doc.team = Utils.filesToJSON("ulteam");
    doc.training = Utils.filesToJSON("ultraining");
    doc.purchasing = Utils.filesToJSON("ulpurchasing");
    doc.cleaning = Utils.filesToJSON("ulcleaning");
    doc.production = Utils.filesToJSON("ulproduction");
    doc.handling = Utils.filesToJSON("ulhandling");
    doc.storage = Utils.filesToJSON("ulstorage");
    doc.traceability = Utils.filesToJSON("ultraceability");
    doc.audit = Utils.filesToJSON("ulaudit");
    doc.analysis = Utils.filesToJSON("ulanalysis");
    doc.addoc = Utils.filesToJSON("uladdoc");
    doc.flowchart = Utils.filesToJSON("ulflowchart");
    doc.qcertificate = Utils.filesToJSON("ulqcertificate");
    return doc;
  },

  validateForm: function () {
    if ($("#qm-form #dt").val().trim() == "") {
      Utils.notifyInput($("#qm-form #dt"), "No Year specified");
      return false;
    }
    return true;
  },

  sendModifyQMRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", { rtype: "saveQM", uid: 0, data: doc }).done(
      function (data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          return;
        }
        Utils.notify("success", "Changes were submitted");
        $("#qmModal").prop("submit", 1);
        $("#qmModal").modal("hide");
      }
    );
  },

  sendRemoveQMRequest: function () {
    var doc = { id: $("#qm-form #qmid").val() };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeQM",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#qmGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New QM record data was added");
    });
  },

  sendDeleteQMRequest: function () {
    var doc = {
      id: $("#qmGrid").jqGrid(
        "getCell",
        $("#qmGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "markDeletedQM",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#qmGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Record was deleted");
    });
  },

  onSave: function () {
    QP.clearAlerts();
    if (!QP.validateForm()) {
      return;
    }
    var doc = QP.createDocFromInputData();
    QP.sendModifyQMRequest(doc);
  },
};

var AP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("auditItem");

    AP.initGrid();

    AP.initFileUploader();

    $('a[data-toggle="tab"]').on("shown.bs.tab", function () {
      $(".chosen-select").chosen("destroy").chosen();
    });

    $("#auditModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        AP.sendRemoveAuditRequest();
      } else jQuery("#auditGrid").jqGrid().trigger("reloadGrid");
    });

    $(document).bind("drag-over", function (e) {
      var dropZones = $(".fileinput-button"),
        timeout = window.dropZoneTimeout;
      if (timeout) {
        clearTimeout(timeout);
      } else {
        dropZones.addClass("in");
      }
      var hoveredDropZone = $(e.target).closest(dropZones);
      dropZones.not(hoveredDropZone).removeClass("hover");
      hoveredDropZone.addClass("hover");
      window.dropZoneTimeout = setTimeout(function () {
        window.dropZoneTimeout = null;
        dropZones.removeClass("in hover");
      }, 100);
    });
  },

  initFileUploader: function () {
    $("#fileupload1")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone1"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "audit",
            subFolder: $(this).attr("subfolder"),
            client: $("#audit-clientid").data("clientname"),
            auditorid: $("#audit-form #auditorid").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload2")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone2"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "audit",
            subFolder: $(this).attr("subfolder"),
            client: $("#audit-clientid").data("clientname"),
            auditorid: $("#audit-form #auditorid").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload3")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone3"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "audit",
            subFolder: $(this).attr("subfolder"),
            client: $("#audit-clientid").data("clientname"),
            auditorid: $("#audit-form #auditorid").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload4")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone4"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "audit",
            subFolder: $(this).attr("subfolder"),
            client: $("#audit-clientid").data("clientname"),
            auditorid: $("#audit-form #auditorid").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload5")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone5"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"),
            infoType: "audit",
            subFolder: $(this).attr("subfolder"),
            client: $("#audit-clientid").data("clientname"),
            auditorid: $("#audit-form #auditorid").val(),
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#auditGrid").jqGrid({
      url: "ajax/getAudit.php",
      datatype: "json",
      mtype: "POST",
      width: $("#auditGrid").parent().width(),
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "Audit Nr",
          name: "auditnr",
          index: "auditnr",
          align: "center",
          width: 100,
        },
        {
          label: "Auditor ID",
          name: "auditorid",
          index: "auditorid",
          align: "center",
          width: 100,
        },
        {
          label: "Auditor Name",
          name: "auditorname",
          index: "auditorname",
          align: "left",
          width: 200,
        },
        {
          label: "Auditee Name",
          name: "auditeename",
          index: "auditeename",
          align: "left",
          width: 200,
        },
        {
          label: "Order",
          name: "order",
          index: "order",
          align: "left",
          width: 220,
          formatter: formatDoclink,
          unformat: unformatDoclink,
        },
        {
          label: "Plan",
          name: "plan",
          index: "plan",
          align: "left",
          width: 220,
          formatter: formatDoclink,
          unformat: unformatDoclink,
        },
        {
          label: "Report",
          name: "report",
          index: "report",
          align: "left",
          width: 220,
          formatter: formatDoclink,
          unformat: unformatDoclink,
        },
        {
          label: "Certificate",
          name: "certificate",
          index: "certificate",
          align: "left",
          width: 220,
          formatter: formatDoclink,
          unformat: unformatDoclink,
        },
        {
          label: "GTC",
          name: "gtc",
          index: "gtc",
          align: "left",
          width: 220,
          formatter: formatDoclink,
          unformat: unformatDoclink,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#auditPager",
      sortname: "auditnr",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
      },
    });
    $("#auditGrid").jqGrid("navGrid", "#auditPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: true,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        AP.newAudit();
      },
      editfunc: function () {
        AP.editAudit();
      },
      delfunc: function () {
        AP.deleteAudit();
      },
    });
    $("#auditGrid").jqGrid("filterToolbar", {
      enableClear: false,
      searchOnEnter: false,
    });
  },

  clearForm: function () {
    AP.clearAlerts();
    $("#audit-form input").val("");
    $("#audit-form ul").empty();
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#audit-form #auditid").val(response.data.id);
    }
    $("#auditModal").prop("submit", 0);
    $("#auditModal").modal("show");
  },

  getNextAuditId: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "nextAuditId" }).done(
      callback
    );
  },

  newAudit: function () {
    AP.clearForm();

    $("#auditModal-label").text("New Audit record");
    AP.getNextAuditId(AP.fillForm);
  },

  editAudit: function () {
    if (
      $("#auditGrid").jqGrid(
        "getCell",
        jQuery("#auditGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    AP.clearForm();

    $("#auditModal-label").text("Edit Audit record");
    $("#audit-form #auditid").val(
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "id"
      )
    );
    $("#audit-form #auditnr").val(
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "auditnr"
      )
    );
    $("#audit-form #auditorid").val(
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "auditorid"
      )
    );
    $("#audit-form #auditorname").val(
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "auditorname"
      )
    );
    $("#audit-form #auditeename").val(
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "auditeename"
      )
    );
    Utils.filesToList("ulorder", "auditGrid", "order");
    Utils.filesToList("ulplan", "auditGrid", "plan");
    Utils.filesToList("ulreport", "auditGrid", "report");
    Utils.filesToList("ulcertificate", "auditGrid", "certificate");
    Utils.filesToList("ulgtc", "auditGrid", "gtc");
    $("#auditModal").prop("submit", 1);
    $("#auditModal").modal("show");
  },

  deleteAudit: function () {
    if (
      $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete the Audit record?")) {
      AP.sendDeleteAuditRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.id = $("#audit-form #auditid").val();
    doc.auditnr = $("#audit-form #auditnr").val();
    doc.auditorid = $("#audit-form #auditorid").val();
    doc.auditorname = $("#audit-form #auditorname").val();
    doc.auditeename = $("#audit-form #auditeename").val();
    doc.order = Utils.filesToJSON("ulorder");
    doc.plan = Utils.filesToJSON("ulplan");
    doc.report = Utils.filesToJSON("ulreport");
    doc.certificate = Utils.filesToJSON("ulcertificate");
    doc.gtc = Utils.filesToJSON("ulgtc");
    return doc;
  },

  validateForm: function () {
    if ($("#audit-form #auditnr").val().trim() == "") {
      Utils.notifyInput($("#audit-form #auditnr"), "No Audit Nr specified");
      return false;
    }
    if ($("#audit-form #auditid").val().trim() == "") {
      Utils.notifyInput($("#audit-form #auditid"), "No Audit ID specified");
      return false;
    }
    if ($("#audit-form #auditorname").val().trim() == "") {
      Utils.notifyInput(
        $("#audit-form #auditorname"),
        "No Auditor Name specified"
      );
      return false;
    }
    if ($("#audit-form #auditeename").val().trim() == "") {
      Utils.notifyInput(
        $("#audit-form #auditeename"),
        "No Auditee Name specified"
      );
      return false;
    }
    return true;
  },

  sendModifyAuditRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveAudit",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#auditModal").prop("submit", 1);
      $("#auditModal").modal("hide");
    });
  },

  sendRemoveAuditRequest: function () {
    var doc = { id: $("#audit-form #auditid").val() };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAudit",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#auditGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New Audit record data was added");
    });
  },

  sendDeleteAuditRequest: function () {
    var doc = {
      id: $("#auditGrid").jqGrid(
        "getCell",
        $("#auditGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAudit",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#auditGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Record was deleted");
    });
  },

  onSave: function () {
    AP.clearAlerts();
    if (!AP.validateForm()) {
      return;
    }
    var doc = AP.createDocFromInputData();
    AP.sendModifyAuditRequest(doc);
  },
};

// ADMINISTRATION page

var SP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("groupItem");

    SP.gridMode = 0;
    SP.initGrid();

    $("#adminModal").on("shown.bs.modal", function () {
      $(".chosen-select").chosen("destroy").chosen();
    });

    $("#adminModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        SP.sendRemoveAdminRequest();
      } else jQuery("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onRestoreAdmin: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "restoreAdmin",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onChangeProp: function (n, id) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "change" + n,
      uid: 0,
      data: { id: id },
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#adminGrid").jqGrid({
      url: "ajax/getAdmin.php?displaymode=0",
      datatype: "json",
      mtype: "POST",
      width: $("#adminGrid").parent().width(),
      height: h,

      viewrecords: true,
      sortorder: "desc",
      shrinkToFit: false,
      toppager: true,
      hoverrows: false,
      gridview: true,

      colModel: [
        {
          index: "id",
          name: "id",
          align: "left",
          hidden: true,
          key: true,
          frozen: true,
        },

        {
          label: "Client Name",
          name: "name",
          index: "name",
          align: "left",
          width: 200,
          sortable: false,
          frozen: true,
        },

        {
          label: "Type",
          name: "type",
          index: "type",
          align: "left",
          width: 200,
          stype: "select",
          searchoptions: { value: ":[All];1:Company;2:Facility" },
        },
        /*
        {
          label: "Company",
          name: "company",
          index: "company",
          align: "left",
          width: 200,
        },
        */
        {
          label: "Email",
          name: "email",
          index: "email",
          align: "left",
          width: 300,
        },
        {
          label: "Prefix",
          name: "prefix",
          index: "prefix",
          align: "left",
          width: 220,
        },
        {
          label: "Login",
          name: "login",
          index: "login",
          align: "left",
          width: 220,
        },
        /*
        {
          label: 'Contact Person',
          name: 'contact_person',
          index: 'contact_person',
          align: 'left',
          width: 220,
        },

        {
          label: 'VAT',
          name: 'vat',
          index: 'vat',
          align: 'left',
          width: 220,
        },

        {
          label: 'Industry',
          name: 'industry',
          index: 'industry',
          align: 'left',
          stype: 'select',
          searchoptions: { value: ":[All];Slaughter Houses:Slaughter Houses;Meat Processing:Meat Processing;All Other:All Other"},

          width: 220,
        },

        {
          label: 'Product Category',
          name: 'category',
          index: 'category',
          align: 'left',
          stype: 'select',
          searchoptions: { value: ":[All];Meat Abattoir:Meat Abattoir;Meat Processing Plant:Meat Processing Plant;Manufacturing including Animal Derived Materials:Manufacturing including Animal Derived Materials;Dairy and/or Egg Farming or Processing:Dairy and/or Egg Farming or Processing;Bakery and/or Confectionery:Bakery and/or Confectionery;Beverages:Beverages;Oils:Oils;Non-Edible Foods or Non-consumable Liquids:Non-Edible Foods or Non-consumable Liquids;Spices and/or Sauces:Spices and/or Sauces;(Synthetic) Chemicals Cosmetics:(Synthetic) Chemicals Cosmetics;Trading or Private Labeling:Trading or Private Labeling;Warehousing and/or Storage Catering:Warehousing and/or Storage Catering;Other:Other"},
          width: 220,
        },

        {
          label: 'Process Status',
          name: 'process_status',
          index: 'process_status',
          align: 'left',
          width: 220,
        },

        {
          label: 'Days remain to expiry date',
          name: 'CertificateExpiryDate',
          index: 'CertificateExpiryDate',
          align: 'left',
          width: 220,
        },

        
        {
          label: 'Products allowed/published',
          name: 'prodnumber',
          index: 'prodnumber',
          align: 'left',
          width: 220,
        },

        {
          label: 'Ingredients allowed/published',
          name: 'ingrednumber',
          index: 'ingrednumber',
          align: 'left',
          width: 220,
        },

         {
          label: "Password",
          name: "pass",
          index: "pass",
          align: "left",
          width: 200,
          search: false,
        },
        {
          label: "Ingredients numeber",
          name: "ingrednumber",
          index: "ingrednumber",
          width: 70,
          align: "right",
          search: false,
        },
        {
          label: "Products numeber",
          name: "prodnumber",
          index: "prodnumber",
          width: 70,
          align: "right",
          search: false,
        },
    */
        {
          label: "Role",
          name: "isclient",
          index: "isclient",
          width: 200,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];0:Admin;1:Client;2:Auditor" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        /*
        {
          label: "Applications",
          name: "application",
          index: "application",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "P/I/QM",
          name: "clients",
          index: "clients",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "Audit",
          name: "audit",
          index: "audit",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "Administration",
          name: "canadmin",
          index: "canadmin",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
    */
        /*
        { name: 'deleted', index: 'deleted', editable: false, hidden: true },
        */
        {
          label: "Blocked",
          name: "blocked",
          index: "blocked",
          editable: false,
          width: 140,
          search: false,
          sortable: false,
          formatter: formatAdminUnblockButton,
        },

        {
          label: "Deleted",
          name: "deleted",
          index: "deleted",
          formatter: formatAdminRestoreButton,
          editable: false,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#adminPager",
      sortname: "name",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("span.isclient")) {
          SP.onChangeProp(
            "IsClient",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.application")) {
          SP.onChangeProp(
            "Application",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.clients")) {
          SP.onChangeProp(
            "Clients",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.audit")) {
          SP.onChangeProp("Audit", $(e.target).closest("tr.jqgrow").attr("id"));
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.canadmin")) {
          SP.onChangeProp(
            "CanAdmin",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        }

        return true; // select the row
      },
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.deleted === "1") rowclass = { class: "deleted" };
        else if (rd.blocked === "1") rowclass = { class: "highlighted-week" };
        return rowclass;
      },
    });

    $("#adminGrid").jqGrid("navGrid", "#adminPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: true,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        SP.newAdmin();
      },
      editfunc: function () {
        SP.editAdmin();
      },
      delfunc: function () {
        SP.deleteAdmin();
      },
    });
    $("#adminGrid").jqGrid("filterToolbar", {
      enableClear: false,
      searchOnEnter: false,
    });
    /*
    $('#adminGrid').jqGrid(
      'setLabel',
      'prodnumber',
      'Product number',
      { 'text-align': 'center' },
      { title: 'Number of products allowed for the certification' }
    );
    $('#adminGrid').jqGrid(
      'setLabel',
      'ingrednumber',
      'Ingredients number',
      { 'text-align': 'center' },
      { title: 'Number of ingredients allowed for the certification' }
    );
    */

    $("#adminGrid").navButtonAdd("#adminPager", {
      caption: "",
      title: "Export all clients to Excel",
      buttonicon: "ace-icon fa fa-file-excel-o",
      onClickButton: function () {
        SP.onExportAllClientsToExcel();
      },
    });

    $("#adminGrid").navButtonAdd("#adminGrid_toppager", {
      caption: "",
      title: "Export all clients to Excel",
      buttonicon: "ace-icon fa fa-file-excel-o",
      onClickButton: function () {
        SP.onExportAllClientsToExcel();
      },
    });

    $("#adminGrid").navButtonAdd("#adminPager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        SP.onToggleRemovedRecordsMode();
      },
    });

    $("#adminGrid").navButtonAdd("#adminGrid_toppager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        SP.onToggleRemovedRecordsMode();
      },
    });

    // Add bulk import button to bottom pager
    $("#adminGrid").navButtonAdd("#adminPager", {
      caption: "",
      title: "Bulk import clients from Excel",
      buttonicon: "ace-icon fa fa-upload",
      onClickButton: function () {
        SP.onBulkImportClients();
      },
    });

    // Add bulk import button to top pager
    $("#adminGrid").navButtonAdd("#adminGrid_toppager", {
      caption: "",
      title: "Bulk import clients from Excel",
      buttonicon: "ace-icon fa fa-upload",
      onClickButton: function () {
        SP.onBulkImportClients();
      },
    });
  },

  // Add this to your SP object
  onBulkImportClients: function () {
    // Create modal HTML
    var modalHTML = `
    <div class="modal fade" id="bulkImportModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="bulkImportModalLabel">Bulk Import Clients</h4>
          </div>
          <div class="modal-body">
            <div class="alert alert-info">
              <p>Upload an Excel file with client data. </p>
            </div>
            <form id="bulkImportForm" enctype="multipart/form-data">
              <div class="form-group">
                <label for="excelFile">Excel File</label>
                <input type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" class="form-control" required>
                <p class="help-block">Only .xlsx or .xls files are allowed</p>
              </div>
            </form>
            <div id="importProgress" style="display: none;">
              <div class="progress progress-striped active">
                <div class="progress-bar" style="width: 0%"></div>
              </div>
              <div id="importStatus" class="text-center"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="startImport">Start Import</button>
          </div>
        </div>
      </div>
    </div>
  `;

    // Append modal to body if not already there
    if ($("#bulkImportModal").length === 0) {
      $("body").append(modalHTML);
    }

    // Show modal
    $("#bulkImportModal").modal("show");

    // Handle sample file download
    $("#downloadSample")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        $.post("ajax/ajaxHandler.php", {
          rtype: "getClientImportSample",
          uid: 0,
        }).done(function (data) {
          var response = JSON.parse(data);
          if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
          }
          downloadURI(response.data.url, response.data.name);
        });
      });

    // Handle import start
    $("#startImport")
      .off("click")
      .on("click", function () {
        var fileInput = $("#excelFile")[0];
        if (fileInput.files.length === 0) {
          Utils.notify("error", "Please select a file first");
          return;
        }

        // Show progress and reset
        $("#importProgress").show();
        $("#importProgress .progress-bar").css("width", "0%");
        $("#startImport").prop("disabled", true);

        var fakeProgress = 0;
        var progressInterval = setInterval(function () {
          if (fakeProgress < 95) {
            fakeProgress += Math.random() * 5; // Randomly between 0 to 5
            if (fakeProgress > 95) fakeProgress = 95;
            $("#importProgress .progress-bar").css("width", fakeProgress + "%");
          }
        }, 200);

        // Read file as binary string
        var reader = new FileReader();
        reader.onload = function (e) {
          var fileContent = e.target.result;

          var doc = {
            fileName: fileInput.files[0].name,
            fileContent: btoa(fileContent),
          };

          $("#infoModal").modal("show");

          $.post("ajax/ajaxHandler.php", {
            rtype: "importClientsFromExcel",
            uid: 0,
            data: doc,
          })
            .done(function (data) {
              $("#infoModal").modal("hide");
              clearInterval(progressInterval);
              $("#importProgress .progress-bar").css("width", "100%");

              var response = JSON.parse(data);
              if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                $("#importStatus").html(
                  '<span class="text-danger">' +
                    response.statusDescription +
                    "</span>"
                );
              } else {
                $("#importStatus").html(
                  '<span class="text-success">Import completed successfully! ' +
                    response.data.processed +
                    " records processed.</span>"
                );
                $("#adminGrid").trigger("reloadGrid");
                setTimeout(function () {
                  $("#bulkImportModal").modal("hide");
                }, 2000);
              }
            })
            .fail(function () {
              $("#infoModal").modal("hide");
              clearInterval(progressInterval);
              $("#importProgress .progress-bar").css("width", "100%");

              Utils.notify("error", "Error during import");
              $("#importStatus").html(
                '<span class="text-danger">Error during import</span>'
              );
            })
            .always(function () {
              $("#startImport").prop("disabled", false);
            });
        };

        reader.readAsBinaryString(fileInput.files[0]);
      });

    // Reset modal when closed
    $("#bulkImportModal").on("hidden.bs.modal", function () {
      $("#bulkImportForm")[0].reset();
      $("#importProgress").hide();
      $("#importProgress .progress-bar").css("width", "0%");
      $("#importStatus").text("");
    });
  },

  onExportAllClientsToExcel: function () {
    $("#infoModal").modal("show");
    var doc = {};
    doc.ids = $("#adminGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
      rtype: "sendAllClientsExcelReportRequest",
      uid: 0,
      data: doc,
    }).done(function (data) {
      $("#infoModal").modal("hide");
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      downloadURI(response.data.url, response.data.name);
    });
  },

  onToggleRemovedRecordsMode: function () {
    if (SP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      SP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      SP.gridMode = 1;
    }
    $("#adminGrid").jqGrid("setGridParam", {
      url: "ajax/getAdmin.php?displaymode=" + SP.gridMode,
    });
    $("#adminGrid").jqGrid().trigger("reloadGrid");
  },

  onUnblockUser: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "unblockUser",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  clearForm: function () {
    SP.clearAlerts();
    $("#admin-form input").not(":radio").val("");
    $("#admin-form .ace-switch").prop("checked", false);
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#admin-form #adminid").val(response.data.id);
    }
    $("div[rel*=isclient1]").show();
    $("#adminModal").prop("submit", 0);
    $("#adminModal").modal("show");
  },

  getNextAdminId: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "nextAdminId" }).done(
      callback
    );
  },

  newAdmin: function () {
    SP.clearForm();

    $("#adminModal-label").text("New User");
    SP.getNextAdminId(SP.fillForm);
  },

  editAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        jQuery("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    SP.clearForm();

    var id = $("#adminGrid").jqGrid(
      "getCell",
      $("#adminGrid").jqGrid("getGridParam", "selrow"),
      "id"
    );

    $("#adminModal-label").text("Edit User");

    $.post("ajax/ajaxHandler.php", {
      rtype: "getAdmin",
      uid: 0,
      id: id,
    }).done(function (data) {
      var response = JSON.parse(data);

      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }

      var data = response.data;
      $("#admin-form #adminid").val(data.id);
      //$("#admin-form #company_id").val(data.company_id);
      $("#admin-form #parent_id").val(data.parent_id);

      if (!data.parent_id) {
        // If idparent has a value, select "Company"
        $("input[name='type'][value='company']").prop("checked", true);
        $("#company-fields").slideUp(); // Show company fields
      } else {
        // Otherwise, select "Facility"
        $("input[name='type'][value='facility']").prop("checked", true);
        $("#company-fields").slideDown(); // Hide company fields
      }

      // Initially hide the company admin field
      /*
      if ($("#admin-form #company_id").val() !== "") {
        $("#admin-form #company_admin_field").show();
      } else {
        $("#admin-form #company_admin_field").hide();
      }
      if (data.company_admin == "1") {
        $("#admin-form #company_admin").prop("checked", true);
      } else {
        $("#admin-form #company_admin").prop("checked", false);
      }
      */

      $("#admin-form #name").val(data.name);
      $("#admin-form #email").val(data.email);
      $("#admin-form #prefix").val(data.prefix);
      $("#admin-form #login").val(data.login);
      $("#admin-form #ingrednumber").val(data.ingrednumber);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form #address").val(data.address);
      $("#admin-form #city").val(data.city);
      $("#admin-form #zip").val(data.zip);
      $("#admin-form #country").val(data.country);
      $("#admin-form #vat").val(data.vat);
      $("#admin-form #industry").val(data.industry);
      $("#admin-form #category").val(data.category);
      $("#admin-form #contact_person").val(data.contact_person);
      $("#admin-form #phone").val(data.phone);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form input[name=isclient][value='" + data.isclient + "']").prop(
        "checked",
        true
      );
      $("#admin-form #clients_audit option:selected").prop("selected", false);
      $("#admin-form #sources_audit option:selected").prop("selected", false);

      $(
        "input[name='pork_free_facility'][value='" +
          data.pork_free_facility +
          "']"
      ).prop("checked", true);
      $(
        "input[name='dedicated_halal_lines'][value='" +
          data.dedicated_halal_lines +
          "']"
      ).prop("checked", true);
      $("#admin-form #export_regions").val(data.export_regions);
      $(
        "input[name='third_party_products'][value='" +
          data.third_party_products +
          "']"
      ).prop("checked", true);
      $(
        "input[name='third_party_halal_certified'][value='" +
          data.third_party_halal_certified +
          "']"
      ).prop("checked", true);

      if (data.clients_audit) {
        for (i = 0; i < data.clients_audit.length; i++) {
          v = data.clients_audit[i];
          $("#admin-form #clients_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #clients_audit").trigger("chosen:updated");
      }
      if (data.sources_audit) {
        for (i = 0; i < data.sources_audit.length; i++) {
          v = data.sources_audit[i];
          $("#admin-form #sources_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #sources_audit").trigger("chosen:updated");
      }
      $("#admin-form #dashboard").prop(
        "checked",
        data.dashboard == 1 ? true : false
      );
      $("#admin-form #application").prop(
        "checked",
        data.application == 1 ? true : false
      );
      $("#admin-form #calendar").prop(
        "checked",
        data.calendar == 1 ? true : false
      );
      $("#admin-form #products").prop(
        "checked",
        data.products == 1 ? true : false
      );
      $("#admin-form #ingredients").prop(
        "checked",
        data.ingredients == 1 ? true : false
      );
      $("#admin-form #documents").prop(
        "checked",
        data.documents == 1 ? true : false
      );
      $("#admin-form #canadmin").prop(
        "checked",
        data.canadmin == 1 ? true : false
      );
      var rel = "isclient" + data.isclient;
      $(".rel").hide();
      $("div[rel*=" + rel + "]").show();
    });

    /*

    $("#admin-form #adminid").val(

    );
    $("#admin-form #name").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "name"
      )
    );
    $("#admin-form #email").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "email"
      )
    );
    $("#admin-form #prefix").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prefix"
      )
    );
    $("#admin-form #login").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "login"
      )
    );
    $("#admin-form #prodnumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prodnumber"
      )
    );
    $("#admin-form #ingrednumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "ingrednumber"
      )
    );

    $("#admin-form #isclient").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "isclient"
      ) == 1
    );

    $("#admin-form #application").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "application"
      ) == 1
    );
  /*
    $("#admin-form #clients").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "clients"
      ) == 1
    );
    $("#admin-form #audit").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "audit"
      ) == 1
    );

    $("#admin-form #canadmin").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "canadmin"
      ) == 1
    );
  */
    $("#adminModal").prop("submit", 1);
    $("#adminModal").modal("show");
  },

  deleteAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete record?")) {
      SP.sendDeleteAdminRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.id = $("#admin-form #adminid").val();
    doc.company_id = $("#admin-form #company_id").val();
    doc.company_admin = $("#admin-form #company_admin").is(":checked")
      ? "1"
      : "0";
    doc.parent_id = $("#admin-form #parent_id").val();
    doc.name = $("#admin-form #name").val();
    doc.email = $("#admin-form #email").val();
    doc.prefix = $("#admin-form #prefix").val();
    doc.login = $("#admin-form #login").val();

    doc.address = $("#admin-form #address").val();
    doc.city = $("#admin-form #city").val();
    doc.zip = $("#admin-form #zip").val();
    doc.country = $("#admin-form #country").val();
    doc.vat = $("#admin-form #vat").val();
    doc.industry = $("#admin-form #industry").val();
    doc.category = $("#admin-form #category").val();
    doc.contact_person = $("#admin-form #contact_person").val();
    doc.phone = $("#admin-form #phone").val();

    doc.pork_free_facility = $(
      "input[name='pork_free_facility']:checked"
    ).val();
    doc.dedicated_halal_lines = $(
      "input[name='dedicated_halal_lines']:checked"
    ).val();
    doc.export_regions = $("#admin-form #export_regions").val();
    doc.third_party_products = $(
      "input[name='third_party_products']:checked"
    ).val();
    doc.third_party_halal_certified = $(
      "input[name='third_party_halal_certified']:checked"
    ).val();

    if ($("#admin-form #pass").val().trim() != "")
      doc.pass = hex_sha512($("#admin-form #pass").val());
    doc.ingrednumber = $("#admin-form #ingrednumber").val();
    doc.prodnumber = $("#admin-form #prodnumber").val();
    doc.isclient = $("#admin-form input[name=isclient]:checked").val();
    doc.clients_audit = $("#clients_audit")
      .map(function () {
        return $(this).val();
      })
      .get();
    doc.sources_audit = $("#sources_audit")
      .map(function () {
        return $(this).val();
      })
      .get();
    doc.dashboard = $("#admin-form #dashboard").prop("checked") ? 1 : 0;
    doc.application = $("#admin-form #application").prop("checked") ? 1 : 0;
    doc.calendar = $("#admin-form #calendar").prop("checked") ? 1 : 0;
    doc.products = $("#admin-form #products").prop("checked") ? 1 : 0;
    doc.ingredients = $("#admin-form #ingredients").prop("checked") ? 1 : 0;
    doc.documents = $("#admin-form #documents").prop("checked") ? 1 : 0;
    doc.canadmin = $("#admin-form #canadmin").prop("checked") ? 1 : 0;
    /*
    doc.clients = $("#admin-form #clients").prop("checked") ? 1 : 0;
    doc.application = $("#admin-form #application").prop("checked") ? 1 : 0;
    doc.audit = $("#admin-form #audit").prop("checked") ? 1 : 0;
    doc.canadmin = $("#admin-form #canadmin").prop("checked") ? 1 : 0;
  */
    return doc;
  },

  validateForm: function () {
    if ($("#admin-form #name").val().trim() == "") {
      Utils.notifyInput($("#admin-form #name"), "No Client Name specified");
      return false;
    }
    if (!validateEmailsList($("#admin-form #email").val().trim())) {
      Utils.notifyInput($("#admin-form #email"), "Wrong Email(s) specified");
      return false;
    }
    if ($("#admin-form #prefix").val().trim() == "") {
      //Utils.notifyInput($("#admin-form #prefix"), "No Prefix specified");
      //return false;
    }
    if ($("#admin-form #login").val().trim() == "") {
      Utils.notifyInput($("#admin-form #login"), "No Login specified");
      return false;
    }
    if (
      $("#adminModal").prop("submit") == 0 &&
      !validatePassword($("#admin-form #pass").val().trim())
    ) {
      Utils.notifyInput($("#admin-form #pass"), "Wrong Password specified");
      return false;
    }
    if ($("#admin-form input[name=isclient]:checked").val() == "1") {
      if ($("#admin-form #ingrednumber").val().trim() == "") {
        Utils.notifyInput(
          $("#admin-form #ingrednumber"),
          "No Ingredients number specified"
        );
        return false;
      }
      if ($("#admin-form #prodnumber").val().trim() == "") {
        Utils.notifyInput(
          $("#admin-form #prodnumber"),
          "No Products number specified"
        );
        return false;
      }
    }

    return true;
  },

  sendModifyAdminRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminModal").prop("submit", 1);
      $("#adminModal").modal("hide");
    });
  },

  sendRemoveAdminRequest: function () {
    var doc = { id: $("#admin-form #adminid").val() };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New Admin record data was removed");
    });
  },

  sendDeleteAdminRequest: function () {
    var doc = {
      id: $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Record was deleted");
    });
  },

  onSave: function () {
    SP.clearAlerts();
    if (!SP.validateForm()) {
      return;
    }
    var doc = SP.createDocFromInputData();
    SP.sendModifyAdminRequest(doc);
  },
};

function formatAdminRestoreButton(cellValue, options, rowObject) {
  return cellValue == 0
    ? ""
    : '<button class="btn btn-success" data-grid="' +
        '" data-id="' +
        options.rowId +
        '"onclick=SP.onRestoreAdmin(event)>Restore</button>';
}

var APP = {
  onDocumentReady: function () {
    APP.isClient = $("input#app-clientid").length > 0;

    Common.setMainMenuItem("appItem");

    Common.loadClientsData(Common.populateClients);

    $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });

    $(".datepicker")
      .datepicker()
      .on("changeDate", function (e) {
        APP.clearAlerts();
      });

    APP.initGrid();

    APP.initFileUploader();

    $("#app-clientid").on("change", function () {
      $("#app-clientid").data(
        "clientname",
        $("#app-clientid option:selected").data("clientname")
      );
      jQuery("#appGrid").jqGrid("setGridParam", {
        url: "ajax/getCycles.php?idclient=" + this.value,
      });
      jQuery("#appGrid").jqGrid().trigger("reloadGrid");
    });

    $("#appModal").on("hide.bs.modal", function (e) {});

    $(document).bind("drag-over", function (e) {
      var dropZones = $(".fileinput-button"),
        timeout = window.dropZoneTimeout;
      if (timeout) {
        clearTimeout(timeout);
      } else {
        dropZones.addClass("in");
      }
      var hoveredDropZone = $(e.target).closest(dropZones);
      dropZones.not(hoveredDropZone).removeClass("hover");
      hoveredDropZone.addClass("hover");
      window.dropZoneTimeout = setTimeout(function () {
        window.dropZoneTimeout = null;
        dropZones.removeClass("in hover");
      }, 100);
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 200;
    $("#appGrid").jqGrid({
      url: "ajax/getCycles.php?idclient=" + $("#app-clientid").val(),
      datatype: "json",
      mtype: "POST",
      width: $("#appGrid").parent().width(),
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          name: "Name",
          index: "name",
          align: "left",
          width: 250,
          sortable: false,
        },
        {
          name: "Started",
          index: "started",
          align: "center",
          width: 100,
          sortable: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        {
          name: "Completed",
          index: "completed",
          align: "center",
          width: 100,
          sortable: false,
          formatter: "date",
          formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
        },
        { name: "state", index: "state", hidden: true },
      ],
      rowNum: 20,
      viewrecords: true,
      pgbuttons: false,
      pginput: false,
      altRows: true,
      sortname: "id",
      sortorder: "asc",
      shrinkToFit: true,
      toppager: false,
      subGrid: true,
      gridComplete: function () {
        $("#app-clientid").trigger("change");
      },

      subGridOptions: {
        plusicon: "ace-icon fa fa-plus center bigger-110 blue",
        minusicon: "ace-icon fa fa-minus center bigger-110 blue",
        openicon: "ace-icon fa fa-chevron-right center orange",
      },
      subGridRowExpanded: function (subgrid_id, row_id) {
        var subgridTableId = subgrid_id + "_t";
        $("#" + subgrid_id).html(
          "<table id='" + subgridTableId + "' class='scroll'></table>"
        );
        $("<div id='" + subgridTableId + "pager" + "'></div>").insertAfter(
          $("#" + subgrid_id)
        );
        $("#" + subgridTableId).data(
          "cycle",
          $("#appGrid").jqGrid("getCell", row_id, "Name")
        );
        $("#" + subgridTableId).jqGrid({
          datatype: "json",
          url: "ajax/getCycleApp.php?idcycle=" + row_id,
          rowNum: 3,
          pager: "#" + subgridTableId + "pager",
          pgbuttons: false,
          pginput: false,
          viewrecords: true,
          shrinkToFit: false,
          autowidth: true,
          height: "100%",
          sortname: "id",
          sortorder: "asc",
          colModel: [
            {
              index: "id",
              name: "id",
              align: "left",
              hidden: true,
              key: true,
              frozen: true,
            },
            {
              name: "Name",
              index: "name",
              align: "left",
              width: 135,
              sortable: false,
              frozen: true,
              editoptions: { isClient: APP.isClient },
              formatter: formatApplink,
              unformat: unformatFirstColumn,
            },
            {
              label: "Application",
              name: "app",
              index: "app",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "offerstate",
                isClientField: true,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "appstate", index: "appstate", hidden: true },
            {
              label: "Offer",
              name: "offer",
              index: "offer",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "sofferstate",
                isClientField: false,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "offerstate", index: "offerstate", hidden: true },
            {
              label: "Signed offer",
              name: "soffer",
              index: "soffer",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "planstate",
                isClientField: true,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "sofferstate", index: "sofferstate", hidden: true },
            {
              label: "Audit plan",
              name: "plan",
              index: "plan",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "checkliststate",
                isClientField: false,
                needConfirm: true,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "planstate", index: "planstate", hidden: true },
            {
              label: "Auditor ID/name",
              name: "auditorname",
              index: "auditorname",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "auditornamestate",
                isClientField: false,
                needConfirm: true,
                isClient: APP.isClient,
              },
            },
            {
              name: "auditornamestate",
              index: "auditornamestate",
              hidden: true,
            },
            {
              label: "Checklist",
              name: "checklist",
              index: "checklist",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "reportstate",
                isClientField: false,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "checkliststate", index: "checkliststate", hidden: true },
            {
              label: "Audit report",
              name: "report",
              index: "report",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "actionstate",
                isClientField: false,
                needConfirm: true,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "reportstate", index: "reportstate", hidden: true },
            {
              label: "Corrective action",
              name: "action",
              index: "action",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "liststate",
                isClientField: true,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "actionstate", index: "actionstate", hidden: true },
            {
              label: "List of products for certification",
              name: "list",
              index: "list",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "paymentstate",
                isClientField: true,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "liststate", index: "liststate", hidden: true },
            {
              label: "Proof of payment",
              name: "payment",
              index: "payment",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "certstate",
                isClientField: true,
                needConfirm: false,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "paymentstate", index: "paymentstate", hidden: true },
            {
              label: "Certificate",
              name: "cert",
              index: "cert",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "newappstate",
                isClientField: false,
                needConfirm: true,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "certstate", index: "certstate", hidden: true },
            {
              label: "Application for new items",
              name: "newapp",
              index: "newapp",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "newcertstate",
                isClientField: true,
                needConfirm: true,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "newappstate", index: "newappstate", hidden: true },
            {
              label: "New certificates",
              name: "newcert",
              index: "newcert",
              align: "left",
              width: 120,
              sortable: false,
              editoptions: {
                nextStateCol: "newappstate",
                isClientField: false,
                needConfirm: true,
                isClient: APP.isClient,
              },
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "newcertstate", index: "newcertstate", hidden: true },
            {
              label: "Issue date",
              name: "issuedate",
              index: "issuedate",
              align: "center",
              width: 100,
              sortable: false,
              formatter: "date",
              formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
            },
            {
              name: "Expiry date",
              index: "completed",
              align: "center",
              width: 100,
              sortable: false,
              formatter: "date",
              formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" },
            },
            {
              label: "Halal training",
              name: "halaltraining",
              index: "halaltraining",
              align: "left",
              width: 120,
              sortable: false,
              formatter: formatApplink,
              unformat: unformatApplink,
            },
            { name: "state", index: "state", hidden: true },
            { name: "notifystatus", index: "notifystatus", hidden: true },
          ],
          rowattr: function (rd) {
            var rowclass = "";
            if (rd.state === "0") rowclass = { class: "inactive" };
            else rowclass = { class: "active-cycle" };

            return rowclass;
          },
          gridComplete: function () {
            Common.updatePagerIcons(this);
          },
        });
        $("#" + subgridTableId).jqGrid("setFrozenColumns");
      },
      caption: "",
      loadComplete: function (data) {
        var req_top_row = $("#appGrid").getDataIDs()[0];
        $("#appGrid").setSelection(req_top_row, true);
        Common.updatePagerIcons(this);
      },
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.state === "1") rowclass = { class: "active-cycle" };

        return rowclass;
      },
    });
  },

  initFileUploader: function () {
    $("#fileupload1")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone1"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "application",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload2")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone2"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "offer",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload3")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone3"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "signed offer",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload4")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone4"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "audit plan",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload5")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone5"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "check list",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload6")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone6"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "audit report",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload7")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone7"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "corrective action",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload8")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone8"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "list of products",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(doc|docx|DOC|DOCX)$/i.test(uploadFile.name)) {
            alert("You can upload MS Word Document file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload9")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone9"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "certificate",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload10")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone10"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "application",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload11")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone11"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "certificate",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload12")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone12"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "halaltraining",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
    $("#fileupload13")
      .fileupload({
        url: "fileupload/ProcessFiles.php",
        dataType: "json",
        dropZone: $("#dropzone13"),
        add: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for audit
            client: $("#app-clientid").data("clientname"),
            cycle: $("#app-form").data("cycle"),
            subcycle: $("#app-form").data("subcycle"),
            infoType: "application",
            docType: "payment",
          };
          var goUpload = true;
          var uploadFile = data.files[0];
          if (!/\.(pdf|PDF)$/i.test(uploadFile.name)) {
            alert("You can upload PDF file(s) only");
            goUpload = false;
          }
          if (goUpload == true) {
            data.submit();
          }
        },
        start: function (e) {
          $(this).parent().siblings(".loader").css("display", "block").show();
        },
        fail: function (e, data) {
          // kill all progress bars awaiting for showing
          $(this).parent().siblings(".loader").hide();
          alert("Error uploading file (" + data.errorThrown + ")");
        },
        done: function (e, data) {
          // hide loader and add new li with new file info
          $(this).parent().siblings(".loader").hide();
          $.each(data.result.files, function (index, file) {
            var jsonstring =
              '{"name":"' +
              file.name +
              '","glink":"' +
              file.googleDriveUrl +
              '","hostpath":"' +
              file.url +
              '","hostUrl":"' +
              file.hostUrl +
              '"}';
            var ell;
            if (file.name.length > 35) ell = file.name.substr(0, 30) + "...";
            else ell = file.name;
            var filename = $(
              '<li class="uploaded-file-name" originalname="' +
                encodeURI(jsonstring) +
                '"></li>'
            );
            filename.append($("<span>", { text: ell }));
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  file.googleDriveId +
                  " hostpath=" +
                  encodeURI(file.url) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
            // add li to the list of the appropriate ul - class from folderType
            $("#ul" + file.folderType).append(filename);
          });
        },
      })
      .prop("disabled", !$.support.fileInput)
      .parent()
      .addClass($.support.fileInput ? undefined : "disabled");
  },

  onEditApplication: function (e) {
    var grid = $("#" + $(e.target).data("grid"));
    var rowid = $(e.target).data("id");
    grid.jqGrid("setSelection", rowid);
    APP.clearForm();
    $("#appModal-cycle").text(
      grid.data("cycle") + " / " + grid.jqGrid("getCell", rowid, "Name")
    );
    $("#app-form").data("cycle", grid.data("cycle"));
    $("#app-form").data("subcycle", grid.jqGrid("getCell", rowid, "Name"));
    $("#app-form").data("appid", rowid);
    $("#app-form").data("gridid", grid[0].id);
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ulapp",
      grid,
      "app",
      "appstate",
      "dropzone1",
      false,
      APP.isClient
    );
    // halal dropzone
    Utils.filesToListForApplication(
      "uloffer",
      grid,
      "offer",
      "offerstate",
      "dropzone2",
      false,
      APP.isClient
    );
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ulsoffer",
      grid,
      "soffer",
      "sofferstate",
      "dropzone3",
      true,
      APP.isClient
    );
    // halal dropzone
    Utils.filesToListForApplication(
      "ulplan",
      grid,
      "plan",
      "planstate",
      "dropzone4",
      false,
      APP.isClient
    );

    $("#app-form #auditorname").val(
      grid.jqGrid("getCell", rowid, "auditorname")
    );
    if (
      grid.jqGrid("getCell", rowid, "planstate") <= 3 &&
      grid.jqGrid("getCell", rowid, "planstate") > 0
    ) {
      $("#app-form #auditorname").show();
      $("#app-form #auditorname").prop(
        "disabled",
        grid.jqGrid("getCell", rowid, "planstate") > 1
      );
    } else $("#app-form #auditorname").hide();

    Utils.filesToListForApplication(
      "ulchecklist",
      grid,
      "checklist",
      "checkliststate",
      "dropzone5",
      false,
      APP.isClient
    );
    Utils.filesToListForApplication(
      "ulreport",
      grid,
      "report",
      "reportstate",
      "dropzone6",
      false,
      APP.isClient
    );
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ulaction",
      grid,
      "action",
      "actionstate",
      "dropzone7",
      true,
      APP.isClient
    );
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ullist",
      grid,
      "list",
      "liststate",
      "dropzone8",
      true,
      APP.isClient
    );
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ulpayment",
      grid,
      "payment",
      "paymentstate",
      "dropzone13",
      true,
      APP.isClient
    );
    // halal dropzone
    Utils.filesToListForApplication(
      "ulcert",
      grid,
      "cert",
      "certstate",
      "dropzone9",
      false,
      APP.isClient
    );
    // show certificate issue date only if certificate stage is active
    $("#app-form #issuedate").val(grid.jqGrid("getCell", rowid, "issuedate"));
    $("#app-form #issuedate").prop(
      "disabled",
      grid.jqGrid("getCell", rowid, "certstate") > 1
    );
    $("#app-form #issuedate").prop(
      "activefield",
      grid.jqGrid("getCell", rowid, "certstate") == 1
    );
    if (
      grid.jqGrid("getCell", rowid, "certstate") <= 3 &&
      grid.jqGrid("getCell", rowid, "certstate") > 0
    ) {
      $("#app-form #issuedate").show();
    } else $("#app-form #issuedate").hide();
    // ------ client dropzone
    Utils.filesToListForApplication(
      "ulnewapp",
      grid,
      "newapp",
      "newappstate",
      "dropzone10",
      true,
      APP.isClient
    );
    // halal dropzone
    Utils.filesToListForApplication(
      "ulnewcert",
      grid,
      "newcert",
      "newcertstate",
      "dropzone11",
      false,
      APP.isClient
    );
    // halal training  dropzone
    Utils.filesToList(
      "ulhalaltraining",
      $(e.target).data("grid"),
      "halaltraining"
    );

    if (!APP.isClient) $("#cycleswitch").show();
    else $("#cycleswitch").hide();

    $("#appModal").prop("submit", 1);
    $("#appModal").modal("show");
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  clearForm: function () {
    $("#app-form").data("cycle", "");
    $("#app-form").data("subcycle", "");

    $(".fileinput-button").show();
    $(".ace-switch-4").prop("checked", false);
    $(".ace-switch-4").prop("disabled", false);
    $(".ace-switch-4").trigger("change");

    $("#ulapp").empty();
    $("#uloffer").empty();
    $("#ulsoffer").empty();
    $("#ulplan").empty();
    $("#auditorname").val("");
    $("#ulchecklist").empty();
    $("#ulreport").empty();
    $("#ulaction").empty();
    $("#ullist").empty();
    $("#ulpayment").empty();
    $("#ulcert").empty();
    $("#ulnewapp").empty();
    $("#ulnewcert").empty();
    $("#ulhalaltraining").empty();

    $("#app-form input").val("");
    $("#app-form select").val(null).trigger("change");
  },

  onCompleteApp: function (e) {
    event.preventDefault();
    if (!confirm("Complete the stage?")) return;
    var grid = $("#" + $(e.target).data("grid"));
    var rowid = $(e.target).data("id");
    var params = {};
    params.id = $(e.target).data("id");
    params.name = $(e.target).data("name");
    params.nextname = $(e.target).data("nextname");
    params.cycle =
      grid.data("cycle") + " / " + grid.jqGrid("getCell", rowid, "Name");
    $.post("ajax/ajaxHandler.php", {
      rtype: "completeApplication",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#" + $(e.target).data("grid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  onConfirmApp: function (e) {
    event.preventDefault();
    if (!confirm("Confirm the stage?")) return;
    var grid = $("#" + $(e.target).data("grid"));
    var rowid = $(e.target).data("id");
    var params = {};
    params.id = $(e.target).data("id");
    params.name = $(e.target).data("name");
    params.nextname = $(e.target).data("nextname");
    params.isclient = APP.isClient ? 1 : 0;
    params.cycle =
      grid.data("cycle") + " / " + grid.jqGrid("getCell", rowid, "Name");
    $.post("ajax/ajaxHandler.php", {
      rtype: "confirmApplication",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#" + $(e.target).data("grid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  onStopNotification: function (e) {
    event.preventDefault();
    if (!confirm("Stop notifing the client about the certificate expiry?"))
      return;
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "stopApplicationNotification",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#" + $(e.target).data("grid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  onSkipApplication: function (e) {
    event.preventDefault();
    if (!confirm("Skip this subcycle and move forward to the next one?"))
      return;
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "skipApplication",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#" + $(e.target).data("grid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  onConcelApp: function (e) {
    event.preventDefault();
    if (!confirm("Cancel application process from this step onwards?")) return;
    var params = {};
    params.id = $(e.target).data("id");
    params.name = $(e.target).data("name");
    $.post("ajax/ajaxHandler.php", {
      rtype: "cancelApplication",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#" + $(e.target).data("grid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.id = $("#app-form").data("appid");
    doc.auditorname = $("#app-form #auditorname").val().trim();
    doc.app = Utils.filesToJSON("ulapp");
    doc.offer = Utils.filesToJSON("uloffer");
    doc.soffer = Utils.filesToJSON("ulsoffer");
    doc.plan = Utils.filesToJSON("ulplan");
    doc.checklist = Utils.filesToJSON("ulchecklist");
    doc.report = Utils.filesToJSON("ulreport");
    doc.action = Utils.filesToJSON("ulaction");
    doc.list = Utils.filesToJSON("ullist");
    doc.payment = Utils.filesToJSON("ulpayment");
    doc.cert = Utils.filesToJSON("ulcert");
    doc.issuedate = $("#app-form #issuedate").val().trim();
    doc.newapp = Utils.filesToJSON("ulnewapp");
    doc.newcert = Utils.filesToJSON("ulnewcert");
    doc.halaltraining = Utils.filesToJSON("ulhalaltraining");
    doc.state = $("#cycleconf").prop("checked") ? 0 : 1;
    return doc;
  },

  validateForm: function () {
    if ($("#issuedate").prop("activefield") == 1) {
      if ($("#issuedate").val().trim() == "") {
        Utils.notifyInput(
          $("#issuedate"),
          "No Certificate issue date specified"
        );
        return;
      }
    }
    return true;
  },

  sendModifyProductRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveApplication",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");

      $("#appModal").prop("submit", 1);
      $("#appModal").modal("hide");
      $("#" + $("#app-form").data("gridid"))
        .jqGrid()
        .trigger("reloadGrid");
    });
  },

  onSave: function () {
    if (!APP.validateForm()) {
      return;
    }
    if ($("#cycleconf").prop("checked") == 1) {
      if (!confirm("Do you want to complete the current cycle?")) return;
    }
    var doc = APP.createDocFromInputData();
    APP.sendModifyProductRequest(doc);
  },
};

var CP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("groupItem");

    CP.gridMode = 0;
    CP.initGrid();

    $("#companyModal").on("shown.bs.modal", function () {
      $(".chosen-select").chosen("destroy").chosen();
    });
    $("#companyModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        CP.sendRemoveCompanyRequest();
      } else jQuery("#companyGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onChangeProp: function (n, id) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "change" + n,
      uid: 0,
      data: { id: id },
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#companyGrid").jqGrid().trigger("reloadGrid");
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#companyGrid").jqGrid({
      url: "ajax/getCompanies.php?displaymode=0",
      datatype: "json",
      mtype: "POST",
      width: $("#companyGrid").parent().width(),
      height: h,
      colModel: [
        { index: "id", name: "id", align: "left", hidden: true, key: true },
        {
          label: "Company Name",
          name: "name",
          index: "name",
          align: "left",
          width: 850,
        },

        {
          label: "Active",
          name: "active",
          index: "active",
          width: 450,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Active;0:In-Active" },
          formatter: formatCompanyButton,
          unformat: unformatButton,
        },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#companyPager",
      sortname: "name",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
      },
      beforeSelectRow: function (rowid, e) {},
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.active === "0") rowclass = { class: "text-danger" };
        else if (rd.blocked === "1") rowclass = { class: "highlighted-week" };
        return rowclass;
      },
    });
    $("#companyGrid").jqGrid("navGrid", "#companyPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: false,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        CP.newCompany();
      },
      editfunc: function () {
        CP.editCompany();
      },
      delfunc: function () {
        CP.deleteCompany();
      },
    });
    $("#companyGrid").jqGrid("filterToolbar", {
      enableClear: false,
      searchOnEnter: false,
    });
    $("#companyGrid").jqGrid(
      "setLabel",
      "prodnumber",
      "Product number",
      { "text-align": "center" },
      { title: "Number of products allowed for the certification" }
    );
    $("#companyGrid").jqGrid(
      "setLabel",
      "ingrednumber",
      "Ingredients number",
      { "text-align": "center" },
      { title: "Number of ingredients allowed for the certification" }
    );
    /*
    $("#companyGrid").navButtonAdd("#companyPager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        CP.onToggleRemovedRecordsMode();
      },
    });
    $("#companyGrid").navButtonAdd("#companyGrid_toppager", {
      caption: "",
      title: "Toggle displaying removed records mode",
      buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
      onClickButton: function () {
        CP.onToggleRemovedRecordsMode();
      },
    });
    */
  },

  onToggleRemovedRecordsMode: function () {
    if (CP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      CP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      CP.gridMode = 1;
    }
    $("#companyGrid").jqGrid("setGridParam", {
      url: "ajax/getCompany.php?displaymode=" + CP.gridMode,
    });
    $("#companyGrid").jqGrid().trigger("reloadGrid");
  },

  onUnblockUser: function (e) {
    event.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "unblockUser",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#companyGrid").jqGrid().trigger("reloadGrid");
    });
  },

  clearForm: function () {
    CP.clearAlerts();
    $("#company-form input").not(":radio").val("");
    $("#company-form .ace-switch").prop("checked", false);
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#company-form #companyid").val(response.data.id);
    }
    $("div[rel*=isclient1]").show();
    $("#companyModal").prop("submit", 0);
    $("#companyModal").modal("show");
  },

  getNextCompanyId: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "nextCompanyId" }).done(
      callback
    );
  },

  newCompany: function () {
    CP.clearForm();

    $("#companyModal-label").text("New Company");
    CP.getNextCompanyId(CP.fillForm);
  },

  editCompany: function () {
    if (
      $("#companyGrid").jqGrid(
        "getCell",
        jQuery("#companyGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    CP.clearForm();

    var id = $("#companyGrid").jqGrid(
      "getCell",
      $("#companyGrid").jqGrid("getGridParam", "selrow"),
      "id"
    );

    $("#companyModal-label").text("Edit Company");

    $.post("ajax/ajaxHandler.php", {
      rtype: "getCompany",
      uid: 0,
      id: id,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      var data = response.data;
      $("#company-form #companyid").val(data.id);
      $("#company-form #name").val(data.name);
      $("#company-form input[name=active][value='" + data.active + "']").prop(
        "checked",
        true
      );
    });

    $("#companyModal").prop("submit", 1);
    $("#companyModal").modal("show");
  },

  deleteCompany: function () {
    if (
      $("#companyGrid").jqGrid(
        "getCell",
        $("#companyGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete record?")) {
      CP.sendDeleteCompanyRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.id = $("#company-form #companyid").val();
    doc.name = $("#company-form #name").val();
    doc.active = $("#company-form input[name=active]:checked").val();
    return doc;
  },

  validateForm: function () {
    if ($("#company-form #name").val().trim() == "") {
      Utils.notifyInput($("#company-form #name"), "No Company Name specified");
      return false;
    }
    return true;
  },

  sendModifyCompanyRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveCompany",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#companyModal").prop("submit", 1);
      $("#companyModal").modal("hide");
    });
  },

  sendRemoveCompanyRequest: function () {
    var doc = { id: $("#company-form #companyid").val() };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeCompany",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#companyGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New Company record data was removed");
    });
  },

  sendDeleteCompanyRequest: function () {
    var doc = {
      id: $("#companyGrid").jqGrid(
        "getCell",
        $("#companyGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeCompany",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#companyGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Record was deleted");
    });
  },

  onSave: function () {
    CP.clearAlerts();
    if (!CP.validateForm()) {
      return;
    }
    var doc = CP.createDocFromInputData();
    CP.sendModifyCompanyRequest(doc);
  },
};

// BRANCHES page

var BP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("groupItem");

    BP.gridMode = 0;
    BP.initGrid();

    $("#adminModal").on("shown.bs.modal", function () {
      $(".chosen-select").chosen("destroy").chosen();
    });

    $("#adminModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        BP.sendRemoveAdminRequest();
      } else jQuery("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onChangeProp: function (n, id) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "change" + n,
      uid: 0,
      data: { id: id },
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#adminGrid").jqGrid({
      url: "ajax/getBranches.php?displaymode=0",
      datatype: "json",
      mtype: "POST",
      width: $("#adminGrid").parent().width(),
      height: h,

      viewrecords: true,
      sortorder: "desc",
      shrinkToFit: false,
      toppager: true,
      hoverrows: false,
      gridview: true,

      colModel: [
        {
          index: "id",
          name: "id",
          align: "left",
          hidden: true,
          key: true,
          frozen: true,
        },

        {
          label: "Name",
          name: "name",
          index: "name",
          align: "left",
          width: 250,
          sortable: false,
          frozen: true,
        },

        {
          label: "Address",
          name: "address",
          index: "address",
          align: "left",
          width: 350,
          sortable: false,
          frozen: true,
        },

        {
          label: "Email",
          name: "email",
          index: "email",
          align: "left",
          width: 300,
        },

        {
          label: "Login",
          name: "login",
          index: "login",
          align: "left",
          width: 250,
        },

        {
          label: "Contact Person",
          name: "contact_person",
          index: "contact_person",
          align: "left",
          width: 250,
        },

        { name: "deleted", index: "deleted", editable: false, hidden: true },
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#adminPager",
      sortname: "name",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("span.isclient")) {
          BP.onChangeProp(
            "IsClient",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.application")) {
          BP.onChangeProp(
            "Application",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.clients")) {
          BP.onChangeProp(
            "Clients",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.audit")) {
          BP.onChangeProp("Audit", $(e.target).closest("tr.jqgrow").attr("id"));
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.canadmin")) {
          BP.onChangeProp(
            "CanAdmin",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        }

        return true; // select the row
      },
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.deleted === "1") rowclass = { class: "deleted" };
        else if (rd.blocked === "1") rowclass = { class: "highlighted-week" };
        return rowclass;
      },
    });
    $("#adminGrid").jqGrid("navGrid", "#adminPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: false,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        BP.newAdmin();
      },
      editfunc: function () {
        BP.editAdmin();
      },
      delfunc: function () {
        BP.deleteAdmin();
      },
    });
    $("#adminGrid").jqGrid("filterToolbar", {
      enableClear: false,
      searchOnEnter: false,
    });
    /*
    $('#adminGrid').jqGrid(
      'setLabel',
      'prodnumber',
      'Product number',
      { 'text-align': 'center' },
      { title: 'Number of products allowed for the certification' }
    );
    $('#adminGrid').jqGrid(
      'setLabel',
      'ingrednumber',
      'Ingredients number',
      { 'text-align': 'center' },
      { title: 'Number of ingredients allowed for the certification' }
    );
    */
    /*
    $('#adminGrid').navButtonAdd('#adminPager', {
      caption: '',
      title: 'Toggle displaying removed records mode',
      buttonicon: 'ace-icon fa fa-adjust gridmode-toggle',
      onClickButton: function () {
        BP.onToggleRemovedRecordsMode();
      },
    });
    $('#adminGrid').navButtonAdd('#adminGrid_toppager', {
      caption: '',
      title: 'Toggle displaying removed records mode',
      buttonicon: 'ace-icon fa fa-adjust gridmode-toggle',
      onClickButton: function () {
        BP.onToggleRemovedRecordsMode();
      },
    });
    */
  },

  onToggleRemovedRecordsMode: function () {
    if (BP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      BP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      BP.gridMode = 1;
    }
    $("#adminGrid").jqGrid("setGridParam", {
      url: "ajax/getAdmin.php?displaymode=" + BP.gridMode,
    });
    $("#adminGrid").jqGrid().trigger("reloadGrid");
  },

  onUnblockUser: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "unblockUser",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  clearForm: function () {
    BP.clearAlerts();
    $("#admin-form input").not(":radio").val("");
    $("#admin-form .ace-switch").prop("checked", false);
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#admin-form #adminid").val(response.data.id);
    }
    $("div[rel*=isclient1]").show();
    $("#adminModal").prop("submit", 0);
    $("#adminModal").modal("show");
  },

  getNextAdminId: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "nextAdminId" }).done(
      callback
    );
  },

  newAdmin: function () {
    BP.clearForm();

    $("#adminModal-label").text("New Branch");
    BP.getNextAdminId(BP.fillForm);
  },

  editAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        jQuery("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    BP.clearForm();

    var id = $("#adminGrid").jqGrid(
      "getCell",
      $("#adminGrid").jqGrid("getGridParam", "selrow"),
      "id"
    );

    $("#adminModal-label").text("Edit Branch");

    $.post("ajax/ajaxHandler.php", {
      rtype: "getAdmin",
      uid: 0,
      id: id,
    }).done(function (data) {
      var response = JSON.parse(data);

      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }

      var data = response.data;
      $("#admin-form #adminid").val(data.id);
      $("#admin-form #company_id").val(data.company_id);

      // Initially hide the company admin field
      if ($("#admin-form #company_id").val() !== "") {
        $("#admin-form #company_admin_field").show();
      } else {
        $("#admin-form #company_admin_field").hide();
      }
      if (data.company_admin == "1") {
        $("#admin-form #company_admin").prop("checked", true);
      } else {
        $("#admin-form #company_admin").prop("checked", false);
      }

      $("#admin-form #name").val(data.name);
      $("#admin-form #email").val(data.email);
      $("#admin-form #prefix").val(data.prefix);
      //$('#admin-form #login').val(data.login);
      $("#admin-form #ingrednumber").val(data.ingrednumber);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form #address").val(data.address);
      $("#admin-form #city").val(data.city);
      $("#admin-form #zip").val(data.zip);
      $("#admin-form #country").val(data.country);
      $("#admin-form #vat").val(data.vat);
      $("#admin-form #industry").val(data.industry);
      $("#admin-form #category").val(data.category);
      $("#admin-form #contact_person").val(data.contact_person);
      $("#admin-form #phone").val(data.phone);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form input[name=isclient][value='" + data.isclient + "']").prop(
        "checked",
        true
      );
      $("#admin-form #clients_audit option:selected").prop("selected", false);
      $("#admin-form #sources_audit option:selected").prop("selected", false);

      if (data.clients_audit) {
        for (i = 0; i < data.clients_audit.length; i++) {
          v = data.clients_audit[i];
          $("#admin-form #clients_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #clients_audit").trigger("chosen:updated");
      }
      if (data.sources_audit) {
        for (i = 0; i < data.sources_audit.length; i++) {
          v = data.sources_audit[i];
          $("#admin-form #sources_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #sources_audit").trigger("chosen:updated");
      }
      $("#admin-form #dashboard").prop(
        "checked",
        data.dashboard == 1 ? true : false
      );
      $("#admin-form #application").prop(
        "checked",
        data.application == 1 ? true : false
      );
      $("#admin-form #products").prop(
        "checked",
        data.products == 1 ? true : false
      );
      $("#admin-form #ingredients").prop(
        "checked",
        data.ingredients == 1 ? true : false
      );
      $("#admin-form #documents").prop(
        "checked",
        data.documents == 1 ? true : false
      );
      $("#admin-form #canadmin").prop(
        "checked",
        data.canadmin == 1 ? true : false
      );
      var rel = "isclient" + data.isclient;
      $(".rel").hide();
      $("div[rel*=" + rel + "]").show();
    });

    /*

    $("#admin-form #adminid").val(

    );
    $("#admin-form #name").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "name"
      )
    );
    $("#admin-form #email").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "email"
      )
    );
    $("#admin-form #prefix").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prefix"
      )
    );
    $("#admin-form #login").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "login"
      )
    );
    $("#admin-form #prodnumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prodnumber"
      )
    );
    $("#admin-form #ingrednumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "ingrednumber"
      )
    );

    $("#admin-form #isclient").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "isclient"
      ) == 1
    );

    $("#admin-form #application").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "application"
      ) == 1
    );
  /*
    $("#admin-form #clients").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "clients"
      ) == 1
    );
    $("#admin-form #audit").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "audit"
      ) == 1
    );

    $("#admin-form #canadmin").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "canadmin"
      ) == 1
    );
  */
    $("#adminModal").prop("submit", 1);
    $("#adminModal").modal("show");
  },

  deleteAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete record?")) {
      BP.sendDeleteAdminRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.id = $("#admin-form #adminid").val();
    doc.company_id = $("#admin-form #company_id").val();
    doc.company_admin = $("#admin-form #company_admin").is(":checked")
      ? "1"
      : "0";
    doc.name = $("#admin-form #name").val();
    doc.email = $("#admin-form #email").val();
    doc.prefix = $("#admin-form #prefix").val();
    //doc.login = $('#admin-form #login').val();

    doc.address = $("#admin-form #address").val();
    doc.city = $("#admin-form #city").val();
    doc.zip = $("#admin-form #zip").val();
    doc.country = $("#admin-form #country").val();
    doc.vat = $("#admin-form #vat").val();
    doc.industry = $("#admin-form #industry").val();
    doc.category = $("#admin-form #category").val();
    doc.contact_person = $("#admin-form #contact_person").val();
    doc.phone = $("#admin-form #phone").val();

    //if ($('#admin-form #pass').val().trim() != '')
    //      doc.pass = hex_sha512($('#admin-form #pass').val());
    doc.ingrednumber = $("#admin-form #ingrednumber").val();
    doc.prodnumber = $("#admin-form #prodnumber").val();
    doc.isclient = $("#admin-form input[name=isclient]:checked").val();
    doc.clients_audit = $("#clients_audit")
      .map(function () {
        return $(this).val();
      })
      .get();
    doc.sources_audit = $("#sources_audit")
      .map(function () {
        return $(this).val();
      })
      .get();
    doc.dashboard = $("#admin-form #dashboard").prop("checked") ? 1 : 0;
    doc.application = $("#admin-form #application").prop("checked") ? 1 : 0;
    doc.calendar = $("#admin-form #calendar").prop("checked") ? 1 : 0;
    doc.products = $("#admin-form #products").prop("checked") ? 1 : 0;
    doc.ingredients = $("#admin-form #ingredients").prop("checked") ? 1 : 0;
    doc.documents = $("#admin-form #documents").prop("checked") ? 1 : 0;
    doc.canadmin = $("#admin-form #canadmin").prop("checked") ? 1 : 0;
    /*
    doc.clients = $("#admin-form #clients").prop("checked") ? 1 : 0;
    doc.application = $("#admin-form #application").prop("checked") ? 1 : 0;
    doc.audit = $("#admin-form #audit").prop("checked") ? 1 : 0;
    doc.canadmin = $("#admin-form #canadmin").prop("checked") ? 1 : 0;
  */
    return doc;
  },

  validateForm: function () {
    if ($("#admin-form #name").val().trim() == "") {
      Utils.notifyInput($("#admin-form #name"), "No Client Name specified");
      return false;
    }
    if (!validateEmailsList($("#admin-form #email").val().trim())) {
      Utils.notifyInput($("#admin-form #email"), "Wrong Email(s) specified");
      return false;
    }
    /*
    if ($('#admin-form #prefix').val().trim() == '') {
      Utils.notifyInput($('#admin-form #prefix'), 'No Prefix specified');
      return false;
    }
    if ($('#admin-form #login').val().trim() == '') {
      Utils.notifyInput($('#admin-form #login'), 'No Login specified');
      return false;
    }
    */
    /*
    if (
      $('#adminModal').prop('submit') == 0 &&
      !validatePassword($('#admin-form #pass').val().trim())
    ) {
      Utils.notifyInput($('#admin-form #pass'), 'Wrong Password specified');
      return false;
    }
    */
    if ($("#admin-form input[name=isclient]:checked").val() == "1") {
      if ($("#admin-form #ingrednumber").val().trim() == "") {
        Utils.notifyInput(
          $("#admin-form #ingrednumber"),
          "No Ingredients number specified"
        );
        return false;
      }
      if ($("#admin-form #prodnumber").val().trim() == "") {
        Utils.notifyInput(
          $("#admin-form #prodnumber"),
          "No Products number specified"
        );
        return false;
      }
    }

    return true;
  },

  sendModifyAdminRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminModal").prop("submit", 1);
      $("#adminModal").modal("hide");
    });
  },

  sendRemoveAdminRequest: function () {
    var doc = { id: $("#admin-form #adminid").val() };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "New Admin record data was removed");
    });
  },

  sendDeleteAdminRequest: function () {
    var doc = {
      id: $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ),
    };
    $.post("ajax/ajaxHandler.php", {
      rtype: "removeAdmin",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
      Utils.notify("success", "Record was deleted");
    });
  },

  onSave: function () {
    BP.clearAlerts();
    if (!BP.validateForm()) {
      return;
    }
    var doc = BP.createDocFromInputData();
    BP.sendModifyAdminRequest(doc);
  },
};

// Facility Management
var FP = {
  onDocumentReady: function () {
    Common.setMainMenuItem("groupItem");

    FP.gridMode = 0;
    FP.initGrid();

    $("#adminModal").on("shown.bs.modal", function () {
      $(".chosen-select").chosen("destroy").chosen();
    });

    $("#adminModal").on("hide.bs.modal", function (e) {
      // remove added if modal was closed not by Submit
      if ($(e.target).prop("submit") == 0) {
        //  FP.sendRemoveAdminRequest();
      } else jQuery("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onRestoreAdmin: function (e) {
    e.preventDefault();
    var params = {};
    params.id = $(e.target).data("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "restoreAdmin",
      uid: 0,
      data: params,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  onChangeProp: function (n, id) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "change" + n,
      uid: 0,
      data: { id: id },
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminGrid").jqGrid().trigger("reloadGrid");
    });
  },

  initGrid: function () {
    var h =
      (window.innerHeight ||
        document.documentElement.clientHeight ||
        document.body.clientHeight) - 300;
    $("#adminGrid").jqGrid({
      url: "ajax/getFacility.php",
      datatype: "json",
      mtype: "POST",
      width: $("#adminGrid").parent().width(),
      height: h,

      viewrecords: true,
      sortorder: "desc",
      shrinkToFit: false,
      toppager: true,
      hoverrows: false,
      gridview: true,

      colModel: [
        {
          index: "id",
          name: "id",
          align: "left",
          hidden: true,
          key: true,
          frozen: true,
        },

        {
          label: "Facility Name",
          name: "name",
          index: "name",
          align: "left",
          width: 200,
          sortable: false,
          frozen: true,
        },

        {
          label: "Address",
          name: "company",
          index: "company",
          align: "left",
          width: 200,
        },
        {
          label: "Email",
          name: "email",
          index: "email",
          align: "left",
          width: 300,
        },
        {
          label: "Phone",
          name: "prefix",
          index: "prefix",
          align: "left",
          width: 220,
        },

        {
          label: "Contact Person",
          name: "contact_person",
          index: "contact_person",
          align: "left",
          width: 220,
        },

        {
          label: "VAT",
          name: "vat",
          index: "vat",
          align: "left",
          width: 220,
        },
        /*
        {
          label: 'Industry',
          name: 'industry',
          index: 'industry',
          align: 'left',
          stype: 'select',
          searchoptions: { value: ":[All];Slaughter Houses:Slaughter Houses;Meat Processing:Meat Processing;All Other:All Other"},

          width: 220,
        },

        {
          label: 'Product Category',
          name: 'category',
          index: 'category',
          align: 'left',
          stype: 'select',
          searchoptions: { value: ":[All];Meat Abattoir:Meat Abattoir;Meat Processing Plant:Meat Processing Plant;Manufacturing including Animal Derived Materials:Manufacturing including Animal Derived Materials;Dairy and/or Egg Farming or Processing:Dairy and/or Egg Farming or Processing;Bakery and/or Confectionery:Bakery and/or Confectionery;Beverages:Beverages;Oils:Oils;Non-Edible Foods or Non-consumable Liquids:Non-Edible Foods or Non-consumable Liquids;Spices and/or Sauces:Spices and/or Sauces;(Synthetic) Chemicals Cosmetics:(Synthetic) Chemicals Cosmetics;Trading or Private Labeling:Trading or Private Labeling;Warehousing and/or Storage Catering:Warehousing and/or Storage Catering;Other:Other"},
          width: 220,
        },

        {
          label: 'Process Status',
          name: 'process_status',
          index: 'process_status',
          align: 'left',
          width: 220,
        },

        {
          label: 'Days remain to expiry date',
          name: 'CertificateExpiryDate',
          index: 'CertificateExpiryDate',
          align: 'left',
          width: 220,
        },

        
        {
          label: 'Products allowed/published',
          name: 'prodnumber',
          index: 'prodnumber',
          align: 'left',
          width: 220,
        },

        {
          label: 'Ingredients allowed/published',
          name: 'ingrednumber',
          index: 'ingrednumber',
          align: 'left',
          width: 220,
        },

         {
          label: "Password",
          name: "pass",
          index: "pass",
          align: "left",
          width: 200,
          search: false,
        },
        {
          label: "Ingredients numeber",
          name: "ingrednumber",
          index: "ingrednumber",
          width: 70,
          align: "right",
          search: false,
        },
        {
          label: "Products numeber",
          name: "prodnumber",
          index: "prodnumber",
          width: 70,
          align: "right",
          search: false,
        },
 
        {
          label: "Applications",
          name: "application",
          index: "application",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "P/I/QM",
          name: "clients",
          index: "clients",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "Audit",
          name: "audit",
          index: "audit",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
        {
          label: "Administration",
          name: "canadmin",
          index: "canadmin",
          width: 90,
          align: "center",
          stype: "select",
          searchoptions: { value: ":[All];1:Yes;0:No" },
          formatter: formatAdminButton,
          unformat: unformatButton,
        },
    */
        /*
        { name: 'deleted', index: 'deleted', editable: false, hidden: true },
        */
      ],
      rowNum: 20,
      rowList: [20, 60, 100, 500],
      pager: "#adminPager",
      sortname: "name",
      viewrecords: true,
      sortorder: "asc",
      shrinkToFit: false,
      toppager: true,
      gridComplete: function () {
        Common.updatePagerIcons(this);
      },
      beforeSelectRow: function (rowid, e) {
        if ($(e.target).is("span.isclient")) {
          FP.onChangeProp(
            "IsClient",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.application")) {
          FP.onChangeProp(
            "Application",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.clients")) {
          FP.onChangeProp(
            "Clients",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.audit")) {
          FP.onChangeProp("Audit", $(e.target).closest("tr.jqgrow").attr("id"));
          return false; // don't select the row on click on the button
        } else if ($(e.target).is("span.canadmin")) {
          FP.onChangeProp(
            "CanAdmin",
            $(e.target).closest("tr.jqgrow").attr("id")
          );
          return false; // don't select the row on click on the button
        }

        return true; // select the row
      },
      rowattr: function (rd) {
        var rowclass = "";
        if (rd.deleted === "1") rowclass = { class: "deleted" };
        else if (rd.blocked === "1") rowclass = { class: "highlighted-week" };
        return rowclass;
      },
    });
    $("#adminGrid").jqGrid("navGrid", "#adminPager", {
      cloneToTop: true,
      edit: true,
      add: true,
      del: false,
      search: false,
      refresh: true,
      view: false,
      addfunc: function () {
        FP.newAdmin();
      },
      editfunc: function () {
        FP.editAdmin();
      },
      delfunc: function () {
        FP.deleteAdmin();
      },
    });
    $("#adminGrid").jqGrid("filterToolbar", {
      enableClear: false,
      searchOnEnter: false,
    });
    /*
    $('#adminGrid').jqGrid(
      'setLabel',
      'prodnumber',
      'Product number',
      { 'text-align': 'center' },
      { title: 'Number of products allowed for the certification' }
    );
    $('#adminGrid').jqGrid(
      'setLabel',
      'ingrednumber',
      'Ingredients number',
      { 'text-align': 'center' },
      { title: 'Number of ingredients allowed for the certification' }
    );
    */
  },

  clearForm: function () {
    FP.clearAlerts();
    $("#admin-form input").not(":radio").val("");
    $("#admin-form .ace-switch").prop("checked", false);
  },

  clearAlerts: function () {
    $(".alert-string").text("");
  },

  fillForm: function (data) {
    /*
    var response = JSON.parse(data);
    if (response.status == 0) {
      alert(response.statusDescription);
      return;
    }
    if (response.data) {
      $("#admin-form #adminid").val(response.data.id);
    }
      */
    $("#admin-form #adminid").val("");
    $("#adminModal").prop("submit", 0);
    $("#adminModal").modal("show");
  },

  getNextAdminId: function (callback) {
    $.get("ajax/ajaxHandler.php", { uid: 0, rtype: "nextAdminId" }).done(
      callback
    );
  },

  newAdmin: function () {
    FP.clearForm();

    $("#adminModal-label").text("Create Facility");
    FP.fillForm();
    //FP.getNextAdminId(FP.fillForm);
  },

  editAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        jQuery("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    FP.clearForm();

    var id = $("#adminGrid").jqGrid(
      "getCell",
      $("#adminGrid").jqGrid("getGridParam", "selrow"),
      "id"
    );

    $("#adminModal-label").text("Edit Facility");

    $.post("ajax/ajaxHandler.php", {
      rtype: "getFacility",
      uid: 0,
      id: id,
    }).done(function (data) {
      var response = JSON.parse(data);

      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }

      var data = response.data;
      $("#admin-form #adminid").val(data.id);

      $("#admin-form #name").val(data.name);
      $("#admin-form #email").val(data.email);
      $("#admin-form #prefix").val(data.prefix);
      $("#admin-form #ingrednumber").val(data.ingrednumber);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form #address").val(data.address);
      $("#admin-form #city").val(data.city);
      $("#admin-form #zip").val(data.zip);
      $("#admin-form #country").val(data.country);
      $("#admin-form #vat").val(data.vat);
      $("#admin-form #industry").val(data.industry);
      $("#admin-form #category").val(data.category);
      $("#admin-form #contact_person").val(data.contact_person);
      $("#admin-form #phone").val(data.phone);
      $("#admin-form #prodnumber").val(data.prodnumber);
      $("#admin-form input[name=isclient][value='" + data.isclient + "']").prop(
        "checked",
        true
      );
      $("#admin-form #clients_audit option:selected").prop("selected", false);
      $("#admin-form #sources_audit option:selected").prop("selected", false);

      $(
        "input[name='pork_free_facility'][value='" +
          data.pork_free_facility +
          "']"
      ).prop("checked", true);
      $(
        "input[name='dedicated_halal_lines'][value='" +
          data.dedicated_halal_lines +
          "']"
      ).prop("checked", true);
      $("#admin-form #export_regions").val(data.export_regions);
      $(
        "input[name='third_party_products'][value='" +
          data.third_party_products +
          "']"
      ).prop("checked", true);
      $(
        "input[name='third_party_halal_certified'][value='" +
          data.third_party_halal_certified +
          "']"
      ).prop("checked", true);

      if (data.clients_audit) {
        for (i = 0; i < data.clients_audit.length; i++) {
          v = data.clients_audit[i];
          $("#admin-form #clients_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #clients_audit").trigger("chosen:updated");
      }
      if (data.sources_audit) {
        for (i = 0; i < data.sources_audit.length; i++) {
          v = data.sources_audit[i];
          $("#admin-form #sources_audit option[value='" + v + "']").prop(
            "selected",
            true
          );
        }
        $("#admin-form #sources_audit").trigger("chosen:updated");
      }
      $("#admin-form #dashboard").prop(
        "checked",
        data.dashboard == 1 ? true : false
      );
      $("#admin-form #application").prop(
        "checked",
        data.application == 1 ? true : false
      );
      $("#admin-form #products").prop(
        "checked",
        data.products == 1 ? true : false
      );
      $("#admin-form #ingredients").prop(
        "checked",
        data.ingredients == 1 ? true : false
      );
      $("#admin-form #documents").prop(
        "checked",
        data.documents == 1 ? true : false
      );
      $("#admin-form #canadmin").prop(
        "checked",
        data.canadmin == 1 ? true : false
      );
      var rel = "isclient" + data.isclient;
      $(".rel").hide();
      $("div[rel*=" + rel + "]").show();
    });

    /*

    $("#admin-form #adminid").val(

    );
    $("#admin-form #name").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "name"
      )
    );
    $("#admin-form #email").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "email"
      )
    );
    $("#admin-form #prefix").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prefix"
      )
    );
    $("#admin-form #login").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "login"
      )
    );
    $("#admin-form #prodnumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "prodnumber"
      )
    );
    $("#admin-form #ingrednumber").val(
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "ingrednumber"
      )
    );

    $("#admin-form #isclient").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "isclient"
      ) == 1
    );

    $("#admin-form #application").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "application"
      ) == 1
    );
  /*
    $("#admin-form #clients").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "clients"
      ) == 1
    );
    $("#admin-form #audit").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "audit"
      ) == 1
    );

    $("#admin-form #canadmin").prop(
      "checked",
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "canadmin"
      ) == 1
    );
  */
    $("#adminModal").prop("submit", 1);
    $("#adminModal").modal("show");
  },

  deleteAdmin: function () {
    if (
      $("#adminGrid").jqGrid(
        "getCell",
        $("#adminGrid").jqGrid("getGridParam", "selrow"),
        "id"
      ) == null
    ) {
      alert("Please select record");
      return;
    }
    if (confirm("Delete record?")) {
      FP.sendDeleteAdminRequest();
    }
  },

  createDocFromInputData: function () {
    var doc = {};
    doc.id = $("#admin-form #adminid").val();

    doc.name = $("#admin-form #name").val();
    doc.email = $("#admin-form #email").val();
    doc.prefix = $("#admin-form #prefix").val();
    doc.address = $("#admin-form #address").val();
    doc.city = $("#admin-form #city").val();
    doc.zip = $("#admin-form #zip").val();
    doc.country = $("#admin-form #country").val();
    doc.vat = $("#admin-form #vat").val();
    doc.industry = $("#admin-form #industry").val();
    doc.category = $("#admin-form #category").val();
    doc.contact_person = $("#admin-form #contact_person").val();
    doc.phone = $("#admin-form #phone").val();

    doc.pork_free_facility = $(
      "input[name='pork_free_facility']:checked"
    ).val();
    doc.dedicated_halal_lines = $(
      "input[name='dedicated_halal_lines']:checked"
    ).val();
    doc.export_regions = $("#admin-form #export_regions").val();
    doc.third_party_products = $(
      "input[name='third_party_products']:checked"
    ).val();
    doc.third_party_halal_certified = $(
      "input[name='third_party_halal_certified']:checked"
    ).val();

    doc.ingrednumber = $("#admin-form #ingrednumber").val();
    doc.prodnumber = $("#admin-form #prodnumber").val();
    doc.isclient = $("#admin-form input[name=isclient]:checked").val();

    /*
    doc.clients = $("#admin-form #clients").prop("checked") ? 1 : 0;
    doc.application = $("#admin-form #application").prop("checked") ? 1 : 0;
    doc.audit = $("#admin-form #audit").prop("checked") ? 1 : 0;
    doc.canadmin = $("#admin-form #canadmin").prop("checked") ? 1 : 0;
  */
    return doc;
  },

  validateForm: function () {
    if ($("#admin-form #name").val().trim() == "") {
      Utils.notifyInput($("#admin-form #name"), "No Facility Name specified");
      return false;
    }
    if (!validateEmailsList($("#admin-form #email").val().trim())) {
      Utils.notifyInput($("#admin-form #email"), "Wrong Email(s) specified");
      return false;
    }
    /*
    if ($("#admin-form #prefix").val().trim() == "") {
      Utils.notifyInput($("#admin-form #prefix"), "No Prefix specified");
      return false;
    }
    if ($("#admin-form #login").val().trim() == "") {
      Utils.notifyInput($("#admin-form #login"), "No Login specified");
      return false;
    }
    if (
      $("#adminModal").prop("submit") == 0 &&
      !validatePassword($("#admin-form #pass").val().trim())
    ) {
      Utils.notifyInput($("#admin-form #pass"), "Wrong Password specified");
      return false;
    }
      */
    var address = $("#admin-form #address").val().trim();
    var city = $("#admin-form #city").val().trim();
    var zip = $("#admin-form #zip").val().trim();
    var country = $("#admin-form #country").val().trim();
    var industry = $("#admin-form #industry").val().trim();
    var category = $("#admin-form #category").val().trim();
    var vat = $("#admin-form #vat").val().trim();
    var email = $("#admin-form #email").val().trim();

    // Address validation
    if (address === "") {
      Utils.notifyInput($("#admin-form #address"), "Address is required.");
      return false;
    }

    // City validation
    if (city === "") {
      Utils.notifyInput($("#admin-form #city"), "City is required.");
      return false;
    }

    // Zip code validation
    if (zip === "") {
      Utils.notifyInput($("#admin-form #zip"), "Zip Code is required.");
      return false;
    }

    // Country validation
    if (country === "") {
      Utils.notifyInput($("#admin-form #country"), "Country is required.");
      return false;
    }

    // Industry validation
    if (industry === "") {
      Utils.notifyInput($("#admin-form #industry"), "Industry is required.");
      return false;
    }

    // Category validation
    if (category === "") {
      Utils.notifyInput(
        $("#admin-form #category"),
        "Product Category is required."
      );
      return false;
    }

    // VAT Number validation
    if (vat === "") {
      Utils.notifyInput($("#admin-form #vat"), "VAT Number is required.");
      return false;
    }

    // Email validation
    if (email === "") {
      Utils.notifyInput($("#admin-form #email"), "Email Address is required.");
      return false;
    }

    // Validate email format using regex
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      Utils.notifyInput($("#admin-form #email"), "Invalid Email Address.");
      return false;
    }

    if ($("#admin-form #ingrednumber").val().trim() == "") {
      Utils.notifyInput(
        $("#admin-form #ingrednumber"),
        "No Ingredients number specified"
      );
      return false;
    }
    if ($("#admin-form #prodnumber").val().trim() == "") {
      Utils.notifyInput(
        $("#admin-form #prodnumber"),
        "No Products number specified"
      );
      return false;
    }

    return true;
  },

  sendModifyAdminRequest: function (doc) {
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveFacility",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      Utils.notify("success", "Changes were submitted");
      $("#adminModal").prop("submit", 1);
      $("#adminModal").modal("hide");
    });
  },

  onSave: function () {
    FP.clearAlerts();
    if (!FP.validateForm()) {
      return;
    }
    var doc = FP.createDocFromInputData();
    FP.sendModifyAdminRequest(doc);
  },
};

// JavaScript to populate the "Current URL" field when the modal is opened
$(document).ready(function () {
  $("#logout").click(function () {
    $.ajax({
      type: "POST",
      url: "ajax/ajaxHandler.php",
      cache: false,
      data: { uid: 0, rtype: "logout" },
      success: function (data) {
        var response = JSON.parse(data);
        if (response.status == 1) document.location.href = "";
        else {
          alert(response.data);
        }
      },
    });
  });
  $("#btnReportIssueForm").on("click", function () {
    var texts = [];

    $("#uladdoc133 li").each(function () {
      var spanText = $(this).find("span:first").text();
      texts.push(spanText);
    });

    var attachments = texts.join(", ");
    var formData = {
      uid: 0,
      rtype: "createTicket",
      issueType: $("#reportIssueModal #issueType").val(),
      issueDescription: $("#reportIssueModal #issueDescription").val(),
      currentURL: $("#reportIssueModal #currentURL").val(),
      attachments: attachments,
    };

    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: formData,
      success: function (response) {
        var jsonResponse = JSON.parse(response);
        if (jsonResponse.data.errors.length > 0) {
          $("#reportIssueErrors")
            .show()
            .html("<ul>" + jsonResponse.data.errors + "</ul>");
        } else {
          //alert('Issue reported successfully with ID: ' + jsonResponse.data.id);
          $("#reportIssueModal").modal("hide");
          $("#reportIssueForm")[0].reset();
          alert(
            "Thank You! Your issue has been reported successfully. We will update you as soon as possible."
          );
        }
      },
    });
  });

  $("#fileupload133")
    .fileupload({
      url: "fileupload/ProcessFiles.php",
      dataType: "json",
      dropZone: $("#dropzone133"),
      add: function (e, data) {
        data.formData = {
          folderType: $(this).attr("foldertype"),
          infoType: $(this).attr("infotype"),
          subFolder: $(this).attr("subfolder"),
          client: $("#reportIssueModal #clientname").val(),
        };
        var goUpload = true;
        var uploadFile = data.files[0];
        if (!/\.(jpg|jpeg|png|gif|xls|xlsx|pdf)$/i.test(uploadFile.name)) {
          alert(
            "You can upload JPG, JPEG, PNG, GIF, PDF, or Excel file(s) only"
          );
          goUpload = false; // Prevent form submission
        }
        if (goUpload == true) {
          data.submit();
        }
      },
      start: function (e) {
        $(this).parent().siblings(".loader").css("display", "block").show();
      },
      fail: function (e, data) {
        // kill all progress bars awaiting for showing
        $(this).parent().siblings(".loader").hide();
        alert("Error uploading file (" + data.errorThrown + ")");
      },
      done: function (e, data) {
        // hide loader and add new li with new file info
        $(this).parent().siblings(".loader").hide();
        $.each(data.result.files, function (index, file) {
          var jsonstring =
            '{"name":"' +
            file.name +
            '","glink":"' +
            file.googleDriveUrl +
            '","hostpath":"' +
            file.url +
            '","hostUrl":"' +
            file.hostUrl +
            '"}';
          var ell;
          /*
        if (file.name.length > 35) ell = file.name.substr(0, 30) + '...';
        else ell = file.name;
        */
          ell = file.name;
          var filename = $(
            '<li class="uploaded-file-name" originalname="' +
              encodeURI(jsonstring) +
              '"></li>'
          );
          filename.append($("<span>", { text: ell }));
          filename.append(
            $(
              '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                "fileid=" +
                file.googleDriveId +
                " hostpath=" +
                encodeURI(file.url) +
                ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
            ).bind("click", function (e) {
              delDocClick(e);
            })
          );
          // add li to the list of the appropriate ul - class from folderType
          $("#ul" + file.folderType).append(filename);
        });
      },
    })
    .prop("disabled", !$.support.fileInput)
    .parent()
    .addClass($.support.fileInput ? undefined : "disabled");

  $("#reportIssueModal").on("shown.bs.modal", function () {
    var currentURL = window.location.href;
    $("#reportIssueModal #currentURL").val(currentURL);
    $("#reportIssueModal #reportIssueErrors").hide().html("");
    $("#uladdoc133").html("");
  });

  // Function to fetch unviewed ticket count and update the badge
  function updateNewTicketsBadge() {
    $.post(
      "ajax/ajaxHandler.php",
      { uid: 0, rtype: "getNewTicketsCount" },
      function (data) {
        if (data) {
          var response = JSON.parse(data);
          var unviewedCount = response.unviewed_count;

          if (unviewedCount > 0) {
            $("#unviewedBadge").text(unviewedCount).show();
          } else {
            $("#unviewedBadge").hide();
          }
        }
      }
    );
  }

  // Call the function on page load
  //updateNewTicketsBadge();
  //setInterval(updateNewTicketsBadge, 2500);

  $("#fileupload233")
    .fileupload({
      url: "fileupload/ProcessFiles.php",
      dataType: "json",
      dropZone: $("#dropzone233"),
      add: function (e, data) {
        data.formData = {
          folderType: $(this).attr("foldertype"),
          infoType: $(this).attr("infotype"),
          subFolder: $(this).attr("subfolder"),
          client: $("#customerServiceModal #clientname").val(),
        };
        var goUpload = true;
        var uploadFile = data.files[0];
        if (!/\.(jpg|jpeg|png|gif|xls|xlsx|pdf)$/i.test(uploadFile.name)) {
          alert(
            "You can upload JPG, JPEG, PNG, GIF, PDF, or Excel file(s) only"
          );
          goUpload = false; // Prevent form submission
        }
        if (goUpload == true) {
          data.submit();
        }
      },
      start: function (e) {
        $(this).parent().siblings(".loader").css("display", "block").show();
      },
      fail: function (e, data) {
        // kill all progress bars awaiting for showing
        $(this).parent().siblings(".loader").hide();
        alert("Error uploading file (" + data.errorThrown + ")");
      },
      done: function (e, data) {
        // hide loader and add new li with new file info
        $(this).parent().siblings(".loader").hide();
        $.each(data.result.files, function (index, file) {
          var jsonstring =
            '{"name":"' +
            file.name +
            '","glink":"' +
            file.googleDriveUrl +
            '","hostpath":"' +
            file.url +
            '","hostUrl":"' +
            file.hostUrl +
            '"}';
          var ell;
          /*
        if (file.name.length > 35) ell = file.name.substr(0, 30) + '...';
        else ell = file.name;
        */
          ell = file.name;
          var filename = $(
            '<li class="uploaded-file-name" originalname="' +
              encodeURI(jsonstring) +
              '"></li>'
          );
          filename.append($("<span>", { text: ell }));
          filename.append(
            $(
              '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                "fileid=" +
                file.googleDriveId +
                " hostpath=" +
                encodeURI(file.url) +
                ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
            ).bind("click", function (e) {
              delDocClick(e);
            })
          );
          // add li to the list of the appropriate ul - class from folderType
          $("#ul" + file.folderType).append(filename);
        });
      },
    })
    .prop("disabled", !$.support.fileInput)
    .parent()
    .addClass($.support.fileInput ? undefined : "disabled");

  $("#customerServiceModal").on("shown.bs.modal", function () {
    var currentURL = window.location.href;

    $("#currentURL").val(currentURL);
    $("#customerServiceErrors").hide().html("");
    $("#uladdoc233").html("");
  });

  $("#btnCustomerServiceForm").on("click", function () {
    var texts = [];

    $("#uladdoc233 li").each(function () {
      var spanText = $(this).find("span:first").text();
      texts.push(spanText);
    });

    var attachments = texts.join(", ");
    var formData = {
      uid: 0,
      rtype: "createCustomerService",
      requestType: $("#customerServiceModal #requestType").val(),
      requestDescription: $("#customerServiceModal #requestDescription").val(),
      currentURL: $("#customerServiceModal #currentURL").val(),
      attachments: attachments,
      user_id: $("#tidclient").length ? $("#tidclient").val() : null,
    };

    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: formData,
      success: function (response) {
        var jsonResponse = JSON.parse(response);
        if (jsonResponse.data.errors.length > 0) {
          $("#customerServiceErrors")
            .show()
            .html("<ul>" + jsonResponse.data.errors + "</ul>");
        } else {
          //alert('Issue reported successfully with ID: ' + jsonResponse.data.id);
          $("#customerServiceModal").modal("hide");
          $("#customerServiceForm")[0].reset();
          alert(
            "Thank You! Your request has been submitted successfully. We will update you as soon as possible."
          );
        }
      },
    });
  });

  $(".customer-service-button, .report-issue-button").tooltipster({
    trigger: "hover",
    theme: "tooltipster-light", // You can choose a different theme or customize it
    delay: 200, // Delay before the tooltip appears
    animation: "fade", // Animation type
    contentAsHTML: true, // Allows HTML content inside the tooltip
  });
});
