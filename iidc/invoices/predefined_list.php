<?php
if (!isset($_GET['clid']))
    exit();
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$nr = 1;
$clid = isset($_GET['clid']) ? intval($_GET['clid']) : 0;

if (!$predefined_prices = $amdb->get_results("SELECT * FROM hqc_predefined_prices WHERE status != 'deleted' ORDER BY invoice_type, service_type, item_code")) {
    $predefined_prices = array();
}

$invoice_types = array(
    'general' => 'General invoices', 
    'annual' => 'Annual invoices', 
    'shipment' => 'Shipment invoices', 
    'shipment_sab' => 'Shipment invoices for SA', 
    'hfc' => 'Halal Facility Certificate for SA', 
    'audit' => 'Audit invoices', 
    'supervision' => 'Supervision invoices'
);

$title = 'Default Prices';
$subtitle = 'Applies to all companies without custom pricing';
$isCustom = false;
$selectedCompany = array();

if (isset($_GET['clid']) && $_GET['clid'] != '0') {
    if ($selectedCompany = $amdb->get_row("SELECT companies.clid as clid, companies.company_name, hqc_companies_prices.prices FROM hqc_companies_prices RIGHT JOIN companies ON hqc_companies_prices.clid = companies.clid WHERE companies.clid = '$_GET[clid]'")) {
        $title = htmlspecialchars($selectedCompany['company_name']);
        $subtitle = 'Custom pricing for this company';
        $isCustom = true;
    }
}

if (isset($selectedCompany['prices']) && is_array((json_decode($selectedCompany['prices'], true)))) {
    $selectedCompanyPrices = json_decode($selectedCompany['prices'], true);
} else {
    $selectedCompanyPrices = array();
}

$totalItems = count($predefined_prices);
$customizedItems = count($selectedCompanyPrices);
?>

<style>
/* Prices List Specific Styles */
.prices-list-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(135deg, <?php echo $isCustom ? '#f0fdf4 0%, #dcfce7' : '#fefce8 0%, #fef3c7'; ?> 100%);
    border-bottom: 1px solid <?php echo $isCustom ? '#bbf7d0' : '#fde68a'; ?>;
}

.prices-list-header .header-icon {
    width: 48px;
    height: 48px;
    background: <?php echo $isCustom ? 'linear-gradient(135deg, #16a34a 0%, #22c55e 100%)' : 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)'; ?>;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 20px;
    flex-shrink: 0;
}

.prices-list-header .header-info {
    flex: 1;
}

.prices-list-header .header-info h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 700;
    color: <?php echo $isCustom ? '#166534' : '#92400e'; ?>;
    display: flex;
    align-items: center;
    gap: 10px;
}

.prices-list-header .header-info p {
    margin: 0;
    font-size: 13px;
    color: <?php echo $isCustom ? '#16a34a' : '#b45309'; ?>;
}

.prices-list-header .header-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: <?php echo $isCustom ? '#dcfce7' : '#fef3c7'; ?>;
    color: <?php echo $isCustom ? '#166534' : '#92400e'; ?>;
    border: 1px solid <?php echo $isCustom ? '#bbf7d0' : '#fde68a'; ?>;
}

.prices-list-header .header-stats {
    display: flex;
    gap: 12px;
}

.prices-list-header .stat-box {
    text-align: center;
    padding: 8px 16px;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid <?php echo $isCustom ? '#bbf7d0' : '#fde68a'; ?>;
}

.prices-list-header .stat-box .stat-value {
    font-size: 20px;
    font-weight: 700;
    color: <?php echo $isCustom ? '#166534' : '#92400e'; ?>;
    line-height: 1;
}

.prices-list-header .stat-box .stat-label {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.prices-list-header .back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;
    color: <?php echo $isCustom ? '#166534' : '#92400e'; ?>;
    background: #ffffff;
    border: 1px solid <?php echo $isCustom ? '#86efac' : '#fcd34d'; ?>;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.prices-list-header .back-btn:hover {
    background: <?php echo $isCustom ? '#f0fdf4' : '#fffbeb'; ?>;
}

/* Filter Bar */
.prices-filter-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.prices-filter-bar .filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.prices-filter-bar select {
    padding: 8px 32px 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center;
    background-size: 16px;
    cursor: pointer;
    appearance: none;
    transition: all 0.25s ease;
}

.prices-filter-bar select:hover {
    border-color: #f59e0b;
}

.prices-filter-bar select:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
}

