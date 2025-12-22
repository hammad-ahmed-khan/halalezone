<?php
if (!defined("_HQC_")) {
    exit();
};
if (in_array("home_send_documents", $user_permissions) or $_SESSION['user_type'] == "admin") {
?>
    <script>
        $("#page_title").html("Send Documents")

        function send_docs() {
            if (document.new_docs_form.sent_date.value == "" || document.new_docs_form.doc_nrs.value == "") {
                alert("All fields are required")
                return false;
            }
            if (document.new_docs_form.doc_nrs.value.indexOf('-') == -1) {
                alert("You are sending one document")
                return false;
            }

            document.getElementById('chkdocs').src = "check_docs.php?docs=" + document.new_docs_form.doc_nrs.value;
            document.new_docs_form.submit();
        }
    </script>
    <?php
    include "../checkuser.inc.php";
    include "../config/paths.inc.php";
    include "../config/mysql_ftp.inc.php";
    include "../config/connect.inc.php";
    $company_name = '';
    if (isset($id)) {
        $result = MYSQL_QUERY("SELECT * FROM companies where clid='$id'");
        if (@MYSQL_NUM_ROWS($result) > 0) {
            $row = MYSQL_FETCH_ARRAY($result);
            $company_name = $row['company_name'];
        }
    } else {
        $result = MYSQL_QUERY("SELECT * FROM companies JOIN users ON companies.clid = users.clid where users.active='y' AND companies.clof='' order by companies.company_name ASC");
        if (@MYSQL_NUM_ROWS($result) > 0) {
            $coms = "";
            while ($row = MYSQL_FETCH_ARRAY($result)) {
                $coms .= "<option value='$row[clid]'>$row[company_name]</option>\n";
            }
        }
    }
    ?>
    <form action="admin_save.php" name="new_docs_form" method=post>
        <input type=hidden value='docNrs' name='act'>
        <?php if (isset($_GET['id'])) { ?>
            <input type=hidden value='<?php echo $_GET['id']; ?>' name='id'>
        <?php }; ?>
        <table border=0 class="alternate" style="max-width:450px">
            <tr>
                <td colspan=3><b>Send documents to:</b>
                    <?php
                    if (isset($coms)) { ?>
                        <select size=1 name="id">
                            <option value="">Select Company</option>
                            <?php echo $coms; ?>
                        </select>
                    <?php } else {
                        echo "[$company_name]";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan=3 height=20></td>
            </tr>
            <tr>
                <th>Document numbers</th>
                <th>Sent on</th>
            </tr>
            <tr bgcolor=#EEEEEE>
                <td><input type=text style='width:250px' name='doc_nrs'></td>
                <td><input type=text style='width:100px' name='sent_date' id='sent_date' class="date"></td>
            </tr>
            <tr>
                <td colspan=4 height=20></td>
            </tr>
            <tr>
                <td colspan=4 class="sub_title">
                    <center><input type="button" onclick="send_docs()" value=" Save "></center>
                </td>
            </tr>
        </table>
    </form>
    <span id='usedDocs' style="display:none"></span>
    <iframe style="position:fixed;left:-10000px;" src='' id='chkdocs'></iframe>
<?php } ?>