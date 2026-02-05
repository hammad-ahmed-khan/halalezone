<?php
if (!defined('__HQC__')) {
    exit;
}

if ($reminders = $amdb->get_results("SELECT hqc_default_invoice_reminders.*,companies.company_name,companies.clid FROM `hqc_default_invoice_reminders` RIGHT JOIN companies ON hqc_default_invoice_reminders.clid = companies.clid WHERE companies.active = 'y' AND companies.offid = '0' AND companies.clof = '0' ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {

    $office = $amdb->get_row("SELECT offid,office_name,reference_prefix,certificate_prefix FROM offices WHERE offid = '0'");
    
    // Calculate stats
    $totalCompanies = count($reminders);
    $activeReminders = 0;
    foreach ($reminders as $r) {
        if ($r['status'] == 'on') $activeReminders++;
    }
?>

<style>
/* ============================================
   Invoice Reminders Page Styling
   ============================================ */

/* Page Header */
.reminders-page-header {
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    border-radius: 12px;
    border: 1px solid #bae6fd;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.reminders-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.reminders-header-icon {
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

.reminders-header-info {
    flex: 1;
    min-width: 200px;
}

.reminders-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.reminders-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.reminders-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.reminders-quick-stats {
    display: flex;
    gap: 12px;
}

.reminders-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #ffffff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    min-width: 90px;
}

.reminders-stat-item .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1;
}

.reminders-stat-item .stat-label {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.reminders-stat-item.active .stat-value {
    color: #16a34a;
}

/* Info Strip */
.reminders-info-strip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 32px;
    background: #f0f9ff;
    border-top: 1px solid #bae6fd;
    font-size: 13px;
    color: #0369a1;
}

.reminders-info-strip i {
    color: #0ea5e9;
}

/* Table Container */
.reminders-table-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* Toolbar */
.reminders-toolbar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.toolbar-search {
    position: relative;
    flex: 1;
    max-width: 350px;
}

.toolbar-search input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
    background: #ffffff;
}

.toolbar-search input:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
}

.toolbar-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

