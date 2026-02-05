<?php
if (!defined("__HQC__")) {
	exit();
};
//TODO: upload the file to the server
include "../checkuser.inc.php";
include "../config/paths.inc.php";
?>
<style>
	.hidden,
	.hideSelected {
		visibility: hidden;
		position: fixed;
		top: -5000px;
		display: none;
	}

	#exportItems input[type='text'] {
		padding: 5px 10px;
		margin-left: 5px;
		height: 20px;
	}

	.auditorsHolder {
		max-width: 200px;
	}

	.office {
		max-width: 300px
	}

	td.actions div i {
		font-size: 18px !important;
		float: left;
		margin-right: 10px;
		width: 30px;
	}

	td.actions div {
		clear: both;
		margin-bottom: 10px;
		white-space: nowrap;
		width: 180px;
		overflow: hidden;
	}

	tr.underline {
		background: #f7f3ec;
		padding: 10px 2px;
	}

	tr.underline th,
	tr.underline td {
		padding: 5px 2px;
	}

	td div {
		overflow: hidden;
	}

	td fieldset {
		height: 30px;
		overflow: hidden;
		border: 1px solid #ccc;
		cursor: pointer;
		transition: height 0.3s ease;
		line-height: 18px;
		padding-top: 0px;
	}

	.expanded {
		height: auto;
		/* Allow full text to show */
		line-height: inherit;
	}
</style>
<script>
	$("#page_title").html("HQC clients")

	function susClient(id) {
		jQuery.post("/admin/client_save.php?act=sus&id=" + id, function(data) {
			if (data != "") {
				document.location.reload();
			};
		});
	}

	function reinsClient(id) {
		jQuery.post("/admin/client_save.php?act=reins&id=" + id, function(data) {
			if (data != "") {
				document.location.reload();
			};
		});
	}

	function delclients(obj) {
		if (confirm("Are you sure?") == "1") {
			return true;
		} else {
			return false;
		}
	}

	function doLogInasCl(username, password) {
		if ($("#username").val() == "Username" || $("#password").val() == "Password") {
			alert("Please fill in the username and  password");
			return false;
		} else {
			var time = new Date().getTime();
			location = "<?php echo $prog_www ?>/login_out.php?tm=" + time + "&act=logIn&username=" + username + "&password=" + password + "&asClient=y";
			return false;
		}
		return false;
	}
	jQuery.expr[':'].Contains = function(a, i, m) {
		return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
	};
	// Overwrites old selecor
	jQuery.expr[':'].contains = function(a, i, m) {
		return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
	};

	function doSearch(act) {
		if (act == 'reset') {
			location.reload();
			return false;
		}

		if (act == 'search') {

			var foundNr = 1;
			srchWht = jQuery("#srchWht").val();
			srch = "." + jQuery("#srch").val();

			if (srchWht.length < 3)
				return false;

			jQuery("tbody#clientsList tr").addClass('hidden');
			jQuery("tbody#clientsList " + srch + ":contains('" + srchWht + "')").each(function(index, element) {
				tr = jQuery(this).parents("tr")
				tr.find("span.foundNr").html(foundNr++);
				tr.find("span.nr").css('display', 'none');
				tr.removeClass('hidden');
			});
			return false;
		}


		var clids = [];
		// get a list of tbody#clientsList tr visibility is visible
		if (jQuery("#exportAll").is(":checked")) {
			jQuery("#form_act").val('all');
		} else {
			jQuery("tbody#clientsList tr").each(function() {
				if (jQuery(this).hasClass('hidden') == false)
					clids.push(jQuery(this).data('id'));
			})

			jQuery("#form_clids").val(clids);
			jQuery("#form_act").val(act);
		}

		if (act == 'exportClients') {
			jQuery("#clients_search_form").prop({
				'action': 'export_clients.php'
			});
		}

		if (act == 'exportCertificates') {
			jQuery("#clients_search_form").prop({
				'action': 'export_certificates.php'
			});
		}

		if (act == 'exportEmailsCsv' ||
			act == 'exportEmailsExcel') {
			jQuery("#clients_search_form").prop({
				'action': 'export_emails.php'
			});
		}
		jQuery("#clients_search_form").submit();
	}

	function searchForAuditor(val) {

		var foundNr = 1;

		if (val.length < 3) {
			jQuery("tbody#clientsList tr").removeClass('hidden').removeClass('underline');
			jQuery("table#theClients").addClass('alternateOn');
			return false;
		}

		jQuery("tbody#clientsList tr").addClass('hidden');
		jQuery("table#theClients").removeClass('alternateOn');
		jQuery("tbody#clientsList .auditorsHolder:contains('" + val + "')").each(function(index, element) {
			tr = jQuery(this).parents("tr")
			tr.find("span.foundNr").html(foundNr++);
			tr.find("span.nr").css('display', 'none');
			tr.removeClass('hidden');
			//if tr is even add class underline
			if (foundNr % 2 == 0) {
				tr.addClass('underline');
			}
		});
	}

	function showHideScope(obj) {
		obj.classList.toggle('expanded');
	}
