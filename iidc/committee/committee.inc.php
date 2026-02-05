<?php
if (!defined("_HQC_")) {
    exit();
};
?>
<style>
/* Committee Meetings Page Header */
.meetings-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef7f0 100%);
    border-radius: 12px;
    border: 1px solid #fed7aa;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.meetings-header.history {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-color: #bbf7d0;
}

.meetings-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.meetings-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.meetings-header-icon.history {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

.meetings-header-info {
    flex: 1;
    min-width: 200px;
}

.meetings-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.meetings-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Status Badge */
.meetings-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.meetings-status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.meetings-status-badge.approved {
    background: #dcfce7;
    color: #166534;
}

.meetings-status-badge i {
    font-size: 10px;
}

/* Quick Stats */
.meetings-quick-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.meetings-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #ffffff;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    min-width: 90px;
}

.meetings-header.history .meetings-stat-item {
    border-color: #bbf7d0;
}

.meetings-stat-item .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #ea580c;
    line-height: 1;
}

.meetings-header.history .meetings-stat-item .stat-value {
    color: #16a34a;
}

.meetings-stat-item .stat-label {
    font-size: 11px;
    color: #9a3412;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.meetings-header.history .meetings-stat-item .stat-label {
    color: #166534;
}

/* Header Actions */
.meetings-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-meetings-action {
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

.btn-meetings-action.primary {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    color: #ffffff;
}

.btn-meetings-action.primary:hover {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
}

.btn-meetings-action.secondary {
    background: #ffffff;
    color: #ea580c;
    border: 2px solid #fed7aa;
}

.btn-meetings-action.secondary:hover {
    background: #fff7ed;
    border-color: #fdba74;
    color: #c2410c;
    text-decoration: none;
}

.btn-meetings-action.history {
    background: #ffffff;
    color: #16a34a;
    border: 2px solid #bbf7d0;
}

.btn-meetings-action.history:hover {
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
    text-decoration: none;
}

/* Filter Bar */
.meetings-filter-bar {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 14px 32px;
    background: #fff7ed;
    border-top: 1px solid #fed7aa;
    flex-wrap: wrap;
}

.meetings-header.history .meetings-filter-bar {
    background: #f0fdf4;
    border-top-color: #bbf7d0;
}

.meetings-filter-bar .filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #9a3412;
    display: flex;
    align-items: center;
    gap: 8px;
}

.meetings-header.history .meetings-filter-bar .filter-label {
    color: #166534;
}

.meetings-filter-bar .filter-label i {
    color: #f97316;
}

.meetings-header.history .meetings-filter-bar .filter-label i {
    color: #22c55e;
}

.meetings-filter-bar select {
    padding: 10px 36px 10px 14px;
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    min-width: 200px;
}

.meetings-header.history .meetings-filter-bar select {
    border-color: #bbf7d0;
}

.meetings-filter-bar select:hover {
    border-color: #f97316;
}

.meetings-header.history .meetings-filter-bar select:hover {
    border-color: #22c55e;
}

.meetings-filter-bar select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.meetings-header.history .meetings-filter-bar select:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

/* View Toggle */
.view-toggle {
    display: flex;
    gap: 4px;
    background: #ffffff;
    padding: 4px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-left: auto;
}

.view-toggle-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.view-toggle-btn:hover {
    color: #374151;
    background: #f1f5f9;
    text-decoration: none;
}

.view-toggle-btn.active {
    color: #ffffff;
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
}

.view-toggle-btn.active.history-active {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

/* Table Container */
.meetings-table-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.meetings-table-container table {
    margin-bottom: 0;
}

.meetings-table-container thead th {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    color: #9a3412;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: 2px solid #fed7aa;
}

.meetings-header.history + .meetings-table-container thead th {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #166534;
    border-bottom-color: #bbf7d0;
}

.meetings-table-container thead th input.search {
    display: block;
    margin-top: 8px;
    padding: 6px 10px;
    font-size: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    width: 100%;
    max-width: 180px;
}

.meetings-table-container thead th input.search:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.12);
}

.meetings-table-container tbody tr:hover {
    background: #fff7ed;
}

