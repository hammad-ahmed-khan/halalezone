<script language="javascript">
    $("#page_title").html("Create General invoices")
</script>
<?php
if (!isset($act)) {
    echo "<center><h2>Create General Invoices</h2><p>";
    $clients_ids = array();
    $clients = array();
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offid = "and companies.offid='$_SESSION[offid]'";
    } else {
        $offid = '';
    }
    $result = $amdb->get_results("SELECT * FROM companies JOIN users ON companies.clid = users.clid WHERE companies.clof='0' $offid and users.active='y' order by TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC");
    if (count($result) > 0) {
        echo "<select style='width:400px' name=\"clid\" class=\"searchable\" size=1 onchange=\"if(this.value!='')document.location.href='index.php?inc=create_invoice&type=general&goback=$_GET[inc]&clid='+this.value\">
<option value=''>Select a company</option>";
        foreach ($result as $row) {
            echo "<option value='$row[clid]'>$row[company_name]";
            if (in_array($row['clid'], $clients_ids))
                echo "(" . $clients[$row['clid']] . ")";
            echo "</option>";
        }
        echo "</select></center</p>";
    }
}
