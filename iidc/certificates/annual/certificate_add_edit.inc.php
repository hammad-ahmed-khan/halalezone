<?php
if (!defined("_HQC_")) {
	exit();
};
//show php errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_GET['act']))
	$_GET['act'] = 'add';
?>
<script type="text/javascript">
	$("#page_title").html("Annual Certificate (Request / Update)")
</script>
ver: 01 14/052025
<?php
if ($_SESSION['user_type'] == 'client' and !isset($_REQUEST['offid'])) {
	if ($userOffices = $amdb->get_results("SELECT offid,office_name FROM offices WHERE FIND_IN_SET($_GET[clid],clients) AND status='active'")) { ?>
		<center> <b>Request the certificate from:</b>
			<select size="1" name="office" onchange="document.location='index.php?inc=certificate_add_edit&act=add&clid=<?php echo $_GET['clid']; ?>&offid='+this.value">
				<option value="">Please select Office</option>
				<?php foreach ($userOffices as $office) { ?>
					<option value="<?php echo $office['offid']; ?>"><?php echo $office['office_name']; ?></option>
				<?php }; ?>
			</select>
		</center>
	<?php
		return;
	} else {
		$_GET['offid'] = 0;
	}
	?>
<?php
}
?>
<style>
	#approval div {
		margin-bottom: 5px
	}

	#approval span {
		font-weight: bold;
		display: inline-block;
		width: 110px
	}

	#certTbl textarea {
		width: 95%
	}

	ol#productsOl {
		margin-left: 30px
	}

	ul#productsOl li {
		list-style: decimal !important;
		margin-left: 20px;

	}

	ol#sortableTitles {
		margin: 10px 0px;
		padding: 0px;
		overflow: hidden
	}

	ol#sortableTitles li {
		float: left;
		background: #fff !important;
		padding: 5px;
		margin-right: 5px;
		border: 1px solid #bbb;
		border-radius: 5px;
	}

	ol#sortableTitles li input[type='text'] {
		padding: 5px !important
	}

	ol#sortableTitles li input[type='text']:last-child {
		width: 32px !important
	}

	ol#sortableTitles li input[type='text']:first-child {
		width: 100px !important
	}

	li.ui-sortable-handle b {
		background: #eee;
		display: block;
		padding: 2px;
		margin-bottom: 5px;
		width: 160px !important;
		position: relative;
		font-size: 11px !important;
	}

	li.ui-sortable-handle b:after {
		content: "\f0b2";
		font-family: "Font Awesome 5 Free";
		position: absolute;
		right: 5px;
		top: 5px;
		font-size: 14px;
		cursor: move;
	}

	.red {
		color: red
	}

	.fa-question-circle:hover {
		color: red
	}

	ul.categoriesUl li .fa-question-circle,
	ul.categoriesUl li .fa-angle-double-down {
		position: absolute;
		right: 20px;
		top: 2px;
		font-size: 16px !important;
	}

	.fa-angle-double-down {
		right: 5px;
	}

	ul.categoriesUl li {
		position: relative;
	}

	.categoriesUl li.category {
		padding: 2px;
	}

	i.fas.fa-exclamation-triangle {
		position: absolute;
		right: 40px;
		font-size: 14px !important;
	}

	.colorLabel {
		border-radius: 10px;
		display: inline-block;
		width: 40px;
	}

	.disabled {
		background: transparent;
	}

	.removeProduct {
		position: absolute;
		right: 10px;
		top: 0px
	}

	.removeProduct i,
	.removeProduct i span {
		font-size: 12px !important;
		color: firebrick;
	}
</style>
<script type="text/javascript">
	$("#page_title").html("Halal Certificate (Request / Update)");
	var prohibited = false;

	function checkAnnexSepareted(val) {
		jQuery("#annexSepareted,#DownLoadZip").css("display", val)
		if (val == 'block')
			jQuery("#DownLoadZip").css("display", 'inline-block')
	}
	var offid = <?php echo $_GET['offid']; ?>;

	async function crtDoAct(act) {
		document.addEditForm.crtDo.value = act;
		var reqs = ['products', 'clid', 'reference_standards', 'scope_of_certification', 'category', 'date_of_issue', 'date_of_expiry', 'initial_issue_date', 'signatory'];
		if (act == "preview") {
			// jQuery("#certificate_option_invoiced").removeAttr("data-required");
			jQuery("#future_action_when").removeAttr("data-required");
			// jQuery("#certificate_option_invoiced").parent().css("color", "")
			document.addEditForm.action = 'certificate.pdf.php';
			document.addEditForm.target = '_blank';
		} else {
			// jQuery("#certificate_option_invoiced").attr("data-required", "yes");
			if (jQuery("#send_by_email").is(":checked")) jQuery("#future_action_when").attr("data-required", "yes");
			document.addEditForm.action = 'certificate_save.php';
			document.addEditForm.target = '_blank';
			document.addEditForm.target = 'fIframe';
		}
		if (jQuery("#awarded_to_site").is(":checked")) {
			sitesSelected = jQuery("#manufacturingSites input[type='checkbox']:checked").length;
			if (sitesSelected != 1) {
				if (sitesSelected == 0)
					alert_message("Please select a manufacturing site");
				else
					alert_message("Please select only one manufacturing site");
				return false;
			}
		}
		checkForm(reqs);
		if (jQuery("#signatory").val() == '' || jQuery("#signatory").val() == 'Select Signatory') {
			//alert_message("Please select a signatory");
			//return false;
		}

		if (prohibited == true && jQuery("#certificate_option_prohibited").is(":checked") == false) {
			alert_message("There is a prohibited product in the selected item(s).");
			return false;
		}

		if ($(".product:checked").length > 0) {
			var selectedProducts = $('.product:checked').map(function() {
				return this.value;
			}).get().join(",");
			jQuery("#selectedProducts").val(selectedProducts);
		}

		if (post_this_form("#addEditForm")) {
			document.addEditForm.submit();

		}
	}

	function checkForm(reqs) {
		jQuery("#addEditForm").find("[data-required='yes']").removeAttr("data-required");
		reqs.forEach(function(item) {
			jQuery("#" + item).attr("data-required", "yes");
		});
	}

	function checkProductsCount() {
		checkedProducts = $(".product:checked").length;
		jQuery("#productsCount").html(checkedProducts);
		prohibited = false;
		if (checkedProducts > 0) {
			//find if there is any prohibited product
			$(".product:checked").each(function() {
				if ($(this).closest("li").find(".prohibited").length > 0) {
					prohibited = true;
					if (jQuery("#prohibitedConfirm").length == 0) {
						jQuery("#productTd").append("<div id='prohibitedConfirm' style='padding:5px;background:beige'><span style='color:red'>Prohibited product name(s) in the selected item(s).</span> Would you like to go ahead and process the certificate?<label><input type='checkbox' name='certificate_options[prohibited]' id='certificate_option_prohibited' value='yes'data-required='yes'/> Yes</label> </div>")
					}
				}
			})
		}

		if (prohibited == false) {
			jQuery("#prohibitedConfirm").remove();
		}
	}

	$(window).load(function(e) {
		$("#date_of_issue").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: dateFormat
		});
		$("#date_of_expiry").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: dateFormat
		});
		$("#status_sent_on").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: dateFormat
		});
		$("#status_recieved_on").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: dateFormat
		});
		jQuery(".product").on("click", function() {
			checkProductsCount();
		})
		checkProductsCount();
	});

	function nextYear() {
		jQuery("#surveillance").html("");
		var time = new Date().getTime();
		if (jQuery("#date_of_issue").val().trim() == '') {
			alert_message("Please select the date of issue");
			return false;
		}
		date_of_issue = jQuery("#date_of_issue").val();
		cert_validity = jQuery("#cert_validity").val();
		$.post(prog_www + "/config/date_conv.inc.php?tm=" + time, {
				"getNextYear": "true",
				"date_of_issue": date_of_issue,
				"cert_validity": cert_validity
			},
			function(data) {
				if (data != "") {
					//parse json and fill the data
					data = JSON.parse(data);
					jQuery("#date_of_expiry").val(data['date_of_expiry']);
					if (data['surveillance']) {
						for (var fld in data['surveillance']) {
							sDate = data['surveillance'][fld];
							jQuery("#surveillance").append(fld + ' surveillance: <input name="certificate_options[surveillance][]"  type="text" class="date" value="' + sDate + '"/> ');
						};
						rtfDate = data['Recertification'];
						jQuery("#surveillance").append(' Recertification:' + '<input name="certificate_options[recertification][]"  type="text" class="date disabled" value="' + rtfDate + '"/>');
					}
				};
			});
	}
	<?php if ($_SESSION['user_role'] != 'super_admin') { ?>

		function checkExpiryPeriod() {
			return false;
			iss = jQuery("#date_of_issue").val();
			exp = jQuery("#date_of_expiry").val();
			validity = jQuery("#certificateValidity").val();
			var time = new Date().getTime();
			jQuery("span#maxPeriod").removeClass("red");
			$.post(prog_www + "/config/date_conv.inc.php?checkPeriod=true&validity=" + validity + "&tm=" + time + "&iss=" + iss + "&exp=" + exp,
				function(data) {
					if (data != "") {
						nextYear(iss, "date_of_expiry");
						jQuery("span#maxPeriod").addClass("red");
					}
				});
		}
	<?php }; ?>

	function checProductBox(obj) {
		if (jQuery("#productsOl input[type='checkbox']").length) {
			jQuery("#productsOl input[type='checkbox']").prop("checked", obj.checked);
			checkProductsCount();
		}
	}

	function countChars(obj) {
		jQuery("#maxChars").html(jQuery(obj).attr("maxlength") - jQuery(obj).val().length)
	}

	function getSiteVersion(val) {
		<?php if (isset($_GET['clid'])) { ?>
			url = 'index.php?inc=certificate_add_edit&clid=<?php echo $_GET['clid']; ?>&act=<?php echo $_GET['act']; ?>&offid=<?php echo $_GET['offid']; ?><?php echo isset($_GET['crtNr']) ? '&crtNr=' . $_GET['crtNr'] : ''; ?>';
			window.location = url + val;
		<?php }; ?>
	}
	$(document).ready(function() {
		jQuery('.categoriesUl').css("width", jQuery('.categoriesUl').width() + 'px');
		$('.reference_standards').click(function(event) {
			jQuery("#referenceStandardsCount").removeClass("red");
			if ($(".reference_standards:checked").length > 5) {
				$(this).prop("checked", false);
				jQuery("#referenceStandardsCount").addClass("red");
				alert_message('Maximum 5 items (combined Halal standards)');
			}
		});
		$('.category').click(function(event) {
			jQuery("#categoriesCount").removeClass("red");
			if ($(".category:checked").length > 3) {
				$(this).prop("checked", false);
				jQuery("#categoriesCount").addClass("red");
				alert_message('Maximum 3 categories');
			}
		});
		$('.fa-question-circle').click(function(event) {
			catid = jQuery(this).data('id');
			$.post(prog_www + "/certificates/annual/get_info.php?act=getCategoryDescription&catid=" + catid,
				function(data) {
					if (data != "") {
						alert_message(data);
					}
				});
		})
	});

	function toggleCategory(cat) {
		if (jQuery('.cat_' + cat).is(":visible") == false)
			jQuery('.categoriesUl li.category').hide('slow');
		jQuery('.cat_' + cat).toggle('slow');
	}

	function checkOfficeSignature(offid) {
		jQuery('.officeSignature').checked = false;
		jQuery('.officeSignature').hide();
		if (document.getElementById('officeSignature_' + offid)) {
			document.getElementById('officeSignature_' + offid).style.display = 'inline-block';
		}

	}

	function copySiteAddress(obj) {
		var siteName = jQuery(obj).closest("li").find(".siteName").text();
		jQuery("#awarded_additional_title").val(siteName);
		var siteAddress = jQuery(obj).closest("li").find(".siteAddress").text();
		jQuery("#awarded_additional_text").val(siteAddress);
		jQuery("#insert_additional_title").prop("checked", true);
	}

	function clearAwardedAdditional() {
		jQuery("#awarded_additional_title").val('');
		jQuery("#awarded_additional_text").val('');
		jQuery("#insert_additional_title").prop("checked", false);
	}
