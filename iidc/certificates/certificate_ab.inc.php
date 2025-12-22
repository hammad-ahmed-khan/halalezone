<script>
	$("#page_title").html("Requested Certificates A (Meat)")
</script>
<?php
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
//TODO recheck this file and make it cleaner and more readable and fix the issues
$attch_display = 'none';
$companies = array();
$option = array();
$batchCountry = false;
$batchOffices = array();
$offices = array();
if (!isset($_GET['clid']) and $_SESSION['user_type'] != 'client') {
	if (!isset($_GET['clid']) || $_GET['clid'] == '') {
		if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='{$_GET['offid']}'")) {
			$user_clients = ($office['clients']);
		} else {
			echo "<center><b>Office not found</b></center>";
			return;
		}

		if ($companies = $amdb->get_results("SELECT * FROM companies
                                   LEFT JOIN users ON companies.clid = users.clid
                                   WHERE users.active='y' ORDER BY companies.company_name ASC")) { ?>
								   <h2 class="content_title">Inssue slaughtering cartificate (<span style="color:#900"><?php echo $office['office_name'];?></span>)</h2>
			<center><b>Issue certificate for: </b><select name="clid" size="1" onchange="document.location='?inc=certificate_ab&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $_REQUEST['offid']; ?>&clid='+this.value">
					<option value="">Select a company</option>
					<?php foreach ($companies as $company) { ?>
						<option value="<?php echo $company['clid']; ?>"><?php echo $company['company_name']; ?></option>
					<?php } ?>
				</select>
			</center>
<?php } else {
			echo "<center><b>No companies found for this office</b></center>";
			return;
		}
		return;
	}
	exit();
}
$companies = array();
$importers = '';
$exporters = '';
$producers = '';
if ($result = $amdb->get_results("SELECT * FROM companies WHERE clid='$clid' or clof='$clid' ORDER BY company_name ASC")) {
	foreach ($result as $company) {
		if (!isset($companies[$company['clid']])) {
			if (trim($company['country1']) == 'SA' or trim($company['country1']) == 'Saudi Arabia') {
				$SFDA = ' data-sfda="yes"';
			} else {
				$SFDA = '';
			}

			$producers .= "<option value='$company[clid]'>$company[company_name]</option>\n";
			if (in_array($_GET['tp'], ['a', 'b']) or (in_array($_GET['tp'], ['sa', 'sb']) && (trim($company['country1']) == 'SA' or trim($company['country1']) == 'Saudi Arabia')))
				$importers .= "<option data-CRN='$company[CRN]' value='$company[clid]'$SFDA>$company[company_name]" . (trim($company['country1']) != '' ? '(' . $company['country1'] . ')' : '') . "</option>\n";
			$exporters .= "<option value='$company[clid]'>$company[company_name]</option>\n";
			$companies[$company['clid']] = $company['company_name'];
			if ($company['clid'] == $clid) {
				$company_country = $company['country1'];
				$company_name = $company['company_name'];
				$client_extra = $company['client_extra'];
				$company_email = $company['email1'];
				$offid = $company['offid'];
			}
		}
	}
};

