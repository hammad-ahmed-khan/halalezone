<?php
$Standards = array("standard" => "Halal Standards and Guidelines", "normative" => "Normative References");
$orgs = array(
    "OIC" => "Organisation of Islamic Countries [OIC]",
    "UAE" => "United Arab Emirates [UAE]",
    "GCC" => "Gulf Countries [GCC]",
    "MS" => "Malaysia",
    "HAS" => "Indonesia"
);
?>
<script>
    jQuery("#page_title").html("Add / EDit <?php echo $Standards[$_GET['type']]; ?>");
</script>
<form method="post" action="halal-standards_save.php" onsubmit="return post_this_form(this)">
    <input type="hidden" name="act" value="<?php echo $_GET['act']; ?>" />
    <input type="hidden" name="type" value="<?php echo $_GET['type']; ?>" />
    <?php if ($_GET['act'] == 'update') { ?>
        <input type="hidden" name="<?php echo ($_GET['type'] == 'standard') ? 'stnid' : 'normid'; ?>" value="<?php echo ($_GET['type'] == 'standard') ? $_GET['stnid'] : $_GET['normid']; ?>" />
    <?php }; ?>

    <table class="alternate center" style="max-width: 75%;">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center;" class="sub_title"><?php echo $_GET['act']; ?> <?php echo $Standards[$_GET['type']]; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Organisation / Country:*</th>
                <td>
                    <select name="organisation" size="1" data-required="yes">
                        <option value="">Please select</option>
                        <?php foreach ($orgs as $orgKey => $orgValue) { ?>
                            <option value="<?php echo $orgKey; ?>"><?php echo $orgValue; ?></option>
                        <?php }; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">Code:*</th>
                <td><input type="text" name="code" style="width: 20%;" data-required="yes" /></td>
            </tr>
            <tr>
                <th>Description:*</th>
                <td><input type="text" name="description" style="width:100%" data-required="yes" /></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align: center;">
                    <input type="submit" value="Save" /><input type="reset" value="Reset" /> <a href="index.php" class="button">Cancel</a>
                </th>
            </tr>
        </tfoot>
    </table>
</form>