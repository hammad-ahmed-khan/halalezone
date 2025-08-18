$(document).ready(function () {
  var table_soffer = $("#table_soffer").DataTable({
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
        data.idclient = $("#app-clientid").val();
        data.idapp = $("#idapp").val();
        data.category = "soffer";
        data.deleted = $("#soffer #filter-actions-deleted").is(":checked")
          ? 1
          : 0;
      },
    },
    columns: [
      { data: "FileName" },
      { data: "UserName" },
      { data: "Uploaded" },
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

  $(document).on("click", "#modalsoffer #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#soffer_filename").val() == "") {
      $("#modalsoffer #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("soffer-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#soffer .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/soffer.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_soffer").DataTable();
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

  $(document).on("click", "#modalsend #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#soffer_filename").val() == "") {
      $("#modalsoffer #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("soffer-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#soffer #btn-send", function () {
    var table = $("#table_soffer").DataTable();
    if (table.rows().count() < 1) {
      //alert("No record found, signed offer not uploaded.");
      //return false;
    }

    if (confirm("Are you sure you want to send?")) {
      var doc = {};
      doc.idclient = $("#app-clientid").val();
      doc.idapp = $("#idapp").val();
      $.ajax({
        type: "POST",
        url: "ajax/ajaxHandler.php",
        data: { rtype: "sendClientLogin", uid: 0, data: doc },
        beforeSend: function () {
          jQuery.blockUI({
            css: {
              border: "none",
              padding: "15px",
              backgroundColor: "#000",
              "-webkit-border-radius": "10px",
              "-moz-border-radius": "10px",
              opacity: 0.5,
              color: "#fff",
            },
            message: "Please wait...",
          });
        },
        success: function (data) {
          jQuery.unblockUI();
          var response = JSON.parse(data);
          if (response.data.errors) {
            $("#soffer #errors").html(response.data.errors).show();
            return;
          }

          // var table_declarations = $('#table_declarations').DataTable();
          // table_declarations.ajax.reload( null, false );
          $("#last_login_sent").html(
            "Last Login Sent: " + response.data.last_login_sent
          );
          $("div#offer").notify("Login and password have been sent.", {
            position: "top right",
            className: "success",
          });
        },
      });
    }
    return false;
  });

  $(document).on("click", "#soffer #btn-upload", function () {
    $.fileup("soffer-pdf", "remove", "*");
    $("#modalsoffer #btn-submit").prop("disabled", false);
    $("#modalsoffer").modal("show");
    $("#modalsoffer .modal-title.title-add").show();
    $("#errors").hide();
    $("#modalsoffer #Comments").val("");
    return false;
  });

  $("#soffer #filter-actions-deleted").click(function (e) {
    var table = $("#table_soffer").DataTable();
    table.ajax.reload(null, false);
  });

  $("#soffer #btn-search").click(function (e) {
    var table = $("#table_soffer").DataTable();
    table.ajax.reload(null, false);
  });

  if ($("#soffer-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/soffer.php",
      inputID: "soffer-pdf",
      queueID: "soffer-pdf-queue",
      dropzoneID: "soffer-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Comments: jQuery("#modalsoffer #Comments"),
      },
      //filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalsoffer #errors").hide();
        $("#soffer_filename").val(file.name);
        $.blockUI();
      },
      onRemove: function (total, file_number, file) {
        $("#soffer_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_soffer").DataTable();
        table.ajax.reload(null, false);
        $("#modalsoffer").modal("hide");
        //changeAppState('app');
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        window.location.href =
          window.location.href + separator + "_n=" + randomNumber;
      },
      onError: function (event, file, file_number, response) {
        $("#modalsoffer #btn-submit").prop("disabled", false);
        $("#errors").show().html(response);
        $.unblockUI();
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
