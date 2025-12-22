<?php if (!defined("__HQC__")) {
	exit();
};
?>

<script>
    jQuery('#page_title').html('New companies waiting for approval');
    function delclient(id) {
        if (confirm("Are you sure?") == "1")
            document.location.href = "admin_save.php?act=delNew&id=" + id;
    }

    function delact(dothis) {
        var chck = false;
        for (var i = 0; i <= document.new_clients_form.elements.length - 1; i++) {
            if (document.new_clients_form.elements[i].type == 'checkbox' && document.new_clients_form.elements[i].checked == true)
                chck = true;
        }
        if (chck == false) {
            alert("Please select a client");
            return false;
        }
        if (dothis == 'activate') {
            document.new_clients_form.act.value = 'activate';
            document.new_clients_form.submit();
        }
        if (dothis == 'delclient') {
            if (confirm("Selected clients will be deleted. Are you sure?") == 1) {
                document.new_clients_form.act.value = 'delclient';
                document.new_clients_form.submit();
            }
        }
    }
</script>
<h1 style="text-align: center;">New companies waiting for approval</h1>
<table border=0 id="newCompanies" class="alternateOn" style="width: 100%; margin: 0 auto; text-align: left;">
    <tr>
        <?php
        if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
            <th width="20">::</th>
        <?php } ?>
        <th width="400">Company</th>
        <th style="width:90px">Date</th>
        <?php if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
            <th style="width:40px;">Action</th>
        <?php } ?>
    </tr>
    <?php
    $offids = array();
    if ($offices = $amdb->query("SELECT offices.offid,offices.office_name FROM offices order by offices.office_name ASC")) {
        if (count($offices) > 0) {
            foreach ($offices as $off) {
                $offids[$off['offid']] = $off['office_name'];
            }
        }
    }
    $result = array();
    $result = $amdb->get_results("SELECT * FROM users,companies where companies.clof='0' and users.approved='n' and users.active!='b' and companies.clid=users.clid");
    if (count($result) > 0) {
    ?>
        <form action="admin_save.php" name="new_clients_form" method=post target="">
            <input type=hidden value='' name='act'>
            <?php
            $not_confirm = "";
            $noCo = 0;
            foreach ($result as $row) {
                $date = strtotime(fix_date($row['date']));
                if (trim($row['activate_code']) == '') {
                    if ((time() - $date) < 7776000) { ?>
                        <tr>
                            <?php if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <td valign=top><input type=checkbox name='clid[]' value='<?php echo $row['clid']; ?>'></td>
                            <?php }; ?>
                            <td>
                                <span style="cursor:pointer" class="com com_<?php echo $row['clid']; ?> clid load_popup" data-url="load_company.php?clid=<?php echo $row['clid']; ?>" title="<?php echo $row['company_name']; ?>"><b><?php echo $row['company_name']; ?></b></span>
                                <br>
                                <?php echo trim($row['street1']) != '' ? $row['street1'] . ',' : ''; ?><?php echo "$row[zip1] $row[city1], $row[country1]<br><b>Tel:</b>$row[tel1] <b>E-mail:</b><a href='mailto:$row[email1]'>$row[email1]</a>"; ?>
                                <br />
                                <b style="color:green">Office:</b> <?php echo isset($offids[$row['offid']]) ? $offids[$row['offid']] : ''; ?>
                                <?php echo trim($row['hqc_contact_person']) != '' ? ' / ' . $row['hqc_contact_person'] : ''; ?>
                            </td>
                            <td valign=top><b><?php echo date("d/m/Y", $date); ?></td>
                            <?php
                            if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <td valign=top>
                                    <a href='javascript:delclient(<?php echo $row['clid']; ?>)'><img title='Delete client' src="../images/delete.gif" border=0></a><i class="far fa-times-circle" title="Block client" data-clid="<?php echo $row['clid']; ?>"></i>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } else {
                    if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                        <?php if ((time() - $date) < 432000) { ?>
                            <tr>
                                <td valign=top>
                                    <input type=checkbox name='clid[]' value='<?php echo $row['clid']; ?>'>
                                </td>
                                <td><b style="cursor:pointer" class="com com_<?php echo $row['clid']; ?> clid load_popup" data-url="load_company.php?clid=<?php echo $row['clid']; ?>"><?php echo $row['company_name']; ?></b><br>
                                    <?php echo trim($row['street1']) != '' ? $row['street1'] . ',' : ''; ?><?php echo "$row[zip1] $row[city1], $row[country1]<br><b>Tel:</b>$row[tel1] <b>E-mail:</b><a href='mailto:$row[email1]'>$row[email1]</a>"; ?>
                                    <br />
                                    <b style="color:green">Office:</b> <?php echo isset($offids[$row['offid']]) ? $offids[$row['offid']] : ''; ?>
                                    <?php echo trim($row['hqc_contact_person']) != '' ? ' / ' . $row['hqc_contact_person'] : ''; ?>
                                </td>
                                <td valign=top><b><?php echo date("d/m/Y", $date); ?></td>
                                <?php
                                if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                    <td valign=top>
                                        <a href='javascript:delclient(<?php echo $row['clid']; ?>)'><img title='Delete client' src="../images/delete.gif" border=0></a>
                                        <i class="far fa-times-circle" title="Block client" data-clid="<?php echo $row['clid']; ?>"></i>
                                    </td>
            <?php }
                                echo "</tr>";
                                $noCo++;
                            }
                        }
                    }
                }
                if (isset($noCo) and is_array($noCo) and count($noCo) > 0) {
                    echo "<tr><td colspan=4 align=center height=10></td></tr>";
                    echo "<tr bgcolor=\"#CCCCCC\"><td colspan=4 align=center style=\"cursor:pointer\" onclick=\"noco.style.display=''\">There are $noCo new users unconfirmed </td></tr>";
                    echo "<tr><td colspan=4 align=center height=10></td></tr>";
                }
                if (in_array("home_ceriticate_actions", $user_permissions) or $_SESSION['user_type'] == "admin") {
                    echo "<tr bgcolor=\"#CCCCCC\"></form><td colspan=4 align=center>
	<input type='button' onclick=\"delact('activate')\" value=\" Activate selected \">
	<input type='button' onclick=\"delact('delclient')\" value=\" Delete selected \">
	</td>
	</tr>";
                }
            }
            echo "</table>";
            ?>