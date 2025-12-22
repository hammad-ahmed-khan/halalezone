<?php
//use this for all function
if (defined("functions_loaded"))
	return;
define("functions_loaded", true);

if (!function_exists('get_ip_address')) {
	function get_ip_address()
	{
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP');
			} else {
				$ip = getenv('REMOTE_ADDR');
			}
		}
		return $ip;
	}
}

function is_admin($uid = '')
{
	$admin = false;
	if ($uid != '') {
		if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
			if ($_SESSION['user']['uid'] == $uid or $_SESSION['user']['uid'] == '11' or $_SESSION['user']['uid'] == '1')
				$admin = true;
		}
	} else {
		if ($_SESSION['user_role'] == 'super_admin')
			$admin = true;
	}
	return $admin;
}

if (!function_exists('is_local')) {
	function is_local()
	{
		if ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == '127.0.0.1')
			return true;
		else
			return false;
	}
}
if (!defined('€'))
	define('€', chr(128) . ' ');
define("functions", true);
if (!function_exists('fix_date')) {
	function fix_date($theDate)
	{
		if (trim($theDate) == '')
			return;
		$theFinalDate = $theDate;
		$date_seps = array('/', '\\', '-');
		foreach ($date_seps as $seps) {
			if (strstr($theDate, $seps))
				$date_parts = explode($seps, $theDate);
		}
		if (!isset($date_parts) or !is_array($date_parts))
			return $theDate;
		if (strlen($date_parts[2]) == 4)
			$theFinalDate = $date_parts[2] . '-' . str_pad($date_parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
		else
			$theFinalDate = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($date_parts[1], 2, '0', STR_PAD_LEFT) . '-' . $date_parts[2];
		return str_replace(' ', '', $theFinalDate);
	}
}

function web_date($date)
{
	if ($date == '0000-00-00')
		return '';
	if (is_numeric($date) && $date > 0) {
		// Convert from timestamp to dd.mm.yyyy
		return date("d.m.Y", $date);
	}
	if (strpos($date, '-') !== false && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
		// Convert from yyyy-mm-dd to dd.mm.yyyy
		[$y, $m, $d] = explode('-', $date);
		return "$d.$m.$y";
	}
	return $date; // Return as-is if format is unrecognized
}

function sql_date($date)
{
	if (strpos($date, '.') !== false) {
		// Convert from dd.mm.yyyy to yyyy-mm-dd
		[$d, $m, $y] = explode('.', $date);
		return "$y-$m-$d";
	}
	return $date; // Return as-is if format is unrecognized
}

function get_client_id($clid, $cc = 'NL')
{
	return $cc . str_pad($clid, 6, '0', STR_PAD_LEFT);
}

function get_client($clid, $inputs = '')
{
	if (!isset($clid))
		return;
	global $amdb;
	if ($client = $amdb->get_row("SELECT * FROM companies  WHERE clof='0' AND clid=$clid")) {
		$client['client_company_name'] = $client['company_name'];
		$client['client_contact_person'] = "$client[contact_title1] $client[contact_name1] $client[contact_surname1]";
		$client['company_contact_person'] = $client['client_contact_person'];
		$client['contact_name'] = $client['client_contact_person'];
		$client['client_address'] = "$client[street1]<br/>$client[zip1] $client[city1]<br/>$client[country1]";
		$client['company_address'] = $client['client_address'];
		$client['company_country'] = $client['country1'];
		$client['company_city'] = $client['city1'];
		$client['country'] = $client['country1'];
		$client['city'] = $client['city1'];
		$client['telephone'] = $client['tel1'];
		$client['client_email'] = $client['email1'];
		$client['email'] = $client['client_email'];
		$client['company_email'] = $client['client_email'];

		if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$client[offid]'")) {
			$client['client_id'] = $office['office_country'] . str_pad($clid, 6, '0', STR_PAD_LEFT);
			$client['company_id'] = $client['client_id'];
			$client['office'] = $office;
		}

		if ($sites = get_client_production_sites($clid)) {
			$client['production_sites'] = $sites['addresses'];
			$client['production_sites_total'] = $sites['count'];
			$client['production_sites_count'] = $sites['count'];
		}

		if ($inputs != '') {
			$res = array();
			$the_inputs =  explode(',', $inputs);
			foreach ($the_inputs as $input) {
				if (isset($client[$input])) {
					$res[$input] = $client[$input];
				}
			}
			return $res;
		}
		return $client;
	}
}

