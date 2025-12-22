<?php if (!defined("_HQC_")) {
    exit();
};
if (!isset($_COOKIE['predefined'])) { ?>
    <style>
        #accessDiv {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
    </style>
    <script>
        function accessCode(act) {
            if (act == 'checkCode') {
                if ($("input[name='AccessCode']").val().trim() == '') {
                    alert_message('Access code is required');
                    return false;
                }

                $.post('access_code.php', {
                    act: 'checkAccessCode',
                    AccessCode: $("input[name='AccessCode']").val()
                }).done(function(data) {
                    if (data.trim().length > 0) {
                        if (data == 'success') {
                            document.location.reload();
                        } else {
                            alert_message(data);
                            return false;
                        }
                    } else {
                        alert_message('An error occurred');
                    }
                });
                return false;
            } else if (act == 'sendCode') {
                $.post('access_code.php', {
                    act: 'sendMeAccessCode'
                }).done(function(data) {
                    if (data.trim().length > 0) {
                        alert_message(data);
                    } else {
                        alert_message('An error occurred');
                    }
                });
            }
        }
    </script>
    <div id="accessDiv">
        <h2>Access code required.</h2>
        <form method="post" onsubmit="return accessCode('checkCode');">
            <input type="hidden" name="act" value="checkAccessCode" />
            <input type="text" name="AccessCode" />
            <input type="submit" value="Verify" />
        </form>
        <a onclick="accessCode('sendCode')">Email me access code</a>
    </div>
<?php return;
} ?>
<style>
    #predefined_prices tbody input[type='text'],
    #predefined_prices tbody textarea,
    #predefined_prices tbody select {
        background: transparent;
        border-color: transparent;
        padding: 0px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    #predefined_prices textarea {
        width: 100%;
        min-height: 30px;
    }

    #predefined_prices .amount {
        width: 80px;
    }

    #predefined_prices td b {
        display: inline-block;
        width: 110px;
        margin-right: 2px;
    }

    #predefined_prices input.id {
        width: 100px
    }

    #predefined_prices ul li {
        padding: 2px
    }

    select {
        text-transform: capitalize;
    }

    i.far.fa-save {
        font-size: 30px !important;
        margin: 10px;
    }