</script>
<?php
include "$prog_path/config/connect.inc.php";
if ($_SESSION['user_type'] == 'admin') {
	$hqc_user = $amdb->get_row("SELECT permissions FROM hqc_admin_users WHERE uid = '$_SESSION[uid]'");
	$hqc_user_permissions = explode(",", str_replace('"', '', $hqc_user['permissions']));
}

$company = "";
$scope_of_activities = '';
$reference_number = 1;
$row = array();
$certificate_options = array();
$annex_options = array();
$php = array();
$revision = array();
if (isset($_GET['act']) and $_GET['act'] == 'reissue') {
	$act = "edit";
	$_GET['reissue'] = 'y';
}
if (isset($_SESSION['clid']))
	$clid = $_SESSION['clid'];
elseif (isset($_GET['clid']))
	$clid = $_GET['clid'];

if (isset($act) and $act == "edit") {
	if (isset($_GET['ver'])) {
		if ($certificate_ver = $amdb->get_row("SELECT item_content FROM hqc_versions WHERE verid='$_GET[ver]'")) {
			$row = unserialize($certificate_ver['item_content']);
		}
	} else {
		$row = $amdb->get_row("SELECT *,acms_halal_certificates.offid as offid FROM $tbl[prefix]_halal_certificates, companies where  $tbl[prefix]_halal_certificates.clid = companies.clid and $tbl[prefix]_halal_certificates.crtNr='$crtNr'");
	}
} elseif ($user_type == "client") {
	$row = $amdb->get_row("SELECT * FROM companies where  clid = '$clid'");
	$act = "add";
}