if (!function_exists('get_clients')) {
	function get_clients($select = "*", $srchFor = "")
	{
		global $amdb;
		if ($result = $amdb->get_results("SELECT $select FROM companies
						JOIN users ON companies.clid=users.clid
						WHERE companies.clof='0' and users.active='y' and users.approved='y' $srchFor
						ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {
			return $result;
		}
	}
}
if (!function_exists('date2num')) {
	function date2num($d)
	{
		$theDate = "";
		if (strlen($d) > 9)
			$theDate = strtotime(str_replace("/", "-", $d) . " 00:00:00");
		return $theDate;
	}
	if (!function_exists('num2date')) {
		function num2date($n)
		{
			$theDate = "";
			if (strlen($n) >= 8)
				$theDate = date("d/m/Y", $n);
			return $theDate;
		}
	}
}
if (!function_exists('get_office_data')) {
	function get_office_data($offid)
	{
		global $amdb, $prog_path;
		if (!isset($country))
			include "$prog_path/config/countries.code.php";
		if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='$offid'")) {
			$office['office_name'] = $office['company_name_english'];
			$office['office_address'] = $office['office_street'] . "<br/>"
				. $office['office_zipcode'] . ", " . $office['office_city'] . "<br/>"
				. $country[$office['office_country']] . "<br/>"
				. 'Tel.: ' . $office['office_telephone'] . "<br/>"
				. 'Email: ' . $office['office_email'] . "<br/>";
			if (trim($office['office_website']) != '')
				$office['office_address'] .= 'Website: ' . $office['office_website'];
			return $office;
		}
	}
}
if (!function_exists('billing_address')) {
	function billing_address($data)
	{
		//billing address
		if (trim($data) != '' and is_array(json_decode($data, true))) {
			$BA = json_decode($data, true);
			$address = '';
			if ((isset($BA['street']) and trim($BA['street']) != '') or isset($BA['email']) and trim($BA['email'])) {
				echo "<div style=\"margin-top:20px;font-weight:bold\"><label><input type=\"checkbox\" name=\"uba\" value=\"yes\" checked=\"checked\"/>BILLING ADDRESS</label></div>";
				if (trim($BA['name']) != "")
					echo "<div class=\"prevTtl\">Name:</div><div class=\"prevVal\"> $BA[name]</div>";
				if (trim($BA['street']) != "")
					$address = "<div class=\"prevTtl\">Address:</div><div class=\"prevVal\"> $BA[street]";
				if (trim($BA['zipcode']) != "")
					$address .= "<br>$BA[zipcode]";
				if (trim($BA['city']) != "")
					$address .= " $BA[city]";
				if (trim($BA['country']) != "" and trim($address) != '')
					echo "<br/>$BA[country]</div>";
				if (trim($BA['telephone']) != "")
					echo "<div class=\"prevTtl\">Telephone:</div><div class=\"prevVal\"> $BA[telephone]</div>";
				if (trim($BA['email']) != "")
					echo "<div class=\"prevTtl\">Email:</div><div class=\"prevVal\"><a href=\"mailto:$BA[email]\">$BA[email]</a></div>";
			}
		}
	}
}
if (!function_exists('invoice_address')) {
	function invoice_address($clid)
	{
		global $amdb, $_REQUEST;
		if (!isset($clid))
			return;
		$cla = array();
		$address = "";
		$uba = array();
		if ($row = $amdb->get_row("SELECT * FROM companies WHERE clid='$clid'")) {
			foreach ($row as $key => $value) {
				$row[$key] = trim($value);
			}
			$cla['row'] = $row;
			if (trim($row['billing_address']) != '' && isset($_REQUEST['uba'])) {
				$uba = json_decode($row['billing_address'], true);
				$cla['uba'] = 'yes';
			} else {
				$cla['uba'] = 'no';
			}
			$address .= "$row[company_name]\n";
			if (isset($uba['name']) and trim($uba['name']) != '') {
				$address .= "Att. $uba[name]";
				$client_name = $uba['name'];
			} elseif ($row['contact_name1']) {
				$address .= "Att. $row[contact_title1] $row[contact_name1] $row[contact_surname1]";
				$client_name = "$row[contact_title1] $row[contact_name1] $row[contact_surname1]";
			}
			if (isset($uba['street']) and trim($uba['street']) != '') {
				$address .= "\n$uba[street]\n$uba[zipcode] $uba[city]";
			} else {
				$address .= "\n$row[street1]\n$row[zip1] $row[city1]";
			}
			$address .= "\nVAT No: $row[vatNr]\nVAT shifted";
			$cla['address'] = $address;
			$cla['company_name'] = $row['company_name'];
			$cla['client_name'] = $client_name;
			if (isset($uba['email']) and trim($uba['email']) != '')
				$cla['client_email'] = $uba['email'];
			elseif ($row['email1'])
				$cla['client_email'] = $row['email1'];
			else
				$cla['client_email'] = $row['email2'];
			return $cla;
		};
	}
}
if (!function_exists('clean_string')) {
	function clean_string($string)
	{
		$s = trim($string);
		$s = iconv("UTF-8", "UTF-8//IGNORE", $s); // drop all non utf-8 characters
		$s = str_replace(' ', ' ', $s);
		// this is some bad utf-8 byte sequence that makes mysql complain - control and formatting i think
		$s = preg_replace('/(?>[\x00-\x1F]|\xC2[\x80-\x9F]|\xE2[\x80-\x8F]{2}|\xE2\x80[\xA4-\xA8]|\xE2\x81[\x9F-\xAF])/', ' ', $s);
		$s = preg_replace("/\s+/", ' ', $s); // reduce all multiple whitespace to a single space
		$s = str_replace(' – ', ' - ', $s);
		return trim($s);
	}
}
function encodeText($text)
{
	if (mb_detect_encoding($text) != 'ASCII') {
		if ($_SERVER['REMOTE_ADDR'] == '::1') {
			//	echo $text.": ".mb_detect_encoding($text."auto")."<br/>";
		}
		if (
			strstr(strtolower($text), 'š')
			or strstr(strtolower($text), 'ě')
			or strstr(strtolower($text), 'ř')
			or strstr(strtolower($text), 'ň')
		)
			$encoding = 'cp1250';
		else
			$encoding = 'ISO-8859-1';
		$text = iconv(mb_detect_encoding($text), $encoding, $text);
	}
	return $text;
}
if (!function_exists('fix_currency')) {
	function fix_currency($amount = 0)
	{
		if (strstr($amount, '.') and strstr($amount, ','))
			$amount = str_replace(array('.', ','), array('', '.'), $amount);
		elseif (strstr($amount, ','))
			$amount = str_replace(',', '.', $amount);
		return trim($amount);
	}
}
if (!function_exists('do_currency')) {
	function do_currency($amount = 0, $dem = 2)
	{
		if (strstr($amount, '.') and strstr($amount, ','))
			$amount = str_replace(array('.', ','), array('', '.'), $amount);
		elseif (strstr($amount, ','))
			$amount = str_replace(',', '.', $amount);
		if (is_numeric($amount))
			return number_format(trim($amount), $dem, ',', '.');
		else
			return $amount;
	}
}
if (!function_exists('format_number')) {
	function format_number($number = 0, $dem = 2)
	{
		return do_currency($number, $dem);
	}
}

if (!function_exists('replace_content')) {
	function replace_content($short, $text, $new_text)
	{
		if (!isset($short) or !isset($text) or !isset($new_text))
			return;

		$doc = load_DOMDocument($text);
		$els = $doc->getElementsByTagName("*");

		for ($i = $els->length - 1; $i >= 0; $i--) {
			$el = $els->item($i);

			if ($el->hasAttribute('data-replace') and $el->hasAttribute('data-replace') == $short) {
				//replace content of the element
				$el->nodeValue = $new_text;
			}
		}
		$text = unload_DOMDocument($doc);
		return $text;
	}
}

if (!function_exists('remove_tag')) {
	function remove_tag($short, $text, $tag = "*")
	{
		if (!isset($short) or !isset($text))
			return;
		$doc = load_DOMDocument($text);
		$els = $doc->getElementsByTagName($tag);
		for ($i = $els->length - 1; $i >= 0; $i--) {
			$el = $els->item($i);
			if ((strstr($el->textContent, '[' . $short . ']') and $tag != '*') or ($el->hasAttribute('data-rem') and $el->getAttribute('data-rem') == $short)) {
				$el->parentNode->removeChild($el);
			}
		}

		$text = unload_DOMDocument($doc);
		return $text;
	}
}

//remove attrs
if (!function_exists('remove_attr')) {
	function remove_attr($text, $attrs, $tags = NULL, $cleanStyle = false)
	{
		if (!isset($attrs) or !isset($text))
			return;
		$attrs = explode(',', $attrs);
		$styleAttrs = 	"/" . implode(':(.*);|', $attrs) . ":(.*);/";
		if (isset($tags))
			$tags = explode(',', $tags);
		$doc = new DOMDocument();
		$doc->recover = true;
		$doc->strictErrorChecking = false;
		$doc->preserveWhiteSpace = false;
		if (@!$doc->loadHTML('<?xml encoding="UTF-8">' . str_replace('&', '&amp;', $text)))
			return;
		$els = $doc->getElementsByTagName("*");
		for ($i = $els->length - 1; $i >= 0; $i--) {
			$el = $els->item($i);
			if (!isset($tags) or in_array($el->tagName, $tags)) {
				foreach ($el->attributes as $attr) {
					$theAttrName = $attr->nodeName;
					if (in_array($theAttrName, $attrs)) {
						$attr->parentNode->removeAttribute($theAttrName);
					}
					if ($cleanStyle == true and $theAttrName == 'style') {
						$attr->value = preg_replace($styleAttrs, '', $attr->value);
						if (trim($attr->value) == '')
							$attr->parentNode->removeAttribute($theAttrName);
					}
				}
			}
		}
		if (preg_match('/<body>(.*)<\/body>/is', $doc->saveHTML(), $text))
			$text = $text[1];
		else $text = $doc->saveHTML();
		return $text;
	}
}
//parse schorde code
if (!function_exists('parse_shortcode')) {
	function parse_shortcode($code, $text)
	{
		$results = array();
		if (preg_match_all("/\[$code (.*?)\]/is", $text, $matches, PREG_SET_ORDER)) {
			$props = array();
			foreach ($matches as $key => $shortMatch) {
				$codeContent = $shortMatch[1];
				if (preg_match_all('/"(.*?)"/', $codeContent, $spaces)) {
					foreach ($spaces[0] as $tag) {
						$codeContent = str_replace($tag, str_replace(' ', '_#_', $tag), $codeContent);
					};
				};
				parse_str(str_replace(array('  ', '"', ' ', '_#_', '/'), array(' ', '', '&', ' ', ''), $codeContent), $vars);
				$vars['element'] = $shortMatch[0];
				if (!strstr($shortMatch[0], '/]')) {
					$regex = str_replace(array('[', ']'), array('\[', '\]'), $shortMatch[0]);
					if (preg_match_all("/$regex(.*?)\[\/$code\]/s", $text, $content))
						$vars['content'] = $content[1][0];
					$vars['element'] = $content[0][0];
				};
				$props = $vars;
				if (isset($props['content']))
					unset($props['content']);
				if (isset($props['element']))
					unset($props['element']);
				if (isset($props['title']))
					unset($props['title']);
				if (isset($props['type']) and ($props['type'] == 'textarea' or $props['type'] == 'select'))
					unset($props['type']);
				if (isset($props['group']))
					unset($props['group']);
				$vars['props'] = $props;
				$results[] = $vars;
			}
		}
		return $results;
	}
}
if (!function_exists('encode_text')) {
	function encode_text($text)
	{
		return str_replace(array('<', '>'), array('&lt;', '&gt;'), $text);
	}
}
if (!function_exists('sys_admin')) {
	function sys_admin()
	{
		if ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == '2a02:a459:7f75:1:1917:4bb0:f989:e44d')
			return true;
		else
			return false;
	}
}
if (!function_exists('order_by')) {
	function order_by($data, $order_by, $ascDsc = 'ASC')
	{
		if (!isset($data) or !isset($order_by) or !is_array($data))
			return;
		$items = array();
		$final = array();
		foreach ($data as $item) {
			if (isset($item[$order_by])) {
				$items[trim($item[$order_by])][] = $item; //$item;
			}
		}
		if (strtoupper($ascDsc) == 'ASC')
			ksort($items);
		else
			krsort($items);
		foreach ($items  as $item) {
			foreach ($item as $subItem)
				$final[] = $subItem;
		}
		return $final;
	}
}
if (!function_exists('get_goBack')) {
	function get_goBack()
	{
		foreach ($_SESSION as $k => $v) {
			$goback = array();
			if (strstr($k, 'goBack_url')) {
				$goBacks = explode('&ttl=', $v . '&ttl=');
				$goback['url'] = $goBacks[0];
				$goback['title'] = $goBacks[1];
				return $goback;
			}
		}
	}
}
//translation functions
if (!function_exists('loadLang')) {
	function loadLang()
	{
		if (isset($_COOKIE['lang'])) {
			$lang = $_COOKIE['lang'];
			putenv("LANG=$lang");
			setlocale(LC_ALL, $lang);
			// Specify location of translation tables
			bind_textdomain_codeset($lang, 'UTF-8');
			bindtextdomain($lang, dirname(__FILE__) . "/languages/");
			// Choose domain
			textdomain($lang);
		}
	}
}
loadLang();
if (!function_exists('_e')) {
	function _e($text, $s = '', $d = '')
	{
		if (function_exists("gettext"))
			echo str_replace(array("\n", "%s", "%d"), array("<br/>", $s, $d), gettext($text));
		else
			echo $text . "::";
	}
}
if (!function_exists('__')) {
	function __($text, $s = '', $d = '')
	{
		if (function_exists("gettext"))
			return str_replace(array("\n", "%s", "%d"), array("<br/>", $s, $d), gettext($text));
		else
			return $text . "::";
	}
}
//encoding json with quotes
if (!function_exists('encode_json')) {
	function encode_json($data)
	{
		if ($data and is_array($data))
			return str_replace(array("\r\n", "\n\r", "\n", "\r"), '\n', json_encode($data, JSON_UNESCAPED_UNICODE));
		else
			return $data;
	}
}
//encoding json with quotes
if (!function_exists('decode_json')) {
	function decode_json($data)
	{
		$data = str_replace(array("\r\n", "\n\r", "\n", "\r"), '\n', $data);
		$jsonData = array();
		if ($data and is_array(json_decode($data, true))) {
			$jsonData = json_decode($data, true);
			foreach ($jsonData as $key => $value) {
				if ($value != null && !is_array($value) and is_array(json_decode($value, true)))
					$jsonData[$key] = json_decode($value, true);
			}
		}
		return $jsonData;
	}
}