</style>
<script>
    jQuery("#page_title").html('Predefined prices');
    var tbodyContent;

    function setTextareaHeight() {
        jQuery("#predefined_prices tbody textarea").each(function() {
            this.style.height = "60px";
            if (this.value != '')
                this.style.height = this.scrollHeight + "px";
        })

        $('textarea').on('input', function() {
            this.style.height = "";
            this.style.height = this.scrollHeight + "px";
        });
    }

    function resetItems() {
        jQuery("#predefined_prices tbody").html(tbodyContent);
        jQuery("#savePriceItem,#newPriceItem").css("display", "none");
        jQuery("#addNewPriceItem").css("display", "inline-block");
        jQuery("#actionTitle").css("display", "table-cell");
    }

    function resetInputs() {
        jQuery("#predefined_prices tbody input[type='text'],#predefined_prices tbody select,#predefined_prices tbody textarea").css({
            "background": "transparent",
            "padding": "0px",
            "border-color": "transparent"
        }).attr('disabled', 'disabled');
        jQuery("#savePriceItem,#newPriceItem").css("display", "none");
        jQuery("#addNewPriceItem").css("display", "inline-block");
    }

    function resetIcons() {
        jQuery("#predefined_prices tbody td.action").html('<i class="fa fa-edit" onclick="editDefault(this)"></i><i class="fa fa-trash-alt" onclick="deleteDefault(this)"></i>')
    }

    function removeIcons() {
        jQuery("#predefined_prices tbody td.action").html('')
    }

    function insertUpdateIcons(obj) {
        jQuery(obj).html('<i class="far fa-save" onclick="savePrices(this)"></i>')
    }

    function editDefault(obj) {
        preid = jQuery(obj).parents('tr').attr('data-preid');
        jQuery("input[name='preid']").val(preid);
        parent = jQuery(obj).parent('td');
        $.when(resetInputs()).then(
            jQuery(obj).parents('tr').find('input[type=text],select,textarea').css({
                "background": "#fff",
                "padding": "inherit",
                "border-color": "#c0c0c0"
            }).removeAttr('disabled')
        ).then(removeIcons()).then(insertUpdateIcons(parent)).then(setTextareaHeight())

        jQuery("#addNewPriceItem,#newPriceItem").css("display", "none");
        jQuery("#savePriceItem").css("display", "inline-block");
        jQuery("input[name='act']").val('update_default_prices');
    }

    function deleteDefault(obj) {
        invoiceItemToBeDeleted = jQuery(obj).parents('tr').find('input[name="service_type"]').val();
        alert_confirm('Delete invoice item <strong>(' + invoiceItemToBeDeleted + ')</strong>');

        act = 'delete_default_prices',
            preid = jQuery(obj).parents('tr').attr('data-preid'),
            clid = jQuery("select[name='clid']").val()

        jQuery("#alertYesBtn").on("click", function() {
            close_alert();
            jQuery.post('predefined_prices_save.php', {
                act: act,
                preid: preid,
                clid: clid
            }).done(function(data) {
                if (data.trim().length > 0) {
                    if (data == 'success') {
                        jQuery(obj).parents('tr').remove();
                        serializeTable("#predefined_prices");
                    } else {
                        alert_message(data);
                    }
                } else {
                    alert_message('An error occurred');
                }
            });

        });
    }


    function addNewPriceItem() {
        removeIcons();
        jQuery("#addNewPriceItem").css("display", "none");
        jQuery("#savePriceItem").css("display", "inline-block");
        jQuery("#newPriceItem").css("display", "table-row");
        jQuery("act").val('insert_default_prices');
        jQuery("input[name='act']").val('insert_default_prices');
    }

    async function insertNewItem() {
        jQuery("#newPriceItem input[type='text'],#newPriceItem select,#newPriceItem textarea").each(function() {
            if (this.value.trim() == '') {
                alert_message('All fields are required');
                return false;
            }
        })
        return true;
    }

    jQuery(document).ready(function() {
        $.when(resetIcons()).then(setTextareaHeight()).then(tbodyContent = jQuery("#predefined_prices tbody").html())
    })

    async function savePrices(obj) {
        try {
            let data = {
                'act': jQuery(obj).parents('form').find('input[name="act"]').val(),
                'preid': jQuery(obj).parents('tr').attr('data-preid'),
                'clid': jQuery("select[name='clid']").val()
            };

            jQuery(obj).parents('tr').find('input[type=text],select,textarea').each(function() {
                data[jQuery(this).attr('name')] = jQuery(this).val();
            });

            let url = 'predefined_prices_save.php';
            let response = await $.ajax({
                type: "POST",
                url: url,
                crossDomain: true,
                data: data
            });

            if (response == 'success') {
                $.when(resetIcons()).then(resetInputs()).then(setTextareaHeight()).then(tbodyContent = jQuery("#predefined_prices tbody").html())
                // location.reload();
            } else {
                if (response != '')
                    alert_message(response);
            }
        } catch (err) {
            alert_message(err.statusText || "An error occurred");
        }

        return false;
    }

    function getClientPrices(clid) {

        url = '?inc=predefined_prices';
        if (clid != '')
            url += '&clid=' + clid;

        document.location = url;
    }

    function getInvoiceType(val) {
        if (val == '') {
            jQuery("#predefined_prices tbody tr").css("display", "table-row");
        } else {
            jQuery("#predefined_prices tbody tr").css("display", "none");
            jQuery("#predefined_prices tbody tr." + val).css("display", "table-row");
        }
    }
</script>
<?php
$nr = 1;
if (!$predefined_prices = $amdb->get_results("SELECT * FROM hqc_predefined_prices WHERE status != 'deleted' ORDER BY invoice_type,service_type, item_code")) {
    $predefined_prices = array();
}

