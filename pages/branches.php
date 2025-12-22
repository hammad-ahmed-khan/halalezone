<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Manage Branches - Halal e-Zone</title>
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
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="adminGrid" style="min-height: 1px !important; width:100%;"></table>
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
                <h4 class="modal-title" id="adminModal-label">Add Branch</h4>
            </div>
            <div class="modal-body row">
                <form id="admin-form" class="col-md-12 form-horizontal">
                  <input type="hidden" id="company_id" name="company_id"  />
                  <input type="text" hidden id="adminid"/>
                     
                    <div class="row form-group">
                    <label class="col-xs-12 col-md-4">User Name</label>
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
                        <!--
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

                        -->

 
                    <div class="row form-group">
  <label class="col-xs-12 col-md-4">Branch Address </label>
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
                          <input type="text" class="form-control" name="other-category" id="other-category" placeholder="Other Category" style="display: none; margin-top:5px;" value="" />
                        <div class="alert-string category"></div>
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
                    
                  
                    
                   
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="BP.onSave();" >Save changes</button>
            </div>
        </div>
    </div>
</div>
<?php include_once('pages/footer.php');?>
<script src="js/jquery-2.1.4.min.js"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="js/jquery-1.11.3.min.js"></script>
<![endif]-->
<script src="js/bootstrap.min.js"></script>
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
    BP.onDocumentReady();
	var chosenLoaded = false;
	$(document).ready(function(e) {
        $('input[type=radio]').click(function(e) {
            var rel = $(this).attr('id');
			if (rel == 'isclient2' && !chosenLoaded) {
				//alert('tes');
				//$('.chosen-select').chosen('destroy').chosen();
				chosenLoaded = true;
			}			
			$('.rel').hide();
			$('div[rel*='+rel+']').show();
        });
    });

    $(document).ready(function(){
        // Initially hide the company admin field
        if ( $("#company_id").val() !== "" ) { 
            $("#company_admin_field").show();
        } else { 
            $("#company_admin_field").hide();
        } 
        // Show/hide company admin field based on company selection
        $("#company_id").change(function(){
            if($(this).val() !== "") {
                $("#company_admin_field").show();
            } else {
                $("#company_admin_field").hide();
            }
        });
    });
</script>

</body>
</html>