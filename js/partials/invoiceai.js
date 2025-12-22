$(document).ready(function () {
  var table_invoiceai = $("#table_invoiceai").DataTable({
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
        data.category = "invoiceai";
        //data.deleted=$("#invoiceai #filter-actions-deleted").is(":checked") ? 1 : 0;
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

  $(document).on("click", "#modalinvoiceai #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#invoiceai_filename").val() == "") {
      $("#modalinvoiceai #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoiceai-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#invoiceai .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/invoiceai.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_invoiceai").DataTable();
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

  $(document).on("click", "#modalinvoiceai #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#invoiceai_filename").val() == "") {
      $("#modalinvoiceai #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoiceai-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#invoiceai #btn-upload", function () {
    $.fileup("invoiceai-pdf", "remove", "*");
    $("#invoiceai #btn-submit").prop("disabled", false);
    $("#modalinvoiceai").modal("show");
    $("#modalinvoiceai .modal-title.title-add").show();
    $("#modalinvoiceai #errors").hide();
    $("#modalinvoiceai #Title").val("");
    $("#modalinvoiceai #Comments").val("");
    $("#modalinvoiceai #Signautre1").prop("checked", true);
    return false;
  });

  $(document).on("click", "#invoiceai .btn-sign", function () {
    var ID = $(this).attr("id");
    var Title = $(this).data("title");
    $.fileup("invoiceai-pdf", "remove", "*");
    $("#invoiceai #btn-submit").prop("disabled", false);
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
    if ($("#invoiceai_signed_filename").val() == "") {
      $("#modalsign #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoiceai-pdf", "upload", "*");
    }
    return false;
  });

  $("#invoiceai #filter-actions-deleted").click(function (e) {
    var table = $("#table_invoiceai").DataTable();
    table.ajax.reload(null, false);
  });

  $("#invoiceai #btn-search").click(function (e) {
    var table = $("#table_invoiceai").DataTable();
    table.ajax.reload(null, false);
  });

  if ($("#invoiceai-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/invoiceai.php",
      inputID: "invoiceai-pdf",
      queueID: "invoiceai-pdf-queue",
      dropzoneID: "invoiceai-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Signature: jQuery("#modalinvoiceai input[name=Signature]:checked"),
        Title: jQuery("#modalinvoiceai #Title"),
        Comments: jQuery("#modalinvoiceai #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalinvoiceai #errors").hide();
        $("#invoiceai_filename").val(file.name);
      },
      onRemove: function (total, file_number, file) {
        $("#invoiceai_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_invoiceai").DataTable();
        table.ajax.reload(null, false);
        $("#modalinvoiceai").modal("hide");
      },
      onError: function (event, file, file_number, response) {
        $.fileup("invoiceai-pdf", "remove", "*");
        $("#modalinvoiceai #btn-submit").prop("disabled", false);
        $("#modalinvoiceai #errors").show().html(response);
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        window.location.href =
          window.location.href + separator + "_n=" + randomNumber;
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
