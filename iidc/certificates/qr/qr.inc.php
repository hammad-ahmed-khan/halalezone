<?php if (!defined('__HQC__')) {
    exit();
} ?>
<h2>QR validated certificates</h2>
<?php
$QRS_checked = array();
$crt_checked = array();

function get_certificate()
{
    global $hqcdb, $crt_checked;
    if ($crts = $hqcdb->get_results("SELECT hqc_certificates_annual.awarded_to AS company_name,hqc_certificates_annual.url, hqc_certificates_annual.date_of_issue,hqc_certificates_checked.* FROM hqc_certificates_annual
    JOIN hqc_certificates_checked ON hqc_certificates_checked.crtNr = hqc_certificates_annual.certificate_nr")) {
        foreach ($crts as $crt) {
            $crt['date'] = date("d/m/Y", $crt['date_of_issue']);
            unset($crt['date_of_issue']);
            $crt_checked[$crt['crtNr']]  = $crt;
        }
    }

    if ($crts = $hqcdb->get_results("SELECT hqc_certificates_a.company_name,hqc_certificates_a.url,hqc_certificates_a.date,hqc_certificates_a.issue_date, hqc_certificates_checked.* FROM hqc_certificates_a
    JOIN hqc_certificates_checked ON hqc_certificates_checked.crtNr = hqc_certificates_a.hc_nr")) {
        foreach ($crts as $crt) {
            if(trim($crt['issue_date'])!='')
            $crt['date'] = $crt['issue_date'];
            else
            $crt['date'] = date("d/m/Y", strtotime($crt['date']));
            $crt_checked[$crt['crtNr']]  = $crt;
        }
    }

    if ($crts = $hqcdb->get_results("SELECT hqc_certificates_b.company_name,hqc_certificates_b.url,hqc_certificates_b.date, hqc_certificates_b.issue_date, hqc_certificates_checked.* FROM hqc_certificates_b
    JOIN hqc_certificates_checked ON hqc_certificates_checked.crtNr = hqc_certificates_b.hc_nr")) {
        foreach ($crts as $crt) {
            if(trim($crt['issue_date'])!='')
            $crt['date'] = $crt['issue_date'];
            else
            $crt['date'] = date("d/m/Y", strtotime($crt['date']));
            $crt_checked[$crt['crtNr']]  = $crt;
        }
    }
}


get_certificate();

if ($QRS = $hqcdb->get_results("SELECT * FROM hqc_certificates_checked ORDER BY date DESC")) {
    foreach ($QRS as $QR) {
        if (isset($crt_checked[$QR['crtNr']])) {
            $QR['company_name'] = $crt_checked[$QR['crtNr']]['company_name'];
            $QR['url'] = $crt_checked[$QR['crtNr']]['url'];
            $QR['crt_date'] = $crt_checked[$QR['crtNr']]['date'];
            $QRS_checked[$QR['nr']] = $QR;
        }
    };
}

if (count($QRS_checked) > 0) {
    include shared_path . "/countries.code.php";
    $certFilesDir = hqc_path . "/data/certificates";
    $srNR = 1; ?>
    <style>
        .valid {
            color: green
        }

        .expired {
            color: red;
            text-transform: capitalize;
        }
    </style>
    <table class="alternate">
        <thead>
            <tr>
                <th>#</th>
                <th colspan="2">QR check</th>
                <th colspan="5">Certificate</th>
            </tr>
            <tr>
                <th></th>
                <th>Date / Time</th>
                <th>Country / city</th>
                <th>Certificate number</th>
                <th>Type</th>
                <th>Issue date</th>
                <th>Awarded to</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($QRS_checked as $qr) { ?>
                <tr>
                    <th><?php echo $srNR++; ?></th>
                    <td style="white-space: nowrap;"><?php echo date("d/m/Y H:i:s", strtotime($qr['date'])); ?></td>
                    <td><?php echo $country[$qr['country']]; ?>, <?php echo $qr['city']; ?></td>
                    <td>
                        <?php if (trim($qr['url']) != '' && file_exists($certFilesDir . '/' . $qr['url'])) { ?>
                            <a target="_new" href="<?php echo hqc_url ; ?>/certificates/view/<?php echo $qr['url']; ?>?act=print"><i class="far fa-file-pdf"></i><?php echo $qr['crtNr']; ?></a>
                        <?php } else { ?>
                            <?php echo $qr['crtNr']; ?>
                        <?php }; ?>
                    </td>
                    <td style="text-transform: capitalize;white-space:nowrap"><?php echo $qr['certificate_type'] == 'annual' ? 'Annual certificate' : 'Batch certificate Type ' . $qr['certificate_type']; ?></td>
                    <td><?php echo $qr['crt_date']; ?></td>
                    <td><?php echo $qr['company_name']; ?></td>
                    <td><?php echo $qr['check_result'] == 'active' ? '<span class="valid">Valid</span>' : '<span class="expired">' . $qr['check_result'] . '</span>'; ?></td>
                </tr>
            <?php }; ?>
        </tbody>
    </table>
<?php }
