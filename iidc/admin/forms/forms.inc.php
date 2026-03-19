<?php
/*
Name: forms
*/
if (!defined("__HQC__")) {
    exit();
};
?>
<style>
    span.info {
        font-size: 12px;
        padding: 5px;
        display: inline-block;
        width: 39%;
        color: var(--color80);
    }

    span.docType {
        width: 18% !important;
    }

    span.info i {
        color: var(--color80);
        font-size: 12px !important;
    }
</style>
<script>
    function publishUnpublishForm(obj) {

        jQuery.post('form_save.php', {
            act: 'change_published_status',
            published: jQuery(obj).data('published'),
            foid: jQuery(obj).data('id')
        }).done(function(data) {
            if (data) {
                if (data.indexOf("error:") > -1) {
                    alert_message(data.replace('error:', ''));
                    return false;
                }

                location.reload()
            }

        })
    }
    //function to search forms and applications in formsAndApplications table
    function searchFormsAndApplications() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchFormsAndApplications");
        filter = input.value.toUpperCase();
        table = document.getElementById("formsAndApplications");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>
<div>
    Search for form: <input type="text" id="searchFormsAndApplications" placeholder="Search" style="width:20%" onkeyup="searchFormsAndApplications()">
</div>
<table style="width:100%" class="alternateOn">
    <thead>
        <tr>
            <th style="width:20px">::</th>
            <?php if (!isset($_GET['cat'])) { ?>
                <th style="width:80px"><?php _e('ID'); ?></th>
            <?php }; ?>
            <th><?php _e('Form name'); ?></th>
            <?php if (!isset($_GET['cat'])) { ?>
                <th><?php _e('Category'); ?></th>
                <th style="width:50px"><?php _e("Revision"); ?></th>
                <th style="width:100px"><?php _e('Date'); ?></th>
                <th style="width:100px"><?php _e('Modified'); ?></th>
            <?php }; ?>
            <th style="width:100px"><?php _e('Options'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th style="width:20px">::</th>
            <?php if (!isset($_GET['cat'])) { ?>
                <th style="width:80px"><?php _e('ID'); ?></th>
            <?php }; ?>
            <th><?php _e('Form name'); ?></th>
            <?php if (!isset($_GET['cat'])) { ?>
                <th><?php _e('Category'); ?></th>
                <th><?php _e('Revision'); ?></th>
                <th style="width:100px"><?php _e('Date'); ?></th>
                <th style="width:100px"><?php _e('Modified'); ?></th>
            <?php }; ?>
            <th style="width:220px"><?php _e('Options'); ?></th>
        </tr>
    </tfoot>
    <tbody id="formsAndApplications">
        <?php
        $nr = 1;

        if ($categories = get_hqc_options('form_categories')) {
            if (is_array(json_decode($categories, true))) {
                $categories = json_decode($categories, true);
                $categories['0'] =  'Form';
            }
        }
        $documents = array();
        if ($documents_to_download = $hqcdb->get_results("SELECT * FROM hqc_filestore WHERE parent = 'form'")) {
            foreach ($documents_to_download as $document) {
                $documents[$document['parent']][$document['parent_id']] = '/clients/documents/download_document.php?location=general-data&file=' . str_replace(' ', '-', $document['file_name']);
            };
        };
        $document_types = array("form" => '<i class="fab fa-wpforms" title="Fill-in document"></i>', "download_document" => '<i class="fas fa-download" title="Downloadable document"></i>', "both" => '<i class="fab fa-wpforms" title="Fill-in document"></i><i class="fas fa-download" title="Downloadable document"></i>');
        if (!isset($_GET['cat']))
            $whr = "AND category != '0'";
        else
            $whr = "AND category = '0'";
        if ($theForms = $hqcdb->get_results("SELECT * FROM hqc_forms where status!='example' $whr and status!='deleted' order by form_id ASC ")) {
            foreach ($theForms as $form) {
                if (trim($form['form_meta']) != '' and is_array(json_decode($form['form_meta'], true)))
                    $form_meta = decode_json($form['form_meta']);
                else
                    $form_meta = array();
                if (!$form_options = decode_json($form['form_options']))
                    $form_options = unserialize($form['form_options']);

                if (isset($form_options['approval_required_by_client']))
                    $approval_required_by_client = strtoupper($form_options['approval_required_by_client']);
                else
                    $approval_required_by_client = 'NO';
                if (isset($form_options['approval_required']))
                    $approval_required = strtoupper($form_options['approval_required']['type']);
                else
                    $approval_required = '';
        ?>
                <tr>
                    <th><?php echo $nr++; ?></th>
                    <?php if (!isset($_GET['cat'])) { ?>
                        <td><?php echo $form['form_id']; ?></td>
                    <?php }; ?>
                    <td><?php echo $form['form_name']; ?> <?php if (!isset($_GET['cat'])) { ?><?php echo (trim($form['remarks']) != "") ? '(' . $form['remarks'] . ')' : ''; ?>
                        <div style="border-top:1px solid var(--color50);margin-top:10px">
                            <span class="info docType">Type:<?php echo isset($document_types[$form['form_type']]) ? $document_types[$form['form_type']] : ''; ?></span>
                            <span class="info" <?php echo $approval_required == '' ? 'style="color:var(--color30)"' : ''; ?>>Admin approval required:<?php echo $approval_required != '' ? $approval_required : 'NO'; ?></span>
                            <span class="info" <?php if ($approval_required_by_client == 'NO') echo 'style="color:var(--color30)"'; ?>>Client approval required: <?php echo $approval_required_by_client; ?></span>
                        </div>
                    <?php }; ?>
                    </td>
                    <?php if (!isset($_GET['cat'])) { ?>
                        <td class="nowrap"><?php echo $categories[$form['category']]; ?></td>
                        <td class="nowrap" style="text-align:center"><?php echo $form['revision']; ?></td>
                        <td><?php echo isset($form_meta['revision_date']) ? $form_meta['revision_date'] : ''; ?></td>
                        <td><?php echo isset($form_meta['modification_date']) ? $form_meta['modification_date'] : ''; ?></td>
                    <?php }; ?>
                    <td>
                        <?php if ($form['form_type'] == "download_document" and isset($documents['form_' . $form['foid']])) {
                        ?>
                            <a href="<?php echo $documents['form_' . $form['foid']]; ?>"><i class="fas fa-file-download" style="margin-right:8px"></i></a>
                        <?php } else { ?>
                            <a href="view_form.php?foid=<?php echo $form['foid']; ?>" title="<?php _e('View form'); ?>" target="pdfIframe"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <?php }; ?>
                        <a href="?inc=form-maker&foid=<?php echo $form['foid']; ?><?php echo isset($_GET['cat']) ? '&cat=0' : ''; ?>" title="<?php _e('Edit Application'); ?>"><i class="far fa-edit"></i></a>
                        <?php if ($form['form_type'] == 'form' && !isset($_GET['cat'])) { ?>
                            <a href="?inc=form-maker&foid=<?php echo $form['foid']; ?>&sandbox=1" title="<?php _e('Edit Form'); ?>"><i class="far fa-edit" style="color:orange"></i></a>
                        <?php }; ?>
                        <a href="?inc=form-maker&foid=<?php echo $form['foid']; ?>&act=copy" title="<?php _e('Make a copy'); ?>"><i class="far fa-copy"></i></a>
                        <i class="fa fa-trash-alt" data-save="form" data-id="<?php echo $form['foid']; ?>" aria-hidden="true" data-confirm="<?php _e('Are you sure? Delete Form'); ?>" title="<?php _e('Delete Form'); ?>"></i>
                        <i class="fa fa-toggle-<?php echo ($form['status'] == "active") ? "on" : "off"; ?> status" aria-hidden="true" data-save="form" data-id="<?php echo $form['foid']; ?>" title="<?php _e('Activate / Deactivate Form'); ?>"></i>
                        <i class="far fa-check-square" data-id="<?php echo $form['foid']; ?>" data-published="<?php echo $form['published']; ?>" style="color:<?php echo ($form['published'] == "yes" && $form['status'] == "active") ? "green" : "#eee"; ?>" onclick="publishUnpublishForm(this)" title="Make it available for clients"></i>
                    </td>
                </tr>
            <?php }; ?>
    </tbody>
<?php }; ?>
</table>
<center>
    <a href="?inc=form-maker<?php echo isset($_GET['cat']) ? '&cat=0' : ''; ?>" class="button"><?php _e('Add new form'); ?></a>
    <?php if (!isset($_GET['cat'])) { ?>
        <a href="rearrange-documents.php" class="loadContent button">Rearrange documents</a>
        <a href="?inc=stages" class="button">Application process</a>
    <?php }; ?>
</center>