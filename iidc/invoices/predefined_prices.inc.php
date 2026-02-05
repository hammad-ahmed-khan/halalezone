<?php if (!defined("_HQC_")) {
    exit();
};

if (isset($_COOKIE['predefined'])) { ?>
    <style>
        /* Access Code Screen */
        .access-code-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            max-width: 400px;
            width: 100%;
            padding: 0 20px;
        }
        
        .access-code-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 40px 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .access-code-card .lock-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        
        .access-code-card .lock-icon i {
            font-size: 32px;
            color: #ffffff;
        }
        
        .access-code-card h2 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .access-code-card p {
            margin: 0 0 24px 0;
            font-size: 14px;
            color: #64748b;
        }
        
        .access-code-card input[type="text"] {
            width: 100%;
            padding: 14px 18px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 4px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 16px;
            transition: all 0.25s ease;
        }
        
        .access-code-card input[type="text"]:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
        }
        
        .access-code-card input[type="submit"] {
            width: 100%;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        
        .access-code-card input[type="submit"]:hover {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .access-code-card .resend-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
            color: #0369a1;
            cursor: pointer;
            text-decoration: none;
        }
        
        .access-code-card .resend-link:hover {
            text-decoration: underline;
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
    <div class="access-code-container">
        <div class="access-code-card">
            <div class="lock-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Access Code Required</h2>
            <p>Please enter your verification code to continue</p>
            <form method="post" onsubmit="return accessCode('checkCode');">
                <input type="hidden" name="act" value="checkAccessCode" />
                <input type="text" name="AccessCode" placeholder="Enter code" autocomplete="off" />
                <input type="submit" value="Verify Access" />
            </form>
            <a class="resend-link" onclick="accessCode('sendCode')">
                <i class="fas fa-envelope"></i> Email me a new access code
            </a>
        </div>
    </div>
<?php return;
} ?>

<style>
/* ============================================
   Predefined Prices Page Styling
   ============================================ */

/* Page Header */
.prices-page-header {
    background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%);
    border-radius: 12px;
    border: 1px solid #fde68a;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.prices-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.prices-header-icon {
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

.prices-header-info {
    flex: 1;
    min-width: 200px;
}

.prices-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.prices-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.prices-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.prices-header-actions {
    display: flex;
    gap: 12px;
}

.btn-prices-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
    text-decoration: none;
}

.btn-prices-action.primary {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    color: #ffffff;
}

.btn-prices-action.primary:hover {
    background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
}

.btn-prices-action.secondary {
    background: #ffffff;
    color: #92400e;
    border: 2px solid #fde68a;
}

.btn-prices-action.secondary:hover {
    background: #fffbeb;
    border-color: #fcd34d;
}

/* Main Layout */
.prices-layout {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

/* Sidebar - Companies List */
.prices-sidebar {
    width: 280px;
    flex-shrink: 0;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.sidebar-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.sidebar-header h3 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sidebar-header h3 i {
    color: #64748b;
}

.sidebar-search {
    position: relative;
}

.sidebar-search input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.sidebar-search input:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
}

.sidebar-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

/* Companies List */
#companiesList {
    padding: 0;
    margin: 0;
    list-style: none;
    max-height: 500px;
    overflow-y: auto;
}

#companiesList li {
    padding: 12px 20px;
    font-size: 13px;
    color: #374151;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

#companiesList li:last-child {
    border-bottom: none;
}

#companiesList li:hover {
    background: #fffbeb;
}

#companiesList li.active,
#companiesList li[style*="font-weight: bold"],
#companiesList li[style*="font-weight:bold"] {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
    color: #92400e !important;
    font-weight: 600 !important;
}

#companiesList li.has-custom-prices,
#companiesList li[style*="color:green"],
#companiesList li[style*="color: green"] {
    color: #16a34a !important;
}

#companiesList li.has-custom-prices::before,
#companiesList li[style*="color:green"]::before,
#companiesList li[style*="color: green"]::before {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 10px;
    color: #16a34a;
    background: #dcfce7;
    padding: 4px;
    border-radius: 50%;
}

#companiesList li.default-item {
    background: #f8fafc;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
}

#companiesList li.default-item::before {
    content: "\f0c8";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 12px;
    color: #f59e0b;
}