.prices-filter-bar .filter-info {
    margin-left: auto;
    font-size: 12px;
    color: #64748b;
}

.prices-filter-bar .filter-info strong {
    color: #1e293b;
}

/* Table Styles */
#predefined_prices {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

#predefined_prices thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #475569;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 12px;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#predefined_prices thead th:first-child {
    text-align: center;
    width: 50px;
}

#predefined_prices thead th select {
    font-size: 11px;
    padding: 6px 24px 6px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 4px center;
    background-size: 14px;
    cursor: pointer;
    appearance: none;
    text-transform: none;
    letter-spacing: normal;
    font-weight: 500;
}

#predefined_prices tbody tr {
    transition: background 0.2s ease;
}

#predefined_prices tbody tr:hover {
    background: #fffbeb;
}

#predefined_prices tbody tr.green {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

#predefined_prices tbody tr.green:hover {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
}

#predefined_prices tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
    font-size: 13px;
    color: #374151;
}

#predefined_prices tbody th.srNr {
    text-align: center;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 16px;
}

/* Form Elements */
#predefined_prices tbody select,
#predefined_prices tfoot select {
    width: 100%;
    padding: 8px 28px 8px 10px;
    font-size: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 6px center;
    background-size: 14px;
    cursor: pointer;
    appearance: none;
    transition: all 0.25s ease;
}

#predefined_prices tbody input[type="text"],
#predefined_prices tfoot input[type="text"] {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.25s ease;
    font-family: inherit;
}

#predefined_prices tbody textarea,
#predefined_prices tfoot textarea {
    width: 100%;
    min-height: 60px;
    padding: 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    resize: vertical;
    font-family: inherit;
    transition: all 0.25s ease;
}

#predefined_prices tbody input:focus,
#predefined_prices tbody select:focus,
#predefined_prices tbody textarea:focus,
#predefined_prices tfoot input:focus,
#predefined_prices tfoot select:focus,
#predefined_prices tfoot textarea:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
}

#predefined_prices .green input,
#predefined_prices .green select,
#predefined_prices .green textarea {
    border-color: #86efac;
    background-color: #ffffff;
}

#predefined_prices .green input:focus,
#predefined_prices .green select:focus,
#predefined_prices .green textarea:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

/* Column Widths */
#predefined_prices .service_type input {
    min-width: 140px;
}

#predefined_prices .item_code input {
    width: 80px;
    min-width: 80px;
}

#predefined_prices .description textarea {
    min-width: 200px;
}

/* Prices Column */
#predefined_prices .prices {
    white-space: nowrap;
    min-width: 220px;
}

#predefined_prices .prices ul {
    padding: 0;
    margin: 0;
    list-style: none;
}

#predefined_prices .prices ul li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px dashed #e2e8f0;
}

#predefined_prices .prices ul li:last-child {
    border-bottom: none;
}

#predefined_prices .prices ul li b {
    min-width: 100px;
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
}

#predefined_prices .prices ul li input.amount {
    width: 90px;
    text-align: right;
    font-family: 'Monaco', 'Consolas', monospace;
}

#predefined_prices .prices .price-simple {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

#predefined_prices .prices .price-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

#predefined_prices .prices .price-row label {
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
    min-width: 45px;
}

#predefined_prices .prices .price-row input {
    width: 100px;
    text-align: right;
}

#predefined_prices .prices textarea.extra_costs {
    min-height: 40px;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 11px;
}

/* Action Column */
#predefined_prices .action {
    text-align: center;
    width: 60px;
}

#predefined_prices .action i {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px !important;
    color: #dc2626;
    background: #fef2f2;
}

#predefined_prices .action i:hover {
    background: #fee2e2;
    color: #b91c1c;
}