.toolbar-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.btn-toolbar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-toolbar.on {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.btn-toolbar.on:hover {
    background: #bbf7d0;
}

.btn-toolbar.off {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.btn-toolbar.off:hover {
    background: #fecaca;
}

.btn-toolbar.edit {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.btn-toolbar.edit:hover {
    background: #bae6fd;
}

.toolbar-selection-info {
    font-size: 12px;
    color: #64748b;
    padding: 8px 12px;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.toolbar-selection-info strong {
    color: #0369a1;
}

/* Table Styles */
.reminders-table {
    width: 100%;
    border-collapse: collapse;
}

.reminders-table thead th {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    color: #0369a1;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 12px;
    text-align: left;
    border-bottom: 2px solid #bae6fd;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.reminders-table thead th:first-child {
    text-align: center;
    width: 50px;
}

.reminders-table thead th.checkbox-col {
    text-align: center;
    width: 50px;
}

.reminders-table thead th.center {
    text-align: center;
}

.reminders-table tbody tr {
    transition: background 0.2s ease;
}

.reminders-table tbody tr:hover {
    background: #f0f9ff;
}

.reminders-table tbody tr.selected {
    background: #e0f2fe;
}

.reminders-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 14px;
    color: #374151;
}

.reminders-table tbody td:first-child {
    text-align: center;
    font-weight: 600;
    color: #64748b;
    font-size: 12px;
}

.reminders-table tbody td.checkbox-col {
    text-align: center;
}

.reminders-table tbody td.center {
    text-align: center;
}

/* Checkbox Styling */
.reminders-table input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #0369a1;
    cursor: pointer;
}

/* Company Cell */
.company-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.company-id {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    font-family: 'Monaco', 'Consolas', monospace;
    cursor: pointer;
    transition: all 0.2s ease;
}

.company-id:hover {
    background: #e0f2fe;
    color: #0369a1;
}

.company-name {
    font-weight: 500;
    color: #1e293b;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Days Badge */
.days-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.days-badge .value {
    font-weight: 700;
    color: #1e293b;
}

.days-badge.default {
    background: #f1f5f9;
    color: #64748b;
    border-color: #e2e8f0;
}

.days-badge.custom {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

/* Status Toggle */
.status-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.status-toggle i {
    font-size: 24px !important;
    transition: all 0.2s ease;
}

.status-toggle i.fa-toggle-on {
    color: #16a34a;
}

.status-toggle i.fa-toggle-off {
    color: #cbd5e1;
}

.status-toggle:hover {
    background: #f1f5f9;
}

.status-toggle:hover i.fa-toggle-on {
    color: #15803d;
}

.status-toggle:hover i.fa-toggle-off {
    color: #94a3b8;
}

/* Action Button */
.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #0369a1;
    background: #f0f9ff;
}

.action-btn:hover {
    background: #e0f2fe;
    color: #075985;
}

.action-btn i {
    font-size: 14px !important;
}

/* Table Footer */
.reminders-table tfoot th {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #475569;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 12px;
    text-align: left;
    border-top: 2px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reminders-table tfoot th:first-child {
    text-align: center;
}

.reminders-table tfoot th.checkbox-col {
    text-align: center;
}

/* Bulk Actions Footer */
.bulk-actions-footer {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.bulk-actions-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.bulk-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.bulk-action-btn.enable {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: #ffffff;
}

.bulk-action-btn.enable:hover {
    background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.bulk-action-btn.disable {
    background: #ffffff;
    color: #dc2626;
    border: 2px solid #fecaca;
}

.bulk-action-btn.disable:hover {
    background: #fef2f2;
    border-color: #f87171;
}

.bulk-action-btn.edit {
    background: #ffffff;
    color: #0369a1;
    border: 2px solid #bae6fd;
}

.bulk-action-btn.edit:hover {
    background: #f0f9ff;
    border-color: #7dd3fc;
}

.bulk-action-btn i {
    font-size: 16px !important;
}

/* Empty State */
.reminders-empty-state {
    text-align: center;
    padding: 60px 24px;
}

.reminders-empty-state .empty-icon {
    width: 80px;
    height: 80px;
    background: #e0f2fe;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.reminders-empty-state .empty-icon i {
    font-size: 36px;
    color: #0ea5e9;
}

.reminders-empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
}

.reminders-empty-state p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Responsive */
@media (max-width: 1200px) {
    .company-name {
        max-width: 200px;
    }
}

@media (max-width: 1024px) {
    .reminders-header-content {
        flex-wrap: wrap;
    }
    
    .reminders-quick-stats {
        width: 100%;
        justify-content: flex-start;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #bae6fd;
    }
    
    .reminders-table-container {
        overflow-x: auto;
    }
}

@media (max-width: 768px) {
    .reminders-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .reminders-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .reminders-quick-stats {
        justify-content: center;
    }
    
    .reminders-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .toolbar-search {
        max-width: 100%;
    }
    
    .toolbar-actions {
        justify-content: center;
        margin-left: 0;
    }
    
    .bulk-actions-footer {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .bulk-action-btn {
        width: 100%;
        justify-content: center;
    }
    
    .company-cell {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    
    .company-name {
        max-width: 150px;
    }
}
</style>

<script>
    function changeReminderStatus(obj) {
        var clid = $(obj).closest('tr').data('clid');
        var status = $(obj).hasClass('fa-toggle-on') ? 'off' : 'on';
        $.ajax({
            url: 'reminder_save.php',
            type: 'POST',
            data: {
                act: 'changeReminderStatus',
                clid: clid,
                status: status
            },
            success: function(data) {
                if (data == 'ok') {
                    $('.com_' + clid).find('.status-toggle').html('<i class="fas fa-toggle-' + status + '"></i>');
                    updateStats();
                    alert_message('Status changed successfully');
                } else {
                    alert_message(data);
                }
            }
        });
    }

    function checkAllCheckboxes(obj) {
        var checkboxes = document.querySelectorAll('.clidCheckbox');
        checkboxes.forEach(function(checkbox) {
            if (checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = obj.checked;
            }
        });
        updateSelectionInfo();
    }

    async function changeBulkReminderStatus(status) {
        var clids = [];
        $('.clidCheckbox:checked').each(function() {
            clids.push($(this).val());
        });
        if (clids.length == 0) {
            alert_message('Please select at least one company');
            return;
        }
        await working_alert();
        $.ajax({
            url: 'reminder_save.php',
            type: 'POST',
            data: {
                act: 'changeBulkReminderStatus',
                clids: clids,
                status: status
            },
            success: function(data) {
                if (data == 'ok') {
                    $('.clidCheckbox:checked').each(function() {
                        var clid = $(this).val();
                        $('.com_' + clid).find('.status-toggle').html('<i class="fas fa-toggle-' + status + '"></i>');
                    });
                    updateStats();
                    close_alert();
                    alert_message(clids.length + ' companies updated successfully');
                } else {
                    alert_message(data);
                }
            }
        });
    }

    function massEdit() {
        var clids = [];
        $('.clidCheckbox:checked').each(function() {
            clids.push($(this).val());
        });
        if (clids.length == 0) {
            alert_message('Please select at least one company');
            return;
        }
        var url = 'reminder_edit.php?act=massEdit&clid=*';
        doLoadPopup(url, '', 'Update ' + clids.length + ' Selected Companies');
    }

    function searchClients(val) {
        var search = val.toLowerCase();
        var rows = document.querySelectorAll('.reminders-table tbody tr');
        var visibleCount = 0;
        rows.forEach(function(row) {
            var companyName = row.querySelector('.company-name').textContent.toLowerCase();
            var companyId = row.querySelector('.company-id').textContent.toLowerCase();
            if (companyName.includes(search) || companyId.includes(search)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        document.getElementById('visibleCount').textContent = visibleCount;
    }

    function updateSelectionInfo() {
        var checkedCount = $('.clidCheckbox:checked').length;
        var infoEl = document.getElementById('selectionInfo');
        if (checkedCount > 0) {
            infoEl.innerHTML = '<strong>' + checkedCount + '</strong> selected';
            infoEl.style.display = 'block';
        } else {
            infoEl.style.display = 'none';
        }
    }

    function updateStats() {
        var activeCount = $('.fa-toggle-on').length;
        document.getElementById('activeCount').textContent = activeCount;
    }

    // Checkbox change event
    $(document).ready(function() {
        $('.clidCheckbox').on('change', function() {
            updateSelectionInfo();
            $(this).closest('tr').toggleClass('selected', this.checked);
        });
    });
</script>

<!-- Page Header -->
<div class="reminders-page-header">
    <div class="reminders-header-content">
        <div class="reminders-header-icon">
            <i class="fas fa-bell"></i>
        </div>
        
        <div class="reminders-header-info">
            <h2>
                Payment Terms & Reminders
                <span class="reminders-badge">
                    <i class="fas fa-cog"></i>
                    Configuration
                </span>
            </h2>
            <p>Configure default payment terms and automatic reminder schedules for each company</p>
        </div>
        
        <div class="reminders-quick-stats">
            <div class="reminders-stat-item">
                <span class="stat-value"><?php echo $totalCompanies; ?></span>
                <span class="stat-label">Companies</span>
            </div>
            <div class="reminders-stat-item active">
                <span class="stat-value" id="activeCount"><?php echo $activeReminders; ?></span>
                <span class="stat-label">Active</span>
            </div>
        </div>
    </div>
    
    <div class="reminders-info-strip">
        <i class="fas fa-info-circle"></i>
        <span>Reminders are sent automatically based on the configured schedule. Days are counted from the invoice due date.</span>
    </div>
</div>

<!-- Table Container -->
<div class="reminders-table-container">
    <!-- Toolbar -->
    <div class="reminders-toolbar">
        <div class="toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search companies by name or ID..." onkeyup="searchClients(this.value)" />
        </div>
        
        <span class="toolbar-selection-info" id="selectionInfo" style="display: none;">
            <strong>0</strong> selected
        </span>
        
        <div class="toolbar-actions">
            <button class="btn-toolbar on" onclick="changeBulkReminderStatus('on')" title="Enable selected">
                <i class="fas fa-toggle-on"></i>
                Enable
            </button>
            <button class="btn-toolbar off" onclick="changeBulkReminderStatus('off')" title="Disable selected">
                <i class="fas fa-toggle-off"></i>
                Disable
            </button>
            <button class="btn-toolbar edit" onclick="massEdit()" title="Edit selected">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>
    
    <!-- Table -->
    <table class="reminders-table">
        <thead>
            <tr>
                <th>Nr</th>
                <th class="checkbox-col">
                    <input type="checkbox" onclick="checkAllCheckboxes(this)" title="Select all" />
                </th>
                <th>
                    Company
                    <span style="font-weight: 400; text-transform: none; letter-spacing: normal; margin-left: 10px; color: #64748b;">
                        (showing <span id="visibleCount"><?php echo $totalCompanies; ?></span>)
                    </span>
                </th>
                <th class="center">Term / 1st Reminder</th>
                <th class="center">2nd Reminder</th>
                <th class="center">Phone Call</th>
                <th class="center" style="width: 70px;">Edit</th>
                <th class="center" style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $first_reminder = 21;
            $second_reminder = 7;
            $telephone_call = 7;
            $i = 1;
            
            foreach ($reminders as $reminder) {
                $status = $reminder['status'] != '' ? $reminder['status'] : 'off';
                $hasCustomFirst = ($reminder['first_reminder'] != '' && $reminder['first_reminder'] != $first_reminder);
                $hasCustomSecond = ($reminder['second_reminder'] != '' && $reminder['second_reminder'] != $second_reminder);
                $hasCustomPhone = ($reminder['telephone_call'] != '' && $reminder['telephone_call'] != $telephone_call);
            ?>
                <tr data-clid="<?php echo $reminder['clid']; ?>" class="com_<?php echo $reminder['clid']; ?>">
                    <td><?php echo $i; ?></td>
                    <td class="checkbox-col">
                        <input type="checkbox" class="clidCheckbox" name="clid[]" value="<?php echo $reminder['clid']; ?>">
                    </td>
                    <td>
                        <div class="company-cell">
                            <span class="company-id load_popup" 
                                  data-url="../../admin/load_company.php?clid=<?php echo $reminder['clid']; ?><?php echo (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) ? '&uid=' . $_SESSION['user']['uid'] : ''; ?>" 
                                  title="View company details">
                                <?php echo $office['reference_prefix']; ?><?php echo $office['certificate_prefix']; ?><?php echo str_pad($reminder['clid'], 6, '0', STR_PAD_LEFT); ?>
                            </span>
                            <span class="company-name" title="<?php echo htmlspecialchars($reminder['company_name']); ?>">
                                <?php echo htmlspecialchars($reminder['company_name']); ?>
                            </span>
                        </div>
                    </td>
                    <td class="center">
                        <span class="days-badge <?php echo $hasCustomFirst ? 'custom' : 'default'; ?>">
                            <span class="value"><?php echo $reminder['first_reminder'] != '' ? $reminder['first_reminder'] : $first_reminder; ?></span>
                            Days
                        </span>
                    </td>
                    <td class="center">
                        <span class="days-badge <?php echo $hasCustomSecond ? 'custom' : 'default'; ?>">
                            <span class="value"><?php echo $reminder['second_reminder'] != '' ? $reminder['second_reminder'] : $second_reminder; ?></span>
                            Days
                        </span>
                    </td>
                    <td class="center">
                        <span class="days-badge <?php echo $hasCustomPhone ? 'custom' : 'default'; ?>">
                            <span class="value"><?php echo $reminder['telephone_call'] != '' ? $reminder['telephone_call'] : $telephone_call; ?></span>
                            Days
                        </span>
                    </td>
                    <td class="center">
                        <span class="action-btn load_popup" 
                              data-url="reminder_edit.php?act=edit&clid=<?php echo $reminder['clid']; ?>" 
                              title="Edit reminder settings">
                            <i class="fas fa-edit"></i>
                        </span>
                    </td>
                    <td class="center">
                        <span class="status-toggle" onclick="changeReminderStatus(this)" title="Toggle reminder status">
                            <i class="fas fa-toggle-<?php echo $status; ?>"></i>
                        </span>
                    </td>
                </tr>
            <?php
                $i++;
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Nr</th>
                <th class="checkbox-col">
                    <input type="checkbox" onclick="checkAllCheckboxes(this)" title="Select all" />
                </th>
                <th>Company</th>
                <th class="center">Term / 1st Reminder</th>
                <th class="center">2nd Reminder</th>
                <th class="center">Phone Call</th>
                <th class="center">Edit</th>
                <th class="center">Status</th>
            </tr>
        </tfoot>
    </table>
    
    <!-- Bulk Actions Footer -->
    <div class="bulk-actions-footer">
        <span class="bulk-actions-label">Bulk Actions:</span>
        
        <button class="bulk-action-btn enable" onclick="changeBulkReminderStatus('on')">
            <i class="fas fa-toggle-on"></i>
            Enable Selected
        </button>
        
        <button class="bulk-action-btn disable" onclick="changeBulkReminderStatus('off')">
            <i class="fas fa-toggle-off"></i>
            Disable Selected
        </button>
        
        <button class="bulk-action-btn edit" onclick="massEdit()">
            <i class="fas fa-edit"></i>
            Edit Selected
        </button>
    </div>
</div>

<?php
} else {
?>
<!-- Empty State -->
<div class="reminders-page-header">
    <div class="reminders-header-content">
        <div class="reminders-header-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="reminders-header-info">
            <h2>
                Payment Terms & Reminders
                <span class="reminders-badge">
                    <i class="fas fa-cog"></i>
                    Configuration
                </span>
            </h2>
            <p>Configure default payment terms and automatic reminder schedules</p>
        </div>
    </div>
</div>

<div class="reminders-table-container">
    <div class="reminders-empty-state">
        <div class="empty-icon">
            <i class="fas fa-bell-slash"></i>
        </div>
        <h3>No Companies Found</h3>
        <p>There are no active companies to configure reminders for.</p>
    </div>
</div>
<?php
}
?>