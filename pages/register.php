<?php
include_once('config/config.php');
include_once('classes/users.php');
$is_login_page = true;
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

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('pages/header.php');?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<title>Register - Halal e-Zone</title>
<style>
label {
	text-align:right;
}
.blockUI.blockMsg.blockPage {
	border: none !important; 
	background-color: #000 !important; 
	-webkit-border-radius: 10px;
	-moz-border-radius': 10px; 
	opacity: .5; 
	color: #fff !important;
}
.blockUI.blockMsg.blockPage h1 {
	font-size: 18px !important;
	margin: 15px 0 !important;
}
.skiptranslate iframe {
    display: none !important;
    } 
body {
    top: 0px !important; 
    }
</style>
</head>
<body>
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div id="logo"></div>

        <div id="success" class="alert- alert-success- hidden text-center" style="margin-top:25px; font-size:18px;">
                   <h1>Thanks for the registration!</h1>

                   <div>
                    <i class="fa fa-check-circle-o" style="font-size:100px;margin: 15px 0 25px;"></i> 
                   </div>

                   <div class="col-md-6 col-md-offset-3">
                   <p><!--We have sent an application form to the email address you provided. Please check your email for further instructions on how to complete and upload the form using the link provided. If you require any assistance or have any questions, please do not hesitate to contact us.-->
                   Your information has been successfully received. We will review it thoroughly and aim to provide you with a response within a few days.
                  </p>
                   </div>

                  </div>

        <div class="row">
          <div class="col-md-7 col-md-offset-2">
            <div class="clearfix">
              <div class="box">
                <div class="content-wrap">
                  <div id="mainForm">
                   <div id="errors" class="alert alert-danger hidden"></div>
                   <h3 style="text-align:center;margin-bottom:30px;">Registration</h3>
                   <form id="admin-form" class="col-md-12 form-horizontal">
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Language Preference</label>
                      <div class='col-xs-12 col-md-7'><div id="google_translate_element_n"></div></div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Company Name <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="name" id="name" value="" />
                        <div class="alert-string name"></div>
                      </div>
                    </div>
                    <div class="row form-group">
  <label class="col-xs-12 col-md-5">Company Address <span class="text-danger">*</span></label>
  <div class='col-xs-12 col-md-7'>
    <input type="text" class="form-control" name="address" id="address" value="" />
    <div class="alert-string address"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-5">City <span class="text-danger">*</span></label>
  <div class='col-xs-12 col-md-7'>
    <input type="text" class="form-control" name="city" id="city" value="" />
    <div class="alert-string city"></div>
  </div>
</div>
<input type="hidden" name="state" id="state" value="" />
<!--
<div class="row form-group">
  <label class="col-xs-12 col-md-5">State </label>
  <div class='col-xs-12 col-md-7'>
    <input type="text" class="form-control" name="state" id="state" value="" />
    <div class="alert-string state"></div>
  </div>
</div>
-->
<div class="row form-group">
  <label class="col-xs-12 col-md-5">Zip Code <span class="text-danger">*</span></label>
  <div class='col-xs-12 col-md-7'>
    <input type="text" class="form-control" name="zip" id="zip" value="" />
    <div class="alert-string zip"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-5">Country <span class="text-danger">*</span></label>
  <div class='col-xs-12 col-md-7'>
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
                      <label class="col-xs-12 col-md-5">Industry <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
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
                      <label class="col-xs-12 col-md-5">Product Category <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
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
                      <label class="col-xs-12 col-md-5">Number of products to be certified(estimated) <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>  
                        <input type="text" class="form-control" name="prodnumber" id="prodnumber" value="" />
                        <div class="alert-string prodnumber"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Number of raw materials(estimated) <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="ingrednumber" id="ingrednumber" value="" />
                        <div class="alert-string ingrednumber"></div>
                      </div>
                    </div>
                     <div class="row form-group">
                      <label class="col-xs-12 col-md-5">VAT Number <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="vat" id="vat" value="" />
                        <div class="alert-string vat"></div>
                      </div>
                    </div>
                   
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Contact Person Name  <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="contact_person" id="contact_person" value="" />
                        <div class="alert-string contact_person"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Phone Number</label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="phone" id="phone" value="" />
                        <div class="alert-string phone"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Email Address <span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="email" id="email" value="" />
                        <div class="alert-string email"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5">Confirm Email Address<span class="text-danger">*</span></label>
                      <div class='col-xs-12 col-md-7'>
                        <input type="text" class="form-control" name="cemail" id="cemail" value="" />
                        <div class="alert-string cemail"></div>
                      </div>
                    </div>
