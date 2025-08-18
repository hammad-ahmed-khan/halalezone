$(document).ready(function () {
  var table_app = $("#table_app").DataTable({
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
        data.category = "app";
        data.deleted = $("#app #filter-actions-deleted").is(":checked") ? 1 : 0;
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
  $(document).on("click", "#app #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#app_filename").val() == "") {
      $("#modalapp #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("app-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#app #btn-upload", function () {
    $.fileup("app-pdf", "remove", "*");
    $("#app #btn-submit").prop("disabled", false);
    $("#modalapp").modal("show");
    $("#modalapp .modal-title.title-add").show();
    $("#errors").hide();
    $("#modalapp #Comments").val("");
    return false;
  });

  $("#app #filter-actions-deleted").click(function (e) {
    var table = $("#table_app").DataTable();
    table.ajax.reload(null, false);
  });

  $("#app #btn-search").click(function (e) {
    var table = $("#table_app").DataTable();
    table.ajax.reload(null, false);
  });

  $(document).on("click", "#app .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/app.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_app").DataTable();
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

  if ($("#app-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/app.php",
      inputID: "app-pdf",
      queueID: "app-pdf-queue",
      dropzoneID: "app-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
      },
      //filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalapp #errors").hide();
        $("#app_filename").val(file.name);
        $.blockUI();
      },
      onRemove: function (total, file_number, file) {
        $("#app_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_app").DataTable();
        table.ajax.reload(null, false);
        $("#modalapp").modal("hide");
        var separator = (window.location.href.indexOf('?') > -1) ? '&' : '?';
        var randomNumber = Math. floor(Math. random() * 100) + 1;
        window.location.href = window.location.href + separator + '_n='+randomNumber;
        //changeAppState("declarations");
      },
      onError: function (event, file, file_number, response) {
        $("#app #btn-submit").prop("disabled", false);
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
