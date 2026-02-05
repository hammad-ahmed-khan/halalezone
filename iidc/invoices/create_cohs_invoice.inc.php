<script>
    $("#page_title").html("Create batch certificate invoice")
</script>
<style>
    /* Create Batch Certificate Invoice Header */
.batch-invoice-header {
    background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
    border-radius: 12px;
    border: 1px solid #fde68a;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.batch-invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.batch-invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.batch-invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.batch-invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.batch-invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Batch Badge */
.batch-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fef3c7;
    color: #92400e;
}

.batch-badge i {
    font-size: 10px;
}

/* On-Hold Badge */
.onhold-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.onhold-badge i {
    font-size: 10px;
}

/* Quick Stats */
.batch-quick-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.batch-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #ffffff;
    border: 1px solid #fde68a;
    border-radius: 10px;
    min-width: 100px;
}

.batch-stat-item .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #d97706;
    line-height: 1;
}

.batch-stat-item .stat-label {
    font-size: 11px;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Header Actions */
.batch-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-batch-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-batch-action.back {
    background: #ffffff;
    color: #d97706;
    border: 2px solid #fde68a;
}

.btn-batch-action.back:hover {
    background: #fffbeb;
    border-color: #fcd34d;
    color: #b45309;
    text-decoration: none;
}

.btn-batch-action.onhold {
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
}

.btn-batch-action.onhold:hover {
    background: #fee2e2;
    border-color: #fca5a5;
}

.btn-batch-action.primary {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    color: #ffffff;
    border: none;
}

.btn-batch-action.primary:hover {
    background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
    color: #ffffff;
    text-decoration: none;
}

/* Info Bar */
.batch-info-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 32px;
    background: #fffbeb;
    border-top: 1px solid #fde68a;
    flex-wrap: wrap;
}

.batch-info-bar .info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #92400e;
}

.batch-info-bar .info-item i {
    color: #f59e0b;
}

.batch-info-bar .info-item strong {
    color: #78350f;
}

/* Company Card in List */
.batch-company-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.25s ease;
}

.batch-company-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #fde68a;
}

.batch-company-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-bottom: 1px solid #fde68a;
    flex-wrap: wrap;
}

.batch-company-card-header .company-icon {
    width: 44px;
    height: 44px;
    background: #ffffff;
    border: 2px solid #fde68a;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d97706;
    font-size: 18px;
}

.batch-company-card-header .company-info {
    flex: 1;
    min-width: 200px;
}

.batch-company-card-header .company-info .company-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.batch-company-card-header .company-info .company-meta {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 12px;
}

.batch-company-card-header .company-info .company-meta .office-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    background: #dcfce7;
    color: #166534;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.batch-company-card-header .company-stats {
    display: flex;
    gap: 16px;
    align-items: center;
}

.batch-company-card-header .stat-box {
    text-align: center;
    padding: 8px 16px;
    background: #ffffff;
    border: 1px solid #fde68a;
    border-radius: 8px;
}

.batch-company-card-header .stat-box .stat-value {
    font-size: 16px;
    font-weight: 700;
    color: #d97706;
}

.batch-company-card-header .stat-box .stat-label {
    font-size: 10px;
    color: #92400e;
    text-transform: uppercase;
}

.batch-company-card-header .company-actions {
    display: flex;
    gap: 8px;
}

.btn-invoice-company {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-invoice-company:hover {
    background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
    transform: translateY(-1px);
}

.btn-hold-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #dc2626;
    background: #ffffff;
    border: 1px solid #fecaca;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-hold-all:hover {
    background: #fef2f2;
}

.btn-restore-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #16a34a;
    background: #ffffff;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-restore-all:hover {
    background: #f0fdf4;
}

/* Certificate List */
.batch-cert-list {
    padding: 0;
    margin: 0;
    list-style: none;
}

.batch-cert-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.batch-cert-list li:last-child {
    border-bottom: none;
}

.batch-cert-list li:hover {
    background: #fffbeb;
}

.batch-cert-list .cert-number {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    min-width: 30px;
}

.batch-cert-list .cert-link {
    font-size: 14px;
    font-weight: 500;
    color: #d97706;
    text-decoration: none;
}

.batch-cert-list .cert-link:hover {
    color: #b45309;
    text-decoration: underline;
}

.batch-cert-list .cert-date {
    font-size: 13px;
    color: #64748b;
}

.batch-cert-list .cert-onhold-date {
    font-size: 11px;
    color: #dc2626;
    margin-left: auto;
    padding: 2px 8px;
    background: #fef2f2;
    border-radius: 4px;
}

