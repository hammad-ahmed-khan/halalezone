<?php
if ((!isset($_GET['clid']) and !isset($_GET['internal'])) and !isset($_GET['act'])) {
    exit();
};

$data = array();
$invoice_options = array();
if (isset($_GET['act']) and isset($_GET['nr'])) {
    if ($invoice_data = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_GET[nr]'")) {
        $_GET['clid'] = $invoice_data['clid'];
        $_GET['type'] = $invoice_data['invoice_type'];
        $invoffid = $invoice_data['offid'];
        $data = decode_json($invoice_data['invoice_data']);
        if (trim($invoice_data['invoice_options']) != '')
            $invoice_options = decode_json($invoice_data['invoice_options']);
    }
}

if (isset($_GET['clid']))
    $clid = $_GET['clid'];

$result = array();
$nr = 0;
$nrIndex = 0;
$template_name = 'invoice';
$offids[0] = 0;
$user_options = get_office_options()['options'];
if (isset($user_options) and isset($user_options['invoices_create'])) {
    $offids[0] = $_SESSION['offid'];
    $offices[$_SESSION['offid']] = $_SESSION['hqc_title'];
    if (isset($user_options['invoice_office']) and is_array($user_options['invoice_office']))
        $offids = array_merge($offids, array_values($user_options['invoice_office']));
} else {
    $offids[0] = 0;
    $offices = array();
    $sql = "SELECT offid,office_name FROM offices WHERE JSON_VALID(options) = 1 AND JSON_EXTRACT(options,'$.invoicing_by') = '0' OR offid=0";
    if ($options = $amdb->get_results($sql)) {
        foreach ($options as $option) {
            $offids[$option['offid']] = $option['offid'];
            $offices[$option['offid']] = $option['office_name'];
        };
    }
}
function get_default_prices()
{
    global $amdb;
    $defaultPrices = array();
    $comPrices = array();

    if ($_GET['type'] != 'general')
        $whr = "AND invoice_type = '$_GET[type]'";
    else
        $whr = "";

    if ($defaultPricesAll = $amdb->get_results("SELECT * FROM hqc_predefined_prices WHERE status != 'deleted' $whr  ORDER BY invoice_type,service_type, item_code")) {
        foreach ($defaultPricesAll as $defaultPrice) {
            $thisPrice = array();
            $thisPrice['preid'] = $defaultPrice['preid'];
            $thisPrice['service_type'] = $defaultPrice['service_type'];
            $thisPrice['item'] = $defaultPrice['item_code'];
            $thisPrice['description'] = $defaultPrice['description'];
            $thisPrice['cost'] = str_replace('.', ',', $defaultPrice['price']);
            $defaultPrices['predefined'][$defaultPrice['preid']] = $thisPrice;
        }
    }

    if ($comPrices = $amdb->get_row("SELECT * FROM hqc_companies_prices where clid='$_GET[clid]'")) {
        if (trim($comPrices['prices']) != '' and is_array(json_decode($comPrices['prices'], true))) {
            $comPrices = json_decode($comPrices['prices'], true);
        }
    }
    return ['defaultPrices' => $defaultPrices, 'comPrices' => $comPrices];
}
//show php errors
//ini_set('display_errors', 1);
$offids = implode(',', $offids);
$service_type_default = array('a' => 'Batch Certificate(s)', 'b' => 'Batch Certificate(s)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'supervision' => 'Halal supervision', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'expenses' => 'Expenses', 'hsa' => 'Saudi Halal Center Shipment Certificate Request', 'hfc' => 'Halal Facility Certificate for Saudi Halal Center');
$invoice_item_default = array('a' => 'Certificate A (Meat product)', 'b' => 'Certificate B (Non-meat product)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'supervision' => 'Halal supervision', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'expenses' => 'Expenses');
$service_type = array();
$invoice_item = array();
if ($defaults = json_decode(get_option('invoice_defaults'), true)) {
    $service_type = $defaults['service_type'];
    $invoice_item = $defaults['invoice_item'];
}
foreach ($service_type_default as $key => $value) {
    if (!isset($service_type[$key]))
        $service_type[$key] = $value;
}
foreach ($invoice_item_default as $key => $value) {
    if (!isset($invoice_item[$key]))
        $invoice_item[$key] = $value;
}
$batch = array();
$product = array();
$annual = array();

//get default prices
if ($_GET['type'] == 'hsa' or $_GET['type'] == 'hfc') {
    $invoice_type = $_GET['type'] == 'hsa' ? 'shipment_sab' : 'hfc';
    if ($predefinedPrices = $amdb->get_results("SELECT preid,service_type,item_code,price,description,extra_costs FROM hqc_predefined_prices where invoice_type = '$invoice_type'")) {
        foreach ($predefinedPrices as $price) {
            $defaultPrices[$price['preid']] = $price;
        }
    }
    $comPrices = $defaultPrices;
    if ($comDefPrices = $amdb->get_row("SELECT * FROM hqc_companies_prices where clid = '$_GET[clid]'")) {
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
} else {
    $defaultPrices = get_default_prices()['defaultPrices'];
    $comPrices = get_default_prices()['comPrices'];
    // print_r($defaultPrices);
    // print_r($comPrices);
    // exit();
}


$batchPrices = array();

if (isset($defaultPrices['batch']) and is_array($defaultPrices['batch']))
    $batchPrices = $defaultPrices['batch'];
if (isset($defaultPrices['annual']) and is_array($defaultPrices['annual']))
    $annualPrice = $defaultPrices['annual'];
if (isset($defaultPrices['product']) and is_array($defaultPrices['product']))
    $productPrice = $defaultPrices['product'];

if ($_GET['type'] == 'hfc') {
    $pageTtl = "Create Halal Facility invoice for Saudi Arabia";
    $invoiceTitle = $pageTtl;
    $serviceType = $service_type['hfc'];
    $vat = 21;
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $batch_invoice_items = decode_json($invoice_data['invoice_items']);
        if (count($batch_invoice_items) > 0) {
            foreach ($batch_invoice_items as $batch_invoice_item) {
                if (isset($batch_invoice_item['description']) && trim($batch_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $batch_invoice_item['type'];
                    if (isset($result[$nrIndex]['crtNr']))
                        $result[$nrIndex]['crtNr'] = $batch_invoice_item['crtNr'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $batch_invoice_item['amount']);
                    $result[$nrIndex]['description'] = $batch_invoice_item['description'];
                    $result[$nrIndex]['product'] = $batch_invoice_item['product'];
                    if (isset($result[$nrIndex]['crtNr']) && $crtNrRes = $amdb->get_row("SELECT  certificate_nr FROM certificates_{$batch_invoice_item['type']} WHERE nr = $batch_invoice_item[crtNr]")) {
                        $result[$nrIndex]['certificate_nr'] = $crtNrRes['certificate_nr'];
                    }
                }
            }
        }
    } else {
        foreach ($comPrices as $comKey => $comValue) {
            $nrIndex++;
            $result[$nrIndex]['type'] = $_GET['type'];
            $result[$nrIndex]['product'] = $comValue['service_type'];
            $result[$nrIndex]['description'] = $comValue['description'];
            $result[$nrIndex]['amount'] = $comValue['price'];
        }
    }
} elseif ($_GET['type'] == 'hsa') {
    $pageTtl = "Create Shipment certificates invoice for Saudi Arabia";
    $invoiceTitle = $pageTtl;
    $serviceType = $service_type['hsa'];

    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $batch_invoice_items = decode_json($invoice_data['invoice_items']);
        if (count($batch_invoice_items) > 0) {
            foreach ($batch_invoice_items as $batch_invoice_item) {
                if (isset($batch_invoice_item['description']) && trim($batch_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $batch_invoice_item['type'];
                    if (isset($result[$nrIndex]['crtNr']))
                        $result[$nrIndex]['crtNr'] = $batch_invoice_item['crtNr'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $batch_invoice_item['amount']);
                    $result[$nrIndex]['description'] = $batch_invoice_item['description'];
                    $result[$nrIndex]['product'] = $batch_invoice_item['product'];
                    if (isset($result[$nrIndex]['crtNr']) && $crtNrRes = $amdb->get_row("SELECT  certificate_nr FROM certificates_{$batch_invoice_item['type']} WHERE nr = $batch_invoice_item[crtNr]")) {
                        $result[$nrIndex]['certificate_nr'] = $crtNrRes['certificate_nr'];
                    }
                }
            }
        }
    } else {
        //show php errors
        ini_set('display_errors', 1);
        $tps = array("sa", "sb");

        foreach ($tps as $tp) {
            $certificates = $amdb->get_results("SELECT certificates_{$tp}.*, companies.offid,companies.company_name FROM certificates_{$tp}
            JOIN companies ON certificates_{$tp}.clid = companies.clid where certificates_{$tp}.done='y' and certificates_{$tp}.invoice_nr='0' and  certificates_{$tp}.status = 'active' AND certificates_{$tp}.clid = '$_GET[clid]'");

            if (count($certificates) > 0) {
                if (isset($certificates[0]['offid']) && $certificates[0]['offid'] == '0') {
                    $defaultPrices = get_default_prices()['defaultPrices'];
                    $comBatchPrices = get_default_prices()['comPrices'];
                    $batchPrices = $defaultPrices['batch'];
                    $row = array();
                    if (isset($comBatchPrices['batch']))
                        $row = $comBatchPrices['batch'];
                    $minimum_amount = (isset($row['minimum_amount']) and trim($row['minimum_amount']) != '') ? $row['minimum_amount'] : $batchPrices['minimum_amount'];
                    $admin_costs =  (isset($row['admin_costs']) and trim($row['admin_costs']) != '') ? $row['admin_costs'] : $batchPrices['admin_costs'];
                    $price1 =  (isset($row['price1']) and trim($row['price1']) != '') ? $row['price1'] : $batchPrices['price1'];
                    $price2 = (isset($row['price2']) and trim($row['price2']) != '') ? $row['price2'] : $batchPrices['price2'];

                    foreach ($certificates as $certificate) {

                        $nrIndex++;
                        $net_weight = str_replace('.', ',', $certificate['weight_net']);

                        if (!strstr($net_weight, ','))
                            $net_weight .= ',00';

                        if (isset($certificate['weight_net'])) {
                            if (strstr($certificate['weight_net'], '.')) {
                                if ((explode('.', $certificate['weight_net'])[1]) / 1000 >= 1)
                                    $certificate['weight_net'] = str_replace(
                                        '.',
                                        '',
                                        $certificate['weight_net']
                                    );
                            };

                            if ($certificate['weight_net'] <= 10000)
                                $amount = ($certificate['weight_net'] * fix_currency($price1));
                            else
                                $amount = ($certificate['weight_net'] * fix_currency($price2));
                            if ($amount < $minimum_amount)
                                $amount = $minimum_amount;
                            $amount = $amount + fix_currency($admin_costs);
                            if ($certificate['is_bad'] == "y")
                                $amount = $admin_costs;
                        }
                        $result[$nrIndex]['type'] = $tp;
                        $result[$nrIndex]['product'] = 'General Fees';
                        $result[$nrIndex]['crtNr'] = $certificate['nr'];
                        $result[$nrIndex]['description'] = 'Certificate Nr: ' . $certificate['certificate_nr'] . "\r\n" . 'Date: ' . $certificate['date'];
                        if (trim($certificate['reference']) != '')
                            $result[$nrIndex]['description'] .= "\r\n" . 'reference: ' . str_replace('_', ' ', $certificate['reference']);
                        $result[$nrIndex]['description'] .= "\r\n" . 'Net weight: ' .  $net_weight . 'KG';

                        $result[$nrIndex]['amount'] = $amount;
                        $result[$nrIndex]['certificate_nr'] = $certificate['certificate_nr'];
                    }
                }
                $row = array();
                if (isset($comPrices['HSC']))
                    $row = $comPrices['HSC'];
                $price1 =  (isset($row['price']) and trim($row['price']) != '') ? $row['price'] : 240;
                $price2 = $price1 * 2;
                $vat = 21;
                $total_admin_costs = 0;
                foreach ($certificates as $certificate) {
                    $nrIndex++;
                    $total_admin_costs++;
                    $net_weight = str_replace('.', ',', $certificate['weight_net']);

                    if (!strstr($net_weight, ','))
                        $net_weight .= ',00';

                    if (isset($certificate['weight_net'])) {

                        //TODO: add this filter to the new system
                        if (strstr($certificate['weight_net'], '.')) {
                            if ((explode('.', $certificate['weight_net'])[1]) / 1000 >= 1)
                                $certificate['weight_net'] = str_replace(
                                    '.',
                                    '',
                                    $certificate['weight_net']
                                );
                        };

                        if ($certificate['weight_net'] <= 27000)
                            $amount = $price1;
                        else
                            $amount = $price2;
                    }
                    $result[$nrIndex]['type'] = $tp;
                    $result[$nrIndex]['product'] = ($tp == 'sa') ? 'Certificate Type: HSA' : 'Certificate Type: HSB';
                    $result[$nrIndex]['crtNr'] = $certificate['nr'];
                    $result[$nrIndex]['description'] = 'Certificate Nr: ' . $certificate['certificate_nr'] . "\r\n" . 'Date: ' . $certificate['date'];
                    if (trim($certificate['reference']) != '')
                        $result[$nrIndex]['description'] .= "\r\n" . 'reference: ' . str_replace('_', ' ', $certificate['reference']);
                    $result[$nrIndex]['description'] .= "\r\n" . 'Net weight: ' .  $net_weight . 'KG';
                    if ($certificate['weight_net'] > 27000) {
                        $result[$nrIndex]['description'] .= "\r\n" . 'Net weight above 27 tons';
                        $total_admin_costs++;
                    }
                    $result[$nrIndex]['amount'] = $amount;
                    $result[$nrIndex]['certificate_nr'] = $certificate['certificate_nr'];
                }
                foreach ($comPrices as $comKey => $comValue) {
                    $nrIndex++;
                    if ($comKey == 'HSC') continue;
                    $result[$nrIndex]['type'] = $tp;
                    $result[$nrIndex]['product'] = $comValue['service_type'];
                    $result[$nrIndex]['description'] = $comValue['description'];
                    if ($comKey == 'HSC-Admin') {
                        $result[$nrIndex]['description'] .= "\r\n" . str_replace('.', ',', $comValue['price']) . ' Euro per requested certificate';
                        $result[$nrIndex]['amount'] = $total_admin_costs * $comValue['price'];
                    } else {
                        $result[$nrIndex]['amount'] = $comValue['price'];
                    }
                }
            }
        }
    }
} else if ($_GET['type'] == 'shipment') {
    $pageTtl = "Create shipment certificates invoice";
    $invoiceTitle = "Shipment certificates invoice";
    $serviceType = $service_type['a'];
    //getting default prices===============================================================================

    $row = array();
    print_r($comPrices);
    if (isset($comPrices['shipment']))
        $row = $comPrices['shipment'];
    $minimum_amount = (isset($row['minimum_amount']) and trim($row['minimum_amount']) != '') ? $row['minimum_amount'] : $batchPrices['minimum_amount'];
    $admin_costs =  (isset($row['admin_costs']) and trim($row['admin_costs']) != '') ? $row['admin_costs'] : $batchPrices['admin_costs'];
    $price1 =  (isset($row['price1']) and trim($row['price1']) != '') ? $row['price1'] : $batchPrices['price1'];
    $price2 = (isset($row['price2']) and trim($row['price2']) != '') ? $row['price2'] : $batchPrices['price2'];
    $vat = 20;
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $batch_invoice_items = decode_json($invoice_data['invoice_items']);
        if (count($batch_invoice_items) > 0) {
            foreach ($batch_invoice_items as $batch_invoice_item) {
                if (isset($batch_invoice_item['description']) && trim($batch_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $batch_invoice_item['type'];
                    if (isset($result[$nrIndex]['crtNr']))
                        $result[$nrIndex]['crtNr'] = $batch_invoice_item['crtNr'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $batch_invoice_item['amount']);
                    $result[$nrIndex]['description'] = $batch_invoice_item['description'];
                    $result[$nrIndex]['product'] = $batch_invoice_item['product'];
                    if (isset($result[$nrIndex]['crtNr']) && $crtNrRes = $amdb->get_row("SELECT  certificate_nr FROM certificates_{$batch_invoice_item['type']} WHERE nr = $batch_invoice_item[crtNr]")) {
                        $result[$nrIndex]['certificate_nr'] = $crtNrRes['certificate_nr'];
                    }
                }
            }
        }
    } else {
        $tps = array("a", "b");
        foreach ($tps as $tp) {
            $certificates = $amdb->get_results("SELECT certificates_{$tp}.*, companies.offid,companies.company_name FROM certificates_{$tp}
            JOIN companies ON certificates_{$tp}.clid = companies.clid where FIND_IN_SET(companies.offid,'$offids') AND certificates_{$tp}.done='y' and certificates_{$tp}.invoice_nr='0' and  certificates_{$tp}.status = 'active' AND certificates_{$tp}.clid = '$_GET[clid]'");
            if (count($certificates) > 0) {
                foreach ($certificates as $certificate) {
                    $nrIndex++;

                    $net_weight = str_replace('.', ',', $certificate['weight_net']);
                    if (!strstr($net_weight, ','))
                        $net_weight .= ',00';

                    if (isset($certificate['weight_net'])) {


                        //TODO: add this filter to the new system
                        if (strstr($certificate['weight_net'], '.')) {
                            if ((explode('.', $certificate['weight_net'])[1]) / 1000 >= 1)
                                $certificate['weight_net'] = str_replace(
                                    '.',
                                    '',
                                    $certificate['weight_net']
                                );
                        };

                        if ($certificate['weight_net'] <= 10000)
                            $amount = ($certificate['weight_net'] * fix_currency($price1));
                        else
                            $amount = ($certificate['weight_net'] * fix_currency($price2));
                        if ($amount < $minimum_amount)
                            $amount = $minimum_amount;
                        $amount = $amount + fix_currency($admin_costs);
                        if ($certificate['is_bad'] == "y")
                            $amount = $admin_costs;
                    }
                    $result[$nrIndex]['type'] = $tp;
                    $result[$nrIndex]['product'] = ($tp == 'a') ? $invoice_item['a'] : $invoice_item['b'];
                    $result[$nrIndex]['crtNr'] = $certificate['nr'];
                    $result[$nrIndex]['description'] = 'Certificate Nr: ' . $certificate['certificate_nr'] . "\r\n" . 'Date: ' . $certificate['date'];
                    if (trim($certificate['reference']) != '')
                        $result[$nrIndex]['description'] .= "\r\n" . 'reference: ' . str_replace('_', ' ', $certificate['reference']);
                    $result[$nrIndex]['description'] .= "\r\n" . 'Net weight: ' .  $net_weight . 'KG';

                    $result[$nrIndex]['amount'] = $amount;
                    $result[$nrIndex]['certificate_nr'] = $certificate['certificate_nr'];
                }
            }
        }
    }
} elseif ($_GET['type'] == 'annual') {
    $pageTtl = "Create annual certificate invoice";
    $invoiceTitle = "Annual certificates invoice";
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $annual_invoice_items = decode_json($invoice_data['invoice_items']);
        $serviceType = $invoice_data['service_type'];
        if (count($annual_invoice_items) > 0) {
            foreach ($annual_invoice_items as $annual_invoice_item) {
                if (isset($annual_invoice_item['description']) && trim($annual_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $annual_invoice_item['type'];
                    $result[$nrIndex]['product'] = $annual_invoice_item['product'];
                    $result[$nrIndex]['description'] = $annual_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $annual_invoice_item['amount']);
                }
            }
        }
    } else {
        $serviceType = $service_type['annual'];
        if (isset($_REQUEST['crtNr']))
            $annual = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates where crtNr = '$_REQUEST[crtNr]' and $tbl[prefix]_halal_certificates.clid = $clid");
        else
            $annual = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates where FIND_IN_SET(offid,'$offids') and $tbl[prefix]_halal_certificates.clid = $clid");
        $result[$nrIndex]['type'] = 'annual';
        $result[$nrIndex]['product'] = $invoice_item['annual'];
        if (count($annual) > 0) {
            $result[$nrIndex]['crtNr'] = $annual['crtNr'];
            $result[$nrIndex]['description'] = 'Certificate Nr: ' . $annual['certificate_nr'] . "\r\n" . 'Issue date: ' . date("d/m/Y", $annual['date_of_issue']);
        }
        if (isset($comPrices['annual'])) {
            $row = $comPrices['annual'];
            if (trim($row['cost']) != '')
                $annualAmount = $row['cost'];
        }
        $result[$nrIndex]['amount'] = isset($annualAmount) ? $annualAmount : (isset($annualPrice) ? $annualPrice['cost'] : '0,00');
    }
} elseif ($_GET['type'] == 'audit') {
    $audit = array();
    $expense_type = array();
    $auditors = array();
    $types = array();
    $pageTtl = "Create audit invoice";
    $invoiceTitle = "Audit invoice";
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        if (is_array(decode_json($invoice_data['invoice_data']))) {
            $this_invoice_data = decode_json($invoice_data['invoice_data']);
            if (isset($this_invoice_data['audit'])) {
                $audit = $this_invoice_data['audit'];
            }
        }
        $audit_invoice_items = decode_json($invoice_data['invoice_items']);
        $serviceType = $invoice_data['service_type'];
        if (count($audit_invoice_items) > 0) {
            foreach ($audit_invoice_items as $audit_invoice_item) {
                if (isset($audit_invoice_item['description']) && trim($audit_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $audit_invoice_item['type'];
                    $result[$nrIndex]['product'] = $audit_invoice_item['product'];
                    $result[$nrIndex]['description'] = $audit_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $audit_invoice_item['amount']);
                }
            }
        }
    } else {
        $serviceType = $service_type['audit'];
        if (isset($_GET['auid']) && $audit = $amdb->get_row("SELECT * FROM audits WHERE auid = '$_GET[auid]'")) {
            if ($audits_types = json_decode(get_option('audit_type'), true)) {
                foreach ($audits_types as $key => $type) {
                    if (trim($type['name']) != '')
                        $types[$key] = $type['name'];
                }
            }
            if ($expense_types = json_decode(get_option('expense_type'), true)) {
                foreach ($expense_types as $key => $type) {
                    if (trim($type['name']) != '')
                        $expense_type[$key] = $type['name'];
                }
            }
            if ($auditorsAll = $amdb->get_results("SELECT uid,username_owner FROM hqc_admin_users WHERE 1 ORDER BY username_owner ASC")) {
                foreach ($auditorsAll as $theAuditor) {
                    $auditors[$theAuditor['uid']] = $theAuditor['username_owner'];
                }
            }
            if ($expense_items = json_decode(str_replace("\r\n", '\n', $audit['expenses']), true)) {
                foreach ($expense_items as $key => $item) {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = 'audit';
                    $result[$nrIndex]['product'] = $expense_type[$item['type']];
                    $result[$nrIndex]['description'] = $item['description'];
                    $result[$nrIndex]['amount'] = '';
                    $result[$nrIndex]['expense'] = fix_currency($item['amount']);
                };
            };
        } else {
            $result[$nrIndex]['type'] = 'audit';
            $result[$nrIndex]['product'] = $invoice_item['audit'];
            $result[$nrIndex]['description'] = '';
            $result[$nrIndex]['amount'] = '';
        }
    }
} elseif ($_GET['type'] == 'supervision') {

    $audit = array();
    $expense_type = array();
    $auditors = array();
    $types = array();
    $pageTtl = "Create halal supervision invoice";
    $invoiceTitle = "Halal supervision invoice";
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        if (is_array(decode_json($invoice_data['invoice_data']))) {
            $this_invoice_data = decode_json($invoice_data['invoice_data']);
            if (isset($this_invoice_data['audit'])) {
                $audit = $this_invoice_data['audit'];
            }
        }
        $audit_invoice_items = decode_json($invoice_data['invoice_items']);

        $serviceType = $invoice_data['service_type'];
        if (count($audit_invoice_items) > 0) {
            foreach ($audit_invoice_items as $audit_invoice_item) {
                if (isset($audit_invoice_item['description']) && trim($audit_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $audit_invoice_item['type'];
                    $result[$nrIndex]['product'] = $audit_invoice_item['product'];
                    $result[$nrIndex]['description'] = $audit_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $audit_invoice_item['amount']);
                }
            }
        }
    } else {
        $serviceType = $service_type['supervision'];
        if (isset($_GET['auid']) && $audits = $amdb->get_results("SELECT * FROM hqc_supervisions WHERE status = 'active' AND clid = '$_GET[clid]' AND auid = '$_GET[auid]'")) {

            foreach ($audits as $audit) {
                if ($audits_types = json_decode(get_option('audit_type'), true)) {
                    foreach ($audits_types as $key => $type) {
                        if (trim($type['name']) != '')
                            $types[$key] = $type['name'];
                    }
                }
                $expense_types = array("supervision_costs" => array("name" => "Supervision costs"), "expenses" => array("name" => "Other costs"));
                foreach ($expense_types as $key => $type) {
                    if (trim($type['name']) != '')
                        $expense_type[$key] = $type['name'];
                }

                if ($auditorsAll = $amdb->get_results("SELECT uid,username_owner FROM hqc_admin_users WHERE 1 ORDER BY username_owner ASC")) {
                    foreach ($auditorsAll as $theAuditor) {
                        $auditors[$theAuditor['uid']] = $theAuditor['username_owner'];
                    }
                }
                if ($expense_items = json_decode(str_replace("\r\n", '\n', $audit['expenses']), true)) {
                    foreach ($expense_items as $key => $item) {
                        $nrIndex++;
                        $result[$nrIndex]['type'] = 'supervision';
                        $result[$nrIndex]['product'] = 'AUD-02';
                        $result[$nrIndex]['description'] = "Halal supervision fees\nSupervisor: Mohamad Al Chaman\nDate: " . date("d/m/Y", strtotime($audit['supervision_date']));
                        $result[$nrIndex]['amount'] = fix_currency($item['amount']);
                        $result[$nrIndex]['expense'] = fix_currency($item['amount']);
                    };
                };

                if ($expense_items = json_decode(str_replace("\r\n", '\n', $audit['kilometers']), true)) {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = 'supervision';
                    $result[$nrIndex]['product'] = 'EXP-01';
                    $result[$nrIndex]['description'] = "Kilometers count,Total: $expense_items[total] KM\nCharges per KM: $expense_items[rate] Euro cent";
                    $result[$nrIndex]['amount'] = fix_currency($expense_items['costs']);
                    $result[$nrIndex]['expense'] = fix_currency($expense_items['costs']);
                };
            }
        } else {
            $result[$nrIndex]['type'] = 'supervision';
            $result[$nrIndex]['product'] = $invoice_item['audit'];
            $result[$nrIndex]['description'] = '';
            $result[$nrIndex]['amount'] = '';
        }
    }
} elseif ($_GET['type'] == 'general') {
    $pageTtl = "Create general invoice";
    $invoiceTitle = "General invoice";
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $general_invoice_items = decode_json($invoice_data['invoice_items']);
        $serviceType = $invoice_data['service_type'];
        if (count($general_invoice_items) > 0) {
            foreach ($general_invoice_items as $general_invoice_item) {
                if (isset($general_invoice_item['description']) && trim($general_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $general_invoice_item['type'];
                    $result[$nrIndex]['product'] = $general_invoice_item['product'];
                    $result[$nrIndex]['description'] = $general_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $general_invoice_item['amount']);
                }
            }
        }
    } else {
        $serviceType = $service_type['general'];
        $result[$nrIndex]['type'] = 'General invoice';
        $result[$nrIndex]['product'] = $invoice_item['general'];
        $result[$nrIndex]['description'] = '';
        $result[$nrIndex]['amount'] = '';
    }
} elseif ($_GET['type'] == 'credit_note') {
    $pageTtl = "Create credit note";
    $invoiceTitle = "Credit note";
    //TODO: add this to the new system
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        $annual_invoice_items = decode_json($invoice_data['invoice_items']);
        $serviceType = $invoice_data['service_type'];
        if (count($annual_invoice_items) > 0) {
            foreach ($annual_invoice_items as $annual_invoice_item) {
                if (isset($annual_invoice_item['description']) && trim($annual_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = $annual_invoice_item['type'];
                    $result[$nrIndex]['product'] = $annual_invoice_item['product'];
                    $result[$nrIndex]['description'] = $annual_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $annual_invoice_item['amount']);
                }
            }
        }
    } else {
        $serviceType = $service_type['credit_note'];
        $result[$nrIndex]['type'] = 'Credit note';
        $result[$nrIndex]['product'] = $invoice_item['credit_note'];;
        $result[$nrIndex]['description'] = '';
        $result[$nrIndex]['amount'] = '';
        $template_name = 'credit_note';
    }
} elseif ($_GET['type'] == 'expenses') {
    include dirname(__FILE__) . '/inc/expenses.inc.php';
}
$invoice_template = array();
$invoice_template = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$template_name'");
?>
<script>
    $("#page_title").html("<?php echo $pageTtl; ?>")

    function badCer(goodBad, nr, tp) {
        if (confirm("Are you sure?") == "1") {
            var time = new Date().getTime();
            $.post("<?php echo $prog_www ?>/admin/admin_save.php?tm=" + time, {
                    act: "badCer",
                    tp: tp,
                    nr: nr,
                    goodBad: goodBad
                },
                function(data) {
                    if (data != "") {
                        if (data.indexOf('success') > -1) {
                            document.location = document.location.href
                        } else {
                            alert_message(data);
                        }
                    }
                });
        }
    }
    var MailPost = 'mail';

    function selectall(obj) {
        $("#invoiceTbl .invoiceItem").prop('checked', $(obj).prop('checked'));
    }

    async function create_invoice(act) {
        document.invoice_form.act.value = act;
        document.invoice_form.target = '_blank';
        if (act == 'crt' || act == 'test') {
            if (MailPost == 'mail')
                document.invoice_form.target = 'invoice_frame';
            else
                document.invoice_form.target = '_blank';
        }
        if (act == 'save_draft' || act == 'update_draft' || act == 'save_scheduled' || act == 'update_scheduled') {
            if (act == 'save_scheduled' || act == 'update_scheduled') {
                if (jQuery("#scheduled_date").val().trim() == '' || jQuery("#scheduled_hour").val().trim() == '') {
                    alert_message("Scheduled hour & time are required.");
                    return false;
                }
            }
            document.invoice_form.target = 'invoice_frame';
        }
        <?php if ($_SERVER['REMOTE_ADDR'] == '::1') { ?>
            document.invoice_form.target = '_blank';
        <?php }; ?>
        var sel = 0;
        var error = false;
        var amountError = false;
        var invoiceType = '<?php echo $_GET['type']; ?>';

        if (post_this_form(document.invoice_form) == false) {
            return false;
        };
        $("#invoiceTbl input[type='text'], #invoiceTbl textarea").css('border-color', '#c0c0c0')
        $(".invoiceItem").each(function(index, element) {
            if ($(this).prop('checked') == true) {
                sel++;
                id = $(this).data('id');
                if ($('#product_' + id).val() == '') {
                    $('#product_' + id).css('border-color', 'red');
                    error = true;
                }
                if ($('#description_' + id).val() == '') {
                    $('#description_' + id).css('border-color', 'red');
                    error = true;
                }
                if ($('#amount_' + id).val() == '') {
                    $('#amount_' + id).css('border-color', 'red');
                    error = true;
                } else if (invoiceType == 'credit_note') {
                    amount = $('#amount_' + id).val().replace(/,/g, '').replace(/\./g, '')
                    if (amount > 0) {
                        $('#amount_' + id).css('border-color', 'red');
                        amountError = true;
                    }
                }
            }
        });

        if (sel > 0) {
            if (error == true) {
                alert_message('Some fields are empty!');
                return false;
            }
            if (jQuery("#splitInvoice").prop('checked') == true) {
                //count checked invoiceSender2
                if (jQuery(".invoiceSender2:checked").length > 0) {
                    if (jQuery(".invoiceItem:checked").length < 2) {
                        alert_message('Please select at least two invoice item!');
                        return false;

                    }

                    if (jQuery(".invoiceItem:checked").length == jQuery(".invoiceSender2:checked").length) {
                        alert_message('Selected invoice items should be split invoice sender!');
                        return false;

                    }

                    if (jQuery("#sbsid1").val() == jQuery("#sbsid").val()) {
                        alert_message('Split invoice sender should be different from main invoice sender!');
                        return false;
                    }
                } else {
                    alert_message('Please select at least one split invoice sender!');
                    return false;
                }
            }
            if (amountError == true) {
                alert_message('For credit notes. Amounts should be negative!');
                return false;
            }
            document.invoice_form.submit();

            if (jQuery("#splitInvoice").prop('checked') == true && jQuery(".invoiceSender2:checked").length > 0 && jQuery(".invoiceItem").length > 1) {
                console.log(jQuery(".invoiceSender2:checked").length);
                setTimeout(() => {
                    jQuery("#splittedInvoice").val('1');
                    document.invoice_form.submit();
                }, 500);
                jQuery("#splittedInvoice").val('');
            }
            if (act == "crt") {
                <?php
                $goback = '/invoices/?show=all';
                if ($_GET['goback'] == 'expenses')
                    $goback = '../expenses/index.php?inc=' . $_GET['goback'];
                elseif ($_GET['goback'] == 'audits')
                    $goback = '../audit/';
                elseif ($_GET['goback'] == 'invoices' && isset($_GET['show']))
                    $goback = '/invoices/?show=' . $_GET['show'];
                elseif ($_GET['goback'] == 'create_cohs_invoice')
                    $goback = '/invoices/index.php?inc=create_cohs_invoice';
                elseif ($_GET['goback'] == 'draft')
                    $goback = '/invoices/?show=draft';
                elseif ($_GET['goback'] == 'halal-supervision')
                    $goback = '/audit/halal-supervision/index.php';
                ?>
                setTimeout("document.location.href='<?php echo $goback; ?>'", 500);
            } else if (act == 'save_draft' || act == 'update_draft') {
                setTimeout("document.location.href='/invoices/?show=draft'", 500);
            } else if (act == 'save_scheduled' || act == 'update_scheduled') {
                setTimeout("document.location.href='/invoices/?show=scheduled'", 500);
            }
        } else
            alert_message("Please select at least one invoice item");
    }

    function showHideSplitInvoice() {
        if (jQuery("#invoiceItems .invoiceItem").length > 1) {
            jQuery("#splitInvoiceTbl").css('display', 'block');
        } else {
            jQuery(".invoiceSender2").prop('checked', false);
            jQuery("#splitInvoice").prop('checked', false);
            jQuery("#splitInvoiceTbl").css('display', 'none');
        }
    }

    function addNewItem(tp) {
        newItem = $("#selectAll").attr('data-nr');
        newItem++;
        $("#selectAll").attr('data-nr', newItem)
        if (tp == 'product') {
            type = 'text';
            value = '';
        } else {
            type = 'hidden'
            value = 'hidden'
        }

        newInvoiceItem = '<tr><th><input type="checkbox" name="item[' + newItem + '][selected]" class="invoiceItem" data-id="' + newItem + '" value="yes" checked><input type="hidden" name="item[' + newItem + '][type]" value="costume"></th>' +
            '<td><input type="checkbox" name="invoiceSender2[]" class="invoiceSender2" value="' + newItem + '"><input type="' + type + '" name="item[' + newItem + '][product]" id="product_' + newItem + '" value="' + value + '" class="invoiceService"></td>' +
            '<td><textarea name="item[' + newItem + '][description]" id="description_' + newItem + '"></textarea></td>' +
            '<td><input name="item[' + newItem + '][amount]" class="amount" id="amount_' + newItem + '" type="text" value=""><i class="fa fa-trash-alt" onclick="deleteInvoiceItem(this);"></i></td></tr>';
        jQuery("#invoiceItems").append(newInvoiceItem);
        loadServiceItems();
        jQuery("#invoiceItems .invoiceSender2").css('display', 'none');
        jQuery("#invoiceItems td input.invoiceService").css('width', '100%');
        jQuery("#splitInvoice").prop('checked', false);
        jQuery(".invoiceSender2").prop('checked', false);
        showHideSplitInvoice();
        return newItem;
    }

    function testMail(obj) {
        if (obj.checked) {
            jQuery("emailmeacopy").prop("checked", false);
            jQuery('#sendmecopy').css('display', 'none');
            jQuery("test_invoice_email").prop("checked", false);
            jQuery('#testEmail').css('display', 'none')
            jQuery('#bcc_email').css('display', 'none')
            MailPost = 'post';
        }
    }
    <?php if (isset($clid)) { ?>

        function updateVatNumber() {
            vatNr = $("#vat_number").val();
            jQuery.post("invoice_save.php", {
                act: 'update_vat_number',
                clid: '<?php echo $_GET['clid']; ?>',
                vatNr: vatNr
            }, function(data) {
                if (data.trim() != '') {}
            })
        }

        function updatePONumber() {
            poNr = $("#po_number").val();
            jQuery.post("invoice_save.php", {
                act: 'update_po_number',
                clid: '<?php echo $_GET['clid']; ?>',
                poNr: poNr
            }, function(data) {
                if (data.trim() != '') {
                    alert_message(data);
                }
            })
        }
    <?php }; ?>

    function toggleItems() {
        jQuery("#predefinedPrices").slideToggle()
    }

    var inputSelected = null;
    var predefinedItemsSelected = false;

    function showServiceItems(input) {
        inputSelected = jQuery(input);
        jQuery("#predefinedItems").css({
            "visibility": "visible",
            "position": "absolute",
            "top": jQuery(input).offset().top + jQuery(input).height() + 12,
            "left": jQuery(input).offset().left,
            "width": jQuery(input).width() + 18
        });
        searchList(input, "#predefinedItems", false, false);
    }

    function hideServiceItems() {
        inputSelected = null;
        predefinedItemsSelected = false;
        jQuery("#predefinedItems").css({
            "visibility": "hidden",
            "position": "fixed",
            "top": "-10000"
        });
    }

    function loadServiceItems() {
        jQuery("ul#predefinedItems li").on("click", function() {
            var date = '<?php echo date('d/m/Y'); ?>';
            if (jQuery("#dhl_waybill"))
                Waybill = jQuery("#dhl_waybill").val()
            else
                Waybill = '';

            predefinedItemsSelected = true;
            if (inputSelected != null) {
                itemNr = jQuery(inputSelected).attr('id').split('_')[1]
                id = jQuery(this).data('item')
                var priceItem = jQuery.parseJSON(predefinedPrices);
                jQuery("#product_" + itemNr).val(priceItem[id].item)
                jQuery("#description_" + itemNr).val(priceItem[id].description.replace('[date]', date).replace('[Waybill]', Waybill))
                jQuery("#amount_" + itemNr).val(priceItem[id].cost)
                hideServiceItems();
            }
        })

        jQuery("#invoiceItems td input.invoiceService").on("click", function() {
            showServiceItems(this);
        })

        jQuery("#invoiceItems td input.invoiceService").on("blur", function() {
            setTimeout(() => {
                if (predefinedItemsSelected == false) {
                    hideServiceItems();
                }
            }, 300);
        })
        jQuery("#splitInvoice").on("click", function() {
            if (jQuery("#splitInvoice").is(':checked')) {
                jQuery("#invoiceItems td input.invoiceService").css('width', '85%');
                jQuery("#invoiceItems .invoiceSender2").css('display', 'inline-block');
            } else {
                jQuery("#invoiceItems").find('.fa-square').remove();
                jQuery(".invoiceSender2").prop('checked', false);
                jQuery("#invoiceItems .invoiceSender2").css('display', 'none');
                jQuery("#invoiceItems td input.invoiceService").css('width', '100%');
            }
        })

        jQuery(".invoiceSender2").on("click", function() {
            jQuery("#invoiceItems").find('.fa-square').remove();
            jQuery(".invoiceSender2").each(function() {
                if (jQuery(this).is(':checked')) {
                    jQuery(this).parents("tr").find("th").append('<span class="fa fa-square" style="color:gold"></span>');
                }
            })
        })
    }

    <?php if (isset($defaultPrices['predefined']) and count($defaultPrices['predefined']) > 0) {

        foreach ($defaultPrices['predefined'] as $key => $value) {
            if (isset($comPrices[$key])) {
                if ($comPrices[$key]['price'] == null)
                    $comPrices[$key]['price'] = '0,00';
                $defaultPrices['predefined'][$key]['cost'] = str_replace('.', ',', $comPrices[$key]['price']);
                $defaultPrices['predefined'][$key]['extra_costs'] = $comPrices[$key]['extra_costs'];
            }
        }

        $predefinedTem = array();
        foreach ($defaultPrices['predefined'] as $predefined) {
            $predefinedTem[$predefined['item']] = $predefined;
        }
        // print_r($predefinedTem);
        $defaultPrices['predefined'] = $predefinedTem;
        ksort($defaultPrices['predefined']);
        //jseon($defaultPrices['predefined']); with fixing break lines and quotes
        foreach ($defaultPrices['predefined'] as $key => $value) {
            $defaultPrices['predefined'][$key]['description'] = str_replace(array("\r\n", "\n"), '\n', $value['description']);
            $defaultPrices['predefined'][$key]['description'] = str_replace('"', '\"', $defaultPrices['predefined'][$key]['description']);
        }
    ?>
        var predefinedPrices = '<?php echo json_encode($defaultPrices['predefined']); ?>';

        function insertPredefined(id) {
            var priceItem = jQuery.parseJSON(predefinedPrices);
            newItemId = addNewItem('product');
            jQuery("#product_" + newItem).val(priceItem[id].item)
            jQuery("#description_" + newItem).val(priceItem[id].description)
            jQuery("#amount_" + newItem).val(priceItem[id].cost)
            jQuery("#predefinedPrices").slideToggle();
        }
    <?php }; ?>

    function deleteInvoiceItem(obj) {
        selectedItem = $(obj).parents('tr');
        alert_confirm('Delete invoice item?');
        jQuery("button#alertYesBtn").click(function() {
            close_alert();
            $(selectedItem).remove();
            showHideSplitInvoice();
        })
    }
