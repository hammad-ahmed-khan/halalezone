 $(document).ready(function() { 

		var date = moment().millisecond(0).second(0).minute(0).hour(0);
$("#extra14").datetimepicker({format : "DD/MM/YYYY", minDate: date});
	$("#extra15").datetimepicker({format : "DD/MM/YYYY", minDate: date});
	
      var table_audit = $('#table_audit').DataTable({
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
			   //data.actions="delete";
			   data.idclient=$("#app-clientid").val();			   
			   data.idapp=$("#idapp").val();			   
			   data.category="audit";
			   //data.deleted=$("#audit #filter-actions-deleted").is(":checked") ? 1 : 0;			   			   
		  }
        },
        "columns": [
		 {
			"className":      'details-control',
			"orderable":      false,
			"data":           null,
			"defaultContent": '',
			"render": function (data, type, row) {
						   if (row.hasChildren > 0) //Check column value "Yes"
							   return "<div></div>"; //Empty cell content
						   else
							   return '';                                  
					   }
		 },
        { "data": "Title"}, 
        { "data": "FileName"}, 
        { "data": "UserName"}, 
        { "data": "Uploaded"}, 
        //{ "data": "Signature"}, 
        //{ "data": "Comments"}, 
        { "data": "button", "sClass": "text-center buttons"}, 
        ],
		"columnDefs": [{ 
		"targets"  : 'no-sort',
      	"orderable": false,
     	}],
      });

	
	$('#table_audit').on('click', 'td.details-control', function(){
	  var tr = $(this).closest('tr');
        var row = table_audit.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            d = row.data();
			console.log(d);
            row.child( format2(d) ).show();
			     var child_table = $('#table_audit_'+d.id).DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
			"pageLength": 25,
	
        "ordering": false,
        "info": false,
        "responsive": true,
        "autoWidth": false,
		"processing": true,
        "serverSide": true,		
        "ajax": {
          "url": "../ajax/getDocs.php",
          "type": "POST",
		   "async": true,
		   "data": function(data) {
			   data.idparent=d.id;			   
			   data.idclient=$("#app-clientid").val();			   
			   data.idapp=$("#idapp").val();			   
			   data.category="audit";
		  }
        },
        "columns": [
        { "data": "Title"}, 
        { "data": "FileName"}, 
        { "data": "UserName"}, 
        { "data": "Uploaded"}, 
        //{ "data": "Comments"}, 
        { "data": "button", "sClass": "text-center buttons"}, 
        ],
		"columnDefs": [{ 
		"targets"  : 'no-sort',
      	"orderable": false,
     	}]		
      });
	  /*
 var table = $('#child_table_'+d.TankID).DataTable(); 
			  table.ajax.reload( null, false );
		*/	
            tr.addClass('shown');
        }
	});
	  
    $(document).on("click","#modalaudit #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#audit_filename").val() == '') {
			$("#modalaudit #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('audit-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#audit .btndel-doc",function() { 
		if (confirm('Are you sure you want to delete?')) {
		  var ID = $(this).attr("id");
		  var values = {
			Delete: ID,
			idclient: jQuery("#app-clientid").val(), 
			idapp: jQuery("#idapp").val(),
		  };
		  $.ajax({
			url : "/fileupload/partials/audit.php",
			type: "POST",
			data : values,
			dataType: "json",
			beforeSend: function(){
			},
			
			success: function(data, textStatus, jqXHR) {
			  if (data.success == 1) { 
				  var table = $('#table_audit').DataTable(); 
				  table.ajax.reload( null, false );
				  //$("#errors").hide();
				return;
			  }
			},
			error: function(jqXHR, textStatus, errorThrown) {
				}
		  });
		}
		return false;
    });

    $(document).on("click","#modalaudit #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#audit_filename").val() == '') {
			$("#modalaudit #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('audit-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#audit #btn-upload",function() { 
		$.fileup('audit-pdf', 'remove', '*');
		$("#audit #btn-submit").prop('disabled', false);
		$("#modalaudit").modal("show");
		$("#modalaudit .modal-title.title-add").show();
		$("#modalaudit #errors").hide();
		$("#modalaudit #Title").val("");
		$("#modalaudit #Comments").val("");
		$("#modalaudit #Signautre1").prop("checked", true);		
		return false;
    });

    $(document).on("click","#audit .btn-sign",function() {
		var ID = $(this).attr('id'); 
		var Title = $(this).data('title'); 
		$.fileup('sign-audit-pdf', 'remove', '*');
		$("#audit #btn-submit").prop('disabled', false);
		$("#modalsignaudit").modal("show");
		$("#modalsignaudit .modal-title.title-add").show();
		$("#modalsignaudit #errors").hide();
		$("#modalsignaudit #ID").val(ID);
		$("#modalsignaudit #Title").val(Title);
		$("#modalsignaudit #Label").html(Title);
		return false;
    });

	$('#audit #btn-sendplan').on('click', function(e) {
		if (confirm("Are you sure you want to send?")) {
			var doc = {};
			doc.idclient = $("#app-clientid").val();
			doc.idapp = $("#idapp").val();
			$.post("ajax/ajaxHandler.php", {
			  rtype: "sendAuditPlan",
			  uid: 0,
			  data: doc,
			}).done(function (data) {
				var response = JSON.parse(data);
				if (response.data.errors) {
				  $("#audit #errors").html(response.data.errors).show();
				  return;
				}				
			   var table = $('#table_audit').DataTable(); 
		  		table.ajax.reload( null, false );
				//changeAppState("report"); 
			  //$("#ingredGrid").jqGrid().trigger("reloadGrid");
			  var separator = (window.location.href.indexOf('?') > -1) ? '&' : '?';
			  var randomNumber = Math. floor(Math. random() * 100) + 1;
			  window.location.href = window.location.href + separator + '_n='+randomNumber;
	  
			});
		}
		return false;
    });
	
	$(document).on("click","#audit #btnsave-settings",function() { 
		$.post("ajax/ajaxHandler.php", $("#frmAuditPlanSettings").serialize()+"&idclient="+$("#app-clientid").val()+"&idapp="+$("#idapp").val()+"&rtype=saveAuditPlanSettings&uid=0"
		).done(function (data) {
		  var response = JSON.parse(data);
		  if (response.status == 0) {
			$("#dates #errors").html(response.statusDescription).show();
			return;
		  }
			$("#dates #errors").html("").hide();
		  $("#modalauditplansettings").modal("hide");
		  //$("div#dates").notify( "Settings saved successfully.", { position:"top right", className: "success" });
		  //$("#ingredGrid").jqGrid().trigger("reloadGrid");
		});	
		return false;
    });
	
	$('#audit #btn-preview').on('click', function(e) {
		idclient = $("#app-clientid").val();
		idapp = $("#idapp").val();
		$("#audit #previewIdClient").val(idclient);
		$("#audit #previewIdApp").val(idapp);		
		$("#audit #frmPreview").submit();
		return false;
	});
	
    $(document).on("click","#modalsignaudit #btn-submit",function() {
		$(this).prop('disabled', true);
		if ($("#audit_signed_filename").val() == '') {
			$("#modalsignaudit #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('sign-audit-pdf', 'upload', '*');
		}
		return false;
    });

	$("#audit #filter-actions-deleted").click(function(e) {
	  var table = $('#table_audit').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$("#audit #btn-search").click(function(e) {
	  var table = $('#table_audit').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$.fileup({
		url: '/fileupload/partials/audit.php',
		inputID: 'audit-pdf',
		queueID: 'audit-pdf-queue',
        dropzoneID: 'audit-pdf-dropzone',
		extraFields: {idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp"), Signature: jQuery("#modalaudit input[name=Signature]:checked"), Title: jQuery("#modalaudit #Title"), Comments: jQuery("#modalaudit #Comments")},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		onSelect: function(file) {
			$("#modalaudit #errors").hide();
			$("#audit_filename").val(file.name);			
		},
		onRemove: function(total, file_number, file) {
			$("#audit_filename").val("");
		},
		onSuccess: function(response, file_number, file) {
			var table = $('#table_audit').DataTable(); 
			table.ajax.reload( null, false );
			$("#modalaudit").modal("hide");			
		},
		onError: function(event, file, file_number, response) {
			$.fileup('audit-pdf', 'remove', '*');
			$("#modalaudit #btn-submit").prop('disabled', false);
			$("#modalaudit #errors").show().html(response);
		},		
	});

if ($("#sign-audit-pdf").length) {
	$.fileup({
		url: '/fileupload/partials/sign.php',
		inputID: 'sign-audit-pdf',
		queueID: 'sign-audit-pdf-queue',
        dropzoneID: 'sign-audit-pdf-dropzone',
		extraFields: {idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp"), category: "audit", ID: jQuery("#modalsignaudit #ID"), Title: jQuery("#modalsignaudit #Title"),  Comments: jQuery("#modalsignaudit #Comments")},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 0,
		onSelect: function(file) {
			$("#modalsignaudit #errors").hide();
			$("#audit_signed_filename").val(file.name);			
		},
		onRemove: function(total, file_number, file) {
			$("#audit_signed_filename").val("");
		},
		onSuccess: function(response, file_number, file) {
			var table = $('#table_audit').DataTable(); 
			table.ajax.reload( null, false );
			$("#modalsignaudit").modal("hide");			
		},
		onError: function(event, file, file_number, response) {
			$.fileup('sign-audit-pdf', 'remove', '*');
			$("#modalsignaudit #btn-sign").prop('disabled', false);
			$("#modalsignaudit #errors").show().html(response);
		},		
	}).dragEnter(function(event) {
                $(event.target).addClass('over');
            })
            .dragLeave(function(event) {
                $(event.target).removeClass('over');
            })
            .dragEnd(function(event) {
                $(event.target).removeClass('over');
            });
}
});

function format2( d ) {

return '<div style="width:98%; margin:0 auto;"><table id="table_audit_'+d.id+'" class="table table-bordered table-condensed">'+
	'<thead>'+
	  '<tr class="tableheader">'+
      '<th style="">Title</th>'+
      '<th style="">File Name</th>'+
      '<th style="">Signed by</th>'+
      '<th style="">Date Signed</th>'+
      '<th style="width:150px;"></th>'+
	  '</tr>'+
	'</thead>'+
	'<tbody>'+
	'</tbody>'+
  '</table></div>';
}