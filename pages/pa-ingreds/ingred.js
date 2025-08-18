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
        targets: "no-sort",
        orderable: false,
      },
    ],
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
});