</script>
<style>
    #invoiceTbl input[type='text'],
    #invoiceTbl textarea {
        width: 100%;
    }

    #invoiceTbl textarea {
        height: 85px
    }

    #auditTable th {
        width: 80px;
    }

    #auditTable td {
        width: 260px;
    }

    .creditNoteInvoice b {
        display: inline-block;
        width: 60px;
        text-transform: capitalize;
        text-align: right;
        margin-right: 5px;
    }

    .invoice-address td {
        padding: 20px;
        border: none
    }

    .invoice-address td:after {
        content: none
    }

    td#client_address b {
        float: left;
        width: 120px;
    }

    td#client_address div {
        margin-bottom: 5px;
    }

    table {
        width: 100%;
    }

    input.amount {
        width: 100px !important
    }

    i.fa-trash-alt {
        margin-left: 10px
    }

    #predefinedPrices {
        position: absolute;
        bottom: 0;
        background: #fff;
        border: 1px solid var(--color40);
        padding: 10px;
        left: 10px;
        right: 10px;
        box-shadow: 2px 3px 3px #555;
        display: none;
    }

    #predefinedPrices .title {
        padding: 5px;
        background: var(--color30);
        width: 100%;
        margin-bottom: 10px;
    }

    #predefinedPrices ul {
        overflow: auto;
        max-height: 250px;
    }

    #predefinedPrices ul li {
        padding: 2px;
        cursor: pointer;
        font-weight: normal;
    }

    #predefinedPrices ul li:hover {
        color: #cf2423;
        background: var(--color40);
    }

    ul#predefinedItems {
        position: fixed;
        visibility: hidden;
        top: -5000px;
        background: white;
        padding: 10px;
        border: 1px solid var(--color40)
    }

    ul#predefinedItems li {
        cursor: pointer;
    }

    ul#searchPredefinedList {
        padding: 0px;
        overflow: hidden !important;
    }

    <?php if ($_GET['type'] == 'recurring') { ?>#monthly_settings b {
        width: 120px;
        display: inline-block;
    }

    #monthly_settings select,
    #monthly_settings input {
        width: 100px !important;
    }

    #monthly_settings div {
        margin: 5px 0px !important;
    }

    <?php }; ?>.invoiceService {
        /* width: 85% !important; */
    }

    .invoiceSender2 {
        margin-right: 5px !important;
        display: none;
    }
