<script>
    jQuery("#page_title").html("Halal Standards and Guidelines");
</script>
<h2 style="text-align:center">Halal Standards and Guidelines | Normative References</h2>
<?php
//TODO: upload thi so the server and to new system as well
$Standards = array("standard" => "Halal Standards and Guidelines", "normative" => "Normative References");
$orgs = array(
    "OIC" => "Organisation of Islamic Countries [OIC]",
    "UAE" => "United Arab Emirates [UAE]",
    "GCC" => "Gulf Countries [GCC]",
    "MS" => "Malaysia",
    "HAS" => "Indonesia"
);
$org = '';
?>
<table class="alternate center">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center;">Halal Standards and Guidelines</th>
        </tr>
        <tr>
            <th style="width: 0px;">#</th>
            <th style="width: 150px;">Code</th>
            <th>Description</th>
            <th style="width: 50px;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE status='active'")) {
            $srNr = 1;
            foreach ($standards as $standard) {
                if($org != $standard['organisation']){
                    $org = $standard['organisation'];
                    ?>
                    <tr><th></th><th colspan="3"><?php echo $orgs[$standard['organisation']];?></th></tr>
                    <?php
                }
                ?>
                <tr>
                    <th><?php echo $srNr++; ?></th>
                    <td> <?php echo ($standard['code']); ?></td>
                    <td> <?php echo ($standard['description']); ?></td>
                </tr>
        <?php };
        }; ?>
    </tbody>
</table>
<div style="text-align:center"><a href="index.php?inc=halal-standards_add_edit&act=add&type=standard" class="button center">Add Halal Standards</a></div> <br /><br />
<table class="alternate center">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center;">Normative References</th>
        </tr>
        <tr>
            <th style="width: 20px;">#</th>
            <th style="width: 100px;">Code</th>
            <th>Description</th>
            <th style="width: 50px;">Action</th>
        </tr>
    </thead>
    <tbody>
        hqc_normative_references
        <tr></tr>

    </tbody>
</table>
<div style="text-align:center"><a href="index.php?inc=halal-standards_add_edit&act=add&type=normative" class="button center">Normative References</a></div>