if ($row) {
	if ($_GET['act'] == 'edit' && $row['date_of_issue'] == 0) {
		if (is_array(decode_json($row['certificate_content']))) {
			foreach (decode_json($row['certificate_content']) as $key => $content) {
				$doi = 0;
				$exp = 0;
				$date_of_issue = strtotime(fix_date($content['data']['date_of_issue']));
				$date_of_expiry = strtotime(fix_date($content['data']['date_of_expiry']));
				if ($date_of_issue > $doi) {
					$doi = $date_of_issue;
					$row['date_of_issue'] = $doi;
				}
				if ($date_of_expiry > $exp) {
					$exp = $date_of_expiry;
					$row['date_of_expiry'] = $exp;
				}
			};
		}
	}

	if (isset($row['options']) and trim($row['options']) != '' and is_array(json_decode($row['options'], true)))
		$certificate_options = json_decode($row['options'], true);
	if (isset($row['annex_options']) and trim($row['annex_options']) != '' and is_array(json_decode($row['annex_options'], true)))
		$annex_options = json_decode($row['annex_options'], true);
	if (isset($row['revision']) and trim($row['revision']) != '' and is_array(json_decode($row['revision'], true))) {
		$revision = json_decode($row['revision'], true);
		$revision['insert'] = true;
	}
	$awarded_to = $row['company_name'];
	$company_country = $row['country1'];
	$scope_of_activities = $row['scope_of_activities'];
	$company = "<b>$row[company_name]</b><br>
		$row[street1]<br/>
		$row[zip1], $row[city1]<br />
		$row[country1]<br />
		<b>EEC No.:</b>$row[ec_number]";
	if ($active = $amdb->get_row("SELECT * FROM users WHERE clid='$clid' AND active='n'")) {
		$company .= "<br/><h2 style=\"color:red\">THIS CLIENT IS DELETED</H2>";
	}
} else {
	$awarded_to = '';
	$products = array();
	if ($productsCount = $amdb->get_results("SELECT clid,count(clid) AS prds FROM acms_hdcs_products
								WHERE  approved='y' and status = 'active'
								GROUP BY clid")) {
		foreach ($productsCount as $product) {
			$products[$product['clid']] = $product['prds'];
		}
	}
	$result = get_clients("companies.clid,companies.company_name,companies.scope_of_activities,companies.email1,companies.country1");
	if (count($result) > 0) {
		$company = '<select size=1 name="clid" style="max-width:400px" id="clid" name="clid" class="searchable" data-required="yes"><option value="">Select a company</option>';

		foreach ($result as $row) {
			if ($row['country1'] != 'Israel') {
				if (isset($clid) and $clid == $row['clid']) {
					$awarded_to = $row['company_name'];
					$company_country = $row['country1'];
					$scope_of_activities = $row['scope_of_activities'];
					$company .= "<option value='$row[clid]' selected=\"selected\">$row[company_name]</option>";
				} else {
					if (isset($products[$row['clid']]))
						$totPrds = $products[$row['clid']];
					else
						$totPrds = '0';
					$company .= "<option value='$row[clid]'>$row[company_name] ($totPrds products)</option>";
				}
			}
		}
		$company .= "</select>";
	}
	$act = "add";
}
if (isset($_SESSION['offid']) && $_SESSION['offid'] != 0)
	$_GET['offid'] = $_SESSION['offid'];

if (!isset($_GET['offid']))
	$_GET['offid'] = 0;
if ($user_type != "client") {
	if (!$template = $amdb->get_row("SELECT content,php,revision FROM office_certificate_templates WHERE offid='$_GET[offid]' and status='active' and type='annual'"))
		$template = $amdb->get_row("SELECT content,php,revision FROM office_certificate_templates WHERE offid='0' and status='active' and type='annual'");
}
?>
<center>
	<form action="" method="post" target="_blank" data-target="fIframe" id="addEditForm" name="addEditForm">
		<input type="hidden" name="act" id="act" value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reissue') ? "add" : $act ?>" />
		<input type="hidden" value="" name="crtDo" />
		<input type="hidden" value="certsList" name="afterPrint" id="afterPrint" />
		<input type="hidden" value="" name="products" id="selectedProducts" />
		<input type="hidden" data-check=".product" value=".product" data-min='1' data-error="Please select at least one product" />
		<input type="hidden" data-check=".reference_standards" data-min='1' data-error="Please select at least one Halal Reference standards" />
		<input type="hidden" data-check=".category" data-min='1' data-error="Please select at least one category" />
		<input type="hidden" value="<?php echo $_GET['offid']; ?>" name="offid" />
		<input type="hidden" value="<?php echo ($_SESSION['user_type'] == 'client') ? 'new' : 'active'; ?>" name="status" />
		<?php if (isset($_GET['verid'])) { ?>
			<input type="hidden" name="certificate_options[verid]" value="<?php echo $_GET['verid']; ?>" />
		<?php }; ?>
		<?php if (isset($_GET['stid'])) { ?>
			<input type="hidden" name="certificate_options[stid]" value="<?php echo $_GET['stid']; ?>" />
		<?php }; ?>
		<input type="hidden" id="approval_required" name="certificate_options[approval_required]" value="no" />
		<?php

		if ($user_type == "client" or $act == "edit" and !isset($_GET['reissue']))
			echo "<input type=\"hidden\" name=\"clid\" value=\"$clid\">";
		if (isset($act) and $act == "edit") {
		?>
			<input type="hidden" id="crtNr" name="crtNr" value="<?php echo $crtNr; ?>" />
			<?php if (trim($row['certificate_nr']) != '') { ?>
				<input type="hidden" name="certificate_nr" value="<?php echo $row['certificate_nr']; ?>" />
			<?php }; ?>
		<?php
		}
		if (isset($_GET['reissue'])) {
			echo "<input type=\"hidden\" name=\"clid\" value=\"$clid\"/>
<input type=\"hidden\" name=\"reissue\" value=\"y\"/>";
		}
		?>
		<h2 class="content_title">
			<center><?php echo (isset($_GET['act']) and $_GET['act'] == "edit") ? "Update" : ($_GET['act'] == "reissue" ? "Reissue" : "Issue") ?> Halal Certificate</center>
		</h2>
		<table id="certTbl" style="border:1px solid #EEE;min-width: 100%;" class="alternate">
			<tr>
				<th style="min-width:150px">Company:*</th>
				<td colspan="4">
					<?php
					echo $company;
					if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_GET[offid]'")) {
						if (isset($office['options']) && is_array(json_decode($office['options'], true))) {
							$options = json_decode($office['options'], true);
							if (isset($options['restricted_standards']))
								$restricted_standards = $options['restricted_standards'];
							else
								$restricted_standards = array();
						}
					}
					?>
					<?php if (!isset($_GET['clid'])) { ?>
						<script>
							function redirectToNewCertificate() {
								document.location.href = 'index.php?inc=certificate_add_edit&clid=' + document.getElementById("clid").value + '&act=add&offid=<?php echo $_GET['offid']; ?>';
							}
						</script>
						<button onclick="redirectToNewCertificate()" class="btn btn-primary" type="button" style="margin-left:10px;">New Certificate</button>
					<?php } ?>
				</td>
			</tr>

			<?php if (trim($awarded_to) != '') {  ?>
				<?php
				$certificate_validity = 1;
				$annual_permissions = array();
				if (trim($office['certificate_permissions']) != '' && is_array(json_decode($office['certificate_permissions'], true))) {
					$certificate_permissions = json_decode($office['certificate_permissions'], true);
					if (isset($certificate_permissions['annual']['validity']))
						$certificate_validity = $certificate_permissions['annual']['validity'];
					if (isset($certificate_permissions['annual'])) {
						$annual_permissions = $certificate_permissions['annual'];
					}
				}
				$certificate_validity = 4;
				?>
				<tr>
					<th>Manufacturing Sites:</th>
					<td colspan="3">
						<?php

						if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status!='deleted' AND clid='$_GET[clid]'")) {
							if (count($sites) > 0) {
								if ($act == 'edit')
									$selectedSite = explode(',', $row['manufacturing_site']);
								else
									$selectedSite = array();
						?>
								<ul style="padding:0px" id="manufacturingSites" class="alternateOn">
									<?php foreach ($sites as $site) {
										if (trim($site['site_address']) != '' and is_array(json_decode($site['site_address'], true))) {
											$site_address = json_decode($site['site_address'], true);
										}
									?>
										<li style="padding: 2px;;">
											<label><input type="checkbox" name="manufacturing_site[]" value="<?php echo $site['stid']; ?>" <?php echo ($act == 'edit' && in_array($site['stid'], $selectedSite)) ? 'checked' : ''; ?> /> <?php echo (trim($site['site_name']) != '') ? '<b class="siteName">' . $site['site_name'] . '</b>' : ''; ?> <span class="siteAddress"><?php echo (isset($site_address)) ? $site_address['street'] . ', ' . $site_address['zipcode'] . ' ' . $site_address['city'] . ', ' . $site_address['country'] : ''; ?></span></label> <i class="far fa-clone" style="color:darkcyan;margin-top:5px;font-size:12px !important;position:absolute;right:10px;" onclick="copySiteAddress(this)"><span>Copy into additional title</span></i>
										</li>
									<?php }; ?>
								</ul>
								<div style="margin-top:20px;border:1px solid #eee;padding:5px 10px; background: lightyellow;">
									<label><input type="checkbox" name="certificate_options[manufacturing_sites_OL]" <?php echo (isset($certificate_options['manufacturing_sites_OL'])) ? 'checked' : ''; ?> /> Manufacturing sites on one line</label>
									<br />
									<label><input type="checkbox" name="certificate_options[awarded_to_site]" <?php echo (isset($certificate_options['awarded_to_site'])) ? 'checked' : ''; ?> id="awarded_to_site" onclick="$('#awarded_as_site_label').css('display',this.checked?'inline-block':'none')" /> Awarded to Manufacturing site</label>

									<label id="awarded_as_site_label" style="display: <?php echo (isset($certificate_options['awarded_to_site'])) ? 'inline-block' : 'none'; ?> ;"><input type="checkbox" name="certificate_options[awarded_as_site]" <?php echo (isset($certificate_options['awarded_as_site'])) ? 'checked' : ''; ?> id="awarded_as_site" /> Print company address as Manufacturing site address</label>
								</div>
						<?php };
						}; ?>
					</td>
				</tr>
			<?php }; ?>
			<?php if (isset($_GET['clid']) and trim($_GET['clid']) != '') { ?>

				<?php
				if ($sitesData = $amdb->get_results("SELECT companies_production_sites.*,acms_hdcs_products.site,acms_hdcs_products.prdid FROM companies_production_sites
									LEFT JOIN  acms_hdcs_products ON acms_hdcs_products.site = companies_production_sites.stid
									where  companies_production_sites.clid = '$_GET[clid]' and companies_production_sites.status != 'deleted' and acms_hdcs_products.site!='0' AND acms_hdcs_products.status='active' GROUP BY acms_hdcs_products.site")) {
					if (isset($sitesData) and count($sitesData) > 0) {
						$selectFromSites = array();
						foreach ($sitesData as $site) {
							if (trim($site['site_name']) != '') {
								$selectFromSites[$site['stid']] = $site['site_name'];
							} else {
								if (is_array(json_decode($site['site_address'], true))) {
									$site_address = json_decode($site['site_address'], true);
									$selectFromSites[$site['stid']] = $site_address['street'];
								}
							}
						}
						asort($selectFromSites);
					}
				}

				if ($products_version  = $amdb->get_results("SELECT verid,version_name FROM companies_products_version WHERE clid = '$_GET[clid]' and status != 'deleted'  ORDER BY version_name ASC")) {
					$versions = array();
					if ($_GET['act'] != 'edit') {
						$whr = " AND status='active'";
					} else {
						$whr = " AND status='active'";
					}
					foreach ($products_version as $version) {
						if ($versionsData = $amdb->get_row("SELECT prdid,versions FROM acms_hdcs_products WHERE FIND_IN_SET($version[verid],versions) AND clid = '$_GET[clid]' $whr")) {
							$versions[$version['verid']] = $version['version_name'];
						}
					}
				}
				?>
				<tr>
					<th>Products:* <input type="checkbox" onclick="checProductBox(this)" /></th>
					<td colspan="4" id="productTd">
						<?php
						if ($act == 'edit' or $act == "reissue")
							$products = explode(',', $row['products']);
						else
							$products = array();
						if (isset($clid)) {
							$whr = '';
							if (isset($_GET['stid']) and trim($_GET['stid']) != '') {
								$whr = "and acms_hdcs_products.site='$_GET[stid]'";
							}
							if (isset($_GET['verid']) and trim($_GET['verid']) != '') {
								$whr = "and FIND_IN_SET($_GET[verid],versions)";
							}

							$whr .= " AND acms_hdcs_products.status='active'";
							if (($_GET['act'] == 'edit' or $_GET['act'] == 'reissue')) {
								$oldProducts = $amdb->get_results("SELECT * FROM acms_hdcs_products where prdid IN ($row[products]) AND approved='y' AND acms_hdcs_products.status='deleted' ORDER BY prdid ASC");
							}

							$resultPrd = $amdb->get_results("SELECT * FROM acms_hdcs_products where  approved='y' and clid = '$_REQUEST[clid]' $whr ORDER BY prdid ASC");

							if (isset($_GET['oldProducts']) && isset($oldProducts) && count($oldProducts) > 0) {
								$resultPrd = array_merge($oldProducts, $resultPrd);
							}

							if (count($resultPrd) == 0) {
								echo "<div style='color:red'>No products found for this company</div>";
							}
						?>
							<?php if (isset($selectFromSites) or isset($versions) > 0) { ?>
								<select name="productionSiteVersion" size="1" onchange="getSiteVersion(this.value)">
									<option value="">All products</option>
									<?php if (isset($oldProducts) or isset($_GET['oldProducts'])) { ?>
										<option value="&oldProducts=1" <?php echo (isset($_GET['oldProducts']) && $_GET['oldProducts'] == 1) ? 'selected' : ''; ?>>All Including deleted products</option>
									<?php }; ?>
									<?php if (isset($selectFromSites) and count($selectFromSites) > 0) { ?>
										<?php foreach ($selectFromSites as $stid => $sitename) { ?>
											<option value="&stid=<?php echo $stid; ?>" <?php echo (isset($_GET['stid']) && $_GET['stid'] == $stid) ? 'selected' : ''; ?>>Site: <?php echo $sitename; ?></option>
										<?php }; ?>
									<?php }; ?>
									<?php if (isset($versions) and count($versions) > 0) { ?>
										<?php foreach ($versions as $verid => $version_name) { ?>
											<option value="&verid=<?php echo $verid; ?>" <?php echo (isset($_GET['verid']) && $_GET['verid'] == $verid) ? 'selected' : ''; ?>>Version: <?php echo $version_name; ?></option>
										<?php }; ?>
									<?php }; ?>
								</select>
							<?php }; ?>
							<a href="/company/products/index.php?inc=products_list&clid=<?php echo $_GET['clid']; ?>&offid=<?php echo $_GET['offid']; ?>&gb=1" onclick="set_session_url('goBack','Annual certificate')">Manage Products</a> (Total selected products: <span id="productsCount"></span>)
							<?php if (isset($oldProducts) and count($oldProducts) > 0 && !isset($_GET['oldProducts'])) { ?>
								<div style="color:darkred;margin:5px 0"><i class="fas fa-exclamation-triangle" style="position: inherit;color: red;"></i> There are one or more products are deleted from the system. To reuse them again select (All including deleted products). </div>
						<?php };
							if (isset($resultPrd) and count($resultPrd) > 0) {
								$selected_products = array();
								$prohibited_products = array();
								$doubles = 0;
								echo "<ul style=\"padding:10px;max-height:250px;overflow:auto;margin:0px\" id=\"productsOl\">";
								foreach ($resultPrd as $rowPrd) {

									$product_item = $rowPrd['article_nr'] . $rowPrd['product_name'] . $rowPrd['description'];
									$liStyle = '';
									$double = false;
									if (!in_array($product_item, $selected_products) && trim($rowPrd['product_name']) != '') {
										$selected_products[] = $product_item;
									} else {
										$liStyle = "color:red;";
										$double = true;
										if (isset($_GET['verid']))
											$doubles++;
									}
									if (in_array($rowPrd['prdid'], $products))
										$checked = " checked";
									else
										$checked = "";
									if ($double == false) {
										echo "<li style=\"position:relative; $liStyle\">";
										if (!strstr(strtolower($rowPrd['product_name']), 'halal') && isset($rowPrd['prohibited']) && trim($rowPrd['prohibited']) == 'yes') {
											echo "<i class='fas fa-exclamation-triangle prohibited' style='color:red'></i>";
											if ($badWord = explode('||', prohibited_words($rowPrd['article_nr'] . '||' . $rowPrd['product_name'], true))) {
												$rowPrd['product_name'] = $badWord[1];
												$rowPrd['article_nr'] = $badWord[0];
											}
										}
										echo "<label><input type=\"checkbox\" class=\"product\" data-name=\"product[$rowPrd[prdid]]\" value=\"$rowPrd[prdid]\" $checked/> $rowPrd[product_name]";
										if (trim($rowPrd['article_nr']) != "")
											echo " (" . clean_string($rowPrd['article_nr']) . ")";
										if (trim($rowPrd['description']) != "")
											echo " - " . clean_string($rowPrd['description']);

										echo "</label>";
										echo "</li>";
									} elseif (isset($_GET['verid'])) {
										echo "<li style=\"position:relative;\" id=\"product_$rowPrd[prdid]\" class=\"double\">";
										echo "<label><input type=\"checkbox\" class=\"product\" data-name=\"product[$rowPrd[prdid]]\" value=\"$rowPrd[prdid]\" $checked/> $rowPrd[product_name]";
										if (trim($rowPrd['article_nr']) != "")
											echo " (" . clean_string($rowPrd['article_nr']) . ")";
										if (trim($rowPrd['description']) != "")
											echo " - " . clean_string($rowPrd['description']);
										echo "</label>";
										echo "<span style=\"color:red\"> (Duplicate product)</span>";
										echo "<span class=\"removeProduct\"><i class=\"fas fa-times\" onclick=\"removeProduct($rowPrd[prdid])\"><span>Remove product</span></i></span>";
										echo "</li>";
									}
								}
								echo "</ul>";
								if (isset($doubles) && $doubles > 0) {
									echo "<div style=\"color:red;padding:2px 0;position:relative\">There are $doubles duplicate products. Please remove the duplicates from the product list.<span class=\"removeProduct\"><i class=\"fas fa-times\" onclick=\"removeProduct('*')\"><span>Remove all duplicates</span></i></span></div>";
								}
							}
						}
						?>
					</td>
				</tr>
				<tr>
					<th>Reference halal standards:* </th>
					<td colspan="3">
						<?php
						if (isset($row['reference_standards']) && is_array(json_decode($row['reference_standards'], true)))
							$stnids = json_decode($row['reference_standards'], true);
						else
							$stnids = array();

						if ($act == 'add')
							$stid = '';
						?>
						<ul id="halalStandards" style="padding: 10px;margin:0px;height:200px;overflow:auto" class="alternateOn">
							<?php
							$standards = array();

							if (count($stnids) > 0)
								$whr = "OR stnid IN (" . implode(',', $stnids) . ")";
							else
								$whr = '';
							$standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE status='active' $whr ORDER BY code ASC");

							if (isset($_SESSION['user']) && ($_SESSION['user']['uid'] == 4 or $_SESSION['user']['uid'] == 5) or (isset($options['print_jakim']) && $options['print_jakim'] == 'yes')) {
								$SM_Pass = true;
							} else {
								$SM_Pass = false;
							}

							foreach ($standards as $standard) {
							?>
								<li><label>
										<input type="checkbox" name="reference_standards[]" class="reference_standards" value="<?php echo $standard['stnid']; ?>" <?php echo (in_array($standard['stnid'], $stnids)) ? 'checked' : ''; ?> data-standard="<?php echo $standard['code']; ?>" data-org="<?php echo $standard['organisation']; ?>" /><?php echo $standard['code']; ?>: <?php echo $standard['description']; ?></label></li>
							<?php };	?>
						</ul>
						<i style="padding-left:10px" id="referenceStandardsCount">Maximum 5 items</i>
					</td>
				</tr>
				<tr>
					<th>Scope of certification:*</th>
					<td colspan="3">
						<textarea name="scope_of_certification" id="scope_of_certification" style="width:95%;height:80px" maxlength="500" onkeyup="countChars(this)" data-required="yes"><?php echo ($act == 'edit' or isset($_GET['reissue'])) ? @$row['scope_of_certification'] : $scope_of_activities; ?></textarea>
						<div>Maximum characters:<span id="maxChars">500</span></div>
					</td>
				</tr>
				<tr>
					<th>Category:*</th>
					<td colspan="3">
						<?php
						if (($act == 'edit' or isset($_GET['reissue'])) && isset($row['category']) && is_array(json_decode($row['category'], true)))
							$catids = json_decode($row['category'], true);
						if ($act == 'add')
							$catid = '';
						?>
						<ul style="padding: 10px;margin:0px;" class="alternateOn categoriesUl">
							<?php
							if ($categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE status='active'")) {
								$category_name = '';
								foreach ($categories as $category) {
							?>
									<?php if ($category['category_name'] != $category_name) { ?>
										<li>
											<label>
												<input type="checkbox" name="category[]" class="category" value="<?php echo $category['catid']; ?>" <?php echo (isset($catids) && in_array($category['catid'], $catids)) ? 'checked' : ''; ?> />
												<?php echo '<b>' . $category['category'] . ": " . $category['category_name'] . "</b>";
												$category_name = $category['category_name'];
												?>
											</label>
											<i class="fas fa-angle-double-down" onclick="toggleCategory('<?php echo $category['category']; ?>')"></i>
										</li>
									<?php }; ?>
									<li class="cat_<?php echo $category['category']; ?> category" style="display:none">
										<span style="display:inline-block;width:40px;"></span>
										<?php echo $category['code']; ?>: <?php echo $category['description']; ?>
										<?php if (trim($category['exapmle']) != '') { ?>
											<i class="far fa-question-circle" data-id="<?php echo $category['catid']; ?>"></i>
										<?php }; ?>
									</li>
							<?php };
							}; ?>
						</ul>
						<i style="padding-left:10px" id="categoriesCount">Maximum 3 categories</i>
					</td>
				</tr>
				<?php if (isset($user_permissions) && in_array("ac_request_certificates", $user_permissions) or $_SESSION['user_type'] == "admin" or $user_type == 'hqc_office' or $user_type = 'committee_member') { ?>
					<?php if (isset($_GET['reissue'])) {
						$act = "add";
					};
					?>
					<?php
					if ($_GET['act'] == 'edit')
						$disabled = 'disabled class="disabled"';
					else
						$disabled = '';
					$disabled = '';
					?>
					<tr>
						<th>Issue & Expiry Dates:*</th>
						<td><b>Issue:</b> <input type="text" name="date_of_issue" class="date" id="date_of_issue" onchange="nextYear()" value="<?php echo ($_GET['act'] == 'edit' and $row['date_of_issue'] != 0) ? web_date($row['date_of_issue']) : ''; ?>" <?php echo $disabled; ?> />
							<b>Expiry:</b> <input type="text" name="date_of_expiry" class="date" id="date_of_expiry" value="<?php echo ($_GET['act'] == 'edit' and $row['date_of_expiry'] != 0) ? web_date($row['date_of_expiry']) : ''; ?>" <?php if ($_SESSION['user_role'] != 'super_admin') { ?>onchange="checkExpiryPeriod()" <?php }; ?> <?php echo $disabled; ?> />
							<b>Validity:</b>
							<input type="number" name="certificate_options[cert_validity]" id="cert_validity" onchange="nextYear()" max="<?php echo $certificate_validity; ?>" min="1" value="<?php echo isset($certificate_options['cert_validity']) ? $certificate_options['cert_validity'] : 1; ?>" <?php echo $disabled; ?> style="width:50px;" /> years
							<span id="maxPeriod"><i>(Maximum <?php echo $certificate_validity; ?> year<?php echo $certificate_validity != 1 ? 's' : ''; ?></i>)</span>
							<b>Initial certification date:</b> <input type="text" class="date" name="initial_issue_date" id="initial_issue_date" value="<?php echo ($_GET['act'] == 'edit') ? web_date($row['initial_issue_date']) : ''; ?>" />
							<div style="margin-top: 5px;" id="surveillance">
								<?php if (isset($certificate_options['surveillance']) and count($certificate_options['surveillance']) > 0) {
									$preSur = array('1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th');
									$surveillance = $certificate_options['surveillance'];
									foreach ($surveillance as $key => $sur) {
										echo $preSur[$key] . ' Surveillance: <input type="text" class="date" name="certificate_options[surveillance][]" value="' . $sur . '" />';
									};
								?>
									Recertification: <input type="text" class="date disabled" name="" value="<?php echo ($_GET['act'] == 'edit' and $row['date_of_expiry'] != 0) ? web_date($row['date_of_expiry']) : ''; ?>" disabled />
								<?php }; ?>
							</div>
						</td>
					</tr>
					<tr id="ApprovalTr">
						<th>Signatory:</th>
						<td colspan="4" id="approval">
							<select name="signatory" id="signatory">
								<option value="">Select Signatory</option>
								<?php
								$signatories = get_signatories('annual', $_GET['offid']);
								if (count($signatories) > 0) {
									foreach ($signatories as $signatory) {
										if (isset($row['signatory']) && $row['signatory'] == $signatory['id'])
											echo "<option value=\"" . $signatory['id'] . "\" selected=\"selected\">" . htmlspecialchars($signatory['name']) . " (" . $signatory['position'] . ")</option>";
										else
											echo "<option value=\"" . $signatory['id'] . "\">" . htmlspecialchars($signatory['name']) . " (" . $signatory['position'] . ")</option>";
									}
								}
								?>
							</select>
							<info>The certificate will be signed by the selected signatory</info>
							<div class="infoBox">You can add or remove Signatories by clicking on setups->certificates Signatories</div>
						</td>
					</tr>
				<?php }; ?>
				<input type="hidden" name="offid" value="<?php echo $_GET['offid']; ?>" />
				<?php
				$certificate_fonts = array();
				$fonts = array(
					'awarded_to' => 'Awarded company name|14',
					'company_address' => 'Company address|14',
					'manufacturing_address' => 'Manufacturing site address(es)|14',
					'reference_standards' => 'Reference halal standards',
					'scope_of_certification' => 'Scope of certification',
					'category' => 'Product category',
					'products' => 'Products columns on annex pages(s)'
				);
				if (isset($certificate_options['fonts'])) {
					$certificate_fonts = $certificate_options['fonts'];
				}
				?>
				<tr>
					<th>Layout font sizes & columns:</th>
					<td colspan="3">
						<b onclick="jQuery('#font_sizes').toggle('slow')" style="cursor:pointer"><i class="fas fa-angle-double-down" style="font-size:14px !important"></i> Font sizes</b>
						<ul style="padding:10px;margin:10px 0px;display:none;border:1px solid #eee;overflow:hidden" id="font_sizes">
							<li style="padding:5px">Font sizes are in pixels</li>
							<?php
							foreach ($fonts as $fontKey => $fontValue) {
								$defSize = 15;
								if (strstr($fontValue, '|')) {
									$fontValues = explode('|', $fontValue);
									$fontValue = $fontValues[0];
									$defSize = $fontValues[1];
								}
							?>
								<li style="padding:5px 0px;margin:0px 2px;background:none !important;border-bottom:1px solid #ccc"><label><input type="number" name="certificate_options[fonts][<?php echo $fontKey; ?>]" id="<?php echo $fontKey; ?>" style="width:60px;" data-required="yes" value="<?php echo (isset($certificate_fonts[$fontKey])) ? $certificate_fonts[$fontKey] : $defSize; ?>" /> <?php echo $fontValue; ?> (default size: <?php echo $defSize; ?>)</label></li>
							<?php	} 	?>
						</ul>
						<b onclick="jQuery('#productsColumns').toggle('slow')" style="cursor:pointer"><i class="fas fa-angle-double-down" style="font-size:14px !important"></i>Product columns</b>
						<div id="productsColumns" style="display:none">
							<?php
							$annex_titles = array(
								'columns' => 'AutNr,article_nr,product_name,description,brand_name',
								'AutNr' => 'Nr',
								'AutNr_width' => '1.5',
								'article_nr' => 'Article code',
								'article_nr_width' => '3.5',
								'product_name' => 'Product name',
								'product_name_width' => '6',
								'description' => 'Description',
								'description_width' => '3',
								'brand_name' => 'Brand name',
								'brand_name_width' => '3'
							);
							if ($act == 'edit') {
								if (isset($annex_options['columns'])) {
									if (count(explode(',', $annex_options['columns'])) != count(explode(',', $annex_titles['columns'])))
										$annex_options['columns'] = $annex_titles['columns'];
								}
								foreach ($annex_titles as $keyTitle => $title) {
									if (isset($annex_options[$keyTitle]))
										$annex_titles[$keyTitle] = $annex_options[$keyTitle];
								}
							}
							$productsColumns = array();
							?>
							<input type="hidden" name="annex_options[columns]" id="annex_options_columns" style="width:100% !important" value="<?php echo $annex_titles['columns']; ?>" />
							<?php ob_start(); ?>
							<b><input type="checkbox" value="yes" name="annex_options[add_AutNr]" id="adAutNr" <?php echo $act == 'edit' && isset($annex_options['add_AutNr']) ? 'checked="checked"' : ($act == 'add' ? 'checked="checked"' : ''); ?> /> Serial number</b>
							<input type="text" name="annex_options[AutNr]" id="annex_title_AutNr" data-required="yes" style="width:80px" title="Auto number" value="<?php echo $annex_titles['AutNr']; ?>" />
							<input type="text" name="annex_options[AutNr_width]" id="annex_title_AutNr_width" data-required="yes" style="width:40px" title="Auto number width" value="<?php echo $annex_titles['AutNr_width']; ?>" />cm
							<?php $productsColumns['AutNr'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start() ?>
							<b><input type="checkbox" value="yes" name="annex_options[add_article_nr]" id="adArticleNr" <?php echo ($act == 'edit' && isset($annex_options['add_article_nr'])) ? 'checked="checked"' : ''; ?> /> Article Nr/Code</b>
							<input type="text" name="annex_options[article_nr]" id="annex_title_article_nr" data-required="yes" style="width:110px" title="Article code" value="<?php echo $annex_titles['article_nr']; ?>" />
							<input type="text" name="annex_options[article_nr_width]" id="annex_title_article_nr_width" data-required="yes" style="width:40px" title="Article code width" value="<?php echo $annex_titles['article_nr_width']; ?>" />cm
							<?php $productsColumns['article_nr'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_product_name]" checked="checked" disabled="disabled" /> Product name</b>
							<input type="text" name="annex_options[product_name]" id="annex_title_product_name" data-required="yes" style="width:110px" title="Product Name" value="<?php echo $annex_titles['product_name']; ?>" />
							<input type="text" name="annex_options[product_name_width]" id="annex_title_product_name_width" data-required="yes" style="width:40px" title="Product name Width" value="<?php echo $annex_titles['product_name_width']; ?>" />cm
							<?php $productsColumns['product_name'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_description]" value="yes" <?php echo ($act == 'edit' && isset($annex_options['add_description'])) ? 'checked="checked"' : ''; ?> /> Description</b>
							<input type="text" name="annex_options[description]" id="annex_title_description" data-required="yes" style="width:110px" title="Description" value="<?php echo $annex_titles['description']; ?>" />
							<input type="text" name="annex_options[description_width]" id="annex_title_description_width" data-required="yes" style="width:40px" title="Description width" value="<?php echo $annex_titles['description_width']; ?>" />cm
							<?php $productsColumns['description'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_brand_name]" value="yes" <?php echo ($act == 'edit' && isset($annex_options['add_brand_name'])) ? 'checked="checked"' : ''; ?> /> Brand name</b>
							<input type="text" name="annex_options[brand_name]" id="brand_name" data-required="yes" style="width:110px" title="Brand name" value="<?php echo $annex_titles['brand_name']; ?>" />
							<input type="text" name="annex_options[brand_name_width]" id="brand_name_width" data-required="yes" style="width:40px" title="Description width" value="<?php echo $annex_titles['brand_name_width']; ?>" />cm
							<?php $productsColumns['brand_name'] = ob_get_contents();
							ob_end_clean(); ?>
							<ol id="sortableTitles">
								<?php
								$columns = explode(',', $annex_titles['columns']);
								foreach ($columns as $column) { ?>
									<li data-column="<?php echo $column; ?>"><?php echo $productsColumns[$column]; ?></li>
								<?php } ?>
							</ol>
							To rearrange product columns click on <i class="fas fa-expand-arrows-alt" style="font-size: 12px !important;"></i> and drug the column to the desired position.
						</div>
				</tr>

				<?php
				$template['php'] = '{
    "images": {
        "main_signature": {
            "title": "Insert main page signature"
        },
        "main_stempel": {
            "title": "Insert main page stempel"
        },
		"main_halal_stempel": {
			"title": "Insert main page halal logo"
		},
		"main_eiaci": {
			"title": "Insert main page EIACI Logo"
		},
        "annex_signature": {
            "title": "Insert annex signature"
        },
        "annex_stempel": {
            "title": "Insert annex page stempel"
        },
		"annex_halal_stempel": {
			"title": "Insert annex page halal logo"
		},
		"annex_eiaci": {
			"title": "Insert annex page EIACI Logo"
		}
    },
    "digital-print": "yes",
    "font_sizes": "yes"
}';
				$inputs['options'] = '';
				$inputs['main'] = '';
				if (isset($template)) {
					if (trim($template['php']) != '' && is_array(json_decode($template['php'], true))) {
						$php = json_decode($template['php'], true);
						if (isset($php['images'])) {
							$images = $php['images'];
							foreach ($images as $key => $image) {
								$imageChecked = '';
								if (isset($certificate_options['image'])) {
									$optionImages = $certificate_options['image'];
									if (in_array($key, $optionImages))
										$imageChecked = 'checked';
								}
								$inputs['options'] .= '<li><label><input type="checkbox" name="certificate_options[image][]" value="' . $key . '" ' . $imageChecked . '/> ' . $image['title'] . '</label></li>';
							};
						};
						if (isset($php['annex-images'])) {
							$images = $php['annex-images'];
							foreach ($images as $key => $image) {
								$imageChecked = '';
								if (isset($certificate_options['annex-image'])) {
									$optionImages = $certificate_options['annex-image'];
									if (in_array($key, $optionImages))
										$imageChecked = 'checked';
								}
								$inputs['options'] .= '<li><label><input type="checkbox" name="certificate_options[annex-image][]" value="' . $key . '" ' . $imageChecked . '/> ' . $image['title'] . '</label></li>';
							};
						}
					};
					$annexNormal = '';
					$annexPageOnly = '';
					$annexSepareted = '';
					if (strstr($template['content'], 'annexPage')) {
						if (isset($certificate_options['annexPages'])) {
							if ($certificate_options['annexPages'] == 'annexPageOnly')
								$annexPageOnly = 'checked';
							elseif ($certificate_options['annexPages'] == 'annexSepareted')
								$annexSepareted = 'checked';
							else
								$annexNormal = 'checked';
						} else {
							$annexNormal = 'checked';
						}
						if ($annexSepareted == '')
							$annexSeparetedFirstPage = 'normal';
						elseif (isset($certificate_options['annexSeparetedFirstPage']))
							$annexSeparetedFirstPage = $certificate_options['annexSeparetedFirstPage'];
						else
							$annexSeparetedFirstPage = 'normal';

						if (isset($certificate_options['auto_certificate_number']))
							$auto_certificate_number = 'yes';

						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'none\')"><input type="radio" name="certificate_options[annexPages]" value="normal" ' . $annexNormal . '/> Normal print</label></li>';
						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'none\')"><input type="radio" name="certificate_options[annexPages]" value="annexPageOnly" ' . $annexPageOnly . ' /> Print annex page only</label></li>';
						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'block\')"><input type="radio" name="certificate_options[annexPages]" value="annexSepareted" ' . $annexSepareted . ' /> Print annex on separate pages</label>
			<ul style="display:' . ($annexSepareted != '' ? 'block' : 'none') . '" id="annexSepareted">
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="normal" ' . ($annexSeparetedFirstPage == 'normal' ? 'checked' : '') . '/> Normal print without the first page</label></li>
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="major" ' . ($annexSeparetedFirstPage == 'major' ? 'checked' : '') . '/> Annex pages with one major first page</label></li>
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="preceded" ' . ($annexSeparetedFirstPage == 'preceded' ? 'checked' : '') . '/> Each annex page preceded by the first page</label></li>';
						$inputs['options'] .= '<li style="background: beige; color: green;"><label><input type="checkbox" name="certificate_options[auto_certificate_number]" value="yes" ' . (isset($auto_certificate_number) ? 'checked' : '') . '/> Auto certificate sub-number.</label> (Ex. NL1051050005.1, NL1051050005.2)</li>';
						$inputs['options'] .= '</ul></li>';
					}
				};
				//get inserted in template inputs
				if ($contentOptions = parse_shortcode('input', $template['content'])) {
					$elements = array(
						'checkbox' => '<label><input type="[type]" name="certificate_options[[name]]" id="certificate_option_[name]" [props] />[title]</label>',
						'text' => '<input type="[type]" name="certificate_options[[name]]" id="certificate_option_[name]" [value] [props]/>',
						'textarea' => '<textarea name="certificate_options[[name]]" id="certificate_option_[name]" class="certificate_option" [props]>[value]</textarea>'
					);
					if (count($contentOptions) > 0) {
						foreach ($contentOptions as $contentOption) {
							$elementName = $contentOption['name'];
							$element = str_replace(
								array('[type]', '[name]', '[title]'),
								array($contentOption['type'], $elementName, isset($contentOption['title']) ? $contentOption['title'] : ''),
								$elements[$contentOption['type']]
							);
							$props = '';
							foreach ($contentOption['props'] as $key => $value) {
								if (!strstr($element, '[' . $key . ']'))
									$props .= $key . '="' . $value . '" ';
							};
							if (isset($certificate_options[$elementName])) {
								if ($contentOption['type'] == 'checkbox')
									$props .= 'checked="checked"';
								else
									$element = str_replace('[value]', $certificate_options[$elementName], $element);
							}
							$element = str_replace(array('[props]', '[value]'), array($props, ''), $element);
							if (isset($contentOption['group']) and $contentOption['group'] == 'options') {
								$inputs['options'] .= '<li>' . $element . '</li>';
							} else {
								$inputs['main'] .= '<tr><th>' . (isset($contentOption['title']) ? $contentOption['title'] : '') . ':</th><td colspan="3">' . $element . '</td></tr>';
							}
						}
					}
				}
				if (trim($inputs['main']) != '') {
					echo $inputs['main'];
				}
				?>
				<tr>
					<th>Certificate options:</th>
					<td colspan="3" id="annexOptions">
						<b>Sort products on annex page: </b>
						<select name="product_sort_by" size="1">
							<option value="">No sorting</option>
							<option value="product_name" <?php echo ($act == 'edit' && $row['product_sort_by'] == "product_name") ? 'selected="selected"' : ''; ?>>Sort by product name</option>
							<option value="article_nr" <?php echo ($act == 'edit' && $row['product_sort_by'] == "article_nr") ? 'selected="selected"' : ''; ?>>Sort by article number</option>
						</select>
						<?php
						if (trim($inputs['options']) != '') { ?>
							<ul style="margin:0px;padding:0px;width:100%"><?php echo $inputs['options'] ?></ul>
						<?php }; ?>
					</td>
				</tr>
				<tr>
					<th>Certificate File Name:</th>
					<td colspan="3">
						<?php
						if (isset($certificate_options['certificate_file_name']))
							$CRTFileName = $certificate_options['certificate_file_name'];
						else
							$CRTFileName = '';
						?>
						<select name="certificate_options[certificate_file_name]">
							<option value="certificate_number">Certificate number</option>
							<option value="company_name_crt_number" <?php echo ($CRTFileName == 'company_name_crt_number') ? 'selected' : ''; ?>>Company name & Certificate number</option>
							<option value="company_name" <?php echo ($CRTFileName == 'company_name') ? 'selected' : ''; ?>>Company name</option>
							<option value="product_name" <?php echo ($CRTFileName == 'product_name') ? 'selected' : ''; ?>>Product name</option>
							<option value="article_nr" <?php echo ($CRTFileName == 'article_nr') ? 'selected' : ''; ?>>Article code</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Certificate language:</th>
					<td>
						<select name="certificate_options[language]" id="certificate_language">
							<option value="en" <?php echo (isset($certificate_options['language']) && $certificate_options['language'] == 'en') ? 'selected' : ''; ?>>English</option>
							<option value="de" <?php echo (isset($certificate_options['language']) && $certificate_options['language'] == 'de') ? 'selected' : ''; ?>>German</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Annex number & Revision:</th>
					<td colspan="3">
						<input name="revision[insert]" value="yes" type="checkbox" <?php echo isset($revision['insert']) ? 'checked' : ''; ?> onclick="autoAnnexNumber(this)">Insert: </label> <b>Annex number:</b> <input type="number" min="1" name="certificate_option[annex_number]" style="width:60px" value="<?php echo isset($certificate_options['annex_number']) ? $certificate_options['annex_number'] : '1'; ?>" />
						<label style="padding: 6px 10px;background:lightblue;display:" <?php echo isset($certificate_options['annex_number']) ? 'none' : 'none'; ?>" id="auto_annex_number"><input type="checkbox" name="certificate_option[auto_annex_number]" <?php echo (isset($certificate_options['auto_annex_number'])) ? 'checked' : ''; ?> /> Auto-number</label>
						<b style="width:auto">Revision number:</b> <input type="text" name="revision[number]" style="width:40px" value="<?php echo isset($revision['number']) ? $revision['number'] : '1.0'; ?>" />
						<b>Revision date:</b> <input type="text" class="date" name="revision[date]" value="<?php echo isset($revision['date']) ? $revision['date'] : date("d.m.Y"); ?>" />
					</td>
				</tr>
				<tr>
					<td colspan="5" class="sub_title" style="text-align: center;">
						<input type="reset" value="Reset" />
						<?php
						if ($user_type == 'admin')
							$request = 'Save';
						else
							$request = 'Request';
						?>
						<input type="button" id="addUpdateReissue" onclick="crtDoAct('save')" value="<?php echo ($_GET['act'] == "edit") ? "Update" : ($_GET['act'] == "reissue" ? "Reissue" : $request) ?>" />
						<input type="button" value="Preview" onclick="crtDoAct('preview')" />
						<?php if (isset($user_permissions) && (in_array("ac_print_certificates", $user_permissions) or $_SESSION['user_type'] == "admin" or $user_type == 'hqc_office')) { ?>
							<?php if ((in_array("ac_print_certificates", $user_permissions) or isset($annual_permissions['print']) or $_SESSION['user_type'] == "admin")) {
								$controlled_by = 'no';
								if (isset($options)) {
									$options = json_decode($office['options'], true);
									if (isset($options['controlled_by']))
										$controlled_by = strtolower($options['controlled_by']);
									if (isset($options['print_jakim']))
										$print_jakim_certificates = $options['print_jakim'];
									else
										$print_jakim_certificates = 'no';
								}
							?>
								<?php if ($user_type == "admin" && $_GET['offid'] != '0') { ?>
									<input type="button" value="Authorize" onclick="crtDoAct('authorize')" />
								<?php }; ?>
								<input type="button" id="printActionButton" value="<?php echo (isset($dmc)) ? 'Create DMC Report' : 'Print'; ?>" onclick="crtDoAct('print')" />
								<span id="DownLoadZip" style="display:none;font-weight:normal"><label><input type="checkbox" name="downLoadZipFile" id="downLoadZipFile" value="yes">Download individual certificates </label></span>
							<?php } else { ?>
								<input type="hidden" value="<?php echo $controlled_by; ?>" name="certificate_options[controlled_by]" />
							<?php }; ?>
						<?php
						};
						?>
					</td>
				</tr>
			<?php
			}; ?>

		</table>
		<?php if ($act == 'add') { ?>
			<input type="hidden" id="DMCUrl" data-href="" title="Create DMC Report" data-resize="true" onclick="doIframe(this)"></input>
		<?php }; ?>
	</form>

	<?php if (isset($_GET['clid']) and (isset($user_permissions) && in_array("ac_print_certificates", $user_permissions) or $_SESSION['user_type'] == "admin")) { ?>
		Please make a preview before you print the certificate.
	<?php } else { ?>
		<div id="productsInfo" style="color:red;margin-top:20px">Before you request a certificate make sure to add some products to the company.</div>
	<?php }; ?>
</center>

<!--End add/edit halal_certificates-->
<template id="remarksStyle">
	<label><input type="checkbox" name="[name][bold]" value="strong" class='remarksBold' /><strong>Font-weight Bold</strong></label>
	<label><input type="checkbox" name="[name][italic]" value="italic" class="remarkItalic" /><i>Font-style Italic</i></label>
	Text color:
	<label class="colorLabel" style="background:black"><input type="radio" name="[name][color]" value="black" class="colorPicked" checked /></label>
	<label class="colorLabel" style="background:red"><input type="radio" name="[name][color]" value="red" class="colorPicked" /></label>
	<label class="colorLabel" style="background:blue"><input type="radio" name="[name][color]" value="blue" class="colorPicked" /></label>
	<label class="colorLabel" style="background:green"><input type="radio" name="[name][color]" value="green" class="colorPicked" /></label>
</template>

<script src="/scripts/color-picker/jqColorPicker.min.js"></script>
<script>
	var remarksStyle = {};
	var JAKIM = '<?php echo isset($options['print_jakim']) ? $options['print_jakim'] : 'no'; ?>';
	<?php
	if (isset($certificate_options['lastPageRemarksStyle'])) {
		echo 'remarksStyle["lastPageRemarksStyle"] = ' . json_encode($certificate_options['lastPageRemarksStyle']) . ';' . "\n";
	}
	if (isset($certificate_options['remarksStyle'])) {
		echo 'remarksStyle["remarksStyle"] = ' . json_encode($certificate_options['remarksStyle']) . ';' . "\n";
	}
	?>
	var offid = <?php echo isset($_SESSION['offid']) ? $_SESSION['offid'] : 0; ?>;
	$(function() {
		$("#sortableTitles").sortable({
			stop: function(event, ui) {
				cols = []
				jQuery("#sortableTitles li").each(function(index, element) {
					cols.push(jQuery(this).data('column'));
				});
				jQuery("#annex_options_columns").val(cols.join(','));
			}
		});
		$("#sortableTitles").disableSelection();
	});

	function checkMiic(obj) {
		if (jQuery(obj).is(":checked")) {
			jQuery("#office_address").val(0);
			jQuery("#office_address_tr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});
		} else {
			jQuery("#office_address_tr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});

		}

	}

	function halalStandardsCheck() {
		var checkedTot = 0,
			OIC = 0;

		jQuery("#halalStandards input[type=checkbox]").each(function() {
			if (jQuery(this).is(":checked")) {
				sta = jQuery(this).data('standard').split(' ')[0];
				if (sta.toUpperCase() == 'MS') {
					checkedTot++;
				}

				if (sta.toUpperCase() == 'OIC/SMIIC') {
					OIC++;
				}
			}
		})
		if (OIC > 0) {
			//TODO: update this
			jQuery("#insertHAKLogo").css("display", "none");
		} else {
			jQuery("#insertHAKLogo").css("display", "none");
		}

		if (checkedTot > 0 && userType == 'hqc_office') {
			//check if office_address has a attr data-offid
			if (jQuery("#office_address").data('offid') == undefined) {
				jQuery("#office_address").data('offid', jQuery("#office_address").val());
			}

			jQuery("#office_address").val(0);
			jQuery("#office_address_tr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});

			//unselect all signatories_main_director
			jQuery("#signatories_main_director option").each(function() {
				jQuery(this).removeAttr("selected");
			})

			//select first index in signatories_main_director using javascript
			document.getElementById('signatories_main_director').selectedIndex = 0;
			//disable signatories_main_director
			jQuery("#ApprovalTr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});
			//check checkbox value = main_signature
			jQuery("#annexOptions input[type=checkbox]").each(function() {

				if (jQuery(this).val() == 'main_signature' || jQuery(this).val() == 'main_stempel' || jQuery(this).val() == 'annex_signature' || jQuery(this).val() == 'annex_stempel') {
					jQuery(this).attr("checked", true);
				}
			})
		} else {
			jQuery("#office_address_tr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});
			jQuery("#ApprovalTr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});
			jQuery("#printActionButton").css("display", "");
			if (jQuery("#office_address").data('offid') != undefined) {
				jQuery("#office_address").val(jQuery("#office_address").data('offid'));
			}
		}
	}


	jQuery(document).ready(function() {
		jQuery(".certificate_option").each(function() {
			name = jQuery(this).attr('name');
			className = name.replace('certificate_options[', '').replace(']', '');
			replaceWith = name.replace('remarks', 'remarksStyle').replace('lastPageRemarks', 'lastPageRemarksStyle');
			template = jQuery("#remarksStyle").html();
			if (name == 'certificate_options[remarks]' || name == 'certificate_options[lastPageRemarks]') {
				jQuery(this).after('<div class="' + className + 'Style" style="padding:5px">' + template.replace(/\[name\]/g, replaceWith) + '</div>');
			}
			jQuery(".colorPicked").on("click", function() {
				jQuery(this).parents('td').find(".certificate_option").css("color", jQuery(this).val());
			})
			jQuery(".remarksBold").on("click", function() {
				if (jQuery(this).is(":checked")) {
					jQuery(this).parents('td').find(".certificate_option").css("font-weight", "bold");
				} else {
					jQuery(this).parents('td').find(".certificate_option").css("font-weight", "normal");
				}
			})
			jQuery(".remarkItalic").on("click", function() {
				if (jQuery(this).is(":checked")) {
					jQuery(this).parents('td').find(".certificate_option").css("font-style", "italic");
				} else {
					jQuery(this).parents('td').find(".certificate_option").css("font-style", "normal");
				}
			})
		})

		if (remarksStyle.remarksStyle != undefined) {
			remarksStyles = remarksStyle.remarksStyle;
			remarkClass = 'div.remarksStyle';
			if (remarksStyles.color != undefined) {
				jQuery(remarkClass).find('.colorPicked[value=' + remarksStyles.color + ']').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("color", remarksStyles.color);
			}
			if (remarksStyles.bold != undefined) {
				jQuery(remarkClass).find('.remarksBold').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-weight", "bold");
			}
			if (remarksStyles.italic != undefined) {
				jQuery(remarkClass).find('.remarkItalic').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-style", "italic");
			}
		}

		if (remarksStyle.lastPageRemarksStyle != undefined) {
			lastPageRemarksStyle = remarksStyle.lastPageRemarksStyle;
			remarkClass = 'div.lastPageRemarksStyle';
			if (remarksStyles.color != undefined) {
				jQuery(remarkClass).find('.colorPicked[value=' + lastPageRemarksStyle.color + ']').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("color", lastPageRemarksStyle.color);
			}
			if (lastPageRemarksStyle.bold != undefined) {
				jQuery(remarkClass).find('.remarksBold').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-weight", "bold");
			}
			if (lastPageRemarksStyle.italic != undefined) {
				jQuery(remarkClass).find('.remarkItalic').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-style", "italic");
			}
		}

		//check if #annexSepareted is visible
		if (jQuery("#annexSepareted").css("display") == "block") {
			jQuery("#DownLoadZip").css("display", "inline-block");
		}

		jQuery(".reference_standards").on("click", function() {
			//halalStandards HQCScheme

			parentStandard = jQuery(this).parents('ul').prop('id') == 'HQCScheme' ? '#halalStandards' : '#HQCScheme';

			// jQuery(parentStandard + " input[type=checkbox]").each(function() {
			// 	if (jQuery(this).is(":checked")) {
			// 		jQuery(this).removeAttr("checked");
			// 	}
			// })
			//	halalStandardsCheck(this);
		})
		//halalStandardsCheck(this);
		if (jQuery("#productsOl").length > 0) {
			jQuery("#productsInfo").css("display", "none");
		}
		jQuery("#addUpdateReissue").attr("data-value", jQuery("#addUpdateReissue").val());
		jQuery("#certificate_option_reprint").on("click", function() {
			if (jQuery(this).is(":checked")) {
				jQuery("#addUpdateReissue").val('Authorize Client');
			} else {
				jQuery("#addUpdateReissue").val(jQuery("#addUpdateReissue").data('value'));
			}
		})
	})

	async function removeProduct(prdid) {
		if (prdid == '*') {
			await confirm_message("Are you sure you want to remove all product from the list?");
			jQuery("#productsOl .double").remove();
		} else {
			await confirm_message("Are you sure you want to remove this product?");
			jQuery("#product_" + prdid).remove();
		}
	}
</script>