</script>
<style>
	i span {
		font-size: 12px !important;
		width: 20px;
		display: inline-block;
		margin: -5px -10px 5px 5px;
		font-weight: normal;
		font-family: times;
	}

	.load_popup:hover {
		color: red
	}
</style>
<h1 style="text-align: center;"><?php echo (isset($_GET['active']) && $_GET['active'] == 'n') ? 'Suspended clients' : 'Clients List'; ?></h1>
<div id="clientsSearchBox">
	<div>
		<div class="infoBox">
			<b>Clients</b> are companies that are registered in the system. They can be audited and have certificates issued for them. You can search for clients by company name, country, city, ID, email or scope of activities. You can also filter clients by their certificate status (valid, expired or no certificate). The list is loaded dynamically as you scroll down the page.
			<?php if (!isset($_GET['active'])) {
				$active = "y"; ?>
				<a href="index.php?inc=clients&active=n">Show Suspended clients</a>
			<?php } else {
				$active = "n"; ?>
				<a href='index.php?inc=clients'>Show Active clients</a>
			<?php } ?>
		</div>
		<div class=title>
			<form method="post" name="clients_search_form" id="clients_search_form" target="_new">
				<input type="hidden" name="act" id="form_act" value="" />
				<input type="hidden" name="clids" id="form_clids" value="" />
			</form>
			<b>Search for:</b>
			<input type=text size=30 name="srchWht" id="srchWht" size="40px" onkeyup="liveSearch(this.value);" />
			<select size="1" name="srch" id="srch" onchange="loadClients()">
				<option value="company_name">Company Name</option>
				<option value="country1">Country</option>
				<option value="city1">City</option>
				<option value="clid">ID</option>
				<option value="email">Email</option>
				<option value="scope_of_activities">Scope of activities</option>
			</select>
			<select name="certificates" id="certificates" onchange="loadClients()">
				<option value="*">All certificates</option>
				<option value="green">Green (Valid certificates)</option>
				<option value="red">Red (Expired certificates)</option>
				<option value="grey">Grey (No certificate)</option>
			</select>
			<input type="button" value="Reset" onclick="doSearch('reset')" />
			<div style="float:right" id="exportItems">
				Export: <label for="exportAll" style="font-weight:normal;"><input type="checkbox" name="exportAll" id="exportAll" />All clients</label><input type="button" value="Clients as Excel" onclick="doSearch('exportClients');" />
			</div>

		</div>
	</div>

</div>
<?php
?>
<table border=0 id="theClients" class="table table-striped table-bordered" width="100%">
	<thead>
		<tr>
			<th style="width:40px">::</th>
			<!-- <th style="width:60px">ID</th> -->
			<th>Company</th>
			<th>Office / contact</th>
			<th>Audit <input type="text" class="search" onkeyup="searchForAuditor(this.value)" style="width:150px" /></th>
			<th style="width:165px !important;padding: 3px;text-align: center;" title="Annual certificate">Annual Cert.</th>
			<!-- <th style="width: 70px !important;padding: 3px;text-align: center;" title="Batch certificates">A/BCRT</th> -->
			<?php if ((isset($user_permissions) and in_array("clients_actions", $user_permissions)) or $_SESSION['user_type'] == "admin" or isset($_SESSION['offid']) && $_SESSION['offid'] != 0) { ?>
				<th style="width:120px">Action</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody id="clientsList"></tbody>
</table>
<div style="padding:20px;text-align:center;">
	<a href="index.php?inc=upload_clients" target="iframe" data-height="150">Upload Clients using Excel file</a>
</div>
<script>
	var st = 0;
	lmt = 50;

	function loadClients() {
		if (st == 0) {
			jQuery("#clientsList").html("");
		}
		jQuery.post('load_clients.php', {
			time: '<?php echo time(); ?>',
			active: '<?php echo $active; ?>',
			srchWht: jQuery("#srchWht").val(),
			srch: jQuery("#srch").val(),
			certificates: jQuery("#certificates").val(),
			st: st,
			lmt: lmt,
		}, function(data) {
			jQuery("#clientsList").append(data);
			jQuery(".load_popup").unbind('click');
			load_popup();
		});
	}
	jQuery(window).scroll(function() {
		windowFromBottom = jQuery(document).height() - jQuery(document).scrollTop();
		if (windowFromBottom < jQuery(window).height() * 2) {
			st = st + lmt;
			loadClients();
		}
	});
	loadClients();

	function liveSearch(txt) {
		jQuery("#clientsList").html("");
		if (txt.length > 2 || txt.length == 0) {
			st = 0;
			loadClients();
		}
	}
</script>