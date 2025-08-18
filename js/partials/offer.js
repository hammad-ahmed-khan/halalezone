$(document).ready(function () {
  var table_offer = $("#table_offer").DataTable({
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
        data.category = "offer";
        data.deleted = $("#offer #filter-actions-deleted").is(":checked")
          ? 1
          : 0;
      },
    },
    columns: [
      { data: "id" },
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

  var table_service = $("#table_service").DataTable({
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
    ajax: {
      url: "../ajax/getOfferServices.php",
      type: "POST",
      async: true,
      data: function (data) {
        data.s = $("#s").val();
        data.idclient = $("#app-clientid").val();
        data.idapp = $("#idapp").val();
        data.deleted = $("#offer #filter-actions-deleted").is(":checked")
          ? 1
          : 0;
      },
    },
    columns: [
      // { "data": "cat_name"},
      { data: "Service" },
      { data: "Fee" },
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
  });

  var table_manage = $("#table_manage").DataTable({
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
    ajax: {
      url: "../ajax/getServicesList.php",
      type: "POST",
      async: true,
      data: function (data) {
        data.s = $("#modalmanage #s").val();
        /*
			   data.s=$("#s").val();
			   data.idclient=$("#app-clientid").val();			   
			   data.idapp=$("#idapp").val();
			   */
      },
    },
    columns: [
      // { "data": "cat_name"},
      { data: "service" },
      { data: "button", sClass: "text-center buttons" },
    ],
    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
  });

  $("#offer #btn-upload").on("click", function () {
    $(".modal-title.title-add").show();
    $(".modal-title.title-edit").hide();
    $("#modalservice #errors").hide();
    $("#modalservice #offerId").val("");
    $("#modalservice #Service").val("");
    $("#modalservice #NewService").val("");
    $("#modalservice #Fee").val("");
    $("#modalservice").modal("show");
    return false;
  });

  $(document).on("click", "#btnadd-manage", function () {
    if ($("#addservice").val() == "") {
      alert("Description is required.");
      return false;
    }
    var doc = {};
    doc.Service = $("#addservice").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveOfferService",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.data.errors == "") {
        var table_manage = $("#table_manage").DataTable();
        table_manage.ajax.reload(null, false);
      }
    });
    return false;
  });

  $("#offer #filter-actions-deleted").click(function (e) {
    var table = $("#table_offer").DataTable();
    table.ajax.reload(null, false);
  });

  $("#offer #btn-manage").on("click", function () {
    $("#modalmanage").modal("show");
    return false;
  });

  $("#Service").on("change", function () {
    var Service = $(this).val();
    if (Service == "addNewService") {
      $("#EditService").addClass("hidden");
      $("#NewService").removeClass("hidden");
      $("#ServiceInfo").removeClass("hidden");
      setTimeout(function () {
        $("#NewService").focus();
      }, 250);
    } else {
      $("#NewService").addClass("hidden");
      $("#EditService").removeClass("hidden");
      $("#EditService").val(Service);
      $("#ServiceInfo").removeClass("hidden");
      setTimeout(function () {
        $("#EditService").focus();
      }, 250);
    }
  });

  $("#offer #btn-preview").on("click", function (e) {
    idclient = $("#app-clientid").val();
    idapp = $("#idapp").val();
    $("#offer #previewIdClient").val(idclient);
    $("#offer #previewIdApp").val(idapp);
    $("#offer #frmPreview").submit();
    return false;
  });

  $("#offer #btn-submit").on("click", function (e) {
    var doc = {};
    doc.id = $("#offerId").val();
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.Service = $("#EditService").val();
    doc.NewService = $("#NewService").val();
    doc.Fee = $("#Fee").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "saveOffer",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.data.errors != "") {
        $("#modalservice #errors")
          .html("<ul>" + response.data.errors + "</ul>")
          .show();
        return;
      }
      $("#modalservice #errors").html("").hide();
      $("div#offer").notify("Data saved successfully.", {
        position: "top right",
        className: "success",
      });
      var table_service = $("#table_service").DataTable();
      table_service.ajax.reload(null, false);
      $("#modalservice").modal("hide");
    });
    return false;
  });

  $("#Service").on("change", function (e) {
    $("#modalservice #errors").html("").hide();
  });
  $("#Fee").on("keyup", function (e) {
    $("#modalservice #errors").html("").hide();
  });

  $("#Service").chosen({ width: "100%" });

  $(document).on("click", ".btndel-servicelist", function () {
    if (confirm("Are you sure you want to delete?")) {
      var doc = {};

      doc.ID = $(this).attr("id");
      $.post("ajax/ajaxHandler.php", {
        rtype: "deleteServiceList",
        uid: 0,
        data: doc,
      }).done(function (data) {
        var table_manage = $("#table_manage").DataTable();
        table_manage.ajax.reload(null, false);
      });
    }
    return false;
  });

  $("#modalservice").on("shown.bs.modal", function () {
    getServices();
  });

  $(document).on("click", ".btndel-service", function () {
    if (confirm("Are you sure you want to delete?")) {
      var doc = {};
      doc.idclient = $("#app-clientid").val();
      doc.idapp = $("#idapp").val();
      doc.ID = $(this).attr("id");
      $.post("ajax/ajaxHandler.php", {
        rtype: "deleteOffer",
        uid: 0,
        data: doc,
      }).done(function (data) {
        var table_service = $("#table_service").DataTable();
        table_service.ajax.reload(null, false);
      });
    }
    return false;
  });

  $(document).on("click", ".btnedit-service", function () {
    var doc = {};
    doc.id = $(this).attr("id");
    $.post("ajax/ajaxHandler.php", {
      rtype: "getOfferData",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      offer = response.data.offer;
      var Service = offer.Service;
      var Fee = offer.Fee;
      $("#EditService").addClass("hidden");
      $("#NewService").removeClass("hidden");
      $("#ServiceInfo").removeClass("hidden");

      $("#modalservice .modal-title.title-add").hide();
      $("#modalservice .modal-title.title-edit").show();
      $("#modalservice #errors").hide();
      $("#modalservice #offerId").val(doc.id);
      $("#modalservice #NewService").val(Service);
      $("#modalservice #Fee").val(Fee);
      $("#modalservice").modal("show");
      return false;
    });

    return false;
  });

  $("#offer #btn-send").on("click", function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to send?")) {
      var doc = {
        idclient: $("#app-clientid").val(),
        idapp: $("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/offer.php",
        type: "POST",
        data: doc,
        beforeSend: function () {
          // Code to execute before the request is sent
          $.blockUI();
        },
        complete: function () {
          // Code to execute when the request is complete
          $.unblockUI();
        },
        success: function (data) {
          var table = $("#table_offer").DataTable();
          table.ajax.reload(null, false);
          //changeAppState('soffer')
        },
        error: function (xhr, status, error) {
          console.error("An error occurred:", error);
        },
      });
    }
    return false;
  });

  $(document).on("click", "#offer .btndel-doc", function () {
    if (confirm("Are you sure you want to delete?")) {
      var ID = $(this).attr("id");
      var values = {
        Delete: ID,
        idclient: jQuery("#app-clientid").val(),
        idapp: jQuery("#idapp").val(),
      };
      $.ajax({
        url: "/fileupload/partials/offer.php",
        type: "POST",
        data: values,
        dataType: "json",
        beforeSend: function () {},

        success: function (data, textStatus, jqXHR) {
          if (data.success == 1) {
            var table = $("#table_offer").DataTable();
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

  $(document).on("click", "#offer #btn-upload-offer", function () {
    $.fileup("offer-pdf", "remove", "*");
    $("#offer #btn-submit").prop("disabled", false);
    $("#modaloffer").modal("show");
    $("#modaloffer .modal-title.title-add").show();
    $("#errors").hide();
    $("#modaloffer #Comments").val("");
    return false;
  });

  $.fileup({
    url: "/fileupload/partials/offer_upload.php",
    inputID: "offer-pdf",
    queueID: "offer-pdf-queue",
    dropzoneID: "offer-pdf-dropzone",
    extraFields: { idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp") },
    //filesLimit: 1,
    sizeLimit: 1000000 * 25,
    autostart: 1,
    onSelect: function (file) {
      $("#modaloffer #errors").hide();
      $("#offer_filename").val(file.name);
      $.blockUI();
    },
    onRemove: function (total, file_number, file) {
      $("#offer_filename").val("");
    },
    onSuccess: function (response, file_number, file) {
      var table = $("#table_offer").DataTable();
      table.ajax.reload(null, false);
      $("#modaloffer").modal("hide");
      $.unblockUI();
      //changeAppState('soffer');
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

  $("#offer #offerOffice").change(function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.offerOffice = $("#offerOffice").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "changeOfferOffice",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        //Utils.notify("error", response.statusDescription);
        return;
      }
      //Utils.notify("success", "Action was added");
    });
  });

  $("#offer #ingredientsLimit").change(function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.ingredientsLimit = $("#ingredientsLimit").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "changeIngredientsLimit",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        //Utils.notify("error", response.statusDescription);
        return;
      }
      //Utils.notify("success", "Action was added");
    });
  });

  $("#offer #productsLimit").change(function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.productsLimit = $("#productsLimit").val();
    $.post("ajax/ajaxHandler.php", {
      rtype: "changeProductsLimit",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        //Utils.notify("error", response.statusDescription);
        return;
      }
      //Utils.notify("success", "Action was added");
    });
  });
});
