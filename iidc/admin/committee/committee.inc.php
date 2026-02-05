<?php if (!defined("_HQC_")) {
    exit();
};
?>
<style>
/* DMC Page Header */
.dmc-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef7f0 100%);
    border-radius: 12px;
    border: 1px solid #fed7aa;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.dmc-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.dmc-header-icon {
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

.dmc-header-info {
    flex: 1;
    min-width: 200px;
}

.dmc-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.dmc-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* DMC Badge */
.dmc-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #ffedd5;
    color: #c2410c;
}

.dmc-badge i {
    font-size: 10px;
}

/* Quick Stats */
.dmc-quick-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.dmc-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #ffffff;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    min-width: 90px;
}

.dmc-stat-item .stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #ea580c;
    line-height: 1;
}

.dmc-stat-item .stat-label {
    font-size: 11px;
    color: #9a3412;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.dmc-stat-item.active .stat-value { color: #16a34a; }
.dmc-stat-item.active { border-color: #bbf7d0; background: #f0fdf4; }

.dmc-stat-item.inactive .stat-value { color: #64748b; }
.dmc-stat-item.inactive { border-color: #e2e8f0; background: #f8fafc; }

/* Header Actions */
.dmc-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-dmc-action {
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

.btn-dmc-action.primary {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    color: #ffffff;
}

.btn-dmc-action.primary:hover {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
}

.btn-dmc-action.secondary {
    background: #ffffff;
    color: #ea580c;
    border: 2px solid #fed7aa;
}

.btn-dmc-action.secondary:hover {
    background: #fff7ed;
    border-color: #fdba74;
    color: #c2410c;
    text-decoration: none;
}

.btn-dmc-action.email {
    background: #eff6ff;
    color: #1d4ed8;
    border: 2px solid #bfdbfe;
}

.btn-dmc-action.email:hover {
    background: #dbeafe;
    border-color: #93c5fd;
}

/* Info Bar */
.dmc-info-bar {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 14px 32px;
    background: #fff7ed;
    border-top: 1px solid #fed7aa;
    flex-wrap: wrap;
}

.dmc-info-bar .info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #9a3412;
}

.dmc-info-bar .info-item i {
    color: #f97316;
}

/* Function Legend */
.dmc-function-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.dmc-function-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    color: #475569;
}

.dmc-function-item .function-code {
    font-weight: 700;
    color: #ea580c;
}

/* Table Enhancement */
.dmc-table-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.dmc-table-container table {
    margin-bottom: 0;
}

.dmc-table-container thead th {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    color: #9a3412;
    font-weight: 600;
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: 2px solid #fed7aa;
    white-space: nowrap;
}

.dmc-table-container tbody tr:hover {
    background: #fff7ed;
}

.dmc-table-container tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.dmc-table-container tfoot th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    padding: 12px 16px;
    font-size: 12px;
    border-top: 1px solid #e2e8f0;
}

/* Action Icons */
.dmc-table-container .nowrap i {
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px !important;
}

.dmc-table-container .nowrap i:hover {
    background: #f1f5f9;
}

.dmc-table-container .nowrap i.fa-edit:hover { color: #0369a1; }
.dmc-table-container .nowrap i.fa-trash-alt:hover { color: #dc2626; }
.dmc-table-container .nowrap i.fa-sign-in-alt:hover { color: #16a34a; }
.dmc-table-container .nowrap i.fa-user-cog:hover { background: #f0fdf4; }

/* Footer Actions */
.dmc-footer-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    padding: 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .dmc-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .dmc-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .dmc-quick-stats {
        justify-content: center;
    }
    
    .dmc-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .dmc-info-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 20px;
    }
    
    .dmc-function-legend {
        justify-content: center;
    }
    
    .dmc-footer-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .dmc-table-container {
        overflow-x: auto;
    }
}    
</style>
<?php if (!defined("_HQC_")) {
    exit();
};
if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] != 'yes')
    exit();

$offices_all = $amdb->get_results("SELECT offid,office_name FROM offices WHERE status='active' ORDER BY office_name ASC");
$offices_all = array_column($offices_all, 'office_name', 'offid');

function get_user_signature($comemid)
{
    global $prog_path;
    $image_file = '/data/DMC/signatures/' . $comemid . '_signature';
    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {
        if (file_exists("$prog_path" . $image_file . $ext))
            return '<i class="fas fa-signature" style="color:green"></i>';
    }
    return '<i class="fas fa-signature" style="color:#ccc"></i>';
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

// Get member statistics
$totalMembers = 0;
$activeMembers = 0;
$inactiveMembers = 0;
$membersData = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status!='deleted' ORDER BY member_name ASC");
if ($membersData) {
    $totalMembers = count($membersData);
    foreach ($membersData as $member) {
        if ($member['status'] == 'active') {
            $activeMembers++;
        } else {
            $inactiveMembers++;
        }
    }
}
?>

<script type="text/javascript">
    $("#page_title").html("Decision Making Committee");

    function doEditMember(id) {
        location = "index.php?inc=committee_member_add_edit&act=edit&comemid=" + id;
    }

    async function doDeleteMember(id) {
        await confirm_message("Are you sure you want to delete this member?");
        $.post("committee_save.php", {
            act: "delete_committee_member",
            comemid: id
        }, function(data) {
            if (data == 'success') {
                $("#hqc_committee_members tr[data-id='" + id + "']").remove();
                serializeTable('#hqc_committee_members');
                // Update stats
                location.reload();
            } else {
                alert_message("Error deleting member!");
            }
        });
    }

    function doChangeMemberStatus(obj) {
        newStatus = $(obj).hasClass('fa-toggle-on') ? 'disabled' : 'active';
        id = $(obj).closest('tr').attr('data-id');
        $.post("committee_save.php", {
            act: "change_committee_member_status",
            comemid: id,
            status: newStatus
        }, function(data) {
            if (data == 'success') {
                $(obj).toggleClass('fa-toggle-on fa-toggle-off');
            } else {
                alert_message("Error changing member status!");
            }
        });
    }

    async function setSuperAdmin(obj) {
        newStatus = $(obj).data('admin') == 'yes' ? 'no' : 'yes';
        id = $(obj).closest('tr').attr('data-id');
        if (id == 2) {
            alert_message("You can't change the status of this member!");
            return false;
        }
        await confirm_message("Are you sure you want to change this member's admin status?");
        $.post("committee_save.php", {
            act: "set_super_admin",
            comemid: id,
            admin: newStatus
        }, function(data) {
            if (data == 'success') {
                $(obj).css('color', newStatus == 'yes' ? 'green' : 'grey');
                $(obj).data('admin', newStatus);
            } else {
                alert_message("Error changing member status!");
            }
        });
    }

    function signinAs(id) {
        $.post("/committee/committee_save.php", {
            act: "signin_as",
            comemid: id
        }, function(data) {
            if (data == 'success') {
                location = "/committee/";
            } else {
                alert_message(data);
            }
        });
    }

    function evaluate_member(id) {
        location = "index.php?inc=committee_evaluate&comemid=" + id;
    }
</script>

<style>
    i.fas.fa-signature { cursor: default; }
    .linked { text-align: center; cursor: pointer; }
    .linked:hover { background: #fff7ed; }
</style>

<div class="dmc-header">
    <div class="dmc-header-content">
        <div class="dmc-header-icon">
            <i class="fas fa-users-cog"></i>
        </div>
        
        <div class="dmc-header-info">
            <h2>
                Decision Making Committee
                <span class="dmc-badge">
                    <i class="fas fa-gavel"></i>
                    DMC
                </span>
            </h2>
            <p>Manage committee members, roles, and permissions</p>
        </div>
        
        <div class="dmc-quick-stats">
            <div class="dmc-stat-item">
                <span class="stat-value"><?php echo $totalMembers; ?></span>
                <span class="stat-label">Total</span>
            </div>
            <div class="dmc-stat-item active">
                <span class="stat-value"><?php echo $activeMembers; ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="dmc-stat-item inactive">
                <span class="stat-value"><?php echo $inactiveMembers; ?></span>
                <span class="stat-label">Inactive</span>
            </div>
        </div>
        
        <div class="dmc-header-actions">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != 'committee_member') { ?>
                <a href="index.php?inc=committee_member_add_edit&act=add" class="btn-dmc-action primary">
                    <i class="fas fa-user-plus"></i>
                    Add Member
                </a>
            <?php } ?>
            <a href="/committee/index.php?inc=send_email&act=sendEmail" class="btn-dmc-action email iframe" data-width="800" data-height="580" title="Send email to DMC members">
                <i class="fas fa-envelope"></i>
                Send Email
            </a>
        </div>
    </div>
    
    <div class="dmc-info-bar">
        <div class="info-item">
            <i class="fas fa-info-circle"></i>
            <span>Member Functions:</span>
        </div>
        <div class="dmc-function-legend">
            <?php foreach ($functions as $code => $name) { ?>
                <span class="dmc-function-item">
                    <span class="function-code"><?php echo $code; ?></span>
                    <?php echo $name; ?>
                </span>
            <?php } ?>
        </div>
    </div>
</div>

<div class="dmc-table-container">
    <table id="hqc_committee_members" class="table table-striped table-bordered" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th style="width:50px">Nr</th>
                <th>Member Name</th>
                <th title="Branch/Manager" style="width:60px; text-align:center;">B/M</th>
                <th>Function</th>
                <th>Office(s)</th>
                <th>Contact</th>
                <th style="width:100px">Evaluation</th>
                <th style="width:50px; text-align:center;"><i class="fas fa-signature" title="Signature"></i></th>
                <th style="width:180px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nr = 0;
            if ($membersData) {
                foreach ($membersData as $row) {
                    $nr++;
                    if (trim($row['offid'])!='' && isset($offices_all[$row['offid']])) {
                        $title = $offices_all[$row['offid']];
                        if ($row['bm'] == 'yes') {
                            $title .= ' - Branch Manager';
                        }
                        $title = 'title="' . $title . '"';
                    } else {
                        $title = '';
                    }
            ?>
                    <tr data-id="<?php echo $row['comemid']; ?>" style="<?php echo $row['status'] != 'active' ? 'opacity: 0.6;' : ''; ?>">
                        <th class="srNr" style="text-align: center;"><?php echo $nr; ?></th>
                        <td class="member_name">
                            <strong><?php echo htmlspecialchars($row['member_name']); ?></strong>
                            <?php if ($row['status'] != 'active') { ?>
                                <span style="display: inline-block; padding: 2px 8px; background: #f1f5f9; color: #64748b; border-radius: 4px; font-size: 10px; margin-left: 8px;">INACTIVE</span>
                            <?php } ?>
                        </td>
                        <td class="linked" data-comemid="<?php echo $row['comemid']; ?>" data-offid_uid_bm="<?php echo $row['offid'] . '_' . $row['uid'] . '_' . $row['bm']; ?>" <?php echo $title; ?>>
                            <?php echo trim($row['bm']) == 'yes' ? '<i class="fas fa-user-tag" style="color:green" title="Branch Manager"></i>' : '<i class="far fa-user" style="color:' . ($row['uid'] != '' ? 'green' : '#ccc') . '" title="' . ($row['uid'] != '' ? 'Linked User' : 'Not Linked') . '"></i>'; ?>
                        </td>
                        <td>
                            <span style="padding: 4px 10px; background: #ffedd5; color: #9a3412; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                <?php echo isset($functions[$row['member_function']]) ? $row['member_function'] : '-'; ?>
                            </span>
                            <?php if (isset($functions[$row['member_function']])) { ?>
                                <br><small style="color: #64748b;"><?php echo $functions[$row['member_function']]; ?></small>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (trim($row['member_offices']) != '') { ?>
                                <ul style="padding: 0; margin: 0; list-style: none;">
                                    <?php
                                    if ($offices = $amdb->get_results("SELECT office_name,offid FROM offices WHERE FIND_IN_SET(offid, '$row[member_offices]')")) {
                                        foreach ($offices as $office) {
                                            echo '<li style="padding: 2px 0; font-size: 13px;"><i class="far fa-building" style="color: #f97316; margin-right: 6px;"></i>' . htmlspecialchars($office['office_name']) . '</li>';
                                        }
                                    } ?>
                                </ul>
                            <?php } else { ?>
                                <span style="color: #94a3b8;">-</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (trim($row['member_mobile_phone']) != '') { ?>
                                <div style="font-size: 13px;">
                                    <i class="fas fa-mobile-alt" style="color: #f97316; margin-right: 6px;"></i>
                                    <?php echo htmlspecialchars($row['member_mobile_phone']); ?>
                                </div>
                            <?php } ?>
                            <?php if (trim($row['member_telephone']) != '') { ?>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                    <i class="fas fa-phone" style="margin-right: 6px;"></i>
                                    <?php echo htmlspecialchars($row['member_telephone']); ?>
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $evaluation = trim($row['evaluation']) != '' ? unserialize($row['evaluation']) : [];
                            $stars = $evaluation['finalRating'] ?? 0;
                            ?>
                            <span class="stars_<?php echo $stars; ?> rating-starts" onclick="evaluate_member(<?php echo $row['comemid']; ?>)" style="cursor: pointer;" title="Click to evaluate"></span>
                        </td>
                        <td style="text-align: center;"><?php echo get_user_signature($row['comemid']); ?></td>
                        <td class="nowrap">
                            
                            <a href="mailto:<?php echo htmlspecialchars($row['member_email']); ?>" style="margin: 0 4px;"><i class="far fa-envelope" title="Send email" style="color: #0369a1;"></i></a>
                            <i class="fa fa-edit" title="Edit member" onclick="doEditMember(<?php echo $row['comemid']; ?>)" style="color: #0369a1;"></i>
                            <i class="fa fa-trash" title="Delete member" onclick="doDeleteMember(<?php echo $row['comemid']; ?>)" style="color: #dc2626;"></i>
                           <i class="fas fa-toggle-<?php echo ($row['status'] == 'active') ? 'on' : 'off'; ?>" title="<?php echo ($row['status'] == 'active') ? 'Active (click to disable)' : 'Disabled (click to activate)'; ?>" onclick="doChangeMemberStatus(this)" style="color: <?php echo ($row['status'] == 'active') ? '#16a34a' : '#94a3b8'; ?>;"></i>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9" style="text-align: center; font-weight: normal; color: #64748b;">
                    Total <?php echo $totalMembers; ?> member<?php echo $totalMembers != 1 ? 's' : ''; ?> 
                    (<?php echo $activeMembers; ?> active, <?php echo $inactiveMembers; ?> inactive)
                </th>
            </tr>
        </tfoot>
    </table>
    
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'committee_member') { ?>
    <div class="dmc-footer-actions">
        <div style="text-align: center; color: #64748b; font-size: 13px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
            To add new committee members, please access this page from the main system.
        </div>
    </div>
    <?php } ?>
</div>

<script>
    $(document).ready(function() {
        jQuery('.linked').click(function() {
            doLoadPopup("/admin/committee/linked.php", {
                act: 'link_user',
                comemid: $(this).data('comemid'),
                offid_uid_bm: $(this).data('offid_uid_bm')
            }, $(this).parent().find('.member_name').text());
        });
    });
</script>