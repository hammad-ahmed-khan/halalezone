$(document).ready(function () {
  var table_report = $("#table_report").DataTable({
    paging: false,
    lengthChange: false,
    searching: false,
    ordering: false,
    info: false,
    responsive: true,
    autoWidth: false,
    pageLength: 1000,
    //"pagingType": "full_numbers",
    processing: true,
    serverSide: true,
    drawCallback: function () {
      var json = table_report.ajax.json();
      $("#totalMajor").html(json.counts.Major);
      $("#totalMinor").html(json.counts.Minor);
      $("#totalOBS").html(json.counts.OBS);
      $("#totalConfirmed").html(json.counts.Confirmed);
      $("#totalNotConfirmed").html(json.counts.NotConfirmed);

      // Call the initFileUploader function here
      initFileUploader({
        fileUploadSelector: "#table_report .fileupload",
        dropzoneSelector: "#table_report .dropzone",
        progressSelector: "#table_report .progress",
        onAdd: function (e, data) {
          data.formData = {
            folderType: $(this).attr("folderType"), // for certificates
            infoType: $(this).attr("infoType"),
            client: $("#app-clientid").data("clientname"),
            idclient: $(this).attr("idclient"),
            idapp: $(this).attr("idapp"),
            id: $(this).attr("id"),
          };
          data.submit();
        },
        afterSuccess: function (e, file) {
          var table = $("#table_report").DataTable();
          table.ajax.reload(null, false);
        },
      });
    },

    ajax: {
      url: "../ajax/getAuditReport.php",
      type: "POST",
      async: true,
      data: function (data) {
        data.s = $("#s").val();
        data.idclient = $("#app-clientid").val();
        data.idapp = $("#idapp").val();
      },
    },
    columns: [
      // { "data": "cat_name"},
      { data: "Type" },
      { data: "Deviation" },
      { data: "Reference" },
      { data: "RootCause" },
      { data: "Measure" },
      { data: "Deadline" },
      { data: "Documents", sClass: "text-center" },
      { data: "Status", sClass: "text-center" },
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
    createdRow: function (row, data, dataIndex) {
      if (data["r_color"] != "") {
        $(row).css({ "background-color": data["r_color"] });
      }
    },
  });

  //$("#Measure").chosen({ width:'100%' });
  $("#Deviation").chosen({ width: "100%" });

  var date = moment().millisecond(0).second(0).minute(0).hour(0);
  $("#Deadline").datetimepicker({ format: "DD/MM/YYYY" });

  $("#report #btn-preview").on("click", function (e) {
    idclient = $("#app-clientid").val();
    idapp = $("#idapp").val();
    $("#report #previewIdClient").val(idclient);
    $("#report #previewIdApp").val(idapp);
    $("#report #frmPreview").submit();
    return false;
  });

  $("#report #btn-upload").on("click", function () {
    $(".modal-title.title-add").show();
    $(".modal-title.title-edit").hide();
    $("#modaldeviation #errors").hide();
    $("#modaldeviation #Type").val("");
    $("#modaldeviation #Deviation").val("");
    $("#modaldeviation #NewDeviation").val("");
    $("#modaldeviation #Reference").val("");
    $("#modaldeviation").modal("show");
    return false;
  });

  $(document).on("click", ".btn-measure", function () {
    var doc = {};
    doc.id = $(this).attr("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "getDeviationData",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      deviation = response.data.deviation;
      var Type = deviation.Type;
      var Deviation = deviation.Deviation;
      var Reference = deviation.Reference;
      var RootCause = deviation.RootCause;
      var Measure = deviation.Measure;
      var Deadline = deviation.Deadline;
      $("#modalmeasure .modal-title.title-add").show();
      $("#modalmeasure .modal-title.title-edit").hide();
      $("#modalmeasure #errors").hide();
      $("#modalmeasure #ID").val(doc.id);
      $("#modalmeasure #lblType").html(Type);
      $("#modalmeasure #lblDeviation").html(Deviation);
      $("#modalmeasure #lblReference").html(Reference);
      $("#modalmeasure #RootCause").val(RootCause);
      $("#modalmeasure #Measure").val(Measure);
      $("#modalmeasure #Deadline").val(Deadline);
      $("#modalmeasure").modal("show");
      return false;
    });
    return false;
  });

  $(document).on("click", "#report .delete-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var id = $(this).attr("id");
      var document = $(this).attr("document");
      var doc = {
        id: id,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
        document: document,
      };
      $.post("ajax/ajaxHandler.php", {
        rtype: "deleteDeviationDoc",
        uid: 0,
        data: doc,
      }).done(function (data) {
        var response = JSON.parse(data);
        var table = $("#table_report").DataTable();
        table.ajax.reload(null, false);
        return false;
      });
    }
    return false;
  });

  $(document).on("click", "#report .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/report.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_report").DataTable();
            table.ajax.reload(null, false);
            //$("#errors").hide();
            return;
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {},
      });
    }
    return false;
  });

  $(document).on("change", ".confirm-checkbox", function () {
    var doc = {};
    doc.id = $(this).attr("data-id");
    doc.Status = $(this).prop("checked") ? "1" : "0"; // Checked = Confirmed, Unchecked = Not Confirmed

    var isChecked = $(this).prop("checked");

    $("#implement_" + doc.id)
      .closest("div")
      .toggle(isChecked);

    $.post("ajax/ajaxHandler.php", {
      rtype: "updateDeviationStatus",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      var response = JSON.parse(data);
      if (response.data.all == "1") {
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        //window.location.href =
        //window.location.href + separator + "_n=" + randomNumber;
        return;
      }
      var table = $("#table_report").DataTable();
      table.ajax.reload(null, false);
    });
  });

  $(document).on("change", ".implement-checkbox", function () {
    var doc = {};
    doc.id = $(this).attr("data-id");
    doc.Implemented = $(this).prop("checked") ? "1" : "0"; // Checked = Confirmed, Unchecked = Not Confirmed

    $.post("ajax/ajaxHandler.php", {
      rtype: "updateImplementationStatus",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      var response = JSON.parse(data);
      if (response.data.all == "1") {
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        //window.location.href =
        //window.location.href + separator + "_n=" + randomNumber;
        return;
      }
      var table = $("#table_report").DataTable();
      table.ajax.reload(null, false);
    });
  });

  /*
  $(document).on("click", ".btn-confirm", function () {
    var doc = {};
    doc.id = $(this).attr("id");
    doc.Status = "1";
    $.post("ajax/ajaxHandler.php", {
      rtype: "updateDeviationStatus",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      var table = $("#table_report").DataTable();
      table.ajax.reload(null, false);
      return false;
    });
    return false;
  });

  $(document).on("click", ".btn-unconfirm", function () {
    var doc = {};
    doc.id = $(this).attr("id");
    doc.Status = "0";
    $.post("ajax/ajaxHandler.php", {
      rtype: "updateDeviationStatus",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      var table = $("#table_report").DataTable();
      table.ajax.reload(null, false);
      return false;
    });
    return false;
  });
*/

  $(document).on("click", "#btn-send-report", function () {
    if (confirm("Are you sure you want to send?")) {
      var doc = {};
      doc.idclient = $("#app-clientid").val();
      doc.idapp = $("#idapp").val();
      $.post("ajax/ajaxHandler.php", {
        rtype: "sendAuditReport",
        uid: 0,
        data: doc,
      }).done(function (data) {
        var response = JSON.parse(data);
        $("div#report").notify("Report sent successfully.", {
          position: "top right",
          className: "success",
        });
        $("#last_report_sent").html(
          "Last Sent: " + response.data.last_report_sent
        );
        return false;
      });
    }
    return false;
  });

  $(document).on("click", ".btn-deldev", function () {
    if (confirm("Are you sure you want to delete?")) {
      var doc = {};
      doc.id = $(this).attr("id");
      doc.idclient = $("#app-clientid").val();
      doc.idapp = $("#idapp").val();
      $.post("ajax/ajaxHandler.php", {
        rtype: "deleteDeviation",
        uid: 0,
        data: doc,
      }).done(function (data) {
        var response = JSON.parse(data);
        var table = $("#table_report").DataTable();
        table.ajax.reload(null, false);
        return false;
      });
    }
    return false;
  });

  $(document).on("click", "#btnsave-measure", function () {
    //if (!$("#btnsave-tenure").hasClass("disabled")) {
    var doc = {};
    doc.id = $("#modalmeasure #ID").val();
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.RootCause = $("#modalmeasure #RootCause").val();
    doc.Measure = $("#modalmeasure #Measure").val();
    doc.Deadline = $("#modalmeasure #Deadline").val();

    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: { rtype: "saveMeasureData", uid: 0, data: doc },
      success: function (data, textStatus, jqXHR) {
        var response = JSON.parse(data);
        if (response.data.errors) {
          $("#modalmeasure #errors")
            .html("<ul>" + response.data.errors + "</ul>")
            .show();
          return;
        }
        var table = $("#table_report").DataTable();
        table.ajax.reload(null, false);
        $("#modalmeasure").modal("toggle");
        $("#modalmeasure #errors").hide();
      },
      error: function (jqXHR, textStatus, errorThrown) {},
    });
    return false;
  });

  $("#Deviation").on("change", function () {
    var Deviation = $(this).val();
    if (Deviation == "addNewDeviation") {
      $("#NewDeviation").removeClass("hidden");
      setTimeout(function () {
        $("#NewDeviation").focus();
      }, 250);
    } else {
      $("#NewDeviation").addClass("hidden");
    }
  });

  $("#report #btn-submit").on("click", function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.Type = $("#Type").val();
    doc.Deviation = $("#Deviation").val();
    doc.NewDeviation = $("#NewDeviation").val();
    doc.Reference = $("#Reference").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveAuditReport",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.data.errors != "") {
        $("#report #errors")
          .html("<ul>" + response.data.errors + "</ul>")
          .show();
        return;
      }
      $("#report #errors").html("").hide();
      $("div#report").notify("Data saved successfully.", {
        position: "top right",
        className: "success",
      });
      var table_report = $("#table_report").DataTable();
      table_report.ajax.reload(null, false);
    });
    return false;
  });

  $("#report #btn-deviation").on("click", function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.Deviation = $("#SaveDeviation").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveDeviation",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.data.errors != "") {
        $("#modaldeviation #errors")
          .html("<ul>" + response.data.errors + "</ul>")
          .show();
        return;
      }
      $("#modaldeviation #errors").html("").hide();
      var id = response.data.id;
      //$("div#report").notify( "Data saved successfully.", { position:"top right", className: "success" });
      getDeviations();

      $("#Deviation").val(id);
      //$("#Deviation").trigger("chosen:updated");

      $("#modaldeviation").modal("hide");
      //$("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
    return false;
  });

  $("#Deviation").on("change", function (e) {
    $("#report #errors").html("").hide();
  });
  $("#Reference").on("keyup", function (e) {
    $("#report #errors").html("").hide();
  });

  $("#Deadline").datetimepicker({ format: "DD/MM/YYYY" });

  $(document).on("click", "#report #btnsave-settings", function () {
    $.post(
      "ajax/ajaxHandler.php",
      $("#frmAuditReportSettings").serialize() +
        "&idclient=" +
        $("#app-clientid").val() +
        "&idapp=" +
        $("#idapp").val() +
        "&rtype=saveAuditReportSettings&uid=0"
    ).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        $("#dates #errors").html(response.statusDescription).show();
        return;
      }
      $("#dates #errors").html("").hide();
      $("#modalauditreportsettings").modal("hide");
      //$("div#dates").notify( "Settings saved successfully.", { position:"top right", className: "success" });
      //$("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
    return false;
  });

  $(document).on("click", "#report .btn-upload", function () {
    $.fileup("docs-pdf", "remove", "*");
    $("#docs #btn-submit").prop("disabled", false);
    $("#modaldocs").modal("show");
    $("#modaldocs .modal-title.title-add").show();
    $("#errors").hide();
    return false;
  });
});
