<div>
<input type="button" value="Change password" style="width: 180px" onclick="document.location.href='<?php echo $prog_www?>/admin/?inc=change_password'">
<?php if (isset($_SESSION['user_type']) and $username=='invoice'){?>
<br /><input type="button" value="Default prices" style="width: 180px" onclick="document.location.href='<?php echo $prog_www?>/invoices/?inc=service_prices'">
<br /><input type="button" value="Invoice numbering" style="width: 180px" onclick="document.location.href='<?php echo $prog_www?>/invoices/?inc=invoice_numbers'">
<?php }?>
</div>
