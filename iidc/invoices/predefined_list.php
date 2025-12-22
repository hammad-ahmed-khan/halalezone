<?php
if (!isset($_GET['clid']))
    exit();
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$nr = 1;
if (!$predefined_prices = $amdb->get_results("SELECT * FROM hqc_predefined_prices WHERE status != 'deleted' ORDER BY invoice_type,service_type, item_code")) {
    $predefined_prices = array();
}
// echo "<pre>";
// print_r($predefined_prices);
$invoice_types = array('general' => 'General invoices', 'annual' => 'Annual invoices', 'shipment' => 'Shipment invoices', 'shipment_sab' => 'Shipment invoices for SA', 'hfc' => 'Halal Facility Certificate for SA', 'audit' => 'Audit invoices', 'supervision' => 'Supervision invoices');
$title = 'Default prices for all companies';
if (isset($_GET['clid']) && $_GET['clid'] != '0') {
    if ($selectedCompany = $amdb->get_row("SELECT companies.clid as clid,companies.company_name,hqc_companies_prices.prices FROM hqc_companies_prices RIGHT JOIN companies ON hqc_companies_prices.clid = companies.clid WHERE companies.clid = '$_GET[clid]'")) {
        $title = 'Default prices for: <span style="color:green;">' . $selectedCompany['company_name'] . '</span>';
    } else {
        $selectedCompany = array();
    }
} else {
    $selectedCompany = array();
}
?>
<h2 style="margin-bottom:10px;line-height: 24px;"><?php echo $title; ?></h2>
<?php
if (isset($selectedCompany['prices']) && is_array((json_decode($selectedCompany['prices'], true)))) {
    $selectedCompanyPrices = json_decode($selectedCompany['prices'], true);
} else {
    $selectedCompanyPrices = array();
}
?>
<form action="predefined_prices_save.php" method="post" onsubmit="return post_this_form(this);" target="">
    <input type="hidden" name="act" value="update" />
    <input type="hidden" name="clid" value="<?php echo $clid; ?>" />
    <table class="alternateOn center" id="predefined_prices" style="width: 100%;margin-bottom:50px">
        <thead>
            <tr>
                <th style="width: 20px;">::</th>
                <th style="width: 150px;">
                    <select name="invoice_types" onchange="getInvoiceType(this.value)" title="Invoice types">
                        <option value="">All types</option>
                        <?php foreach ($invoice_types as $key => $value) { ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </th>
                <th>Service type</th>
                <th>Item code</th>
                <th>Description</th>
                <th>Price</th>
                <th style="width: 40px;" id="actionTitle" class="action">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($predefined_prices) > 0) {
                foreach ($predefined_prices as $default) {
                    if (isset($selectedCompanyPrices[$default['preid']])) {
                        $client_custom = $selectedCompanyPrices[$default['preid']];
                        $class = ' green';
                    } else {
                        $client_custom = array();
                        $class = '';
                    }
            ?>
                    <tr data-preid="<?php echo $default['preid']; ?>" class="<?php echo trim($default['invoice_type']) != '' ? trim($default['invoice_type']) : 'general'; ?><?php echo $class; ?>">
                        <th class="srNr"><?php echo $nr++; ?></th>
                        <td>
                            <select name="predefined[<?php echo $default['preid']; ?>][invoice_type]">
                                <?php foreach ($invoice_types as $key => $value) {
                                    if ($key == $default['invoice_type']) {
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                ?>
                                    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td class="service_type"><input type="text" name="predefined[<?php echo $default['preid']; ?>][service_type]" value="<?php echo @$default['service_type']; ?>" /></td>
                        <td class="item_code"><input type="text" name="predefined[<?php echo $default['preid']; ?>][item_code]" value="<?php echo $default['item_code']; ?>" /></td>
                        <td class="description"><textarea name="predefined[<?php echo $default['preid']; ?>][description]"><?php echo $default['description']; ?></textarea></td>
                        <td style="white-space: nowrap;" class="prices">
                            <?php if (trim($default['extra_costs']) != '' and is_array(json_decode($default['extra_costs'], true))) {
                                $extra_costs = json_decode($default['extra_costs'], true);
                                if (isset($client_custom['extra_costs']) && is_array($client_custom['extra_costs']))
                                    $extra_costs = $client_custom['extra_costs'];
                            ?>
                                <ul class="alternateOff" style="padding: 0;margin:0px">
                                    <li><b>Minimum:</b> &euro;<input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs[minimum_amount]" value="<?php echo  isset($extra_costs['minimum_amount'])?do_currency($extra_costs['minimum_amount']) : '0,00'; ?>" /></li>
                                    <li><b>Administration:</b> &euro;<input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][admin_costs]" value="<?php echo do_currency($extra_costs['admin_costs']); ?>" /></li>
                                    <li><b>&lt;10.000kg:</b> &euro;<input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][price1]" value="<?php echo  do_currency($extra_costs['price1'], 3); ?>" /></li>
                                    <li><b>&gt;10.001kg:</b> &euro;<input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][price2]" value="<?php echo  do_currency($extra_costs['price2'], 3); ?>" /></li>
                                </ul>
                            <?php } else {
                                if (isset($client_custom['price']) && !is_array($client_custom['price']))
                                    $default['price'] = $client_custom['price'];

                                if (isset($client_custom['extra_costs']) && !is_array($client_custom['extra_costs']))
                                    $default['extra_costs'] = $client_custom['extra_costs'];
                            ?>
                                price: <input type="text" name="predefined[<?php echo $default['preid']; ?>][price]" value="<?php echo $default['price']; ?>" style="margin-bottom:5px;" /><br />
                                Math: <textarea class="extra_costs" name="predefined[<?php echo $default['preid']; ?>][extra_costs]" value="<?php echo $default['extra_costs']; ?>"><?php echo $default['extra_costs']; ?></textarea>
                            <?php }; ?>
                        </td>
                        <td class="action"><i class="fa fa-trash-alt" onclick="deleteDefault(this)"></i></td>
                    </tr>
            <?php };
            }; ?>
        </tbody>
        <tfoot>
            <tr id="newPriceItem" style="display: none;">
                <th></th>
                <td>
                    <select name="newPriceItem[invoice_type]">
                        <?php foreach ($invoice_types as $key=>$value) { ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td class="service_type"><input type="text" name="newPriceItem[service_type]" value="" /></td>
                <td class="item_code"><input type="text" name="newPriceItem[item_code]" value="" /></td>
                <td class="description"><textarea name="newPriceItem[description]"></textarea></td>
                <td class="prices">
                    price: <input type="text" name="newPriceItem[price]" style="margin-bottom:5px;" /><br />
                    Math: <textarea type="text" class="extra_costs" name="newPriceItem[extra_costs]"></textarea>
                <td class="action"></td>
            </tr>
        </tfoot>
    </table>
    </div>
    <div style="position:fixed;bottom:20px;text-align:center;width:100%;left:0px;">
        <div style="max-width: 1400px;background:white;margin:0 auto;">
            <input type="submit" value="Save" />
            <input type="button" onclick="resetPrices()" value="Reset" />
            <?php if ($_GET['clid'] == '0') { ?>
                <span id="addNewPriceItem"><input type="button" value="Add new items" onclick="addNewPriceItem();" /></span>
            <?php } ?>
        </div>
    </div>
</form>
<script>
    jQuery(document).ready(function() {
        //change document location without reloading the page
        if (window.history.pushState) {
            window.history.pushState('', '', '?inc=predefined_prices');
        } else {
            window.location.href = window.location.href + '?inc=predefined_prices';
        }
        jQuery("#companiesList").css("height", jQuery("#predefined_prices").height() + "px");
    });
</script>