if (!$companies = $amdb->get_results("SELECT companies.clid as clid,companies.company_name,hqc_companies_prices.prices FROM hqc_companies_prices RIGHT JOIN companies ON hqc_companies_prices.clid = companies.clid WHERE companies.active = 'y' AND companies.offid = '0' ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {
    $companies = array();
}

$invoice_types = array('general', 'annual', 'shipment', 'audit', 'supervision');
$title = 'Default prices for all companies';
?>
<form onsubmit="doSubmitItems(this);return false;">
    <input type="hidden" name="act" value="" />
    <input type="hidden" name="preid" value="" />
    <div style="float: right;">
        <select name="clid" onchange="getClientPrices(this.value)" style="width:500px;" class="searchablex">
            <option value="">Select company</option>
            <?php foreach ($companies as $company) {
                if (isset($_GET['clid']) && $_GET['clid'] == $company['clid']) {
                    $selectedCompany = $company;
                    $selected = 'selected';
                    $title = 'Default prices for:<br/><span style="color:green;padding:5px;display:block;line-height:24px;width:50%">' . $company['company_name'] . '</span>';
                } else {
                    $selected = '';
                }
            ?>
                <option value="<?php echo $company['clid']; ?>" <?php echo $selected; ?> <?php echo ($company['prices'] != NULL) ? 'style="color:green;font-weight:bold;"' : ''; ?>><?php echo $company['company_name']; ?></option>
            <?php } ?>
        </select>
        <a href="?inc=predefined_prices" style="color:blue;">Reset</a>
    </div>
    <h2 style="margin:0px"><?php echo $title; ?></h2>
    <?php
    if (isset($selectedCompany['prices']) && is_array((json_decode($selectedCompany['prices'], true)))) {
        $selectedCompanyPrices = json_decode($selectedCompany['prices'], true);
    } else {
        $selectedCompanyPrices = array();
    }
    ?>
    <table class="alternateOn center" id="predefined_prices" style="width: 100%;margin-bottom:50px">
        <thead>
            <tr>
                <th style="width: 20px;">::</th>
                <th style="width: 150px;">
                    <select name="invoice_types" onchange="getInvoiceType(this.value)" title="Invoice types">
                        <option value="">All types</option>
                        <?php foreach ($invoice_types as $value) { ?>
                            <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </th>
                <th style="width: 200px;">Service type</th>
                <th style="width: 80px;">Item code</th>
                <th>Description</th>
                <th style="width:450px">Price</th>
                <th style="width: 60px;" id="actionTitle">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($predefined_prices) > 0) {
                foreach ($predefined_prices as $default) {
                    if (isset($selectedCompanyPrices[$default['preid']])) {
                        $client_custom = $selectedCompanyPrices[$default['preid']];
                    } else {
                        $client_custom = array();
                    }
            ?>
                    <tr data-preid="<?php echo $default['preid']; ?>" class="<?php echo trim($default['invoice_type']) != '' ? trim($default['invoice_type']) : 'general'; ?>">
                        <th><?php echo $nr++; ?></th>
                        <td>
                            <select name="invoice_type" disabled>
                                <?php foreach ($invoice_types as $value) {
                                    if ($value == $default['invoice_type']) {
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                ?>
                                    <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $value; ?> Invoices</option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="text" name="service_type" disabled value="<?php echo @$default['service_type']; ?>" /></td>
                        <td><input type="text" name="item_code" disabled value="<?php echo $default['item_code']; ?>" /></td>
                        <td><textarea name="description" disabled><?php echo $default['description']; ?></textarea></td>
                        <td style="white-space: nowrap;">
                            <?php if (trim($default['extra_costs']) != '' and is_array(json_decode($default['extra_costs'], true))) {
                                $extra_costs = json_decode($default['extra_costs'], true);
                                if (isset($client_custom['extra_costs']) && is_array($client_custom['extra_costs']))
                                    $extra_costs = $client_custom['extra_costs'];
                            ?>
                                <ul class="alternateOff" style="padding: 0;margin:0px">
                                    <li><b>Minimum:</b> &euro;<input type="text" class="amount" name="extra_costs[minimum_amount]" disabled value="<?php echo  do_currency($extra_costs['minimum_amount']); ?>" /></li>
                                    <li><b>Administration:</b> &euro;<input type="text" class="amount" name="extra_costs[admin_costs]" disabled value="<?php echo do_currency($extra_costs['admin_costs']); ?>" /></li>
                                    <li><b>&lt;10.000kg:</b> &euro;<input type="text" class="amount" name="extra_costs[price1]" disabled value="<?php echo  do_currency($extra_costs['price1'], 3); ?>" /></li>
                                    <li><b>&gt;10.001kg:</b> &euro;<input type="text" class="amount" name="extra_costs[price2]" disabled value="<?php echo  do_currency($extra_costs['price2'], 3); ?>" /></li>
                                </ul>
                            <?php } else {
                                if (isset($client_custom['price']) && !is_array($client_custom['price']))
                                    $default['price'] = $client_custom['price'];

                                if (isset($client_custom['extra_costs']) && !is_array($client_custom['extra_costs']))
                                    $default['extra_costs'] = $client_custom['extra_costs'];
                            ?>
                                price: <input type="text" name="price" disabled value="<?php echo $default['price']; ?>" style="margin-bottom:5px;" /><br />
                                Math: <input type="text" name="extra_costs" disabled value="<?php echo $default['extra_costs']; ?>" />
                            <?php }; ?>
                        </td>
                        <td class="action"></td>
                    </tr>
            <?php };
            }; ?>
        </tbody>
        <tfoot>
            <tr id="newPriceItem" style="display: none;">
                <th></th>
                <td>
                    <select name="newPriceItem[invoice_type]">
                        <?php foreach ($invoice_types as $value) { ?>
                            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="text" name="newPriceItem[service_type]" value="" required /></td>
                <td><input type="text" name="newPriceItem[item_code]" value="" required /></td>
                <td><textarea name="newPriceItem[description]" required></textarea></td>
                <td>
                    price: <input type="text" name="newPriceItem[price]" style="margin-bottom:5px;" /><br />
                    Math: <input type="text" name="newPriceItem[extra_costs]" />
                <td><i class="far fa-save" onclick="savePrices(this)"></i></td>
            </tr>
        </tfoot>
    </table>
    <div style="position:fixed;bottom:20px;text-align:center;width:100%;left:0px;">
        <div style="max-width: 1400px;background:white;margin:0 auto;">
            <span style="display: none;" id="savePriceItem">
                <input type="button" onclick="resetItems()" value="Reset" />
            </span>
            <span id="addNewPriceItem"><input type="button" value="Add new item" onclick="addNewPriceItem();" /></span>
        </div>
    </div>
</form>