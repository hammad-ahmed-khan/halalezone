<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
//TODO: update new system
// $inputs = json_decode('{"artNt":"no","description":"yes","quantity":"no"}', true);
$inputs = json_decode('{"description":"yes","quantity":"no"}', true);
$time = time() . (isset($_REQUEST['count']) ? $_REQUEST['count'] : 0);
$nr = 0;
if (isset($_REQUEST['act'])) {
	if ($_REQUEST['act'] == 'edit' and isset($_REQUEST['nr']) and isset($_REQUEST['tp'])) {
		if ($row = $amdb->get_row("SELECT * FROM certificates_{$_REQUEST['tp']} where nr='$_REQUEST[nr]'")) {
			if (is_array(json_decode($row['products'], true)))
				$products = json_decode($row['products'], true);
			elseif (is_serialized($row['products']))
				$products = unserialize($row['products']);
			if (isset($products) && is_array($products)) {
				foreach (reset($products) as $key => $value) {
					if (!isset($inputs[$key])) {
						$inputs[$key] = 'yes';
					}
				};
				foreach ($products as $qualityKey => $qualityValue) {
					$nr++;
?>
					<tr>
						<?php foreach ($qualityValue as $key => $value) {
							if (isset($inputs[$key])) {
								if($key == 'artNt')
								continue;
						?>
								<td <?php echo strstr($key, 'extra_') ? 'class="extra"' : ''; ?>><input name="products[<?php echo $qualityKey; ?>][<?php echo $key; ?>]" <?php echo ($key == 'description') ? 'data-required="yes"' : ''; ?> style="width:100%" value="<?php echo str_replace('"', '&quot;', $value); ?>" /></td>
						<?php };
						} ?>
						<td><img title="Delete product" src="../images/delete.gif" border="0" onclick="deleteProduct(this)"></td>
					</tr>
	<?php }
			};
		}
		exit();
	}
} else {
	if (isset($_GET['columns'])) {
		if ($_GET['columns'] > 3) {
			$total = $_GET['columns'] - 3;
			for ($j = 1; $j <= $total; $j++) {
				$inputs['extra_' . $j] = 'no';
			}
		}
	};
	?>
	<tr>
		<?php
		foreach ($inputs as $key => $input) { ?>
			<td><input name="products[<?php echo $time; ?>][<?php echo $key; ?>]" <?php echo ($key == 'description') ? 'data-required="yes"' : ''; ?> style="width:100%" /></td>
		<?php }; ?>
		<td><img title="Delete product" style="<?php echo !isset($_REQUEST['count']) ? 'opacity:0.2;cursor:default' : ''; ?>" src="../images/delete.gif" border="0" <?php echo isset($_REQUEST['count']) ? ' onclick="deleteProduct(this)"' : ''; ?>></td>
	</tr>
<?php
}