if (!function_exists('is_serialized')) {
	function is_serialized($data)
	{
		return (is_string($data) && preg_match('/^a:\d+:{.*}$|^O:\d+:"[^"]+":\d+:{.*}$/s', $data));
	}
}

if (!function_exists('post_this_results')) {
	function post_this_results($txt = "", $do = "alert", $theFormID = "")
	{
		if ($theFormID == "" and isset($_REQUEST['formID']))
			$theFormID = $_REQUEST['formID'];
		echo "<script>
	top.document.getElementById(\"contents\").contentWindow.postResults('$do:" . str_replace(array("\n", "\r", "\n\r", "'", "script>"), array("", "", "", "\'", "do-script>"), $txt) . "','$theFormID')
	</script>";
	}
}
if (!function_exists('countries_list')) {
	function countries_list($cc = '', $tag = 'option', $tagName = '')
	{
		global $prog_path, $_POST;
		if (!isset($country))
			include $prog_path . "/config/countries.code.php";
		$countries = '';
		if ($cc == '' and $tagName != '') {
			if (isset($_REQUEST[$tagName]))
				$cc = $_REQUEST[$tagName];
			elseif (isset($_POST[$tagName]))
				$cc = $_POST[$tagName];
		}
		foreach ($country as $code => $name) {
			if ($tag == 'radio') {
				$countries .= '<li><label><radio name="' . $tagName . '" value="' . $code . '" ' . (($cc != '' && $cc == $code) ? 'checked' : '') . '/> ' . $name . '</label></li>';
			} else {
				$countries .= '<option value="' . $code . '" ' . (($cc != '' && $cc == $code) ? 'selected' : '') . '> ' . $name . '</option>';
			}
		}
		if ($tag == 'radio' or $tag == 'option')
			echo $countries;
		else
			echo '<select name="' . $tagName . '" size="1" id="' . $tagName . '"><option value="">Please select</option>' . $countries . '</select>';
	}
}
if (!function_exists('make_nonce')) {
	function make_nonce()
	{
		$nonce = md5(time());
		$_SESSION['nonce'] = $nonce;
		echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
	}
}
if (!function_exists('check_nonce')) {
	function check_nonce()
	{
		if (isset($_REQUEST['nonce']) && isset($_SESSION['nonce']) && $_REQUEST['nonce'] == $_SESSION['nonce']) {
			unset($_SESSION['nonce']);
			return true;
		}
		return false;
	}
}

function get_remote_data($inc)
{
	return file_get_contents((is_local() ? 'http://dev.' : 'https://portal.') . 'iidc.eu/remote/get-data.php?inc=' . str_replace('?', '&', $inc) . '&t=cNN94LjhER7Daq4UkLnr');
}

if (!function_exists('get_offices')) {
	function get_offices($whr = 1)
	{
		global $amdb;
		if ($result = $amdb->get_results("SELECT * FROM offices
                        WHERE $whr AND status='active'
                        ORDER BY TRIM(office_name)+0 ASC, TRIM(office_name) ASC")) {
			return $result;
		}
	}
}
if (!function_exists('get_office_options')) {
	function get_office_options()
	{
		global $amdb;
		if (!isset($_SESSION["offid"]))
			$offid = 0;
		else
			$offid = $_SESSION["offid"];
		$user = array();
		$user['type'] = "hqc_office";
		if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='$offid'")) {
			$user['clients'] = explode(',', $office['clients']);
			if (trim($office['options']) != '' && is_array(json_decode($office['options'], true))) {
				$user['options'] = json_decode($office['options'], true);
			}
			return $user;
		}
	}
}
if (!function_exists("array_set")) {
	function array_set($data, $sub, $act = 'add')
	{
		if (!isset($data) or !isset($sub))
			return;
		$data = explode(',', $data);
		$sub = explode(',', $sub);
		$newData = array();
		if ($act == 'add')
			$newData = array_merge($data, $sub);
		if ($act == 'remove')
			$newData = array_diff($data, $sub);
		return implode(',', array_filter(array_unique($newData)));
	}
}
function this_url($level = 0)
{
	global $prog_path;
	$dir = dirname(debug_backtrace()[$level]['file']);
	return str_replace($prog_path, '', str_replace('\\', '/', $dir));
}
function this_path($level = 0)
{
	$dir = dirname(debug_backtrace()[$level]['file']);
	return str_replace('\\', '/', $dir);
}

//check web browser environment
function is_mobile()
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	// Check if the "mobile" word exists in User-Agent
	$isMob = is_numeric(strpos(strtolower($useragent), "mobile"));

	// Check if the "tablet" word exists in User-Agent
	$isTab = is_numeric(strpos(strtolower($useragent), "tablet"));

	// Platform check
	$isWin = is_numeric(strpos(strtolower($useragent), "windows"));
	$isAndroid = is_numeric(strpos(strtolower($useragent), "android"));
	$isIPhone = is_numeric(strpos(strtolower($useragent), "iphone"));
	$isIPad = is_numeric(strpos(strtolower($useragent), "ipad"));
	$isIOS = $isIPhone || $isIPad;

	if ($isMob or $isTab) {
		return true;
	} else {
		return false;
	}
}

