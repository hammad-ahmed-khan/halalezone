<?php
if (!isset($_POST['act']) or !isset($_POST['predefined']))
    exit();
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$predefined = $_POST['predefined'];
$company_prices = array();

foreach ($predefined as $preid => $predefined_prices) {
    if ($_POST['clid'] == '0') {
        if (isset($predefined_prices['extra_costs']) && is_array($predefined_prices['extra_costs']))
            $predefined_prices['extra_costs'] = json_encode($predefined_prices['extra_costs']);
        $amdb->update('hqc_predefined_prices', $predefined_prices, 'preid = ' . $preid);
    } else {
        if ($predefined_prices['price'] != '0.00') {
            $company_prices[$preid]['price'] = $predefined_prices['price'];
            $company_prices[$preid]['extra_costs'] = $predefined_prices['extra_costs'];
        }
    }
}
if ($_POST['clid'] == '0') {
    if ($_POST['act'] == 'insert_default_prices') {
        $newPriceItem = $_POST['newPriceItem'];
        if (trim($newPriceItem['service_type']) == '' or trim($newPriceItem['item_code']) == '' or trim($newPriceItem['description']) == '' or trim($newPriceItem['price']) == '') {
            $amdb->post_results('Service type, item code description, price are required');
            exit();
        }
        if (isset($newPriceItem['extra_costs']) && is_array($newPriceItem['extra_costs']))
            $newPriceItem['extra_costs'] = json_encode($newPriceItem['extra_costs']);
        $amdb->insert('hqc_predefined_prices', $newPriceItem);
    }
} else {
    $company_prices = json_encode($company_prices);
    if ($clientPrices = $amdb->get_row("SELECT prices FROM hqc_companies_prices WHERE clid = '$_POST[clid]'")) {
        $amdb->update('hqc_companies_prices', array('prices' => $company_prices), 'clid = ' . $clid);
    } else {
        $amdb->insert('hqc_companies_prices', array('clid' => $_POST['clid'], 'prices' => $company_prices));
    }
}

//if act == 'delete_default_prices' to delete predefined prices by clid or preid
if ($_REQUEST['act'] == 'delete_default_prices') {
    if ($_REQUEST['clid'] == '0') {
        $amdb->update('hqc_predefined_prices', array('status' => 'deleted'), 'preid = ' . $_REQUEST['preid']);
    } else {
        $clid = $_REQUEST['clid'];
        $amdb->update('hqc_companies_prices', array('status' => 'deleted'), 'clid = ' . $clid);
    }
}

$amdb->post_results("resetPrices();", "function");
