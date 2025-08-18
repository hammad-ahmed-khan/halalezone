$(document).ready(function() { 
      var table_checklist = $('#table_checklist').DataTable({
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
			   data.category="checklist";
			   //data.deleted=$("#checklist #filter-actions-deleted").is(":checked") ? 1 : 0;			   			   
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

	
	$('#table_checklist').on('click', 'td.details-control', function(){
	  var tr = $(this).closest('tr');
        var row = table_checklist.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            d = row.data();
			console.log(d);
            row.child( format3(d) ).show();
			     var child_table = $('#table_checklist_'+d.id).DataTable({
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
			   data.category="checklist";
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
	  
    $(document).on("click","#modalchecklist #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#checklist_filename").val() == '') {
			$("#modalchecklist #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('checklist-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#checklist .btndel-doc",function() { 
		if (confirm('Are you sure you want to delete?')) {
		  var ID = $(this).attr("id");
		  var values = {
			Delete: ID,
			idclient: jQuery("#app-clientid").val(), 
			idapp: jQuery("#idapp").val(),
		  };
		  $.ajax({
			url : "/fileupload/partials/checklist.php",
			type: "POST",
			data : values,
			dataType: "json",
			beforeSend: function(){
			},
			
			success: function(data, textStatus, jqXHR) {
			  if (data.success == 1) { 
				  var table = $('#table_checklist').DataTable(); 
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

    $(document).on("click","#modalchecklist #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#checklist_filename").val() == '') {
			$("#modalchecklist #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('checklist-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#checklist #btn-upload",function() { 
		$.fileup('checklist-pdf', 'remove', '*');
		$("#checklist #btn-submit").prop('disabled', false);
		$("#modalchecklist").modal("show");
		$("#modalchecklist .modal-title.title-add").show();
		$("#modalchecklist #errors").hide();
		$("#modalchecklist #Title").val("");
		$("#modalchecklist #Comments").val("");
		$("#modalchecklist #Signautre1").prop("checked", true);		
		return false;
    });

    $(document).on("click","#checklist .btn-sign",function() {
		var ID = $(this).attr('id'); 
		var Title = $(this).data('title'); 
		$.fileup('sign-checklist-pdf', 'remove', '*');
		$("#checklist #btn-submit").prop('disabled', false);
		$("#modalsignchecklist").modal("show");
		$("#modalsignchecklist .modal-title.title-add").show();
		$("#modalsignchecklist #errors").hide();
		$("#modalsignchecklist #ID").val(ID);
		$("#modalsignchecklist #Title").val(Title);
		$("#modalsignchecklist #Label").html(Title);
		return false;
    });
	
	
    $(document).on("click","#modalsignchecklist #btn-submit",function() {
		$(this).prop('disabled', true);
		if ($("#checklist_signed_filename").val() == '') {
			$("#modalsignchecklist #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('sign-checklist-pdf', 'upload', '*');
		}
		return false;
    });

	$("#checklist #filter-actions-deleted").click(function(e) {
	  var table = $('#table_checklist').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$("#checklist #btn-search").click(function(e) {
	  var table = $('#table_checklist').DataTable(); 
	  table.ajax.reload( null, false );
	});

if ($("#checklist-pdf").length) {
	$.fileup({
		url: '/fileupload/partials/checklist.php',
		inputID: 'checklist-pdf',
		queueID: 'checklist-pdf-queue',
        dropzoneID: 'checklist-pdf-dropzone',
		extraFields: {idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp"), Signature: jQuery("#modalchecklist input[name=Signature]:checked"), Title: jQuery("#modalchecklist #Title"), Comments: jQuery("#modalchecklist #Comments")},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		onSelect: function(file) {
			$("#modalchecklist #errors").hide();
			$("#checklist_filename").val(file.name);			
		},
		onRemove: function(total, file_number, file) {
			$("#checklist_filename").val("");
		},
		onSuccess: function(response, file_number, file) { 
			var table = $('#table_checklist').DataTable(); 
			table.ajax.reload( null, false );
			$("#modalchecklist").modal("hide");			
		},
		onError: function(event, file, file_number, response) {
			$.fileup('checklist-pdf', 'remove', '*');
			$("#modalchecklist #btn-submit").prop('disabled', false);
			$("#modalchecklist #errors").show().html(response);
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

function format3( d ) {

return '<div style="width:98%; margin:0 auto;"><table id="table_checklist_'+d.id+'" class="table table-bordered table-condensed">'+
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