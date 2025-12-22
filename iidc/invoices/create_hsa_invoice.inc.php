<script>
    $("#page_title").html("Create Shipment certificates invoice for Saudi Arabia")
</script>
<?php
if (!isset($act)) {
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offids[] = $_SESSION['offid'];
        $offices[$_SESSION['offid']] = $_SESSION['hqc_title'];
        if (isset($user_options['invoice_office']) and is_array($user_options['invoice_office']))
            $offids = array_merge($offids, array_values($user_options['invoice_office']));
    } else {
        $offids[0] = 0;
        $offices = array();
        $sql = "SELECT offid,office_name FROM offices";
        if ($options = $amdb->get_results($sql)) {
            foreach ($options as $option) {
                $offids[$option['offid']] = $option['offid'];
                $offices[$option['offid']] = $option['office_name'];
            };
        }
    }

    $offids = implode(',', $offids);

    echo "<center><h2>Create Shipment certificates invoice for Saudi Arabia</h2><p>";
    $invoices = array();
    $client_office = array();
    if (!isset($_GET['status']))
        $status = 'active';
    else
        $status = $_GET['status'];
    $tps = array('sa', 'sb');
    foreach ($tps as $tp) {
        if ($_SERVER['REMOTE_ADDR'] == '82.169.177.142') {
        }
        if ($result = $amdb->get_results("SELECT certificates_{$tp}.nr,certificates_{$tp}.updated_on,certificates_{$tp}.weight_net,certificates_{$tp}.issue_date,certificates_{$tp}.url,certificates_{$tp}.certificate_nr,certificates_{$tp}.clid, certificates_{$tp}.status,companies.offid,companies.company_name FROM certificates_{$tp}
	JOIN companies ON certificates_{$tp}.clid = companies.clid where certificates_{$tp}.done='y' and certificates_{$tp}.invoice_nr='0' and  certificates_{$tp}.status = '$status'")) {
            foreach ($result as $row) {
                $row['tp'] = $tp;
                $invoices[$row['company_name']][$row['clid']][] = $row;
                //   if (isset($offices[$row['offid']]))
                $client_office[$row['clid']] = $offices[$row['offid']];
            }
        }
    }
    //sorting by array key
    ksort($invoices);
    //getting default prices
    if ($predefinedPrices = $amdb->get_results("SELECT preid,service_type,item_code,price,extra_costs FROM hqc_predefined_prices where invoice_type = 'shipment_sab'")) {
        foreach ($predefinedPrices as $price) {
            $defaultPrices[$price['preid']] = $price;
        }
    }
?>
    <style>
        ol.certificates-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #bbb;
        }

        i.status {
            float: right;
        }

        .action {
            white-space: nowrap;
        }

        button i {
            color: white !important;
        }

        button:hover i {
            color: brown !important;
        }

        button>* {
            vertical-align: middle
        }
    </style>
    <div style="width:1200px; margin:0 auto;text-align:right">
        <?php if (!isset($_GET['status'])) { ?>
            <a href="/invoices/index.php?inc=create_hsa_invoice&status=onhold">Certificates on hold</a>
        <?php } else { ?>
            <a href="/invoices/index.php?inc=create_hsa_invoice">Go Back</a>
        <?php } ?>
    </div>
    <table class="alternate certificatesToInvoice" style="width:1200px;">
        <thead>
            <tr>
                <th style="width:20px">No.</th>
                <th>Company/certificate/issue date</th>
                <?php if (!isset($_GET['status'])) { ?><th style="width:80px">Action</th><?php }; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $nr = 1;
            foreach ($invoices as $company => $value) {
                $clid = key($value);
                $comPrices = $defaultPrices;
                if ($comDefPrices = $amdb->get_row("SELECT * FROM hqc_companies_prices where clid = '$clid'")) {
                    if ($comDefPrices = json_decode($comDefPrices['prices'], true)) {
                        foreach ($comDefPrices as $key => $price) {
                            if (isset($comPrices[$key]))
                                $comPrices[$key] = $price;
                        }
                    }
                }
                foreach ($comPrices as $key => $price) {
                    if (!isset($price['price']) or $price['price'] == '0.00') {
                        unset($comPrices[$key]);
                    } else {
                        $comPrices[$price['item_code']] = $price;
                        unset($comPrices[$key]);
                    }
                }
            ?>
                <tr>
                    <th class="aunr"><?php echo $nr++; ?></th>
                    <td><b><?php echo $company; ?></b>
                        <?php if (isset($client_office[$clid])) { ?>
                            <span style="color:green">
                            <?php echo ($client_office[$clid]);
                        }; ?>
                            </span>
                            <button style="float:right;" class="statusAll" data-status="<?php echo !isset($_GET['status']) ? 'onhold' : 'active'; ?>"> <i class="<?php echo !isset($_GET['status']) ? 'far fa-pause-circle' : 'fas fa-reply'; ?>"></i><span><?php echo !isset($_GET['status']) ? 'Put ALL on hold' : 'Restore ALL'; ?></span></button>
                            <ol style="margin:0px;padding:10px 10px 10px 40px;margin-top:20px" class="alternateOff certificates-list">
                                <?php foreach ($value[$clid] as $cert) { ?>
                                    <li style="list-style-type:decimal" data-nr="<?php echo $cert['nr']; ?>" data-tp="<?php echo $cert['tp']; ?>">
                                        <?php if (!isset($_GET['status'])) { ?>
                                            <i class="far fa-pause-circle status" data-status="onhold"><span>Put on hold</span></i>
                                        <?php } else { ?>
                                            <i class="fas fa-reply status" data-status="active"><span>Restore</span></i>
                                        <?php } ?>
                                        <a target="_blank" href="<?php echo "$prog_www/client_data/certificates/$cert[url]?act=print" ?>">
                                            <?php echo $cert['certificate_nr']; ?></a> - <?php echo $cert['issue_date']; ?>
                                        <?php if ($cert['updated_on'] != '0000-00-00 00:00:00' & isset($_GET['status']) && $_GET['status'] == 'onhold') echo '<span style="font-size:10px;color:#900;position:absolute;right:150px;">On-hold: ' . date("d/m/Y H:s", strtotime($cert['updated_on'])) . '</span>'; ?>
                                    </li>
                                <?php }; ?>
                            </ol>
                    </td>
                    <?php if (!isset($_GET['status'])) { ?>
                        <td class="action">
                            <i class="fas fa-cash-register" data-clid="<?php echo $clid; ?>"><span>Invoice</span></i>
                            <br />
                            <?php
                            //TODO: update new system weights gross and net
                            /*
                            $comTotal = 0;
                            $row = array();
                            if (isset($comPrices['HSC']))
                                $row = $comPrices['HSC'];
                            $price1 =  (isset($row['price']) and trim($row['price']) != '') ? $row['price'] : 23;
                            $price2 = $price1 * 2;
                            $vat = 21;
                            $total_invoice_amount = 0;
                            foreach ($invoices[$company] as $comItem) {
                                foreach ($comItem as $total) {
                                    if (strstr($total['weight_net'], '.')) {
                                        if ((explode('.', $total['weight_net'])[1]) / 1000 >= 1)
                                            $total['weight_net'] = str_replace(
                                                '.',
                                                '',
                                                $total['weight_net']
                                            );
                                    };
                                    $comTotal = $comTotal + $total['weight_net'];

                                    $amount = 0;
                                    if ($total['weight_net'] <= 27000)
                                        $amount = $price1;
                                    else
                                        $amount = $price2;

                                    $total_invoice_amount = $total_invoice_amount + $amount;
                                }
                            }
                            echo "<br/><b>Net weight:</b> " . format_number($comTotal) . " KG";
                            ?>
                            <br />
                            <b>Invoice amount:</b><br>
                            <b>HSC:</b> <?php echo format_number($total_invoice_amount); ?><br>
                            <?php
                            foreach ($comPrices as $comKey => $comValue) {
                                if ($comKey == 'HSC') continue;
                                echo "<b>$comKey:</b> " . format_number($comValue['price']) . "<br>";
                                $total_invoice_amount = $total_invoice_amount + $comValue['price'];
                            }
                            echo "<b>VAT:</b> " . format_number($total_invoice_amount * $vat / 100) . "<br>";
                            $total_invoice_amount = $total_invoice_amount + ($total_invoice_amount * $vat / 100);
                            echo "<b>Total:</b> " . format_number($total_invoice_amount);
                            */?>
                        </td>
                    <?php }; ?>
                </tr>
            <?php }; ?>
        </tbody>
    </table>
    <?php if (isset($_GET['status'])) { ?>
        <center>Certificates put on hold will not be invoiced until you restore them </center>
    <?php }; ?>
    <script>
        certUrl = '../certificates/pdf/pdf_certificate.php?usr=a'
        invUrl = 'index.php?inc=create_invoice&type=hsa&goback=<?php echo $_GET['inc']; ?>&clid=';
        jQuery(".fas.fa-cash-register").click(function(e) {
            clid = jQuery(this).data('clid')
            window.location = invUrl + clid;
        });
        jQuery("a.crtNr").click(function(e) {
            tp = jQuery(this).parents('li').data('tp')
            nr = jQuery(this).parents('li').data('nr')
            window.open(certUrl + '&tp=' + tp + '&nr=' + nr);
        });
        jQuery("i.status").click(function(e) {
            status = jQuery(this).data('status')
            var obj = jQuery(this).parents('li')
            tp = obj.data('tp')
            nr = obj.data('nr')
            statusUrl = 'invoice_save.php';
            jQuery.post(statusUrl, {
                act: 'changeStatus',
                status: status,
                tp: tp,
                nr: nr
            }).done(function(data) {
                if (data.trim().length > 0) {
                    if (data.indexOf("error:") > -1) {
                        alert_message(data.replace('error:', ''));
                    } else {
                        jQuery(obj).remove();
                    }
                }
            });
        });

        jQuery("button.statusAll").click(function(e) {
            const nrs = [];
            tp = '';
            status = jQuery(this).data('status')
            obj = jQuery(this).parents('td');

            jQuery(obj).find('li').each(function() {
                nrs.push(jQuery(this).data('nr'));
                tp = jQuery(this).data('tp');
            })
            statusUrl = 'invoice_save.php';
            jQuery.post(statusUrl, {
                act: 'changeStatusAll',
                status: status,
                tp: tp,
                nrs: nrs
            }).done(function(data) {
                if (data.trim().length > 0) {
                    if (data.indexOf("error:") > -1) {
                        alert_message(data.replace('error:', ''));
                    } else {
                        jQuery(obj).parent('tr').remove();
                        do_auto_number('.certificatesToInvoice');
                    }
                }
            });
        });
    </script>
<?php
    return;
}
