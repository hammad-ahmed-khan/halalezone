$(document).ready(function () {
  var date = moment().millisecond(0).second(0).minute(0).hour(0);
  $("#AuditDate1").datetimepicker({
    format: "DD/MM/YYYY",
    minDate: date,
    daysOfWeekDisabled: [0, 6],
  });
  $("#AuditDate2").datetimepicker({
    format: "DD/MM/YYYY",
    minDate: date,
    daysOfWeekDisabled: [0, 6],
  });
  $("#AuditDate3").datetimepicker({
    format: "DD/MM/YYYY",
    minDate: date,
    daysOfWeekDisabled: [0, 6],
  });
  //	$("#ProposedDate").datetimepicker({format : "YYYY-MM-DD", minDate: date});

  $("#AuditDate1,#AuditDate2,#AuditDate3").on("click", function (e) {
    //getDisabledDates();
    $("#dates #errors").html("").hide();
    //$("#AuditDate1").data("DateTimePicker").disabledDates(datesForDisable)
    //$("#AuditDate2").data("DateTimePicker").disabledDates(datesForDisable)
    //$("#AuditDate3").data("DateTimePicker").disabledDates(datesForDisable)
  });

  $("#AuditDate1,#AuditDate2,#AuditDate3").on("dp.change", function (e) {
    $("#dates #errors").html("").hide();
    $(this).parent().find('input[name="ApprovedDate1"]').val($(this).val());
  });

  $("#dates #btn-submit").on("click", function (e) {
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.AuditDate1 = $("#AuditDate1").val();
    doc.AuditDate2 = $("#AuditDate2").val();
    doc.AuditDate3 = $("#AuditDate3").val();
    doc.PreferredLanguage = $("#PreferredLanguage").val();
    doc.EnglishAcceptable = $("input[name='EnglishAcceptable']:checked").val();

    $.post("ajax/ajaxHandler.php", {
      rtype: "saveAuditDates",
      uid: 0,
      data: doc,
    }).done(function (data) {
      var response = JSON.parse(data);
      if (response.status == 0) {
        $("#dates #errors").html(response.statusDescription).show();
        return;
      }
      $("#dates #errors").html("").hide();
      $("div#dates").notify("Dates saved successfully.", {
        position: "top right",
        className: "success",
      });
      //$("#ingredGrid").jqGrid().trigger("reloadGrid");
    });
    return false;
  });

  $("#dates #btn-approve").on("click", function (e) {
    var ApprovedDate1 = $('input[name="ApprovedDate1"]:checked').val();
    //			var ApprovedDate2 = $('input[name="ApprovedDate2"]:checked').val();
    //			var ApprovedDate3 = $('input[name="ApprovedDate3"]:checked').val();
    var doc = {};
    doc.idclient = $("#app-clientid").val();
    doc.idapp = $("#idapp").val();
    doc.ApprovedDate1 = ApprovedDate1 ? ApprovedDate1 : "";
    doc.AuditDate1 = $("#AuditDate1").val();
    doc.AuditDate2 = $("#AuditDate2").val();
    doc.AuditDate3 = $("#AuditDate3").val();
    //doc.ApprovedDate2 = ApprovedDate2 ? ApprovedDate2 : "";
    //doc.ApprovedDate3 = ApprovedDate3 ? ApprovedDate3 : "";
    /*
 	  $.post("ajax/ajaxHandler.php", {
		  rtype: "approveAuditDates",
		  uid: 0,
		  data: doc,
		}).done(function (data) {
		  var response = JSON.parse(data);
		  if (response.status == 0) {
			$("#dates #errors").html(response.statusDescription).show();
			return;
		  }
		  var approvedText1 = "";
		   console.log(response.data.approved_by);
		   approvedText1= approvedText.replace('[approvedBy]',response.data.approved_by)
			ApprovedDate1=response.data.approved_date1f;
			ApprovedDate1F=response.data.approved_date1f;
			//ApprovedDate2=response.data.approved_date2;
			//ApprovedDate3=response.data.approved_date3;
			
			 $("#dates #errors").html("").hide();
			  $("div#dates").notify( "Date approved successfully.", { position:"top right", className: "success" });
			  
			  //if (ApprovedDate1 == $('#ApprovedDate1').val()) {
				approvedText1 = approvedText.replace('[approvedDate]', ApprovedDate1F);
				  $(".SelectedDate").html(approvedText1);
			  //}
			  //else if (ApprovedDate1 == $('#ApprovedDate2').val()) {
				  //approvedText = approvedText.replace('[approvedDate]', ApprovedDate1F);
///				  $(".SelectedDate").html(approvedText)
	//		  }
	//		  else if (ApprovedDate1 == $('#ApprovedDate3').val()) {
	//			  approvedText = approvedText.replace('[approvedDate]', ApprovedDate1F);
	//			  $(".SelectedDate").html(approvedText)
	//		  }
			   
			  //$("#ingredGrid").jqGrid().trigger("reloadGrid");
		});
		*/

    $.ajax({
      url: "ajax/ajaxHandler.php",
      method: "POST",
      data: {
        rtype: "approveAuditDates",
        uid: 0,
        data: doc,
      },
      beforeSend: function () {
        // Code to execute before the request is sent
        $.blockUI();
      },
      complete: function () {
        // Code to execute when the request is complete
        $.unblockUI();
      },
      success: function (data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
          $("#dates #errors").html(response.statusDescription).show();
          return;
        }
        var approvedText1 = "";
        console.log(response.data.approved_by);
        approvedText1 = approvedText.replace(
          "[approvedBy]",
          response.data.approved_by
        );
        ApprovedDate1 = response.data.approved_date1f;
        ApprovedDate1F = response.data.approved_date1f;

        $("#dates #errors").html("").hide();
        $("div#dates").notify("Date approved successfully.", {
          position: "top right",
          className: "success",
        });

        approvedText1 = approvedText.replace("[approvedDate]", ApprovedDate1F);
        $(".SelectedDate").html(approvedText1);
        ///changeAppState("checklist");
        var separator = window.location.href.indexOf("?") > -1 ? "&" : "?";
        var randomNumber = Math.floor(Math.random() * 100) + 1;
        window.location.href =
          window.location.href + separator + "_n=" + randomNumber;
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // Handle any errors that occur during the request
      },
    });

    return false;
  });
});
