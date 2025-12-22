<?php
function save_version($crtNr,$act='edit'){
    global $amdb;
    if ($data = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE crtNr = '$crtNr'")) {

        unset($data['certificate_content']);
        $old_data['item_id'] = $_POST['crtNr'];
        $old_data['vr'] = $data['vr'];
        $old_data['item_content'] = serialize($data);
        $old_data['item_table'] = "acms_halal_certificates";
        $old_data['item_action'] = $act;
        $old_data['item_url'] = $data['url'];

        if ($item_content = $amdb->get_row("SELECT item_content,inserted_on FROM hqc_versions WHERE item_id = '$crtNr' AND item_table = 'acms_halal_certificates' ORDER BY inserted_on DESC")) {

            //remove all white spaces and compare the two strings
            if (preg_replace('/\s+/', '', $old_data['item_content']) != preg_replace('/\s+/', '', $item_content['item_content'])) {
                $amdb->insert("hqc_versions", $old_data);
            }
        } else {
            $verid = $amdb->insert("hqc_versions", $old_data);
        }
    }
}