<?php
if ((isset($_SESSION['comemid']) and $_SESSION['super_admin'] == 'yes') or (isset($_SESSION['user_type']) and $_SESSION['user_type'] == "admin")) {
?>
	<script>
		$("#page_title").html("Email Template")
	</script>

	<style>
		table td b {
			display: inline-block;
			width: 100px;
			float: left;
		}

		table td div {
			margin: 4px;
			display: flex;
		}

		table td input {
			width: 95%;
		}

		table td div input {
			width: 200px;
		}

		table th {
			white-space: nowrap
		}

		select {
			text-transform: capitalize;
		}
	</style>
	<?php

	?>
	<form action="invoice_templates_save.php" onSubmit="return post_this_form(this)" id="officeForm" name="officeForm" data-error="All fields are required">
		<center>
			Select Message to edit:
			<select size="1" name="template_name" id="template_name" onchange="document.location='index.php?inc=<?php echo $_GET['inc']; ?>&act=editTemplate&tn='+this.value">
				<option value="">Select Email message</option>
				<?php if ($em_templates = $amdb->get_results("SELECT template_name FROM invoice_templates")) {
					foreach ($em_templates as $template) {
						if (trim($template['template_name']) != '') { ?>
							<option value="<?php echo $template['template_name']; ?>"><?php echo str_replace('_', ' ', $template['template_name']); ?></option>
				<?php };
					}
				} ?>
				<option value="0">Add new message</option>
			</select>
		</center>
		<?php if (isset($_GET['act']) && $_GET['act'] == 'editTemplate' && isset($_GET['tn']) and trim($_GET['tn']) != '') {

			if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$_GET[tn]'"))
				$row = $amdb->get_columns('invoice_templates');
		?>
			<script>
				jQuery("#template_name").val('<?php echo $_GET['tn']; ?>');
			</script>
			<input type="hidden" name="act" value="update" />
			<table class="alternate" style="width:100%;margin-top:20px">
				<?php if ($_GET['tn'] != 'email_footer') { ?>
					<tr>
						<th style="text-transform:capitalize;color:#900" colspan="4">
							<?php if ($_GET['tn'] == '0') { ?>
								<center>Add a new email-message</center>
							<?php } else { ?>
								<center>Edit <?php echo str_replace('_', ' ', $_GET['tn']); ?> email-message</center>
							<?php }; ?>
						</th>
					</tr>
					<tr>
						<th style="width:100px">Reply address:*</th>
						<td style="width:300px"><input type="text" name="email_reply_address" data-required="yes" value="<?php echo $row['email_reply_address']; ?>" /></td>
						<th style="width:100px">Sender name:*</th>
						<td><input type="text" name="email_sender_name" data-required="yes" value="<?php echo $row['email_sender_name']; ?>" /></td>
					</tr>
					<tr>
						<th style="width:100px">BCC address:</th>
						<td><input type="text" name="email_bcc_address" value="<?php echo $row['email_bcc_address']; ?>" /></td>
						<td colspan="2">Send a copy of the invoice to this email address</td>
					</tr>
					<tr>
						<th>Email Subject:*</th>
						<td colspan="3"><input type="text" name="email_subject" data-required="yes" value="<?php echo $row['email_subject']; ?>" /></td>
					</tr>
					<tr>
						<th colspan="4">Email body*</th>
					</tr>
				<?php }; ?>
				<tr>
				<tr>
					<td colspan="4">
						<textarea class="tinymce" name="email_body" style="height:400px;"><?php echo $row['email_body']; ?></textarea>
					</td>
				</tr>
				<?php if ($_GET['tn'] == '0') { ?>
					<tr>
						<th>Message ID:*</th>
						<td colspan="3">
							<input name="template_name" id="template_name" data-required="yes" style="width:200px" />
						</td>
					</tr>
				<?php }; ?>
			</table>
			<?php if ($_GET['tn'] == 'invoice' or $_GET['tn'] == 'credit_note') { ?>
				<table class="alternate" style="width:100%;margin-top:20px">
					<tr>
						<th style="text-transform:capitalize;color:#900" colspan="4">
							<center>Edit <?php echo str_replace('_', ' ', $_GET['tn']); ?> pdf template</center>
						</th>
					</tr>
					<tr>
						<td colspan="4">
							<textarea class="tinymce" name="pdf_template" style="height:400px;"><?php echo $row['pdf_template']; ?></textarea>
						</td>
					</tr>
				</table>
			<?php }; ?>
			<center>

				<span class="info">All fields are required</span>
				<input type="submit" value="Save" />
				<input type="reset" value="Reset" />
			</center>
		<?php }; ?>
	</form>
<?php }; ?>