<script>
    function check_span(spn) {
        jQuery("#client_inputs span").css({
            'display': 'none'
        });
        jQuery("#client_inputs span#search_" + spn).css("display", "inline-block")
    }

    function switchInvoiceType(val) {
        window.location = '?show=' + val
    }

    function switchSearchFor(val) {
        jQuery("#searchTable .inputs").css("display", "none")
        jQuery("#" + val + "_inputs").css("display", "table-cell");
        jQuery("#sbsid").cass("display", "inline-block")
    }
</script>
<?php
$whr = '';
$yearStart = 2007;
if (isset($_SESSION['offid']) and $_SESSION['offid'] != 0) {
    if ($office = $amdb->get_row("SELECT clients FROm offices WHERE offid = '$_SESSION[offid]'")) {
        $whr = "AND FIND_IN_SET(invoices.clid,'$office[clients]')";
        if ($invoiceDate = $amdb->get_row("SELECT inserted_on FROM invoices WHERE 1 $whr ORDER BY inserted_on ASC")) {
            $yearStart = date("Y", strtotime($invoiceDate['inserted_on']));
        }
    }
};
$clients = $amdb->get_results("SELECT companies.clid,companies.company_name FROM companies
								JOIN invoices ON companies.clid = invoices.clid
                                JOIN users ON companies.clid = users.clid
                                WHERE 1 $whr
                                group by invoices.clid
                                ORDER BY companies.company_name ASC");
?>
<table border="0" style="border:0px !important;min-width:0px" id="searchTable">
    <form method="post" action="load_invoices.php" name="seach_form" id="seach_form" onsubmit="return loadInvoices()">
        <input type="hidden" name="act" id="seach_form_act" value="search" />
        <input type="hidden" name="orderBy" id="orderBy" value="inserted_on" />
        <input type="hidden" name="ascDsc" id="ascDsc" value="DESC" />
        <input type="hidden" name="show" id="show" value="<?php echo $_GET['show']; ?>" />
        <input type="hidden" name="period" id="period" value="year" />
        <input type="hidden" name="offid" id="offid" value="<?php echo isset($_SESSION['offid']) ? $_SESSION['offid'] : '0'; ?>" />
        <tr>
            <td style="vertical-align:middle">
                SEARCH FOR:
                <select name="searchFor" id="searchFor" style="1" onchange="switchSearchFor(this.value)">
                    <option value="client">Client</option>
                    <option value="invoice_number">Invoice number</option>
                    <option value="date">Date</option>
                    <option value="item_code">Item Code / PO</option>
                </select>
            </td>
            <td id="client_inputs" class="inputs" style="text-align:center;vertical-align: middle;">
                <select size="1" name="clid" id="clid" style="max-width:250px;" class="searchable">
                    <option value="">Search for a client</option>
                    <option value="">All clients</option>
                    <?php foreach ($clients as $client) { ?>
                        <option value="<?php echo $client['clid']; ?>"><?php echo str_replace("\'", "'", $client['company_name']); ?></option>
                    <?php } ?>
                </select>
                <b>Year:</b>
                <select name="year" size="1">
                    <option value="all">All years</option>
                    <?php for ($year = date("Y"); $year >= $yearStart; $year--) { ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == date("Y")) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php }; ?>
                </select>
            </td>
            <td id="item_code_inputs" class="inputs" style="display:none;text-align:center;vertical-align: middle;">
                <input type="text" name="item_code" placeholder="Insert Item Code" />
            </td>
            <td>
                <input type="submit" name="get_invoices" value="Get invoices" style="width: 120px" />
            </td>
            <td id="date_inputs" class="inputs" style="vertical-align:middle;display:none">
                From date:<input type="text" name="date_from" id="date_from" size=10 class="date" />
                To date:<input type="text" name="date_to" id="date_to" size=10 class="date" />
                <input type="submit" name="get_invoices" value="Get invoices" style="width: 120px" />
            </td>
            <td id="invoice_number_inputs" class="inputs" style="vertical-align:middle;display:none">
                <input type="text" name="invoice_number" id="invoice_number" />
                <input type="submit" value="Search" placeholder="Invoice number" />
            </td>
        </tr>
    </form>
</table>