if (!isset($act) or $act == "") $act = "add";
if (isset($act) and $act == "edit") {
	if ($row = $amdb->get_row("SELECT * FROM certificates_{$_REQUEST['tp']} where nr='$nr'")) {
		$importer = $row['importer'];
		$exporter = $row['exporter'];
		$producer = $row['producer'];
		$certificate_nr = $row['certificate_nr'];
		if (trim($row['options']) != '' and is_array(json_decode(str_replace("\r\n", '\n', $row['options']), true)))
			$option = json_decode(str_replace("\r\n", '\n', $row['options']), true);
		if ($row['attachment'])
			$attch_display = '';

		if ($_SESSION['user_type'] != 'client' && !isset($office) && isset($row['tmplid']))
			$office = get_office_data($row['tmplid']);
	}
}
$certificate_title = array('a' => 'Certificate type A (Raw / Fresh / Frozen Meats (Unprocessed))', 'b' => 'Certificate type B (Non-Fresh Meats / Foods / Beverages / Cosmetics)', 'sa' => 'Certificate type A (Raw / Fresh / Frozen Meats (Unprocessed)) <span style="color:red">for Saudi Arabia only</span>', 'sb' => 'Certificate type B (Non-Fresh Meats / Foods / Beverages / Cosmetics) <span style="color:red">for Saudi Arabia only</span>');
?>
<script language="javascript">
	var err;

	function checform() {
		err = '';
		for (var i = 0; i <= document.certificateForm.elements.length - 1; i++) {
			if (document.certificateForm.elements[i].getAttribute('data-required')) {
				document.certificateForm.elements[i].style.backgroundColor = "";
				if (document.certificateForm.elements[i].value == "") {
					document.certificateForm.elements[i].style.backgroundColor = "#FFD9D9";
					err = "y";
				}
			}
		}
	}
	var IsNumber;

	function checknr(nr) {
		var ValidChars = "1234567890.";
		var Char;
		IsNumber = '';
		for (var i = 0; i < nr.length; i++) {
			Char = nr.charAt(i);
			if (ValidChars.indexOf(Char) == -1) {
				IsNumber = 'no';
			}
		}
	}

	function adjustColWidth() {
		width = 0;
		jQuery("#batchHead").find("input[type=number]").each(function() {
			thisWidth = parseInt(jQuery(this).val());
			cmToPx = Math.round(18 * 37.795275591);
			parentWidth = jQuery(this).parents('table').width();
			var tdWidth = Math.round(thisWidth * ((parentWidth / cmToPx) * 37.795275591));
			jQuery(this).parent('td').css('width', tdWidth + 'px');
			width = width + thisWidth;
		})
		//check landscape
		if (jQuery("#option_landscape").is(":checked"))
			decWidth = 26.5 - width;
		else
			decWidth = 18 - width;
		jQuery("#description_width").val(decWidth);
		jQuery(".description_width").html(decWidth)
	}

	function addColumnTools() {
		return false;
		jQuery("#batchHead .productTitleTh").each(function() {
			if (jQuery(this).find('.columnTools').length == 0) {
				columnTools = '<i class="fas fa-angle-left" onclick="moveColumnLeftRight(this,\'left\')" title="Move column to the left"></i><i class="fa fa-edit" onclick="editThisTitle(this);" title="Edit column titles"></i>';
				if (jQuery(this).hasClass('extra') == true)
					columnTools += '<i class="fa fa-times-circle" onclick="deletedThisColumn(this)" title="Delete column"></i>';
				columnTools += '<i class="fas fa-angle-right" onclick="moveColumnLeftRight(this,\'right\')" title="Move column to the right"></i>';
				jQuery(this).prepend('<div class="columnTools">' + columnTools + '</div>');
			}
		})
	}

	function addProductsColumn() {
		resetSortable();
		totalTds = 0;
		thisTime = Date.now();
		trNumbers = jQuery("#batchHead").find("th").length - 3;
		jQuery("#batchHead").find("tr").each(function() {
			totalTds = jQuery(this).find('td,th').length;
			//jquery append at index number position
			jQuery(this).find('th').eq(totalTds - 2).after('<th style="text-align: center;" class="productTitleTh extra">' +
				'<div id = "extra_' + trNumbers + '_english" class = "productTitle english" ><b>English</b></div></th>');
			jQuery(this).find('td').eq(totalTds - 2).after('<td class="extra">width:<input type="number" value="2" name="option[extra_' + trNumbers + '][width]" data-required="yes" onchange="adjustColWidth()"/>cm </td>');
		});
		adjustColWidth();
		jQuery("#batchProducts").find("tr").each(function() {
			totalTds = jQuery(this).find('td').length;
			//jquery append at index number position
			// name = jQuery(this).find('td').find('input').first().prop("name").replace('artNt', 'extra_' + trNumbers);
			// jQuery(this).find('td').eq(totalTds - 2).after('<td style="width:100px"><input type="text" name="' + name + '"/> </td>');
			name = jQuery(this).find('td').find('input').first().prop("name").replace('extra_' + trNumbers);
			jQuery(this).find('td').eq(totalTds - 2).after('<td style="width:100px"><input type="text" name="' + name + '"/> </td>');
		});
		jQuery("#batchFooter").prop("colspan", totalTds + 1);
		fillProductTitle();
	}

	function preview() {
		checform();
		checknr(document.certificateForm.weight_gross.value);
		if (IsNumber == 'no') {
			alert_message("You only can use (0-9 and .) In the weight fields");
			return false;
		}
		checknr(document.certificateForm.weight_net.value);
		if (IsNumber == 'no') {
			alert_message("You only can use (0-9 and .) In the weight fields");
			return false;
		}
		if (err == "y") {
			alert_message("Fields with (*) are required")
			return false;
		}
		document.certificateForm.action = "pdf_certificate.php";
		document.certificateForm.act.value = "preview";
		document.certificateForm.target = "_blank";
		document.certificateForm.submit();
	}

	function save_hc(savePrint) {

		checform()
		checknr(document.certificateForm.weight_gross.value);
		if (IsNumber == 'no') {
			alert_message("You only can use (0-9 and .) In the weight fields");
			return false;
		}
		checknr(document.certificateForm.weight_net.value);
		if (IsNumber == 'no') {
			alert_message("You only can use (0-9 and .) In the weight fields");
			return false;
		}
		if (err == "y") {
			alert_message("Fields with (*) are required")
			return false;
		}
		document.certificateForm.action = "certificate_save.php";
		if (savePrint == 'draft' || savePrint == 'saveDraft')
			document.certificateForm.act.value = savePrint;
		else
			document.certificateForm.act.value = "<?php echo  $act ?>";
		jQuery('#do').val(savePrint);

		if (jQuery("certificateIForm").length == 0)
			jQuery('body').append('<iframe style="position:fixed;left:-5000px" name="certificateIForm" class="post_form_frame"></iframe>');
		document.certificateForm.target = "_new";
		document.certificateForm.target = "certificateIForm";
		document.certificateForm.submit();
	}

	function changeOption(obj) {
		id = 'option_' + obj.name;
		jQuery('#' + id).css('display', 'none')
		if (obj.value == '0')
			jQuery('#' + id).css('display', 'inherit')
		if (obj.value == 'newClient')
			location = '/company/index.php?inc=cl_add_edit&clof=<?php echo $clid; ?>&return=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>';
	}

	function resetSortable() {
		if (jQuery("#batchProducts tr").length > 1)
			jQuery('i.fas.fa-arrows-alt').css('display', 'inherit');
		else
			jQuery('i.fas.fa-arrows-alt').css('display', 'none');

		if (jQuery("#batchProducts").hasClass('ui-sortable')) {
			jQuery("#batchProducts").sortable('destroy');
			jQuery("#batchProducts").removeClass('ui-sortable');
			jQuery("#batchProducts tr td:last-child .fa-arrows-alt").remove();
			jQuery("#batchProducts tr td:last-child img").css('display', '');
			jQuery("#batchProducts tr td").each(function() {
				jQuery(this).css('width', '');
			})
		}
	}

	function sortProducts() {
		if (jQuery("#batchProducts").hasClass('ui-sortable')) {
			resetSortable();
		} else {
			if (jQuery("#batchProducts").find('tr').length > 1) {
				jQuery("#batchProducts tr td:last-child img").css('display', 'none');
				jQuery("#batchProducts tr td:last-child").append('<i class="fas fa-arrows-alt"></i>');
				//make batchProducts tr sortable using jquery ui
				jQuery("#batchProducts").sortable({
					items: "tr",
					cursor: "move",
					opacity: 0.6,
					tolerance: "pointer",
				});

				jQuery("#batchProducts tr td").each(function() {
					jQuery(this).css('width', jQuery(this).width() + 'px');
				})

			}
		}
	}
	var productsHeader = {
		// 'artNt': {
		// 	'english': '<b>Consignment Reference:</b><br/>Article No. / Lot No. / Batch No.',
		// 	'width': 5
		// },
		'description': {
			'english': '<b>Description of Shipped Products or Items</b>',
			'width': 8
		},
		'quantity': {
			'english': '<b>Total Weight</b><br/>in Kilograms',
			'width': 5
		}
	}

	//function to insert header form the list productsHeader using json and jquery
	function insertProductTitle(reset = false) {

		if (reset == true) {
			jQuery("#batchHeadTitles").html('');
			jQuery("#batchHeadWidths").html('');
		}
		if (jQuery("#batchHeadTitles th").length < 3) {

			var headerTh = '',
				headerTd = '';
			jQuery.each(productsHeader, function(key, val) {
				headerTh += '<th style="text-align: center;" class="productTitleTh ' + key + '">' +
					'<div id = "' + key + '_english" class = "productTitle english" >' + val.english + '</div></th>';
				if (key == 'description') {
					headerTd += '<td>Width: <input type="hidden" name="option[' + key + '][width]" id="description_width" value="' + val.width + '" /><span class="description_width">' + val.width + '</span> CM</td>';
				} else {
					headerTd += '<td class="extra">width: <input type="number" value="' + val.width + '" name="option[' + key + '][width]" data-required="yes" onchange="adjustColWidth()"/> cm </td>';
				};
			});

			jQuery("#batchHeadTitles").prepend(headerTh);

			//check if last th contains class fa-plus using jquery
			if (jQuery("#batchHeadTitles th:last-child").find('i.fas.fa-plus').length == 0)
				// jQuery("#batchHeadTitles").append('<th style="width:20px;vertical-align: middle;"><i class="fas fa-plus" onclick="addProductsColumn()"></i></th>');


				jQuery("#batchHeadWidths").prepend(headerTd);

			//check if batchHeadWidths td length  equal to batchHeadTitles th length
			if (jQuery("#batchHeadTitles th").length != jQuery("#batchHeadWidths td").length)
				jQuery("#batchHeadWidths").append('<td><i class="fas fa-arrows-alt" onclick="sortProducts()"></i></td>');
			adjustColWidth();
		}
	}


	function insertExcelProducts(data) {
		jsonObjs = JSON.parse(data);
		thisTime = Date.now();
		jQuery("#batchProducts").html('');
		jQuery("#excelCertificateItems").val('');

		jQuery("#batchHead tr").each(function() {
			jQuery(this).find('.extra').remove();
		})

		jQuery("#batchProducts tr").each(function() {
			jQuery(this).find('.extra').remove();
		})

		insertProductTitle(true);
		jQuery("#batchFooter").prop("colspan", jQuery("#batchHead .productTitleTh").length + 1);

		// defFields = ['artNt', 'description', 'quantity'];
		defFields = ['description', 'quantity'];
		// if (jsonObjs[0].length > 2) {
		// 	for (i = 3; i < jsonObjs[0].length; i++) {
		// 		addProductsColumn();
		// 	}
		// }


		jQuery.each(jsonObjs, function(objkey, fields) {

			// if (fields[0] != null) {
			thisTime = thisTime + 60;
			thisItem = '<tr>';
			jQuery.each(fields, function(key, val) {
				tdClass = defFields[key] == null ? ' class="extra"' : '';
				td = '<td' + tdClass + '><input type="text" name="products[' + thisTime + '][' + (defFields[key] != null ? defFields[key] : 'extra_' + (key - 2)) + ']" value="' + (val != null ? val : '') + '"></td>';
				thisItem += td;
			});
			thisItem += '<td> <img title="Delete product" src = "../images/delete.gif"	border="0" onclick="deleteProduct(this)"> </td> </tr>';
			jQuery("#batchProducts").append(thisItem);
			adjustColWidth();
			resetSortable();
			// }
		})
	}

	function getExcelCertificateItems(obj) {
		if (obj.value.trim() != '') {
			var fd = new FormData();
			var files = $(obj)[0].files;
			var inputs = "<?php echo isset($inputItems) ? implode(',', $inputItems) : ''; ?>";
			if (files.length > 0) {
				fd.append('file', files[0]);
				fd.append('inputs', inputs);
				$.ajax({
					url: 'import_excel_file.php',
					type: 'post',
					data: fd,
					contentType: false,
					processData: false,
					success: function(data) {
						if (data != 0) {
							if (data.indexOf('error:') > -1) {
								alert_message(data.replace('error:', ''));
							} else {
								insertExcelProducts(data);
							}
						} else {
							alert_message('file not uploaded');
						}
					},
				});
			}
		} else {
			alert_message("Please select a file.");
		}
	}

	function saveExcelCertificateItems() {
		obj = jQuery("#batchProducts");
		let expItems = [];
		let headerItems = []
		jQuery(obj).parent('table').find('th').each(function(el) {
			headerText = jQuery(this).text().replace(/1|2|\*|::/, '');
			if (headerText.trim() != '') {
				headerItems.push(headerText.trim());
			}
		})
		expItems.push(headerItems);
		jQuery(obj).find('tr').each(function(el) {
			tableItems = []
			jQuery(this).find('input').each(function() {
				tableItems.push(this.value.trim());
			})
			expItems.push(tableItems);
		})
		expItems = JSON.stringify(expItems);
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
	}
