<?php
session_start();
//show php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['username']))
    exit();
include "../config/paths.inc.php";
$username = $_SESSION['username'];
if (file_exists("$prog_path/hqc_users/$username.usr.php")) {
    include "$prog_path/hqc_users/$username.usr.php";
}
include "$prog_path/hqc_users/admin_users.inc.php";
$admin_users = json_decode($admin_users, true);

$srchFor = "";

$products = array();
if ($productsAll = $amdb->get_results("SELECT clid, count(clid) AS total FROM acms_hdcs_products WHERE status!='deleted' GROUP BY clid")) {
    foreach ($productsAll as $product) {
        $products[$product['clid']] = $product['total'];
    }
}
$clients = array();
if ($clientsAll = $amdb->get_results("SELECT clid,clof,count(clof) AS total FROM companies WHERE `clof`!='0' GROUP BY clof")) {
    foreach ($clientsAll as $client) {
        $clients[$client['clof']] = $client['total'];
    }
}
$office = array();
$officeName = array();
if ($offices = $amdb->get_results("SELECT offid,office_name,reference_prefix,certificate_prefix,clients FROM offices")) {
    foreach ($offices as $off) {
        if (trim($off['clients']) != '' && $off['offid'] == $_SESSION['offid'] && $_SESSION['offid'] != '0') {
            $user_clients = explode(',', $off['clients']);
        }
        $office[$off['offid']] = $off['reference_prefix'];
        $offids[$off['offid']] = $off['reference_prefix'] . $off['certificate_prefix'];
        $officeName[$off['offid']] = $off['office_name'];
    }
}
$production_sites = array();
if ($production_sitesAll = $amdb->get_results("SELECT clid, count(clid) AS total FROM companies_production_sites WHERE status='active' GROUP BY clid")) {
    foreach ($production_sitesAll as $production_site) {
        $production_sites[$production_site['clid']] = $production_site['total'];
    }
}
$certificates = array();
$certificatesIssue = array();
if ($clients_certificate = $amdb->get_results("SELECT crtNr,clid,MAX(date_of_expiry) AS date_of_expiry,date_of_issue FROM `acms_halal_certificates` WHERE date_of_expiry > 0 GROUP BY clid")) {
    foreach ($clients_certificate as $client_certificate) {
        $certificates[$client_certificate['clid']] = $client_certificate['date_of_expiry'];
        $certificatesIssue[$client_certificate['clid']] = $client_certificate['date_of_issue'];
    }
}

// $certificates_a = array();
// if ($clients_certificate = $amdb->get_results("SELECT clid,COUNT(clid) AS total FROM `certificates_a` GROUP BY clid")) {
// 	foreach ($clients_certificate as $client_certificate) {
// 		$certificates_a[$client_certificate['clid']] = $client_certificate['total'];
// 	}
// }
// $certificates_b = array();
// if ($clients_certificate = $amdb->get_results("SELECT clid,COUNT(clid) AS total FROM `certificates_b` GROUP BY clid")) {
// 	foreach ($clients_certificate as $client_certificate) {
// 		$certificates_b[$client_certificate['clid']] = $client_certificate['total'];
// 	}
// }

$clientOffices = array();
if ($userOffces = $amdb->get_results("SELECT offid,clients FROM offices WHERE status='active'")) {
    foreach ($userOffces as $userOffce) {
        if (trim($userOffce['clients']) != '') {
            $officeClients = explode(',', $userOffce['clients']);
            foreach ($officeClients as $officeClient) {
                $clientOffices[$officeClient][] = $userOffce['offid'];
            };
        };
    };
}

$applications = array();
if ($applicationsAll = $amdb->get_results("SELECT clid,url FROM application_form WHERE status='active'")) {
    foreach ($applicationsAll as $application) {
        $applications[$application['clid']] = $application['url'];
    }
}

//show php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_SESSION['user']) && isset($_SESSION['user']['uid']))
    $uid = $_SESSION['user']['uid'];
