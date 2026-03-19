<?php
if (!session_id()) {
    session_start();
}
include($_SESSION['hqc_path'] . '/load.inc.php');

if (isset($_POST['getHQCData']) && isset($_POST['get'])) {
    if ($_POST['get'] == 'data') {
        $hqcData = get_office_data(0);
        if (isset($hqcData[$_POST['getHQCData']]))
            echo $hqcData[$_POST['getHQCData']];
    }
    exit();
}
$options = '';

if ($_POST['elementName'] == 'select') {
    $options = '<option value="">Please Select</option>';
} else if ($_POST['elementName'] == 'title') {
    $options = '<option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Miss">Miss</option><option value="Ms">Ms</option><option value="Dr">Dr</option><option value="Prof">Prof</option>';
} else if ($_POST['elementName'] == 'countries') {
    if (!isset($country))
        require shared_path . "/countries.code.php";
    $options .= "<option value=''>Select a country</option>";
    foreach ($country as $countryKey => $countryValue) {
        $options .= "<option value='$countryKey'>$countryValue</option>";
    }
} else if ($_POST['elementName'] == 'languages') {
    //create langues list of all countries in the world
    $langs = json_decode(file_get_contents(shared_path . "/languages.json"), true);
    $options .= "<option value=''>Select a language</option>";
    foreach ($langs as $lngKey => $langValue) {
        $options .= "<option value='$lngKey'>$langValue[name] - $langValue[nativeName]</option>";
    }
} else if ($_POST['elementName'] == 'offices') {

    $options = '<option value="">Select an office</option>';
    $offices = get_offices();
    foreach ($offices as $office) {
        $options .= '<option value="' . $office['offid'] . '">' . $office['company_name_english'] . ' (' . $office['contact_person'] . ')</option>';
    }
}

if ($_POST['elementName'] == 'address') {

    ob_start();
?>
    <tr>
        <td><strong>Title:</strong></td>
        <td>
            <select name="title" id="title" required>
                <option value="Mr">Mr</option>
                <option value="Mrs">Mrs</option>
                <option value="Miss">Miss</option>
                <option value="Ms">Ms</option>
                <option value="Dr">Dr</option>
                <option value="Prof">Prof</option>
            </select>
        </td>
    </tr>
    <tr id="insertedBlock">
        <td><strong>Name:</strong></td>
        <td>
            <input type="text" id="name" name="name" required placeholder="First name" style="max-width: 45%;">/
            <input type="text" id="surname" name="surname" required placeholder="Last name" style="max-width: 45%;">
        </td>
    </tr>
    <?php
    $nameInputs = ob_get_clean();
    //create html address form with title,name,surname,zipcode,city,country,telephone and email address
    if (!isset($country))
        require shared_path . "/countries.code.php";
    $options = "<option value=''>Select a country</option>";
    foreach ($country as $countryKey => $countryValue) {
        $options .= "<option value='$countryKey'>$countryValue</option>";
    }
    ob_start();
    ?>
    <tr>
        <td><strong>Street:</strong></td>
        <td>
            <input type="tel" id="street" name="street" required style="width:65%">
        </td>
    </tr>
    <tr>
        <td><strong>Zip Code:</strong></td>
        <td>
            <input type="text" id="zipcode" name="zipcode" required style="width: 100px;">
            <strong>City:</strong>
            <input type="text" id="city" name="city" required>
        </td>
    </tr>
    <tr>
        <td><strong>Country:</strong></td>
        <td>
            <select type="text" id="country" name="country" required>
                <?php echo $options; ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><strong>Telephone:</strong></td>
        <td>
            <input type="tel" id="telephone" name="telephone" required>
        </td>
    </tr>
    <tr>
        <td><strong>Email Address:</strong></td>
        <td><input type="email" id="email" name="email" required>
        </td>
    </tr>
<?php
    $address = ob_get_clean();
    $options = $address;

    if ($_POST['get'] == 'nameInputs')
    $options = $nameInputs;

    if($_POST['get'] == 'full-address')
    $options = $nameInputs.$address;

}
echo $options;
