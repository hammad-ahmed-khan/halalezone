<script language="javascript">
    $("#page_title").html("Create credit note")
</script>
<?php
if (!isset($act)) {
    echo "<center><b>Create credit note invoice</b><p>";
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offid = "and companies.offid='$_SESSION[offid]";
    } else {
        $offid = "";
    }

    if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_SESSION[offid]'")) {
        $cc = $office['office_country'];
    }
    $result = $amdb->get_results("SELECT * FROM companies JOIN users ON companies.clid = users.clid WHERE companies.clof='0' $offid and users.active='y' order by companies.company_name ASC");
    if (count($result) > 0) { ?>
        <select style='width:400px' name="clid" class="searchable" size=1 onchange="if(this.value!='')document.location.href='index.php?inc=create_invoice&type=credit_note&goback=<?php echo $_GET['inc']; ?>&clid='+this.value">
            <option value=''>Select a company</option>
            <?php foreach ($result as $row) { ?>
                <option value='<?php echo $row['clid']; ?>'><?php echo get_client_id($row['clid'],$cc); ?>-<?php echo $row['company_name']; ?></option>
            <?php }; ?>
        </select>
<?php
    }
}