$auditors = array();
if ($admin_users = $amdb->get_results("SELECT * FROM `hqc_admin_users` WHERE permissions LIKE '%auditor%'")) {
    foreach ($admin_users as $user) {
        $auditors[$user['uid']] = $user['username_owner'];
    }
};

$auditor_clients = array();
if ($audits = $amdb->get_results("SELECT auid,clid,audit_date, auditors FROM audits WHERE status != 'deleted' GROUP BY clid ORDER BY audit_date DESC")) {
    foreach ($audits as $audit) {
        if (is_array(json_decode($audit['auditors'], true))) {
            $audit_auditors = json_decode($audit['auditors'], true);
            if (isset($auditors[$audit_auditors['leading']]))
                $auditor_clients[$audit['clid']]['leading'] = $audit_auditors['leading'];
            if (isset($audit_auditors['co']) and count($audit_auditors['co']) > 0) {
                foreach ($audit_auditors['co'] as $coKey => $coValue) {
                    if (isset($auditors[$coValue]))
                        $auditor_clients[$audit['clid']]['co'][$coValue] = $coValue;
                }
            }
            if (isset($auditor_clients[$audit['clid']]))
                $auditor_clients[$audit['clid']]['date'] = date("d/m/Y", strtotime($audit['audit_date']));
        }
    }
}
$time = time();
if (isset($srch_wht) and $srch_wht != '')
    $srchFor = " and $srch like '%$srch_wht%'";
// if(isset($user_permissions) && in_array('auditor',$user_permissions))
// $srchFor .= " AND companies.offid = '0'";

if (trim($_POST['srchWht']) != '' && trim($_POST['srch']) != '')
    $srchFor = " AND companies.$_POST[srch] like '%$_POST[srchWht]%'";
if (isset($user_clients) and count($user_clients) > 0 && $_SESSION['offid'] != 0)
    $srchFor .= " AND companies.clid IN (" . implode(',', $user_clients) . ")";

$sql = "SELECT * FROM companies JOIN users ON companies.clid=users.clid WHERE companies.clof='0' and users.active='$_REQUEST[active]' $srchFor order by TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC limit $_POST[st],$_POST[lmt]";

