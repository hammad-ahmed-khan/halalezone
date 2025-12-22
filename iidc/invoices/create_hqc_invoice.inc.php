<script language="javascript">
  $("#page_title").html("Create HQC invoice")
</script>
<?php
if (!isset($act)) {
  $user_options = get_office_options()['options'];
  if (isset($user_options) and isset($user_options['invoices_create'])) {
    $offids[0] = $_SESSION['offid'];
    $offices[$_SESSION['offid']] = $_SESSION['hqc_title'];
  } else {

    $offids[0] = 0;
    if ($options = $amdb->get_results("SELECT offid FROM offices WHERE JSON_VALID(options) = 1 AND JSON_EXTRACT(options,'$.invoicing_by') = '0'")) {
      foreach ($options as $option) {
        $offids[$option['offid']] = $option['offid'];
      };
    }
  }
  $offids = implode(',', $offids);

  echo "<center><b>Create HQC Invoices</b><p>";
  $clients_ids = array();
  $clients = array();
  $result = $amdb->get_results("SELECT * FROM $tbl[prefix]_halal_certificates LEFT JOIN companies ON $tbl[prefix]_halal_certificates.clid = companies.clid where $tbl[prefix]_halal_certificates.invoice_nr='' and FIND_IN_SET($tbl[prefix]_halal_certificates.offid,'$offids') group by $tbl[prefix]_halal_certificates.clid order by companies.company_name ASC");
  if (count($result) > 0) {
    echo "<select style='width:400px;' name=\"clid\" class=\"searchable\" size=1 onchange=\"if(this.value!='')document.location.href='index.php?inc=create_invoice&type=annual&goback=$_GET[inc]&clid='+this.value\">
<option value=''>Select a company</option>";
    foreach ($result as $row) {
      if (trim($row['company_name']) != '') {
        echo "<option value='$row[clid]'>$row[company_name]";
        if (in_array($row['clid'], $clients_ids))
          echo "(" . $clients[$row['clid']] . ")";
        echo "</option>";
      }
    }
    echo "</select>";
  }
} elseif (isset($act) and $act == 'hqc' and $_GET['clid'] != '') {
  $clid = $_GET['clid'];
  include "../date-picker.inc.php";
?>

  <script language="javascript">
    var MailPost = 'mail';

    function creat_invoice(act) {

      var err;
      for (var i = 0; i <= document.forms[0].elements.length - 1; i++) {
        if (document.forms[0].elements[i].getAttribute('data-req')) {
          document.forms[0].elements[i].style.backgroundColor = "";
          if (document.forms[0].elements[i].value == "") {
            document.forms[0].elements[i].style.backgroundColor = "#FFD9D9";
            err = "y";
          }
        }
      }
      if (err == "y") {
        alert("Fields with (*) are required")
        return false;
      }

      document.invoice_form.act.value = act;

      if (act == 'crt') {
        if (MailPost == 'mail')
          document.invoice_form.target = 'invoice_frame';
        else
          document.invoice_form.target = '_blank';
      }
      document.invoice_form.submit();
      if (act == "crt")
        setTimeout("document.location.href='index.php?inc=create_hqc_invoice'", 200);
    }

    $(function() {
      $("#issued_at").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: dateFormat
      });
      $("#valid_until").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: dateFormat
      });
    });

    function paidBy(nr) {
      if (confirm("Are you sure??") == true) {
        var time = new Date().getTime();
        $.post("<?php echo $prog_www ?>/invoices/certificates_save.php?tm=" + time, {
            act: "paid_by",
            crtNr: nr
          },
          function(data) {
            if (data != "") {
              if (data.indexOf('ok') > -1) {
                document.location = "<?php echo $prog_www ?>/invoices/index.php?inc=create_hqc_invoice";
              } else {
                alert(data);
              }
            }
          });
      }
    }
  </script>

  <iframe src="" name="invoice_frame" style="position:fixed;left:-10000px;"></iframe>
  <form action="pdf/pdf_hqc_invoice.php" method="post" target="_blank" name="invoice_form">
    <table border=0 width="750" cellpadding="0" cellpadding="0" class="alternate">
      <tr>
        <td colspan=2>
          HQC invoice for:</td>
      </tr>
      <?php
      $billing_clients = array();
      $clientAddress = "";
      $billingAdress = "";
      if ($row = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates, companies where  $tbl[prefix]_halal_certificates.clid = companies.clid and companies.clid='$clid' order by companies.company_name ASC")) {
        $clientAddress = "<tr><td valign=top><b>$row[company_name]</b><br>
		$row[street1]<br>
		$row[zip1]<br>
		$row[city1]";
        $crtNr = $row['crtNr'];

        if ($resultCl = $amdb->get_results("SELECT * FROM companies where clof='$clid' order by company_name ASC")) {
          $billingAdress = "<p><b>Billing client:</b> <select size=\"1\" name=\"bclid\"><option value=\"\">Select company</option>";
          foreach ($resultCl as $rowCl) {
            $billingAdress .= "<option value=\"$rowCl[clid]\">$rowCl[company_name]</option>";
          }
          $billingAdress .= "</select>";
        }
        echo $clientAddress;
        billing_address($row['billing_address']) . "<br/>";
        echo $billingAdress;
      }
      ?>
      </td>
      <td align="right" style="text-align:right">
        <input type="button" value="Free of charge" title="Free of charge" onclick="paidBy('<?php echo @$crtNr; ?>')" />
      </td>
      </tr>

      <input type="hidden" name="clid" value="<?php echo  $clid ?>" />
      <input type="hidden" name="act" value="" />
      <!--input type="hidden" name="vat" value="<?php echo  @$vat ?>" /-->
      <input type="hidden" name="crtNr" value="<?php echo  @$crtNr; ?>" />
      <tr>
        <td width=200 class="sub_title">HQC certificate</td>
        <td width=550 class="sub_title">Invoice items</td>
      </tr>
      <tr>
        <td valign="top">
          <table bgcolor="#eeeeee" cellpadding="0" cellspacing="0">
            <tr>
              <th nowrap="nowrap">HQC Nr:*</th>
              <td><input type="text" name="HQC_Nr" data-req="y" style="background-color:" style="width:80px" value="<?php echo $row['certificate_nr']; ?>" /></td>
            </tr>
            <th nowrap="nowrap">Issued at:*</th>
            <td><input type="text" name="issued_at" id="issued_at" data-req="y" style="background-color:;width:80px" value="<?php echo num2date($row['date_of_issue']); ?>" /></td>
      </tr>
      <th nowrap="nowrap">Valid Until:*</th>
      <td><input type="text" name="valid_until" id="valid_until" data-req="y" style="background-color:;width:80px" value="<?php echo num2date($row['date_of_expiry']); ?>" /></td>
      </tr>
    </table>
    </td>
    <td>
      <table width="100%" id="tableHQ" cellpadding="0" cellspacing="0">
        <tr>
          <th>Discription</th>
          <th width=100><b>Amount (&euro;)</th>
        </tr>
        <tr>
          <td><input type="text" name="HQCD1" style="width:95%" value="Products Registration" /></td>
          <td><input type="text" name="HQCA1" style="width:90%" /></td>
        </tr>
        <tr>
          <td><input type="text" name="HQCD2" style="width:95%" value="Transportation" /></td>
          <td><input type="text" name="HQCA2" style="width:90%" /></td>
        </tr>
        <tr>
          <td><input type="text" name="HQCD3" style="width:95%" /></td>
          <td><input type="text" name="HQCA3" style="width:90%" /></td>
        </tr>
        <tr>
          <td><input type="text" name="HQCD4" style="width:95%" /></td>
          <td><input type="text" name="HQCA4" style="width:90%" /></td>
        </tr>
        <tr>
          <td><input type="text" name="HQCD5" style="width:95%" /></td>
          <td><input type="text" name="HQCA5" style="width:90%" /></td>
        </tr>
      </table>
    </td>
    </tr>
    <tr>
      <td colspan="2" style="height:10"></td>
    </tr>
    <tr>
      <td colspan="2">
        <table cellpadding="2" cellspacing="2" width="100%">
          <tr>
            <td width="140">
              <b>First page comment:</b>
            </td>
            <td><input type="text" name="FPC" style="width:100%"></td>
          </tr>
          <tr>
            <td>
              <b>Last page comment:</b>
            </td>
            <td><input type="text" name="LPC" style="width:100%"></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="7" style="height:10"></td>
    </tr>
    <tr style="background-color:#EEEEEE">
      <td colspan="7">
        <table cellpadding="2" cellspacing="2" width="100%">
          <tr>
            <th width="140">Invoice template:</th>
            <td>
              <?php if (in_array("invoices_show_nl", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                <input type="radio" name="template" checked value="nl">
                NL &nbsp;&nbsp;Vat:<input size="2" name="vat" value="21" />%<br />
              <?php }; ?>
              <?php if (in_array("invoices_show_uae", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                <input type="radio" name="template" value="uae">
                UAE
              <?php }; ?>
            </td>
            <td rowspan="3" style="width:300px" align="center" bgcolor="#CCCCCC"><input type="button" onclick="creat_invoice('prv')" value=" Preview " style="width:200px" />
              <br />
              <input type="button" onclick="creat_invoice('crt')" value=" Create " style="width:200px"><br />
              <input type="reset" value=" Reset " style="width:200px">
            </td>
          </tr>
          <tr>
            <td colspan="2" style="height:2" bgcolor="#FFFFFF"></td>
          </tr>
          <tr>
            <th>Send invoice by:</th>
            <td valign="top">
              <input type="radio" name="mail_post" checked value="mail" onclick="
    if(this.checked){
    emailmeacopy.checked=true;
    document.all.sendmecopy.style.display='';
    MailPost = 'mail';
    }
    ">
              E-mail <span id="sendmecopy">
                <input type="checkbox" checked="checked" name="emailmeacopy" value='y'>
                Email me a copy</span><br>
              <input type="radio" name="mail_post" value="post" onclick="
    if(this.checked){
    emailmeacopy.checked=false;
    document.all.sendmecopy.style.display='none';
    MailPost = 'post';
    }
    ">
              Post
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </form>
  </table>
<?php
} elseif (isset($act) and $act == 'invOk' and $_GET['clid'] != '' and $_GET['invNr'] != '') {
  $clid = $_GET['clid'];
  $invNr = $_GET['invNr'];
  echo $invNr;
}
?>