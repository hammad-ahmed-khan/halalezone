$(document).ready(function () {
  var table_declarations = $("#table_declarations").DataTable({
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
        data.category = "declarations";
      },
    },
    columns: [
      {
        className: "details-control",
        orderable: false,
        data: null,
        defaultContent: "",
        render: function (data, type, row) {
          return row.hasChildren > 0 ? "<div></div>" : "";
        },
      },
      { data: "Title" },
      { data: "FileName" },
      { data: "UserName" },
      { data: "Uploaded" },
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
    initComplete: function () {
      // Automatically expand all rows when the table loads
      this.api()
        .rows()
        .every(function () {
          var row = this;
          var tr = $(row.node());
          if (row.data().hasChildren > 0) {
            //row.child(format(row.data())).show();
            //  tr.addClass("shown");
            initChildTable(row.data()); // Initialize child table
          }
        });
    },
  });

  // Function to initialize child table
  function initChildTable(d) {
    $("#table_declarations_" + d.id).DataTable({
      paging: false,
      lengthChange: false,
      searching: false,
      pageLength: 25,
      ordering: false,
      info: false,
      responsive: true,
      autoWidth: false,
      processing: true,
      serverSide: true,
      ajax: {
        url: "../ajax/getDocs.php",
        type: "POST",
        async: true,
        data: function (data) {
          data.idparent = d.id;
          data.idclient = $("#app-clientid").val();
          data.idapp = $("#idapp").val();
          data.category = "declarations";
        },
      },
      columns: [
        { data: "Title" },
        { data: "FileName" },
        { data: "UserName" },
        { data: "Uploaded" },
        { data: "button", sClass: "text-center buttons" },
      ],
      columnDefs: [
        {
          targets: "no-sort",
          orderable: false,
        },
      ],
    });
  }

  // Click event for toggling child rows manually (optional, since they are opened by default)
  $("#table_declarations").on("click", "td.details-control", function () {
    var tr = $(this).closest("tr");
    var row = table_declarations.row(tr);
    if (row.data().hasChildren > 0) {
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass("shown");
      } else {
        row.child(format(row.data())).show();
        tr.addClass("shown");
        initChildTable(row.data()); // Ensure the child table loads
      }
    }
  });

  $(document).on("click", "#modaldeclarations #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#declarations_filename").val() == "") {
      $("#modaldeclarations #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("declarations-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#declarations .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/declarations.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_declarations").DataTable();
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

  $(document).on("click", "#modaldeclarations #btn-submit", function () {
    $(this).prop("disabled", true);
    if ($("#declarations_filename").val() == "") {
      $("#modaldeclarations #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("declarations-pdf", "upload", "*");
    }
    return false;
  });

  $(document).on("click", "#declarations #btn-upload", function () {
    $.fileup("declarations-pdf", "remove", "*");
    $("#declarations #btn-submit").prop("disabled", false);
    $("#modaldeclarations").modal("show");
    $("#modaldeclarations .modal-title.title-add").show();
    $("#modaldeclarations #errors").hide();
    $("#modaldeclarations #Title").val("");
    $("#modaldeclarations #Comments").val("");
    $("#modaldeclarations #Signautre1").prop("checked", true);
    return false;
  });

  $(document).on("click", "#declarations .btn-sign", function () {
    var ID = $(this).attr("id");
    var Title = $(this).data("title");
    $.fileup("sign-pdf", "remove", "*");
    $("#declarations #btn-submit").prop("disabled", false);
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
    if ($("#declarations_signed_filename").val() == "") {
      $("#modalsign #errors").show().html("Please select a PDF file.");
      $(this).prop("disabled", false);
    } else {
      jQuery.fileup("sign-pdf", "upload", "*");
    }
    return false;
  });

  $("#declarations #filter-actions-deleted").click(function (e) {
    var table = $("#table_declarations").DataTable();
    table.ajax.reload(null, false);
  });

  $("#declarations #btn-search").click(function (e) {
    var table = $("#table_declarations").DataTable();
    table.ajax.reload(null, false);
  });

  if ($("#declarations-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/declarations.php",
      inputID: "declarations-pdf",
      queueID: "declarations-pdf-queue",
      dropzoneID: "declarations-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        Signature: jQuery("#modaldeclarations input[name=Signature]:checked"),
        Title: jQuery("#modaldeclarations #Title"),
        Comments: jQuery("#modaldeclarations #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modaldeclarations #errors").hide();
        $("#declarations_filename").val(file.name);
        $.blockUI();
      },
      onRemove: function (total, file_number, file) {
        $("#declarations_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        var table = $("#table_declarations").DataTable();
        table.ajax.reload(null, false);
        $("#modaldeclarations").modal("hide");
        $.unblockUI();
      },
      onError: function (event, file, file_number, response) {
        $.fileup("declarations-pdf", "remove", "*");
        $("#modaldeclarations #btn-submit").prop("disabled", false);
        $("#modaldeclarations #errors").show().html(response);
        $.unblockUI();
      },
    });
  }

  if ($("#sign-pdf").length) {
    $.fileup({
      url: "/fileupload/partials/sign.php",
      inputID: "sign-pdf",
      queueID: "sign-pdf-queue",
      dropzoneID: "sign-pdf-dropzone",
      extraFields: {
        idclient: jQuery("#app-clientid"),
        idapp: jQuery("#idapp"),
        category: "declarations",
        ID: jQuery("#modalsign #ID"),
        Title: jQuery("#modalsign #Title"),
        Comments: jQuery("#modalsign #Comments"),
      },
      filesLimit: 1,
      sizeLimit: 1000000 * 25,
      autostart: 1,
      onSelect: function (file) {
        $("#modalsign #errors").hide();
        $("#declarations_signed_filename").val(file.name);
        $.blockUI();
      },
      onRemove: function (total, file_number, file) {
        $("#declarations_signed_filename").val("");
      },
      onSuccess: function (response, file_number, file) {
        /*
        var table = $("#table_declarations").DataTable();
        table.ajax.reload(null, false);
        $("#modalsign").modal("hide");
        */
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        window.location.href =
          window.location.href + separator + "_n=" + randomNumber;
      },
      onError: function (event, file, file_number, response) {
        $.fileup("sign-pdf", "remove", "*");
        $("#modalsign #btn-sign").prop("disabled", false);
        $("#modalsign #errors").show().html(response);
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

function format(d) {
  return (
    '<div style="width:98%; margin:0 auto;"><table id="table_declarations_' +
    d.id +
    '" class="table table-bordered table-condensed">' +
    "<thead>" +
    '<tr class="tableheader">' +
    '<th style="">Title</th>' +
    '<th style="">File Name</th>' +
    '<th style="">Signed by</th>' +
    '<th style="">Date Signed</th>' +
    '<th style="width:150px;"></th>' +
    "</tr>" +
    "</thead>" +
    "<tbody>" +
    "</tbody>" +
    "</table></div>"
  );
}
