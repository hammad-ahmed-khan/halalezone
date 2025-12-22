<style>
    #searchTable td {
        vertical-align: middle;
    }
</style>
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
<table style="border:0px !important;min-width:0px" id="searchTable">
    <form method="post" action="load_invoices.php" name="seach_form" id="seach_form" onsubmit="return loadInvoices()">
        <input type="hidden" name="act" id="seach_form_act" value="search" />
        <input type="hidden" name="orderBy" id="orderBy" value="inserted_on" />
        <input type="hidden" name="ascDsc" id="ascDsc" value="DESC" />
        <input type="hidden" name="show" id="show" value="<?php echo $_GET['show']; ?>" />
        <input type="hidden" name="period" id="period" value="year" />
        <input type="hidden" name="offid" id="offid" value="<?php echo isset($_SESSION['offid']) ? $_SESSION['offid'] : '0'; ?>" />
        <tr>
            <td style="vertical-align:middle;white-space: nowrap;">
                SEARCH:
                <?php if ($_GET['show'] == 'draft') { ?>
                    <input type="hidden" name="searchFor" id="searchFor" value="client">
                <?php } else { ?>
                    <select name="searchFor" id="searchFor" size="1" onchange="switchSearchFor(this.value)">
                        <option value="client">Client</option>
                        <option value="invoice_number">Invoice number</option>
                        <option value="date">Date</option>
                        <option value="item_code">Item Code / PO</option>
                        <option value="internal">Internal invoices</option>
                        <option value="invoice_items">Invoice items</option>
                    </select>
                <?php }; ?>

                <?php
                $invoice_types = array(
                    "annual" => "Annual certificate invoices",
                    "batch" => "Shipment certificate invoices",
                    "hfc" => "HFC invoice for Saudi Arabia",
                    "hsa" => "HSA invoice for Saudi Arabia",
                    "audit" => "Audit invoices",
                    "supervision" => "Halal supervision invoices",
                    "general" => "General invoices",
                    "recurring" => "Monthly invoices",
                    "credit_note" => "Credit notes"
                );
                ?>
                <?php if ($_GET['show'] == 'draft') { ?>
                    <input type="hidden" name="invoice_type" id="invoice_type" value="all">
                <?php } else { ?>
                    <select name="invoice_type" id="invoice_type">
                        <option value="all">All invoices</option>
                        <?php foreach ($invoice_types as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo ($key == $_GET['show']) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                        <?php }; ?>
                    </select>
                <?php }; ?>
            </td>
            <td id="client_inputs" class="inputs" style="text-align:center;vertical-align: middle;">
                <select size="1" name="clid" id="clid" style="max-width:250px;" class="searchable">
                    <option value="">Search for a client</option>
                    <option value="">All clients</option>
                    <?php foreach ($clients as $client) { ?>
                        <option value="<?php echo $client['clid']; ?>"><?php echo str_replace("\'", "'", $client['company_name']); ?></option>
                    <?php } ?>
                </select>
                <select name="year" size="1">
                    <option value="all">All years</option>
                    <?php for ($year = date("Y"); $year >= $yearStart; $year--) { ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == date("Y")) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php }; ?>
                </select>
            </td>
            <td id="date_inputs" class="inputs" style="vertical-align:middle;display:none">
                From date:<input type="text" name="date_from" id="date_from" size=10 class="date" />
                To date:<input type="text" name="date_to" id="date_to" size=10 class="date" />
            </td>
            <td id="invoice_number_inputs" class="inputs" style="vertical-align:middle;display:none">
                <input type="text" name="invoice_number" id="invoice_number" placeholder="Invoice number" />
            </td>
            <td id="invoice_items_inputs" class="inputs" style="vertical-align:middle;display:none">
                <input type="text" name="invoice_items" id="invoice_items" placeholder="Invoice Items" />
            </td>
            <td id="item_code_inputs" class="inputs" style="display:none;text-align:center;vertical-align: middle;">
                <input type="text" name="item_code" placeholder="Insert Item Code" />
            </td>
            <td> <input type="submit" value="Search" /></td>
        </tr>
    </form>
</table>