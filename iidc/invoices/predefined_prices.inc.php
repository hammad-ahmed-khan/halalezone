<?php if (!defined("_HQC_")) {
    exit();
};

if (isset($_COOKIE['predefined'])) { ?>
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
        padding: 3px 5px;
    }

    #predefined_prices .description textarea {
        width: 250px;
        min-height: 60px;
    }

    #predefined_prices .service_type input {
        width: 150px;
    }

    #predefined_prices .item_code input {
        width: 60px;
    }

    #predefined_prices .prices input {
        width: 90px;
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

    #companiesList li {
        padding: 5px;
        cursor: pointer;
    }

    .green * {
        color: green;
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

    function deleteDefault(obj) {
        invoiceItemToBeDeleted = jQuery(obj).parents('tr').find('.service_type').find('input').val();
        alert_confirm('Delete invoice item <strong>(' + invoiceItemToBeDeleted + ')</strong>');

        act = 'delete_default_prices',
            preid = jQuery(obj).parents('tr').attr('data-preid'),
            clid = jQuery("select[name='clid']").val()

        jQuery("#alertYesBtn").on("click", function() {

        });
    }


    function addNewPriceItem() {
        jQuery("#predefined_prices").find(".action").remove();
        jQuery("#addNewPriceItem").css("display", "none");
        jQuery("#newPriceItem").css("display", "table-row");
        jQuery("input[name='act']").val('insert_default_prices');
        //add data-preid to newPriceItem input fields
        jQuery("#newPriceItem input,textarea").each(function() {
            //if this element has no class extra_costs
            if (!jQuery(this).hasClass("extra_costs")) {
                jQuery(this).attr("required", "required");
            }
        });
    }
    var url = 'predefined_list.php?clid=0';

    function getClientPrices(clid) {
        url = 'predefined_list.php?clid=' + clid;
        jQuery("#companiesPricesHolder").load(url);
    }

    function resetPrices() {
        jQuery("#companiesPricesHolder").load(url);
    }

    function getInvoiceType(val) {
        if (val == '') {
            jQuery("#predefined_prices tbody tr").css("display", "table-row");
        } else {
            jQuery("#predefined_prices tbody tr").css("display", "none");
            jQuery("#predefined_prices tbody tr." + val).css("display", "table-row");
        }
    }

    function searchCompanies(val) {
        jQuery("#companiesList li").each(function() {
            if (jQuery(this).text().toLowerCase().indexOf(val.toLowerCase()) > -1) {
                jQuery(this).css("display", "block");
            } else {
                jQuery(this).css("display", "none");
            }
        });
    }
</script>
<?php
if (!$companies = $amdb->get_results("SELECT companies.clid as clid,companies.company_name,hqc_companies_prices.prices FROM hqc_companies_prices RIGHT JOIN companies ON hqc_companies_prices.clid = companies.clid WHERE companies.active = 'y' AND companies.offid = '0' ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {
    $companies = array();
}
?>
<div style="float: left; width:20%;">
    <input type="search" placeholder="Search companies" style="width: 100%;margin-bottom:10px" onkeyup="searchCompanies(this.value)" />
    <ol class="alternateOn" style="padding:0px;overflow:auto" id="companiesList">
        <li data-clid="0">Default prices for all companies</li>
        <?php foreach ($companies as $company) {
            if (isset($_GET['clid']) && $_GET['clid'] == $company['clid']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
        ?>
            <li data-clid="<?php echo $company['clid']; ?>" <?php echo $selected; ?> <?php echo ($company['prices'] != NULL) ? 'style="color:green;font-weight:bold;"' : ''; ?>><?php echo $company['company_name']; ?></li>
        <?php } ?>
    </ol>
</div>
<div style="float:left;width:78%;" id="companiesPricesHolder"></div>

<script>
    jQuery(document).ready(function() {
        getClientPrices(0);
        //change document location without reloading the page
        if (window.history.pushState) {
            window.history.pushState('', '', '?inc=predefined_prices');
        } else {
            window.location.href = window.location.href + '?inc=predefined_prices';
        }
        jQuery("#companiesList").css("height", jQuery("#predefined_prices").height() + "px");
        jQuery("#companiesList li").on("click", function() {
            jQuery("#companiesList li").css("font-weight", "normal");
            jQuery(this).css("font-weight", "bold");
            getClientPrices(jQuery(this).attr('data-clid'));
        });
    });
</script>