/* New Item Row */
#predefined_prices tfoot tr#newPriceItem {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

#predefined_prices tfoot tr#newPriceItem td,
#predefined_prices tfoot tr#newPriceItem th {
    padding: 16px;
    border-top: 2px solid #86efac;
}

#predefined_prices tfoot tr#newPriceItem input,
#predefined_prices tfoot tr#newPriceItem select,
#predefined_prices tfoot tr#newPriceItem textarea {
    border-color: #86efac;
}

#predefined_prices tfoot tr#newPriceItem input:focus,
#predefined_prices tfoot tr#newPriceItem select:focus,
#predefined_prices tfoot tr#newPriceItem textarea:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

/* Fixed Footer */
.prices-fixed-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
}

.prices-fixed-footer .footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.prices-fixed-footer .footer-info {
    font-size: 13px;
    color: #64748b;
}

.prices-fixed-footer .footer-info strong {
    color: #1e293b;
}

.prices-fixed-footer .footer-actions {
    display: flex;
    gap: 12px;
}

.btn-footer {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-footer.reset {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-footer.reset:hover {
    background: #f1f5f9;
    color: #475569;
    border-color: #cbd5e1;
}

.btn-footer.add-new {
    background: #ffffff;
    color: #16a34a;
    border: 2px solid #86efac;
}

.btn-footer.add-new:hover {
    background: #f0fdf4;
    border-color: #22c55e;
}

.btn-footer.save {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: #ffffff;
}

.btn-footer.save:hover {
    background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

/* Empty State */
.prices-empty-state {
    text-align: center;
    padding: 60px 24px;
}

.prices-empty-state .empty-icon {
    width: 80px;
    height: 80px;
    background: #fef3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.prices-empty-state .empty-icon i {
    font-size: 36px;
    color: #f59e0b;
}

.prices-empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
}

.prices-empty-state p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Responsive */
@media (max-width: 1024px) {
    .prices-list-header {
        flex-wrap: wrap;
    }
    
    .prices-list-header .header-stats {
        width: 100%;
        justify-content: flex-start;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed <?php echo $isCustom ? '#bbf7d0' : '#fde68a'; ?>;
    }
    
    #predefined_prices {
        display: block;
        overflow-x: auto;
    }
}

@media (max-width: 768px) {
    .prices-list-header {
        padding: 16px 20px;
    }
    
    .prices-filter-bar {
        flex-wrap: wrap;
        padding: 12px 16px;
    }
    
    .prices-filter-bar .filter-info {
        width: 100%;
        margin-left: 0;
        margin-top: 8px;
    }
    
    .prices-fixed-footer .footer-content {
        flex-direction: column;
        text-align: center;
    }
    
    .prices-fixed-footer .footer-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-footer {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- List Header -->
<div class="prices-list-header">
    <div class="header-icon">
        <i class="fas <?php echo $isCustom ? 'fa-building' : 'fa-tags'; ?>"></i>
    </div>
    
    <div class="header-info">
        <h3>
            <?php echo $title; ?>
            <span class="header-badge">
                <i class="fas <?php echo $isCustom ? 'fa-user-edit' : 'fa-globe'; ?>"></i>
                <?php echo $isCustom ? 'Custom' : 'Default'; ?>
            </span>
        </h3>
        <p><?php echo $subtitle; ?></p>
    </div>
    
    <div class="header-stats">
        <div class="stat-box">
            <div class="stat-value"><?php echo $totalItems; ?></div>
            <div class="stat-label">Total Items</div>
        </div>
        <?php if ($isCustom && $customizedItems > 0): ?>
        <div class="stat-box">
            <div class="stat-value"><?php echo $customizedItems; ?></div>
            <div class="stat-label">Customized</div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($isCustom): ?>
    <button class="back-btn" onclick="getClientPrices(0)">
        <i class="fas fa-arrow-left"></i>
        Back to Default
    </button>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<div class="prices-filter-bar">
    <span class="filter-label">Filter by Type:</span>
    <select onchange="getInvoiceType(this.value)">
        <option value="">All Invoice Types</option>
        <?php foreach ($invoice_types as $key => $value): ?>
            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
        <?php endforeach; ?>
    </select>
    <span class="filter-info">
        Showing <strong id="visibleCount"><?php echo $totalItems; ?></strong> of <strong><?php echo $totalItems; ?></strong> items
    </span>
</div>

<!-- Prices Table -->
<form action="predefined_prices_save.php" method="post" onsubmit="return post_this_form(this);" target="" id="pricesForm">
    <input type="hidden" name="act" value="update" />
    <input type="hidden" name="clid" value="<?php echo $clid; ?>" />
    
    <?php if (count($predefined_prices) > 0): ?>
    <table id="predefined_prices">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 160px;">Invoice Type</th>
                <th style="width: 160px;">Service Type</th>
                <th style="width: 100px;">Item Code</th>
                <th>Description</th>
                <th style="width: 240px;">Pricing</th>
                <th style="width: 60px;" class="action">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($predefined_prices as $default):
                if (isset($selectedCompanyPrices[$default['preid']])) {
                    $client_custom = $selectedCompanyPrices[$default['preid']];
                    $class = ' green';
                } else {
                    $client_custom = array();
                    $class = '';
                }
                $invoiceTypeClass = trim($default['invoice_type']) != '' ? trim($default['invoice_type']) : 'general';
            ?>
                <tr data-preid="<?php echo $default['preid']; ?>" class="<?php echo $invoiceTypeClass . $class; ?>">
                    <th class="srNr"><?php echo $nr++; ?></th>
                    <td>
                        <select name="predefined[<?php echo $default['preid']; ?>][invoice_type]">
                            <?php foreach ($invoice_types as $key => $value):
                                $selected = ($key == $default['invoice_type']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="service_type">
                        <input type="text" name="predefined[<?php echo $default['preid']; ?>][service_type]" value="<?php echo htmlspecialchars(@$default['service_type']); ?>" placeholder="Service type" />
                    </td>
                    <td class="item_code">
                        <input type="text" name="predefined[<?php echo $default['preid']; ?>][item_code]" value="<?php echo htmlspecialchars($default['item_code']); ?>" placeholder="Code" />
                    </td>
                    <td class="description">
                        <textarea name="predefined[<?php echo $default['preid']; ?>][description]" placeholder="Description"><?php echo htmlspecialchars($default['description']); ?></textarea>
                    </td>
                    <td class="prices">
                        <?php if (trim($default['extra_costs']) != '' && is_array(json_decode($default['extra_costs'], true))):
                            $extra_costs = json_decode($default['extra_costs'], true);
                            if (isset($client_custom['extra_costs']) && is_array($client_custom['extra_costs']))
                                $extra_costs = $client_custom['extra_costs'];
                        ?>
                            <ul>
                                <li>
                                    <b>Minimum:</b>
                                    <span>&euro;</span>
                                    <input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][minimum_amount]" value="<?php echo isset($extra_costs['minimum_amount']) ? do_currency($extra_costs['minimum_amount']) : '0,00'; ?>" />
                                </li>
                                <li>
                                    <b>Administration:</b>
                                    <span>&euro;</span>
                                    <input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][admin_costs]" value="<?php echo do_currency($extra_costs['admin_costs']); ?>" />
                                </li>
                                <li>
                                    <b>&lt;10.000kg:</b>
                                    <span>&euro;</span>
                                    <input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][price1]" value="<?php echo do_currency($extra_costs['price1'], 3); ?>" />
                                </li>
                                <li>
                                    <b>&gt;10.001kg:</b>
                                    <span>&euro;</span>
                                    <input type="text" class="amount" name="predefined[<?php echo $default['preid']; ?>][extra_costs][price2]" value="<?php echo do_currency($extra_costs['price2'], 3); ?>" />
                                </li>
                            </ul>
                        <?php else:
                            if (isset($client_custom['price']) && !is_array($client_custom['price']))
                                $default['price'] = $client_custom['price'];
                            if (isset($client_custom['extra_costs']) && !is_array($client_custom['extra_costs']))
                                $default['extra_costs'] = $client_custom['extra_costs'];
                        ?>
                            <div class="price-simple">
                                <div class="price-row">
                                    <label>Price:</label>
                                    <input type="text" name="predefined[<?php echo $default['preid']; ?>][price]" value="<?php echo htmlspecialchars($default['price']); ?>" placeholder="0.00" />
                                </div>
                                <div class="price-row">
                                    <label>Math:</label>
                                    <textarea class="extra_costs" name="predefined[<?php echo $default['preid']; ?>][extra_costs]" placeholder="Formula (optional)"><?php echo htmlspecialchars($default['extra_costs']); ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="action">
                        <i class="fas fa-trash-alt" onclick="deleteDefault(this)" title="Delete this item"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr id="newPriceItem" style="display: none;">
                <th></th>
                <td>
                    <select name="newPriceItem[invoice_type]">
                        <?php foreach ($invoice_types as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="service_type">
                    <input type="text" name="newPriceItem[service_type]" placeholder="Service type" />
                </td>
                <td class="item_code">
                    <input type="text" name="newPriceItem[item_code]" placeholder="Code" />
                </td>
                <td class="description">
                    <textarea name="newPriceItem[description]" placeholder="Description"></textarea>
                </td>
                <td class="prices">
                    <div class="price-simple">
                        <div class="price-row">
                            <label>Price:</label>
                            <input type="text" name="newPriceItem[price]" placeholder="0.00" />
                        </div>
                        <div class="price-row">
                            <label>Math:</label>
                            <textarea class="extra_costs" name="newPriceItem[extra_costs]" placeholder="Formula (optional)"></textarea>
                        </div>
                    </div>
                </td>
                <td class="action"></td>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
    <div class="prices-empty-state">
        <div class="empty-icon">
            <i class="fas fa-tags"></i>
        </div>
        <h3>No Price Items Found</h3>
        <p>There are no predefined price items yet. Click "Add New Item" to create one.</p>
    </div>
    <?php endif; ?>
</form>

<!-- Fixed Footer -->
<div class="prices-fixed-footer">
    <div class="footer-content">
        <div class="footer-info">
            <?php if ($isCustom): ?>
                Editing prices for <strong><?php echo htmlspecialchars($selectedCompany['company_name']); ?></strong>
                <?php if ($customizedItems > 0): ?>
                    &mdash; <strong><?php echo $customizedItems; ?></strong> customized item(s)
                <?php endif; ?>
            <?php else: ?>
                Editing <strong>default prices</strong> for all companies
            <?php endif; ?>
        </div>
        <div class="footer-actions">
            <button type="button" class="btn-footer reset" onclick="resetPrices()">
                <i class="fas fa-undo"></i>
                Reset Changes
            </button>
            <?php if ($_GET['clid'] == '0'): ?>
                <button type="button" class="btn-footer add-new" id="addNewPriceItem" onclick="addNewPriceItem()">
                    <i class="fas fa-plus"></i>
                    Add New Item
                </button>
            <?php endif; ?>
            <button type="submit" class="btn-footer save" form="pricesForm">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function() {
    // Update URL without reload
    if (window.history.pushState) {
        window.history.pushState('', '', '?inc=predefined_prices');
    }
    
    // Adjust companies list height
    jQuery("#companiesList").css("height", jQuery("#predefined_prices").height() + "px");
    
    // Auto-resize textareas
    setTextareaHeight();
    
    // Update visible count when filtering
    updateVisibleCount();
});

function updateVisibleCount() {
    var visible = jQuery("#predefined_prices tbody tr:visible").length;
    jQuery("#visibleCount").text(visible);
}

// Override getInvoiceType to update count
var originalGetInvoiceType = window.getInvoiceType;
window.getInvoiceType = function(val) {
    if (val == '') {
        jQuery("#predefined_prices tbody tr").css("display", "table-row");
    } else {
        jQuery("#predefined_prices tbody tr").css("display", "none");
        jQuery("#predefined_prices tbody tr." + val).css("display", "table-row");
    }
    updateVisibleCount();
};
</script>