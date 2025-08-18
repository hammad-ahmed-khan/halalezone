var $input = $("#tenure-image");

$(document).ready(function() { 

      var table_tenure = $('#table_tenure').on('xhr.dt', function (e, settings, json, xhr) {
            json.recordsTotal = json.recordsFiltered = xhr.getResponseHeader('recordsTotal');
        }).DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "responsive": true,
        "autoWidth": false,
        "pageLength": 15,
		"pagingType": "full_numbers",
		"processing": true,
        "serverSide": true,		
        "ajax": {
          "url": "data.php",
          "type": "POST",
		   "async": true,
		   "data": function(data) {
			 data.s = $("#sMPRN").val();
		  }
        },
        "columns": [
       // { "data": "cat_name"}, 
        { "data": "TankID"}, 
        { "data": "CRN"}, 
        { "data": "MPRN"}, 
        { "data": "ReadingJustBeforeFirstFill"}, 
        { "data": "ReadingJustBeforeFirstFillTimestamp"}, 
        { "data": "ReadingOnChurn"}, 
        { "data": "ReadingOnChurnTimestamp"}, 
        { "data": "Email"}, 
        { "data": "Mobile"}, 
        { "data": "FirstName"}, 
        { "data": "button", "sClass": "text-center buttons"}, 
        ],
		"columnDefs": [{ 
		"targets"  : 'no-sort',
      	"orderable": false,
     	}]		
      });

    $(document).on("click","#btnadd-tenure",function() { 
		$("#modaltenure").modal("show");
		$(".modal-title.title-add").show();
		$(".modal-title.title-edit").hide();
		$("#errors").hide();
		$("#ID").val("");
		$("#ReadingJustBeforeFirstFill").val("");
		$("#ReadingJustBeforeFirstFillTimestamp").val("");
		$("#ReadingOnChurn").val("");
		$("#ReadingOnChurnTimestamp").val("");
		$("#Email").val("");
		$("#Mobile").val("");
		$("#FirstName").val("");
		return false;
    });

    $(document).on("click","#btnsave-tenure",function(){
		//if (!$("#btnsave-tenure").hasClass("disabled")) {
		  var values = {
			ID: $("#ID").val(),
			MPRN: $("#MPRN").val(),
			CRN: $("#CRN").val(),
			TankID: $("#TankID").val(),
			ReadingJustBeforeFirstFill: $("#ReadingJustBeforeFirstFill").val(),
			ReadingJustBeforeFirstFillTimestamp: $("#ReadingJustBeforeFirstFillTimestamp").val(),
			ReadingOnChurn: $("#ReadingOnChurn").val(),
			ReadingOnChurnTimestamp: $("#ReadingOnChurnTimestamp").val(),
			Email: $("#Email").val(),
			Mobile: $("#Mobile").val(),
			FirstName: $("#FirstName").val(),
		  };
		  $.ajax({
			url : "save.php",
			type: "POST",
			data : values,
			success: function(data, textStatus, jqXHR) {
			  if (data.length>0) {
				$("#errors").show().html(data);
			  	return;
			  }
			  var table = $('#table_tenure').DataTable(); 
			  table.ajax.reload( null, false );
			  $("#modaltenure").modal('toggle');
			  $("#errors").hide();
			},
			error: function(jqXHR, textStatus, errorThrown) {}
		  });
		return false;
    });
	
    $(document).on("click",".btnedit-tenure",function(){
	  $(".modal-title.title-add").hide();
	  $(".modal-title.title-edit").show();
	  $("#errors").hide();
      var ID = $(this).attr("id");
      var values = {
        ID: ID
      };
      $.ajax({
        url : "get.php",
        type: "POST",
        data : values,
        success: function(data, textStatus, jqXHR) { 
          var data = jQuery.parseJSON(data);
			$("#ID").val(data.ID);
			$("#ReadingJustBeforeFirstFill").val(data.ReadingJustBeforeFirstFill);
			$("#ReadingJustBeforeFirstFillTimestamp").val(data.ReadingJustBeforeFirstFillTimestamp);
			$("#ReadingOnChurn").val(data.ReadingOnChurn);
			$("#ReadingOnChurnTimestamp").val(data.ReadingOnChurnTimestamp);
			$("#Email").val(data.Email);
			$("#Mobile").val(data.Mobile);
			$("#FirstName").val(data.FirstName);
			$("#MPRN").val(data.MPRN);
			$("#CRN").val(data.CRN);
			$("#TankID").val(data.TankID);
			$("#modaltenure").modal('show');
			$("#name").focus();
        },
        error: function(jqXHR, textStatus, errorThrown) { 
        }
      });
	  return false;
    });
	
    $(document).on( "click",".btndel-tenure", function() {
		var ID = $(this).attr("id");
		var values = {
			ID: ID
		};
		if (confirm('Are you sure you want to delete this Tenure?')) {
			$.ajax({
				url : "delete.php",
				type: "POST",
				data : values,
				success: function(data, textStatus, jqXHR) {
					var table = $('#table_tenure').DataTable(); 
					table.ajax.reload( null, false );
				},
				error: function(jqXHR, textStatus, errorThrown) {}
			});
		}
		return false;
	});

    $(document).on( "click",".btn-filter", function() {
		var table = $('#table_tenure').DataTable(); 
		table.ajax.reload( null, false );
		return false;
	});

	$("#ReadingJustBeforeFirstFillTimestamp").datetimepicker({format : "YYYY-MM-DD HH:mm:ss"});
	$("#ReadingOnChurnTimestamp").datetimepicker({format : "YYYY-MM-DD HH:mm:ss"});
	
	$("#MPRN").change(function(){
        var MPRN = $(this).val();
        var CRN = $(this).find(':selected').data("crn");
        var TankID = $(this).find(':selected').data("tankid");
		//$("#sMPRN").val(MPRN).trigger("chosen:updated");
		//$("#sCRN").val(CRN).trigger("chosen:updated");
		//$("#sTankID").val(TankID).trigger("chosen:updated");
		$("#CRN").val(CRN);
		$("#TankID").val(TankID);
		//  var table = $('#table_tenure').DataTable(); 
		 // table.ajax.reload( null, false );
    });
	
	$("#CRN").change(function(){
        var CRN = $(this).val();
        var MPRN = $(this).find(':selected').data("mprn");
        var TankID = $(this).find(':selected').data("tankid");
		//$("#sMPRN").val(MPRN).trigger("chosen:updated");
		//$("#sCRN").val(CRN).trigger("chosen:updated");
		//$("#sTankID").val(TankID).trigger("chosen:updated");
		$("#MPRN").val(MPRN);
		$("#TankID").val(TankID);
		  //var table = $('#table_tenure').DataTable(); 
		  //table.ajax.reload( null, false );
    });
	
	$("#TankID").change(function(){
        var TankID = $(this).val();
        var MPRN = $(this).find(':selected').data("mprn");
        var CRN = $(this).find(':selected').data("crn");
		//$("#sMPRN").val(MPRN).trigger("chosen:updated");
		//$("#sCRN").val(CRN).trigger("chosen:updated");
		//$("#sTankID").val(TankID).trigger("chosen:updated");
		$("#MPRN").val(MPRN);
		$("#CRN").val(CRN);
		 // var table = $('#table_tenure').DataTable(); 
		 // table.ajax.reload( null, false );
    });

	$("#sMPRN").chosen().change(function(){
        var MPRN = $(this).val();
        var CRN = $(this).find(':selected').data("crn");
        var TankID = $(this).find(':selected').data("tankid");
		$("#sCRN").val(CRN).trigger("chosen:updated");
		$("#sTankID").val(TankID).trigger("chosen:updated");
		$("#MPRN").val(MPRN);
		$("#CRN").val(CRN);
		$("#TankID").val(TankID);
		  var table = $('#table_tenure').DataTable(); 
		  table.ajax.reload( null, false );
    });
	
	$("#sCRN").chosen().change(function(){
        var CRN = $(this).val();
        var MPRN = $(this).find(':selected').data("mprn");
        var TankID = $(this).find(':selected').data("tankid");
		$("#sMPRN").val(MPRN).trigger("chosen:updated");
		$("#sTankID").val(TankID).trigger("chosen:updated");
		$("#MPRN").val(MPRN);
		$("#CRN").val(CRN);
		$("#TankID").val(TankID);
		  var table = $('#table_tenure').DataTable(); 
		  table.ajax.reload( null, false );
    });
	
	$("#sTankID").chosen().change(function(){
        var TankID = $(this).val();
        var MPRN = $(this).find(':selected').data("mprn");
        var CRN = $(this).find(':selected').data("crn");
		$("#sMPRN").val(MPRN).trigger("chosen:updated");
		$("#sCRN").val(CRN).trigger("chosen:updated");
		$("#MPRN").val(MPRN);
		$("#CRN").val(CRN);
		$("#TankID").val(TankID);
		  var table = $('#table_tenure').DataTable(); 
		  table.ajax.reload( null, false );
    });

	/*
    $.notifyDefaults({
      type: 'success',
      delay: 500
    });
	*/
});