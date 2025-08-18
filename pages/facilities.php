<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Facility Management - Halal e-Zone</title>
    <style>
    .rel {
		display:none;
	}
.chosen-container {
    min-width:100%;
}	
    </style>
</head>

<body>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 ORDER BY name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		echo json_encode(generateErrorResponse("Getting clients list failed"));
		die();
	}
	$clients = $stmt->fetchAll();

    $sql = "SELECT id, name FROM tcompanies WHERE active=1 ORDER BY name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		echo json_encode(generateErrorResponse("Getting clients list failed"));
		die();
	}
	$companies = $stmt->fetchAll();

    $country_list = array(
        "AF" => "Afghanistan",
        "AX" => "Aland Islands",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BQ" => "Bonaire, Sint Eustatius and Saba",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CD" => "Congo, the Democratic Republic of the",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Cote D'Ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CW" => "Curacao",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and Mcdonald Islands",
        "VA" => "Holy See (Vatican City State)",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KP" => "Korea, Democratic People's Republic of",
        "KR" => "Korea, Republic of",
        "XK" => "Kosovo",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People's Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libyan Arab Jamahiriya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia, the Former Yugoslav Republic of",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States of",
        "MD" => "Moldova, Republic of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territory, Occupied",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russian Federation",
        "RW" => "Rwanda",
        "BL" => "Saint Barthelemy",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "MF" => "Saint Martin",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "CS" => "Serbia and Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SX" => "Sint Maarten",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and the South Sandwich Islands",
        "SS" => "South Sudan",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TW" => "Taiwan, Province of China",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic of",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela",
        "VN" => "Viet Nam",
        "VG" => "Virgin Islands, British",
        "VI" => "Virgin Islands, U.s.",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
      );
      
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        <h3 style="margin-bottom:25px;"> Facility Management</h3>
                        <p>The Facility Management feature provides a centralized solution for overseeing and managing multiple facilities from a single account. With this feature, you no longer need to create and manage separate accounts for each facility's certification process. Instead, you can streamline and coordinate the management of all your facilities under your primary account.</p>
<p><strong>Key Benefits:</strong></p>
<ul>
    <li><strong>Unified Management:</strong> Efficiently handle the certification process for all your facilities from one central location.</li>
    <li><strong>Simplified Access:</strong> Avoid the complexity of managing multiple accounts by consolidating everything under your main account.</li>
    <li><strong>Enhanced Coordination:</strong> Seamlessly manage and monitor your facilities' process status in a unified interface.</li>
</ul><br/>

                        <!-- PAGE CONTENT BEGINS -->
                        <table id="adminGrid" style="min-height: 1px !important;"></table>
                        <div id="adminPager"></div>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- Admin Modal -->
<div class="modal fade" id="adminModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="adminModal-label">Add Facility</h4>
            </div>
            <div class="modal-body row">
                <form id="admin-form" class="col-md-12 form-horizontal">
                  <input type="hidden" id="adminid"/>
                  <input type="hidden" id="prefix"/>
                    <?php /*
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Company (Optional)</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" name="company_id" id="company_id">
                                <option value="">Select Company</option>
                            	<?php foreach ($companies as $company): ?>
                                	<option value="<?php echo $company["id"]; ?>"><?php echo $company["name"];?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="alert-string"></div>
                    </div></div>
                    
                    <div class="row form-group" id="company_admin_field" style="display: none;">
            <label class="col-xs-12 col-md-4">Company Admin</label>
            <div class='col-xs-12 col-md-8'>
                <input type="checkbox" id="company_admin" name="company_admin" value="1">
                <span class="checkbox-label">Check this box if this client is the company admin.</span>
                <div class="alert alert-info">
                    The company administrator has the authority to create sub-accounts for individual branches. 
                    The company administrator can oversee the applications of all branches. 
                    Additionally, each individual branch can log in using their own unique credentials to manage their respective applications.
                        </div>
                    </div>
                </div>
                */ 
  
                ?>                        
                    <div class="row form-group">
                    <label class="col-xs-12 col-md-4">Facility Name  
                        <span class="tooltip-icon" data-placement="bottom" data-toggle="tooltip" title="Please provide a unique name for this facility to differentiate it from others. You can include the location (e.g., '<?php echo $_SESSION['halal']['user']; ?> - New York'), a facility code (e.g., '<?php echo $_SESSION['halal']['user']; ?> - Facility 001'), or the facility type (e.g., '<?php echo $_SESSION['halal']['user']; ?> - Manufacturing Plant'). This helps to identify the facility while keeping the main account name consistent.">
    <i class="fa fa-info-circle"></i>