$nr = $_POST['st'];
$result = $amdb->get_results($sql);
if (count($result) > 0) {
    foreach ($result as $row) {
        if ($_SESSION['user_type'] == "admin" or (isset($user_clients) and in_array($row['clid'], $user_clients)) or (isset($_SESSION['offid']) && $_SESSION['offid'] == $row['offid'])) {

            $crtClr = 'grey';
            $daysToGo = '';
            if (isset($certificates[$row['clid']]) && $certificates[$row['clid']] != 0) {
                //find the difference between the current date and the certificate expiry date in months, weeks and days
                $timeToGo = timeToGo($certificates[$row['clid']]);

                // Convert the $timeToGo array to a string with keys and values separated by &
                $timeToGoString = implode(' & ', array_map(
                    function ($key, $value) {
                        return $value . strtoupper($key[0]);
                    },
                    array_keys($timeToGo),
                    $timeToGo
                ));
                //if the certificate has expired
                $timeDiff = time() - $certificates[$row['clid']];
                if ($timeDiff > 0) {
                    $crtClr = 'red';
                } else {
                    if (!isset($timeToGo['years']) && isset($timeToGo['months']) && $timeToGo['months'] <= 3) {
                        $crtClr = 'darkorange';
                        if (isset($timeToGo['months']))
                            $timeToGo = $timeToGo['months'] . ' month(s)';
                        elseif (isset($timeToGo['weeks']))
                            $timeToGo = $timeToGo['weeks'] . ' week(s)';
                        if (isset($timeToGo['days']))
                            $timeToGo .= '<br/>& ' . $timeToGo['days'] . ' day(s)';
                        //else
                        //	$timeToGo = '1 day';
                    } else {
                        $crtClr = 'green';
                        if (isset($timeToGo['years'])) {
                            $timeToGo  = $timeToGo['years'] . ' year(s)';
                        } elseif (isset($timeToGo['months'])) {
                            $timeToGo  = $timeToGo['months'] . ' month(s)';
                        } elseif (isset($timeToGo['weeks'])) {
                            $crtClr = 'darkorange';
                            $timeToGo  = $timeToGo['weeks'] . ' week(s)';
                        } elseif (isset($timeToGo['days'])) {
                            $crtClr = 'darkorange';
                            $timeToGo  = $timeToGo['days'] . ' day(s)';
                        } else {
                            $timeToGo = '1 day';
                        }
                    }
                }
            }
            if ($_POST['certificates'] == 'green' && $crtClr != 'green') continue;
            if ($_POST['certificates'] == 'red' && $crtClr != 'red') continue;
            if ($_POST['certificates'] == 'grey' && $crtClr != 'grey') continue;
            $nr++;
            if (trim($row['client_extra']) != '' && is_array(json_decode($row['client_extra'], true)))
                $extra_data = json_decode($row['client_extra'], true);
            else
                $extra_data = array();
?>
            <tr id="cl_<?php echo $row['clid']; ?>" data-id="<?php echo $row['clid']; ?>">
                <th class="thNr"><span class="nr"><?php echo $nr; ?></span><span class="foundNr"></span></th>
                <td><span class="com com_<?php echo $row['clid']; ?> company_name"><?php echo $row['company_name']; ?></span>
                    <div style="margin:5px 0px"><span style="cursor:pointer" class="com com_<?php echo $row['clid']; ?> clid load_popup" data-url="load_company.php?clid=<?php echo $row['clid']; ?>&login=true<?php echo (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) ? '&uid=' . $_SESSION['user']['uid'] : ''; ?>" title="<?php echo str_replace('"', '&quot;', $row['company_name']); ?>">
                            <?php echo $offids[$row['offid']]; ?><?php echo str_pad($row['clid'], 6, '0', STR_PAD_LEFT); ?></span></div>
                    <span class="city1" style="color:green"><?php echo $row['city1']; ?></span>, <span class="country1" style="color:green"><?php echo $row['country1']; ?></span>
                    <?php /*<div style="margin-top:5px">
                        <i class="fa fa-envelope load_popup" aria-hidden="true" style="font-size:14px !important;float:left;margin:3px 10px" data-url="update-emails.php?clid=<?php echo $row['clid']; ?>" title="Update email address(es)"></i> <span class="email" style="float:left;overflow:hidden">
                            <span id="emails_<?php echo $row['clid']; ?>">
                                <?php
                                if (strstr($row['email1'], '@iidc.eu'))
                                    $color = 'red';
                                else
                                    $color = 'green';
                                echo '<span style="color:' . $color . '">' . $row['email1'] . '</span>';

                                if (trim($row['email2']) != '')
                                    echo '<br/>' . $row['email2'];
                                ?>
                            </span>
                        </span>
                    </div> */?>
                    <?php if (trim($row['scope_of_activities']) != '') { ?>
                        <fieldset style="margin-top:5px" onclick="showHideScope(this)" <?php echo ($_POST['srch'] == 'scope_of_activities')?'class="expanded"':'';?>>
                            <legend>Scope of activities</legend>
                            <?php
                            echo trim($row['scope_of_activities']);
                            ?>
                        </fieldset>
                    <?php } ?>
                </td>
                <td style="white-space:nowrap" class="office">
                    <?php echo $officeName[$row['offid']]; ?><br />
                    <?php echo trim($row['hqc_contact_person']) != '' ? '<span style="color:green">' . $row['hqc_contact_person'] . '</span>' : ''; ?>
                </td>
                <td style="white-space:nowrap" class="auditorsHolder">
                    <?php if (isset($auditor_clients) && isset($auditor_clients[$row['clid']])) {
                        $clientAudit = $auditor_clients[$row['clid']];
                    ?>
                        <b>Date:</b> <?php echo $clientAudit['date']; ?><br />
                        <b>leading:</b> <span <?php echo (isset($uid) && $clientAudit['leading'] == $uid) ? 'style="color:green"' : ''; ?>><?php echo $auditors[$clientAudit['leading']]; ?></span>
                        <?php if (isset($clientAudit['co'])) {
                            echo '<br/><b>Co-auditors:</b> ';
                            foreach ($clientAudit['co'] as $auid) { ?>
                                <span <?php echo (isset($uid) && $auid == $uid) ? 'style="color:green"' : ''; ?>><?php echo $auditors[$auid]; ?></span><br />
                        <?php }
                        }; ?>
                    <?php }; ?>

                </td>
                <?php
                $clientOf = '';
                if (isset($_SESSION['user_type']) and $_SESSION['user_type'] == "client")
                    $clientOf = "&clof=$clid";
                ?>
                <td nowrap="nowrap" class="certificate_<?php echo $crtClr == "orange" ? "green" : $crtClr; ?>">
                    <i class="fas fa-file-contract load_popup" style="float:left;color:<?php echo $crtClr; ?>" data-url="load_company_certificates.php?clid=<?php echo $row['clid']; ?>&login=true" title="<?php echo $row['company_name']; ?>"></i>
                    <?php if (isset($certificates[$row['clid']]) && $certificates[$row['clid']] != 0) { ?>

                        <div style="float:left;color:<?php echo $crtClr; ?>">
                            Iss. On: <?php echo date("d/m/Y", $certificatesIssue[$row['clid']]); ?><br />
                            Exp. On: <?php echo date("d/m/Y", $certificates[$row['clid']]); ?>
                            <?php if ($certificates[$row['clid']] > time()) { ?>
                                <br />Exp. In: <?php echo ($timeToGoString); ?>
                        </div>
                    <?php } else { ?>
                        <br />Expired</div>
                    <?php } ?>
                <?php } else { ?><span>No Cert.</span><?php }; ?>
                </td>
                <?php if ((isset($user_permissions) and in_array("clients_actions", $user_permissions)) or $_SESSION['user_type'] == "admin" or isset($_SESSION['offid']) && $_SESSION['offid'] != 0) { ?>
                    <td nowrap="nowrap" class="actions">
                        <div>
                            <a href="../company/?inc=production_sites&clid=<?php echo $row['clid']; ?>" title="Production sites">
                                <i class="fa fa-building" aria-hidden="true" style="color: <?php echo isset($production_sites[$row['clid']]) ? 'green' : ''; ?>" title="Production sites"><span><?php echo isset($production_sites[$row['clid']]) ? $production_sites[$row['clid']] : 0; ?></span></i></i></a>
                            <a href="../company/products/index.php?inc=products_list&clid=<?php echo $row['clid']; ?>"><i class="fa fa-th-large" style="color: <?php echo isset($products[$row['clid']]) ? 'green' : ''; ?>" title="Products"><span><?php echo isset($products[$row['clid']]) ? $products[$row['clid']] : 0; ?></span></i></a>
                            <a href="../company/?inc=clients&clid=<?php echo $row['clid']; ?>" title="clients"><i class="fa fa-users" style="color: <?php echo isset($clients[$row['clid']]) ? 'green' : ''; ?>"><span><?php echo isset($clients[$row['clid']]) ? $clients[$row['clid']] : 0; ?></span></i></a>
                        </div>
                        <div style="margin-top:10px">
                            <a href='../company/index.php?inc=profile&act=edit&id=<?php echo $row['clid'] . $clientOf; ?>'><i class="far fa-edit"></i></a>

                            <i class="fa fa-trash-alt" data-id="<?php echo $row['clid']; ?>" data-url="admin_save.php"></i>

                            <?php if ($row['active'] == "y") { ?>
                                <i class="fas fa-unlock" style="color:green" title="Suspend account" onclick="susClient(<?php echo $row['clid']; ?>)"></i>
                            <?php } else { ?>
                                <i class="fas fa-lock" style="color:red" title="Reactivate account" onclick="reinsClient(<?php echo $row['clid']; ?>)"></i>
                            <?php }; ?>
                        </div>
                    </td>
                <?php }; ?>
            </tr>
<?php
        }
    }
}

?>