</script>
<style>
	.newClient {
		color: red
	}

	input#excelCertificateItems {
		position: absolute;
		left: -5000px;
	}

	#batchProducts input[type=text] {
		width: 100%
	}

	#batchHead textarea {
		width: 100%;
		height: 50px;
		margin: 5px 0px;
	}

	#batchHead th {
		font-weight: normal;
		position: relative;
		max-width: 100%;
		width: 20%;
	}

	#batchHead th:first-child{
		width:80%;
	}


	.productTitle.active {
		padding: 5px;
		background: beige;
		border: 1px solid darkgreen;
		margin-bottom: 5px;
	}

	.columnTools {
		margin-bottom: 10px;
		padding: 5px 0px;
		border-bottom: 1px solid white;

	}

	/*set the first icon in the first column hidden */
	#batchHeadTitles th:first-child i.fas.fa-angle-left {
		display: none;
	}

	/*set fas.fa-angle-right  in the before the last th column hidden */
	#batchHeadTitles th:nth-last-child(2) i.fas.fa-angle-right {
		display: none;
	}

	.columnTools i {
		margin: 0px 10px;
		cursor: pointer;
		color: grey;
	}

	div.productTitle {
		white-space: normal;
	}

	#batchHeadWidths td {
		text-align: center;
		vertical-align: middle;
	}

	#batchProducts td {
		vertical-align: middle;
	}

	#batchProducts i.fas.fa-arrows-alt {
		color: cadetblue;
		cursor: move;
	}

	#batchProducts tr:first-child td:last-child img {
		display: none;
	}

	#batchHeadWidths td input {
		width: 50px;
	}
</style>
<?php
if (isset($_SESSION['offid']) && $_SESSION['offid'] != '0')
	$_GET['offid'] = $_SESSION['offid'];