</style>
<?php
echo "<pre>";
//print_r($GLOBALS);
echo "</pre>";
?>
<h2 style="text-align:center"><?php echo $invoiceTitle; ?></h2>
<div style="position: absolute; left: -1000; top: -1000;display:none;visibility:hidden"><iframe src="" name="invoice_frame" style="width:0px;height:0px"></iframe></div>
<form action="pdf/pdf_invoice.php" method="post" target="_blank" name="invoice_form" autocomplete="off">
    <input type="hidden" name="splittedInvoice" id="splittedInvoice" value="">
    <?php
    $client_emails = array();
    $client_country = 'Netherlands';
    if ($row = $amdb->get_row("SELECT * FROM companies where clid='$clid'")) {
        $client_country = $row['country1'];
        if ($row['sbsid'] == 0)
            $row['sbsid'] = 1;
        if (isset($data['sbsid']))
            $row['sbsid'] = $data['sbsid'];

        $company_address['company_name'] = $row['company_name'];
        $company_address['contact_person'] = 'Att. ' . trim(htmlspecialchars($row['contact_title1'] . ' ' . $row['contact_name1'] . ' ' . $row['contact_surname1']));
        $company_address['address'] = trim(htmlspecialchars($row['street1'] . "\n" . $row['zip1'] . " " . $row['city1'] . "\n" . $row['country1']));
        $vat_number = $row['vatNr'];
        $company_data['company_name'] = $company_address['company_name'];
        $company_data['contact_person'] = $company_address['contact_person'];
        $company_data['address'] = $company_address['address'];
        $company_data['vat_number'] = $row['vatNr'];
        $company_data['telephone'] = $row['tel1'];
        $company_data['email'] = $row['email1'];
        $company_data = json_encode($company_data);
        if ($row['offid'] != '0') {
            if (!isset($country))
                include "../config/countries.code.php";
            $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$row[offid]'");
            $office_data['address'] = trim(htmlspecialchars($office['office_street'] . "\n" . $office['office_zipcode'] . " " . $office['office_city'] . "\n" . $country[$office['office_country']]));
            $office_data['company_name'] = $office['company_name_english'];
            $office_name = $office_data['company_name'];
            $office_email = $office['office_email'];
            $office_data['contact_person'] = 'Att. ' . trim(htmlspecialchars($office['contact_person']));
            $office_data['vat_number'] = $office['office_vat'];
            $office_data['telephone'] = $office['office_telephone'];
            $office_data['email'] = $office['office_email'];
            $office_data = json_encode($office_data);
        }
    }

    if (isset($company_address)) {
        if (isset($data['company_address'])) {
            $company_address = $data['company_address'];
            $vat_number = $data['vat_number'];
        };
    ?>
        <script>
            function updateCompanyAddress(obj) {
                if (jQuery(obj).is(':checked')) {
                    if (jQuery(obj).data('address') == 'office' && jQuery('#office_data').val().trim() != '') {
                        jQuery('[name="uba"]').prop('checked', false);
                        var data = jQuery.parseJSON(jQuery('#office_data').val());
                    }
                } else {
                    var data = jQuery.parseJSON(jQuery('#company_address_holder').val());
                }
                var company_name = data.company_name;
                var name = data.contact_person;
                var address = data.address;
                var vat = data.vat_number;
                var email = data.email;
                var tel = data.telephone;
                jQuery("#company_name").val(company_name.trim());
                jQuery("#company_contact_person").val(name.trim());
                jQuery("#company_address").val(address.trim());
                jQuery("#vat_number").val(vat);
                jQuery("#company_email").html(email);
                jQuery("#company_email").attr('href', 'mailto:' + email);
                jQuery("#company_telephone").html(tel);
                jQuery("#client_emails,#client_email").val(email);
            }
        </script>
        <?php if (isset($_GET['act']) && $_GET['act'] == 'edit') { ?>
            <h2 style="color:red;text-align:center">You are about to edit the invoice</h2>
            <div style="text-align:center">The number and the date of the invoice will not be changed <span style="color:red">please make a preview first</span></div>
        <?php }; ?>
        <table border=0 width="750" style="margin-bottom: 12px;border:0px" class="alternate invoice-address">
            <tr>
                <td valign=top style='width:50%;vertical-align:top' id="client_address">
                    <div>
                        <b style="text-transform: uppercase;">Invoice to:</b> <input type="text" id="company_name" name="company_address[company_name]" style="width:60%" value="<?php echo htmlspecialchars(trim($company_address['company_name'])); ?>" data-required="yes" />
                        <?php
                        if (($_GET['type'] == 'hsa' or $_GET['type'] == 'hfc') && isset($office_data)) { ?>
                            <br />
                            <b>To HQC office:</b><label><input type="checkbox" name="hqcOffice" value="yes" onclick="updateCompanyAddress(this)" data-address="office" /><?php echo $office_name; ?></label>
                            <textarea style="display:none" id="office_data"><?php echo $office_data; ?></textarea>
                        <?php } ?>
                    </div>
                    <div>
                        <b>Contact Person:</b> <input type="text" name="company_address[contact_person]" id="company_contact_person" value="<?php echo $company_address['contact_person'] ?>" data-required="yes" style="width:60%" />
                    </div>
                    <div>
                        <b>Address:</b>
                        <textarea name="company_address[address]" id="company_address" data-required="yes" style="width:60%;height:80px"><?php echo $company_address['address']; ?></textarea>
                    </div>
                    <?php
                    $po = '';
                    if (is_array(json_decode($row['billing_address'], true))) {
                        $billing_address = json_decode($row['billing_address'], true);
                        if (isset($billing_address['email']) and trim($billing_address['email']) != '')
                            $client_emails['Billing address'] = $billing_address['email'];
                        $po = isset($billing_address['po']) ? $billing_address['po'] : '';
                    }
                    ?>
                    <div><b>PO number:</b>
                        <input type="text" name="po_number" id="po_number" value="<?php echo $po; ?>" style="width:160px" />
                    </div>
                    <div><b>VAT number:</b><input name="vat_number" id="vat_number" value="<?php echo $vat_number; ?>" style="width:160px"></div>
                    </span>
                    <br />
                    <?php
                    $client_emails['Head Office'] = $row['email1'];
                    if (isset($data['client_email']))
                        $client_emails['Other'] = $data['client_email'];
                    if (isset($office_email))
                        $client_emails['HQC Office'] = $office_email;
                    ?>
                    <b>Telephone:</b><span id="company_telephone"> <?php echo $row['tel1']; ?></span><br />
                    <b>E-mail:</b> <a href="mailto:<?php echo $row['email1']; ?>" id="company_email"><?php echo $row['email1']; ?></a>
                    <?php billing_address($row['billing_address']); ?>
                    <textarea style="display:none" id="company_address_holder"><?php echo $company_data; ?></textarea>
                    <textarea style="display:none" id="billing_address_holder"><?php echo $row['billing_address']; ?></textarea>
                    <script>
                        function updateBillingAddress() {
                            if (jQuery('[name="uba"]').is(':checked') && jQuery('#billing_address_holder').val() != '') {
                                jQuery('[name="hqcOffice"]').prop('checked', false);

                                //parse billing_address_holder json
                                var billing_address = jQuery.parseJSON(jQuery('#billing_address_holder').val());
                                var name = 'Att.: ' + billing_address.name;
                                var address = billing_address.street + "\n" + billing_address.zipcode + " " + billing_address.city + "\n" + billing_address.country;
                                var email = billing_address.email;
                                if (address.trim() != 'none') {
                                    jQuery("#company_contact_person").val(name.trim());
                                    jQuery("#company_address").val(address.trim());
                                } else {
                                    updateCompanyAddress(null);
                                }
                                jQuery("#client_emails,#client_email").val(email.trim());
                            } else {
                                updateCompanyAddress(jQuery('[name="uba"]'));
                            }
                        }

                        if (jQuery('[name="uba"]').length > 0) {
                            jQuery('[name="uba"]').on("click", function() {
                                updateBillingAddress();
                            })
                        }
                        updateBillingAddress();
                    </script>
                </td>
                <td>
                    <div>
                        <b>INVOICE FROM:</b>
                        <?php
                        if (!isset($invoffid))
                            $invoffid  = $_SESSION['offid'];
                        $invoicing_address = '';
                        $invoicing_offices = $amdb->get_results("SELECT * FROM `hqc_invoicing_offices` WHERE invoice_company_name != '' ORDER BY invoice_company_name");
                        ?>
                        <select size="1" name="invoffid" id="invoffid" style="margin-bottom: 10px;" onchange="jQuery('#invoicing_office').load('/invoices/get_invoicing_office.php?offid='+this.value)">
                            <?php
                            foreach ($invoicing_offices as $invoicing_office) {
                                if (isset($invoffid) and $invoffid == $invoicing_office['offid']) {
                                    $selected = 'selected';
                                    $invoicing_address = $invoicing_office['invoice_address'];
                                    $vat_rate = $invoicing_office['invoice_vat_rate'];
                                    $data['bcc_email'][] = $invoicing_office['invoice_email'];
                                } else {
                                    $selected = '';
                                }
                                echo "<option value='$invoicing_office[offid]' $selected>$invoicing_office[invoice_company_name]</option>";
                            }
                            ?>
                        </select>
                        <div id="invoicing_office"><?php echo $invoicing_address; ?>
                            <?php
                            if ($invoffid == '0') { ?>
                                <div style="margin-top: 20px;">
                                    <b>Invoice Language:</b>
                                    <select name="invoice_lang">
                                        <option value="german">German</option>
                                        <option value="english">English</option>
                                    </select>
                                </div>
                            <?php
                            }
                            ?>
                            <input type="hidden" name="vat_rate" id="vat_rate" value="<?php echo $vat_rate; ?>" />
                        </div>
                    </div>
                    <?php if ($_GET['type'] == 'batch') { ?>
                        <div>
                            <a data-url="/invoices/service_prices_save.php?clid=<?php echo $clid; ?>&act=get_defaults" class="load_popup" title="Default prices"><b>Service prices</b></a><br>
                            Minimum amount: <?php echo $minimum_amount; ?><br>
                            Admin costs: <?php echo $admin_costs; ?><br>
                            Less than 10,001 KG: <?php echo $price1; ?><br>
                            More than 10,000 KG: <?php echo $price2; ?><br>
                        </div>
                    <?php }; ?>
                    <?php if (isset($_GET['invnr']) and $credit_invoices = $amdb->get_results("SELECT * FROM invoices where FIND_IN_SET(nr, '$_GET[invnr]')")) {
                        $invoice = $credit_invoices[0];
                    ?>

                        <div style="padding:5px" class="creditNoteInvoice">
                            <h4 style="white-space: nowrap;margin: 0 0 5px 0;">Credit note for the invoice(s):</h4>
                            <?php
                            $invoice_numbers = array();
                            $subtotal = 0;


                            foreach ($credit_invoices as $credit_invoice) {
                                $invFile = "/client_data/invoices/$credit_invoice[invoice_nr].pdf";
                            ?>
                                <div style="margin-bottom:20px;">
                                    <b>Number:</b>
                                    <?php if (file_exists($prog_path . $invFile)) { ?>
                                        <a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $credit_invoice['invoice_nr']; ?>.pdf" target="_blank"><?php echo $credit_invoice['invoice_nr']; ?></a>
                                    <?php } else {
                                        echo $credit_invoice['invoice_nr'];
                                    } ?>
                                    <br />
                                    <b>date:</b><?php echo $credit_invoice['date']; ?><br />
                                    <b>subtotal:</b>&euro; <?php echo $credit_invoice['subtotal']; ?><br />
                                    <b>VAT:</b>&euro; <?php echo $credit_invoice['vat']; ?>
                                    <hr />
                                    <b>Total:</b><strong>&euro; <?php echo $credit_invoice['total']; ?></strong>
                                </div>
                            <?php
                                $invoice_numbers[] = $credit_invoice['invoice_nr'];
                                $subtotal = $subtotal + $credit_invoice['subtotal'];
                            }; ?>
                        </div>
                    <?php
                        $invoice['invoice_nr'] = implode(',', $invoice_numbers);
                        $result[0]['description'] = 'Credit note for invoice' . (count($invoice_numbers) > 1 ? 's' : ' No') . ': ' . implode(' | ', $invoice_numbers);
                        $result[0]['amount'] = '-' . $subtotal;
                    } ?>
                </td>
            </tr>
        <?php
    };
        ?>
        </table>
        <?php if (isset($clid)) { ?>
            <input type="hidden" name="clid" value="<?php echo  $clid ?>">
        <?php } else { ?>
            <input type="hidden" name="intID" value="<?php echo  $_GET['intID'] ?>">
        <?php }; ?>
        <input type="hidden" name="act" value="">
        <input type="hidden" name="invoice_type" value="<?php echo  $_GET['type']; ?>">
        <?php if (isset($_GET['exid'])) { ?>
            <input type="hidden" name="exid" value="<?php echo  $_GET['exid']; ?>">
        <?php }; ?>
        <?php if (isset($invoice) and $invoice['invoice_nr']) { ?>
            <input type="hidden" name="credit_invnr" value="<?php echo  $invoice['invoice_nr']; ?>">
        <?php }; ?>
        <?php if (isset($_GET['auid'])) { ?>
            <input type="hidden" name="auid" value="<?php echo  $_GET['auid']; ?>">
        <?php }; ?>
        <?php if (isset($_GET['nr']) && (!isset($_GET['act']) or $_GET['act'] != 'clone')) { ?>
            <input type="hidden" name="nr" value="<?php echo  $_GET['nr']; ?>">
        <?php }; ?>
        <?php if (isset($_GET['nr']) && (isset($_GET['act']) and $_GET['act'] == 'edit') && isset($invoice_data)) { ?>
            <input type="hidden" name="nr" value="<?php echo  $_GET['nr']; ?>">
            <input type="hidden" name="invoice_number" value="<?php echo  $invoice_data['invoice_nr']; ?>">
            <input type="hidden" name="invoice_date" value="<?php echo  $invoice_data['date']; ?>">
            <input type="hidden" name="update" value="yes">
        <?php }; ?>
        <?php
        if ($_GET['type'] == 'audit' and is_array($audit) and count($audit) > 0) {
            if (isset($audit['date']))
                $audit['audit_date'] = fix_date($audit['date']);
            if (isset($audit['auditors']) and !is_array(json_decode($audit['auditors'], true))) {
                $thisAuditors = $audit['auditors'];
            } else {
                $thisAuditors = '';
                if ($auditAuditors = json_decode($audit['auditors'], true)) {
                    if (isset($auditAuditors['leading']) and isset($auditors[$auditAuditors['leading']])) {
                        $thisAuditors = '1- ' . $auditors[$auditAuditors['leading']];
                    };
                    if (isset($auditAuditors['co']) and count($auditAuditors['co']) > 0) {
                        $co = 2;
                        foreach ($auditAuditors['co'] as $key => $value) {
                            if (isset($auditors[$value])) {
                                $thisAuditors .= "\n" . $co++ . ' - ' . $auditors[$value];
                            };
                        }
                    };
                }
            }
            if (isset($audit['place'])) {
                $audit_place = $audit['place'];
            } else {
                $audit_place = '';
                if (trim($audit['audit_place']) != '') {
                    $audit_place = $audit['audit_place'];
                } else {
                    $audit_place = $row['street1'] . "\n" . $row['zip1'] . ', ' . $row['city1'];
                }
            }
        ?>
            <table class="alternateOn" width="750" id="auditTable">
                <tr>
                    <th>Audit Date:</th>
                    <td><input type="text" class="date" name="audit[date]" data-required="yes" value="<?php echo (isset($audit['audit_date'])) ? date("d/m/Y", strtotime($audit['audit_date'])) : ''; ?>" /></td>
                    <th>Aditor(s):</th>
                    <td><textarea name="audit[auditors]" style="width:100%;height:60px" data-required="yes"><?php echo $thisAuditors; ?></textarea></td>
                </tr>
                <tr>
                    <th>Audit Type:</th>
                    <td><input type="text" name="audit[type]" style="width:100%" data-required="yes" value="<?php echo (isset($audit['audit_type']) && isset($types[$audit['audit_type']])) ? $types[$audit['audit_type']] : (isset($audit['type']) ? $audit['type'] : ''); ?>" /></td>
                    <th>Adit Place:</th>
                    <td><textarea name="audit[place]" style="width:100%;height:60px" data-required="yes"><?php echo $audit_place; ?></textarea></td>
                </tr>
            </table>
            <div style="text-transform:uppercase; width:750px;min-width:75%;margin:0 auto;margin-top:20px"><b>Audit Expenses: </b></div>
        <?php };  ?>
        <table border=0 width="750" id="invoiceTbl" class="alternate">
            <tr>
                <th width="20"><input type="checkbox" onclick="selectall(this);"></th>
                <th style="width:250px">Item</th>
                <th>Description</th>
                <th style="width:150px">Amount</th>
            </tr>
            <tbody id="invoiceItems">
                <?php
                if (count($result) > 0) {
                    foreach ($result as $key => $row) {
                        $nr++;
                ?>
                        <tr <?php echo (isset($row['crtNr'])) ? 'data-tp="' . $row['type'] . '" data-nr="' . $row['crtNr'] . '"' : ''; ?>>
                            <th><input type="checkbox" name='item[<?php echo $nr; ?>][selected]' value="yes" class="invoiceItem" data-id="<?php echo $nr; ?>" <?php echo isset($_GET['act']) && $_GET['act'] == 'edit' ? 'checked' : ''; ?>></th>
                            <input type="hidden" name='item[<?php echo $nr; ?>][type]' value="<?php echo $row['type']; ?>">
                            <?php if (isset($row['crtNr'])) { ?>
                                <input type="hidden" name='item[<?php echo $nr; ?>][crtNr]' value="<?php echo $row['crtNr']; ?>">
                            <?php }; ?>
                            </th>
                            <td>
                                <input type="checkbox" name="invoiceSender2[]" class="invoiceSender2" value="<?php echo $nr; ?>">
                                <input type="<?php echo $row['product'] == 'hidden' ? 'hidden' : 'text'; ?>" name="item[<?php echo $nr; ?>][product]" id="product_<?php echo $nr; ?>" value="<?php echo $row['product']; ?>" class="invoiceService" />
                                <?php
                                if (isset($row['certificate_nr']) and trim($row['certificate_nr']) != '') { ?>
                                    <br /><br />
                                    <a href='../certificates/pdf/pdf_certificate.php?tp=<?php echo $row['type']; ?>&nr=<?php echo $row['crtNr']; ?>&usr=a' target=_blank><?php echo $row['certificate_nr']; ?></a>
                                <?php } ?>
                            </td>
                            <td>
                                <textarea name="item[<?php echo $nr; ?>][description]" id="description_<?php echo $nr; ?>"><?php echo $row['description']; ?></textarea>
                            </td>
                            <td>
                                <input name="item[<?php echo $nr; ?>][amount]" class="amount" id="amount_<?php echo $nr; ?>" type='text' value='<?php echo (trim($row['amount']) != '') ? number_format(fix_currency($row['amount']), 2, ',', '.') : ''; ?>' />
                                <?php if ($nr > 1) { ?>
                                    <i class="fa fa-trash-alt" onclick="deleteInvoiceItem(this);"></i>
                                <?php }; ?>
                                <?php if ($_GET['type'] == 'batch' && !isset($_GET['act'])) { ?>
                                    <div style="margin-top:12px">
                                        <i class="far fa-pause-circle status" data-status="onhold"><span>Put on hold</span></i>
                                        <div style="margin-top:12px">
                                        <?php }; ?>
                                        <?php if ($_GET['type'] == 'audit' && isset($row['expense'])) { ?>
                                            <div style="margin-top:12px"><b> Audit expense:</b> &euro;<?php echo number_format($row['expense'], 2, ',', '.'); ?></div>
                                        <?php }; ?>
                            </td>
                        </tr>
                <?php
                    };
                };
                ?>
            </tbody>
            <tr>
                <th><input type="checkbox" id="selectAll" data-nr="<?php echo $nr; ?>" onclick="selectall(this);"></th>
                <th style="position: relative;">
                    <input type="button" onclick="addNewItem('product')" value="Add item" />
                    <?php if (isset($defaultPrices['predefined']) and count($defaultPrices['predefined']) > 0) { ?>
                        <i class="far fa-list-alt" onclick="toggleItems()" title="Predefined" style="float: right;;margin-top:10px;"></i>
                        <div id="predefinedPrices">
                            <div class="th" style="padding: 5px 10px; margin-bottom: 10px;"><i class="fas fa-times" onclick="toggleItems()" style="float: right; margin-right: 5px;"></i> Predefined Items</div>
                            <input type="text" id="searchPredefined" style="padding: 5px;margin-bottom: 10px;" placeholder="Search predefined" />
                            <ul id="searchPredefinedList">
                                <?php
                                $predefinedItems = '<ul id="predefinedItems" class="alternateOn">';
                                foreach ($defaultPrices['predefined'] as $price) {
                                    $predefinedItems .= '<li data-item="' . $price['item'] . '">' . $price['item'] . ' / ' . $price['service_type'] . '</li>';
                                ?>
                                    <li onclick="insertPredefined('<?php echo $price['item']; ?>')"><?php echo $price['item']; ?> / <?php echo $price['service_type']; ?></li>
                                <?php }
                                $predefinedItems .= '</ul>'; ?>
                            </ul>
                            <!-- <span style="" data-width="850" class="load_popup" data-url="predefined.php">Manage predefined items</span> -->
                        </div>
                        <script>
                            searchList("#searchPredefined", "#searchPredefinedList", false, false);
                            jQuery("body").append('<?php echo $predefinedItems; ?>');
                        </script>
                    <?php }; ?>
                </th>
                <th>
                    <input type="button" onclick="addNewItem('description')" value="Add Subitem" style="width:50%;" />
                </th>
                <th id="totalAmount"></th>
            </tr>
        </table>
        <table cellpadding="2" cellspacing="2" width="750" style="margin-top:20px;">
            <tr>
                <th>Email comment:</th>
                <th><?php echo (!in_array('invoices_draft_only', $user_permissions)) ? 'Send invoice by:' : '' ?></th>
            </tr>
            <?php if (isset($data['email_message'])) {
                $email_message = $data['email_message'];
            } else {
                $email_message = array('body' => '', 'color' => '');
            }; ?>
            <tr>
                <td><textarea name="email_message[body]" style="width:98%;height:55px"><?php echo $email_message['body']; ?></textarea>
                    <div style="margin:12px 5px"><b>Comment style:</b> <select size="1" name="email_message[color]">
                            <option value="">Default color</option>
                            <option value="blue" <?php echo $email_message['color'] == 'blue' ? 'selected' : ''; ?>>Blue</option>
                            <option value="green" <?php echo $email_message['color'] == 'green' ? 'selected' : ''; ?>>Green</option>
                            <option value="red" <?php echo $email_message['color'] == 'red' ? 'selected' : ''; ?>>Red</option>
                        </select>
                        <label><input type="checkbox" name="email_message[font-weight]" value="bold" <?php echo isset($email_message['font-weight']) ? 'checked' : ''; ?> /> Bold</label> <label><input type="checkbox" name="email_message[font-style]" value="italics" <?php echo isset($email_message['font-style']) ? 'checked' : ''; ?> /> Italics</label>
                    </div>
                    <div style="font-size:12px;font-style:italic">This comment will be inserted in the email message send to the client <span style="color:red">(will replace [admin_message])</span></div>
                </td>
                <?php                ?>
                <td style="background:#eee">
                    <ul style="padding:0px 5px;<?php echo (in_array('invoices_draft_only', $user_permissions)) ? 'display:none' : '' ?>">
                        <li>
                            <label>
                                <input type="radio" name="mail_post" value="post" onclick="testMail(this);">
                                Print invoice</label>
                        </li>
                        <li>
                            <label><input type="radio" name="mail_post" checked value="mail" onclick="
    if(this.checked){
    jQuery('#emailmeacopy').prop('checked',true);
    jQuery('#sendmecopy').css('display','block')
    jQuery('#bcc_email').css('display','block')
    MailPost = 'mail';
    }
    ">
                                E-mail</label> <span id="sendmecopy" style="display:nonr">
                                <label><input type="checkbox" checked="checked" name="emailmeacopy" value='y' onclick="$('#bcc_email').css('display',(this,checked)?'block':'none')">
                                    Email me a copy</label>
                                <label><input type="checkbox" name="test_invoice_email" value='y' onclick="$('#testEmail').css('display',(this,checked)?'block':'none')">
                                    Test email</label>
                            </span>
                        </li>
                        <li id="bcc_email" style="padding:12px;border: 1px solid rgb(187, 187, 187);margin-top: 12px;background: rgb(236, 234, 228);">
                            <b>Send the invoice to the client to the following address:</b><br />
                            <select size="1" id="client_emails" onchange="jQuery('#client_email').val(this.value)">
                                <?php foreach ($client_emails as $emailKey => $emailValue) { ?>
                                    <option value="<?php echo $emailValue; ?>"><?php echo $emailKey; ?></option>
                                <?php }    ?>
                            </select>

                            <input type="text" name="client_email" id="client_email" style="width:70%" value="" data-required='yes' placeholder="Client email is required" /><br /><br />
                            <script>
                                <?php if (isset($data['client_email'])) { ?>
                                    jQuery('#client_emails').val('<?php echo $data['client_email']; ?>');
                                <?php }; ?>
                                jQuery('#client_email').val(jQuery('#client_emails').val());
                            </script>
                            <b>Send us a BCC to the following address(es):</b><br />
                            <?php
                            if (isset($data['bcc_email']) and is_array($data['bcc_email']) and count($data['bcc_email']) > 0) {
                                $bccNr = 1;
                                foreach ($data['bcc_email'] as $bccK => $bcc) { ?>
                                    <div style="margin-top:10px">
                                        <input type="text" name="bcc_email[]" value="<?php echo $bcc; ?>" style="width:90%" class="bcc_email" />
                                        <?php if ($bccNr > 1) { ?>
                                            <i class="far fa-trash-alt" onclick="$(this).closest('div').remove();"></i>
                                        <?php
                                        } else {  ?>
                                            <i class="fa fa-plus" onclick="addBccEmail('');"></i>
                                        <?php }; ?>
                                    </div>
                                <?php $bccNr++;
                                }
                            } else { ?>
                                <input type="text" name="bcc_email[]" style="width:90%" value="<?php echo (isset($invoice_template['email_bcc_address'])) ? $invoice_template['email_bcc_address'] : 'info@iidc.eu'; ?>" class="bcc_email" /><i class="fa fa-plus" onclick="addBccEmail('');"></i>
                            <?php }; ?>
                        </li>
                        <li id="testEmail" style="padding:12px;display:none;border: 1px solid rgb(187, 187, 187);margin-top: 12px;background: rgb(236, 234, 228);">
                            <b>Test Email</b><br />
                            <input type="text" name="test_email" style="width:70%" value="info@iidc.eu" />
                            <input type="button" onclick="create_invoice('test')" value="Test" style="width:25%" />
                            <br />
                            <span style="font-size:12px;font-style:italic">You will receive exact copy of the invoice, but no data will be saved nor email will be sent to the client</span>
                        </li>
                    </ul>
                </td>
            </tr>
        </table>
        <table cellpadding="2" cellspacing="2" width="750" style="margin-top:20px;">
            <tr style="background:#eee">
                <th style="width:50px !important">Vat:</th>
                <td style="background:#eee;width:180px;" id="vatRate"></td>
                <td style="text-align:center">
                    <?php /*if ((isset($_GET['type']) && $_GET['type'] == 'general') or (isset($_GET['act']) and $_GET['act'] == 'scheduled')) {
                        $scheduled = array();
                        if (isset($invoice_options['scheduled']))
                            $scheduled = $invoice_options['scheduled'];
                    ?>
                        <div style="float:left"><label><input type="checkbox" value="scheduled" name="scheduled" id="scheduledCheckBox" onclick="switchScheduleInputs()" <?php echo (isset($_GET['act']) && $_GET['act'] == 'scheduled') ? 'checked' : ''; ?> />Schedule invoice</label> <span id="scheduleInputs" style="display:none"><strong>Send on date:</strong><input type="text" name="scheduled[date]" id="scheduled_date" value="<?php echo isset($scheduled['date']) ? $scheduled['date'] : ''; ?>" class="date" /> <strong>Hour:</strong> <input type="number" max="23" min="1" name="scheduled[hour]" id="scheduled_hour" value="<?php echo isset($scheduled['hour']) ? $scheduled['hour'] : '1'; ?>" style="width:50px" /> (Between 1 & 23) </span></div>
                        <script>
                            function switchScheduleInputs() {
                                if (document.getElementById("scheduledCheckBox").checked) {
                                    jQuery("#scheduledButton").css("display", "")
                                    jQuery("#scheduleInputs").css("display", "")
                                    jQuery("#invoiceButtons").css("display", "none")
                                } else {
                                    jQuery("#scheduleInputs").css("display", "none")
                                    jQuery("#scheduledButton").css("display", "none")
                                    jQuery("#invoiceButtons").css("display", "")
                                }
                            }
                        </script>
                    <?php };*/ ?>

                    <input type="reset" value="Reset">
                    <input type="button" onclick="create_invoice('prv')" value="Preview" />
                    <?php /*if ((isset($_GET['type']) && $_GET['type'] == 'general') or (isset($_GET['act']) and $_GET['act'] == 'scheduled')) { ?>
                        <span id="scheduledButton" style="display: none;">
                            <?php if (isset($_GET['act']) and $_GET['act'] == 'scheduled') { ?>
                                <input type="button" onclick="create_invoice('update_scheduled')" value="Update scheduled" />
                            <?php } else { ?>
                                <input type="button" onclick="create_invoice('save_scheduled')" value="Save" />
                            <?php }; ?>
                        </span>
                        <script>
                            switchScheduleInputs();
                        </script>
                    <?php }; */ ?>
                    <span id="invoiceButtons">
                        <?php if (isset($_GET['act']) and $_GET['act'] == 'draft') { ?>
                            <input type="button" onclick="create_invoice('update_draft')" value="Update draft" />
                        <?php } elseif (!isset($_GET['act'])) { ?>
                            <input type="button" onclick="create_invoice('save_draft')" value="Save draft" />
                        <?php } ?>
                        <?php if (!in_array('invoices_draft_only', $user_permissions) && (!isset($_GET['act']) or $_GET['act'] != 'scheduled')) { ?>
                            <input type="button" onclick="create_invoice('crt')" value="Create">
                        <?php }; ?>
                    </span>
                </td>
            </tr>
        </table>
        ver 1.1 20/05/2025
