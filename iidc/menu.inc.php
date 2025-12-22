<?php
//TODO: add switching possibilities to new system
if (isset($_SESSION['user_type'])) {
    ob_start();
    if ($_SESSION['user_type'] == "admin") {
?>
        <ul class="hqcMenu">
            <li><a href="<?php echo $prog_www ?>/admin/" style="border-left:0px">Home</a></li>
            <?php if ($_SESSION['user_type'] == "admin" or (in_array("menu_clients", $user_permissions) or in_array("clients_actions", $user_permissions))) { ?><li><a style="cursor: default;">Clients</a>
                    <div>
                        <ul>
                            <li><a href="<?php echo $prog_www ?>/admin/?inc=clients&ref=true">All Clients</a></li>
                            <li>
                                <a href="<?php echo $prog_www ?>/admin/?inc=new-clients">New clients</a>
                            </li>
                            <li>
                                <a href="<?php echo $prog_www ?>/company/index.php?inc=register">Add New client</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <span>Products</span>
                    <div>
                        <ul>
                            <li>
                                <a href="<?php echo $prog_www ?>/company/products/index.php?inc=products_home">Products list</a>
                            </li>
                            <li>
                                <a href="<?php echo $prog_www ?>/company/products/index.php?inc=products_versions">Products versions</a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php }; ?>
            <?php if (in_array("menu_ac", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                <li><a>DMC</a>
                    <ul>
                        <li><a href="<?php echo $prog_www ?>/committee/?inc=account">My account</a></li>
                        <li><a href="<?php echo $prog_www ?>/admin/committee/?inc=committee">Decision committee members</a></li>
                        <li><a href="<?php echo $prog_www ?>/committee/index.php?inc=schedule_committee" style="border-left:0px">Schedule a meeting</a></li>
                        <li><a href="<?php echo $prog_www ?>/committee/" style="border-left:0px">Scheduled meetings</a></li>
                        <li><a href="<?php echo $prog_www ?>/committee/?status=approved" style="border-left:0px">Decision history</a></li>
                        <li><a href="<?php echo $prog_www ?>/guidelines/" style="border-left:0px">Guidelines</a></li>

                    </ul>
                </li>
                <li><a>Certificates</a>
                    <div>
                        <ul>
                            <li><a href="<?php echo $prog_www ?>/admin/?inc=certificates&tp=a&offid=0">Slaughtering Certificate </a></li>
                            <li><a href="<?php echo $prog_www ?>/certificates/annual/?inc=certificates">Annual Certificates</a></li>
                        </ul>
                    </div>
                </li><?php }; ?>
            <?php if ($_SERVER['REMOTE_ADDR'] == "82.171.201.165") {
                if (in_array("menu_communications", $user_permissions) or $_SESSION['user_type'] == "admin") { ?><li><a href="<?php echo $prog_www ?>/communications">Communications</a>
                        <div>
                            <ul>
                                <?php if (in_array("communications_open_ticket", $user_permissions) or $_SESSION['user_type'] == "admin") { ?><li><a href="<?php echo $prog_www ?>/communications/index.php?inc=ticket_add<?php echo (isset($_GET['coid'])) ? "&coid=$_GET[coid]" : ""; ?>" class="lastLiItem">Open new ticket</a></li><?php }; ?>
                            </ul>
                        </div>
                    </li><?php };
                    }; ?>
            <?php
            //todo: fix and activate this menu
            /*if (in_array("auditor", $user_permissions) or in_array("supervisor", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                <li>
                    <a>Audits / Supervisions</a>
                    <div>
                        <ul>
                            <?php if (in_array("auditor", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <li><a href="<?php echo $prog_www ?>/audit/" style="border-left:0px">Audit list</a></li>
                                <li><a href="<?php echo $prog_www ?>/audit/index.php?inc=audit_add_edit" style="border-left:0px">Add new audit</a></li>
                            <?php }; ?>
                            <?php if (in_array("supervisor", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <li><a href="<?php echo $prog_www ?>/audit/halal-supervision/" style="border-left:0px">Halal Supervisions list</a></li>
                                <li><a href="<?php echo $prog_www ?>/audit/halal-supervision/index.php?inc=supervision_add_edit" style="border-left:0px" class="lastLiItem">Add new Halal Supervision</a></li>
                            <?php }; ?>
                        </ul>
                    </div>
                <?php };*/ ?>
            <?php if (in_array("auditor", $user_permissions) and $username != "admin") { ?>
                <li><a href="<?php echo $prog_www ?>/user/" style="border-left:0px">My profile</a></li>
            <?php }; ?>
            <?php if ($_SESSION['user_type'] == "admin" or $username == "tariq") { ?><li><a>Setups</a>
                    <div>
                        <ul>
                            <?php if ($_SESSION['user_type'] == "admin") { ?>
                                <li><a href="<?php echo $prog_www ?>/admin/?inc=admin_users">Admin Users</a></li>
                                <!-- <li><a href="<?php echo $prog_www ?>/admin/?inc=admin_users&type=auditor">Auditors list</a></li>
                                <li><a href="<?php echo $prog_www ?>/admin/?inc=change_password">Change admin password</a></li>-->
                                <?php /*<li><a href="<?php echo $prog_www ?>/invoices/?inc=service_prices">Default prices & costs</a></li>
                                <?php /*<li><a href="<?php echo $prog_www ?>/invoices/?inc=default_expenses">Predefined expenses</a></li> */ ?>
                                <?php /* <li><a href="<?php echo $prog_www ?>/audit/halal-supervision/?inc=settings">Default kilometer rate</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?inc=monthly_invoices">Recurring Invoices (monthly)</a></li>*/ ?>
                                <!-- <li><a href="<?php echo $prog_www ?>/admin/?inc=invoice_defaults">Invoice defaults</a></li> -->
                                <li><a href="<?php echo $prog_www ?>/invoices/?inc=predefined_prices">Predefined prices</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/reminders/?inc=reminders">Invoice default payment terms & reminders</a></li>
                                <li><a href="<?php echo $prog_www ?>/admin/?inc=invoice_template">Invoice email-messages & Templates</a></li>
                                <li><a href="<?php echo $prog_www ?>/admin/?inc=test_email">Test - email address</a></li>
                                <li><a href="<?php echo $prog_www ?>/admin/?inc=pdf_protection">PDF files protection</a></li>
                                <li><a href="<?php echo $prog_www ?>/offices/admin/?inc=offices">Our offices</a></li>
                                <li><a href="<?php echo $prog_www ?>/offices/admin/signatories/?inc=signatories">Certificates signatories</a></li>
                                <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=halal_standards&offid=0">Halal standards</a></li>
                                <?php /* <li><a href="<?php echo $prog_www ?>/images_management/">Images management</a></li>*/ ?>
                            <?php }; ?>
                            <?php  /*<li><a href="<?php echo $prog_www ?>/admin/?inc=bvs">Our subsidiaries</a></li> */ ?>
                            <li><a href="<?php echo $prog_www ?>/admin/?inc=prohibited">Prohibited words</a></li>
                        </ul>
                    </div>
                </li><?php }; ?>
            <?php if (in_array("menu_invoices", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                <li><a>Invoices</a>
                    <ul>
                        <li><span>Invoices</span></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=all">All Invoices</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=paid">Paid Invoices</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=unpaid">Unpaid Invoices</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=overdue">Over due</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=credit">Credit notes</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?show=credited">Credited invoices</a></li>
                        <li><a href="<?php echo $prog_www ?>/invoices/?index.php&inc=client_invoices">Client invoices</a></li>
                        <?php if (in_array("invoices_create", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                            <li><a href="<?php echo $prog_www ?>/invoices/export/index.php">Export invoices</a></li>
                            <li><span>Create invoices</span></li>
                            <li><a href="<?php echo $prog_www ?>/invoices/?show=draft">Draft invoices</a></li>
                            <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_cohs_invoice">Shipment certificates invoice</a></li>
                            <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_hqc_invoice">Annual certificates invoice</a></li>
                            <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_general_invoice">General invoice</a></li>
                            <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_credit_note" class="lastLiItem">Create credit note</a></li>
                        <?php }; ?>
                    </ul>
                </li>
            <?php }; ?>
            <?php if (isset($_SESSION['sys_admin'])) { ?>
                <li><a>Tools</a>
                    <ul>
                        <li><a href=" <?php echo $prog_www ?>/system/under-construction/index.php">Under construction</a></li>
                        <li><a href="<?php echo $prog_www ?>/system/notifications/index.php?inc=notifications">Notifications</a></li>
                        <li>
                            <a href="<?php echo $prog_www ?>/company/messages/index.php?inc=send_message">Email clients</a>
                        </li>
                    </ul>
                </li>
            <?php }; ?>
            <li><a href="" onclick="set_session_url('new');return false;">New window</a></li>
            [joined_accounts]
            <?php
            if (isset($_SESSION['user']) && isset($_SESSION['user']['name']))
                $loggedInAs = $_SESSION['user']['name'];
            else
                $loggedInAs = $_SESSION['username'];
            ?>
            <?php $logout = '<a href="' . $prog_www . '/logout.php" target=_top>Log-out [' . @$loggedInAs . ']</a>'; ?>
        </ul>
    <?php
    } elseif (isset($_SESSION['user_type']) and $_SESSION['user_type'] == "hqc_office") { ?>
        <ul class="hqcMenu">
            <li><a href="<?php echo $prog_www ?>/offices/home/" style="border-left:0px">Home</a></li>
            <li><a>DMC</a>
                <ul>
                    <li><a href="<?php echo $prog_www ?>/committee/?inc=account">My account</a></li>
                    <li><a href="<?php echo $prog_www ?>/committee/index.php?inc=schedule_committee" style="border-left:0px">Schedule a meeting</a></li>
                    <li><a href="<?php echo $prog_www ?>/committee/" style="border-left:0px">Scheduled meetings</a></li>
                    <li><a href="<?php echo $prog_www ?>/committee/?status=approved" style="border-left:0px">Decision history</a></li>
                    <li><a href="<?php echo $prog_www ?>/guidelines/" style="border-left:0px">Guidelines</a></li>
                </ul>
            </li>
            <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("certificates", $_SESSION['permissions']))) { ?>
                <li><span>Certificates</span>
                    <div>
                        <ul>
                            <li><a href='<?php echo $prog_www ?>/offices/home/?inc=certificates&tp=a'>A (Only meat)</a></li>
                            <li><a href='<?php echo $prog_www ?>/offices/home/?inc=certificates&tp=b'>B (Other products)</a></li>
                            <li><a href="<?php echo $prog_www ?>/offices/home/?inc=certificates&tp=sa">HSA: (Fresh meats) For Saudi Arabia only</a></li>
                            <li><a href="<?php echo $prog_www ?>/offices/home/?inc=certificates&tp=sb">HSB: (Other products) For Saudi Arabia only</a></a></li>
                            <li><a href="<?php echo $prog_www ?>/certificates/annual/?inc=certificates">Annual Certificates</a></li>
                            <li><a href="<?php echo $prog_www ?>/offices/admin/?inc=certificate_data&offid=<?php echo $_SESSION['offid']; ?>">Certificates templates data</a></li>
                        </ul>
                    </div>
                </li>
            <?php }; ?>
            <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("clients", $_SESSION['permissions']))) { ?>
                <li><span>Clients</span>
                    <div>
                        <ul>
                            <li><a href="<?php echo $prog_www ?>/admin/?inc=clients">Clients list</a></li>
                            <li><a href="<?php echo $prog_www ?>/company/index.php?inc=register" class="lastLiItem">Add New client</a></li>
                            <?php /* if (isset($user_options['form'][1])) { ?>
                            <a href="<?php echo $prog_www ?>/company/forms/application/index.php?inc=applications&foid=1">Application forms</a>
                        <?php }; */ ?>
                        </ul>
                    </div>
                </li>
            <?php }; ?>
            <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("products", $_SESSION['permissions']))) { ?>
                <li>
                    <span>Products</span>
                    <div>
                        <ul>
                            <li>
                                <a href="<?php echo $prog_www ?>/company/products/index.php?inc=products_home">Products list</a>
                            </li>
                            <!-- <li>
                                <a href="<?php echo $prog_www ?>/company/products/index.php?inc=products_versions">Products versions</a>
                            </li> -->
                        </ul>
                    </div>
                </li>
            <?php }; ?>
            <?php if (isset($user_options['invoices_view']) or isset($user_options['invoices_create'])  or $_SESSION['user_type'] == "admin") { ?>
                <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("invoices", $_SESSION['permissions']))) { ?>
                    <li><a>Invoices</a>
                        <ul>
                            <?php if (isset($user_options['invoices_view']) or $_SESSION['user_type'] == "admin") { ?>
                                <li><span>Invoices</span></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=all">All Invoices</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=paid">Paid Invoices</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=unpaid">Unpaid Invoices</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=overdue">Over due</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=credit">Credit notes</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=credited">Credited invoices</a></li>
                            <?php }; ?>
                            <?php if (isset($user_options['invoices_create']) or $_SESSION['user_type'] == "admin") { ?>
                                <li><span>Create invoices</span></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/?show=draft">Draft invoices</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_cohs_invoice">Batch certificates invoice</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_hqc_invoice">Annual certificates invoice</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_audit_invoice">Audit invoice</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_general_invoice">General invoice</a></li>
                                <li><a href="<?php echo $prog_www ?>/invoices/index.php?inc=create_credit_note" class="lastLiItem">Create credit note</a></li>
                            <?php }; ?>
                        </ul>
                    </li>
                <?php }; ?>
            <?php }; ?>
            <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("profile", $_SESSION['permissions']))) { ?>
                <?php if (isset($_SESSION['offuid'])) { ?>
                    <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=office_add_edit">My profile</a></li>
                <?php } else { ?>
                    <li><a>My Office</a>
                        <ul>
                            <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=office_add_edit">Office data</a></li>
                            <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=certificate_data">Certificates data</a></li>
                            <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=joined_offices">Joined Offices</a></li>
                        </ul>
                    </li>
                <?php }; ?>
            <?php }; ?>
            <?php if (!isset($_SESSION['offuid']) or (isset($_SESSION['offuid']) and isset($_SESSION['permissions']) and in_array("users", $_SESSION['permissions']))) { ?>
                <li><a href="<?php echo $prog_www ?>/offices/admin/index.php?inc=office_users">Users</a></li>
            <?php }; ?>
            <?php if (isset($_SESSION['adminData'])) { ?>
                <li><?php $logout = ' <a href="' . $prog_www . '/logout.php?act=backToAdmin">Go Back</a>'; ?></li>
            <?php } else { ?>
                <?php if (!isset($_SESSION['offuid'])) { ?>
                    [joined_accounts]
                <?php  } ?>
                <?php $logout = '<a href="' . $prog_www . '/logout.php" target=_top>Log-out [' . @$username . ']</a>'; ?>
            <?php }; ?>
        </ul>
    <?php
    } elseif (isset($_SESSION['clid']) and $_SESSION['clid'] != '') {
        $clid = $_SESSION['clid'];
        if ($company = $amdb->get_row("SELECT subs FROM companies WHERE clid = '$_SESSION[clid]'"))
            $subs = $company['subs'];
        else
            $subs = '';
    ?>
        <ul class="hqcMenu">
            <li><a href="<?php echo $prog_www ?>/company/" style="border-left:0px">Home</a></li>
            <li><a>Certificates</a>
                <div>
                    <ul>
                        <li><span>Requested Certificates </span></li>
                        <?php if ($result = $amdb->get_results("SELECT nr FROM certificates_a where done='y' and clid='$clid' OR FIND_IN_SET(clid,'$subs')")) { ?>
                            <li><a href="<?php echo $prog_www ?>/company/certificates/index.php?inc=certificates&tp=a">A (Meat products) <span style="color:red"><?php echo count($result); ?></span></a></li>
                        <?php } ?>
                        <?php if ($result = $amdb->get_results("SELECT nr FROM certificates_b where done='y' and clid='$clid' OR FIND_IN_SET(clid,'$subs')")) { ?>
                            <li><a href="<?php echo $prog_www ?>/company/certificates/index.php?inc=certificates&tp=b">B (Non meat products) <span style="color:red"><?php echo count($result); ?></span></a></li>
                        <?php }; ?>
                        <?php if ($result = $amdb->get_results("SELECT certificate_nr FROM acms_halal_certificates where clid='$clid' OR FIND_IN_SET(clid,'$subs')")) { ?>
                            <li><a href="<?php echo $prog_www ?>/company/certificates/index.php?inc=annual_certificates">Annual Certificates <span style="color:red"><?php echo count($result); ?></span></a></li>
                        <?php }; ?>
                        <li><span>Request Certificate</span></li>
                        <li><a href='<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=a'>Type A (Fresh meats)</a></li>
                        <li><a href='<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=b'>Type B (Other products)</a></li>

                        <li><a href="<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=sa">HSA: (Fresh meats) For Saudi Arabia only</a></li>
                        <li><a href="<?php echo $prog_www ?>/certificates/?inc=certificate_ab&tp=sb">HSB: (Other products) For Saudi Arabia only</a></a></li>
                        <?php /* <li><a href='<?php echo $prog_www ?>/certificates/annual/index.php?inc=certificate_add_edit&act=add&clid=<?php echo $clid ?>'>Annual Certificate</a></li> */ ?>
                    </ul>
                </div>
            </li>
            <li><a>My assets</a>
                <div>
                    <ul>
                        <li><a href="<?php echo $prog_www ?>/company/index.php?inc=clients&clid=<?php echo $clid ?>">Clients</a></li>
                        <li><a href="<?php echo $prog_www ?>/company/products/index.php?inc=products_list">Products</a></li>
                        <li><a href="<?php echo $prog_www ?>/company/index.php?inc=production_sites&clid=<?php echo $clid ?>">Production sites</a></li>
                    </ul>
                </div>
            </li>
            <?php if (!isset($_SESSION['cluid'])) { ?>
                <li><a>My Company</a>
                    <ul>
                        <li>
                            <a href="<?php echo $prog_www ?>/company/index.php?inc=profile&clid=<?php echo $clid ?>">My profile</a>
                        </li>
                        <?php if (trim($subs) != '') { ?>
                            <li>
                                <a href="<?php echo $prog_www ?>/company/index.php?inc=branches&clid=<?php echo $clid ?>">Branches / Companies</a>
                            </li>
                        <?php }; ?>
                        <li>
                            <a href="<?php echo $prog_www ?>/company/users/index.php?inc=users&clid=<?php echo $clid ?>">Users</a>
                        </li>
                    </ul>
                </li>
            <?php }; ?>
            <?php /*if (isset($_SESSION['logedAsClient']) and isset($_SESSION['uid']) or $amdb->get_row("SELECT * FROM forms WHERE foid='1' and status = 'active'")) { ?>
                <li><a href="<?php echo $prog_www ?>/company/forms/application/index.php?inc=my_application&foid=1&clid=<?php echo $clid ?>">Application form</a></li>
                <li class="logout">
                <?php }; */ ?>
            <?php if (isset($_SESSION['adminData'])) {
                $loggedInAs = '';
                if (isset($_SESSION['clid']) and $company = $amdb->get_row("SELECT company_name FROM companies WHERE clid = '$_SESSION[clid]'")) {
                    $loggedInAs = "Logged-in as <span style=\"color:red\">($company[company_name])</span>";
                }
            ?>
                <?php $logout = '<a href="' . $prog_www . '/logout.php?act=backToAdmin">[Go Back]</a>'; ?>
            <?php } else { ?>
                <?php $logout = '<a href="' . $prog_www . '/logout.php" target=_top>Log-out [' . @$username . ']</a>'; ?>
            <?php }; ?>
            </li>
        </ul>
    <?php }; ?>