function letter_avatar($word = "HQC")
{
	if (strlen($word) < 3)
		$word = str_pad($word, 3, substr($word, -1), STR_PAD_RIGHT);

	$word = strtoupper($word);
	$letters = range("A", "Z");
	$colorTags = range("A", "F");

	$rSub = isset($colorTags[array_search(substr($word, 0, 1), $letters)]) ? $colorTags[array_search(substr($word, 0, 1), $letters)] : 'A';
	$gSub = isset($colorTags[array_search(substr($word, 1, 1), $letters)]) ? $colorTags[array_search(substr($word, 1, 1), $letters)] : 'A';
	$bSub = isset($colorTags[array_search(substr($word, 2, 1), $letters)]) ? $colorTags[array_search(substr($word, 2, 1), $letters)] : 'A';

	$r = str_pad((array_search(substr($word, 0, 1), $letters) + 1), 2, $rSub, STR_PAD_LEFT);
	$g = str_pad((array_search(substr($word, 1, 1), $letters) + 1), 2, $gSub, STR_PAD_LEFT);
	$b = str_pad((array_search(substr($word, 2, 1), $letters) + 1), 2, $bSub, STR_PAD_LEFT);
	$color = $b . $g . $r;

	$avatar = '<span style="display:inline-block;width:30px;Height:30px;color:white;background:#' . $color . ';text-align:center;padding-top:6px;border-radius:15px;font-size:14px">' . substr($word, 0, 1) . '</span>';
	return $avatar;
}

