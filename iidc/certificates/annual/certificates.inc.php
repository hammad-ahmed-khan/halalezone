<?php if (!defined("_HQC_")) {
	exit();
}; ?>
<?php if (isset($_SESSION['offid']) && (!isset($_GET['offid']) || $_GET['offid'] == '')) {
	$_GET['offid'] = $_SESSION['offid'];
};
?>
<style>
	td.status {
		/* white-space: nowrap */
	}

	td.status b {
		display: inline-block;
		width: 100px;
		white-space: nowrap
	}

	#searchHead td input {
		width: 99%;
		padding: 4px 10px;
	}

	#searchAnnualCertificates select {
		margin: 5px 0px
	}

	.load_popup {
		cursor: pointer
	}

	.load_popup:hover {
		color: red
	}

	fieldset {
		background: lightyellow;
		border: 1px solid darkgrey;
		border-radius: 5px;
	}

	.remarks {
		position: relative;
		background: lightyellow;
		border: 1px solid darkgrey;
		min-height: 20px;
		padding: 10px;
	}

	i.fas.fa-paperclip {
		color: darkorange !important;
	}

	.remarks i.fas.fa-paperclip {
		right: 0px !important;
		top: -10px;
		position: absolute;
		color: blue;
	}

	.remarks i.fa.fa-trash-alt {
		right: 5px !important;
		bottom: 5px;
		position: absolute;
		color: red;
		font-size: 12px !important;
	}

	#halal_standards {
		display: none;
	}

	.future,
	.future i {
		color: green;
	}

	.future {
		border: 1px solid green;
	}

	fieldset.old_versions {
		max-height: 100px;
		overflow: auto;
	}

	fieldset.old_versions ul {
		padding: 0px;
		margin: 0px;
	}

	fieldset.old_versions li {
		white-space: nowrap !important;
	}

	fieldset.old_versions li i.far.fa-file-pdf {
		font-size: 12px !important;
	}

	td.actions * {
		display: inline-flex;
	}
</style>
<script type="text/javascript">
	$("#page_title").html("Annual Certificate");

	function deleteCert(crtNr) {
		if (confirm("Are you sure Delete selected Certificate") == 1) {
			var time = new Date().getTime();
			$.post("certificate_save.php?tm=" + time, {
					act: "delete",
					crtNr: crtNr
				},
				function(data) {
					if (data != "") {
						if (data.indexOf('success') > -1) {
							document.location = document.location.href
						} else {
							alert(data);
						}
					}
				});
		}
	}

	function document_ready() {
		$(".crtDocNr").css({
			"cursor": "pointer"
		});
		$('#certificatesA tr').bind('mouseenter', function() {
			$('#crtId').val($(this).attr('data-crtNr'));
		});
		var selectedCrtNr = null;
		$('#certificatesA .crtDocNr').bind('click', function() {
			$('#crtDocNr').val($(this).attr('data-crtDocNr'));
			$('#crtDocNr').css({
				"width": $(this).width()
			});
			var position = $(this).position();
			$("#fixDocNrDiv").css({
				"left": position.left + "px",
				"top": position.top + "px",
				"display": "block"
			});
			jQuery("#crtDocNr").focus()
			selectedCrtNr = jQuery(this)
		});
	}
	var itemNr
	var clid;
	var orderBy = 'certificate_nr';
	var ascDsc = 'DESC';
	var srearchQ = '';
	var searchField = '';
	var st = 0;
	var lmt = 50;
	var startLoading = false;;

	async function deleteMemo(crtNr) {
		await confirm_message('Remove memo?');
		jQuery.post('certificate_save.php', {
			act: 'delete_memo',
			crtNr: crtNr
		}).done(function(data) {
			jQuery("#remark_" + crtNr).parent("div").remove();
		})
	}

	function loadCertificates(start) {
		jQuery("#st").val(start);
		jQuery("#lmt").val(lmt);
		st = start;
		jQuery.post('load_certificates.php', jQuery("#searchAnnualCertificates").serialize()).done(function(data) {
			if (data.trim().length > 0) {
				if (jQuery("#certificateItemsLoading")) {
					jQuery("#certificateItemsLoading").remove();
				}
				if (data.toLowerCase().indexOf("error:") > -1) {
					//jQuery('#searchHead').css("display", "none")
					jQuery("#certificateItems").html('<tr><td colspan="7" style="text-align:center;color:red">' + data + '</td></tr>')
				} else {
					if (jQuery("#exportExcel").val() == "yes") {
						doExportCertificates(data);
						return false;
					} else if (start == 0)
						jQuery("#certificateItems").html(data);
					else
						jQuery("#certificateItems").append(data);
					//jQuery('#searchHead').css("display", "table-row");
					jQuery(".load_popup").unbind('click');
					post_links();
					do_document_ready();
					load_popup();
					//doSearch('#searchHead', '#certificateItems', '#fixDocNrDiv');
					startLoading = true;
				}
			}
		});
	}

	function searchInvoicedCertificates() {
		srearchQ = '';
		searchField = '';
		if (jQuery("#srearchQ").val().trim() != '') {
			srearchQ = jQuery("#srearchQ").val();
			searchField = jQuery("#searchField").val()
			loadCertificates(0);
		}
		return false;
	}

	function showSearchInputs(val) {
		jQuery("#loadLimit").val('all');
		jQuery("#subSearchQ").val('');
		jQuery("#subSearchField").val('');
		jQuery(".search").val('');
		jQuery("#searchAnnualCertificates input[type='text'],#halal_standards").val('')
		jQuery("#searchAnnualCertificates span,#halal_standards,#showSearchInputs").css("display", "none")
		jQuery("#searchAnnualCertificates input[type='submit']").css("display", "none")

		if (val == 'crtNr' || val == 'company_name') {
			jQuery("#searchAnnualCertificates span#srearchQ").css("display", "inline-block")
			jQuery("#searchAnnualCertificates input[type='submit']").css("display", "inline-block")
		}
		if (val == 'date_of_expiry' || val == 'order_date') {
			jQuery("#searchAnnualCertificates span#srearchDates").css("display", "inline-block")
			jQuery("#searchAnnualCertificates input[type='submit']").css("display", "inline-block")
		}
		if (val == 'reference_standards') {
			jQuery("#halal_standards").css("display", "inline-block");

			//check if halal_standards has onchange property
			if (jQuery("#halal_standards").attr('onchange') == undefined)
				jQuery("#halal_standards").attr("onchange", 'loadCertificates(0)');
		}
		if (val == '' || val == 'new_certificates' || val == 'all_expired' || val == 'all_certificates' || val == 'certificates_not_sent')
			loadCertificates(0)
	}

	//TODO add export to excel to new system
	function exportCertificates() {
		st = jQuery("#st").val();
		jQuery("#exportExcel").val("yes");
		loadCertificates(0);
	}

	function doExportCertificates(expItems) {
		//expItems = JSON.stringify(certificates);
		jQuery("#exportExcel").val("no");
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "export_excel_file.php");
		var itemInput = document.createElement("input");
		itemInput.setAttribute("type", "hidden");
		itemInput.setAttribute("name", "items");
		itemInput.setAttribute("value", expItems);
		form.appendChild(itemInput);
		document.body.appendChild(form);
		form.submit();
		form.remove();
		// location.reload();
	}

	function meetingEmailResults(crtNr) {
		jQuery("#status_" + crtNr).html("Verification process started");
	}