<?php
    $theMenu = ob_get_contents();
    ob_end_clean();
    $menuItems = '';
    if (isset($_SESSION['uid'])) {
        $id_type = 'uid';
        $joined = $_SESSION['uid'];
        $whr = "AND uid = '$joined'";
    } elseif (isset($_SESSION['offid'])) {
        $id_type = 'offid';
        $joined = $_SESSION['offid'];
        $whr = "AND FIND_IN_SET('$joined', accounts)";
    } else {
        $id_type = '';
        $joined = '';
        $whr = '';
    }
    if ($accounts = $amdb->get_row("SELECT * FROM hqc_joined_accounts WHERE 1=1 $whr AND id_type='$id_type'")) {
        if ($offices = $amdb->get_results("SELECT office_name,offid FROM offices WHERE FIND_IN_SET(offid,'$accounts[accounts]')")) {
            $menuItems = '<li><a style="cursor: default;">Switch Office</a><div><ul>';
            foreach ($offices as $switch_office) {
                if (isset($_SESSION['uid'])) {
                    $menuItems .= '<li><a class="post_this_link" href="/offices/admin/switch_user.php?act=admin2office&offid=' . $switch_office['offid'] . '&suid=' . $_SESSION['uid'] . '" style="cursor: pointer !important;">' . $switch_office['office_name'] . '</a></li>';
                } else {
                    if ($switch_office['offid'] == $_SESSION['offid']) {
                        continue;
                    }
                    $menuItems .= '<li><a href="/offices/admin/switch_user.php?act=office2office&offid=' . $switch_office['offid'] . '" style="cursor: pointer !important;">' . $switch_office['office_name'] . '</a></li>';
                }
            };
            $menuItems .= '</ul></div></li>';
        }
    }
    $theMenu = str_replace('[joined_accounts]', $menuItems, $theMenu);
    $theMenu = str_replace('<li></li>', '', $theMenu);
    echo $theMenu;
};

function logOutLink()
{
    global $logout;
    if (isset($logout))
        echo $logout;
    else
        echo '';
}
// echo $_SESSION['offid'];
?>