//function to validate email address
function validateEmail($email)
{
	$email = trim($email);
	$regExpn = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
	// Remove all illegal characters from the email
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);

	// Validate the email using a regular expression
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		if (preg_match($regExpn, $email))
			return true;
		else
			return false;
	} else {
		return false;
	}
}

function load_DOMDocument($text)
{
	$doc = new DOMDocument();
	$doc->recover = true;
	$doc->strictErrorChecking = false;
	$doc->preserveWhiteSpace = false;
	if (@$doc->loadHTML('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><?xml encoding="UTF-8"><body>' . $text . "</body></html>", LIBXML_HTML_NODEFDTD)) {
		return $doc;
	}
}

function unload_DOMDocument($doc)
{
	if (preg_match('/<body>(.*)<\/body>/is', $doc->saveHTML(), $text))
		$content = $text[1];
	else $content = $doc->saveHTML();
	return $content;
}

function get_client_production_sites($clid)
{
	global $amdb;
	if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status = 'active' AND clid = '$clid'")) {
		if (count($sites) > 0) {
			$manufacturing_site_address = '';
			foreach ($sites as $site) {
				$manufacturing_site_address .=  $site['site_name'] . "\n";

				if (trim($site['site_address']) != '' and is_array(json_decode($site['site_address'], true))) {
					$site_address = json_decode($site['site_address'], true);
					$address = array(1, 2, 3);

					if (trim($site_address['street']) != '')
						$address[1] = $site_address['street'];

					if (trim($site_address['zipcode']) != '')
						$address[2] = $site_address['zipcode'];

					if (trim($site_address['city']) != '')
						$address[2]  .=   ' ' . $site_address['city'];

					if (trim($site_address['country']) != '')
						$address[3] = $site_address['country'];

					$manufacturing_site_address  .=   implode("\n", $address);
					if (trim($site_address['telephone']) != '')
						$manufacturing_site_address .= "Telephone: " . $site_address['telephone'];
					if (trim($site_address['email']) != '')
						$manufacturing_site_address .= "Email: " . $site_address['email'];
				};
			};
			return array("addresses" => $manufacturing_site_address, "count" => count($sites));
		}
	}
}