</script>

<?php
/*if (!isset($_SESSION['offid']) or $_SESSION['offid'] == '0') { ?>
	<center>
		<select size="1" name="offid" onchange="document.location='<?php echo $prog_www; ?>/certificates/annual/?inc=certificates&offid='+this.value;">
			<option value="">Select office</option>
			<?php
			$offices = $amdb->query("SELECT * FROM offices WHERE status != 'deleted'");
			if (count($offices) > 0) {
				include "$hcp_path/config/countries.code.php";
				$nr = 1;
				foreach ($offices as $office) { ?>
					<option value="<?php echo $office['offid']; ?>" <?php echo (isset($_GET['offid']) && $_GET['offid'] == $office['offid']) ? 'selected' : ''; ?>><?php echo $country[$office['office_country']]; ?> - <?php echo $office['office_name']; ?></option>
			<?php
				}
			}
			?>
		</select>

	<?php
} else {
	$_GET['offid'] = $_SESSION['offid'];
}
*/
$_GET['offid'] = $_SESSION['offid'];
if (!isset($_GET['offid']) or trim($_GET['offid']) == '') {
	return;
}
?>
<?php if (in_array("ac_request_certificates", $user_permissions) or $_SESSION['user_type'] == "admin" or $user_type == 'hqc_office') { ?>
	<div style="text-align: center;"><a href='?inc=certificate_add_edit&offid=<?php echo $_GET['offid']; ?>' class="button">Issue Annual Certificate</a></div>
<?php }; ?>

