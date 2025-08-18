$(document).ready(function() { 
      var table_doc = $('#table_doc').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "responsive": true,
        "autoWidth": false,
        "pageLength": 25,
		"pagingType": "full_numbers",
		"processing": true,
        "serverSide": true,
		"createdRow": function( row, data, dataIndex){
			if( data['deleted'] ==  '1'){
				$(row).addClass('strikeout');
			}
		},				
        "ajax": {
          "url": "../ajax/getDocs.php",
          "type": "POST",
		   "async": true,
		   "data": function(data) {
			   data.s=$("#s").val();
			   data.category="app";
			   data.idclient=$("#app-clientid").val();			   
			   data.deleted=$("#filter-actions-deleted").is(":checked") ? 1 : 0;			   			   
		  }
        },
        "columns": [
        { "data": "FileName"}, 
        { "data": "UserName"}, 
        { "data": "Uploaded"}, 
        { "data": "button", "sClass": "text-center buttons"}, 
        ],
		"columnDefs": [{ 
		"targets"  : 'no-sort',
      	"orderable": false,
     	}],
      });
	  
	  table_doc.on("click", "th.select-checkbox", function() {
    if ($("th.select-checkbox").hasClass("selected")) {
        example.rows().deselect();
        $("th.select-checkbox").removeClass("selected");
    } else {
        example.rows().select();
        $("th.select-checkbox").addClass("selected");
    }
}).on("select deselect", function() {
    if (example.rows({
            selected: true
        }).count() !== example.rows().count()) {
        $("th.select-checkbox").removeClass("selected");
    } else {
        $("th.select-checkbox").addClass("selected");
    }
});

    $(document).on("click","#btnadd-doc",function() { 
		$("#bmrow").hide();
		$("#btrow").hide();
		$("#modaldoc").modal("show");
		$(".modal-title.title-add").show();
		$(".modal-title.title-edit").hide();
		if ($("#billing_mode_ui_add_visibility").val()==1) {
			$("#bmrow").show();
		}
		if ($("#billing_target_ui_add_visibility").val()==1) {
			$("#btrow").show();
		}
		$("#errors").hide();
		$("#ID").val("");
		$("#FuelID").val("1");
		$("#MPRN").val("");
		$("#CRN").val("");
		$("#TankID").val("");
		$("#Status").val($("#doc_status_ui_default").val());
		$("#FillRestriction1").prop("checked", true);
		$("#AnomaliesStatus1").prop("checked", true);
		$("#Type1").prop("checked", true);
		var bm_default_value = $("#billing_mode_ui_default").val();
		var bt_default_value = $("#billing_target_ui_default").val();
		$("input[name=BillingMode][value='"+bm_default_value+"']").prop("checked", true);
		$("input[name=BillingTarget][value='"+bt_default_value+"']").prop("checked", true);		
		$("#InstallDate").val("");
		$("#SensorComms").val("");
		$("#MPRN").focus();
		return false;
    });
	
	$("#filter-actions-deleted").click(function(e) {
	  var table = $('#table_doc').DataTable(); 
	  table.ajax.reload( null, false );
	});
	$("#btn-search").click(function(e) {
	  var table = $('#table_doc').DataTable(); 
	  table.ajax.reload( null, false );
	});

    $(document).on("click","#btnsave-doc",function(){
		//if (!$("#btnsave-doc").hasClass("disabled")) {
		  var values = {
			ID: $("#ID").val(),
			FuelID: $("#FuelID").val(),
			InstallDate: $("#InstallDate").val(),
			SensorComms: $("#SensorComms").val(),
			MPRN: $("#MPRN").val(),
			CRN: $("#CRN").val(),
			TankID: $("#TankID").val(),
			Status: $("#Status").val(),
			Type: $("input[name=Type]:checked").val(),
			BillingMode: $("input[name=BillingMode]:checked").val(),
			BillingTarget: $("input[name=BillingTarget]:checked").val(),
			FillRestriction: $("input[name=FillRestriction]:checked").val(),
			AnomaliesStatus: $("input[name=AnomaliesStatus]:checked").val(),
			InstallDate: $("#InstallDate").val(),
			SensorComms: $("#SensorComms").val(),
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
			  var table = $('#table_doc').DataTable(); 
			  table.ajax.reload( null, false );
			  $("#modaldoc").modal('toggle');
			  $("#errors").hide();
			},
			error: function(jqXHR, textStatus, errorThrown) {}
		  });
		return false;
    });
	
    $(document).on("click",".btnedit-doc",function(){
	  $("#bmrow").hide();
	  $("#btrow").hide();
	  $(".modal-title.title-add").hide();
	  $(".modal-title.title-edit").show();
	  $("#errors").hide();
	  if ($("#billing_mode_ui_edit_visibility").val()==1) {
	    $("#bmrow").show();
	  }
	  if ($("#billing_target_ui_edit_visibility").val()==1) {
	    $("#btrow").show();
	  }
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
			$("#FuelID").val(data.FuelID);
			$("#MPRN").val(data.MPRN);
			$("#CRN").val(data.CRN);
			$("#TankID").val(data.TankID);
			$("#Status").val(data.Status);
			$("input[name=Type][value='"+data.Type+"']").prop("checked", true);
			$("input[name=BillingMode][value='"+data.BillingMode+"']").prop("checked", true);
			$("input[name=BillingTarget][value='"+data.BillingTarget+"']").prop("checked", true);
			$("input[name=FillRestriction][value='"+data.FillRestriction+"']").prop("checked", true);		
			$("input[name=AnomaliesStatus][value='"+data.AnomaliesStatus+"']").prop("checked", true);		
			$("#InstallDate").val(data.InstallDate);
			$("#SensorComms").val(data.SensorComms);
			$("#modaldoc").modal('show');
			$("#name").focus();
        },
        error: function(jqXHR, textStatus, errorThrown) { 
        }
      });
	  return false;
    });
	
    $(document).on( "click",".btndel-doc", function() {
		var ID = $(this).attr("id");
		var values = {
			ID: ID
		};
		if (confirm('Are you sure you want to delete this Tank with associated Fills, Factors, Tenures and Bad Reading Configs?')) {
			$.ajax({
				url : "delete.php",
				type: "POST",
				data : values,
				success: function(data, textStatus, jqXHR) {
					var table = $('#table_doc').DataTable(); 
					table.ajax.reload( null, false );
				},
				error: function(jqXHR, textStatus, errorThrown) {}
			});
		}
		return false;
	});

    $(document).on( "click",".btn-filter", function() {
		var table = $('#table_doc').DataTable(); 
		table.ajax.reload( null, false );
		return false;
	});

    $("#app-clientid").on("change", function() {
		var table = $('#table_doc').DataTable(); 
		table.ajax.reload( null, false );
		return false;
	});
	
	$.fileup({
		url: '/fileupload/upload.php',
		inputID: 'pdf',
		queueID: 'pdf-queue',
            dropzoneID: 'pdf-dropzone',
		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		onRemove: function(total, file_number, file) {
			//$("#agent_photo_filename").val("");
		},
		onSuccess: function(response, file_number, file) {
			//$("#agent_photo_filename").val(response);
		},
		onError: function(event, file, file_number) {
			//$("#agent_photo").val(file.name);
		},		
	});

	//$("#InstallDate").datetimepicker({format : "DD/MM/YYYY"});
	
	/*
    $.notifyDefaults({
      type: 'success',
      delay: 500
    });
	*/
});