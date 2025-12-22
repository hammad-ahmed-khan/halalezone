 $(document).ready(function() { 
      var table_certificate = $('#table_certificate').DataTable({
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
			   data.category="certificate";
			   //data.deleted=$("#certificate #filter-actions-deleted").is(":checked") ? 1 : 0;			   			   
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
	  
    $(document).on("click","#modalcertificate #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#certificate_filename").val() == '') {
			$("#modalcertificate #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('certificate-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#certificate .btndel-doc",function() { 
		if (confirm('Are you sure you want to delete?')) {
		  var ID = $(this).attr("id");
		  var values = {
			Delete: ID,
			idclient: jQuery("#app-clientid").val(), 
			idapp: jQuery("#idapp").val(),
		  };
		  $.ajax({
			url : "/fileupload/partials/certificate.php",
			type: "POST",
			data : values,
			dataType: "json",
			beforeSend: function(){
			},
			
			success: function(data, textStatus, jqXHR) {
			  if (data.success == 1) { 
				  var table = $('#table_certificate').DataTable(); 
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

    $(document).on("click","#modalcertificate #btn-submit",function() { 
		$(this).prop('disabled', true);
		if ($("#certificate_filename").val() == '') {
			$("#modalcertificate #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('certificate-pdf', 'upload', '*');
		}
		return false;
    });

    $(document).on("click","#certificate #btn-upload",function() { 
		$.fileup('certificate-pdf', 'remove', '*');
		$("#certificate #btn-submit").prop('disabled', false);
		$("#modalcertificate").modal("show");
		$("#modalcertificate .modal-title.title-add").show();
		$("#modalcertificate #errors").hide();
		$("#modalcertificate #Title").val("");
		$("#modalcertificate #Comments").val("");
		$("#modalcertificate #Signautre1").prop("checked", true);		
		return false;
    });

    $(document).on("click","#certificate .btn-sign",function() {
		var ID = $(this).attr('id'); 
		var Title = $(this).data('title'); 
		$.fileup('certificate-pdf', 'remove', '*');
		$("#certificate #btn-submit").prop('disabled', false);
		$("#modalsign").modal("show");
		$("#modalsign .modal-title.title-add").show();
		$("#modalsign #errors").hide();
		$("#modalsign #ID").val(ID);
		$("#modalsign #Title").val(Title);
		$("#modalsign #Label").html(Title);
		return false;
    });
	
	$(document).on("click", "#btnsave-certdata", function(){
		var doc = {};
		doc.idclient = $("#app-clientid").val();
		doc.idapp = $("#idapp").val();		
		doc.certificateNumber = $("#certificateNumber").val();
		doc.certificateIssueDate = $("#certificateIssueDate").val();
		doc.certificateExpiryDate = $("#certificateExpiryDate").val();
		$.post("ajax/ajaxHandler.php", {
		  rtype: "saveCertificateData",
		  uid: 0,
		  data: doc,
		}).done(function (data) {
		  var response = JSON.parse(data);
		 if (response.data.errors) {
			$("#frmCertificateData #errors").html('<ul>'+response.data.errors+'</ul>' ).show();
			return;
		  }
		  else {
			 $("#frmCertificateData").notify( "Data saved successfully.", { position:"top right", className: "success" });
			 }			  
			 
		  
		});
		return false;
    });
	
    $(document).on("click","#modalsign #btn-submit",function() {
		$(this).prop('disabled', true);
		if ($("#certificate_signed_filename").val() == '') {
			$("#modalsign #errors").show().html('Please select a PDF file.');
			$(this).prop('disabled', false);
		}
		else {
			jQuery.fileup('certificate-pdf', 'upload', '*');
		}
		return false;
    });

	$("#certificate #filter-actions-deleted").click(function(e) {
	  var table = $('#table_certificate').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$("#certificate #btn-search").click(function(e) {
	  var table = $('#table_certificate').DataTable(); 
	  table.ajax.reload( null, false );
	});

	$("#certificateIssueDate").datetimepicker({ format: "DD/MM/YYYY" });
	$("#certificateExpiryDate").datetimepicker({ format: "DD/MM/YYYY" });	

	if ($("#certificate-pdf").length) {
	$.fileup({
		url: '/fileupload/partials/certificate.php',
		inputID: 'certificate-pdf',
		queueID: 'certificate-pdf-queue',
        dropzoneID: 'certificate-pdf-dropzone',
		extraFields: {idclient: jQuery("#app-clientid"), idapp: jQuery("#idapp"), Signature: jQuery("#modalcertificate input[name=Signature]:checked"), Title: jQuery("#modalcertificate #Title"), Comments: jQuery("#modalcertificate #Comments")},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		onSelect: function(file) {
			$("#modalcertificate #errors").hide();
			$("#certificate_filename").val(file.name);			
		},
		onRemove: function(total, file_number, file) {
			$("#certificate_filename").val("");
		},
		onSuccess: function(response, file_number, file) { 
			var table = $('#table_certificate').DataTable(); 
			table.ajax.reload( null, false );
			$("#modalcertificate").modal("hide");			
		},
		onError: function(event, file, file_number, response) {
			$.fileup('certificate-pdf', 'remove', '*');
			$("#modalcertificate #btn-submit").prop('disabled', false);
			$("#modalcertificate #errors").show().html(response);
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
 