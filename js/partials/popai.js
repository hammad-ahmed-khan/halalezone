$(document).ready(function () {
  var table_popai = $("#table_popai").DataTable({
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
        data.category = "popai";
        //data.deleted=$("#popai #filter-actions-deleted").is(":checked") ? 1 : 0;
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

  $(document).on("click", "#modalpopai #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#popai_filename").val() == "") {
      $("#modalpopai #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("popai-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#popai .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/popai.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_popai").DataTable();
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

  $(document).on("click", "#modalpopai #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#popai_filename").val() == "") {
      $("#modalpopai #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("popai-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#popai #btn-upload", function () {
    $.fileup("popai-pdf", "remove", "*");
    $("#popai #btn-submit").prop("disabled", false);
    $("#modalpopai").modal("show");
    $("#modalpopai .modal-title.title-add").show();
    $("#modalpopai #errors").hide();
    $("#modalpopai #Title").val("");
    $("#modalpopai #Comments").val("");
    $("#modalpopai #Signautre1").prop("checked", true);
    return false;
  });

  $(document).on("click", "#popai .btn-sign", function () {
    var ID = $(this).attr("id");
    var Title = $(this).data("title");
    $.fileup("popai-pdf", "remove", "*");
    $("#popai #btn-submit").prop("disabled", false);
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
    if ($("#popai_signed_filename").val() == "") {
      $("#modalsign #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("popai-pdf", "upload", "*");
    }
    return false;
  });

  $("#popai #filter-actions-deleted").click(function (e) {
    var table = $("#table_popai").DataTable();
    table.ajax.reload(null, false);
  });

  $("#popai #btn-search").click(function (e) {
    var table = $("#table_popai").DataTable();
    table.ajax.reload(null, false);
  });

  if ($("#popai-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/popai.php",
      inputID: "popai-pdf",
      queueID: "popai-pdf-queue",
      dropzoneID: "popai-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Signature: jQuery("#modalpopai input[name=Signature]:checked"),
        Title: jQuery("#modalpopai #Title"),
        Comments: jQuery("#modalpopai #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalpopai #errors").hide();
        $("#popai_filename").val(file.name);
      },
      onRemove: function (total, file_number, file) {
        $("#popai_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_popai").DataTable();
        table.ajax.reload(null, false);
        $("#modalpopai").modal("hide");
      },
      onError: function (event, file, file_number, response) {
        $.fileup("popai-pdf", "remove", "*");
        $("#modalpopai #btn-submit").prop("disabled", false);
        $("#modalpopai #errors").show().html(response);
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
