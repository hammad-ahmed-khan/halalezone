<script>
     $("#page_title").html("Default service costs")
</script>
<style>
     a:before {
          content: none !important;
     }
</style>
<?php

$office = array();
if ($offices = $amdb->get_results("SELECT offid,office_name,reference_prefix,certificate_prefix FROM offices")) {
     foreach ($offices as $off) {
          $office[$off['offid']] = $off['reference_prefix'];
          $offids[$off['offid']] = $off['reference_prefix'] . $off['certificate_prefix'];
     }
}
$batch = array();
$product = array();
$annual = array();
$supervision = array();

$default = json_decode(get_option('default_prices'), true);

if (isset($default['batch']) and is_array($default['batch']))
     $batch = $default['batch'];

if (isset($default['annual']) and is_array($default['annual']))
     $annual = $default['annual'];

if (isset($default['product']) and is_array($default['product']))
     $product = $default['product'];

if (isset($default['supervision']) and is_array($default['supervision']))
     $supervision = $default['supervision'];

$clients = $amdb->get_results("SELECT * FROM companies
                                     JOIN users ON companies.clid = users.clid
                                     JOIN companies_prices ON  companies.clid = companies_prices.clid
                                     WHERE companies.clof='0' and users.active='y'
                                     GROUP BY companies_prices.clid
                                     ORDER BY companies.company_name ASC");

?>
<style>
     td.companyName {
          white-space: normal !important
     }

     #servicePrices td,
     #servicePrices th {
          white-space: nowrap;
     }

     #servicePrices b {
          display: inline-block;
          width: 85px
     }
</style>
<script>
     function reorderPrices() {
          jQuery('.auNr').each(function(index, element) {
               jQuery(this).html(index + 1)
          });
     }

     function doSearch(obj) {
          srchWht = jQuery(obj).val().toLowerCase();
          srch = "." + jQuery(obj).attr('id');
          if (srchWht.length > 2) {
               jQuery("#servicePrices").width(jQuery("#servicePrices").width())
               jQuery("#companiesPrices tr").css('display', 'none');
               jQuery(srch).each(function(index, element) {
                    if (jQuery(this).text().toLowerCase().indexOf("" + srchWht + "") != -1) {
                         jQuery(this).parent('tr').css({
                              "display": "table-row"
                         });
                    }
               });
          } else {
               jQuery("#servicePrices tbody tr").css("display", "table-row");
          }
     }
</script>
<div style="float:right">
     <a href="<?php echo $prog_www ?>/invoices/service_prices_save.php?clid=-1&act=get_defaults" class="load_popup button" title="Add new client">Add new client</a>
</div>
<h2>DEFAULT SERVICE COSTS</h2>
<table id="servicePrices" class="table table-striped table-bordered" style="width:100%">
     <thead>
          <tr>
               <th>Nr</th>
               <th>ID <input type="text" style="width: 100px; padding: 2px 5px;" id="clientID" onkeyup="doSearch(this)" /></th>
               <th>Company <input type="text" style="width: auto; padding: 2px 5px;" id="companyName" onkeyup="doSearch(this)" /></th>
               <th colspan="2">Batch</th>
               <th>Annual</th>
               <th>New product</th>
               <th>Supervision costs</th>
               <th>Charges per KM</th>
               <th>Action</th>
          </tr>
     </thead>
     <!--default prices-->
     <tbody id="companiesPrices">
          <tr>
               <td colspan="2" bgcolor="#eeeeee"></td>
               <td bgcolor="#eeeeee">
                    <font color="#FF0000"><b>DEFAULT PRICES</b></font>
               </td>
               <td bgcolor="#eeeeee">
                    <font color="#FF0000"><b>Minimum:</b>&euro;<?php echo  do_currency($batch['minimum_amount']); ?><br />
                         <b>Admin:</b>&euro;<?php echo do_currency($batch['admin_costs']); ?>
                    </font>
               </td>
               <td>
                    <font color="#FF0000">
                         <b>&lt;10.000kg:</b>&euro;<?php echo  do_currency($batch['price1'], 3); ?>
                         <br /><b>&gt;10.001kg:</b>&euro;<?php echo  do_currency($batch['price2'], 3); ?>
               </td>
               <td>&euro;<?php echo do_currency($annual['cost']); ?></td>
               <td>&euro;<?php echo do_currency($product['cost']); ?></td>
               <td style="color:red">&euro;<?php echo isset($supervision['costs']) ? do_currency($supervision['costs']) : ''; ?></td>
               <td style="color:red"><?php echo isset($supervision['perKM']) ? $supervision['perKM'] : ''; ?> (Euro cents)</td>
               <td bgcolor="#eeeeee" align="center"><a data-url="<?php echo $prog_www ?>/invoices/service_prices_save.php?clid=0&act=get_defaults" class="load_popup" title="Default prices"><img title='Edit Prices' src="../images/edit.gif" border=0></a></td>
          </tr>
          <!-- companies prices-->
          <pre>