function get_halal_standards($return_type = '', $stnid = array())
{
	global $amdb;
	if ($standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE status = 'active' ORDER BY code ASC")) {
		if ($return_type == 'array' or $return_type == 'select') {
			if ($return_type == 'select')
				$return_select = '<select name="halal_standards" id="halal_standards" style="width:250px"><option value="">Please select halal standard</option>';
			else
				$standards_array = array();
			foreach ($standards as $standard) {
				if ($return_type == 'select')
					$return_select .= '<option value="' . $standard['stnid'] . '">' . $standard['code'] . ' (' . substr($standard['description'], 0, strpos($standard['description'] . '-', '-')) . ')</option>';
				else
					$standards_array[$standard['standard_id']] = $standard['standard_name'];
			}
			if ($return_type == 'select')
				return $return_select . '</select>';
			else
				return $standards_array;
		}
		return $standards;
	}
}

function prohibited_words($text, $return_text = false)
{

	$return = ($return_text == true) ? $text : false;
	$prohibited_words = explode(",", strtolower(get_option('prohibited_words')));
	foreach ($prohibited_words as $word) {
		if (strstr(strtolower(' ' . $text . ' '), strtolower(' ' . $word . ' '))) {

			if ($return_text == true)
				$return = str_ireplace($word, "<span style='color:red'>" . $word . "</span>", $return);
			else
				return true;
		}
	}
	return $return;
}