</span></label>
                    <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" id="name" maxlength="50"/>
                       
                        <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Email</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="email" maxlength="500"/>
                            <div class="alert-string"></div>
                        </div></div>
                        <?php /*
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Prefix</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="prefix" maxlength="15"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Login</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="login" maxlength="20"/>
                            <div class="alert-string"></div>
                        </div></div>
                    
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Password</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="pass" maxlength="10"/>
                            <div class="alert-string"></div>
                        </div></div>
                                    */?>
 
                    <div class="row form-group">
  <label class="col-xs-12 col-md-4"> Address </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="address" id="address" value="" />
    <div class="alert-string address"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-4">City </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="city" id="city" value="" />
    <div class="alert-string city"></div>
  </div>
</div>
<input type="hidden" name="state" id="state" value="" />
<!--
<div class="row form-group">
  <label class="col-xs-12 col-md-4">State </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="state" id="state" value="" />
    <div class="alert-string state"></div>
  </div>
</div>
-->
<div class="row form-group">
  <label class="col-xs-12 col-md-4">Zip Code </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="zip" id="zip" value="" />
    <div class="alert-string zip"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-4">Country </label>
  <div class='col-xs-12 col-md-8'>
    <select name="country" id="country" class="form-control">
      <option value="">Please Select</option>
      <?php foreach ($country_list as $country): ?>
        <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
      <?php endforeach; ?>
    </select>
    <div class="alert-string country"></div>
  </div>
</div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Industry </label>
                      <div class='col-xs-12 col-md-8'>
                        <select class="form-control" name="industry" id="industry">
                        	<option value=""></option>
                        	<option value="Slaughter Houses">Slaughter Houses</option>
                            <option value="Meat Processing">Meat Processing</option>
                            <option value="All Other">All Other</option>
                        </select>
                        <div class="alert-string industry"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Product Category </label>
                      <div class='col-xs-12 col-md-8'>
                        <?php $categories = getProductCategories(); ?>
                          <select name="category" id="category" class="form-control">
                            <option value=""></option>  
                            <?php foreach ($categories as $i => $category): ?>
                              <option value="<?php echo $category; ?>"><?php echo preg_replace ("/<sup>(.*?)<\/sup>/i", "", $category); ?></option>
                            <?php endforeach; ?>
                            <option value="other">Other</option>
                          </select>                          
                        <div class="alert-string category"></div>
                        <input type="text" class="form-control" name="other-category" id="other-category" placeholder="Other Category" style="display: none; margin-top:5px;" value="" />

                      </div>
                    </div>
                    
                     <div class="row form-group">
                      <label class="col-xs-12 col-md-4">VAT Number </label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="vat" id="vat" value="" />
                        <div class="alert-string vat"></div>
                      </div>
                    </div>
                   
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Contact Person Name  </label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="contact_person" id="contact_person" value="" />
                        <div class="alert-string contact_person"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Phone Number</label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="phone" id="phone" value="" />
                        <div class="alert-string phone"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of raw materials(estimated)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="ingrednumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group" >
                        <label class="col-xs-12 col-md-4">Number of products to be certified(estimated)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="prodnumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
    <label class="col-xs-12 col-md-4">Is your facility a pork-free facility?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="pork_free_facility" value="Yes"> Yes</label>
        <label><input type="radio" name="pork_free_facility" value="No"> No</label>
        <div class="alert-string pork_free_facility"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Do you have dedicated lines for Halal production?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="dedicated_halal_lines" value="Yes"> Yes</label>
        <label><input type="radio" name="dedicated_halal_lines" value="No"> No</label>
        <label><input type="radio" name="dedicated_halal_lines" value="Not applicable"> Not applicable</label>
        <div class="alert-string dedicated_halal_lines"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">What are your target export regions?</label>
    <div class='col-xs-12 col-md-8'>
        <input type="text" class="form-control" name="export_regions" id="export_regions" value="" />
        <div class="alert-string export_regions"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Are the products to be Halal certified, produced by a third party?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="third_party_products" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_products" value="No"> No</label>
        <div class="alert-string third_party_products"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Is this third party Halal certified?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="third_party_halal_certified" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_halal_certified" value="No"> No</label>
        <label><input type="radio" name="third_party_halal_certified" value="Not applicable"> Not applicable</label>
        <div class="alert-string third_party_halal_certified"></div>
    </div>