.batch-cert-list .cert-action {
    margin-left: auto;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}

.batch-cert-list .cert-action.hold {
    color: #dc2626;
    background: transparent;
}

.batch-cert-list .cert-action.hold:hover {
    background: #fef2f2;
}

.batch-cert-list .cert-action.restore {
    color: #16a34a;
    background: transparent;
}

.batch-cert-list .cert-action.restore:hover {
    background: #f0fdf4;
}

/* On-Hold Notice */
.onhold-notice {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    margin-top: 20px;
    font-size: 14px;
    color: #991b1b;
}

.onhold-notice i {
    font-size: 20px;
    color: #dc2626;
}

/* Responsive */
@media (max-width: 768px) {
    .batch-invoice-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .batch-invoice-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .batch-quick-stats {
        justify-content: center;
    }
    
    .batch-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .batch-info-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 20px;
    }
    
    .batch-company-card-header {
        flex-direction: column;
        text-align: center;
    }
    
    .batch-company-card-header .company-stats {
        width: 100%;
        justify-content: center;
    }
    
    .batch-company-card-header .company-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>
<script>
    $("#page_title").html("Create Batch Certificate Invoice")
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
        $sql = "SELECT offid,office_name FROM offices WHERE JSON_VALID(options) = 1 AND JSON_EXTRACT(options,'$.invoicing_by') = '0' OR offid=0";
        if ($options = $amdb->get_results($sql)) {
            foreach ($options as $option) {
                $offids[$option['offid']] = $option['offid'];
                $offices[$option['offid']] = $option['office_name'];
            };
        }
    }
    $offids = implode(',', $offids);
    
    $invoices = array();
    $client_office = array();
    if (!isset($_GET['status']))
        $status = 'active';
    else
        $status = $_GET['status'];
    
    $tps = array('a', 'b');
    $totalCertificates = 0;
    $totalCompanies = 0;
    
    foreach ($tps as $tp) {
        if ($result = $amdb->get_results("SELECT certificates_{$tp}.nr,certificates_{$tp}.updated_on,certificates_{$tp}.weight_net,certificates_{$tp}.issue_date,certificates_{$tp}.url,certificates_{$tp}.certificate_nr,certificates_{$tp}.clid, certificates_{$tp}.status,companies.offid,companies.company_name FROM certificates_{$tp}
        JOIN companies ON certificates_{$tp}.clid = companies.clid where FIND_IN_SET(companies.offid,'$offids') AND certificates_{$tp}.done='y' and certificates_{$tp}.invoice_nr='0' and certificates_{$tp}.status = '$status'")) {
            foreach ($result as $row) {
                $row['tp'] = $tp;
                $invoices[$row['company_name']][$row['clid']][] = $row;
                if (isset($offices[$row['offid']]))
                    $client_office[$row['clid']] = $offices[$row['offid']];
                $totalCertificates++;
            }
        }
    }
    
    ksort($invoices);
    $totalCompanies = count($invoices);
    
    // Get default prices
    $defaultPrices = json_decode(get_option('default_prices'), true);
    if (isset($defaultPrices['batch']) and is_array($defaultPrices['batch']))
        $batchPrices = $defaultPrices['batch'];
    
    $isOnHold = isset($_GET['status']) && $_GET['status'] == 'onhold';
?>

<div class="batch-invoice-header">
    <div class="batch-invoice-header-content">
        <div class="batch-invoice-header-icon">
            <i class="fas <?php echo $isOnHold ? 'fa-pause-circle' : 'fa-boxes'; ?>"></i>
        </div>
        
        <div class="batch-invoice-header-info">
            <h2>
                <?php echo $isOnHold ? 'Certificates On Hold' : 'Batch Certificate Invoices'; ?>
                <?php if ($isOnHold) { ?>
                    <span class="onhold-badge">
                        <i class="fas fa-pause-circle"></i>
                        On Hold
                    </span>
                <?php } else { ?>
                    <span class="batch-badge">
                        <i class="fas fa-shipping-fast"></i>
                        Shipment
                    </span>
                <?php } ?>
            </h2>
            <p><?php echo $isOnHold ? 'Certificates temporarily excluded from invoicing' : 'Create invoices for batch/shipment certificates'; ?></p>
        </div>
        
        <div class="batch-quick-stats">
            <div class="batch-stat-item">
                <span class="stat-value"><?php echo $totalCompanies; ?></span>
                <span class="stat-label">Companies</span>
            </div>
            <div class="batch-stat-item">
                <span class="stat-value"><?php echo $totalCertificates; ?></span>
                <span class="stat-label">Certificates</span>
            </div>
        </div>
        
        <div class="batch-header-actions">
            <?php if ($isOnHold) { ?>
                <a href="index.php?inc=create_cohs_invoice" class="btn-batch-action back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Active
                </a>
            <?php } else { ?>
                <a href="index.php?inc=create_cohs_invoice&status=onhold" class="btn-batch-action onhold">
                    <i class="fas fa-pause-circle"></i>
                    View On Hold
                </a>
            <?php } ?>
            <a href="index.php?show=all" class="btn-batch-action back">
                <i class="fas fa-file-invoice-dollar"></i>
                All Invoices
            </a>
        </div>
    </div>
    
    <?php if (!$isOnHold) { ?>
    <div class="batch-info-bar">
        <div class="info-item">
            <i class="fas fa-info-circle"></i>
            <span>Showing certificates ready for invoicing</span>
        </div>
        <div class="info-item">
            <i class="fas fa-clock"></i>
            <span>Use <strong>"Put on hold"</strong> to temporarily exclude certificates</span>
        </div>
    </div>
    <?php } ?>
</div>

<?php if (count($invoices) == 0) { ?>
    <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
        <i class="fas <?php echo $isOnHold ? 'fa-check-circle' : 'fa-inbox'; ?>" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
        <h3 style="color: #64748b; font-weight: 600; margin: 0 0 8px 0;">
            <?php echo $isOnHold ? 'No Certificates On Hold' : 'No Pending Certificates'; ?>
        </h3>
        <p style="color: #94a3b8; margin: 0;">
            <?php echo $isOnHold ? 'All certificates are currently active and available for invoicing.' : 'All batch certificates have been invoiced.'; ?>
        </p>
    </div>
<?php } else { ?>

<div class="certificatesToInvoice" style="max-width: 1200px; margin: 0 auto;">
    <?php
    $nr = 1;
    foreach ($invoices as $company => $value) {
        $clid = key($value);
        $comPrices = $defaultPrices;
        if ($comPrices = $amdb->get_row("SELECT * FROM companies_prices where clid = '$clid'")) {
            if (trim($comPrices['prices']) != '' and is_array(json_decode($comPrices['prices'], true))) {
                $comPrices = json_decode($comPrices['prices'], true);
            }
        }
        
        // Calculate totals
        $comTotal = 0;
        $row = array();
        if (isset($comPrices['batch']))
            $row = $comPrices['batch'];
        $minimum_amount = (isset($row['minimum_amount']) and trim($row['minimum_amount']) != '') ? $row['minimum_amount'] : $batchPrices['minimum_amount'];
        $admin_costs = (isset($row['admin_costs']) and trim($row['admin_costs']) != '') ? $row['admin_costs'] : $batchPrices['admin_costs'];
        $price1 = (isset($row['price1']) and trim($row['price1']) != '') ? $row['price1'] : $batchPrices['price1'];
        $price2 = (isset($row['price2']) and trim($row['price2']) != '') ? $row['price2'] : $batchPrices['price2'];
        $total_invoice_amount = 0;
        $certCount = 0;
        
        foreach ($invoices[$company] as $comItem) {
            foreach ($comItem as $total) {
                $certCount++;
                if (strstr($total['weight_net'], '.')) {
                    if ((explode('.', $total['weight_net'])[1]) / 1000 >= 1)
                        $total['weight_net'] = str_replace('.', '', $total['weight_net']);
                };
                $comTotal = $comTotal + $total['weight_net'];
                
                $amount = 0;
                if ($total['weight_net'] <= 10000)
                    $amount = $total['weight_net'] * $price1;
                else
                    $amount = ($total['weight_net'] * $price2);
                if ($amount < $minimum_amount)
                    $amount = $minimum_amount;
                $amount = $amount + $admin_costs;
                
                $total_invoice_amount = $total_invoice_amount + $amount;
            }
        }
    ?>
    
    <div class="batch-company-card">
        <div class="batch-company-card-header">
            <div class="company-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="company-info">
                <h3 class="company-name"><?php echo htmlspecialchars($company); ?></h3>
                <div class="company-meta">
                    <span><?php echo $certCount; ?> certificate<?php echo $certCount > 1 ? 's' : ''; ?></span>
                    <?php if (isset($client_office[$clid])) { ?>
                        <span class="office-tag">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo $client_office[$clid]; ?>
                        </span>
                    <?php } ?>
                </div>
            </div>
            
            <?php if (!$isOnHold) { ?>
            <div class="company-stats">
                <div class="stat-box">
                    <div class="stat-value"><?php echo format_number($comTotal); ?></div>
                    <div class="stat-label">Net Weight (KG)</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">&euro;<?php echo format_number($total_invoice_amount); ?></div>
                    <div class="stat-label">Total Amount</div>
                </div>
            </div>
            <?php } ?>
            
            <div class="company-actions">
                <?php if (!$isOnHold) { ?>
                    <button class="btn-invoice-company" data-clid="<?php echo $clid; ?>">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Create Invoice
                    </button>
                <?php } ?>
                <button class="<?php echo $isOnHold ? 'btn-restore-all' : 'btn-hold-all'; ?> statusAll" data-status="<?php echo $isOnHold ? 'active' : 'onhold'; ?>">
                    <i class="fas <?php echo $isOnHold ? 'fa-undo' : 'fa-pause'; ?>"></i>
                    <?php echo $isOnHold ? 'Restore All' : 'Hold All'; ?>
                </button>
            </div>
        </div>
        
        <ol class="batch-cert-list certificates-list">
            <?php 
            $certNr = 1;
            foreach ($value[$clid] as $cert) { 
            ?>
                <li data-nr="<?php echo $cert['nr']; ?>" data-tp="<?php echo $cert['tp']; ?>">
                    <span class="cert-number"><?php echo $certNr++; ?>.</span>
                    <a class="cert-link" target="_blank" href="<?php echo "$prog_www/client_data/certificates/$cert[url]?act=print"; ?>">
                        <?php echo $cert['certificate_nr']; ?>
                    </a>
                    <span class="cert-date">
                        <i class="far fa-calendar-alt" style="margin-right: 4px; font-size: 11px;"></i>
                        <?php echo $cert['issue_date']; ?>
                    </span>
                    
                    <?php if ($isOnHold && $cert['updated_on'] != '0000-00-00 00:00:00') { ?>
                        <span class="cert-onhold-date">
                            <i class="fas fa-clock"></i>
                            On-hold: <?php echo date("d/m/Y H:i", strtotime($cert['updated_on'])); ?>
                        </span>
                    <?php } ?>
                    
                    <span class="cert-action <?php echo $isOnHold ? 'restore' : 'hold'; ?> status" data-status="<?php echo $isOnHold ? 'active' : 'onhold'; ?>">
                        <i class="fas <?php echo $isOnHold ? 'fa-undo' : 'fa-pause'; ?>"></i>
                        <?php echo $isOnHold ? 'Restore' : 'Hold'; ?>
                    </span>
                </li>
            <?php } ?>
        </ol>
    </div>
    
    <?php 
        $nr++;
    } 
    ?>
</div>

<?php } ?>

<?php if ($isOnHold) { ?>
<div class="onhold-notice">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Note:</strong> Certificates on hold will not be invoiced until you restore them. 
        Click "Restore" to make them available for invoicing again.
    </div>
</div>
<?php } ?>

<script>
    var certUrl = '../certificates/pdf/pdf_certificate.php?usr=a';
    var invUrl = 'index.php?inc=create_invoice&type=shipment&goback=<?php echo $_GET['inc']; ?>&clid=';
    var statusUrl = 'invoice_save.php';
    
    // Invoice button click
    jQuery(".btn-invoice-company").click(function(e) {
        var clid = jQuery(this).data('clid');
        window.location = invUrl + clid;
    });
    
    // Individual certificate status change
    jQuery(".cert-action.status").click(function(e) {
        var status = jQuery(this).data('status');
        var obj = jQuery(this).parents('li');
        var tp = obj.data('tp');
        var nr = obj.data('nr');
        
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
                    obj.fadeOut(300, function() {
                        jQuery(this).remove();
                        // Check if no more certificates in this company
                        var parentCard = obj.closest('.batch-company-card');
                        if (parentCard.find('.batch-cert-list li').length === 0) {
                            parentCard.fadeOut(300, function() {
                                jQuery(this).remove();
                            });
                        }
                    });
                }
            }
        });
    });
    
    // All certificates status change
    jQuery(".statusAll").click(function(e) {
        var nrs = [];
        var tp = '';
        var status = jQuery(this).data('status');
        var obj = jQuery(this).closest('.batch-company-card');
        
        obj.find('.batch-cert-list li').each(function() {
            nrs.push(jQuery(this).data('nr'));
            tp = jQuery(this).data('tp');
        });
        
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
                    obj.fadeOut(300, function() {
                        jQuery(this).remove();
                    });
                }
            }
        });
    });
</script>

<?php
    return;
}
?>