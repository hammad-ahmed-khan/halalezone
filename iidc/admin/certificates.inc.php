<div id="fixDocNrDiv" style="display:none;position:absolute">
	<input type="hidden" id="crtId" />
	<input type="text" id="crtDocNr" />
</div>
<?php
$cert_types = [
    'a' => ['name' => 'HA: Slaughtering Certificate', 'icon' => 'fa-drumstick-bite', 'class' => 'type-ha'],
    'b' => ['name' => 'HB: Non-meat Certificate', 'icon' => 'fa-leaf', 'class' => 'type-hb'],
    'sa' => ['name' => 'SA: Slaughtering Certificate (Saudi Arabia)', 'icon' => 'fa-drumstick-bite', 'class' => 'type-sa'],
    'sb' => ['name' => 'SB: Non-meat Certificate (Saudi Arabia)', 'icon' => 'fa-leaf', 'class' => 'type-sb']
];

$currentType = isset($_GET['tp']) ? $_GET['tp'] : 'a';
$certInfo = isset($cert_types[$currentType]) ? $cert_types[$currentType] : $cert_types['a'];

if (!isset($_SESSION['offid']) or trim($_SESSION['offid']) == '0') {
    $offIds = array();
    if ($offices = $amdb->query("SELECT * FROM offices WHERE status != 'deleted'")) {
        foreach ($offices as $office)
            $offIds[$office['offid']] = $office['offid'];
    }
?>

<div class="shipment-header-section">
    <div class="shipment-header-content">
        <div class="shipment-header-left">
            <div class="shipment-header-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="shipment-header-info">
                <h3>Shipment Certificates</h3>
                <p>Issue and manage shipment certification documents</p>
            </div>
            <span class="cert-type-badge <?php echo $certInfo['class']; ?>">
                <i class="fas <?php echo $certInfo['icon']; ?>"></i>
                <?php echo $currentType; ?>
            </span>
        </div>
        
        <div class="office-selector-wrapper">
            <label for="officeSelect">Select Office:</label>
            <select class="office-selector" id="officeSelect" name="offid" 
                    onchange="document.location='<?php echo $prog_www; ?>/admin/?inc=certificates&tp=<?php echo $_GET['tp']; ?>&offid='+this.value;">
                <option value="*" <?php echo (isset($_GET['offid']) && $_GET['offid'] == '*') ? 'selected' : ''; ?>>All Offices</option>
                <?php
                $offices = $amdb->query("SELECT * FROM offices WHERE status != 'deleted'");
                if (count($offices) > 0) {
                    include "$hcp_path/config/countries.code.php";
                    foreach ($offices as $office) {
                        if (isset($offIds[$office['offid']])) { ?>
                            <option value="<?php echo $office['offid']; ?>" <?php echo (isset($_GET['offid']) && $_GET['offid'] == $office['offid']) ? 'selected' : ''; ?>>
                                <?php echo $country[$office['office_country']]; ?> - <?php echo $office['office_name']; ?>
                            </option>
                <?php
                        }
                    }
                }
                ?>
            </select>
            
            <?php if (isset($_GET['offid']) && $_GET['offid'] != '*') { ?>
                <a href="<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=<?php echo $_GET['tp']; ?>&offid=<?php echo $_GET['offid']; ?>" 
                   class="btn-issue-shipment">
                    <i class="fas fa-plus-circle"></i>
                    Issue Certificate
                </a>
            <?php } else { ?>
                <span class="btn-issue-shipment disabled" title="Please select an office first">
                    <i class="fas fa-plus-circle"></i>
                    Issue Certificate
                </span>
            <?php } ?>
        </div>
    </div>
</div>

<?php } else { 
    // When session office is set, show simplified header
?>

<div class="shipment-header-section">
    <div class="shipment-header-content">
        <div class="shipment-header-left">
            <div class="shipment-header-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="shipment-header-info">
                <h3>Shipment Certificates</h3>
                <p>Issue and manage shipment certification documents</p>
            </div>
            <span class="cert-type-badge <?php echo $certInfo['class']; ?>">
                <i class="fas <?php echo $certInfo['icon']; ?>"></i>
                <?php echo $certInfo['name']; ?>
            </span>
        </div>
        
        <a href="<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=<?php echo $_GET['tp']; ?>&offid=<?php echo $_SESSION['offid']; ?>" 
           class="btn-issue-shipment">
            <i class="fas fa-plus-circle"></i>
            Issue Certificate
        </a>
    </div>
</div>

<?php }
if (!isset($_GET['offid']) or trim($_GET['offid']) == '') {
	$_GET['offid'] = $_SESSION['offid'];
}
?>
<script>
	$("#page_title").html("Issued shipment Certificates");

	function delcer(nr, tp) {

		alert_confirm('Are you sure, delete certificate?');
		jQuery("button#alertYesBtn").click(function() {
			close_alert();
			jQuery.post('<?php echo $prog_www ?>/admin/admin_save.php', {
				act: 'delcer',
				nr: nr,
				tp: tp
			}).done(function(data) {
				if (data.trim() == 'success') {
					jQuery("tr[data-nr='" + nr + "']").css('display', 'none');
				} else {
					alert_message(data);
					return false;
				}
			});
		})
	}

	function undoCer(nr, tp, obj) {
		alert_confirm('Are you sure, undo print / authorize?');
		jQuery("button#alertYesBtn").click(function() {
			close_alert();
			jQuery.post('<?php echo $prog_www ?>/admin/certificates_save.php', {
				act: 'undo_printed_authorized',
				nr,
				tp
			}).done(function(data) {
				if (data.trim().length > 0) {
					if (data.toLowerCase().indexOf("error:") > -1) {
						alert_message(data.replace('error:', ''));
						return false;
					} else {
						jQuery(obj).parents('tr').css('display', 'none');
						close_alert();
					}
				}
			});
		})
	}

	function badCer(goodBad, nr) {
		if (confirm("Are you sure?") == "1") {
			var time = new Date().getTime();
			$.post("<?php echo $prog_www ?>/admin/admin_save.php?tm=" + time, {
					act: "badCer",
					tp: '<?php echo $_GET['tp']; ?>',
					nr: nr,
					goodBad: goodBad
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

	var selectedCrtNr = null;

	function fixCerNr(nr, doc_nr) {
		var time = new Date().getTime();
		$.post("<?php echo $prog_www ?>/admin/certificates_save.php?tm=" + time, {
				act: "fixCerNr",
				tp: '<?php echo $_GET['tp']; ?>',
				nr: nr,
				doc_nr: doc_nr
			},
			function(data) {
				if (data != "") {
					if (data.indexOf('success') > -1) {
						jQuery(selectedCrtNr).html(doc_nr)
						jQuery(selectedCrtNr).attr('data-crtdocnr', doc_nr);
						$("#fixDocNrDiv").css('display', 'none')
					} else {
						alert(data);
					}
				}
			});
	}

	function changeCertDocNumber() {
		$(".crtDocNr").css({
			"cursor": "pointer"
		});

		$('#shipmentCertificates tr').unbind('mouseenter');
		$('#shipmentCertificates tr').bind('mouseenter', function() {
			$('#crtId').val($(this).attr('data-nr'));
		});
		$('#shipmentCertificates .crtDocNr').unbind('click');
		$('#shipmentCertificates .crtDocNr').bind('click', function() {
			$('#crtDocNr').val($(this).attr('data-crtDocNr'));
			$('#crtDocNr').css({
				"width": $(this).width()
			});
			var position = $(this).position();
			$("#fixDocNrDiv").css({
				"left": position.left + "px",
				"top": position.top + "px",
				"display": "block",
				"z-index": "1000"
			});
			jQuery("#crtDocNr").focus()
			selectedCrtNr = jQuery(this)
		});

		$("#crtDocNr").keypress(function(event) {
			if (event.which == 13) {
				fixCerNr($('#crtId').val(), $('#crtDocNr').val());
			}
		});
	}

	function confirmByadmin(nr, hcNr) {
		$("#hcNr").html("<b>" + hcNr + "</b>")
		$("#toBconfirmNr").val(nr)
		$("#arrival_date").val("")
		showPopupDialog('confirmTbl', 'confirm')
	}
	var itemNr
	var clid;
	var st = 0;
	var lmt = 50;
	var orderBy = 'issue_date';
	var ascDsc = 'DESC';
	var srearchQ = '';
	var searchField = '';
	var certYear = '<?php echo date('Y'); ?>';
	var offid = '<?php echo $_GET['offid']; ?>';
	var country = '';
	var fromDate = '';
	var toDate = '';
	var exportExcel = 'no';
	var startLoading = true;

	function loadCertificates(start) {
		st = start;
		jQuery.post('/iidc/admin/load_certificates.php', {
			tp: '<?php echo $_GET['tp']; ?>',
			offid: offid,
			country: country,
			st: start,
			lmt: lmt,
			clid: clid,
			orderBy: orderBy,
			ascDsc: ascDsc,
			srearchQ: srearchQ,
			searchField: searchField,
			year: certYear,
			fromDate: fromDate,
			toDate: toDate,
			exportExcel: exportExcel
		}).done(function(data) {
			//alert(data);
			if (data.trim().length > 0) {
				if (jQuery("#certificateItemsLoading")) {
					jQuery("#certificateItemsLoading").remove();
				}
				if (data.indexOf("error:") > -1) {
					if (data.replace('error:', '').trim().length > 2)
						jQuery("#certificateItems").html('<td colspan="12" style="text-align:center;vertical-align:middle;">' + data.replace('error:', '').trim() + '</td>');
					jQuery('#searchHead').css("display", "none")
				} else {
					if (exportExcel == "yes") {
						doExportCertificates(data);
						return false;
					} else if (start == 0)
						jQuery("#certificateItems").html(data);
					else
						jQuery("#certificateItems").append(data);
					post_links();
					changeCertDocNumber();
					do_document_ready();
					jQuery(".load_popup").unbind('click');
					load_popup();
					//doSearch('#searchHead', '#certificateItems', '#fixDocNrDiv');
					startLoading = true;
					jQuery('#searchHead').css("display", "table-row")
					doSearch('#searchHead', '#certificateItems', '#fixDocNrDiv');
				}
			}
		});
	}

	function searchShipmentCertificates() {
		srearchQ = '';
		searchField = '';
		if (jQuery("select#year").val() == 'd2d') {
			fromDate = jQuery("#from_date").val();
			toDate = jQuery("#to_date").val();
			if (fromDate == '' || toDate == '') {
				alert('Please select from and to date');
				return false;
			}
			loadCertificates(0);
		} else {
			if (jQuery("#srearchQ").val().trim() != '' || jQuery("#searchField").val() == 'country') {
				if (jQuery("#searchField").val() == 'country') {
					country = jQuery('select#country').val()
				} else {
					country = '';
				}

				srearchQ = jQuery("#srearchQ").val();
				searchField = jQuery("#searchField").val();
			}
			loadCertificates(0);
		}
		return false;
	}

	function loadCountries() {
		jQuery(document).ready(function(e) {
			if (jQuery("#searchField").val() == 'country') {
				year = jQuery("#year").val();
				jQuery("#country").load("/admin/load_certificates.php?act=load_countries&offid=<?php echo $_GET['offid']; ?>&tp=<?php echo $_GET['tp']; ?>&year=" + year);
			}
			return false;
		});
	}

	function changeYear(crtYear) {
		certYear = crtYear;
		if (crtYear == 'd2d') {
			jQuery("#srearchQ").val('').css("display", "none")
			jQuery("#searchField").css("display", "none")
			jQuery("input#from_date").val('')
			jQuery("input#to_date").val('')
			jQuery("#fromDateToDate").css("display", "inline-block")

		} else {
			jQuery("#fromDateToDate").css("display", "none")
			jQuery("#srearchQ").css("display", "")
			jQuery("#searchField").css("display", "")
			loadCertificates(0);
		}
	}

	function showCountries(val) {
		jQuery(document).ready(function(e) {
			jQuery('select#country').css('display', 'none');
			if (val == 'country') {
				jQuery('select#country').css('display', 'inline-block');
				loadCountries()
			}
		});
	}

	//TODO add export to excel to new system
	function exportCertificates() {
		exportExcel = 'yes';
		loadCertificates(0);
	}

	function doExportCertificates(expItems) {
		//expItems = JSON.stringify(certificates);
		exportExcel = 'no';
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "/certificates/annual/export_excel_file.php?tp=<?php echo $_GET['tp']; ?>");
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
</script>
<style>
/* Shipment Certificate Header Section */
.shipment-header-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf9 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin: 15px 0 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.shipment-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 32px;
    flex-wrap: wrap;
    gap: 20px;
}

.shipment-header-left {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.shipment-header-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    flex-shrink: 0;
}

.shipment-header-info h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.shipment-header-info p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}

/* Office Selector */
.office-selector-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.office-selector-wrapper label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
}

.office-selector {
    min-width: 280px;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
	height:34px;
}

.office-selector:hover {
    border-color: #0284c7;
    background-color: #f8fafc;
}

.office-selector:focus {
    outline: none;
    border-color: #0284c7;
    box-shadow: 0 0 0 4px rgba(3, 105, 161, 0.12);
}

/* Issue Button */
.btn-issue-shipment {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
    border: none;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(3, 105, 161, 0.3);
    white-space: nowrap;
}

.btn-issue-shipment:hover {
    background: linear-gradient(135deg, #075985 0%, #0369a1 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(3, 105, 161, 0.4);
    color: #ffffff;
    text-decoration: none;
}

.btn-issue-shipment:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(3, 105, 161, 0.3);
}

.btn-issue-shipment i {
    font-size: 15px;
}

.btn-issue-shipment.disabled {
    background: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.7;
}

.btn-issue-shipment.disabled:hover {
    transform: none;
    background: #94a3b8;
    box-shadow: none;
}

/* Certificate Type Badge */
.cert-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cert-type-badge.type-ha { background: #dcfce7; color: #166534; }
.cert-type-badge.type-hb { background: #fef3c7; color: #92400e; }
.cert-type-badge.type-sa { background: #e0e7ff; color: #3730a3; }
.cert-type-badge.type-sb { background: #fce7f3; color: #9d174d; }

/* Responsive */
@media (max-width: 768px) {
    .shipment-header-content {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
        padding: 20px;
    }
    
    .shipment-header-left {
        flex-direction: column;
        align-items: center;
    }
    
    .office-selector-wrapper {
        flex-direction: column;
        width: 100%;
    }
    
    .office-selector {
        width: 100%;
        min-width: unset;
    }
    
    .btn-issue-shipment {
        width: 100%;
        justify-content: center;
    }
}

	#searchHead td input {
		width: 99% !important;
		padding: 4px 10px;
	}

	#shipmentCertificates td.nowrap b {
		width: 110px;
		display: inline-block
	}

	select {
		padding: 6px 8px
	}

	td.crtDocNr {
		background-image: url(../images/edit.svg);
		background-repeat: no-repeat;
		background-position: right 3px;
		background-size: 20px;
	}
</style>
<?php
if ($_GET['offid'] != '*')
	$whrYear = "WHERE offid = $_GET[offid]";
else
	$whrYear = "";

$years = $amdb->get_row("SELECT YEAR(inserted_on) as year FROM certificates_{$_GET['tp']} $whrYear");
if (!isset($years['year'])) {
	$years['year'] = date("Y");
}
?>
<table border=0 class="table table-striped table-bordered" id="shipmentCertificates" style="background:#fff;width:100%">
	<thead>
		<tr class="alternateOff">
			<td colspan=10 class="sub_title">
				<div><b style="font-size:16px;margin: 10px !important; display: block;">Issued shipment certificates</b></div>
				<input type="button" value="Export to excel" style="float: right;" onclick="exportCertificates()" />
				<div style="float:left;padding:5px 0">
					<select size="1" name="year" id="year" onchange="changeYear(this.value)">
						<option value="d2d">Date to date</option>
						<?php for ($year = date("Y"); $year >= $years['year']; $year--) { ?>
							<option value="<?php echo $year; ?>" <?php echo $year == date("Y") ? 'selected' : ''; ?>><?php echo $year; ?></option>
						<?php }; ?>
					</select>
				</div>
				<div style="float:left">
					<form onsubmit="return searchShipmentCertificates()">
						<input type="hidden" value="<?php echo $_GET['offid']; ?>" name="offid" />
						<span id="fromDateToDate" style="display:none">
							<input type="date" name="from_date" id="from_date" placeholder="From date" /> To : <input type="date" name="to_date" id="to_date" placeholder="To date" />
						</span>
						<input type="text" name="srearchQ" id="srearchQ" placeholder="Search Certificates" />
						<select size="1" name="searchField" id="searchField" onchange="showCountries(this.value)">
							<option value="certificate_nr">Certificate Nr</option>
							<option value="doc_nr">Document Nr</option>
							<option value="companies.company_name">Company Name</option>
							<option value="reference">Reference</option>
							<option value="country">Country</option>
						</select>
						<?php if (!isset($_SESSION['offid']) or $_SESSION['offid'] == '0') { ?>
							<select size="1" name="country" id="country" style="display:none">
							</select>
						<?php }; ?>
						<input type="submit" value="search" />
					</form>
				</div>
			</td>
		</tr>
		<tr id="headerThx">
			<th>No.</th>
			<th data-id="certificate_nr">Certificate Nr.</th>
			<th data-id="issue_date">Issue date</th>
			<th width="80">Weight</th>
			<th data-id="importer">Importer & country</th>
			<th data-id="company_name">Company</th>
			<th data-id="reference">Ref.</th>
			<th style="width:180px">Status</th>
			<?php if ((isset($user_permissions) && in_array("certificates_actions", $user_permissions)) or $_SESSION['user_type'] == "admin" or $_SESSION['user_type'] == 'hqc_office') { ?>
				<th>Action</th>
			<?php } ?>
		</tr>
		<?php /*<tr id="searchHead" class="alternateOff" style="display:none;background:#eee">
			<th></th>
			<td><input type="text" id="certificate_nr" /></td>
			<?php if ($_SESSION['user_type'] == 'admin') { ?>
				<td><input type="text" id="doc_nr" /></td>
			<?php }; ?>
			<td><input type="text" id="issue_date" /></td>
			<td></td>
			<td><input type="text" id="importer" /></td>
			<td><input type="text" id="company_name" /></td>
			<td><input type="text" id="reference" /></td>
			<td><input type="text" id="status" /></td>
			<?php if ((isset($user_permissions) && in_array("certificates_actions", $user_permissions)) or $_SESSION['user_type'] == "admin" or $_SESSION['user_type'] == 'hqc_office') { ?><td></td><?php } ?>
		</tr>*/ ?>
	</thead>
	<tbody id="certificateItems">
		<tr id="certificateItemsLoading"></tr>
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
		jQuery("#fixDocNrDiv").css("display", "none")
		jQuery("#searchHead input").val('')
		loadCertificates(0);
	});
	<?php if (!isset($_GET['search'])) { ?>
		loadCertificates(0);
	<?php }; ?>
	<?php if (!isset($_SESSION['offid']) or $_SESSION['offid'] == '0') { ?>
		loadCountries();
	<?php }; ?>

	jQuery(window).scroll(function() {
		windowFromBottom = jQuery(document).height() - jQuery(document).scrollTop();
		if (windowFromBottom < jQuery(window).height() * 2 && startLoading) {
			loadCertificates(st + lmt);
			startLoading = false;
		}
	});
</script>