</div>
 
<?php /*
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Role</label>
                        <div class='col-xs-12 col-md-8'>
                               <div class="col-sm-8">
                          <label class="radio-inline">
                              <input type="radio" name="isclient" id="isclient0" value="0">Admin
                            </label>
                            <label class="radio-inline">
                              <input type="radio" name="isclient" id="isclient1" value="1" checked>Client
                            </label>
                            <label class="radio-inline">
                              <input type="radio" name="isclient" id="isclient2" value="2">Auditor
                            </label>
                            
                            <div class="alert-string"></div>
                        </div></div>
                    </div>
                    <div class="row form-group rel" rel="isclient2">
                    <label class="col-xs-12 col-md-4">Source of Raw Material</label>
                    <div class='col-xs-12 col-md-8'>
                        <select id="sources_audit" name="sources_audit[]" class="form-control chosen-select" multiple>
                            <option role="option" value="">Not specified</option>
                            <option role="option" value="Animal">Animal</option>
                            <option role="option" value="Plant">Plant</option>
                            <option role="option" value="Synthetic">Synthetic</option>
                            <option role="option" value="Mineral">Mineral</option>
                            <option role="option" value="Cleaning agents">Cleaning agents</option>
                            <option role="option" value="Other agents">Other agents</option>
                        </select>
                        <div class="alert-string"></div>
                    </div></div>
                    <div class="row form-group rel" rel="isclient2">
                        <label class="col-xs-12 col-md-4">Clients</label>
                        <div class='col-xs-12 col-md-8'>
                            <select id="clients_audit" name="clients_audit[]" class="form-control chosen-select" multiple>
                            	<?php foreach ($clients as $client): ?>
                                	<option value="<?php echo $client["id"]; ?>"><?php echo $client["name"];?> - <?php echo $client["id"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient1">
                        <label class="col-xs-12 col-md-4">Ingredients Limit</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="ingrednumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient1">
                        <label class="col-xs-12 col-md-4">Products Limit</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="prodnumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">Dashboard</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="dashboard" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">Applications</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="application" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
						<div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">Calendar</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="calendar" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>                        
                    <div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">Products</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="products" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">Ingredients</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="ingredients" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient0,isclient1">
                        <label class="col-xs-12 col-md-4">QM Documents</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="documents" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group rel" rel="isclient0">
                        <label class="col-xs-12 col-md-4">Facility Management</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="canadmin" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                        */  ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="FP.onSave();" >Save changes</button>
            </div>
        </div>
    </div>
</div>
<?php include_once('pages/footer.php');?>
<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/sha512.js"></script>
<script src="js/chosen/chosen.jquery.min.js"></script>
<script src="js/all.js?v=<?php echo rand(); ?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    FP.onDocumentReady();
 $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 

 })
</script>

</body>
</html>