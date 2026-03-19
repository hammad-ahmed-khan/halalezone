<?php
if (!defined("__HQC__")) {
	exit();
}
if (!session_id() and !isset($_SESSION['AM_REFFERER']) and $_SESSION['AM_REFFERER'] != $_SERVER['HTTP_HOST']) {
	exit();
}

if (!function_exists('key_implode')) {
	function key_implode($array)
	{
		foreach ($array as $key => $value) {
			$theSpliter = '|' . time() . '|';
			if (is_array($value)) {
				$theRes = http_build_query($value, '', $theSpliter);
				$theRes = str_replace(array('%5B', '%5D', '__', '_='), array('_', '_', '_', '='), $theRes);
				$theParts = explode($theSpliter, $theRes);
				foreach ($theParts as $part) {
					$theKey = substr($part, 0, strpos($part, '='));
					$array[$key . '_' . $theKey] =  urldecode(str_replace($theKey . '=', '', $part));
				}
			} else {
				$array[$key] = $value;
			}
		}
		return $array;
	}
}

if (isset($_GET['act']) && ($_GET['act'] == 'insert' or $_GET['act'] == 'update')) {
?>
	<script>
		function insertSignature(img, input, holder) {
			document.getElementById(input).value = img;
			document.getElementById(holder).innerHTML = '<img src="<?php echo data_url; ?>' + img + '" style="max-width: 250px; max-height: 250px;"/>';
		}
	</script>
	<?php
}
if (!class_exists('amform')) {
	class amform
	{
		private function remove_content($content, $remove)
		{

			$content = preg_replace("/\[$remove](.*?)\[\/$remove]/is", '', $content);
			$content = preg_replace("/<div><\/div>/is", '', $content);
			$content = preg_replace("/\[$remove(.*?)\]/is", '', $content);
			$content = preg_replace("/<!--<!--\[(.*?)\]-->/is", '<!--[$1]', $content);

			if (@!$doc = load_DOMDocument($content))
				return;
			$els = $doc->getElementsByTagName('*');
			for ($i = $els->length - 1; $i >= 0; $i--) {
				$el = $els->item($i);
				if ($style = $el->getAttribute('style')) {
					$style = preg_replace("/width:(.*)" . (($remove != 'pdf') ? 'px' : 'cm') . "(.*);/is", '', $style);
					$el->setAttribute('style', $style);
				}
			}

			return unload_DOMDocument($doc);
		}

		private function remove_shortcodes($content, $codes)
		{
			$code_items = explode(',', $codes);
			foreach ($code_items as $code) {

				if (preg_match_all("/\[$code(.*?)](.*?)\[\/$code]/is", $content, $code_contents, PREG_SET_ORDER)) {

					foreach ($code_contents as $code_item) {
						$content = str_replace($code_item[0], $code_item[2], $content);
					}
				}

				if (preg_match_all("/\[$code(.*?)]/is", $content, $code_contents, PREG_SET_ORDER)) {
					foreach ($code_contents as $code_item) {
						$content = str_replace($code_item[0], '', $content);
					}
				}
			}
			return $content;
		}

		private function fix_actions($content)
		{
			$actions = array("onblur", "onchange", "onclick", "ondblclick", "onfocus", "onkeydown", "onkeypress", "onkeyup", "onmousedown", "onmousemove", "onmouseout", "onmouseover", "onmouseup", "onselect", "onsubmit", "autocomplete");
			foreach ($actions as $action) {
				$content = str_replace('data-' . $action, $action, $content);
			}
			return $content;
		}
		private function fix_spaces($content)
		{
			$content = preg_replace('/=\s+=/', '==', $content);
			$content = preg_replace('/!\s+=/', '!=', $content);
			$content = preg_replace('/\.\s+=/', '.=', $content);
			$content = preg_replace('/=\s+>/', '=>', $content);
			$content = preg_replace('/>\s+=/', '>=', $content);
			$content = preg_replace('/<\s+=/', '<=', $content);
			$content = preg_replace('/-\s+>/', '->', $content);

			return $content;
		}

		private function fill_data($content, $data)
		{
			foreach ($data as $dataKey => $dataValue) {
				if (!is_array($dataValue)) {
					$content = str_replace(array('[' . $dataKey . ']', '[client_' . $dataKey . ']'), $dataValue, $content);
				} else {
					foreach ($dataValue as $key => $value) {
						if (!is_array($value))
							$content = str_replace('[client_' . $key . ']', $value, $content);
					}
				}
			}
			return $content;
		}

		private function include_content($content, $incType = '')
		{
			global $data, $hqcdb, $_USER;
			//define temporary file and dir
			$content = str_replace(array('<div>[?', '?]</div>'), array('[?', '?]'), $content);
			$temFile = temp_path . "/" . time() . ".inc.php";
			if (@$dir  = opendir(temp_path)) {
				while (false !== ($filename = readdir($dir))) {
					if (is_file(temp_path . "/$filename") && file_exists(temp_path . "/$filename") && $filename != "index.html") {
						@unlink(temp_path . "/$filename");
					}
				}
				@closedir($dir);
			}
			if (isset($data))
				$content = $this->fill_data($content, $data);

			if ($incType == 'php')
				$theData = $content;
			else
				$theData = str_replace(
					array('%5B', '%5D', '%20', '%24', '[?', '?]', '<?phpphp', '[script]', '[/script]', '[style]', '[/style]', '<do-script>'),
					array('[', ']', ' ', '$', '<?php', '?>', '<?php', '<script>', '</script>', '<style>', '</style>'),
					$content
				);
			if (preg_match_all("/\<\?(.*?)\?\>/is", $theData, $phpCodes)) {
				foreach ($phpCodes[0] as $code) {
					$theData = str_replace($code, str_replace(array('&lt;?', '&gt;', '&nbsp;'), array('<', '>', ' '), $code), $theData);
				}
			}
			$fl = fopen($temFile, "w");
			fwrite($fl, $this->fix_spaces($theData));
			fclose($fl);
			if ($incType == 'php') {
				if (!defined("functions"))
					include hqc_path . "/include/functions.inc.php";
				include $temFile;
				//unlink($temFile);
			} else {
				ob_start();
				if (!defined("functions"))
					include hqc_path . "/include/functions.inc.php";
				include $temFile;
				$content = ob_get_contents();
				ob_end_clean();
				//unlink($temFile);

				$passedData = array();
				if (isset($data))
					$passedData = $data;
				return array($content, $passedData);
			}
			// if (file_exists($temFile))
			// 	unlink($temFile);
		}

		private function make_popups($the_form)
		{
			if (trim($the_form) == '')
				return;

			if (preg_match_all("/\[popup (.*?)](.*?)\[\/popup]/is", $the_form, $popups, PREG_SET_ORDER)) {
				foreach ($popups as $popup) {
					$popupID = '';
					$attr = ' style="max-width:800px;max-height:400px;overflow:auto"';
					$popupAttr = '';
					if (preg_match('/id(.*?)"(.*?)"/', $popup[1], $ids)) {
						$popupID = $ids[2];
						$popupAttr = trim(str_replace($ids[0], '', $popup[1]));
					}

					if (trim($popupAttr) == '' or !preg_match('/style(.*?)"(.*?)"/', $popup[1]) or !preg_match('/class(.*?)"(.*?)"/', $popup[1]))
						$popupAttr .= $attr;

					$popup[2] = '<div ' . $popupAttr . '>' . $popup[2] . '</div>';
					$popup[2] = str_replace('<p></p>', '', $popup[2]);

					$popup_item = '<div id="' . $popupID . '" style="display:none;">' . trim($popup[2]) . '</div>';
					$the_form = str_replace($popup[0], $popup_item, $the_form);
				}
			}
			return $the_form;
		}

		private function make_tabs($the_form)
		{
			if (trim($the_form) == '')
				return;

			//find in div in $the_form contains only tab-group or tab-item and replace them with tab-group or tab-item
			if (preg_match_all("/<div(.*?)>(.*?)<\/div>/is", $the_form, $divs, PREG_SET_ORDER)) {
				foreach ($divs as $div) {
					if (preg_match_all("/\[tab-group(.*?)]|\[\/tab-group]|\[tab-item(.*?)]|\[\/tab-item]/is", $div[2], $tab_group, PREG_SET_ORDER)) {
						$the_form = str_replace($div[0], $div[2], $the_form);
					}
					if (trim(strip_tags($div[2])) == '')
						$the_form = str_replace($div[0], '', $the_form);
				}
			}

			if (preg_match_all("/\[tab-group (.*?)](.*?)\[\/tab-group]/is", $the_form, $tab_group, PREG_SET_ORDER)) {
				foreach ($tab_group as $group) {
					$tabs = '';
					$tab_nr = 0;
					if (preg_match('/"(.*?)"/', $group[1], $groupItem)) {
						$groupId = $groupItem[1];
					}

					$groupVars = parse_shortcode($group[1]);
					if (!isset($groupVars['id']))
						return;

					$groupId = $groupVars['id'];
					$group_content = $group[2];

					if (preg_match_all("/\[tab-item (.*?)](.*?)\[\/tab-item]/is", $group_content, $tab_items, PREG_SET_ORDER)) {

						foreach ($tab_items as $tab_item) {
							$tabVars = parse_shortcode($tab_item[1]);

							$tab_nr++;
							$tabItem = '';
							if (isset($tabVars['title'])) {
								$the_title = $tabVars['title'];
								$subTab = '';
								$hqc = '';
								if (isset($tabVars['data-subtab']))
									$subTab = ' data-subTab="' . $tabVars['data-subtab'] . '"';

								if (isset($tabVars['data-hqc']))
								$hqc = '<span>' . $tabVars['data-hqc'] . '</span>';

								$tabId = trim(str_replace(array(' ', '(', ')', '__', ':'), array('_', '', '', '_', ''), strtolower($the_title)));

								$tabs .= '<li data-id="' . $tabId . '" ' . (!isset($tabStart) ? ' class="active"' : '') . ' data-tab_nr="' . ($tab_nr) . '"' . $subTab . '>' . $the_title . $hqc.'</li>';
								if (strpos(trim($tab_item[2]), '<br') == 0)
									$tab_item[2] = preg_replace('/<br>/', '', $tab_item[2], 1);
								$tabItem = '<div id="' . $tabId . '" class="tab-content ' . $groupId . (!isset($tabStart) ? " active" : "") . '">' . $tab_item[2] . '</div>';
								if (!isset($tabStart)) {
									$tabItem = '[tab_header]' . $tabItem;
									$tabStart = true;
								}
								$group_content = str_replace($tab_item[0], $tabItem, $group_content);
							};
						};
					};

					if (isset($groupVars['pagination']) && $groupVars['pagination'] == 'yes') {
						$group_content .= '<div id="tabs-pagination"><ul class="tabs-pagination" data-tabsGroup="' . $groupId . '"><li style="width:20%" class="tab-previous">Previous</li><li style="text-align:center;width:60%" id="' . $groupId . '_footer"></li><li style="width:20%" class="tab-next">Next</li></ul></div>';
					}
					$the_form = str_replace($tab_group[0], $group_content, $the_form);


					if (isset($groupVars['joined']) && $groupVars['joined'] == 'yes') {
						$tabs .= '<li class="tabs-all"><i class="fas fa-angle-double-down"></i>Expand all</li>';
					}

					if (count($tab_group) == 1) {
						$sticky = ' sticky';
					} else {
						$sticky = '';
					}

					$the_form = str_replace('[tab_header]', '<ul class="tabs' . $sticky . '" data-tabs="' . $groupId . '" data-total_tabs="' . $tab_nr . '">' . $tabs . '</ul>', $the_form);
				}
			}
			return $the_form;
		}


		function get_signature($the_form, $data, $htmlPdf)
		{

			foreach ($data as $data_key => $data_value) {
				if (strstr($data_key, 'signature_')) {
					if (trim($data_value) != '' && file_exists(data_path .  $data_value)) {
						if ($htmlPdf == 'pdf')
							$style = 'style="width: 6cm; "';
						else
							$style = 'style="max-width: 300px; max-height:250px;"';
						$signature =  '<div style="text-align:center;"><img src="' . data_url .  $data_value . '" ' . $style . '/></div>';
						$the_form = str_replace('[' . $data_key . ']', $signature, $the_form);
					};
				};
			};

			if ($htmlPdf == 'pdf' && preg_match_all('/\[signature(.*)\]/U', $the_form, $signatures)) {
				foreach ($signatures[0] as $signature) {
					$the_form = str_replace($signature, '', $the_form);
				}
				return $the_form;
			}

			$users_type = array('admin' => 'uid', 'office' => 'offid', 'client' => 'clid', 'auditor' => 'uid', 'applicant' => 'aplid');

			if (preg_match_all('/\[signature(.*)\]/U', $the_form, $signatures)) {
				foreach ($signatures[1] as $signature) {
					$user_type = explode('_', $signature)[1];
					$signature_item = '[signature' . $signature . ']';
					$signature_id = 'signature_holder' . $signature;
					if (!isset($_SESSION['offid']))
						$_SESSION['offid'] = 0;
					$users_path = array('admin' => '/offices/0/signatures/', 'office' => '/offid/' . $_SESSION['offid'] . '/signatures/', 'client' => '/clients/' . $_SESSION['uid'] . '/signatures/', 'auditor' => '/offices/0/signatures/');
					$image_file = $users_path[$user_type] . $_SESSION['uid'] . '_signature';

					$image_exts = array('.jpg', '.jpeg', '.png', '.svg');
					foreach ($image_exts as $ext) {
						if (file_exists(data_path . $image_file . $ext))
							$user_signature  = $image_file . $ext;
					}
					ob_start();
	?>
					<input type="text" name="signature<?php echo $signature; ?>" id="signature<?php echo $signature; ?>" data-usertype="<?php echo $user_type; ?>" data-available="yes" class="offscreen">
					<div style="text-align: left;padding:10px;" id="<?php echo $signature_id; ?>">
						You can sign this document using one of the following methods:
						<ol style="margin-top:10px;">
							<li><a class="iframe" title="Digital signature" href="/user/shared/signature/signature.php?<?php echo $users_type[$user_type]; ?>=<?php echo $_SESSION['uid']; ?>&sg=<?php echo $signature_id; ?>&foid=<?php echo isset($data['foid']) ? $data['foid'] : ''; ?>" data-height="450" data-width="700">Digital signature</a></li>
							<li><a class="iframe" title="Upload signature" href="/user/shared/signature/signature.php?act=upload&sg=<?php echo $signature_id; ?>&<?php echo $users_type[$user_type]; ?>=<?php echo $_SESSION['uid']; ?>&foid=<?php echo isset($data['foid']) ? $data['foid'] : ''; ?>" data-height="280" data-width="400">Uploading your signature</a></li>
							<?php if (is_client()) { ?>
								<li><a title="Digital signature" href="/admin/forms/pdf.php?<?php echo $users_type[$user_type]; ?>=<?php echo $_SESSION['uid']; ?>&foid=<?php echo $_REQUEST['foid']; ?><?php echo (isset($_REQUEST['process'])) ? '&process=' . $_REQUEST['process'] : ''; ?><?php echo (isset($_REQUEST['appid'])) ? '&appid=' . $_REQUEST['appid'] : ''; ?><?php echo isset($_GET['clid']) ? '&clid=' . $_GET['clid'] : ''; ?>&foid=<?php echo isset($data['foid']) ? $data['foid'] : ''; ?>" target="pdfIframe">Download the document, sign it and upload it once again</a></li>
							<?php }; ?>
							<?php if (isset($user_signature)) { ?>
								<li><a onclick="insertSignature('<?php echo $user_signature; ?>','signature<?php echo $signature; ?>','<?php echo $signature_id; ?>')">Use saved signature</a></li>
							<?php }; ?>
						</ol>
					</div>
			<?php
					$signature_text = ob_get_contents();
					ob_end_clean();
					$the_form = str_replace($signature_item, $signature_text, $the_form);
				}
			}
			return $the_form;
		}

		public function view_form($the_form)
		{
			ob_start();
			?>
			<div id="viewFormHolder">
				<?php
				echo $the_form;
				?>
			</div>
			<script>
				//function to convert date from 2024-05-08 to 08/05/2024
				function convertDate(date) {
					if (date == '')
						return '';
					var dateParts = date.split("-");
					return dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
				}
				jQuery(document).ready(function() {

					jQuery("#viewFormHolder *").each(function() {
						if (jQuery(this).is("input[type=hidden]") || jQuery(this).is("input[type=file]")) {
							jQuery(this).remove();
						}

						if (jQuery(this).is("select")) {
							if (jQuery(this).find("option:selected").text() == '')
								val = '<span style="color:#eee">N/A</span>';
							else
								val = jQuery(this).find("option:selected").text()
							jQuery(this).replaceWith(val);
						}

						if (jQuery(this).is("input[type=hidden]") || jQuery(this).is("input[type=file]")) {
							jQuery(this).remove();
						}

						if (jQuery(this).is("input[type=text]") || jQuery(this).is("input[type=date]") || jQuery(this).is("input[type=time]") || jQuery(this).is("input[type=email]") || jQuery(this).is("input[type=number]") || jQuery(this).is("input[type=tel]") || jQuery(this).is("input[type=url]") || jQuery(this).is("input[type=hidden]")) {
							if (jQuery(this).val().trim() == '') {
								val = '<span style="color:#eee;width:' + jQuery(this).width() + 'px;display: inline-block;">N/A</span>';
							} else {
								val = jQuery(this).val();
							}
							if (jQuery(this).is("input[type=date]"))
								val = convertDate(val);
							jQuery(this).replaceWith(val);
						}

						if (jQuery(this).is("textarea")) {
							if (jQuery(this).val() == '') {
								val = '<span style="color:#eee;width:' + jQuery(this).width() + 'px;display: inline-block;">N/A</span>';
							} else {
								val = jQuery(this).val().replace(/\n/g, "<br/>");
							}
							jQuery(this).replaceWith(val);
						}

						if (jQuery(this).is("input[type=password]")) {
							if (jQuery(this).val() == '')
								val = '<span style="color:#eee">N/A</span>';
							else
								val = jQuery(this).val();
							jQuery(this).replaceWith('*****');
						}

						if (jQuery(this).is("input[type=radio]")) {
							if (jQuery(this).is(":checked"))
								jQuery(this).replaceWith('<i class="far fa-check-circle" style="font-size:15px !important;"></i>');
							else
								jQuery(this).replaceWith('<i class="far fa-circle" style="font-size:15px !important;"></i>');
						}
						if (jQuery(this).is("input[type=checkbox]")) {
							if (jQuery(this).is(":checked"))
								jQuery(this).replaceWith('<i class="far fa-check-square" style="font-size:15px !important;"></i>');
							else
								jQuery(this).replaceWith('<i class="far fa-square" style="font-size:15px !important;"></i>');
						}
					}) jQuery("#viewFormHolder label,#viewFormHolder i").css("cursor", "default");
				});
			</script>
<?php
			$formContent = ob_get_contents();
			ob_end_clean();
			echo $formContent;
			return;
		}

		public function get_form($foid, $data = array(), $get = 'form')
		{
			global $hqcdb, $_USER;

			if (isset($_USER['offid']))
				$offid = $_USER['offid'];
			else
				$offid = 0;
			$office = get_office_data($offid);

			//check if the $data is null
			if (is_array($data) && count($data) > 0)
				$data = $data + $office;
			else
				$data = $office;

			if (isset($data['the_form'])) {
				$theForm = $_REQUEST;
				$the_form =  $theForm['the_form'];
			} else {
				if (!isset($foid))
					return;

				if (is_numeric($foid))
					$data['foid'] = $foid;

				if (!$theForm = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid = '$data[foid]'"))
					return;

				if (trim($theForm['form_options']) != '' && is_array(decode_json($theForm['form_options'])))
					$form_option = decode_json($theForm['form_options']);

				if ($foid != null && is_numeric($foid))
					$the_form =  $theForm['the_form'];
				else
					$the_form = $foid;
			}

			$the_form = str_replace(array('hidden-input'), array('hidden'), $the_form);
			if ($get != 'pdf')
				$the_form = $this->remove_content($the_form, 'pdf');

			if (isset($theForm['include_php']))
				$this->include_content($theForm['include_php'], 'php');

			$the_form_content = $this->include_content(htmlspecialchars_decode($the_form));
			$the_form = $the_form_content[0];

			$the_form = $this->fix_actions($the_form);
			$the_form = $this->make_tabs($the_form);
			$the_form = $this->make_popups($the_form);
			$the_form = $this->get_signature($the_form, $data, 'html');
			$the_form = str_replace(array('[signature_client]', '[signature_client]', '[html]', '[/html]'), '', $the_form);



			if (count($the_form_content[1]) > 0)
				$data = array_merge($the_form_content[1], $data);

			if (isset($data) and count($data) > 0 and isset($the_form) and trim($the_form) != "") {

				foreach ($data as $key => $value) {
					if (!is_array($value) and is_array(json_decode($value, true))) {
						$data[$key] = json_decode($value, true);
					}
				}

				if (!isset($data['date']) or trim($data['date']) == '') $data['date'] = date('d/m/Y');
				if (!isset($data['time']) or trim($data['time']) == '') $data['time'] = date('H:i');

				$the_form = str_replace('<div></div>', '<br/>', $the_form);

				if (count($data) > 0) {
					$the_form = $this->fill_data($the_form, $data);
					$data = key_implode($data);
					$the_inputs = $data;
				}

				if (@!$doc = load_DOMDocument($the_form))
					return;

				$els = $doc->getElementsByTagName('fieldset');
				for ($i = $els->length - 1; $i >= 0; $i--) {
					$el = $els->item($i);
					if ($el->hasAttribute('data-type') && $el->getAttribute('data-type') == 'instructions') {
						$el->parentNode->removeChild($el);
					}
				}

				$inputsAll = array();
				$tags = array('select', 'textarea', 'input');
				$fInputs = '';
				$orgNames = array();
				$orgSnr = 0;

				foreach ($tags as $tag) {

					$els = $doc->getElementsByTagName($tag);
					for ($i = $els->length - 1; $i >= 0; $i--) {
						$orgSnr++;
						$el = $els->item($i);
						$orgName = $el->getAttribute('name');
						$name = str_replace(array('[]', '[', ']'), array('', '_', ''), $el->getAttribute('name'));
						$orgNames[$orgSnr] = $orgName;

						$el->setAttribute('name', '**' . $orgSnr . '**');

						$type = $el->getAttribute('type');

						if (isset($data[$name])) {
							$the_inputs[$name] = $data[$name];
						}

						if ($tag == 'textarea') {
							if (isset($the_inputs[$name]) and !is_array($the_inputs[$name])) {
								if ($get != 'form') {
									$newEl = $doc->createTextNode($the_inputs[$name]);
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								} else {
									$el->nodeValue = $the_inputs[$name];
								}
							} else {
								if ($get == 'pdf') {
									$newEl = $doc->createTextNode('N/A');
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								}
							}
						}
						//fill in element type inputs
						$inputTags = array('text', 'password', 'date', 'email', 'number', 'range', 'search', 'tel', 'url', 'color');
						if (in_array($type, $inputTags)) {
							if (isset($the_inputs[$name]) and !is_array($the_inputs[$name])) {
								if ($get != 'form') {
									$newEl = $doc->createTextNode($the_inputs[$name]);
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								} else {
									$el->setAttribute('value', $the_inputs[$name]);
								}
							} else {
								if ($get != 'form') {
									$newEl = $doc->createTextNode('N/A');
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								}
							}
						}

						if ($get == 'pdf') {
							$exTypes = array('file', 'hidden', 'form', 'button', 'submit', 'reset');
							if (in_array($type, $exTypes)) {
								$el->parentNode->removeChild($el);
							}
						}
						//fill in element type radio and checkbox
						if ($type == 'radio' or $type == "checkbox") {
							$el->removeAttribute('id');

							if ((isset($the_inputs[$name]) && $the_inputs[$name] == $el->getAttribute('value')) or (isset($the_inputs[$name]) && isset($the_inputs[$name][0]) && $the_inputs[$name][0] == $el->getAttribute('value'))) {
								if ($get != 'form') {

									if ($type == 'radio')
										$checked = 'circle-checked.svg';
									else
										$checked = 'square-checked.svg';

									$newEl = $doc->createTextNode('<img src="' . hqc_url . '/images/' . $checked . '" style="width:15px;height:15px"/> ');
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								} else {
									$el->setAttribute('checked', 'checked');
								}
							} else {
								if ($get != 'form') {
									if ($type == 'radio')
										$checked = 'circle.svg';
									else
										$checked = 'square.svg';
									$newEl = $doc->createTextNode('<img src="' . hqc_url . '/images/' . $checked . '" style="width:15px;height:15px"/> ');
									$el->parentNode->replaceChild($newEl, $el);
									unset($newEl);
								} else {
									$el->removeAttribute('checked');
								}
							}
						}
						//fill in element type select
						if ($tag == 'select' and isset($the_inputs[$name])) {
							if (is_array($the_inputs[$name]))
								$the_select = $the_inputs[$name];
							else
								$the_select = array($the_inputs[$name]);
							$options = $el->getElementsByTagName('option');
							foreach ($options as $option) {
								if (in_array(trim($option->getAttribute('value')), $the_select) or in_array(trim($option->nodeValue), $the_select)) {
									if ($get != 'form') {
										$newEl = $doc->createTextNode($option->textContent);
										$el->parentNode->replaceChild($newEl, $el);
										unset($newEl);
									} else {
										$option->setAttribute('selected', 'selected');
									}
								} else {
									$option->removeAttribute('selected');
								}
							}
						}

						if ($el->hasAttribute('data-usertype')) {
							$user_types = explode(',', $el->getAttribute('data-usertype'));
							if (!in_array(get_user_type(), $user_types)) {
								//$el->setAttribute('disabled', 'disabled');
								$el->setAttribute('style', 'border-color:#EEEEEE');
								$el->setAttribute('placeholder', 'Filled out by ' . $el->getAttribute('data-usertype'));
								$el->removeAttribute('data-required');
							}
						}
					}
				}
				$the_form = unload_DOMDocument($doc);
			}

			if (isset($theForm['header_template']) and trim($theForm['header_template']) != 0) {
				if ($theFormHeader = $this->get_template($theForm['header_template'], $theForm)) {
					$footer = $theForm['footer'];
					$header = $theFormHeader['header'];
					$the_form = $header . $the_form;
				}
			};
			foreach ($orgNames as $orgKey => $orgValue) {
				$the_form = str_replace('"**' . $orgKey . '**"', '"' . $orgValue . '"', $the_form);
			}
			$the_form = $this->include_content($the_form)[0];
			$the_form = str_replace('&nbsp;', '', $the_form);

			if (isset($form_option['approval_required']) && trim($form_option['approval_required']['type']) != '') {
				$approval_type = $form_option['approval_required']['type'];
				$approval_password = $form_option['approval_required']['password'];

				if ($approval_type == 'password') {
					$the_form .= verify_user('password', $approval_password);
				} elseif ($approval_type == 'sms') {
					$the_form .= verify_user('sms');
				};
			}

			if (!isset($theForm['css_js']))
				$theForm['css_js'] = '';
			if ($get == 'pdf') {
				$this->make_pdf(htmlspecialchars_decode($the_form), $data);
			} else {
				$content = $this->fix_spaces($theForm['css_js']) . '<script src="' . shared_url . '/pdf/pdf_contents.js"></script><div id="formHolder">' . htmlspecialchars_decode($the_form) . '</div><script>do_date();post_links();</script>';
				if ($get == 'html')
					return $this->view_form($content);
				else
					return $content;
			}
		}

		//getting for inputs
		private function get_inputs($foid)
		{
			global $hqcdb;
			if (!isset($foid) or !is_numeric($foid))
				return;
			$inputs = array();
			if ($theForm = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid='$foid'")) {
				$the_form = $theForm['the_form'];
				if (@!$doc = load_DOMDocument($the_form))
					return;
				$tags = array('textarea', 'input', 'select');
				foreach ($tags as $tag) {
					$els = $doc->getElementsByTagName($tag);
					foreach ($els as $el) {
						$name = $el->getAttribute('name');
						$type = $el->getAttribute('type');
						if ($type != 'hidden' and !in_array($name, $inputs)) {
							$inputs[] = $name;
						}
					}
				}
			}
			return ($inputs);
		}

		public function make_pdf($the_content, $data = array(), $pdfFile = '')
		{
			global $hqcdb;

			$form_options = array();
			$form_meta = array();
			if (isset($data['foid'])) {

				if ($theForm = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid = '$data[foid]'")) {

					if (is_array(decode_json($theForm['form_options']))) {
						$form_options = decode_json($theForm['form_options']);

						if (is_array(decode_json($theForm['form_meta']))) {
							$form_meta = decode_json($theForm['form_meta']);
						}
					}
				} else {
					return;
				}
			} else {
				return;
			}

			if (isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == "custom") {
				$dataReplace = array("form_id" => $theForm['form_id'], "form_name" => $theForm['form_name'], "revision" => $theForm['revision']);
				foreach ($form_meta as $k => $v) {
					$dataReplace[$k] = $v;
				}
				if (isset($theForm['form_header']) && trim($theForm['form_header']) != '') {
					$header = $theForm['form_header'];
					foreach ($dataReplace as $dataKey => $dataValue) {
						$header = str_replace('[' . $dataKey . ']', $dataValue, $header);
					}
				}
				if (isset($data['form_footer']) && trim($data['form_footer']) != '') {
					$footer = $data['form_footer'];
					foreach ($dataReplace as $dataKey => $dataValue) {
						$footer = str_replace('[' . $dataKey . ']', $dataValue, $footer);
					}
				}
			}

			$the_content = preg_replace("/<\!--\[pdf](.*?)\[\/pdf]-->/is", '[pdf]$1[/pdf]', $the_content);
			$the_content = preg_replace("/<\!--\[pdf(.*?)\]-->/is", '[pdf$1]', $the_content);

			$the_content = preg_replace("/\<info\>(.*)\<\/info\>/U", '', $the_content);
			$the_content = preg_replace('/\s+/', ' ', $the_content);
			$the_content = str_replace('<tr data-add="page">', '</table>[pdf addPage()]<table class="application"><tr>', $the_content);
			//$the_content = str_replace('<table', '<table nobr="true"', $the_content);

			$the_content = preg_replace("/\<br\>\<\/td\>/is", '</td>', $the_content);
			$the_content = preg_replace("/<form(.*)>|<\/form>|<p[^>]*><\\/p[^>]*>/", '', $the_content);
			$the_content = str_replace(array("\r\n\r\n", '<p></p>', '*'), '', trim($the_content));
			if (is_local())
				$the_content = str_replace('https:', 'http:', $the_content);

			require_once(tools_path . "/pdf/tcpdf/hcp_pdf.inc.php");

			if (isset($margins) && strstr($margins, ',')) {
				$margins = explode(',', $margins);
				$pdf->SetMargins($margins[0], $margins[1], $margins[2]);
			} else {
				$pdf->SetMargins(15, 20, 15);
			}

			$pdf->setListIndentWidth(0);

			if (isset($showHeader) and $showHeader == true or isset($header)) {
				$pdf->SetPrintHeader(true);
			}

			if (isset($showFooter) and $showFooter == true or isset($footer)) {
				$pdf->SetPrintFooter(true);
			}

			if (isset($theForm['pdf_template']) && trim($theForm['pdf_template']) != "" && isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == "pdf") {
				$templateFile = hqc_path . '/data/offices/' . $theForm['offid'] . '/templates/' . $theForm['pdf_template'];
				if (file_exists($templateFile)) {
					$pages = $pdf->setSourceFile($templateFile);
					setTemplate(1);
				}
			}

			$tagvs = array('p' => array(0 => array('n' => 1, 'h' => '1'), 1 => array('n' => 1, 'h' => '1')));
			$pdf->setHtmlVSpace($tagvs);
			if (preg_match("/\<style\>(.*?)\<\/style\>/is", $the_content, $style)) {
				$the_content = str_replace($style[0], '', $the_content);
			}

			if (isset($theForm['pdf_css_js']) and trim($theForm['pdf_css_js']) != '') {
				if (preg_match("/\<style\>(.*?)\<\/style\>/is", $theForm['pdf_css_js'], $style)) {
					$theForm['style'] = $style[0];
				} else {
					$theForm['style'] = '<style>' . $theForm['pdf_css_js'] . '</style>';
				}
			}
			//remove all style attrs from the content
			$the_content = preg_replace('/style=".*?"/', '', $the_content);

			$the_content = preg_replace('/> </', '><', $the_content);

			//add image size width:14px;height:14px of type svg
			$the_content = preg_replace('/<img src="(.*?).svg"/', '<img src="$1.svg" style="width:14px;height:14px"', $the_content);

			//add cellspacing="0" cellpadding="2" to all tables
			$the_content = preg_replace('/<table/', '<br/><table cellspacing="0" cellpadding="5"', $the_content);

			//remove div wrapped the [pdf] tags
			$the_content = preg_replace('/<div>\[pdf(.*?)\]<\/div>/', '[pdf$1]', $the_content);

			$the_content = str_replace(array('<br>[pdf addPage()]<br>', '<p>[pdf addPage()]</p>', '<p><!-- pagebreak --></p>', '<!-- pagebreak -->'), '[pdf addPage()]', trim($the_content));
			$the_content = str_replace(array('&amp;', '&lsquo;', '&rsquo;', ') ]'), array('&', '‘', '’', ')]'), $the_content);
			preg_match_all('/\[pdf(.*)\]/U', $the_content, $thePatrs);
			foreach ($thePatrs[0] as $macth) {
				$the_content  = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $the_content);
			};

			$the_content = str_replace(array('<brkPoint><br>', '<brkPoint><brkPoint><brkPoint>', '<brkPoint><brkPoint>'), '<brkPoint>', $the_content);
			$pdfParts = explode('<brkPoint>', $the_content);


			foreach ($pdfParts as $key => $part) {
				if (strstr($part, '[pdf ')) {
					preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);
					if (trim($pdfMatch[1]) == 'setY') {
						$y = $pdfMatch[2];
						if (strstr($y, '+'))
							$y = $pdf->getY() + str_replace('+', '', $y);
						$pdf->setY($y);
					} elseif (trim($pdfMatch[1]) == 'setX') {
						$x = $pdfMatch[2];
						if (strstr($x, '+'))
							$x = $pdf->getX() + str_replace('+', '', $x);
						$pdf->setX($x);
					} elseif (trim($pdfMatch[1]) == 'addPage') {
						if (isset($option['landscape'])) {
							$marginTop = 15;
							$marginBottom = 15;
						} else {
							$marginTop = 25;
							$marginBottom = 25;
						}
						if (trim($pdfMatch[2]) != '') {
							$margin = explode(',', $pdfMatch[2]);
							if (isset($margin[0]))
								$marginTop = $margin[0];
							if (isset($margin[1]))
								$marginBottom = $margin[1];
						}
						$pdf->SetAutoPageBreak(TRUE, $marginBottom);
						$pdf->SetMargins(15, $marginTop, 15);
						if (isset($option['landscape'])) {
							$pdf->AddPage('L');
						} else {
							$pdf->AddPage();
						}
					} elseif (trim($pdfMatch[1]) == 'setMargins') {
						$marginTop = 25;
						$marginBottom = 25;
						if (trim($pdfMatch[2]) != '') {
							$margin = explode(',', $pdfMatch[2]);
							if (isset($margin[0])) {
								$pdf->setY($margin[0]);
								$marginTop = $margin[0];
							}
							if (isset($margin[1]))
								$marginBottom = $margin[1];
						}
						$pdf->SetAutoPageBreak(TRUE, $marginBottom);
						$pdf->SetMargins(15, $marginTop, 15);
					} elseif (trim($pdfMatch[1]) == 'lastPage') {
						$pdf->SetAutoPageBreak(TRUE, 0);
						if (isset($pdfMatch[2]) && $pdf->getY() > $pdfMatch[2]) {
							$pdf->AddPage();
						} elseif (trim($pdfMatch[1]) == 'footer') {
						}
					}
				} elseif (trim(strip_tags($part)) != '') {
					echo trim($part);
					ob_start();
					echo $theForm['style'];
					$part = str_replace(array('&amp;', '&lsquo;', '&rsquo;'), array('&', '‘', '’'), $part);
					echo trim($part);
					if (preg_match('/[\p{Cyrillic}]/u', $part))
						$pdf->SetFont('freeserif');
					$pdf->writeHTML(ob_get_contents());
					ob_end_clean();
				}
			}

			if (trim($pdfFile) != '') {
				$pdfFileDir = dirname($pdfFile);
				if (!is_dir($pdfFileDir))
					mkdir($pdfFileDir, 0777, true);
				$pdf->Output($pdfFile, 'F');
				if (file_exists($pdfFile))
					return true;
			} else {
				$pdf->Output('form.pdf', 'I');
			}
		}

		private function get_template($tmpl = null, $data = array())
		{
			global $hqcdb;
			if ($tmpl != null and count($data) > 0) {
				$theFormHeaders = array();
				$template = $hqcdb->get_row("SELECT * FROM hqc_elements_templates WHERE assid='$tmpl'");
				$template_inputs = decode_json($data['form_meta']);
				$template_inputs['form_id'] = $data['form_id'];
				$template_inputs['revision'] = $data['revision'];
				$template_inputs['form_name'] = $data['template_name'];
				if (trim($template['template_header']) != '') {
					$header = trim($template['template_header']);
					foreach ($template_inputs as $key => $value) {
						if (strstr($header, '[' . $key . ']'))
							$header = str_replace('[' . $key . ']', $value, $header);
					}
					$theFormHeaders['header'] = $header;
				}
				if (trim($template['template_footer']) != '') {
					$footer = trim($template['template_footer']);
					foreach ($template_inputs as $key => $value) {
						if (strstr($footer, '[' . $key . ']'))
							$footer = str_replace('[' . $key . ']', $value, $footer);
					}
					$theFormHeaders['footer'] = $footer;
				}
				return $theFormHeaders;
			}
		}
	}
}
$amform = new amform();