<?php
$nr = 1;

if (count($clients) > 0) {
     foreach ($clients as $client) {

          $batch = array();
          $product = array();
          $annual = array();
          $supervision = array();

          if (trim($client['prices']) != '' and is_array(json_decode($client['prices'], true))) {
               $prices = json_decode($client['prices'], true);
               if (isset($prices['batch']) and is_array($prices['batch']))
                    $batch = $prices['batch'];

               if (isset($prices['annual']) and is_array($prices['annual']))
                    $annual = $prices['annual'];

               if (isset($prices['product']) and is_array($prices['product']))
                    $product = $prices['product'];

               if (isset($prices['supervision']) and is_array($prices['supervision']))
                    $supervision = $prices['supervision'];
          }
?>
<tr>
<th class="auNr"><?php echo $nr++; ?></th>
<th class="clientID"><?php echo $offids[$client['offid']]; ?><?php echo str_pad($client['clid'], 6, '0', STR_PAD_LEFT); ?></th>
<td class="companyName"><?php echo  $client['company_name']; ?></td>
<td>
     <?php if (isset($batch['type']) && $batch['type'] == 'custom') { ?>
     <?php echo (isset($batch['minimum_amount']) && trim($batch['minimum_amount']) != '' && is_numeric($batch['minimum_amount'])) ? '<b>Minimum:</b> &euro;' . do_currency($batch['minimum_amount']) : ''; ?>
     <?php echo (isset($batch['admin_costs']) && trim($batch['admin_costs']) != '' && is_numeric($batch['admin_costs'])) ? '<br/><b>Admin:</b> &euro;' . do_currency($batch['admin_costs']) : ''; ?>
    <?php } else { ?>Default<?php }; ?>
    </td>
     <td>
     <?php if (isset($batch['type']) && $batch['type'] == 'custom') { ?>
     <?php echo (isset($batch['price1']) && trim($batch['price1']) != '' && is_numeric($batch['price1'])) ? '<b>&lt;10.000kg:</b> &euro;' . do_currency($batch['price1'], 3) : ''; ?>
    <?php echo (isset($batch['price2']) && trim($batch['price2']) != '' && is_numeric($batch['price2'])) ? '<br/><b>&gt;10.001kg:</b> &euro;' . do_currency($batch['price2'], 3) : ''; ?>
    <?php } else { ?>Default<?php }; ?>
    </td>
<td>
     <?php if (isset($annual['type']) && $annual['type'] == 'custom') { ?>
     <?php echo (isset($annual['cost']) && trim($annual['cost']) != '') ? '&euro;' . do_currency($annual['cost']) : ''; ?>
    <?php } else { ?>Default<?php }; ?>
    </td>
<td>
     <?php if (isset($product['type']) && $product['type'] == 'custom') { ?>
     <?php echo (isset($product['cost']) && trim($product['cost']) != '') ? '&euro;' . do_currency($product['cost']) : ''; ?>
    <?php } else { ?>Default<?php }; ?>
    </td>
        <td>
             <?php if (isset($supervision['type']) && $supervision['type'] == 'custom') { ?>
     <?php echo (isset($supervision['costs']) && trim($supervision['costs']) != '') ? '&euro;' . do_currency($supervision['costs']) : ''; ?>
    <?php } else { ?>Default<?php }; ?>
        </td>
    <td>     <?php if (isset($supervision['type']) && $supervision['type'] == 'custom') { ?>
     <?php echo (isset($supervision['perKM']) && trim($supervision['perKM']) != '') ? $supervision['perKM'] . ' <span class="info">(Euro cents)</span>' : ''; ?>
    <?php } else { ?>Default<?php }; ?></td>
<td align="center"><a data-url="<?php echo $prog_www ?>/invoices/service_prices_save.php?clid=<?php echo $client['clid']; ?>&act=get_defaults" class="load_popup" title="Default prices"><img  title='Edit Prices' src="../images/edit.gif" border=0></a>
<img title="Delete" src="../images/delete.gif" border="0" class="post_this_link" data-confirm="Are you sure" data-url="<?php echo $prog_www ?>/invoices/service_prices_save.php?clid=<?php echo $client['clid']; ?>&act=delete" /></td>
</tr>
<?php
     }
}
?>
</tbody>
</table>