function get_dmc_signature($comemid)
{
	global $prog_path;

	$image_file = '/data/DMC/signatures/' . $comemid . '_signature';

	$image_exts = array('.jpg', '.jpeg', '.png', '.svg');
	foreach ($image_exts as $ext) {

		if (file_exists($prog_path . $image_file . $ext)) {
			return $image_file . $ext . "?t=" . time();
		}
	}
	return '';
}

//re-arrange $_FILES for easy handling  (useful for multiple uploaded files)
//return a list of files each with its data (name,temp,size etc)
function _Files($fileItems)
{
	if (isset($fileItems['name']) && !is_array($fileItems['name'])) {
		return array($fileItems);
	}

	$files = array();
	foreach ($fileItems as $key => $file) {
		if (isset($file['name']) && !is_array($file['name'])) {
			$files[$key] = $file;
		} else {
			foreach ($file as $fileKey => $item) {
				if (is_array($item)) {
					foreach ($item as $itemKey => $itemValue) {
						if (is_array($itemValue)) {
							foreach ($itemValue as $itemK => $itemV) {
								$files[$key][$itemKey][$itemK][$fileKey] = $itemV;
							}
						} else {
							$files[$key][$itemKey][$fileKey] = $itemValue;
						}
					}
				} else {
					$files[$fileKey][$key] = $item;
				}
			};
		}
	}
	return $files;
}

