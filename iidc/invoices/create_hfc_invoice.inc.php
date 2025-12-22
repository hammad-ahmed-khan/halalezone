<script language="javascript">
    $("#page_title").html("Create Halal Facility invoice for Saudi Arabia")
</script>

<div style="text-align: center;">
    <h3>Create Halal Facility invoice for Saudi Arabia</h3>
    <p>
        <?php
        //show php errors
        ini_set('display_errors', 1);
        $invClients = [];
        $clients = [];
        $offids = [];
        //find invoices with older than a year from the date inserted_on field
        if ($invoices = $amdb->get_results("SELECT clid FROM invoices WHERE invoice_type = 'hfc' AND status = 'active' AND inserted_on < DATE_SUB(NOW(), INTERVAL 1 YEAR) group by clid")) {
            foreach ($invoices as $invoice) {
                $clients[] = $invoices['clid'];
            }
        }

        foreach (array('sa', 'sb') as $tp) {
            if ($certificates = $amdb->get_results("SELECT certificates_{$tp}.clid, certificates_{$tp}.offid FROM certificates_{$tp}
             where certificates_{$tp}.status = 'active' GROUP BY clid")) {
                foreach ($certificates as $certificate) {
                    if (!in_array($certificate['clid'], $clients)) {
                        $invClients[] = $certificate['clid'];
                        $offids[$certificate['clid']] = $certificate['offid'];
                    }
                }
            }
        }
        //get offices names for the clients
        // if (count($offids) > 0) {
        //     if ($offices = $amdb->get_results("SELECT offid,office_name FROM offices WHERE offid IN (" . implode(',', $offids) . ")")) {
        //         foreach ($offices as $office) {
        //             $offices[$office['offid']] = $office['office_name'];
        //         }
        //     }
        // }

        if (count($offids) > 0) {
            if ($offices = $amdb->get_results("SELECT offid,office_name FROM offices")) {
                foreach ($offices as $office) {
                    $offices[$office['offid']] = $office['office_name'];
                }
            }
        }

        if ($clients = $amdb->get_results("SELECT companies.clid,companies.company_name,companies.offid FROM companies JOIN users ON companies.clid = users.clid WHERE users.active='y' order by TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) { ?>
            <select style='width:600px' name="clid" class="searchable" size=1 onchange="if(this.value!='')document.location.href='index.php?inc=create_invoice&type=hfc&goback=<?php echo $_GET['inc']; ?>&clid='+this.value">
                <option value=''>Select a company</option>
                <?php
                foreach ($clients as $client) { ?>
                    <option value='<?php echo $client['clid']; ?>'><?php echo $client['company_name']; ?>
                        <?php if ($offices[$client['offid']]) echo ' (' . $offices[$client['offid']] . ')'; ?></option>
                <?php } ?>
            </select>
        <?php  };
        ?>
    </p>
</div>