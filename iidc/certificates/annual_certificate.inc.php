<?php
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
if (!isset($act) or $act == "") $act = "add";

if (isset($act) and $act == "edit") {
	$result = MYSQL_QUERY("SELECT * FROM annual_certificates where nr='$nr'");
	if (@MYSQL_NUM_ROWS($result) > 0) {
		$row = MYSQL_FETCH_ARRAY($result);
		$importer = $row['importer'];
		$exporter = $row['exporter'];
		$producer = $row['producer'];
		if ($row['attachment'])
			$attch_display = '';
	}
}
include "../date-picker.inc.php";
?>
<script language="javascript">
	$("#page_title").html("Requested annual Certificate")
	var err;

	function checform() {
		err = '';
		for (var i = 0; i <= document.forms[0].elements.length - 1; i++) {
			if (document.forms[0].elements[i].getAttribute('data-req')) {
				document.forms[0].elements[i].style.backgroundColor = "";
				if (document.forms[0].elements[i].value == "") {
					document.forms[0].elements[i].style.backgroundColor = "#FFD9D9";
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
</script>

<script language="javascript">
	function preview() {
		checform();
		if (err == "y") {
			alert("Fields with (*) are required")
			return false;
		}
		document.cer_form.action = "pdf/pdf_annual_certificate.php";
		document.cer_form.act.value = "preview";
		document.cer_form.target = "_blank";
		document.cer_form.submit();
	}

	function save_hc() {
		checform()
		if (err == "y") {
			alert("Fields with (*) are required")
			return false;
		}
		document.cer_form.action = "annual_certificate_save.php";
		document.cer_form.act.value = "<?php echo  $act ?>";
		document.cer_form.target = "";
		document.cer_form.submit();
	}
	$(document).ready(function(e) {
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
	});
</script>
<form name="cer_form" method=post action="" target="">
	<input type=hidden name=clid value="<?php echo $clid; ?>">
	<input type=hidden name=act value="">
	<table class="alternate" id="cer_table" style="border:1px solid #EEE">
		<tr>
			<td colspan=2 class="sub_title">
				<center>Annual Certificate</center>
			</td>
		</tr>
		<tr>
			<th>Company:<div style="margin-top:20px;font-weight:normal">full address</div>
			</th>
			<td>
				<textarea name="company" style="width:350px;height:70px"><?php echo  @$row['company']; ?></textarea>
			</td>
		</tr>
		<tr>
			<th>Products:<div style="margin-top:20px;font-weight:normal">one product<br />per line</div>
				</td>
			<td>
				<textarea name="products" style="width:350px;height:70px"><?php echo  @$row['products']; ?></textarea>
			</td>
		</tr>
		<tr>
			<th>EEC number:</th>
			<td><input type="text" name="eec_nr" value="<?php echo  @$row['eec_nr']; ?>" size="55"></td>
		</tr>
		<tr>
			<th>Controller*:</th>
			<td><input data-req="y" style="background-color:" type="text" name="controller" value="<?php echo  @$row['controller']; ?>" size="55"></td>
		</tr>
		<tr>
			<th>Approval*:</th>
			<td><input data-req="y" style="background-color:" type="text" name="approval" value="<?php echo  @$row['approval']; ?>" size="55"></td>
		</tr>
		<tr>
			<th>Date of issue*:</th>
			<td><input data-req="y" style="background-color:" type="text" id="date_of_issue" name="date_of_issue" value="<?php echo  @$row['date_of_issue']; ?>" size="12">
			</td>
		</tr>
		<tr>
			<th>Date of expiry*:</th>
			<td><input data-req="y" style="background-color:" type="text" id="date_of_expiry" name="date_of_expiry" value="<?php echo  @$row['date_of_expiry']; ?>" size="12">
			</td>
		</tr>
		<tr>
			<th>Certificate nr*:</th>
			<td><input data-req="y" style="background-color:" type="text" name="certificate_nr" value="<?php echo  @$row['certificate_nr']; ?>" size="55"></td>
		</tr>
		<tr>
			<th>Reference:</th>
			<td><input type="text" name="reference" value="<?php echo  @$row['reference']; ?>" size="55"></td>
		</tr>
</form>
<tr>
	<td colspan=2 class="sub_title">
		<center>
			<!--input type=button onclick="preview()" value=" Preview " -->
			<input type=button onclick="document.cer_form.reset()" value=" Reset ">
			<input type=button onclick="save_hc()" value=" Request certificate  " style="color:red">
		</center>
	</td>
</tr>
</table>