/* Prices Content Area */
.prices-content {
    flex: 1;
    min-width: 0;
}

#companiesPricesHolder {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* Prices Table */
#predefined_prices {
    width: 100%;
    border-collapse: collapse;
}

#predefined_prices thead th {
    background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
    color: #92400e;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 13px;
    text-align: left;
    border-bottom: 2px solid #fde68a;
    white-space: nowrap;
}

#predefined_prices tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
    font-size: 14px;
    color: #374151;
}

#predefined_prices tbody tr:last-child td {
    border-bottom: none;
}

#predefined_prices tbody tr:hover {
    background: #fffbeb;
}

#predefined_prices tbody tr.highlighted {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

/* Form Elements in Table */
#predefined_prices tbody input[type='text'],
#predefined_prices tbody input[type='number'],
#predefined_prices tbody textarea,
#predefined_prices tbody select {
    padding: 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.25s ease;
    font-family: inherit;
}

#predefined_prices tbody input:focus,
#predefined_prices tbody textarea:focus,
#predefined_prices tbody select:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
}

#predefined_prices .description textarea {
    width: 100%;
    min-width: 200px;
    min-height: 60px;
    resize: vertical;
}

#predefined_prices .service_type input {
    width: 150px;
}

#predefined_prices .item_code input {
    width: 80px;
}

#predefined_prices .prices input {
    width: 100px;
    text-align: right;
}

#predefined_prices td b {
    display: inline-block;
    min-width: 100px;
    margin-right: 8px;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

#predefined_prices input.id {
    width: 100px;
}

/* Price Fields Group */
.price-fields-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.price-field-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.price-field-row label {
    min-width: 100px;
    font-size: 12px;
    color: #64748b;
}

.price-field-row input {
    flex: 1;
    max-width: 120px;
}

/* Action Buttons in Table */
#predefined_prices .action {
    white-space: nowrap;
}

.table-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    background: transparent;
}

.table-action-btn.edit {
    color: #0369a1;
    background: #f0f9ff;
}

.table-action-btn.edit:hover {
    background: #e0f2fe;
    color: #075985;
}

.table-action-btn.save {
    color: #16a34a;
    background: #f0fdf4;
}

.table-action-btn.save:hover {
    background: #dcfce7;
    color: #15803d;
}

.table-action-btn.delete {
    color: #dc2626;
    background: #fef2f2;
}

.table-action-btn.delete:hover {
    background: #fee2e2;
    color: #b91c1c;
}

.table-action-btn i {
    font-size: 14px !important;
}

/* New Price Item Row */
#newPriceItem {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

#newPriceItem td {
    border-bottom: 2px solid #86efac;
}

/* Add New Button */
#addNewPriceItem {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    margin: 16px;
}

#addNewPriceItem:hover {
    background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

/* Invoice Type Filter */
.prices-filter-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.prices-filter-bar label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.prices-filter-bar select {
    padding: 8px 32px 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #ffffff;
    cursor: pointer;
}

/* Empty State */
.prices-empty-state {
    text-align: center;
    padding: 48px 24px;
}

.prices-empty-state i {
    font-size: 48px;
    color: #fde68a;
    margin-bottom: 16px;
}

.prices-empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #64748b;
}

.prices-empty-state p {
    margin: 0 0 20px 0;
    font-size: 14px;
    color: #94a3b8;
}

/* Table Footer */
.prices-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
    gap: 12px;
}

.prices-table-footer .footer-info {
    font-size: 13px;
    color: #64748b;
}

.prices-table-footer .footer-info strong {
    color: #92400e;
}

/* Save All Button */
.save-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.save-all-btn:hover {
    background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.save-all-btn i {
    font-size: 16px !important;
}

/* Selected Company Info */
.selected-company-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-bottom: 1px solid #bbf7d0;
}

.selected-company-info .company-icon {
    width: 40px;
    height: 40px;
    background: #ffffff;
    border: 2px solid #86efac;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    font-size: 16px;
}

.selected-company-info .company-details {
    flex: 1;
}

.selected-company-info .company-name {
    font-size: 15px;
    font-weight: 600;
    color: #166534;
    margin: 0 0 2px 0;
}