<div class="row form-group">
    <label class="col-xs-12 col-md-5">Is your facility a pork-free facility? <span class="text-danger">*</span></label>
    <div class='col-xs-12 col-md-7'>
        <label><input type="radio" name="pork_free_facility" value="Yes"> Yes</label>
        <label><input type="radio" name="pork_free_facility" value="No"> No</label>
        <div class="alert-string pork_free_facility"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-5">Do you have dedicated lines for Halal production? <span class="text-danger">*</span></label>
    <div class='col-xs-12 col-md-7'>
        <label><input type="radio" name="dedicated_halal_lines" value="Yes"> Yes</label>
        <label><input type="radio" name="dedicated_halal_lines" value="No"> No</label>
        <label><input type="radio" name="dedicated_halal_lines" value="Not applicable"> Not applicable</label>
        <div class="alert-string dedicated_halal_lines"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-5">What are your target export regions?</label>
    <div class='col-xs-12 col-md-7'>
        <input type="text" class="form-control" name="export_regions" id="export_regions" value="" />
        <div class="alert-string export_regions"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-5">Are the products to be Halal certified, produced by a third party? <span class="text-danger">*</span></label>
    <div class='col-xs-12 col-md-7'>
        <label><input type="radio" name="third_party_products" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_products" value="No"> No</label>
        <div class="alert-string third_party_products"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-5">Is this third party Halal certified? <span class="text-danger">*</span></label>
    <div class='col-xs-12 col-md-7'>
        <label><input type="radio" name="third_party_halal_certified" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_halal_certified" value="No"> No</label>
        <label><input type="radio" name="third_party_halal_certified" value="Not applicable"> Not applicable</label>
        <div class="alert-string third_party_halal_certified"></div>
    </div>
</div>

                    <div class="row form-group">
                      <label class="col-xs-12 col-md-5"></label>
                      <div class='col-xs-12 col-md-7'>
                        <div class="g-recaptcha" data-callback="clearCaptchaError" data-sitekey="6Ld8HiIhAAAAAPovBwOTDSt-Q4g8lz0RfO5pHRai"></div>
                        <div class="alert-string captcha"></div>
                      </div>
                    </div>
                  </form>
                   <div class="text-center">
                     <button class="btn btn-primary" id="enter_btn"> <i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;Register </button>
                   </div>
                </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <?php if(file_exists( __DIR__ ."/../terms.txt")){$terms = file_get_contents( __DIR__ ."/../terms.txt"); echo $terms;} else echo "No Terms and Conditions file found!";?>
      </div>
      <div id="s_btn" class="modal-footer">
        <button class="btn" id="close_modal" data-dismiss="modal" aria-hidden="true">Close</button>
      </div>
    </div>
  </div>
</div>
<?php include_once('pages/footer.php');?>
</body>

<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js" integrity="sha512-eYSzo+20ajZMRsjxB6L7eyqo5kuXuS2+wEbbOkpaur+sA2shQameiJiWEzCIDwJqaB0a4a6tCuEvCOBHUg3Skg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/sha512.js"></script>
<!--<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>-->
<script type="text/javascript">
function clearCaptchaError() {
	$(".alert-string.captcha").html("");
}
    $(document).ready(function() {  

      $(document).on("click", ".VIpgJd-ZVi9od-xl07Ob-lTBxed", function() {

        return false;
      });

      $("#category").change(function(){
        if ($(this).val() == "other") {
          
          $("#other-category").show();
        }
        else {
          $("#other-category").hide();
        }
      });
		
		$('form :input').keyup(function(e) { 
            $(this).next('.alert-string').html('');
        });

        // Пользователь пытается войти
        $("#enter_btn").click(function() {
               if (!$('#terms:checked')[0])
            {
               // alert("Please agree to the Terms and Conditions");
               // return;
            }

            var data = {};
            data.name = $('#name').val();
            data.address = $('#address').val();
            data.country = $('#country').val();
            data.city = $('#city').val();
            data.zip = $('#zip').val();
            data.contact_person = $('#contact_person').val();
            data.industry = $('#industry').val();
            data.category = ($('#category').val() == "other" ? $('#other-category').val() : $('#category').val());
            data.prodnumber = $('#prodnumber').val();
            data.ingrednumber = $('#ingrednumber').val();
            data.vat = $('#vat').val();
            data.contact_person = $('#contact_person').val();
            data.phone = $('#phone').val();
            data.email = $('#email').val();
            data.cemail = $('#cemail').val();
		      	data.captcha = grecaptcha.getResponse();
			      data.lang = document.querySelector('html').getAttribute('lang');
            data.pork_free_facility = $("input[name='pork_free_facility']:checked").val();
            data.dedicated_halal_lines = $("input[name='dedicated_halal_lines']:checked").val();
            data.export_regions = $('#admin-form #export_regions').val();
            data.third_party_products = $("input[name='third_party_products']:checked").val();
            data.third_party_halal_certified = $("input[name='third_party_halal_certified']:checked").val();

            // отправка данных для идентификации
            $.ajax({
                type: "POST",
                url: "ajax/ajaxHandler.php",
                data: {uid: 0, rtype: "register", data: data},
                cache: false,
				beforeSend: function() { 
          $.blockUI(); 
				},
                success: function(data) // результат
                {
                    var response = JSON.parse(data);
					if (response.data.errors) {
						$.each( response.data.errors, function( key, value ) {
						  $(".alert-string."+key).html(value);
						  //alert( key + ": " + value );
						});
						grecaptcha.reset();
					}
					else {
						$("#mainForm").addClass('hidden');
						$("#success").removeClass('hidden');
					}
          $.unblockUI(); 
                    //if (response.status == '1') document.location.href = "";
                    //else {
                        //$("#res_enter").html(response.statusDescription);
                    //}
                }
            });
        });
    });
</script>
<script type="text/javascript">// <![CDATA[
function googleTranslateElementInit() {
	var tr = new google.translate.TranslateElement({ includedLanguages:'en,de,it,fr', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element_n');
	//console.log(google.translate.TranslateElement)
  }
 </script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" type="text/javascript"></script>
</html>