</form>

<script>
    function addBccEmail(val) {
        input = '<div style="margin-top:10px"><input type="text" name="bcc_email[]" value="' + val + '" style="width:90%"/> <i class="far fa-trash-alt" onclick="$(this).closest(\'div\').remove();"></i></div>';
        jQuery("#bcc_email").append(input)
    }

    function vatShifted(val) {
        if (val == '0')
            jQuery('#vatShifted').show();
        else
            jQuery('#vatShifted').hide();
    }

    function setInvoiceVatRate() {
        vatRate = jQuery("#vat_rate").val();
        jQuery("#vatRate").html('<select name="vat" onchange="vatShifted(this.value)"><option value="' + vatRate + '">' + vatRate + '</option><option value="0">0</option></select> % <span id="vatShifted" style="display:none">VAT Shifted</span>')
    }
    jQuery(document).ready(function(e) {
        setInvoiceVatRate();
        amount = 0;
        jQuery("#invoiceTbl .amount").each(function() {
            amount = amount + Number(this.value.replace(',', '.'));
        })
        //  jQuery("th#totalAmount").html(new Intl.NumberFormat('en-NL').format(amount));
        <?php if ((isset($_GET['act']) && $_GET['act'] == 'draft') or (isset($_GET['act']) && $_GET['act'] == 'scheduled') or (isset($_GET['type']) && $_GET['type'] == 'credit_note')) { ?> jQuery("#invoiceItems .invoiceItem").prop("checked", "checked");
        <?php }; ?>

        <?php if (isset($invoice) and isset($invoice['sbsid'])) { ?>
            jQuery('#sbsid option')
                .removeAttr('selected')
                .filter('[value=<?php echo $invoice['sbsid']; ?>]')
                .prop('selected', true).val('<?php echo $invoice['sbsid']; ?>');
        <?php }; ?>

        var defBCC = "<?php echo (isset($invoice_template['email_bcc_address'])) ? $invoice_template['email_bcc_address'] : 'info@iidc.eu'; ?>";

        function changeBCC() {
            jQuery("input.bcc_email").first().val(defBCC);
        }
        jQuery("#sbsid").on("change", function(e) {
            changeBCC();
        });

        changeBCC();
        loadServiceItems();
    });
</script>
<?php if ($_GET['type'] == 'batch' or $_GET['type'] == 'hsa') { ?>
    <script>
        jQuery("i.status").click(function(e) {
            status = jQuery(this).data('status')
            var obj = jQuery(this).parents('tr')
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
    </script>
<?php }; ?>