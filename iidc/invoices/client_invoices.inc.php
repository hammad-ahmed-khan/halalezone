<?php
if ($clients = $amdb->get_results("SELECT companies.clid,companies.company_name FROM companies
								JOIN invoices ON companies.clid = invoices.clid
                                group by invoices.clid
                                ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {
?>
    <h4 id="current"></h4>
    <script>
        function loadInvoices() {
            jQuery("#clients_invoices").html('');
            var clid = jQuery("#clid").val();
            var tvh = jQuery("#tvh").val();
            var ascDesc = jQuery("#ascDesc").val();
            jQuery("#clients_invoices").load('load_client_invoices.php?tvh=' + tvh + '&ascDesc=' + ascDesc + '&clid=' + clid);
        }

        function getClientInvoices(pos) {
            jQuery("#clients_invoices").html('');
            jQuery(".searchSelectInput").val('');
            //remove clid all select options
            jQuery("#clid > option").remove();

            if (jQuery(".searchOptionsList li.selected").length > 0) {
                index = jQuery(".searchOptionsList li.selected").index();
                jQuery(".searchOptionsList li.selected").removeClass("selected");
            } else {
                index = -1;
            }

            var next = index + pos;
            if (next >= 0 && next < jQuery(".searchOptionsList li").length) {
                jQuery(".searchOptionsList li").eq(next).addClass("selected");
                jQuery(".searchSelectInput").val(jQuery(".searchOptionsList li.selected").text().trim());
                //add option to select clid
                jQuery("#clid").append('<option value="' + jQuery(".searchOptionsList li.selected").data('value') + ' " selected>' + jQuery(".searchOptionsList li.selected").text() + '</option>');
                loadInvoices();
            }

        }

        function getUnpaidInvoices() {
            jQuery("#client_invoices_table > tbody > tr.unpaid").find('td').each(function() {
                alert(jQuery(this).text());

            })
        }

        function loadUnpaidInvoices(obj) {
            jQuery("#unpaidInvoices").html('');
            var clid = jQuery("#clid").val();
            parentID = jQuery(obj).parent().parents('table').attr('id');
            if (parentID == 'allInvoices')
                year = 'all';
            else
                year = parentID.replace('year_', '');
            jQuery.post('load_invoices.php', {
                show: 'unpaid',
                searchFor: 'client',
                orderBy: 'inserted_on',
                ascDsc: 'DSC',
                offid: '0',
                period:'year',
                year: year,
                clid: clid
            }, function(data) {
                jQuery("#unpaidInvoices").html(data);
            });
        }
    </script>
    <div style="text-align: center;">
        <input type="button" value="<<" onclick="getClientInvoices(-1)"><select size="1" name="clid" id="clid" style="max-width:400px;" class="searchable" onchange="loadInvoices()">
            <option value="">Select Client</option>
        <?php
        foreach ($clients as $client) {
            echo '<option value="' . $client['clid'] . '">' . trim($client['company_name']) . '</option>';
        }
    }
        ?>
        </select><input type="button" value=">>" onclick="getClientInvoices(+1)"><br />
        Results: <select name="tvh" id="tvh" onchange="loadInvoices()">
            <option value="h">Horizontal</option>
            <option value="v">Vertical</option>
        </select>
        Sort Years: <select name="ascDesc" id="ascDesc" onchange="loadInvoices()">
            <option value="ASC">Low -> Height</option>
            <option value="DESC">Height -> Low</option>
        </select>
    </div>
    <div id="clients_invoices" style="margin-top:50px;margin-bottom:20px;"></div>
    <div id="unpaidInvoices"></div>