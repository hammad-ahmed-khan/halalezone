<style>
/* Client Invoices Page Header */
.client-invoices-header {
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    border-radius: 12px;
    border: 1px solid #bae6fd;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.client-invoices-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.client-invoices-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.client-invoices-header-info {
    flex: 1;
    min-width: 200px;
}

.client-invoices-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.client-invoices-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Client Badge */
.client-invoice-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #e0f2fe;
    color: #0369a1;
}

.client-invoice-badge i {
    font-size: 10px;
}

/* Header Actions */
.client-invoices-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-client-action {
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

.btn-client-action.back {
    background: #ffffff;
    color: #0369a1;
    border: 2px solid #bae6fd;
}

.btn-client-action.back:hover {
    background: #f0f9ff;
    border-color: #7dd3fc;
    color: #0284c7;
    text-decoration: none;
}

/* Client Selection Section */
.client-select-section {
    padding: 24px 32px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-top: 1px solid #bae6fd;
}

.client-select-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
}

.client-nav-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 2px solid #bae6fd;
    border-radius: 10px;
    color: #0369a1;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.client-nav-btn:hover {
    background: #0369a1;
    border-color: #0369a1;
    color: #ffffff;
}

.client-nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.client-dropdown {
    flex: 1;
    max-width: 450px;
    min-width: 250px;
}

.client-dropdown select {
    width: 100%;
    padding: 14px 48px 14px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #bae6fd;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.client-dropdown select:hover {
    border-color: #0369a1;
}

.client-dropdown select:focus {
    outline: none;
    border-color: #0369a1;
    box-shadow: 0 0 0 4px rgba(3, 105, 161, 0.12);
}

/* Display Options */
.display-options {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed #7dd3fc;
    justify-content: center;
    flex-wrap: wrap;
}

.display-option-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.display-option-group label {
    font-size: 13px;
    font-weight: 600;
    color: #0369a1;
    white-space: nowrap;
}

.display-option-group select {
    padding: 10px 36px 10px 14px;
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
}

.display-option-group select:hover {
    border-color: #0369a1;
}

.display-option-group select:focus {
    outline: none;
    border-color: #0369a1;
    box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.12);
}

/* Selected Client Info */
.selected-client-info {
    display: none;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #ffffff;
    border-top: 1px solid #bae6fd;
}

.selected-client-info.active {
    display: flex;
}

.selected-client-info .client-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0369a1;
    font-size: 18px;
}

.selected-client-info .client-details {
    flex: 1;
}

.selected-client-info .client-details .client-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 2px 0;
}

.selected-client-info .client-details .client-meta {
    font-size: 12px;
    color: #64748b;
}

/* Results Container */
.client-results-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-top: 24px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.client-results-container #clients_invoices {
    padding: 20px;
    min-height: 200px;
}

.client-results-container #unpaidInvoices {
    border-top: 1px solid #e2e8f0;
    padding: 20px;
}

/* Empty State */
.client-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #64748b;
}

.client-empty-state i {
    font-size: 48px;
    color: #bae6fd;
    margin-bottom: 16px;
}

.client-empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: #475569;
    margin: 0 0 8px 0;
}