function upload_files($files, $path, $overwrite = false)
{
	global $hcp_path;
	if (!is_dir($hcp_path . '/' . $path))
		mkdir($hcp_path . '/' . $path, 0777, true);

	$uploadedFiles = array();
	$files = _FILES($files);

	$exTypes = array("jpg", "jpeg", "png", "doc", "docx", "pdf", "xls", "xlsx");
	if (count($files) > 0) {
		foreach ($files as $key => $file) {

			if (isset($file['name'])) {

				if (in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), $exTypes)) {
					$file['name'] = str_replace(' ', '-', $file['name']);

					if ($overwrite) {
						if (file_exists($path . '/' . $file['name']))
							unlink($path . '/' . $file['name']);
					} else {
						if (file_exists($path . '/' . $file['name'])) {
							$file['name'] = time() . '-' . $file['name'];
						}
					}
					if (move_uploaded_file($file['tmp_name'], $hcp_path . '/' . $path . '/' . $file['name'])) {
						$uploadedFiles[] = $path . '/' . $file['name'];
					}
				}
			}
		}
	}
	return $uploadedFiles;
}

function get_dir_contents($dir, $type = 'png,jpg,jpeg')
{
	global $prog_path;
	$files = [];
	$types = explode(',', $type);
	if (!is_dir($dir)) {
		return $files; // Return empty array if directory doesn't exist
	}

	$items = scandir($dir);
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}

		$path = $dir . DIRECTORY_SEPARATOR . $item;
		if (is_dir($path)) {
			$files = array_merge($files, get_dir_contents($path, implode(',', $types))); // Recursive call for subdirectory
		} else {
			$path = str_replace(array($prog_path . '/data', '\\', '//'), array('', '/', '/'), $path);
			//get file extension
			$file_ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			if (in_array($file_ext, $types)) {
				$files[] = $path;
			}
		}
	}
	return $files;
}
if (!function_exists('create_password')) {
	function create_password($length = 8, $include_special_chars = true)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		if ($include_special_chars) {
			$chars .= "!@$%*";
		}
		return substr(str_shuffle($chars), 0, $length);
	}
}
if (!function_exists('timeToGo')) {
	function timeToGo($expiryDate)
	{

		if (is_numeric($expiryDate)) {
			$expiryDate = date("Y-m-d", $expiryDate);
		}

		// Get the current date
		$currentDate = new DateTime();

		// Create a DateTime object for the expiry date
		$expiryDateTime = new DateTime($expiryDate);

		// Calculate the difference between the current date and the expiry date
		$interval = $currentDate->diff($expiryDateTime);

		// Determine if the difference is negative
		$isNegative = $expiryDateTime < $currentDate;

		// Get the difference in years, months, weeks, and days
		$years = $interval->y;
		$months = $interval->m;
		$weeks = floor($interval->d / 7);
		$days = $interval->d % 7;
		if ($days == 0) {
			$days = 1;
		}

		// If the difference is negative, make the values negative
		if ($isNegative) {
			$years = -$years;
			$months = -$months;
			$weeks = -$weeks;
			$days = -$days;
		}

		// Return the result in an array
		$result = [
			'years' => $years,
			'months' => $months,
			'weeks' => $weeks,
			'days' => $days
		];

		//remove the years and months from the result array if they are 0
		if ($result['years'] == 0) {
			unset($result['years']);
		}
		if ($result['months'] == 0) {
			unset($result['months']);
		}
		if ($result['weeks'] == 0) {
			unset($result['weeks']);
		}
		if ($result['days'] == 0) {
			unset($result['days']);
		}
		//keep 2 elements in the array and remove the rest
		$result = array_slice($result, 0, 2);
		return $result;
	}
}

function get_signatories($CT = 'annual', $offid)
{
	global $prog_path;
	if (!is_numeric($offid)) {
		return [];
	}
	$return = [];
	$signatories_file = $prog_path . "/data/offices/signatories/signatories.json";
	if (file_exists($signatories_file)) {
		$signatories = json_decode(file_get_contents($signatories_file), true);
		if (isset($signatories) && is_array($signatories) && count($signatories) > 0) {
			foreach ($signatories as $key => $value) {
				if (isset($value['certificates']) && isset($value['certificates'][$offid]) && is_array($value['certificates'][$offid])) {
					if (isset($value['certificates'][$offid][$CT]) && $value['certificates'][$offid][$CT] == true) {
						//check if signature file exists
						if (trim($value['signature']) != '' and file_exists($prog_path . "/data/offices/signatories/" . $value['signature'])) {
							$return[$key]['id'] = $key;
							$return[$key]['name'] = $value['name'];
							$return[$key]['position'] = isset($value['position']) ? $value['position'] : '';
							$return[$key]['signature'] =  "/data/offices/signatories/" . $value['signature'];
							$return[$key]['certificates'] = $value['certificates'][$offid][$CT];
						}
					}
				}
			}
		}
	}
	return $return;
}