if (!isset($_GET['offid']))
	$_GET['offid'] = 0;
$batchOffices[] = $_GET['offid'];

if (is_array(json_decode($client_extra, true))) {
	$client_extra = json_decode($client_extra, true);
	if (isset($client_extra['shipment_approval']) && $client_extra['shipment_approval'] == 'yes') {
		$approval_required = 'yes';
	}
}
/*
<form action="import_excel_file.php" method="post" enctype="multipart/form-data" target="_new">
	<input type="hidden" name="offid" value="<?php echo htmlspecialchars($_GET['offid'], ENT_QUOTES, 'UTF-8'); ?>" />

	<label for="excelFile">Upload Excel File:</label>
	<input type="file" id="excelFile" name="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required />

	<button type="submit">Upload</button>
</form>
*/
include "../config/countries.code.php";
?>
<h2 class="content_title"><?php echo isset($_GET['act']) && $_GET['act'] == 'edit' ? 'Update ' : 'Issue '; ?> slaughtering Certificate</h2>
<form name="certificateForm" id="certificateForm" method=post action="" target="" enctype="multipart/form-data">
	<input type=hidden name=offid value="<?php echo $_GET['offid']; ?>">
	<input type=hidden name=clid value="<?php echo $clid; ?>">
	<input type=hidden name=act id="act" value="">
	<input type=hidden name=tp id="tp" value="<?php echo $_REQUEST['tp']; ?>">
	<input type=hidden name=nr id="nr" value="<?php echo @$nr; ?>">
	<input type=hidden name=company_name value="<?php echo @str_replace('"', '&quot;', trim($company_name)); ?>">
	<input type=hidden name="do" id="do" value="">
	<?php if (isset($approval_required)) { ?>
		<input type="hidden" name="approval_required" value="yes">
	<?php } ?>
	<?php if (isset($certificate_nr)) { ?>
		<input type=hidden name=certificate_nr value="<?php echo @$certificate_nr; ?>">
	<?php }; ?>
	<?php make_nonce(); ?>
	<table id="reqCerts" style="border:1px solid #EEE;min-width:80%" class="alternateOn">
		<tr>
			<td colspan=2 class="sub_title">
				<center><?php echo $certificate_title[$_REQUEST['tp']]; ?></center>
			</td>
		</tr>
		<?php if ($user_type == 'hqc_office' or $user_type == "admin") { ?>
			<tr>
				<th>Certificate for:</th>
				<td><b><?php echo $company_name; ?></b>
					<?php if (isset($_GET['offid'])) {
						if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='" . $_GET['offid'] . "'")) {
							echo " <span style='color:green;margin-left:50px'>Issued By Office: <b>" . $office['office_name'] . "</b></span>";
						}
					} ?>
				</td>
			</tr>
			<tr>
				<th>Certificate Issuing date*:</th>
				<td><input type="text" class="date" placeholder="Issue date" name="issue_date" value="<?php echo ($act == 'edit' && trim($row['issue_date'])) ? $row['issue_date'] : ''; ?>" data-required="yes" /></td>
			</tr>

		<?php }; ?>
		<tr>
			<th>Country of origin*:</th>
			<td><select name="country_of_origin" size=1 style="width: 220" data-required="y">
					<option value="">Select country </option>
					<?php foreach ($country as $key => $value) { ?>
						<option value="<?php echo $key; ?>" <?php echo (isset($row['country_of_origin']) && $row['country_of_origin'] == $key) ? "selected" : ""; ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>Quantity – Quality*:</th>
			<td><input type="text" name="quality" value="<?php echo ($act == 'edit' && trim($row['quality'])) ? $row['quality'] : ''; ?>" data-required="yes" size="55" /></td>
		</tr>
		<?php
		$weight_gross_gram = '';
		if (isset($row) && isset($row['weight_gross']) && trim($row['weight_gross']) != '') {
			$row['weight_gross'] = explode('.', $row['weight_gross'] . '.');
			$weight_gross_gram = $row['weight_gross'][1];
			$row['weight_gross'] = $row['weight_gross'][0];
		};

		$weight_net_gram = '';
		if (isset($row) && isset($row['weight_net']) && trim($row['weight_net']) != '') {
			$row['weight_net'] = explode('.', $row['weight_net'] . '.');
			$weight_net_gram = $row['weight_net'][1];
			$row['weight_net'] = $row['weight_net'][0];
		};
		?>
		<tr>
			<th>Weight*: </th>
			<td><span style="display: inline-block;width:100px;font-weight:bold">Gross weight:</span><input data-required="y" style="background-color:" type="number" class="number" name="weight_gross" value="<?php echo  @$row['weight_gross']; ?>" size="15" placeholder="KG [Kilogram]"> ,
				<input type="number" name="weight_gross_gram" style="width: 100px;" class="number" placeholder="G [Grams]" value="<?php echo $weight_gross_gram; ?>" max="99" />
				<br />
				<span style="display: inline-block;width:100px;font-weight:bold">Net weight:</span><input type="number" name="weight_net" value="<?php echo  @$row['weight_net']; ?>" size="15" data-required="y" style="margin-top:10px;background-color:" class="number" placeholder="KG [Kilogram]"> , <input type="number" name="weight_net_gram" style="width: 100px;" class="number" placeholder="G [Grams]" value="<?php echo $weight_net_gram; ?>" max="99" /><br />
				<span color="#000000">NOTE: Please insert the KILOGRAMS in the left field and the GRAMS in the right field.<br />
					Maximum 2 decimals can apply within the GRAMS field.<br />
					EXAMPLE: 1,75 KG = 1 Kilogram and 750 Grams.</span>
			</td>
		</tr>
		<tr>
			<th>Transportation:</th>
			<td style="vertical-align:top !important;">
				<div><b style="float:left;width:100px">Method: </b>
					<select name="transportation_method">
						<option value="Vessel Container">Vessel Container</option>
						<option value="Truck" <?php echo ($act == "edit" and $row['transportation_method'] == "Truck") ? "selected" : ""; ?>>Truck</option>
						<option value="Air freight" <?php echo ($act == "edit" and $row['transportation_method'] == "Air freight") ? "selected" : ""; ?>>Air freight</option>
						<option value="YM Unity Container" <?php echo ($act == "edit" and $row['transportation_method'] == "YM Unity Container") ? "selected" : ""; ?>>YM Unity Container</option>
						<option value="YM Uniform" <?php echo ($act == "edit" and $row['transportation_method'] == "YM Uniform") ? "selected" : ""; ?>>YM Uniform</option>
					</select>
				</div>
				<div style="margin-top: 10px;"><b style="float:left;width:100px">Details:</b> <textarea type="text" name="transportation_nr" style="width:550px"><?php echo  @$row['transportation_nr']; ?></textarea></div>
			</td>
		</tr>
		<tr>
			<th>Loading port & destination*:</th>
			<td><b style="display:inline-block;width:100px;">Loading port:</b><input type="text" name="loading_port" value="<?php echo  @$row['loading_port']; ?>" size="45" data-required="yes" placeholder="Loading port (country or city)">
				<div style="margin-top:5px;"><b style="display:inline-block;width:100px;">Destination:</b><input type="text" name="destination" value="<?php echo  @$row['destination']; ?>" size="45" data-required="yes" placeholder="Destination (country or city)"></div>
			</td>
			</td>
		</tr>
		<tr>
			<th>Exporter*:</th>
			<td>
				<select name="exporter" data-required="y" style="background-color:;float:left;margin-right:10px;width:45%" onchange="changeOption(this)">
					<option value=''>Select Exporter</option>
					<option value='newClient' class="newClient">Add Exporter</option>
					<?php if ($_SESSION['user_type'] != 'client') { ?>
						<option value='0' style="color:red" <?php echo (isset($act) and $act == 'edit' && $exporter == '0') ? 'selected' : ''; ?>>Other</option><?php }; ?>
					<?php
					if (isset($act) and $act == 'edit' && $exporter != '0' && isset($companies[$exporter]))
						$exporters = str_replace("value='$exporter'", "value='$exporter' selected", $exporters);
					echo $exporters;
					?>
				</select>
				<?php if ($_SESSION['user_type'] != 'client') { ?>
					<textarea name="option[exporter]" id="option_exporter" style="width:50%;height:60px;display:<?php echo (isset($act) and $act == 'edit' && $exporter == '0') ? 'inherit' : 'none'; ?>" placeholder="Exporter name and address"><?php echo (isset($option['exporter'])) ? $option['exporter'] : ''; ?></textarea><?php }; ?>
			</td>
		</tr>
		<tr>
			<th>Importer*:</th>
			<td>
				<select name="importer" id="selectedImporter" data-required="y" style="background-color:;float:left;margin-right:10px;width:45%" onchange="changeOption(this)">
					<option value=''>Select <?php echo in_array($_GET['tp'], ['sa', 'sb']) ? 'Saudi ' : ''; ?>Importer</option>
					<option value='newClient' class="newClient">Add Importer</option>
					<?php if ($_SESSION['user_type'] != 'client') { ?>
						<option value='0' style="color:red" <?php echo (isset($act) and $act == 'edit' && $importer == '0') ? 'selected' : ''; ?>>Other</option><?php }; ?>
					<?php
					if (isset($act) and $act == 'edit' && $importer != '0' && isset($companies[$importer]))
						$importers = str_replace("value='$importer'", "value='$importer' selected", $importers);
					echo $importers;
					?>
				</select>
				<span id="CRN" style="display: none;">
					<b>Commercial registration number*:</b> <input type="text" name="CRN" id="CRNValue" style="width:120px" id="CRN" value="<?php echo  @$row['CRN']; ?>"> 10 numbers</span>
				<?php if ($_SESSION['user_type'] != 'client') { ?>
					<textarea name="option[importer]" id="option_importer" style="width:50%;height:60px;display:<?php echo (isset($act) and $act == 'edit' && $importer == '0') ? 'inherit' : 'none'; ?>" placeholder="Importer name and address"><?php echo (isset($option['importer'])) ? $option['importer'] : ''; ?></textarea><?php }; ?>
			</td>
		</tr>
		<tr>
			<th>Producer/production plant*:</th>
			<td>
				<select name="producer" data-required="y" style="background-color:;float:left;margin-right:10px;width:45%;" onchange="changeOption(this)">
					<option value=''>Select Producer</option>
					<option value='newClient' class="newClient">Add Producer</option>
					<?php if ($_SESSION['user_type'] != 'client') { ?>
						<option value='0' style="color:red" <?php echo (isset($act) and $act == 'edit' && $producer == '0') ? 'selected' : ''; ?>>Other</option><?php }; ?>
					<?php
					if (isset($act) and $act == 'edit' && $producer != '0' && isset($companies[$producer]))
						$producers = str_replace("value='$producer'", "value='$producer' selected", $producers);
					echo $producers;
					?>
				</select>
				<?php if ($_SESSION['user_type'] != 'client') { ?>
					<textarea name="option[producer]" id="option_producer" style="width:50%;height:60px;display:<?php echo (isset($act) and $act == 'edit' && $producer == '0') ? 'inherit' : 'none'; ?>" placeholder="Producer name and address"><?php echo (isset($option['producer'])) ? $option['producer'] : ''; ?></textarea><?php }; ?>
			</td>
		</tr>

		<?php if ($_REQUEST['tp'] == 'a' or $_REQUEST['tp'] == 'sa') { ?>
			<tr>
				<th>Slaughtering date*:</th>
				<td><input data-required="y" style="background-color:" type="text" class="date" name="slaughtering_date" id="slaughtering_date" value="<?php echo  @$row['slaughtering_date']; ?>">
				</td>
			</tr>
		<?php }; ?>
		<tr>
			<th>Production date:</th>
			<td><input style="background-color:;" type="text" class="date" name="production_date" id="production_date" value="<?php echo  @$row['production_date']; ?>">
			</td>
		</tr>
		<tr>
			<th>Expiry date*:</th>
			<td><input data-required="y" style="background-color:;" type="text" class="date" name="expiry_date" id="expiry_date" value="<?php echo  @$row['expiry_date']; ?>">
			</td>
		</tr>
		<tr>
			<th>Health Certificate No.:</th>
			<td><input type="text" name="hcd_nr" value="<?php echo  @$row['hcd_nr']; ?>" size="25">
			</td>
		</tr>
		<tr>
			<th>Slaughter house:</th>
			<td><textarea name="slaughter_house" style="width:550px"><?php echo  @$row['slaughter_house']; ?></textarea><br /><i>Name & no. of slaughter house</i></td>
		</tr>
		<tr>
			<th>Method of slaughtering:</th>
			<td><input type="text" name="method_of_slaughtering" value="<?php echo  @$row['method_of_slaughtering']; ?>" size="55"></td>
		</tr>
		<tr>
			<th>Slaughtering Supervisor*:</th>
			<td><input type="text" name="slaughterer_name" value="<?php echo  @$row['slaughterer_name']; ?>" size="55" data-required="yes"></td>
		</tr>
		<tr>
			<th class="sub_title" colspan="2">Appendix (please list your items in this field):</th>
		</tr>
		<tr>
			<td colspan="2">
				<?php
				// $tableTitle['artNt']['english'] = '<b>Consignment Reference:</b><br/>Article No. / Lot No. / Batch No.';
				$tableTitle['description']['english'] = '<b>Description of Shipped Products or Items</b>';
				$tableTitle['quantity']['english'] = '<b>Total Weight</b><br/>in Kilograms';
				?>
				<table>
					<thead id="batchHead">
						<tr id="batchHeadTitles">
							<?php
							foreach ($option as $key => $opValue) {
								if ($key == 'artNt') {
									continue; // Skip artNr as it is not used in the table
								}
								if (isset($opValue['title']) && $opValue['title'] != '') {
									$opValue['english'] = $opValue['title'];
								};
								if (isset($opValue['english'])) { ?>
									<th class="productTitleTh<?php echo strstr($key, 'extra_') ? ' extra' : ''; ?>" style="text-align: center;">
										<div id="<?php echo $key; ?>_english" class="productTitle english"><?php echo $opValue['english']; ?></div>
									</th>
								<?php }; ?>
							<?php }	?>
							<th style="width:20px;">
								<!-- <div class="columnTools"><i class="fas fa-plus" onclick="addProductsColumn()"></i></div> -->
							</th>
						</tr>
						<?php /*
						<tr style="background:#EEE" id="batchHeadWidths">

									$artNt_width = isset($option['artNt']['width']) ? $option['artNt']['width'] : '5';
									$description_width = isset($option['description']['width']) ? $option['description']['width'] : '8';
									$quantity_width = isset($option['quantity']['width']) ? $option['quantity']['width'] : '5';
									?>
							<td>Width: <input type="number" name="option[artNt][width]" id="artNt_width" style="width:80px" value="<?php echo $artNt_width; ?>" onchange="adjustColWidth()" /> CM</td>
							<td>Width: <input type="hidden" name="option[description][width]" id="description_width" style="width:80px" value="<?php echo $description_width; ?>" /><span class="description_width"><?php echo $description_width; ?></span> CM</td>
							<td>Width: <input type="number" name="option[quantity][width]" id="quantity_width" style="width:80px" value="<?php echo $quantity_width; ?>" onchange="adjustColWidth()" /> CM</td>
						<?php
						foreach ($option as $key => $opValue) {
							if (isset($opValue['width'])) {
								if ($key == 'artNt')
									continue; // Skip artNr as it is not used in the table
								if ($key == 'description') { ?>
									<td>Width: <input type="hidden" name="option[description][width]" id="description_width" value="<?php echo $opValue['width']; ?>" /><span class="description_width"><?php echo $opValue['width']; ?></span> CM</td>
								<?php } else { ?>
									<td <?php echo strstr($key, 'extra_') ? 'class="extra"' : ''; ?>>Width: <input type="number" value="<?php echo $opValue['width']; ?>" name="option[<?php echo $key; ?>][width]" data-required="yes" onchange="adjustColWidth()" />cm </td>
								<?php } ?>
							<?php }; ?>
						<?php }	?>
						<td><i class="fas fa-arrows-alt" onclick="sortProducts()"></i></td>
		</tr>
		*/ ?>
					</thead>
					<tbody id="batchProducts"></tbody>
					<tfoot>
						<tr>
							<td id="batchFooter" colspan="<?php echo isset($quantity_titles) ? 5 + count($quantity_titles) : '5'; ?>" style="text-align:center">
								<label style="background: gainsboro; padding: 5px 10px; color: darkred;"><input type="checkbox" name="option[landscape]" id="option_landscape" onchange="adjustColWidth()" value="yes" <?php echo (isset($option['landscape'])) ? 'checked' : ''; ?>> Landscape Appendix</label>
								<b>Font sizes:</b> Table head <input min="5" max="20" id="productsHeadFontSize" style="width:50px" type="number" name="option[products-head-font-size]" value="<?php echo isset($option['products-head-font-size']) ? $option['products-head-font-size'] : '12'; ?>" /> point, Table products <input min="5" max="20" id="productsFontSize" style="width:50px;" type="number" name="option[products-font-size]" value="<?php echo isset($option['products-font-size']) ? $option['products-font-size'] : '12'; ?>" /> point <i class="fas fa-undo-alt" style="font-size:14px !important" onclick="restoreFontSizes()" title="Default font sizes"></i>
								<br />
								<input type="button" onclick="addProduct()" value="Add item" style="margin-left:50px !important" />
								<label class="button">Import items from excel file<input type="file" name="excelCertificateItems" id="excelCertificateItems" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" onchange="getExcelCertificateItems(this)" /></label>
								<input type="button" value="Export items to excel file" onclick="saveExcelCertificateItems()" /><br /><a href="/data/templates/Certificate-appendix.xlsx">You can use the following Certificate-appendix template to upload the items</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</td>
		</tr>
		<?php /*
	<tr>
		<th>Remarks or Notes:</th>
		<td><textarea name="remarks" style="width:100%;height:120px"><?php echo  @$row['remarks']; ?></textarea><br />
			Please state any additional information in this text box.
		</td>
	</tr>
	<tr>
		<th>Internal memo:</th>
		<td><textarea name="memo" style="width:100%;height:120px;"><?php echo  @$row['memo']; ?></textarea>
			<i>Note: This internal memo will only display your remarks towards an employee of Halal Quality Control but will not be visible in the Halal Shipment Certificate or in the QR-Code details. Please leave your comments behind in this text box.</i>
		</td>
	</tr>
	*/ ?>
		<?php
		if ($_SESSION['user_type'] != 'client') {
			$offices = get_offices();
			if ($_GET['tp'] == 'sa' or $_GET['tp'] == 'sb') {
				foreach ($offices as $office_item) {
					if ($options = json_decode($office_item['options'], true)) {
						if (isset($options['SFDA']) && $options['SFDA'] == 'yes') {
							$SFDAOffices[$office_item['offid']] = $office_item['office_name'];
						}
					}
				}
			}
		?>
			<?php if ($act == 'edit') { ?>
				<tr>
					<th>Options:</th>
					<td>
						<label><input type="checkbox" name="keepOldCrtNumber" checked><b>Keep old certificate Nr:</b> <?php echo $row['certificate_nr']; ?></label>
					</td>
				</tr>
			<?php }; ?>
		<?php }; ?>
		<?php if ($_SESSION['user_type'] != 'client') {
		?>
			<?php
			if (isset($office_options) && isset($office_options['certificates_by_email']) && $office_options['certificates_by_email'] == 'yes') { ?>
				<tr>
					<th>HQC options:</th>
					<td>
						<label><input type="checkbox" value="1" name="option[HQCstamp]" id="HQCstamp" <?php echo isset($option['HQCstamp']) ? 'checked' : ''; ?> />Print HQC stamp</label>
						<label><input type="checkbox" value="1" name="option[HQCsignature]" id="HQCsignature" <?php echo isset($option['HQCsignature']) ? 'checked' : ''; ?> />Print HQC signature</label>
					</td>
				</tr>
				<tr>
					<th>Send by Email:</th>
					<td>
						<label><input type="checkbox" name="option[sub_act]" value="email" onclick="printAndSend(this)" <?php echo isset($option['sub_act']) && $option['sub_act'] == 'email' ? 'checked' : ''; ?>>Send certificate by email</label> To:
						<input type="text" name="option[to_email]" value="<?php echo isset($option['to_email']) ? $option['to_email'] : $company_email; ?>" placeholder="Email address" style="width:40%">
					</td>
				</tr>
			<?php }; ?>
		<?php }; ?>
		<?php

		if (!isset($office_options) && isset($offid)) {
			$office = get_office_data($offid);
			if (is_array(json_decode($office['options'], true))) {
				$office_options = json_decode($office['options'], true);
			} else {
				$office_options = array();
			};
		} ?>
		<tr>
			<th>Attachments:</th>
			<td>
				<input type="file" name="attachment[]" multiple>
				<?php if (isset($row['attachments']) and trim($row['attachments']) != '' && is_array(decode_json($row['attachments']))) {
					$attachments = decode_json($row['attachments']);
					if (count($attachments) > 0) {
						echo "<ol>";
						foreach ($attachments as $attachment) { ?>
							<li style="padding:5px"><a href='<?php echo $attachment; ?>' target='_blank'><?php echo basename($attachment); ?></a>
								<i class="far fa-trash-alt" style="font-size:12px !important;color:red;margin-left:20px" onclick="deleteAttachment(this)"><span>Delete</span></i>
					<?php };
						echo "</ol>";
					}
				}; ?>
			</td>
		</tr>
		<tr id="formActionButtons">
			<td colspan=2 class="sub_title" style="text-align:center">
				<input type=button onclick="history.go(-1)" value=" Cancel ">
				<input type=button onclick="document.certificateForm.reset()" value=" Reset ">
				<input type=button onclick="preview()" value=" Preview ">
				<?php if ($act == 'add') { ?>
					<input type=button onclick="save_hc('draft')" value="Save draft" />
					<?php } else if ($act == 'edit') {
					if ($row['status'] == 'draft') { ?>
						<input type=button onclick="save_hc('saveDraft')" value="Update draft" />
					<?php } else { ?>
						<input type=button onclick="save_hc('save')" value="Update">
				<?php };
				}; ?>
				<?php if ($_SESSION['user_type'] == 'client') { ?>
					<input type=button onclick="save_hc('request')" value="Request certificate" style="color:red">
				<?php } else { ?>
					<input type=button id="actionPrint" onclick="save_hc('print')" value="Print" />
				<?php }; ?>
			</td>
		</tr>
	</table>
	<div id="sabWarning" style="color:red;text-align:center"></div>
	<input type=hidden name=tmplid value="<?php echo $act == 'edit' ? $row['tmplid'] : $offid; ?>">
</form>
<script>
	function printAndSend(obj) {
		if (jQuery(obj).is(':checked')) {
			jQuery("#actionPrint").val('Email certificate')
		} else {
			jQuery("#actionPrint").val('Print')
		}

	}

	function restoreFontSizes() {
		jQuery("#productsHeadFontSize").val(12);
		jQuery("#productsFontSize").val(12);
	}

	function addProduct() {
		count = jQuery("#batchProducts tr").length
		columns = jQuery("#batchProducts tr").first().find('input').length
		newRow = jQuery("#batchProducts tr").last().html();
		jQuery("#batchProducts").append('<tr>' + newRow + '</tr>');
		jQuery("#batchProducts tr").last().find('input').each(function() {
			jQuery(this).val('');
			jQuery(this).prop('id', jQuery(this).prop('id').replace(count - 1, count));
			jQuery(this).prop('name', jQuery(this).prop('name').replace(count - 1, count));
		})

		// jQuery.get("load_products.php?count=" + count + '&columns=' + columns, function(data) {
		// 	jQuery("#batchProducts").append(data);
		// 	resetSortable();
		// });
	}

	async function deletedThisColumn(obj) {
		resetSortable();
		await confirm_message("Are you sure you want to delete this column?");
		//get the index of the column
		index = jQuery(obj).closest('th').index();
		//remove the column
		jQuery(obj).closest('th').remove();

		jQuery("#batchHead tr").each(function() {
			jQuery(this).find('td').eq(index).remove();
		})

		jQuery("#batchProducts tr").each(function() {
			jQuery(this).find('td').eq(index).remove();
		})

		jQuery("#batchFooter").prop("colspan", jQuery("#batchHead .productTitleTh").length + 1);
	}

	function deleteProduct(obj) {
		jQuery(obj).closest('tr').remove();
		resetSortable();
	}

	//create function to move column left and right in a table using jquery
	function moveColumn(from, to) {
		var rows = jQuery("#batchHead,#batchProducts").find('tr');
		var cols;
		rows.each(function() {
			cols = $(this).children('th, td');
			cols.eq(from).detach().insertBefore(cols.eq(to));
		});
	};

	function moveColumnLeftRight(obj, direction) {
		from = jQuery(obj).closest('th').index();
		if (direction == 'right')
			to = from + 2;
		else
			to = from - 1;
		thCount = jQuery("#batchHead").find('th').length;

		if (to < thCount && to > -1)
			moveColumn(from, to);
	}

	//after loading the products from the database call the function to reset the sortable
	jQuery("#batchProducts").load("load_products.php" + location.search, function() {
		resetSortable();
	});

	function countDates() {
		if (jQuery("#slaughtering_date_counter").length > 0) {
			jQuery("#slaughtering_date_counter").html(jQuery("#slaughtering_date").val().length)
			if (jQuery("#slaughtering_date").val().length > 90)
				jQuery("#slaughtering_date_counter").css("color", "red")
			else
				jQuery("#slaughtering_date_counter").css("color", "green")
		}

		if (jQuery("#production_date_counter").length > 0) {
			jQuery("#production_date_counter").html(jQuery("#production_date").val().length)
			if (jQuery("#production_date").val().length > 90)
				jQuery("#production_date_counter").css("color", "red")
			else
				jQuery("#production_date_counter").css("color", "green")
		}

		if (jQuery("#expiry_date_counter").length > 0) {
			jQuery("#expiry_date_counter").html(jQuery("#expiry_date").val().length)
			if (jQuery("#expiry_date").val().length > 90)
				jQuery("#expiry_date_counter").css("color", "red")
			else
				jQuery("#expiry_date_counter").css("color", "green")
		}
	}

	function fillProductTitle() {
		resetSortable();
		jQuery("#batchHead .productTitleTh").each(function() {

			if (jQuery(this).find('input').length > 0)
				jQuery(this).find('input').remove();

			english = jQuery(this).find('.english');
			englishTitle = jQuery(english).prop('id').split('_');
			if (englishTitle[0] == 'extra') {
				insertDeleteIcon = true;
				englishTitle = englishTitle[0] + '_' + englishTitle[1];
			} else {
				insertDeleteIcon = false;
				englishTitle = englishTitle[0];
			}
			englishValue = jQuery(english).html();

			jQuery(this).append('<input type="hidden" name="option[' + englishTitle + '][english]" value="' + englishValue + '">');
		})
		addColumnTools();
		jQuery("#batchFooter").prop("colspan", jQuery("#batchHead .productTitleTh").length + 1);
	}

	var contenteditable = false;

	function editThisTitle(obj) {

		jQuery(obj).closest('tr').find('.english').prop('contenteditable', false).removeClass('active');
		jQuery(obj).closest('tr').find('.fa').css({
			"color": "",
			"background": "",
			"display": "initial"
		});
		jQuery(obj).removeClass('fa-save').addClass('fa-edit');

		if (contenteditable == false) {
			jQuery(obj).closest('th').find('.english').prop('contenteditable', true).addClass('active');
			jQuery(".openAi").on("focus", function() {
				jQuery("#openAiButton").css("display", "initial")
			})
			jQuery(".openAi").on("blur", function() {
				setTimeout(function() {
					jQuery("#openAiButton").css("display", "none")
				}, 1000)
			})
			contenteditable = true;
			jQuery(obj).closest('tr').find('.fa-edit').css({
				"color": "",
				"background": "",
				"display": "none"
			});
			jQuery(obj).css({
				"color": "green",
				"background": "beige",
				"display": "initial"
			}).removeClass('fa-edit').addClass('fa-save');
		} else {
			contenteditable = false;
			fillProductTitle();
		}
	}
	// attach function to check when the table body #batchProducts tr count is changed
	// jQuery("#batchProducts").bind("DOMSubtreeModified", function() {
	// 	resetSortable();
	// });

	function checkFSDA() {
		tp = '<?php echo $_GET['tp']; ?>'
		if (jQuery("#selectedImporter").val() != '') {
			if (jQuery("#selectedImporter option:selected").data('sfda') == 'yes') {
				jQuery("#tmplid option").each(function() {
					if (jQuery(this).data('sfda') == 'no')
						jQuery(this).css("display", "none").removeAttr('selected');
				})
				if (tp == 'a' || tp == 'b') {
					sab = "S" + tp.toUpperCase();
					url = "<a href='/certificates/?inc=certificate_ab&tp=s" + tp + "'>Click here to switch to  certificate type " + sab + "</a>";
					jQuery("#CRN").html("Please use certificates for Saudi Arabia type (" + sab + ')<br/>' + url);
					jQuery("#sabWarning").html(jQuery("#CRN").html());
					jQuery("#formActionButtons").hide();

				}
				jQuery("#CRN").show();
				if (jQuery("#CRNValue"))
					jQuery("#CRNValue").val(jQuery("#selectedImporter option:selected").data('crn')).attr("data-required", "yes");
			} else {
				jQuery("#tmplid option").css("display", "block");
				jQuery("#CRN").hide();
				jQuery("#formActionButtons").show();
				jQuery("#sabWarning").html('');
				if (jQuery("#CRNValue"))
					jQuery("#CRNValue").val('').removeAttr("data-required");
			}

		} else {
			jQuery("#tmplid option").css("display", "block");
			jQuery("#CRN").hide();
			jQuery("#formActionButtons").show();
			jQuery("#sabWarning").html('');
			if (jQuery("#CRNValue"))
				jQuery("#CRNValue").val('').removeAttr("data-required");
		}
	}
	jQuery("#selectedImporter").on("change", function() {
		checkFSDA();
	});

	async function deleteAttachment(obj) {
		objectParent = jQuery(obj).parent('li').find('a')
		file = jQuery(objectParent).attr('href');
		fileName = jQuery(objectParent).text();
		await confirm_message("Are you sure you want to delete this attachment?<br/><span style='color:red'>" + fileName + '</span>');
		//delete the attachment
		jQuery.ajax({
			url: "certificate_save.php",
			type: "POST",
			data: {
				act: 'delete_file',
				file: file,
				nr: '<?php echo isset($_GET['nr']) ? $_GET['nr'] : ''; ?>',
				tp: '<?php echo $_GET['tp']; ?>'
			},
			success: function(data) {
				if (data != '') {
					jQuery(obj).parent('li').remove();
				}
			}
		})
	}
	jQuery(document).ready(function() {
		checkFSDA();
		countDates();
		insertProductTitle();
		fillProductTitle();
		jQuery("#slaughtering_date,#production_date,#expiry_date").on("keyup", function() {
			countDates();
		})

	})
</script>