.selected-company-info .company-type {
    font-size: 12px;
    color: #16a34a;
}

.selected-company-info .reset-btn {
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 600;
    color: #166534;
    background: #ffffff;
    border: 1px solid #86efac;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.selected-company-info .reset-btn:hover {
    background: #f0fdf4;
}

/* Responsive */
@media (max-width: 1024px) {
    .prices-layout {
        flex-direction: column;
    }
    
    .prices-sidebar {
        width: 100%;
    }
    
    #companiesList {
        max-height: 200px;
    }
    
    .prices-content {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .prices-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .prices-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .prices-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    #predefined_prices {
        display: block;
        overflow-x: auto;
    }
    
    .prices-filter-bar {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .prices-table-footer {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
    jQuery("#page_title").html('Predefined Prices');
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
        jQuery("#newPriceItem input,textarea").each(function() {
            if (!jQuery(this).hasClass("extra_costs")) {
                jQuery(this).attr("required", "required");
            }
        });
    }
    
    var url = 'predefined_list.php?clid=0';

    function getClientPrices(clid) {
        url = 'predefined_list.php?clid=' + clid;
        jQuery("#companiesPricesHolder").load(url);
        
        // Update active state
        jQuery("#companiesList li").removeClass("active");
        jQuery("#companiesList li[data-clid='" + clid + "']").addClass("active");
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
            if (jQuery(this).data('clid') === 0) {
                // Always show default
                jQuery(this).css("display", "flex");
            } else if (jQuery(this).text().toLowerCase().indexOf(val.toLowerCase()) > -1) {
                jQuery(this).css("display", "flex");
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

$companiesWithPrices = 0;
foreach ($companies as $company) {
    if ($company['prices'] != NULL) {
        $companiesWithPrices++;
    }
}
?>

<!-- Page Header -->
<div class="prices-page-header">
    <div class="prices-header-content">
        <div class="prices-header-icon">
            <i class="fas fa-tags"></i>
        </div>
        
        <div class="prices-header-info">
            <h2>
                Predefined Prices
                <span class="prices-badge">
                    <i class="fas fa-cog"></i>
                    Configuration
                </span>
            </h2>
            <p>Set default and company-specific pricing for invoice items</p>
        </div>
        
        <div class="prices-header-actions">
            <button class="btn-prices-action secondary" onclick="resetPrices()">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
            <button class="btn-prices-action primary" onclick="addNewPriceItem()">
                <i class="fas fa-plus"></i>
                Add Price Item
            </button>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="prices-layout">
    <!-- Sidebar -->
    <div class="prices-sidebar">
        <div class="sidebar-header">
            <h3>
                <i class="fas fa-building"></i>
                Companies
            </h3>
            <div class="sidebar-search">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Search companies..." onkeyup="searchCompanies(this.value)" />
            </div>
        </div>
        
        <ol id="companiesList">
            <li data-clid="0" class="default-item active">
                Default prices for all companies
            </li>
            <?php foreach ($companies as $company) {
                $hasCustomPrices = ($company['prices'] != NULL);
                $customClass = $hasCustomPrices ? 'has-custom-prices' : '';
            ?>
                <li data-clid="<?php echo $company['clid']; ?>" class="<?php echo $customClass; ?>">
                    <?php echo htmlspecialchars($company['company_name']); ?>
                </li>
            <?php } ?>
        </ol>
    </div>
    
    <!-- Content Area -->
    <div class="prices-content">
        <div id="companiesPricesHolder">
            <!-- Prices table will be loaded here -->
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        getClientPrices(0);
        
        // Update URL without reload
        if (window.history.pushState) {
            window.history.pushState('', '', '?inc=predefined_prices');
        }
        
        // Adjust list height
        setTimeout(function() {
            var contentHeight = jQuery(".prices-content").height();
            if (contentHeight > 300) {
                jQuery("#companiesList").css("max-height", (contentHeight - 100) + "px");
            }
        }, 500);
        
        // Company list click handler
        jQuery("#companiesList li").on("click", function() {
            jQuery("#companiesList li").removeClass("active");
            jQuery(this).addClass("active");
            getClientPrices(jQuery(this).data('clid'));
        });
    });
</script>