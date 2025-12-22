<?php
if (!defined("__HQC__")) {
	exit();
}
if (!session_id() and !isset($_SESSION['AM_REFFERER']) and $_SESSION['AM_REFFERER'] != $_SERVER['HTTP_HOST']) {
	exit();
}

function is_client()
{
	return false;
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
			$content = preg_replace("/\[$remove](.*?)\[\/$remove]/is", '<!--$0-->', $content);
			$content = preg_replace("/\[$remove(.*?)\]/is", '<!--$0-->', $content);
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

		private function include_content($content, $incType = '')
		{
			global $data, $amdb, $_USER, $prog_path;
			//define temporary file and dir
			$temp_path = $prog_path . "/data/temp";
			$temFile = $temp_path . "/" . time() . ".inc.php";
			if (@$dir  = opendir($temp_path)) {
				while (false !== ($filename = readdir($dir))) {
					if (is_file($temp_path . "/$filename") && $filename != "index.html") {
						unlink($temp_path . "/$filename");
					}
				}
				@closedir($dir);
			}

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
			// echo  $this->fix_spaces($theData);
			$fl = fopen($temFile, "w");
			fwrite($fl, $this->fix_spaces($theData));
			fclose($fl);
			if ($incType == 'php') {
				if (!defined("functions"))
					include $prog_path . "/functions.inc.php";
				include $temFile;
				//unlink($temFile);
			} else {
				ob_start();
				if (!defined("functions"))
					include $prog_path . "/functions.inc.php";
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
								$tabId = trim(str_replace(array(' ', '(', ')', '__'), array('_', '', '', '_'), strtolower($the_title)));

								$tabs .= '<li data-id="' . $tabId . '" ' . (!isset($tabStart) ? ' class="active"' : '') . ' data-tab_nr="' . ($tab_nr) . '">' . $the_title . '</li>';
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
						$group_content .= '<ul class="tabs-pagination" data-tabsGroup="' . $groupId . '"><li style="width:10%" class="tab-previous">Previous</li><li style="text-align:center;width:80%" id="' . $groupId . '_footer"></li><li style="width:10%" class="tab-next">Next</li></ul>';
					}
					$the_form = str_replace($tab_group[0], $group_content, $the_form);


					if (isset($groupVars['joined']) && $groupVars['joined'] == 'yes') {
						$tabs .= '<li class="tabs-all"><i class="fas fa-angle-double-down"></i></li>';

						$the_form = str_replace('[tab_header]', '<ul class="tabs" data-tabs="' . $groupId . '" data-total_tabs="' . $tab_nr . '">' . $tabs . '</ul>', $the_form);
					}
				}
			}
			return $the_form;
		}


		function get_signature($the_form, $data, $htmlPdf)
		{
			global $prog_path;

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

			$users_type = array('admin' => 'uid', 'office' => 'offid', 'client' => 'clid', 'auditor' => 'uid', 'committee' => 'comemid');

			if (preg_match_all('/\[signature(.*)\]/U', $the_form, $signatures)) {
				foreach ($signatures[1] as $signature) {
					$signature_parts = explode('_', $signature);
					$user_type = $signature_parts[1];
					if (isset($signature_parts[2]))
						$signatureID = $signature_parts[2];
					else
						$signatureID = '';
					$signature_item = '[signature' . $signature . ']';
					$signature_id = 'signature_holder' . $signature;

					$users_path = array('admin' => '/offices/0/signatures/', 'office' => '/offid/' . $_SESSION['offid'] . '/signatures/', 'client' => '/clients/' . $_SESSION['uid'] . '/signatures/', 'auditor' => '/offices/0/signatures/', 'committee' => '/Committee_members/' . $signatureID . '/signatures/');
					$image_file = $users_path[$user_type] . $_SESSION['uid'] . '_signature';

					$image_exts = array('.jpg', '.jpeg', '.png', '.svg');
					foreach ($image_exts as $ext) {
						if (file_exists("$prog_path/data/" . $image_file . $ext))
							$user_signature  = $image_file . $ext;
					}
					ob_start();
	?>
					<input type="hidden" name="signature<?php echo $signature; ?>" id="signature<?php echo $signature; ?>" data-usertype="<?php echo $user_type; ?>" data-available="yes" class="offscreen">
					<div style="text-align: left;padding:10px;" id="<?php echo $signature_id; ?>">
						You can sign this document using one of the following methods:
						<ol style="margin-top:10px;">
							<li><a class="iframe" title="Digital signature" href="signature/signature.php?<?php echo $users_type[$user_type]; ?>=<?php echo $_SESSION['uid']; ?>&sg=<?php echo $signature_id; ?>&foid=<?php echo isset($data['foid']) ? $data['foid'] : ''; ?>" data-height="450" data-width="700">Digital signature</a></li>
							<li><a class="iframe" title="Upload signature" href="signature/signature.php?act=upload&sg=<?php echo $signature_id; ?>&<?php echo $users_type[$user_type]; ?>=<?php echo $_SESSION['uid']; ?>&foid=<?php echo isset($data['foid']) ? $data['foid'] : ''; ?>" data-height="280" data-width="400">Uploading your signature</a></li>
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

		public function view_form($foid, $data = array(), $pdfHtml = 'html', $pdfFile = '')
		{
			global $amdb, $_USER;
			if ($pdfHtml == 'html') {
				ob_start();
				?>
				<div id="viewFormHolder">
					<?php
					echo $this->get_form($foid, $data);
					?>
				</div>
				<script>
					jQuery(document).ready(function() {

						jQuery("#viewFormHolder *").each(function() {

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

							if (jQuery(this).is("input[type=text]")) {
								if (jQuery(this).val().trim() == '') {
									val = '<span style="color:#eee;width:' + jQuery(this).width() + 'px;display: inline-block;">N/A</span>';
								} else {
									val = jQuery(this).val();
								}
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
									jQuery(this).replaceWith('<i class="far fa-check-circle" style="font-size:14px !important;color:green !important"></i>');
								else
									jQuery(this).replaceWith('<i class="far fa-circle" style="font-size:14px !important;color:inherit !important"></i>');
							}
							if (jQuery(this).is("input[type=checkbox]")) {
								if (jQuery(this).is(":checked"))
									jQuery(this).replaceWith('<i class="far fa-check-square" style="font-size:14px !important;color:green !important"></i>');
								else
									jQuery(this).replaceWith('<i class="far fa-square" style="font-size:14px !important;color:inherit !important"></i>');
							}
						})
						jQuery("#viewFormHolder label,#viewFormHolder i").css("cursor", "default");
					});
				</script>
				<?php
				$formContent = ob_get_contents();
				ob_end_clean();
				echo $formContent;
				return;
			}


			if (isset($_USER['offid']))
				$offid = $_USER['offid'];
			else
				$offid = 0;

			if (!$office = get_office_data($offid))
				$office = array();

			if (is_array($data) && count($data) > 0)
				$data = $data + $office;
			else
				$data = $office;

			if (isset($data['theForm'])) {
				$theForm = $data['theForm'];
				$the_form =  $theForm['the_form'];
				$data = $data + $theForm;
				unset($data['theForm']);
				unset($data['the_form']);
			} else {
				if (!isset($foid))
					return;

				if (is_numeric($foid))
					$data['foid'] = $foid;
				if (!$theForm = $amdb->get_row("SELECT * FROM hqc_forms WHERE foid = '$data[foid]'"))
					return;

				if ($foid != null && is_numeric($foid))
					$the_form =  $theForm['the_form'];
				else
					$the_form = $foid;
			}

			if (isset($data['the_form']))
				$the_form =  $data['the_form'];

			if (isset($data['clid'])) {
				if (isset($data['appid']) and $thisUserForm = $amdb->get_row("SELECT * FROM hqc_applications WHERE appid='$data[appid]'")) {
					if (is_array(unserialize($thisUserForm['form_content']))) {
						$form_content = unserialize($thisUserForm['form_content']);
						$data = $form_content[array_key_last($form_content)];
					}
				} elseif ($client_data = get_client($data['clid'])) {
					$data = array_merge($client_data, $data);
				}
			}

			//check if data is passed
			if (count($data) > 0) {
				$data = key_implode($data);
				$the_inputs = $data;
				$_POST = $data;
			} else {
				return;
			}

			if ($the_inputs) {
				if ($pdfHtml == 'html') {
					$the_form = $this->get_signature($the_form, $data, 'html');
					$the_form = $this->remove_content($the_form, 'pdf');
					$the_form = $this->make_tabs($the_form);
					$the_form = $this->make_popups($the_form);
				} else {
					$the_form = $this->get_signature($the_form, $data, 'pdf');
					$the_form = $this->remove_content($the_form, 'html');
					$the_form = $this->remove_shortcodes($the_form, 'tab-item,tab-group,signature,popup');
					$the_form = str_replace(array(' alternateOn', ' alternate', '<!-- pagebreak -->', '<table', '</table>', '.svg"'), array('', '', '[pdf addPage()]', '<div style="font-size:1px;color:#ffffff" class="beforeTable">-</div><table', '</table><div style="font-size:5px;color:#ffffff" class="afterTable">-</div>', '.svg" style="width:14px;height:14px"'), $the_form);
				}

				if (trim($theForm['include_php']) != '')
					$this->include_content($theForm['include_php'], 'php');
				$the_form_content = $this->include_content($the_form);

				$the_form = $the_form_content[0];
				if (count($the_form_content[1]) > 0)
					$data = array_merge($the_form_content[1], $data);

				$the_form = str_replace(array('<label>', '</label>'), '', $the_form);

				if (@!$doc = load_DOMDocument($the_form))
					return;

				$els = $doc->getElementsByTagName('*');
				for ($i = $els->length - 1; $i >= 0; $i--) {
					$el = $els->item($i);
					if ($el->hasAttribute('data-insert')) {
						$data_insert = $el->getAttribute('data-insert');
						if (isset($data[$data_insert])) {
							$newEl = $doc->createTextNode($data[$data_insert]);
							$el->parentNode->appendChild($newEl);
						}
					}
				}

				$inputsAll = array();
				$tags = array('textarea', 'input', 'select');
				foreach ($tags as $tag) {
					$els = $doc->getElementsByTagName($tag);
					for ($i = $els->length - 1; $i >= 0; $i--) {
						$el = $els->item($i);
						$name = str_replace('[]', '', $el->getAttribute('name'));
						$nameEmploded = str_replace(array('[', ']'), array('_', ''), $name);
						$the_form = str_replace($name, $nameEmploded, $the_form);
					}
				}

				foreach ($tags as $tag) {
					$els = $doc->getElementsByTagName($tag);
					for ($i = $els->length - 1; $i >= 0; $i--) {
						$el = $els->item($i);
						if ($el->hasAttribute('data-available') && !isset($data['signature']) && $pdfHtml == 'html') {
						} else {
							//$name = str_replace('[]','',$el->getAttribute('name'));
							$name = $el->getAttribute('name');
							//if (isset($the_inputs[$name]))
							//	echo $the_inputs[$name] . "\n";
							$nameEmploded = str_replace(array('[', ']'), array('_', ''), $name);
							$type = $el->getAttribute('type');
							if (isset($data[$name])) {
								$the_inputs[$name] = $data[$name];
							}
							if (isset($data[$nameEmploded])) {
								$the_inputs[$name] = $data[$nameEmploded];
							}
							if (trim($name) != '' and trim($type) != 'radio' and !strstr($name, '[]') and isset($inputsAll[$name])) {
								_e('Duplicated element: ' . $name . '(Index: ' . $i . ')');
								exit();
							}

							$inputsAll[$name] = $name;
							if ($tag == 'select')
								$name = str_replace('[]', '', $name);
							unset($newEl);
							if ($type == 'checkbox' or $type == 'radio') {

								if (isset($the_inputs[$name]) && ($the_inputs[$name] == $el->getAttribute('value') or $type == 'checkbox')) {
									$newEl = $doc->createTextNode('<img src="/images/check-square-checked.svg" style="width:14px;height:14px"/> ');
								} else {
									$newEl = $doc->createTextNode('<img src="/images/square.svg" style="width:14px;height:14px"/> ');
								}
							} elseif (isset($the_inputs[$name])) {
								if ($tag == 'select' and isset($the_inputs[$name])) {
									if (is_array($the_inputs[$name]))
										$the_select = $the_inputs[$name];
									else
										$the_select = array($the_inputs[$name]);
									$the_option = array();
									$options = $el->getElementsByTagName('option');
									foreach ($options as $option) {
										if (in_array(trim($option->getAttribute('value')), $the_select) or in_array(trim($option->nodeValue), $the_select)) {
											$the_option[] = $option->textContent;
										}
									}
									if (strstr($name, 'my_management_signatures'))
										$newEl = $doc->createTextNode('<span class="viewEl">' . implode(', ', $the_option) . '</span><br/><img src="' . get_management_signature($the_inputs[$name]) . '" style="width:3cm" width="200" class="signature"/>');
									else
										$newEl = $doc->createTextNode('<span class="viewEl">' . implode(', ', $the_option) . '</span>');
								} elseif ($type == "hidden" or $type == "submit") {
									$newEl = $doc->createTextNode('');
								} else {
									if (trim($the_inputs[$name]) == '')
										$the_inputs[$name] = '<span class="empty"></span>';
									if (!is_array($the_inputs[$name]))
										$newEl = $doc->createTextNode("<span class=\"viewEl\">" . str_replace("\r\n", "<br/>", trim($the_inputs[$name])) . "</span>");
								}
							}
							if (isset($newEl))
								$el->parentNode->replaceChild($newEl, $el);
							else
								$el->parentNode->removeChild($el);
						}
					}
				}

				$the_form = unload_DOMDocument($doc);
				//echo $the_form;
				foreach ($data as $dataKey => $dataValue) {
					if (!is_array($dataValue))
						$the_form = str_replace('[' . $dataKey . ']', $dataValue, $the_form);
				}
				if ($pdfHtml == 'html') {
					$the_form = $this->include_content("\n" . htmlspecialchars_decode($the_form))[0];
					$the_form = $this->remove_content($the_form, 'pdf');
					$the_form = $this->fix_actions($the_form);
					//form footer
					$the_form_footer_inputs = '';
					if (isset($theForm['header_template']) and trim($theForm['header_template']) != 0) {
						if ($theFormHeader = $this->get_template($theForm['header_template'], $theForm)) {
							$footer = $theForm['footer'];
							$header = $theFormHeader['header'];
							$the_form = $header . $the_form;
						}
					}
					$the_form = str_replace('&nbsp;', '', $the_form);
					$theForm['css_js'] = str_replace(array("\t"), array(''), $theForm['css_js']);
					return $this->fix_spaces($theForm['css_js']) . htmlspecialchars_decode($the_form) . '<script>do_date();</script>';
				} else {
					$the_form = $this->include_content(htmlspecialchars_decode($this->fix_spaces($theForm['pdf_css_js']) . "\n" . $the_form))[0];
					ob_start();
				?>
					<?php echo $the_form; ?>
<?php
					$the_form = ob_get_contents();
					ob_end_clean();

					$styleElements = array();
					$styleEles = array();
					if (preg_match("/\<style\>(.*?)\<\/style\>/is", $the_form, $style)) {
						$styles = explode('}', str_replace("\n", '', trim($style[1])));
						foreach ($styles as $style) {
							if (strstr($style, 'nth-child')) {
								$theStyles = explode('{', $style);
								$items = explode(':nth-child', $theStyles[0]);
								$styleElements[str_replace(array('.', '#', ' '), array('', '', '_'), trim($items[0])) . '_' . str_replace(array('(', ')'), '', trim($items[1]))] = trim($theStyles[1]);
								$styleEle = explode(' ', $items[0]);
								$styleEles[trim($items[0])][str_replace(array('(', ')'), '', trim($items[1]))] = trim($theStyles[1]);
							}
						}
					}
					if (preg_match_all("/\<!--table(.*)--\>(.*)\<!--\/table--\>/U", $the_form, $content, PREG_SET_ORDER)) {
						foreach ($content as $tableTag) {
							$tags = $tableTag[1];
							$cont = $tableTag[2];
							if (preg_match('/\[(.*)\]/s', $tags, $tag)) {
								parse_str($tag[1], $attrs);
								if (is_array($attrs) and isset($attrs['tr']) and isset($attrs['th'])) {
									$cont = str_replace('<' . $attrs['tr'] . '>', '</td></tr><tr>', $cont);
									$cont = str_replace(
										array('<' . $attrs['th'], '</' . $attrs['th'] . '>'),
										array('<td class="innerTh"' . (isset($attrs['th-style']) ? ' style="' . $attrs['th-style'] . '"' : '') . '><' . $attrs['th'], '</' . $attrs['th'] . '></td><td class="innerTd"' . (isset($attrs['td-style']) ? ' style="' . $attrs['td-style'] . '"' : '') . '>'),
										$cont
									);
								}
							}
							$cont = '<table class="innerTable" cellpadding="0" cellspacing="0" style="width:5cm"><tr>' . $cont . '</td></tr></table>';
							$cont = str_replace("> <", '><', $cont);
							$the_form = str_replace($tableTag[0], $cont, $the_form);
						}
					}
					if (preg_match_all("/\<a(.*?)\>(.*?)\<\/a\>/is", $the_form, $links)) {
						foreach ($links[2] as $link) {
							//$the_form = str_replace($links[0],$link,$the_form);
						}
					}

					$the_form = str_replace(array('&nbsp;', ' ', 'Â'), '', $the_form);
					$doc = load_DOMDocument($the_form);
					foreach ($styleEles as $elKey => $elValue) {
						$theTag = array();
						$elKeyParts = explode(' ', $elKey);
						if (strstr($elKeyParts[0], '.')) {
							$tagAttrs = explode('.', $elKeyParts[0]);
							$theTag['name'] = $tagAttrs[0];
							$theTag['attr'] = $tagAttrs[1];
						} elseif (strstr($elKeyParts[0], '#')) {
							$tagAttrs = explode('#', $elKeyParts[0]);
							$theTag['name'] = $tagAttrs[0];
							$theTag['attr'] = $tagAttrs[1];
						}
						if ($tagEls = $doc->getElementsByTagName($theTag['name'])) {
							for ($i = $tagEls->length - 1; $i >= 0; $i--) {
								$tagEl = $tagEls->item($i);
								if ($theTag['name'] == 'table') {
									$tagEl->setAttribute('cellpadding', '4');
									$tagEl->setAttribute('cellspacing', '0');
								}
								//going throuh attrs to find class and ids
								if ($tagEl->attributes) {
									foreach ($tagEl->attributes as $attr) {
										if ($attr->nodeName == 'class' and isset($theTag['attr']) and $attr->nodeValue == $theTag['attr']) {
											if (isset($elKeyParts[1])) {
												$searchEls = $tagEls->item($i)->getElementsByTagName($elKeyParts[1]);
												for ($s = $searchEls->length - 1; $s >= 0; $s--) {
													$theStyle = '';
													if (isset($elKeyParts[2]))
														$foundElx = $searchEls->item($s)->getElementsByTagName($elKeyParts[2]);
													else
														$foundElx = $searchEls;
													foreach ($foundElx as $key => $found) {
														if (isset($elValue[($key + 1)])) {
															$theStyle = $elValue[($key + 1)];
															if (!$found->getAttribute('style') or ($found->getAttribute('style') and !preg_match('/width:(.*)cm/', $found->getAttribute('style'), $match)))
																$found->setAttribute('style', $theStyle);
														}
													};
												}
											}
										}
									}
								}
							}
						}
					}

					$the_form = unload_DOMDocument($doc);

					$the_form = str_replace('&nbsp;', '', $the_form);
					//	$the_form = str_replace(array('[client_signature]', '[company_signature]'), '', $the_form);
					$this->make_pdf($the_form, $theForm, $pdfFile);
				}
			}
			return;
		}

		public function get_form($foid, $data = array())
		{
			global $amdb, $_USER;
			if (isset($_USER['offid']))
				$offid = $_USER['offid'];
			else
				$offid = 0;

			if (!$office = get_office_data($offid))
				$office = array();

			//check if the $data is null
			if (is_array($data) && count($data) > 0)
				$data = $data + $office;
			else
				$data = $office;

			if (isset($data['theForm'])) {
				$theForm = $data['theForm'];
				$the_form =  $theForm['the_form'];
				unset($data['theForm']);
				foreach ($data as $dataKey => $dataValue) {
					if(!is_array($dataValue))
					$the_form = str_replace('[' . $dataKey . ']', $dataValue, $the_form);
				}
			} else {
				if (!isset($foid))
					return;

				if (is_numeric($foid))
					$data['foid'] = $foid;

				if (!$theForm = $amdb->get_row("SELECT * FROM hqc_forms WHERE foid = '$data[foid]'"))
					return;

				if (trim($theForm['form_options']) != '' && is_array(decode_json($theForm['form_options'])))
					$form_option = decode_json($theForm['form_options']);

				if ($foid != null && is_numeric($foid))
					$the_form =  $theForm['the_form'];
				else
					$the_form = $foid;
			}

			$the_form = str_replace(array('hidden-input'), array('hidden'), $the_form);
			$the_form = $this->remove_content($the_form, 'pdf');
			//$the_form = preg_replace("/\[pdf(.*)\]/U", '', $the_form);
			$the_form = $this->make_tabs($the_form);
			$the_form = $this->make_popups($the_form);
			$the_form = $this->get_signature($the_form, $data, 'html');
			$the_form = str_replace(array('[signature_client]', '[signature_client]', '[html]', '[/html]'), '', $the_form);
			if (isset($theForm['include_php']))
				$this->include_content($theForm['include_php'], 'php');
			$the_form_content = $this->include_content(htmlspecialchars_decode($the_form));
			//print_r($the_form_content);
			$the_form = $the_form_content[0];
			$the_form = $this->fix_actions($the_form);

			if (count($the_form_content[1]) > 0)
				$data = array_merge($the_form_content[1], $data);

			if (isset($data) and count($data) > 0 and isset($the_form) and trim($the_form) != "") {

				foreach ($data as $key => $value) {
					if (!is_array($value) and is_array(json_decode($value, true))) {
						$data[$key] = json_decode($value, true);
					}
				}

				$the_form = str_replace('<div></div>', '<br/>', $the_form);

				if (@!$doc = load_DOMDocument($the_form))
					return;
				if (count($data) > 0) {
					$data = key_implode($data);
					$the_inputs = $data;
					$_POST = $data;
				}

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

						if ($tag == 'textarea' and isset($the_inputs[$name]) and !is_array($the_inputs[$name])) {
							$el->nodeValue = $the_inputs[$name];
						}
						if (($type == 'text' or $type == 'password') and isset($the_inputs[$name]) and !is_array($the_inputs[$name])) {
							$el->setAttribute('value', $the_inputs[$name]);
						}
						if ($type == 'radio') {
							$el->removeAttribute('id');

							if (isset($the_inputs[$name]) && $the_inputs[$name] == $el->getAttribute('value'))
								$el->setAttribute('checked', 'checked');
							elseif (isset($the_inputs[$name]) && isset($the_inputs[$name][0]) && $the_inputs[$name][0] == $el->getAttribute('value'))
								$el->setAttribute('checked', 'checked');
							else
								$el->removeAttribute('checked');
						}
						if ($type == 'checkbox') {
							if (isset($the_inputs[$name]) && $the_inputs[$name])
								$el->setAttribute('checked', 'checked');
							else
								$el->removeAttribute('checked');
						}
						if ($tag == 'select' and isset($the_inputs[$name])) {
							if (is_array($the_inputs[$name]))
								$the_select = $the_inputs[$name];
							else
								$the_select = array($the_inputs[$name]);
							$options = $el->getElementsByTagName('option');
							foreach ($options as $option) {
								if (in_array(trim($option->getAttribute('value')), $the_select) or in_array(trim($option->nodeValue), $the_select)) {
									$option->setAttribute('selected', 'selected');
								} else {
									$option->removeAttribute('selected');
								}
							}
						}
						if ($el->hasAttribute('data-usertype')) {
							$user_types = explode(',', $el->getAttribute('data-usertype'));
							if (count($user_types) > 0) {;
								//if (!in_array(get_user_type(), $user_types)) {
								//$el->setAttribute('disabled', 'disabled');
								$el->setAttribute('style', 'border-color:#EEEEEE');
								$el->setAttribute('placeholder', 'Filled out by ' . $el->getAttribute('data-usertype'));
								$el->removeAttribute('data-required');
							}
							//}
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

			foreach ($data as $dataKey => $dataValue) {
				if (!is_array($dataValue))
					$the_form = str_replace('[' . $dataKey . ']', $dataValue, $the_form);
			}

			foreach ($orgNames as $orgKey => $orgValue) {
				$the_form = str_replace('"**' . $orgKey . '**"', '"' . $orgValue . '"', $the_form);
			}

			//	$the_form = '<div id="fom_' . (isset($data['foid']) ? $data['foid'] : time()) . '_div">' . $the_form . '</div>';
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
			return $this->fix_spaces($theForm['css_js']) . '<div id="formHolder">' . htmlspecialchars_decode($the_form) . '</div><script>do_date();post_links();</script>';
			return;
		}

		//getting for inputs
		private function get_inputs($foid)
		{
			global $amdb;
			if (!isset($foid) or !is_numeric($foid))
				return;
			$inputs = array();
			if ($theForm = $amdb->get_row("SELECT * FROM hqc_forms WHERE foid='$foid'")) {
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

			global $showFooter, $amdb, $pdf, $header, $headerY, $useTemplate, $margins, $direction, $pdfTemplate, $firstPageY, $tools_path, $footer, $header, $paths, $footer_start_page, $footerAlign, $remove_tag, $temp_path, $prog_path;
			//define temporary file and dir
			$temp_path = $prog_path . "/data/temp";
			$temFile = $temp_path . "/" . time() . ".inc.php";

			$form_options = array();

			if (isset($data['form_options']) and is_array(unserialize($data['form_options']))) {
				$form_options = unserialize($data['form_options']);
			}

			$form_meta = array();
			if (isset($data['form_meta']) and is_array(json_decode($data['form_meta'], true))) {
				$form_meta = json_decode($data['form_meta'], true);
			}

			if (isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == "custom") {
				$dataReplace = array("form_id" => $data['form_id'], "form_name" => $data['form_name'], "revision" => $data['revision']);
				foreach ($form_meta as $k => $v) {
					$dataReplace[$k] = $v;
				}
				if (isset($data['form_header']) && trim($data['form_header']) != '') {
					$header = $data['form_header'];
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
			if (isset($data['pdf_include_php'])) {
				$temFile = $temp_path . "/" . time() . ".inc.php";
				$fl = fopen($temFile, "w");
				fwrite($fl, $this->fix_spaces($data['pdf_include_php']));
				fclose($fl);
				include $temFile;
				//unlink($temFile);
			}
			if (isset($remove_tag)) {
				$doc = load_DOMDocument($the_content);
				if (is_array($remove_tag)) {
					foreach ($remove_tag as $remTag) {
						if ($doc->getElementsByTagName($remTag)->length > 0) {
							$infoTags = $doc->getElementsByTagName('info');
							while ($infoTags->length > 0) {
								$info = $infoTags->item(0);
								$info->parentNode->removeChild($info);
							}
						};
						if ($doc->getElementsByTagName('div')->length > 0) {
							$infoTags = $doc->getElementsByTagName('div');
							for ($i = $infoTags->length - 1; $i >= 0; $i--) {
								$info = $infoTags->item($i);
								if ($info->hasAttribute('class') and $info->hasAttribute('class') == $remTag) {
									$info->parentNode->removeChild($info);
								}
							}
						};
					}
				}

				$the_content = unload_DOMDocument($doc);
			}
			$the_content = str_replace('<tr data-add="page">', '</table>[pdf addPage()]<table class="application"><tr>', $the_content);
			//$the_content = str_replace('<table', '<table nobr="true"', $the_content);

			$the_content = preg_replace("/\<br\>\<\/td\>/is", '</td>', $the_content);
			$the_content = preg_replace("/<form(.*)>|<\/form>|<p[^>]*><\\/p[^>]*>/", '', $the_content);
			$the_content = str_replace(array("\r\n\r\n", '<p><a name="annex1"></a></p>', '<p></p>', '*'), '', trim($the_content));


			$addPage = false;
			global $footerY;
			$footerY = -20;
			require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");

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

			$pdf->AddPage();
			if (isset($data['pdf_template']) && trim($data['pdf_template']) != "" && isset($form_options['use_pdf_template']) && $form_options['use_pdf_template'] == "pdf") {
				$templateFile = $prog_path . '/data/offices/' . $data['offid'] . '/templates/' . $data['pdf_template'];
				if (file_exists($templateFile)) {
					$pages = $pdf->setSourceFile($templateFile);
					setTemplate(1);
				}
			}

			$tagvs = array('p' => array(0 => array('n' => 1, 'h' => '1'), 1 => array('n' => 1, 'h' => '1')));
			$pdf->setHtmlVSpace($tagvs);
			if (preg_match("/\<style\>(.*?)\<\/style\>/is", $the_content, $style)) {
				$template['style'] = $style[0];
				$the_content = str_replace($style[0], '', $the_content);
			} elseif (isset($data['pdf_css_js']) and trim($data['pdf_css_js']) != '') {
				if (preg_match("/\<style\>(.*?)\<\/style\>/is", $data['pdf_css_js'], $style)) {
					$template['style'] = $style[0];
				}
			}
			$the_content = str_replace(array('<br>[pdf addPage()]<br>', '<p>[pdf addPage()]</p>', '<p><!-- pagebreak --></p>', '<!-- pagebreak -->'), '[pdf addPage()]', trim($the_content));
			$the_content = str_replace(array('&amp;', '&lsquo;', '&rsquo;', ') ]'), array('&', '‘', '’', ')]'), $the_content);
			preg_match_all('/\[pdf(.*)\]/U', $the_content, $thePatrs);
			foreach ($thePatrs[0] as $macth) {
				$the_content  = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $the_content);
			};

			$the_content = str_replace(array('<brkPoint><br>', '<brkPoint><brkPoint><brkPoint>', '<brkPoint><brkPoint>'), '<brkPoint>', $the_content);
			$pdfParts = explode('<brkPoint>', $the_content);

			$curY = 0;
			$curX = 0;
			ob_start();
			echo $this->fix_spaces($data['pdf_css_js']);
			$pdf->writeHTML(ob_get_contents());
			ob_end_clean();
			foreach ($pdfParts as $key => $part) {
				if (strstr($part, '[pdf ')) {
					preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);
					if (trim($pdfMatch[1]) == 'img') {

						$imgParts = explode(',', $pdfMatch[2]);
						if (strstr($imgParts[2], '+'))
							$imgParts[2] = $curY + str_replace('+', '', $imgParts[2]);
						if (strstr($imgParts[2], '-'))
							$imgParts[2] = ($curY - str_replace('-', '', $imgParts[2]));
						$pdf->Image($file = $imgParts[0], $imgParts[1], $imgParts[2], $w = $imgParts[3], $h = '', $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
					} elseif (trim($pdfMatch[1]) == 'setY') {

						$y = $pdfMatch[2];
						if (strstr($y, '+'))
							$y = $pdf->getY() + str_replace('+', '', $y);
						if (strstr($y, '-'))
							$y = $pdf->getY() - str_replace('-', '', $y);
						$pdf->setY($y);
					} elseif (trim($pdfMatch[1]) == 'setX') {

						$x = $pdfMatch[2];
						if (strstr($x, '+'))
							$x = $curX + str_replace('+', '', $x);
						if (strstr($x, '-'))
							$x = $curX - str_replace('-', '', $x);
						$pdf->SetX($x);
					} elseif (trim($pdfMatch[1]) == 'addPage') {

						if (trim($pdfMatch[2]) != '') {
							$margin = explode(',', $pdfMatch[2]);
							if (isset($margin[0]))
								$marginTop = $margin[0];
							if (isset($margin[1]))
								$marginBottom = $margin[1];
						}
						if (isset($marginBottom))
							$pdf->SetAutoPageBreak(TRUE, $marginBottom);
						if (isset($marginTop))
							$pdf->SetMargins(15, $marginTop, 15);
						$pdf->AddPage();
					} elseif (trim($pdfMatch[1]) == 'setMargins') {

						$marginTop = 20;
						$marginBottom = 20;
						if (trim($pdfMatch[2]) != '') {
							$margin = explode(',', $pdfMatch[2]);
							if (isset($margin[0])) {
								$pdf->setY($margin[0]);
								$marginTop = $margin[0];
							}
							if (isset($margin[1]))
								$marginBottom = $margin[1];
						}
					} elseif (trim($pdfMatch[1]) == 'setListPadding') {
						if (trim($pdfMatch[2]) != '') {
							$pdf->setListIndentWidth($pdfMatch[2]);
						}
					} elseif (trim($pdfMatch[1]) == 'lastPage') {
						$pdf->SetAutoPageBreak(TRUE, 0);
						if (trim($pdfMatch[2]) != '') {
							$margins = explode(',', $pdfMatch[2]);
							if (isset($margins[0]) and $pdf->getY() > $margins[0]) {
								$pdf->AddPage();
								if (isset($margins[1]))
									$pdf->setY($margins[1]);
							}
						}
						insertImages('annex-images');
					}
				} elseif (trim($part) != '') {
					ob_start();
					if (isset($data['pdf_css_js']))
						echo $this->fix_spaces($data['pdf_css_js']);
					echo trim($part);
					if (preg_match('/[\p{Old_Turkic}]/u', $part))
						$pdf->SetFont('freeserif');
					$partContent = ob_get_contents();
					$pdf->writeHTML($partContent);
					ob_end_clean();
					//	echo $partContent."\n\n\n";
					$curY = $pdf->getY();
					$curX = $pdf->getX();
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
			global $amdb;
			if ($tmpl != null and count($data) > 0) {
				$theFormHeaders = array();
				$template = $amdb->get_row("SELECT * FROM hqc_elements_templates WHERE assid='$tmpl'");
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