.meetings-table-container tbody td {
    padding: 14px 16px;
    vertical-align: top;
    border-bottom: 1px solid #f1f5f9;
}

/* Decision Row */
.meetings-table-container tr.Decisions th,
.meetings-table-container tr.Decisions td {
    background: linear-gradient(135deg, #fef7f0 0%, #ffedd5 100%) !important;
    border: none;
    border-bottom: 8px solid #ffffff;
    padding: 12px 16px;
}

.meetings-table-container tr.Decisions .subdirectory {
    padding: 12px 8px !important;
    text-align: center;
    vertical-align: middle;
}

.meetings-table-container tr.Decisions .subdirectory img {
    width: 20px;
    opacity: 0.5;
}

/* Action Buttons in Table */
.decision-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.decision-actions a,
.decision-actions .action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.decision-actions .action-btn.certificate {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.decision-actions .action-btn.certificate:hover {
    background: #fde68a;
}

.decision-actions .action-btn.reschedule {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.decision-actions .action-btn.reschedule:hover {
    background: #bae6fd;
}

.decision-actions .action-btn.cancel {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.decision-actions .action-btn.cancel:hover {
    background: #fecaca;
}

.decision-actions .action-btn.pdf {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.decision-actions .action-btn.pdf:hover {
    background: #fecaca;
}

.decision-actions .action-btn.edit {
    background: #ede9fe;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
}

.decision-actions .action-btn.edit:hover {
    background: #ddd6fe;
}

.decision-actions .reference-badge {
    padding: 4px 10px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 4px;
    font-size: 11px;
}

.decision-actions .reference-badge strong {
    color: #1e293b;
}

/* Committee Members List */
#committeeOl {
    margin: 0;
    padding: 0;
    list-style: none;
}

#committeeOl li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 13px;
    position: relative;
}

#committeeOl li .fa-signature {
    font-size: 14px !important;
}

#committeeOl li b {
    font-weight: 500;
}

/* Internal Memo */
.internal-memo {
    margin-top: 12px;
    padding: 12px;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 8px;
}

.internal-memo-title {
    font-size: 12px;
    font-weight: 600;
    color: #0f766e;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.internal-memo-item {
    font-size: 13px;
    color: #374151;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #99f6e4;
}

.internal-memo-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.internal-memo-item .author {
    color: #0f766e;
    font-weight: 500;
}

/* Meeting Details Card */
.meeting-info {
    font-size: 13px;
    line-height: 1.6;
}

.meeting-info .info-row {
    display: flex;
    gap: 8px;
    margin-bottom: 4px;
}

.meeting-info .info-row strong {
    color: #374151;
    min-width: 100px;
}

.meeting-info .info-row span {
    color: #64748b;
}

.meeting-info .zoom-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    background: #eff6ff;
    border-radius: 4px;
    color: #1d4ed8;
    text-decoration: none;
}

.meeting-info .zoom-link:hover {
    background: #dbeafe;
}

.meeting-info .zoom-link img {
    width: 16px;
}

/* Company Info */
.company-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.company-info .company-name {
    font-weight: 600;
    color: #1e293b;
    cursor: pointer;
}

.company-info .company-name:hover {
    color: #ea580c;
}

.company-info .company-address {
    font-size: 12px;
    color: #64748b;
}

/* Branch Info */
.branch-info {
    font-size: 13px;
    line-height: 1.6;
}

.branch-info .info-row {
    display: flex;
    gap: 4px;
}