<table class="alternateOn" style="min-width:100% !important" id="annualCertificates">
	<thead>
		<tr class="alternateOff">
			<td colspan=8 class="sub_title">
				<input type="button" value="Export to excel" style="float: right;" onclick="exportCertificates()" />
				<div style="float:right;">
					<form id="searchAnnualCertificates" onsubmit="loadCertificates(0);return false">
						<input type="hidden" name="exportExcel" id="exportExcel" value="no" />
						<input type="hidden" name="st" id="st" value="0" />
						<input type="hidden" name="lmt" id="lmt" value="50" />
						<input type="hidden" name="offid" value="<?php echo $_GET['offid']; ?>" />
						<input type="hidden" name="subSearchField" id="subSearchField" value="" />
						<input type="hidden" name="subSearchQ" id="subSearchQ" value="" />
						<span style="color:red;font-weight:normal">For more search options use <i class="fas fa-arrow-right" style="color:red"></i></span>
						Search for:
						<select size="1" name="searchField" id="searchField" onchange="showSearchInputs(this.value)">
							<option value="all_certificates">All certificate</option>
							<option value="new_certificates">Certificate in process</option>
							<option value="crtNr">Certificate Nr</option>
							<option value="company_name">Company Name</option>
							<option value="date_of_expiry">Expiry date</option>
							<option value="order_date">Order date</option>
							<option value="certificates_not_sent">Certificates not sent</option>
							<option value="all_expired">All expired certificates</option>
							<option value="reference_standards">Reference standards</option>
						</select>
						<span style="display:none" id="srearchQ">
							<input type="text" name="srearchQ" placeholder="Search term" />
						</span>
						<span style="display:none" id="srearchDates">
							<input type="text" name="fromDate" placeholder="From date" class="date" />
							<input type="text" name="toDate" placeholder="To date" class="date" />
						</span>
						<input type="submit" value="Search" style="display:none" />
						<select name="limit" id="loadLimit" size="1" onchange="loadCertificates(0);">
							<option value="3">Last 3 months</option>
							<option value="6">Last 6 months</option>
							<option value="12">Last 12 months</option>
							<option value="18">Last 18 months</option>
							<option value="all" selected>ALL Certificates</option>
						</select>
						<?php echo get_halal_standards('select'); ?>
					</form>
				</div>
				<b style="font-size:16px;margin: 10px !important; display: block;">Annual <FONT COLOR=RED>CERTIFICATES </FONT></b>
			</td>
		</tr>
		<tr id="headerTh">
			<th>Nr</th>
			<th data-id="certificate_nr">Certificate Nr</th>
			<th data-id="company_name">Company / Country / City</th>
			<th id="thDates" data-id="issue_expiry">Issue / Expiry</th>
			<th data-id="ordered_on" style="width:200px">Certificate Request</th>
			<th id="thStatus" data-id="status" style="width:200px">Certificate Status</th>
			<?php if (in_array("ac_reissue_remove", $user_permissions) or $_SESSION['user_type'] == "admin" or $_SESSION['offid'] != '0') { ?>
				<th id="thAction" data-id="action" style="width:90px">Action</th>
			<?php }; ?>
		</tr>
		<tr id="searchHead" class="alternateOff" style="background:#eee">
			<th></th>
			<td><input type="text" id="crtNr" class="search" /></td>
			<td><input type="text" id="company_name" class="search" /></td>
			<td><input type="text" id="issue_expiry" class="search" style="display:none" /></td>
			<td><input type="text" id="ordered_on" class="search" style="display:none" /></td>
			<td><input type="text" id="status" class="search" style="display:none" /></td>
			<td></td>
		</tr>
	</thead>
	<tbody id="certificateItems">
		<tr id="certificateItemsLoading">
			<td colspan="12" style="text-align:center;vertical-align:middle;"><img src="<?php echo $prog_www; ?>/images/loading.gif" style="height:50px;" /></td>
		</tr>
	</tbody>
</table>
<script>
	$("#headerTh th").each(function(index, element) {
		if (jQuery(this).data('id')) {
			jQuery(this).append('<i class="fa fa-caret-down"></i><i class="fa fa-caret-up"></i>');
			jQuery(this).css('white-space', 'nowrap')
		}
	});
	$("#headerTh th i.fa").click(function(e) {
		$("#headerTh th i.fa").css('color', 'black');
		$(this).css('color', 'red');
		orderBy = (jQuery(this).parent('th').data('id'))
		if (jQuery(this).hasClass('fa-caret-up'))
			ascDsc = 'ASC'
		else
			ascDsc = 'DESC';
		st = 0;
		sortList("#certificateItems", orderBy, ascDsc);
		//jQuery("#fixDocNrDiv").css("display","none")
		//jQuery("#searchHead input").val('')
		//loadCertificates(0);
	});

	// jQuery(window).scroll(function() {
	// 	if (jQuery("#subSearchQ").val().trim() != '' || jQuery("#srearchQ").val().trim() != '') {
	// 		return false;
	// 	}
	// 	windowFromBottom = jQuery(document).height() - jQuery(document).scrollTop();
	// 	if (windowFromBottom < jQuery(window).height() * 2 && startLoading) {
	// 		loadCertificates(st + lmt);
	// 		startLoading = false;
	// 	}
	// });
	loadCertificates(0);
	jQuery(".search").focus(function() {
		jQuery("#searchField").val('all_certificates');
		jQuery(".search").val('');
		if (jQuery("#subSearchQ").val() != '' || jQuery("#subSearchField").val() != '') {
			jQuery("#subSearchQ").val('');
			jQuery("#subSearchField").val('');
			loadCertificates(0);
		}
	});

	jQuery(".search").keyup(function() {
		if (this.value.length > 2) {
			jQuery("#searchField").val(jQuery(this).attr('id'));
			jQuery("#subSearchQ").val(jQuery(this).val());
			jQuery("#subSearchField").val(jQuery(this).attr('id'));
			loadCertificates(0);
		} else {
			jQuery("#searchField").val('all_certificates');
			jQuery("#subSearchQ").val('');
			jQuery("#subSearchField").val('');
			loadCertificates(0);
		}
	});
</script>