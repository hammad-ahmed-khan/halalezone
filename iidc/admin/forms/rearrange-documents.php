<?php if (!defined("__HQC__")) {
	exit();
}; ?>
<h3 class="content_title">Rearrange documents</h3>
<table style="margin:0 auto" class="alternateOn">
	<thead>
		<tr>
			<th style="width:20px">No.</th>
			<th style="width:80px"><?php _e('ID'); ?></th>
			<th><?php _e('Application name'); ?></th>
			<th><?php _e('Category'); ?></th>
			<th style="width:20px;text-align:right"></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width:20px">No.</th>
			<th style="width:80px"><?php _e('ID'); ?></th>
			<th><?php _e('Application name'); ?></th>
			<th><?php _e('Category'); ?></th>
			<th style="width:20px;text-align:right"></th>
		</tr>
	</tfoot>
	<?php
	if ($categories = get_hqc_options('form_categories')) {
		if (is_array(json_decode($categories, true))) {
			$categories = json_decode($categories, true);
			$categories['0'] =  '';
		}
	}
	$nr = 1;
	if ($theForms = $hqcdb->get_results("SELECT * FROM hqc_forms where status!='example' and status ='active' AND published = 'yes' order by pos ASC, form_id ASC ")) {
		foreach ($theForms as $form) {
			$form_meta = decode_json($form['form_meta']);
	?>
			<tr>
				<td class="srn"><?php echo $nr++; ?></td>
				<td><?php echo $form['form_id']; ?></td>
				<td class="nowrap">
					<?php echo $form['form_name']; ?>
				</td>
				<td>
					<?php echo $categories[$form['category']]; ?>
				</td>
				<td style="width:60px;text-align:center">
					<i class="fas fa-arrows-alt" data-id="<?php echo $form['foid']; ?>"></i>
				</td>
			</tr>
	<?php };
	}; ?>
</table>
<div class="cancelLoaded" style="text-align:center"><input type="button" value="Close" onclick="unloadContent(this);" /></div>