.branch-info .info-row strong {
    color: #f97316;
    min-width: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .meetings-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .meetings-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .meetings-quick-stats {
        justify-content: center;
    }
    
    .meetings-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .meetings-filter-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 20px;
    }
    
    .view-toggle {
        margin-left: 0;
        width: 100%;
    }
    
    .view-toggle-btn {
        flex: 1;
        justify-content: center;
    }
    
    .meetings-table-container {
        overflow-x: auto;
    }
    
    .decision-actions {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<?php
$isHistory = isset($_GET['status']) && $_GET['status'];
if ($isHistory) {
    $_POST['status'] = "hqc_committee_decision.status = 'approved' ";
    $title = 'Meeting History';
    $description = 'View completed and approved committee meetings';
} else {
    $_POST['status'] = "hqc_committee_decision.status = 'pending'";
    $title = 'Pending Meetings';
    $description = 'New committee meeting requests awaiting approval';
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

$offices = array();
if ($officesAll = get_offices()) {
    foreach ($officesAll as $office) {
        $offices[$office['offid']]['branch'] = $office['company_name_english'];
        $offices[$office['offid']]['manager'] = $office['contact_person'];
    }
    ksort($offices);
}

// Get meeting counts
$pendingCount = 0;
$approvedCount = 0;
$comemSqlCount = '';
if (isset($_SESSION['comemid']) && $_SESSION['super_admin'] != 'yes')
    $comemSqlCount = "AND FIND_IN_SET('$_SESSION[comemid]',comemids)";
if ($_SESSION['offid'] != 0)
    $comemSqlCount .= " AND hqc_committee_decision.offid = $_SESSION[offid]";

if ($countResult = $amdb->get_row("SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
    FROM hqc_committee_decision WHERE 1=1 $comemSqlCount")) {
    $pendingCount = $countResult['pending'] ?: 0;
    $approvedCount = $countResult['approved'] ?: 0;
}
?>

<script>
    $("#page_title").html("<?php echo $title; ?>");
    
    async function resetDMCProcess(decid, status) {
        var message = status == 'delete' 
            ? "Are you sure you want to cancel this DMC meeting?" 
            : "Are you sure you want to undo this DMC decision?";
        await confirm_message(message);
        $.ajax({
            url: "/committee/app_save.php",
            type: "POST",
            data: { act: "resetDMCProcess", decid: decid, status: status },
            success: function(data) {
                if (data == "success") {
                    location.reload();
                } else {
                    alert_message(data);
                }
            }
        });
    }

    function selectOffices(offid) {
        if (offid == '*') {
            jQuery("#committeeTable tbody tr").show();
        } else {
            jQuery("#committeeTable tbody tr").hide();
            jQuery("#committeeTable tbody tr[data-offid='" + offid + "']").show();
        }
    }

    function editDMC(decid) {
        var url = '/committee/dmc/?inc=dmc&act=edit&ref=reprint&decid=' + decid;
        jQuery("#DMCUrl").attr('data-href', url).click();
    }
</script>

<style>
    .hocMaster { background: green; cursor: default; }
    .hocMember { background: #900; cursor: default; }
    .actions i { margin-bottom: 6px !important; }
</style>

<input type="hidden" id="DMCUrl" data-href="" title="Edit DMC Report" data-resize="true" onclick="doIframe(this)">

<div class="meetings-header <?php echo $isHistory ? 'history' : ''; ?>">
    <div class="meetings-header-content">
        <div class="meetings-header-icon <?php echo $isHistory ? 'history' : ''; ?>">
            <i class="fas <?php echo $isHistory ? 'fa-history' : 'fa-calendar-check'; ?>"></i>
        </div>
        
        <div class="meetings-header-info">
            <h2>
                <?php echo $title; ?>
                <span class="meetings-status-badge <?php echo $isHistory ? 'approved' : 'pending'; ?>">
                    <i class="fas <?php echo $isHistory ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                    <?php echo $isHistory ? 'Approved' : 'Pending'; ?>
                </span>
            </h2>
            <p><?php echo $description; ?></p>
        </div>
        
        <div class="meetings-quick-stats">
            <div class="meetings-stat-item">
                <span class="stat-value"><?php echo $isHistory ? $approvedCount : $pendingCount; ?></span>
                <span class="stat-label"><?php echo $isHistory ? 'Completed' : 'Pending'; ?></span>
            </div>
        </div>
        
        <div class="meetings-header-actions">
            <?php if (!$isHistory) { ?>
                <a href="?inc=meetings&status=approved" class="btn-meetings-action history">
                    <i class="fas fa-history"></i>
                    View History
                </a>
            <?php } else { ?>
                <a href="?inc=meetings" class="btn-meetings-action secondary">
                    <i class="fas fa-clock"></i>
                    View Pending
                </a>
            <?php } ?>
        </div>
    </div>
    
    <?php if ($_SESSION['user_type'] == 'hqc_user') { ?>
    <div class="meetings-filter-bar">
        <span class="filter-label">
            <i class="fas fa-filter"></i>
            Filter by Branch:
        </span>
        <select onchange="selectOffices(this.value)">
            <option value="*">All Branches</option>
            <?php foreach ($offices as $offid => $office) { ?>
                <option value="<?php echo $offid; ?>"><?php echo htmlspecialchars($office['branch']); ?></option>
            <?php } ?>
        </select>
        
        <div class="view-toggle">
            <a href="?inc=meetings" class="view-toggle-btn <?php echo !$isHistory ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i>
                Pending
            </a>
            <a href="?inc=meetings&status=approved" class="view-toggle-btn <?php echo $isHistory ? 'active history-active' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                Approved
            </a>
        </div>
    </div>
    <?php } ?>
</div>

<div class="meetings-table-container">
    <table class="table table-striped table-bordered" id="committeeTable" style="width:100%; margin-bottom: 0;">
        <thead>
            <tr>
                <th style="width:50px">No</th>
                <th style="min-width: 280px;">
                    Company
                    <input type="text" class="search" data-search="company" placeholder="Search company...">
                </th>
                <th style="min-width: 200px;">
                    Branch / Manager
                    <input type="text" class="search" data-search="branch" placeholder="Search branch...">
                </th>
                <th style="min-width: 220px;">
                    Meeting Details
                    <input type="text" class="search" data-search="meeting" placeholder="Search details...">
                </th>
                <th style="min-width: 200px;">
                    Committee Members
                    <input type="text" class="search" data-search="committee" placeholder="Search members...">
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            $committee_member = array();
            $comIds = array();
            if ($committee_members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
                foreach ($committee_members as $member) {
                    $funcTitle = isset($functions[$member['member_function']]) ? $functions[$member['member_function']] : $member['member_function'];
                    $committee_member[$member['comemid']] = array(
                        'name' => $member['member_name'],
                        'function' => $member['member_function'],
                        'title' => $funcTitle
                    );
                    $comIds[] = $member['comemid'];
                }
            }
            
            $srNr = 1;
            $comemSql = '';
            if (isset($_SESSION['comemid']) && $_SESSION['super_admin'] != 'yes')
                $comemSql = "AND FIND_IN_SET('$_SESSION[comemid]',comemids)";
            if ($_SESSION['offid'] != 0)
                $comemSql .= " AND hqc_committee_decision.offid = $_SESSION[offid]";

            if ($meetings = $amdb->get_results("SELECT *, hqc_committee_decision.status AS status FROM hqc_committee_decision
                JOIN companies ON companies.clid = hqc_committee_decision.clid 
                WHERE $_POST[status] $comemSql ORDER BY meeting_date DESC")) {
                
                foreach ($meetings as $meeting) {
                    $decision = unserialize($meeting['decision']);
                    $sms_codes = !is_null($meeting['sms_codes']) ? json_decode($meeting['sms_codes'], true) : array();
            ?>
                    <tr data-offid="<?php echo $meeting['offid']; ?>">
                        <td style="text-align: center; font-weight: 600;"><?php echo $srNr++; ?></td>
                        <td class="company">
                            <div class="company-info">
                                <span class="company-name load_popup" data-url="../../admin/load_company.php?clid=<?php echo $meeting['clid']; ?>" title="<?php echo htmlspecialchars($meeting['company_name']); ?>">
                                    <?php echo htmlspecialchars($meeting['company_name']); ?>
                                </span>
                                <?php if ($company = get_client($meeting['clid'])) { ?>
                                    <span class="company-address"><?php echo $company['client_address']; ?></span>
                                <?php } ?>
                            </div>
                        </td>
                        <td class="branch">
                            <div class="branch-info">
                                <?php
                                if (trim($meeting['branch']) != '') {
                                    $branch = json_decode($meeting['branch'], true);
                                ?>
                                    <div class="info-row"><strong>B:</strong> <?php echo htmlspecialchars($branch['Branch']); ?></div>
                                    <div class="info-row"><strong>M:</strong> <?php echo htmlspecialchars($branch['BranchManager']); ?></div>
                                    <div class="info-row"><strong>R:</strong> <?php echo !strstr($branch['RequestedBy'], 'Warning') ? htmlspecialchars($branch['RequestedBy']) : 'N/A'; ?></div>
                                <?php } elseif (isset($offices[$meeting['offid']])) { ?>
                                    <div class="info-row"><strong>B:</strong> <?php echo htmlspecialchars($offices[$meeting['offid']]['branch']); ?></div>
                                    <div class="info-row"><strong>M:</strong> <?php echo htmlspecialchars($offices[$meeting['offid']]['manager']); ?></div>
                                <?php } ?>
                            </div>
                        </td>
                        <td class="meeting">
                            <div class="meeting-info">
                                <?php
                                if (is_array(json_decode($meeting['event_details'], true))) {
                                    $event_details = json_decode($meeting['event_details'], true);
                                ?>
                                    <div class="info-row"><strong>Date:</strong> <span><?php echo date("d/m/Y", strtotime($event_details['date'])); ?></span></div>
                                    <div class="info-row"><strong>Time:</strong> <span><?php echo $event_details['time']; ?></span></div>
                                    <div class="info-row"><strong>Location:</strong> <span><?php echo $event_details['location']; ?></span></div>
                                    <?php if (isset($event_details['zoom-link'])) { ?>
                                        <div class="info-row">
                                            <strong>Zoom:</strong>
                                            <a href="<?php echo $event_details['zoom-link']; ?>" target="_new" class="zoom-link">
                                                <img src="/images/zoom.svg" alt="Zoom"> Join Meeting
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <div class="info-row"><strong>Requested:</strong> <span><?php echo date("d M Y", strtotime($meeting['inserted_on'])); ?></span></div>
                                    <?php if (isset($event_details['request_by'])) { ?>
                                        <div class="info-row"><strong>By:</strong> <span><?php echo htmlspecialchars($event_details['request_by']); ?></span></div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </td>
                        <td class="committee">
                            <ol id="committeeOl">
                                <?php 
                                $members = explode(',', $meeting['comemids']);
                                foreach ($members as $member) {
                                    if (isset($committee_member[$member])) {
                                        $isSigned = $meeting['status'] == 'approved';
                                ?>
                                    <li>
                                        <i class="fas fa-signature" style="color: <?php echo $isSigned ? '#16a34a' : '#d1d5db'; ?>;"></i>
                                        <b title="<?php echo $committee_member[$member]['title']; ?>">
                                            <?php echo htmlspecialchars($committee_member[$member]['name']); ?>
                                        </b>
                                        <span style="color: #64748b; font-size: 11px;">(<?php echo $committee_member[$member]['function']; ?>)</span>
                                    </li>
                                <?php 
                                    }
                                }
                                ?>
                            </ol>
                            
                            <?php if (trim($meeting['internal_memo']) != '' && is_array(unserialize($meeting['internal_memo']))) { ?>
                                <div class="internal-memo">
                                    <div class="internal-memo-title">
                                        <i class="fas fa-thumbtack"></i>
                                        Internal Memo
                                    </div>
                                    <?php
                                    $memo = unserialize($meeting['internal_memo']);
                                    foreach ($memo as $comemid => $mem) {
                                        if (isset($committee_member[$comemid])) {
                                    ?>
                                        <div class="internal-memo-item">
                                            <span class="author"><?php echo htmlspecialchars($committee_member[$comemid]['name']); ?>:</span>
                                            <?php echo htmlspecialchars($mem); ?>
                                        </div>
                                    <?php 
                                        }
                                    }
                                    ?>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="Decisions" data-offid="<?php echo $meeting['offid']; ?>">
                        <td class="subdirectory"><img src="/images/subdirectory.svg" alt=""></td>
                        <td colspan="4">
                            <div class="decision-actions">
                                <strong style="margin-right: 8px;"><?php echo $meeting['status'] == 'pending' ? 'Actions:' : 'Decision:'; ?></strong>
                                
                                <?php
                                $pdfUrl = "";
                                if (isset($decision['crtNr'])) {
                                    $crtNr = $decision['crtNr'];
                                    $certificate = $amdb->get_row("SELECT url FROM acms_halal_certificates WHERE crtNr = '$crtNr'");
                                    if (isset($certificate['url']) && trim($certificate['url']) != '') {
                                        $pdfUrl = "/client_data/certificates/$certificate[url]";
                                    }
                                }
                                if ($pdfUrl == "") {
                                    $pdfUrl = "/certificates/annual/?inc=certificate_add_edit&act=" . ($meeting['status'] == 'pending' ? 'add' : 'edit') . "&crtNr=$meeting[crtNr]&clid=$meeting[clid]&offid=$meeting[offid]&decid=$meeting[decid]";
                                }
                                ?>
                                
                                <a href="<?php echo $pdfUrl; ?>" <?php echo $meeting['status'] != 'pending' ? 'target="_new"' : ''; ?> class="action-btn certificate">
                                    <i class="fas fa-certificate"></i>
                                    <?php echo $meeting['status'] == 'pending' ? 'Request Certificate' : 'View Certificate'; ?>
                                </a>
                                
                                <?php if ($meeting['status'] == 'pending') { ?>
                                    <a href="/committee/index.php?inc=schedule_committee&decid=<?php echo $meeting['decid']; ?>&act=reschedule&crtNr=<?php echo $meeting['crtNr']; ?>&clid=<?php echo $meeting['clid']; ?>" class="action-btn reschedule" title="Reschedule committee meeting">
                                        <i class="fa fa-user-clock"></i>
                                        Reschedule
                                    </a>
                                    <span class="action-btn cancel" onclick="resetDMCProcess(<?php echo $meeting['decid']; ?>,'delete');">
                                        <i class="fas fa-trash-alt"></i>
                                        Cancel Meeting
                                    </span>
                                <?php } ?>
                                
                                <?php if ($meeting['status'] == 'approved') {
                                    $dmc_file = '/data/DMC/reports/dmc-' . $meeting['decid'] . '.pdf';
                                    if (file_exists($root_path . $dmc_file)) {
                                ?>
                                    <a href="<?php echo $dmc_file; ?>" target="_new" class="action-btn pdf">
                                        <i class="far fa-file-pdf"></i>
                                        Download PDF
                                    </a>
                                    <span class="action-btn edit" onclick="editDMC(<?php echo $meeting['decid']; ?>);">
                                        <i class="fas fa-edit"></i>
                                        Edit Report
                                    </span>
                                <?php }
                                } ?>
                                
                                <span class="reference-badge">
                                    <strong>Ref:</strong> <?php echo trim($meeting['dmr_reference']) != '' ? htmlspecialchars($meeting['dmr_reference']) : 'N/A'; ?>
                                </span>
                            </div>
                        </td>
                    </tr>
            <?php 
                }
            } else {
            ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 60px 20px;">
                        <i class="fas <?php echo $isHistory ? 'fa-inbox' : 'fa-calendar-check'; ?>" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                        <h3 style="color: #64748b; font-weight: 600; margin: 0 0 8px 0;">
                            <?php echo $isHistory ? 'No Completed Meetings' : 'No Pending Meetings'; ?>
                        </h3>
                        <p style="color: #94a3b8; margin: 0;">
                            <?php echo $isHistory ? 'Completed meetings will appear here.' : 'All meeting requests have been processed.'; ?>
                        </p>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    jQuery(document).ready(function($) {
        $(".search").focus(function() {
            $(".search").val('');
            $("#committeeTable tbody tr").show();
        });
        
        $(".search").keyup(function() {
            var search = $(this).val().toLowerCase();
            var dataType = $(this).data('search');
            
            $("#committeeTable tbody tr").hide();
            $("#committeeTable tbody tr").each(function() {
                if ($(this).find("." + dataType).text().toLowerCase().indexOf(search) > -1) {
                    $(this).show();
                    $(this).next('.Decisions').show();
                }
            });
        });
    });
</script>