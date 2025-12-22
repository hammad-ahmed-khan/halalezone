$(document).ready(function () {
  var table_extension = $("#table_extension").DataTable({
    paging: true,
    lengthChange: false,
    searching: false,
    ordering: false,
    info: true,
    responsive: true,
    autoWidth: false,
    pageLength: 25,
    pagingType: "full_numbers",
    processing: true,
    serverSide: true,
    createdRow: function (row, data, dataIndex) {
      if (data["deleted"] == "1") {
        $(row).addClass("strikeout");
      }
    },
    ajax: {
      url: "../ajax/getDocs.php",
      type: "POST",
      async: true,
      data: function (data) {
        data.s = $("#s").val();
        //data.actions="delete";
        data.idclient = $("#app-clientid").val();
        data.idapp = $("#idapp").val();
        data.category = "extension";
        //data.deleted=$("#extension #filter-actions-deleted").is(":checked") ? 1 : 0;
      },
    },
    columns: [
      {
        className: "details-control",
        orderable: false,
        data: null,
        defaultContent: "",
        render: function (data, type, row) {
          if (row.hasChildren > 0)
            //Check column value "Yes"
            return "<div></div>"; //Empty cell content
          else return "";
        },
      },
      { data: "Title" },
      { data: "FileName" },
      { data: "UserName" },
      { data: "Uploaded" },
      //{ "data": "Signature"},
      //{ "data": "Comments"},
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
  });

  $(document).on("click", "#modalextension #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#extension_filename").val() == "") {
      $("#modalextension #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("extension-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#extension .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/extension.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_extension").DataTable();
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

  $(document).on("click", "#modalextension #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#extension_filename").val() == "") {
      $("#modalextension #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("extension-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#extension #btn-upload", function () {
    $.fileup("extension-pdf", "remove", "*");
    $("#extension #btn-submit").prop("disabled", false);
    $("#modalextension").modal("show");
    $("#modalextension .modal-title.title-add").show();
    $("#modalextension #errors").hide();
    $("#modalextension #Title").val("");
    $("#modalextension #Comments").val("");
    $("#modalextension #Signautre1").prop("checked", true);
    return false;
  });

  $(document).on("click", "#extension .btn-sign", function () {
    var ID = $(this).attr("id");
    var Title = $(this).data("title");
    $.fileup("extension-pdf", "remove", "*");
    $("#extension #btn-submit").prop("disabled", false);
    $("#modalsign").modal("show");
    $("#modalsign .modal-title.title-add").show();
    $("#modalsign #errors").hide();
    $("#modalsign #ID").val(ID);
    $("#modalsign #Title").val(Title);
    $("#modalsign #Label").html(Title);
    return false;
  });

  $(document).on("click", "#modalsign #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#extension_signed_filename").val() == "") {
      $("#modalsign #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("extension-pdf", "upload", "*");
    }
    return false;
  });

  $("#extension #filter-actions-deleted").click(function (e) {
    var table = $("#table_extension").DataTable();
    table.ajax.reload(null, false);
  });

  $("#extension #btn-search").click(function (e) {
    var table = $("#table_extension").DataTable();
    table.ajax.reload(null, false);
  });
  if ($("#extension-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/extension.php",
      inputID: "extension-pdf",
      queueID: "extension-pdf-queue",
      dropzoneID: "extension-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Signature: jQuery("#modalextension input[name=Signature]:checked"),
        Title: jQuery("#modalextension #Title"),
        Comments: jQuery("#modalextension #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalextension #errors").hide();
        $("#extension_filename").val(file.name);
      },
      onRemove: function (total, file_number, file) {
        $("#extension_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_extension").DataTable();
        table.ajax.reload(null, false);
        $("#modalextension").modal("hide");
      },
      onError: function (event, file, file_number, response) {
        $.fileup("extension-pdf", "remove", "*");
        $("#modalextension #btn-submit").prop("disabled", false);
        $("#modalextension #errors").show().html(response);
      },
    })
      .dragEnter(function (event) {
        $(event.target).addClass("over");
      })
      .dragLeave(function (event) {
        $(event.target).removeClass("over");
      })
      .dragEnd(function (event) {
        $(event.target).removeClass("over");
      });
  }
});

$(document).ready(function () {
  $(document).on("keyup", "#newCertificationModal input", function () {
    // Clear error messages
    $("#newCertificationModal .errors").empty();
  });

  $(".btn-start-cc").click(function () {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.cycleName = $("#newCertificationModal #cycleName").val();
    doc.startDate = $("#newCertificationModal #certStartDate").val();
    doc.endDate = $("#newCertificationModal #certEndDate").val();
    $.ajax({
      type: "POST",
      url: "ajax/ajaxHandler.php",
      data: { rtype: "startNewCertCycle", uid: 0, data: doc },
      async: false,
      dataType: "json",
      success: function (response) {
        if (response.status == "0") {
          var errors = response.statusDescription;
          var html = "";
          $.each(errors, function (index, error) {
            html += error + "<br/>";
          });

          $("#newCertificationModal .errors")
            .show()
            .html('<div class="alert alert-danger">' + html + "</div>");
        } else {
          window.location.reload();
        }
      },
    });
    return false;
  });

  $("#newCertificationModal").on("show.bs.modal", function (event) {
    // Get the client name from the desired input field
    var clientName = $("#app-clientid option:selected").text();
    // Set the value of the "Client" field in the modal form
    $(".selClientName").html(clientName);
    $("#newCertificationModal .errors").empty();
  });

  $("#btn-start-cc").click(function () {
    if (
      confirm(
        "Are you sure you want to proceed? This action will conclude the current certification cycle and initiate a new one."
      )
    ) {
      $("#newCertificationModal").modal("show");
    }
    return false;
  });
});
