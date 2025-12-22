var $input = $("#tank-image");

$(document).ready(function () {
  var table_tank = $("#table_tank").DataTable({
    paging: true,
    lengthChange: false,
    searching: false,
    ordering: false,
    info: true,
    responsive: true,
    autoWidth: false,
    pageLength: 100,
    pagingType: "full_numbers",
    processing: true,
    serverSide: true,
    fixedHeader: false,
    initComplete: function () {
      return;
      var api = this.api();

      // For each column
      api
        .columns()
        .eq(0)
        .each(function (colIdx) {
          // Set the header cell to contain the input element
          var cell = $(".filters th").eq(
            $(api.column(colIdx).header()).index()
          );

          if ($(cell).hasClass("no-sort")) return;

          var title = $(cell).text();

          $(cell).html(
            '<input type="text" class="' +
              $(cell).attr("id") +
              '" placeholder="' +
              title +
              '" />'
          );

          // On every keypress in this input
          $(
            "input",
            $(".filters th").eq($(api.column(colIdx).header()).index())
          )
            .off("keyup change")
            .on("change", function (e) {
              // Get the search value
              $(this).attr("title", $(this).val());
              var regexr = "({search})"; //$(this).parents('th').find('select').val();

              var cursorPosition = this.selectionStart;
              // Search the column for that value
              api
                .column(colIdx)
                .search(
                  this.value != ""
                    ? regexr.replace("{search}", "(((" + this.value + ")))")
                    : "",
                  this.value != "",
                  this.value == ""
                )
                .draw();
            })
            .on("keyup", function (e) {
              e.stopPropagation();

              $(this).trigger("change");
              $(this)
                .focus()[0]
                .setSelectionRange(cursorPosition, cursorPosition);
            });
        });
    },
    ajax: {
      url: "pages/pa-ingreds/data.php",
      type: "POST",
      async: true,
      data: function (data) {
        data.sproducer_id = $("#sproducer_id").val();
        data.srmcode = $("#srmcode").val();
        data.sname = $("#sname").val();
        data.clientid = $("#ingred-clientid").val();
      },
    },
    columns: [
      { data: "producer_name" },
      { data: "rmcode" },
      { data: "name" },
      { data: "cb" },
      { data: "halalexp" },
      { data: "rmposition" },
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        //targets: "no-sort",
        //	orderable: false,
      },
    ],
  });

  $(document).on("click", ".btn-add", function () {
    $("#bmrow").hide();
    $("#btrow").hide();
    $("#modaltank").modal("show");
    $(".modal-title.title-add").show();
    $(".modal-title.title-edit").hide();
    $("#errors").hide();
    $("#id").val("");
    $("#producer_id").val("");
    $("#rmcode").val("");
    $("#name").val("");
    $("#cb").val("");
    $("#halalexp").val("");
    $("#rmposition").val("");
    $("#rmcode").focus();
    return false;
  });

  $(document).on("click", "#btnsave-tank", function () {
    //if (!$("#btnsave-tank").hasClass("disabled")) {
    var values = {
      id: $("#id").val(),
      producer_id: $("#producer_id").val(),
      rmcode: $("#rmcode").val(),
      name: $("#name").val(),
      cb: $("#cb").val(),
      halalexp: $("#halalexp").val(),
      rmposition: $("#rmposition").val(),
    };
    $.ajax({
      url: "pages/pa-ingreds/save.php",
      type: "POST",
      data: values,
      success: function (data, textStatus, jqXHR) {
        if (data.length > 0) {
          $("#errors").show().html(data);
          return;
        }
        var table = $("#table_tank").DataTable();
        table.ajax.reload(null, false);
        $("#modaltank").modal("toggle");
        $("#errors").hide();
      },
      error: function (jqXHR, textStatus, errorThrown) {},
    });
    return false;
  });

  $(document).on("click", ".btnedit-tank", function () {
    $(".modal-title.title-add").hide();
    $(".modal-title.title-edit").show();
    $("#errors").hide();
    var id = $(this).attr("id");
    var values = {
      id: id,
    };
    $.ajax({
      url: "pages/pa-ingreds/get.php",
      type: "POST",
      data: values,
      success: function (data, textStatus, jqXHR) {
        var data = jQuery.parseJSON(data);
        $("#id").val(data.id);
        $("#producer_id").val(data.producer_id);
        $("#rmcode").val(data.rmcode);
        $("#name").val(data.name);
        $("#cb").val(data.cb);
        $("#halalexp").val(data.halalexp);
        $("#rmposition").val(data.rmposition);
        $("#modaltank").modal("show");
        $("#rmcode").focus();
      },
      error: function (jqXHR, textStatus, errorThrown) {},
    });
    return false;
  });

  $(document).on("click", ".btndel-tank", function () {
    var id = $(this).attr("id");
    var values = {
      id: id,
    };
    if (confirm("Are you sure you want to delete?")) {
      $.ajax({
        url: "pages/pa-ingreds/delete.php",
        type: "POST",
        data: values,
        success: function (data, textStatus, jqXHR) {
          var table = $("#table_tank").DataTable();
          table.ajax.reload(null, false);
        },
        error: function (jqXHR, textStatus, errorThrown) {},
      });
    }
    return false;
  });

  $(document).on("click", ".btnselect-tank", function () {
    var doc = {};
    doc.idclient = $("#ingred-clientid").val();
    doc.id = $(this).attr("id");

    $.post("ajax/ajaxHandler.php", {
      rtype: "savePAIngredient",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        Utils.notify("error", response.statusDescription);
        return;
      }
      $("#paModal").modal("hide");
      jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
    return false;
  });

  $(document).on("change", "#sproducer_id", function () {
    var table = $("#table_tank").DataTable();
    table.ajax.reload(null, false);
    return false;
  });

  $(document).on("keyup", "#srmcode, #sname", function (ev) {
    var table = $("#table_tank").DataTable();
    table.ajax.reload(null, false);
    return false;
  });

  $("#halalexp").datepicker({ format: "dd/mm/yyyy" });

  $(document).on("click", ".btn-import", function () {
    $.fileup("app-pdf", "remove", "*");
    $("#app #btn-submit").prop("disabled", false);
    $("#modalapp").modal("show");
    $("#modalapp .modal-title.title-add").show();
    $("#errors").hide();
    return false;
  });

  $.fileup({
    url: "/fileupload/partials/paingreds.php",
    inputID: "app-pdf",
    queueID: "app-pdf-queue",
    dropzoneID: "app-pdf-dropzone",
    extraFields: { idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp") },
    //filesLimit: 1,
    sizeLimit: 1000000 * 25,
    autostart: 1,
    onSelect: function (file) {
      $("#modalapp #errors").hide();
      $("#app_filename").val(file.name);
    },
    onRemove: function (total, file_number, file) {
      $("#app_filename").val("");
    },
    onSuccess: function (response, file_number, file) {
      var table = $("#table_tank").DataTable();
      table.ajax.reload(null, false);
      $("#modalapp").modal("hide");
    },
    onError: function (event, file, file_number, response) {
      $("#app #btn-submit").prop("disabled", false);
      $("#errors").show().html(response);
    },
  });
  /*
    $.notifyDefaults({
      type: 'success',
      delay: 500
    });
	*/
});