.client-empty-state p {
    font-size: 14px;
    color: #94a3b8;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .client-invoices-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .client-invoices-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .client-invoices-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .client-select-section {
        padding: 20px;
    }
    
    .client-select-wrapper {
        flex-direction: column;
    }
    
    .client-dropdown {
        width: 100%;
        max-width: none;
    }
    
    .client-nav-btn {
        width: 100%;
    }
    
    .display-options {
        flex-direction: column;
        gap: 16px;
    }
    
    .display-option-group {
        width: 100%;
        justify-content: space-between;
    }
    
    .display-option-group select {
        flex: 1;
    }
    
    .selected-client-info {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px;
    }
}
</style>
<?php
if ($clients = $amdb->get_results("SELECT companies.clid,companies.company_name FROM companies
                                LEFT OUTER JOIN invoices ON companies.clid = invoices.clid
                                group by invoices.clid
                                ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {
?>

<script>
    $("#page_title").html("Client Invoices")
</script>

<div class="client-invoices-header">
    <div class="client-invoices-header-content">
        <div class="client-invoices-header-icon">
            <i class="fas fa-user-invoice"></i>
        </div>
        
        <div class="client-invoices-header-info">
            <h2>
                Client Invoices
                <span class="client-invoice-badge">
                    <i class="fas fa-building"></i>
                    By Company
                </span>
            </h2>
            <p>View and manage invoices organized by client</p>
        </div>
        
        <div class="client-invoices-header-actions">
            <a href="index.php?show=all" class="btn-client-action back">
                <i class="fas fa-arrow-left"></i>
                All Invoices
            </a>
        </div>
    </div>
    
    <div class="client-select-section">
        <div class="client-select-wrapper">
            <button type="button" class="client-nav-btn" onclick="getClientInvoices(-1)" title="Previous Client">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="client-dropdown">
                <select name="clid" id="clid" class="searchable" onchange="loadInvoices(); updateSelectedClient();">
                    <option value="">-- Select a Client --</option>
                    <?php foreach ($clients as $client) { ?>
                        <option value="<?php echo $client['clid']; ?>"><?php echo trim(htmlspecialchars($client['company_name'])); ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <button type="button" class="client-nav-btn" onclick="getClientInvoices(+1)" title="Next Client">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="display-options">
            <div class="display-option-group">
                <label for="tvh">
                    <i class="fas fa-th-large" style="margin-right: 6px;"></i>
                    Display:
                </label>
                <select name="tvh" id="tvh" onchange="loadInvoices()">
                    <option value="h">Horizontal</option>
                    <option value="v">Vertical</option>
                </select>
            </div>
            
            <div class="display-option-group">
                <label for="ascDesc">
                    <i class="fas fa-sort" style="margin-right: 6px;"></i>
                    Sort Years:
                </label>
                <select name="ascDesc" id="ascDesc" onchange="loadInvoices()">
                    <option value="ASC">Oldest First</option>
                    <option value="DESC">Newest First</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="selected-client-info" id="selectedClientInfo">
        <div class="client-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="client-details">
            <p class="client-name" id="selectedClientName">-</p>
            <p class="client-meta">Viewing invoice history</p>
        </div>
    </div>
</div>

<div class="client-results-container">
    <div id="clients_invoices">
        <div class="client-empty-state">
            <i class="fas fa-hand-pointer"></i>
            <h3>Select a Client</h3>
            <p>Choose a client from the dropdown above to view their invoices</p>
        </div>
    </div>
    <div id="unpaidInvoices"></div>
</div>

<script>
    function loadInvoices() {
        var clid = jQuery("#clid").val();
        if (!clid) {
            jQuery("#clients_invoices").html('<div class="client-empty-state"><i class="fas fa-hand-pointer"></i><h3>Select a Client</h3><p>Choose a client from the dropdown above to view their invoices</p></div>');
            jQuery("#selectedClientInfo").removeClass('active');
            return;
        }
        
        jQuery("#clients_invoices").html('<div class="client-empty-state"><i class="fas fa-spinner fa-spin"></i><h3>Loading...</h3><p>Fetching invoice data</p></div>');
        
        var tvh = jQuery("#tvh").val();
        var ascDesc = jQuery("#ascDesc").val();
        jQuery("#clients_invoices").load('load_client_invoices.php?tvh=' + tvh + '&ascDesc=' + ascDesc + '&clid=' + clid);
    }
    
    function updateSelectedClient() {
        var selectedText = jQuery("#clid option:selected").text().trim();
        if (selectedText && selectedText !== '-- Select a Client --') {
            jQuery("#selectedClientName").text(selectedText);
            jQuery("#selectedClientInfo").addClass('active');
        } else {
            jQuery("#selectedClientInfo").removeClass('active');
        }
    }

    function getClientInvoices(pos) {
        jQuery("#clients_invoices").html('<div class="client-empty-state"><i class="fas fa-spinner fa-spin"></i><h3>Loading...</h3><p>Fetching invoice data</p></div>');
        jQuery(".searchSelectInput").val('');
        
        // Get current selection index
        var currentIndex = jQuery("#clid")[0].selectedIndex;
        var totalOptions = jQuery("#clid option").length;
        
        // Calculate new index
        var newIndex = currentIndex + pos;
        
        // Skip the first option (placeholder) when navigating
        if (newIndex < 1) newIndex = totalOptions - 1;
        if (newIndex >= totalOptions) newIndex = 1;
        
        // Set new selection
        jQuery("#clid")[0].selectedIndex = newIndex;
        
        // Update UI and load invoices
        updateSelectedClient();
        loadInvoices();
    }

    function getUnpaidInvoices() {
        jQuery("#client_invoices_table > tbody > tr.unpaid").find('td').each(function() {
            alert(jQuery(this).text());
        });
    }

    function loadUnpaidInvoices(obj) {
        jQuery("#unpaidInvoices").html('<div style="padding: 20px; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Loading unpaid invoices...</div>');
        var clid = jQuery("#clid").val();
        var parentID = jQuery(obj).parent().parents('table').attr('id');
        var year = (parentID == 'allInvoices') ? 'all' : parentID.replace('year_', '');
        
        jQuery.post('load_invoices.php', {
            show: 'unpaid',
            searchFor: 'client',
            orderBy: 'inserted_on',
            ascDsc: 'DSC',
            offid: '0',
            period: 'year',
            year: year,
            clid: clid
        }, function(data) {
            jQuery("#unpaidInvoices").html(data);
        });
    }
    
    // Initialize
    jQuery(document).ready(function() {
        // Check if there's a pre-selected client
        if (jQuery("#clid").val()) {
            updateSelectedClient();
            loadInvoices();
        }
    });
</script>

<?php } else { ?>

<div class="client-invoices-header">
    <div class="client-invoices-header-content">
        <div class="client-invoices-header-icon">
            <i class="fas fa-user-invoice"></i>
        </div>
        
        <div class="client-invoices-header-info">
            <h2>
                Client Invoices
                <span class="client-invoice-badge">
                    <i class="fas fa-building"></i>
                    By Company
                </span>
            </h2>
            <p>View and manage invoices organized by client</p>
        </div>
    </div>
</div>

<div class="client-results-container">
    <div class="client-empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Clients Found</h3>
        <p>There are no clients with invoices in the system yet.</p>
    </div>
</div>

<?php } ?>