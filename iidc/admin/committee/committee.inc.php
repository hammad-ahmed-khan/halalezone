<?php if (!defined("_HQC_")) {
    exit();
};
if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] != 'yes')
    exit();
?>
<style>
    i.fas.fa-signature {
        cursor: default;
    }

    .linked {
        text-align: center;
    }
</style>
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
                serializeTable('#hqc_committee_members')
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
        await confirm_message("Are you sure you want to change this member's status?");
        $.post("committee_save.php", {
            act: "set_super_admin",
            comemid: id,
            admin: newStatus
        }, function(data) {
            if (data == 'success') {
                if (newStatus == 'yes') {
                    $(obj).css('color', 'green');
                } else {
                    $(obj).css('color', 'grey');
                }
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

<?php
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
    return '<i class="fas fa-signature" style="color:#eee"></i>';
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);
?>
<h2 class="content_title" style="text-align:center">Decision Making Committee Members</h2>
<table id="hqc_committee_members" class="alternateOn">
    <thead>
        <tr>
            <th style="width:20px">Nr</th>
            <th>Member name</th>
            <th title="Branch/Manager">B/M</th>
            <th>Function</th>
            <th>Office(s)</th>
            <th>Mobile Phone(s)</th>
            <th>Evaluation</th>
            <th style="width:40px;"><i class="fas fa-signature"></i></th>
            <th style="width:180px">Actions</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th style="width:20px">Nr</th>
            <th>Member name</th>
            <th title="Branch/Manager">B/M</th>
            <th>Function</th>
            <th>Office(s)</th>
            <th>Mobile Phone(s)</th>
            <th>Evaluation</th>
            <th><i class="fas fa-signature"></i></th>
            <th style="width:180px">Actions</th>
        </tr>
    </tfoot>
    <tbody>
        <?php
        $nr = 0;
        if ($result = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status!='deleted' ORDER BY member_name ASC")) {
            foreach ($result as $row) {
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
                <tr data-id="<?php echo $row['comemid']; ?>">
                    <th class="srNr"><?php echo $nr; ?></th>
                    <td class="member_name"><?php echo @$row['member_name']; ?></td>
                    <td class="linked" data-comemid="<?php echo $row['comemid']; ?>" data-offid_uid_bm="<?php echo $row['offid'] . '_' . $row['uid'] . '_' . $row['bm']; ?>" <?php echo $title;?>><?php echo trim($row['bm']) == 'yes' ? '<i class="fas fa-user-tag" style="color:green"></i>' : '<i class="far fa-user" style="color:' . ($row['uid'] != '' ? 'green' : '') . '"></i>'; ?></td>
                    <td><?php echo isset($functions[$row['member_function']]) ? $functions[$row['member_function']] : $row['member_function']; ?></td>
                    <td><?php
                        if (trim($row['member_offices']) != '') { ?>
                            <ul style="padding: 0px;margin:0px">
                                <?php
                                if (trim($row['member_offices']) != '' && $offices = $amdb->get_results("SELECT office_name,offid FROM offices WHERE FIND_IN_SET(offid, '$row[member_offices]')")) {
                                    foreach ($offices as $office) {
                                        echo '<li style="padding:2px"><i class="far fa-building"></i> ' . $office['office_name'] . '</li>';
                                    }
                                } ?>
                            </ul>
                        <?php  } ?>
                    </td>
                    <td><span style="display:inline-block;width:100px"><?php echo @$row['member_mobile_phone']; ?></span><?php echo trim($row['member_telephone']) != '' ? '<br/>' . $row['member_telephone'] : ''; ?></td>
                    <td>
                        <?php
                        $evaluation = trim($row['evaluation']) != '' ? unserialize($row['evaluation']) : [];    // Get the evaluation data
                        $stars = $evaluation['finalRating'] ?? 0;
                        ?>
                        <span class="stars_<?php echo $stars; ?> rating-starts" onclick="evaluate_member(<?php echo $row['comemid']; ?>)"></span>
                    </td>
                    <td><?php echo get_user_signature($row['comemid']); ?></td>
                    <td class="nowrap">
                        <!-- edit tools using awesome font icons -->
                        <i class="fas fa-sign-in-alt" onclick="signinAs(<?php echo $row['comemid']; ?>)"></i>
                        <a href="mailto:<?php echo @$row['member_email']; ?>" style="margin-right:20px"><i class="far fa-envelope"></i></a>
                        <i class="fa fa-edit" title="Edit" onclick="doEditMember(<?php echo $row['comemid']; ?>)"></i>
                        <i class="fa fa-trash-alt" title="Delete" onclick="doDeleteMember(<?php echo $row['comemid']; ?>)"></i>
                        <i class="fas fa-user-cog" style="color:<?php echo $row['super_admin'] == 'yes' ? 'green' : 'grey'; ?>" data-admin="<?php echo $row['super_admin']; ?>" onclick="setSuperAdmin(this)"></i>
                        <i class="fas fa-toggle-<?php echo ($row['status'] == 'active') ? 'on' : 'off'; ?>" title="Change status" onclick="doChangeMemberStatus(this)"></i>
                    </td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>
<div style="text-align: center;">
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != 'committee_member') { ?>
        <a href="index.php?inc=committee_member_add_edit&act=add" class="button">Add new member</a>
    <?php } else {
        echo 'To add new committee member you should do it from the main system.<br/>';
    }; ?>
    <a href="/committee/index.php?inc=send_email&act=sendEmail" class="button" target="iframe" data-width="800" data-height="580" title="Send email to DMC members">Send email to DMC members</a>
</div>
<script>
    $(document).ready(function() {
        jQuery('.linked').click(function() {
            //read the data-uid and data-comemid attributes
            doLoadPopup("/admin/committee/linked.php", {
                act: 'link_user',
                comemid: $(this).data('comemid'),
                offid_uid_bm: $(this).data('offid_uid_bm')
            }, $(this).parent().find('.member_name').text());
        });
    });
</script>