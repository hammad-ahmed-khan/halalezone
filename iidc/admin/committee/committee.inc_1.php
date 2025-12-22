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
</script>
<?php
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
?>
<h2 class="content_title" style="text-align:center">Decision Making Committee Members</h2>
<table id="hqc_committee_members" class="alternateOn">
    <thead>
        <tr>
            <th style="width:20px">Nr</th>
            <th>Member name</th>
            <th>Function</th>
            <th>Office(s)</th>
            <th>Email</th>
            <th>Mobile Phone(s)</th>
            <th style="width:40px;"><i class="fas fa-signature"></i></th>
            <th style="width:180px">Actions</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th style="width:20px">Nr</th>
            <th>Member name</th>
            <th>Function</th>
            <th>Office(s)</th>
            <th>Email</th>
            <th>Mobile Phone(s)</th>
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
        ?>
                <tr data-id="<?php echo $row['comemid']; ?>">
                    <th class="srNr"><?php echo $nr; ?></th>
                    <td><?php echo @$row['member_name']; ?></td>
                    <td><?php echo @$row['member_function']; ?></td>
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
                    <td><a href="mailto:<?php echo @$row['member_email']; ?>">Email user</a></td>
                    <td><span style="display:inline-block;width:100px"><?php echo @$row['member_mobile_phone']; ?></span><?php echo trim($row['member_telephone']) != '' ? ' / ' . $row['member_telephone'] : ''; ?></td>
                    <td><?php echo get_user_signature($row['comemid']); ?></td>
                    <td>
                        <!-- edit tools using awesome font icons -->
                        <i class="fas fa-sign-in-alt" onclick="signinAs(<?php echo $row['comemid']; ?>)" style="margin-right:20px"></i>
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
<div style="text-align: center;"><a class="button" href="?inc=committee_member_add_edit&act=add">Add new member</a>
    <a href="/committee/index.php?inc=send_email&act=sendEmail" class="button" target="iframe" data-width="800" data-height="580" title="Send email to DMC members">Send email to DMC members</a>
</div>