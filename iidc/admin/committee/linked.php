<?php
if (!isset($_REQUEST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'update_user' && isset($_POST['comemid'])) {
    $data = ['uid' => $_POST['uid'], 'offid' => $_POST['offid']];

    if (isset($_POST['bm'])) {
        $data['bm'] = 'yes';
        $amdb->update('hqc_committee_members', ['bm' => 'no'], "comemid != $_POST[comemid] AND offid = $_POST[offid]");
    } else {
        $data['bm'] = 'no';
    }

    $amdb->update('hqc_committee_members', $data, "comemid = $_POST[comemid]");
    $amdb->post_results('/admin/committee/', 'url');
    exit();
}

if ($_REQUEST['act'] == 'getUsers' && isset($_REQUEST['offid_uid_bm'])) {
    $users = [];
    //explode offid to array offid and uid
    $offid_uid_bm = explode('_', $_REQUEST['offid_uid_bm']);
    $offid = $offid_uid_bm[0];

    if (isset($offid_uid_bm[1]))
        $uid = $offid_uid_bm[1];

    if (isset($offid_uid_bm[2]))
        $BM = $offid_uid_bm[2];

    if ($offid == '0') {
        if ($result = $amdb->get_results("SELECT * FROM $tbl[prefix]_hqc_admin_users WHERE active='y' ORDER BY username_owner ASC")) {
            foreach ($result as $row) {
                if (isset($uid) && $uid == $row['uid'])
                    $selected = 'selected';
                else
                    $selected = '';
                $users[$row['uid']] = '<option value="' . $row['uid'] . '" ' . $selected . '>' . $row['username_owner'] . '</option>';
            }
        }
        // asort($users);
        echo '<select name="uid" id="uid" data-required="yes" style="width:auto">';
        echo '<option value="">Select User</option>';
        foreach ($users as $uid => $name) {
            echo $name;
        }
        echo '</select>';
    } else {
        if ($user = $amdb->get_row("SELECT offid,contact_person FROM offices  WHERE offid='$offid'")) {

            if ($users = $amdb->get_results("SELECT name,offuid FROM offices_users WHERE status = 'active' AND offid='$offid'")) {
                echo '<select name="uid" id="uid" data-required="yes" style="width:auto">';
                echo '<option value="">Select User</option>';
                $all_users[$offid] = $user['contact_person'];
                foreach ($users as $user) {
                    $all_users[$user['offuid']] = $user['name'];
                }
                foreach ($all_users as $userUid => $name) {
                    if ($userUid == $uid)
                        $selected = 'selected';
                    else
                        $selected = '';
                    echo '<option value="' . $userUid . '" ' . $selected . '>' . $name . '</option>';
                }
                echo '</select>';
            } else {
?>
                <input type="hidden" name="uid" value="<?php echo $user['offid']; ?>">
                <strong style="font-size: 18px;"><?php echo $user['contact_person']; ?></strong>
<?php
            }
        }
    }
    if ($BM == 'yes')
        $checked = 'checked';
    else
        $checked = '';
    echo '<label><input type="checkbox" name="bm" value="yes" ' . $checked . '> Branch Manager</label>';
    exit();
}

//get list of office users
$offices = [];
if ($result = $amdb->get_results("SELECT * FROM offices WHERE status='active'")) {
    foreach ($result as $row) {
        if ($row['offid'] == 0) {
            $HQC = $row['office_name'];
        } else {
            $offices[$row['offid']] = trim(str_replace(' - ', ' ', $row['office_name']));
        }
    }
}
asort($offices);
$offices[0] = $HQC;
$offid_uid_bm = explode('_', $_POST['offid_uid_bm']);
?>
<style>
    #linked-users div {
        padding: 5px 10px;
    }

    #officeUsers {
        padding: 0px !important;
    }

    #linked-users ul {
        list-style-type: none;
        padding: 0px;
        margin: 0;
    }

    #linked-users li {
        padding: 5px 0px;
        border-bottom: 1px dashed #ccc;
    }

    #linked-users h2 {
        border-bottom: 1px solid #ccc;
    }

    #linked-users select {
        width: 100%;
        font-size: 16px;
        text-transform: capitalize;
    }
</style>
<script>
    function loadUsers(offid_uid_bm) {
        jQuery("#officeUsers").html('');
        jQuery("#officeUsers").load("/admin/committee/linked.php?act=getUsers&offid_uid_bm=" + offid_uid_bm)
    }
</script>
<?php if ($_POST['act'] == 'link_user') { ?>
    <form action="/admin/committee/linked.php" method="post" id="linked-users" style="width:800px;" onsubmit="return post_this_form(this)">
        <input type="hidden" name="act" value="update_user">
        <input type="hidden" name="comemid" value="<?php echo $_POST['comemid']; ?>">
        <div style="float: left;width:45%">
            <h2>Branch: </h2>
            <select name="offid" onchange="loadUsers(this.value)">
                <option value="">Select Branch</option>
                <?php foreach ($offices as $offid => $name) { ?>
                    <option value="<?php echo $offid; ?>" <?php echo $offid == '0' ? 'style="font-weight:bold"' : ''; ?> <?php echo ($offid_uid_bm[0] != '' && $offid_uid_bm[0] == $offid) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
        </div>
        <div style="float: left;width:45%">
            <h2>User Name</h2>
            <div id="officeUsers"></div>
        </div>
    </form>
    <?php if ($offid_uid_bm[0] != '') { ?>
        <script>
            loadUsers('<?php echo $_POST['offid_uid_bm']; ?>');
        </script>
    <?php } ?>
<?php } ?>