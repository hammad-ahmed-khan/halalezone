$(document).ready(function () {
  var table_invoice = $("#table_invoice").DataTable({
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
        data.category = "invoice";
        //data.deleted=$("#invoice #filter-actions-deleted").is(":checked") ? 1 : 0;
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

  $(document).on("click", "#modalinvoice #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#invoice_filename").val() == "") {
      $("#modalinvoice #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoice-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#invoice .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/invoice.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_invoice").DataTable();
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

  $(document).on("click", "#modalinvoice #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#invoice_filename").val() == "") {
      $("#modalinvoice #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoice-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#invoice #btn-upload", function () {
    $.fileup("invoice-pdf", "remove", "*");
    $("#invoice #btn-submit").prop("disabled", false);
    $("#modalinvoice").modal("show");
    $("#modalinvoice .modal-title.title-add").show();
    $("#modalinvoice #errors").hide();
    $("#modalinvoice #Title").val("");
    $("#modalinvoice #Comments").val("");
    $("#modalinvoice #Signautre1").prop("checked", true);
    return false;
  });

  $(document).on("click", "#invoice .btn-sign", function () {
    var ID = $(this).attr("id");
    var Title = $(this).data("title");
    $.fileup("invoice-pdf", "remove", "*");
    $("#invoice #btn-submit").prop("disabled", false);
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
    if ($("#invoice_signed_filename").val() == "") {
      $("#modalsign #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("invoice-pdf", "upload", "*");
    }
    return false;
  });

  $("#invoice #filter-actions-deleted").click(function (e) {
    var table = $("#table_invoice").DataTable();
    table.ajax.reload(null, false);
  });

  $("#invoice #btn-search").click(function (e) {
    var table = $("#table_invoice").DataTable();
    table.ajax.reload(null, false);
  });

  if ($("#invoice-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/invoice.php",
      inputID: "invoice-pdf",
      queueID: "invoice-pdf-queue",
      dropzoneID: "invoice-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Signature: jQuery("#modalinvoice input[name=Signature]:checked"),
        Title: jQuery("#modalinvoice #Title"),
        Comments: jQuery("#modalinvoice #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalinvoice #errors").hide();
        $("#invoice_filename").val(file.name);
      },
      onRemove: function (total, file_number, file) {
        $("#invoice_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_invoice").DataTable();
        table.ajax.reload(null, false);
        $("#modalinvoice").modal("hide");
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        window.location.href =
          window.location.href + separator + "_n=" + randomNumber;
      },
      onError: function (event, file, file_number, response) {
        $.fileup("invoice-pdf", "remove", "*");
        $("#modalinvoice #btn-submit").prop("disabled", false);
        $("#modalinvoice #errors").show().html(response);
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
