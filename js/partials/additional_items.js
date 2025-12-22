$(document).ready(function() { 
      var table_additional_items = $('#table_additional_items').DataTable({
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
			   data.category="additional_items";
			   //data.deleted=$("#additional_items #filter-actions-deleted").is(":checked") ? 1 : 0;			   			   
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

	  
    $(document).on("click","#modaladditional_items #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#additional_items_filename").val() == '') {
			$("#modaladditional_items #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('additional_items-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#additional_items .btndel-doc",function() { 
		if (confirm('Are you sure you want to delete?')) {
		  var ID = $(this).attr("id");
		  var values = {
			Delete: ID,
			idclient: jQuery("#app-clientid").val(), 
			idapp: jQuery("#idapp").val(),
		  };
		  $.ajax({
			url : "/fileupload/partials/additional_items.php",
			type: "POST",
			data : values,
			dataType: "json",
			beforeSend: function(){
			},
			
			success: function(data, textStatus, jqXHR) {
			  if (data.success == 1) { 
				  var table = $('#table_additional_items').DataTable(); 
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

    $(document).on("click","#modaladditional_items #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#additional_items_filename").val() == '') {
			$("#modaladditional_items #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('additional_items-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#additional_items #btn-upload",function() { 
		$.fileup('additional_items-pdf', 'remove', '*');
		$("#additional_items #btn-submit").prop('disabled', false);
		$("#modaladditional_items").modal("show");
		$("#modaladditional_items .modal-title.title-add").show();
		$("#modaladditional_items #errors").hide();
		$("#modaladditional_items #Title").val("");
		$("#modaladditional_items #Comments").val("");
		$("#modaladditional_items #Signautre1").prop("checked", true);		
		return false;
    });

    $(document).on("click","#additional_items .btn-sign",function() {
		var ID = $(this).attr('id'); 
		var Title = $(this).data('title'); 
		$.fileup('sign-pdf', 'remove', '*');
		$("#additional_items #btn-submit").prop('disabled', false);
		$("#modalsign").modal("show");
		$("#modalsign .modal-title.title-add").show();
		$("#modalsign #errors").hide();
		$("#modalsign #ID").val(ID);
		$("#modalsign #Title").val(Title);
		$("#modalsign #Label").html(Title);
		return false;
    });
	
	
    $(document).on("click","#modalsign #btn-submit",function() {
		$(this).prop('disabled', true);
		if ($("#additional_items_signed_filename").val() == '') {
			$("#modalsign #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('sign-pdf', 'upload', '*');
		}
		return false;
    });

	$("#additional_items #filter-actions-deleted").click(function(e) {
	  var table = $('#table_additional_items').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$("#additional_items #btn-search").click(function(e) {
	  var table = $('#table_additional_items').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$.fileup({
		url: '/fileupload/partials/additional_items.php',
		inputID: 'additional_items-pdf',
		queueID: 'additional_items-pdf-queue',
        dropzoneID: 'additional_items-pdf-dropzone',
		extraFields: {idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp"), Signature: jQuery("#modaladditional_items input[name=Signature]:checked"), Title: jQuery("#modaladditional_items #Title"), Comments: jQuery("#modaladditional_items #Comments")},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		onSelect: function(file) {
			$("#modaladditional_items #errors").hide();
			$("#additional_items_filename").val(file.name);			
		},
		onRemove: function(total, file_number, file) {
			$("#additional_items_filename").val("");
		},
		onSuccess: function(response, file_number, file) {
			var table = $('#table_additional_items').DataTable(); 
			table.ajax.reload( null, false );
			$("#modaladditional_items").modal("hide");			
		},
		onError: function(event, file, file_number, response) {
			$.fileup('additional_items-pdf', 'remove', '*');
			$("#modaladditional_items #btn-submit").prop('disabled', false);
			$("#modaladditional_items #errors").show().html(response);
		},		
	});
});
 