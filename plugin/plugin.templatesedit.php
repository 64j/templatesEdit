<?php
/*
 * Управление шаблонами
 * templatesEdit
 * plugin
 */

defined('IN_MANAGER_MODE') or die();

function ContentFieldSplit() {
	return '<tr><td colspan="2" style="height:0px"><div class="split"></div></td></tr>';
}
function renderContentField($data) {
	global $modx, $_style, $_lang, $content, $site_name, $use_editor, $which_editor, $editor, $replace_richtexteditor, $search_default, $search_default, $cache_default;
	$field       = '';
	$name        = $data['field']['name'];
	list($item_title, $item_description) = explode('||||', $data['field']['title']);
	$fieldDescription = (!empty($item_description)) ? '<br><span class="comment">' . $item_description . '</span>' : '';
	$title      = '<span class="warning">' . $item_title . '</span>' . $fieldDescription;
	$help        = $data['field']['help'] ? ' <img src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['field']['help']) . '" style="cursor:help;" />' : '';
	$hide        = $data['field']['hide'] ? 'none' : 'table-row';
	$title_width = 150;
	$input_width = '';
	$mx_can_pub  = $modx->hasPermission('publish_document') ? '' : 'disabled="disabled" ';
	if (isset($data['tv'])) {
		$help = $data['tv']['help'] ? ' <img src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['tv']['help']) . '" style="cursor:help;" />' : '';
		if (array_key_exists('tv' . $data['tv']['id'], $_POST)) {
			if ($data['tv']['type'] == 'listbox-multiple') {
				$tvPBV = implode('||', $_POST['tv' . $data['tv']['id']]);
			} else {
				$tvPBV = $_POST['tv' . $data['tv']['id']];
			}
		} else {
			$tvPBV = $data['tv']['value'];
		}
		list($item_title, $item_description) = explode('||||', $data['tv']['title']);
		$tvDescription = (!empty($item_description)) ? '<br><span class="comment">' . $item_description . '</span>' : '';
		//$tvInherited   = (substr($tvPBV, 0, 8) == '@INHERIT') ? '<span class="comment inherited">(' . $_lang['tmplvars_inherited'] . ')</span>' : '';
		$title         = '<span class="warning">' . $item_title . '</span>' . $tvDescription . $tvInherited;
		$field .= '<tr style="display: ' . $hide . '">';
		if ($data['tv']['title']) {
			$field .= '<td valign="top" width="' . $title_width . '">' . $title . '</td>';
		}
		$field .= '<td valign="top" style="position:relative;"' . (!$data['tv']['title'] ? ' colspan="2"' : '') . '>' . renderFormElement($data['tv']['type'], $data['tv']['id'], $data['tv']['default_text'], $data['tv']['elements'], $tvPBV, '', $data['tv']) . $help . '</td></tr>';
	}
	if (isset($data['field'])) {
		switch ($name) {
			case 'weblink':
				if ($content['type'] == 'reference' || $_REQUEST['a'] == '72') {
					$field .= '<tr style="display: ' . $hide . '">
				<td>' . $title . ' <img name="llock" src="' . $_style["tree_folder"] . '" alt="tree_folder" onClick="enableLinkSelection(!allowLinkSelection);" style="cursor:pointer;" /></td>
				<td><input name="ta" type="text" maxlength="255" value="' . (!empty($content['content']) ? stripslashes($content['content']) : "http://") . '" class="inputBox" onChange="documentDirty=true;" />' . $help . '</td></tr>';
				}
				break;
			case 'introtext':
				$field .= '<tr style="display: ' . $hide . '">';
				if ($data['field']['title']) {
					$field .= '<td valign="top" width="' . $title_width . '">' . $title . '</td>';
				}
				$field .= '
			<td valign="top"' . (!$data['field']['title'] ? ' colspan="2"' : '') . '><textarea name="introtext" class="inputBox" rows="3" cols="" onChange="documentDirty=true;">' . $modx->htmlspecialchars(stripslashes($content['introtext'])) . '</textarea>
			' . $help . '
			</td></tr>';
				break;
			case 'template':
				$field .= '<tr style="display: ' . $hide . '">
			<td>' . $title . '</td>
			<td><select id="template" name="template" class="inputBox" onChange="templateWarning();" style="width:308px">
			<option value="0">(blank)</option>';
				$rs              = $modx->db->select("t.templatename, t.id, c.category", $modx->getFullTableName('site_templates') . " AS t LEFT JOIN " . $modx->getFullTableName('categories') . " AS c ON t.category = c.id", '', 'c.category, t.templatename ASC');
				$currentCategory = '';
				while ($row = $modx->db->getRow($rs)) {
					$thisCategory = $row['category'];
					if ($thisCategory == null) {
						$thisCategory = $_lang["no_category"];
					}
					if ($thisCategory != $currentCategory) {
						if ($closeOptGroup) {
							$field .= "</optgroup>";
						}
						$field .= "<optgroup label=\"$thisCategory\">";
						$closeOptGroup = true;
					}
					if (isset($_REQUEST['newtemplate'])) {
						$selectedtext = $row['id'] == $_REQUEST['newtemplate'] ? ' selected="selected"' : '';
					} else {
						if (isset($content['template'])) {
							$selectedtext = $row['id'] == $content['template'] ? ' selected="selected"' : '';
						} else {
							$default_template    = getDefaultTemplate();
							$selectedtext        = $row['id'] == $default_template ? ' selected="selected"' : '';
							$content['template'] = $default_template;
						}
					}
					$field .= '<option value="' . $row['id'] . '"' . $selectedtext . '>' . $row['templatename'] . "</option>";
					$currentCategory = $thisCategory;
				}
				if ($thisCategory != '') {
					$field .= "</optgroup>";
				}
				$field .= '	
			</select>
			' . $help . '
			</td></tr>';
				break;
			case 'menuindex':
				$field .= '<tr style="display: ' . $hide . '">
			<td width="' . $title_width . '">' . $title . '</td>
			<td><input name="menuindex" type="text" maxlength="6" value="' . $content['menuindex'] . '" class="inputBox" style="width:30px;" onChange="documentDirty=true;" />
				<input type="button" value="&lt;" onClick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+\'\')-1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
				<input type="button" value="&gt;" onClick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+\'\')+1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
				<img src="' . $_style["icons_tooltip_over"] . '" alt="' . $_lang['resource_opt_menu_index_help'] . '" style="cursor:help;" />
				<span class="warning">' . $_lang['resource_opt_show_menu'] . '</span>
				<input name="hidemenucheck" type="checkbox" class="checkbox" ' . ($content['hidemenu'] != 1 ? 'checked="checked"' : '') . ' onClick="changestate(document.mutate.hidemenu);" />
				<input type="hidden" name="hidemenu" class="hidden" value="' . ($content['hidemenu'] == 1 ? 1 : 0) . '" />
				<img src="' . $_style["icons_tooltip_over"] . '" alt="' . $_lang['resource_opt_show_menu_help'] . '" style="cursor:help;" />
			</td></tr>';
				break;
			case 'parent':
				$field .= '<tr style="display: ' . $hide . '">
			<td valign="top"><span class="warning">' . $title . '</span></td>
			<td valign="top">';
				$parentlookup = false;
				if (isset($_REQUEST['id'])) {
					if ($content['parent'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $content['parent'];
					}
				} elseif (isset($_REQUEST['pid'])) {
					if ($_REQUEST['pid'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $_REQUEST['pid'];
					}
				} elseif (isset($_POST['parent'])) {
					if ($_POST['parent'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $_POST['parent'];
					}
				} else {
					$parentname        = $site_name;
					$content['parent'] = 0;
				}
				if ($parentlookup !== false && is_numeric($parentlookup)) {
					$rs         = $modx->db->select('pagetitle', $modx->getFullTableName('site_content'), "id='{$parentlookup}'");
					$parentname = $modx->db->getValue($rs);
					if (!$parentname) {
						$modx->webAlertAndQuit($_lang["error_no_parent"]);
					}
				}
				$field .= '
					<img alt="tree_folder" name="plock" src="' . $_style["tree_folder"] . '" onClick="enableParentSelection(!allowParentSelection);" style="cursor:pointer;" />
					<b><span id="parentName">' . (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']) . ' (' . $parentname . ')</span></b>
					' . $help . '
					<input type="hidden" name="parent" value="' . (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']) . '" onChange="documentDirty=true;" />
				</td></tr>';
				break;
			case 'content':
				if ($content['type'] == 'document' || $_REQUEST['a'] == '4') {
					$field .= '<tr style="display: ' . $hide . '"><td colspan="2">';
					if (($content['richtext'] == 1 || $_REQUEST['a'] == '4') && $use_editor == 1) {
						$field .= '<textarea id="ta" name="ta" cols="" rows="" style="width:100%; height: 400px;" onChange="documentDirty=true;">' . $modx->htmlspecialchars($content['content']) . '</textarea>';
/*						$field .='
						<span class="warning">' . $_lang['which_editor_title'] . '</span>
						<select id="which_editor" name="which_editor" onChange="changeRTE();">
						<option value="none">' . $_lang['none'] . '</option>';
						$evtOut = $modx->invokeEvent("OnRichTextEditorRegister");
						if (is_array($evtOut)) {
							for ($i = 0; $i < count($evtOut); $i++) {
								$editor = $evtOut[$i];
								$field .= '<option value="' . $editor . '"' . ($which_editor == $editor ? ' selected="selected"' : '') . '>' . $editor . "</option>";
							}
						}
						$field .= '</select>';*/
						if (is_array($replace_richtexteditor)) {
							$replace_richtexteditor = array_merge($replace_richtexteditor, array(
								'ta'
							));
						} else {
							$replace_richtexteditor = array(
								'ta'
							);
						}
					} else {
						$field .= '<div style="width:100%"><textarea class="phptextarea" id="ta" name="ta" style="width:100%; height: 400px;" onchange="documentDirty=true;">' . $modx->htmlspecialchars($content['content']) . '</textarea></div>';
					}
					$field .= '</td></tr>';
				}
				break;
			case 'published':
				$field .= '<tr style="display: ' . $hide . '">
                <td width="' . $title_width . '">' . $title . '</td>
                <td><input ' . $mx_can_pub . 'name="publishedcheck" type="checkbox" class="checkbox" ' . ((isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && $publish_default == 1) ? "checked" : '') . ' onClick="changestate(document.mutate.published);" />
                <input type="hidden" name="published" value="' . ((isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && $publish_default == 1) ? 1 : 0) . '" />
                ' . $help . '
				</td></tr>';
				break;
			case 'pub_date':
			case 'unpub_date':
				$field .= '<tr style="display: ' . $hide . '">
                <td><span class="warning">' . $title . '</td>
                <td><input id="' . $name . '" ' . $mx_can_pub . 'name="' . $name . '" class="DatePicker" value="' . ($content[$name] == "0" || !isset($content[$name]) ? '' : $modx->toDateFormat($content[$name])) . '" onBlur="documentDirty=true;" />
                <a href="javascript:void(0);" onClick="javascript:document.mutate.' . $name . '.value=\'\'; return true;" onMouseOver="window.status=\'' . $_lang['remove_date'] . '\'; return true;" onMouseOut="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand;">
                <img src="' . $_style["icons_cal_nodate"] . '" width="16" height="16" border="0" alt="' . $_lang['remove_date'] . '" /></a>
                ' . $help . '
				</td></tr>
				<tr style="display: ' . $hide . '">
					<td></td>
					<td style="color: #555;font-size:10px"><em>' . $modx->config['datetime_format'] . ' HH:MM:SS</em></td>
				</tr>';
				break;
			case 'richtext':
			case 'donthit':
			case 'searchable':
			case 'cacheable':
			case 'syncsite':
			case 'alias_visible':
			case 'isfolder':
				if ($name == 'richtext') {
					$value   = $content['richtext'] == 0 && $_REQUEST['a'] == '27' ? 0 : 1;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'donthit') {
					$value   = ($content['donthit'] == 0) ? 0 : 1;
					$checked = !$value ? "checked" : '';
				} elseif ($name == 'searchable') {
					$value   = (isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && $search_default == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'cacheable') {
					$value   = (isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && $cache_default == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'syncsite') {
					$value   = '1';
					$checked = $value ? "checked" : '';
				} elseif ($name == 'alias_visible') {
					$value   = (!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'isfolder') {
					$value   = ($content['isfolder'] == 1 || $_REQUEST['a'] == '85' || $_REQUEST['isfolder'] == '1') ? 1 : 0;
					$checked = $value ? "checked" : '';
				} else {
					$value   = ($content[$name] == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				}
				$field .= '<tr style="display: ' . $hide . '">
                <td width="' . $title_width . '">' . $title . '</td>
                <td><input name="' . $name . 'check" type="checkbox" class="checkbox" ' . $checked . ' onClick="changestate(document.mutate.' . $name . ');" />
                <input type="hidden" name="' . $name . '" value="' . $value . '" onChange="documentDirty=true;" />
                ' . $help . '
				</td></tr>';
				break;
			case 'type':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<tr style="display: ' . $hide . '">
				    <td width="' . $title_width . '">' . $title . '</td>
					<td><select name="type" class="inputBox" onChange="documentDirty=true;" style="width:200px">
					<option value="document"' . (($content['type'] == "document" || $_REQUEST['a'] == '85' || $_REQUEST['a'] == '4') ? ' selected="selected"' : "") . '>' . $_lang["resource_type_webpage"] . '</option>
					<option value="reference"' . (($content['type'] == "reference" || $_REQUEST['a'] == '72') ? ' selected="selected"' : "") . '>' . $_lang["resource_type_weblink"] . '</option>
					</select>
					' . $help . '
					</td></tr>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="type" value="document" />';
					} else {
						$field .= '<input type="hidden" name="type" value="reference" />';
					}
				}
				break;
			case 'contentType':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<tr style="display: ' . $hide . '">
					<td width="' . $title_width . '">' . $title . '</td>
					<td><select name="contentType" class="inputBox" onChange="documentDirty=true;" style="width:200px">';
					if (!$content['contentType'])
						$content['contentType'] = 'text/html';
					$custom_contenttype = (isset($custom_contenttype) ? $custom_contenttype : "text/html,text/plain,text/xml");
					$ct                 = explode(",", $custom_contenttype);
					for ($i = 0; $i < count($ct); $i++) {
						$field .= '<option value="' . $ct[$i] . '"' . ($content['contentType'] == $ct[$i] ? ' selected="selected"' : '') . '>' . $ct[$i] . "</option>";
					}
					$field .= '</select>
					' . $help . '
					</td></tr>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="contentType" value="' . (isset($content['contentType']) ? $content['contentType'] : "text/html") . '" />';
					} else {
						$field .= '<input type="hidden" name="contentType" value="text/html" />';
					}
				}
				break;
			case 'content_dispo':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<tr style="display: ' . $hide . '">
				        <td width="' . $title_width . '">' . $title . '</td>
						<td><select name="content_dispo" size="1" onChange="documentDirty=true;" style="width:200px">
						<option value="0"' . (!$content['content_dispo'] ? ' selected="selected"' : '') . '>' . $_lang['inline'] . '</option>
						<option value="1"' . ($content['content_dispo'] == 1 ? ' selected="selected"' : '') . '>' . $_lang['attachment'] . '</option>
						</select>
						' . $help . '
						</td></tr>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="content_dispo" value="' . (isset($content['content_dispo']) ? $content['content_dispo'] : '0') . '" />';
					}
				}
				break;
			default:
				$field .= '<tr style="display: ' . $hide . '">';
				$field .= $data['field']['title'] ? '<td width="' . $title_width . '">' . $title . '</td><td>' : '<td colspan="2">';
				$field .= '<input name="' . $name . '" type="text" maxlength="255" value="' . $modx->htmlspecialchars(stripslashes($content[$name])) . '" class="inputBox" onChange="documentDirty=true;" spellcheck="true" />
				' . $help . '
				</td></tr>';
		}
	}
	if($hide == 'table-row' && !empty($field)) {
		$field .= ContentFieldSplit();
	}	
	return $field;
}

function renderContentField_edit($data, $editableTemplate) {
	global $modx, $_style, $_lang, $content, $site_name, $use_editor, $which_editor, $editor, $replace_richtexteditor, $search_default, $search_default, $cache_default;
	$field      = '';
	$name       = $data['field']['name'];
	list($item_title, $item_description) = explode('||||', $data['field']['title']);
	$fieldDescription = (!empty($item_description)) ? '<span class="comment">' . $item_description . '</span>' : '';
	$title      = '<span class="warning">' . $item_title . '</span>' . $fieldDescription;
	$help       = $data['field']['help'] ? ' <img id="item-help-' . $name . '" src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['field']['help']) . '" style="cursor:help;" />' : '';
	$hide       = $data['field']['hide'] && !$editableTemplate ? ' style="display: none"' : '';
	$itemClass  = 'pane-item pane-item-field' . ($editableTemplate && $data['field']['hide'] ? ' pane-item-hidden' : '');
	$itemAttr   = $editableTemplate ? 'class="' . $itemClass . '"' . $hide . ' data-item-name="' . $name . '"' : ' class="' . $itemClass . '"' . $hide;
	$mx_can_pub = $modx->hasPermission('publish_document') ? '' : 'disabled="disabled" ';
	if (isset($data['tv'])) {
		$name = 'tv' . $data['tv']['id'];
		if (array_key_exists('tv' . $data['tv']['id'], $_POST)) {
			if ($data['tv']['type'] == 'listbox-multiple') {
				$tvPBV = implode('||', $_POST['tv' . $data['tv']['id']]);
			} else {
				$tvPBV = $_POST['tv' . $data['tv']['id']];
			}
		} else {
			$tvPBV = $data['tv']['value'];
		}
		list($item_title, $item_description) = explode('||||', $data['tv']['title']);
		$tvDescription = (!empty($item_description)) ? '<span class="comment">' . $item_description . '</span>' : '';
		//$tvInherited   = (substr($tvPBV, 0, 8) == '@INHERIT') ? '<span class="comment inherited">(' . $_lang['tmplvars_inherited'] . ')</span>' : '';
		$title         = '<span class="warning">' . $item_title . '</span>' . $tvDescription . $tvInherited;
		$help          = $data['tv']['help'] ? ' <img id="item-help-' . $name . '" src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['tv']['help']) . '" style="cursor:help;" />' : '';
		$hide          = $data['tv']['hide'] && !$editableTemplate ? ' style="display: none"' : '';
		$itemClass     = 'pane-item pane-item-tv' . ($editableTemplate && $data['tv']['hide'] ? ' pane-item-hidden' : '');
		$itemAttr   = $editableTemplate ? 'class="' . $itemClass . '" style="display: ' . $hide . '" data-item-name="' . $name . '"' : ' class="' . $itemClass . '"' . $hide;
		$field .= '<div ' . $itemAttr . '>';
		if ($data['tv']['title']) {
			$field .= '<div class="pane-item-title">' . $title . '</div><div class="pane-item-f">';
		} else {
			$field .= '<div class="pane-item-f pane-item-w">';
		}
		$field .= renderFormElement($data['tv']['type'], $data['tv']['id'], $data['tv']['default_text'], $data['tv']['elements'], $tvPBV, '', $data['tv']) . '
		' . $help . '
		</div></div>';
	}
	if (isset($data['field'])) {
		switch ($name) {
			case 'weblink':
				if ($content['type'] == 'reference' || $_REQUEST['a'] == '72') {
					$field .= '<div ' . $itemAttr . '>
				<div class="pane-item-title">' . $title . ' <img name="llock" src="' . $_style["tree_folder"] . '" alt="tree_folder" onClick="enableLinkSelection(!allowLinkSelection);" style="cursor:pointer;" /></div>
				<div class="pane-item-f"><input name="ta" type="text" maxlength="255" value="' . (!empty($content['content']) ? stripslashes($content['content']) : "http://") . '" class="inputBox" onChange="documentDirty=true;" />
				' . $help . '</div>
				</div>';
				}
				break;
			case 'introtext':
				$field .= '<div ' . $itemAttr . '>';
				if ($data['field']['title']) {
					$field .= '<div class="pane-item-title">' . $title . '</div><div class="pane-item-f">';
				} else {
					$field .= '<div class="pane-item-f pane-item-w">';
				}
				$field .= '<textarea name="introtext" class="inputBox" rows="3" cols="" onChange="documentDirty=true;">' . $modx->htmlspecialchars(stripslashes($content['introtext'])) . '</textarea>
				' . $help . '
				</div></div>';
				break;
			case 'template':
				$field .= '<div ' . $itemAttr . '>
				<div class="pane-item-title">' . $title . '</div>
				<div class="pane-item-f"><select id="template" name="template" class="inputBox" onChange="templateWarning();" style="width:308px">
				<option value="0">(blank)</option>';
				$rs              = $modx->db->select("t.templatename, t.id, c.category", $modx->getFullTableName('site_templates') . " AS t LEFT JOIN " . $modx->getFullTableName('categories') . " AS c ON t.category = c.id", '', 'c.category, t.templatename ASC');
				$currentCategory = '';
				while ($row = $modx->db->getRow($rs)) {
					$thisCategory = $row['category'];
					if ($thisCategory == null) {
						$thisCategory = $_lang["no_category"];
					}
					if ($thisCategory != $currentCategory) {
						if ($closeOptGroup) {
							$field .= "</optgroup>";
						}
						$field .= "<optgroup label=\"$thisCategory\">";
						$closeOptGroup = true;
					}
					if (isset($_REQUEST['newtemplate'])) {
						$selectedtext = $row['id'] == $_REQUEST['newtemplate'] ? ' selected="selected"' : '';
					} else {
						if (isset($content['template'])) {
							$selectedtext = $row['id'] == $content['template'] ? ' selected="selected"' : '';
						} else {
							$default_template    = getDefaultTemplate();
							$selectedtext        = $row['id'] == $default_template ? ' selected="selected"' : '';
							$content['template'] = $default_template;
						}
					}
					$field .= '<option value="' . $row['id'] . '"' . $selectedtext . '>' . $row['templatename'] . "</option>";
					$currentCategory = $thisCategory;
				}
				if ($thisCategory != '') {
					$field .= "</optgroup>";
				}
				$field .= '</select>
				' . $help . '
				</div></div>';
				break;
			case 'menuindex':
				$field .= '<div ' . $itemAttr . '>
				<div class="pane-item-title">' . $title . '</div>
				<div class="pane-item-f"><input name="menuindex" type="text" maxlength="6" value="' . $content['menuindex'] . '" class="inputBox" style="width:30px;" onChange="documentDirty=true;" />
				<input type="button" value="&lt;" onClick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+\'\')-1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
				<input type="button" value="&gt;" onClick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+\'\')+1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
				<img src="' . $_style["icons_tooltip_over"] . '" alt="' . $_lang['resource_opt_menu_index_help'] . '" style="cursor:help;" />
				<span class="warning">' . $_lang['resource_opt_show_menu'] . '</span>
				<input name="hidemenucheck" type="checkbox" class="checkbox" ' . ($content['hidemenu'] != 1 ? 'checked="checked"' : '') . ' onClick="changestate(document.mutate.hidemenu);" />
				<input type="hidden" name="hidemenu" class="hidden" value="' . ($content['hidemenu'] == 1 ? 1 : 0) . '" />
				<img src="' . $_style["icons_tooltip_over"] . '" alt="' . $_lang['resource_opt_show_menu_help'] . '" style="cursor:help;" /></div>
				</div>';
				break;
			case 'parent':
				$field .= '<div ' . $itemAttr . '>
				<div class="pane-item-title">' . $title . '</div>';
				$parentlookup = false;
				if (isset($_REQUEST['id'])) {
					if ($content['parent'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $content['parent'];
					}
				} elseif (isset($_REQUEST['pid'])) {
					if ($_REQUEST['pid'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $_REQUEST['pid'];
					}
				} elseif (isset($_POST['parent'])) {
					if ($_POST['parent'] == 0) {
						$parentname = $site_name;
					} else {
						$parentlookup = $_POST['parent'];
					}
				} else {
					$parentname        = $site_name;
					$content['parent'] = 0;
				}
				if ($parentlookup !== false && is_numeric($parentlookup)) {
					$rs         = $modx->db->select('pagetitle', $modx->getFullTableName('site_content'), "id='{$parentlookup}'");
					$parentname = $modx->db->getValue($rs);
					if (!$parentname) {
						$modx->webAlertAndQuit($_lang["error_no_parent"]);
					}
				}
				$field .= '
					<div class="pane-item-f"><img alt="tree_folder" name="plock" src="' . $_style["tree_folder"] . '" onClick="enableParentSelection(!allowParentSelection);" style="cursor:pointer;" />
					<b><span id="parentName">' . (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']) . ' (' . $parentname . ')</span></b>
					' . $help . '
					<input type="hidden" name="parent" value="' . (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']) . '" onChange="documentDirty=true;" /></div>
				</div>';
				break;
			case 'content':
				if ($content['type'] == 'document' || $_REQUEST['a'] == '4') {
					$field .= '<div ' . $itemAttr . '>';
					if (($content['richtext'] == 1 || $_REQUEST['a'] == '4') && $use_editor == 1) {
						$field .= '<div class="pane-item-f pane-item-w"><textarea id="ta" name="ta" cols="" rows="" style="width:100%; height: 400px;" onChange="documentDirty=true;">' . $modx->htmlspecialchars($content['content']) . '</textarea></div>';
						/*						$field .='
						<span class="warning">' . $_lang['which_editor_title'] . '</span>
						<select id="which_editor" name="which_editor" onChange="changeRTE();">
						<option value="none">' . $_lang['none'] . '</option>';
						$evtOut = $modx->invokeEvent("OnRichTextEditorRegister");
						if (is_array($evtOut)) {
						for ($i = 0; $i < count($evtOut); $i++) {
						$editor = $evtOut[$i];
						$field .= '<option value="' . $editor . '"' . ($which_editor == $editor ? ' selected="selected"' : '') . '>' . $editor . "</option>";
						}
						}
						$field .= '</select>';*/
						if (is_array($replace_richtexteditor)) {
							$replace_richtexteditor = array_merge($replace_richtexteditor, array(
								'ta'
							));
						} else {
							$replace_richtexteditor = array(
								'ta'
							);
						}
					} else {
						$field .= '<div class="pane-item-f pane-item-w" style="width:100%"><textarea class="phptextarea" id="ta" name="ta" style="width:100%; height: 400px;" onchange="documentDirty=true;">' . $modx->htmlspecialchars($content['content']) . '</textarea></div>';
					}
					$field .= '</div>';
				}
				break;
			case 'published':
				$field .= '<div ' . $itemAttr . '>
                <div class="pane-item-title">' . $title . '</div>
                <div class="pane-item-f"><input ' . $mx_can_pub . 'name="publishedcheck" type="checkbox" class="checkbox" ' . ((isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && $publish_default == 1) ? "checked" : '') . ' onClick="changestate(document.mutate.published);" />
                <input type="hidden" name="published" value="' . ((isset($content['published']) && $content['published'] == 1) || (!isset($content['published']) && $publish_default == 1) ? 1 : 0) . '" />
				' . $help . '
				</div></div>';
				break;
			case 'pub_date':
			case 'unpub_date':
				$field .= '<div ' . $itemAttr . '>
                <div class="pane-item-title">' . $title . '</div>
                <div class="pane-item-f"><input id="' . $name . '" ' . $mx_can_pub . 'name="' . $name . '" class="DatePicker" value="' . ($content[$name] == "0" || !isset($content[$name]) ? '' : $modx->toDateFormat($content[$name])) . '" onBlur="documentDirty=true;" />
                <a href="javascript:void(0);" onClick="javascript:document.mutate.' . $name . '.value=\'\'; return true;" onMouseOver="window.status=\'' . $_lang['remove_date'] . '\'; return true;" onMouseOut="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand;">
                <img src="' . $_style["icons_cal_nodate"] . '" width="16" height="16" border="0" alt="' . $_lang['remove_date'] . '" /></a><br>
				<em style="color: #555;font-size:10px">' . $modx->config['datetime_format'] . ' HH:MM:SS</em>
                ' . $help . '
				</div></div>';
				break;
			case 'richtext':
			case 'donthit':
			case 'searchable':
			case 'cacheable':
			case 'syncsite':
			case 'alias_visible':
			case 'isfolder':
				if ($name == 'richtext') {
					$value   = $content['richtext'] == 0 && $_REQUEST['a'] == '27' ? 0 : 1;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'donthit') {
					$value   = ($content['donthit'] == 0) ? 0 : 1;
					$checked = !$value ? "checked" : '';
				} elseif ($name == 'searchable') {
					$value   = (isset($content['searchable']) && $content['searchable'] == 1) || (!isset($content['searchable']) && $search_default == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'cacheable') {
					$value   = (isset($content['cacheable']) && $content['cacheable'] == 1) || (!isset($content['cacheable']) && $cache_default == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'syncsite') {
					$value   = '1';
					$checked = $value ? "checked" : '';
				} elseif ($name == 'alias_visible') {
					$value   = (!isset($content['alias_visible']) || $content['alias_visible'] == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				} elseif ($name == 'isfolder') {
					$value   = ($content['isfolder'] == 1 || $_REQUEST['a'] == '85' || $_REQUEST['isfolder'] == '1') ? 1 : 0;
					$checked = $value ? "checked" : '';
				} else {
					$value   = ($content[$name] == 1) ? 1 : 0;
					$checked = $value ? "checked" : '';
				}
				$field .= '<div ' . $itemAttr . '>
				<div class="pane-item-title">' . $title . '</div>
                <div class="pane-item-f"><input name="' . $name . 'check" type="checkbox" class="checkbox" ' . $checked . ' onClick="changestate(document.mutate.' . $name . ');" />
                <input type="hidden" name="' . $name . '" value="' . $value . '" onChange="documentDirty=true;" />
                ' . $help . '
				</div></div>';
				break;
			case 'type':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<div ' . $itemAttr . '>
				    <div class="pane-item-title">' . $title . '</div>
           		<div class="pane-item-f"><select name="type" class="inputBox" onChange="documentDirty=true;" style="width:200px">
                <option value="document"' . (($content['type'] == "document" || $_REQUEST['a'] == '85' || $_REQUEST['a'] == '4') ? ' selected="selected"' : "") . '>' . $_lang["resource_type_webpage"] . '</option>
                <option value="reference"' . (($content['type'] == "reference" || $_REQUEST['a'] == '72') ? ' selected="selected"' : "") . '>' . $_lang["resource_type_weblink"] . '</option>
                </select>
                ' . $help . '</div></div>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="type" value="document" />';
					} else {
						$field .= '<input type="hidden" name="type" value="reference" />';
					}
				}
				break;
			case 'contentType':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<div ' . $itemAttr . '>
					<div class="pane-item-title">' . $title . '</div>
					<div class="pane-item-f"><select name="contentType" class="inputBox" onChange="documentDirty=true;" style="width:200px">';
					if (!$content['contentType'])
						$content['contentType'] = 'text/html';
					$custom_contenttype = (isset($custom_contenttype) ? $custom_contenttype : "text/html,text/plain,text/xml");
					$ct                 = explode(",", $custom_contenttype);
					for ($i = 0; $i < count($ct); $i++) {
						$field .= '<option value="' . $ct[$i] . '"' . ($content['contentType'] == $ct[$i] ? ' selected="selected"' : '') . '>' . $ct[$i] . "</option>";
					}
					$field .= '</select>' . $help . '</div></div>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="contentType" value="' . (isset($content['contentType']) ? $content['contentType'] : "text/html") . '" />';
					} else {
						$field .= '<input type="hidden" name="contentType" value="text/html" />';
					}
				}
				break;
			case 'content_dispo':
				if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
					$field .= '<div ' . $itemAttr . '>
					<div class="pane-item-title">' . $title . '</div>
                	<div class="pane-item-f"><select name="content_dispo" size="1" onChange="documentDirty=true;" style="width:200px">
                    <option value="0"' . (!$content['content_dispo'] ? ' selected="selected"' : '') . '>' . $_lang['inline'] . '</option>
                    <option value="1"' . ($content['content_dispo'] == 1 ? ' selected="selected"' : '') . '>' . $_lang['attachment'] . '</option>
					</select>
					' . $help . '
					</div></div>';
				} else {
					if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
						$field .= '<input type="hidden" name="content_dispo" value="' . (isset($content['content_dispo']) ? $content['content_dispo'] : '0') . '" />';
					}
				}
				break;
			default:
				$field .= '<div ' . $itemAttr . '>';
				if($data['field']['title']) {
					$field .= '<div class="pane-item-title">' . $title . '</div><div class="pane-item-f">';
				} else {
					$field .= '<div class="pane-item-f pane-item-w">';
				}
				$field .= '<input name="' . $name . '" type="text" maxlength="255" value="' . $modx->htmlspecialchars(stripslashes($content[$name])) . '" class="inputBox" onChange="documentDirty=true;" spellcheck="true" />
				' . $help . '
				</div></div>';
		}
	}
	return $field;
}
// end Generate Fields


global $modx, $content, $docgrp;
$e =& $modx->Event;
$editableTemplate = $editableTemplate == 'true' ? true : false;
$altRenderTemplate = $altRenderTemplate == 'true' ? true : false;
$loadJquery = $loadJquery == 'true' ? true : false;


if ($e->name == 'OnTempFormSave') {
	if ($mode == 'new') {
		require_once MODX_BASE_PATH . "assets/modules/templatesEdit/classes/class.templatesedit.php";
		$tplEdit = new templatesEdit($modx);
		$modx->db->insert(array(
			'data' => $tplEdit->setTemplateDefault(),
			'templateid' => $id
		), $modx->getFullTableName('site_templates_settings'));
	}
}

if ($e->name == 'OnTempFormDelete') {
	$modx->db->delete($modx->getFullTableName('site_templates_settings'), 'templateid=' . $id);
}

if ($e->name == 'OnTVFormSave') {
	require_once MODX_BASE_PATH . "assets/modules/templatesEdit/classes/class.templatesedit.php";
	$tplEdit = new templatesEdit($modx);
	$rs = $modx->db->makeArray($modx->db->select("data, templateid", $modx->getFullTableName('site_templates_settings')));
	foreach ($rs as $v) {
		$check_tpl = $modx->db->getValue($modx->db->select("templateid", $modx->getFullTableName('site_tmplvar_templates'), "tmplvarid=" . $id . " AND templateid=" . $v['templateid']));
		$data      = json_decode($v['data'], true);
		if (!$check_tpl['templateid']) {
			foreach ($data as $key => $val) {
				unset($data[$key]['fields']['tv' . $id]);
			}
			$data = $tplEdit->json_encode_cyr($data);
			$modx->db->update(array(
				'data' => mysql_real_escape_string($data)
			), $modx->getFullTableName('site_templates_settings'), "templateid=" . $v['templateid']);
		} else {
			$tv = $modx->db->getRow($modx->db->select("name, caption, description", $modx->getFullTableName('site_tmplvars'), "id=" . $id));
			if (!$tplEdit->recursive_array_search($tv['name'], $data)) {
				$tv_arr['tv' . $id]        = array(
					'tv' => array(
						'title' => $tv['caption'],
						'help' => $tv['description'],
						'name' => $tv['name'],
						'roles' => '',
						'hide' => ''
					)
				);
				$data['General']['fields'] = array_merge($data['General']['fields'], $tv_arr);
				$data                      = $tplEdit->json_encode_cyr($data);
				$modx->db->update(array(
					'data' => mysql_real_escape_string($data)
				), $modx->getFullTableName('site_templates_settings'), "templateid=" . $v['templateid']);
			}
		}
	}
}

if ($e->name == 'OnTVFormDelete') {
	$rs = $modx->db->makeArray($modx->db->select("data, templateid", $modx->getFullTableName('site_templates_settings')));
	foreach ($rs as $v) {
		$data = json_decode($v['data'], true);
		foreach ($data as $key => $val) {
			unset($data[$key]['fields']['tv' . $id]);
		}
		$data = $tplEdit->json_encode_cyr($data);
		$modx->db->update(array(
			'data' => mysql_real_escape_string($data)
		), $modx->getFullTableName('site_templates_settings'), "templateid=" . $v['templateid']);
	}
}

if ($e->name == 'OnDocFormTemplateRender') {
	if (isset($_REQUEST['newtemplate'])) {
		$template = $_REQUEST['newtemplate'];
	} else {
		if (isset($content['template'])) {
			$template = $content['template'];
		} else {
			$template = getDefaultTemplate();
		}
	}
	$rs                    = $modx->db->select("data", $modx->getFullTableName('site_templates_settings'), "templateid='" . $template . "'");
	$mutate_content_fields = json_decode($modx->db->getValue($rs), true);
	
	// Variables		
	if (($content['type'] == 'document' || $_REQUEST['a'] == '4') || ($content['type'] == 'reference' || $_REQUEST['a'] == 72)) {
		$rs    = $modx->db->select("
				DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value", "" . $modx->getFullTableName('site_tmplvars') . " AS tv
					 INNER JOIN " . $modx->getFullTableName('site_tmplvar_templates') . " AS tvtpl ON tvtpl.tmplvarid = tv.id
					 LEFT JOIN " . $modx->getFullTableName('site_tmplvar_contentvalues') . " AS tvc ON tvc.tmplvarid=tv.id AND tvc.contentid='" . $id . "'
					 LEFT JOIN " . $modx->getFullTableName('site_tmplvar_access') . " AS tva ON tva.tmplvarid=tv.id", "tvtpl.templateid='" . $template . "' 
					 AND (1='" . $_SESSION['mgrRole'] . "' 
					 OR ISNULL(tva.documentgroup)" . (!$docgrp ? '' : " OR tva.documentgroup IN (" . $docgrp . ")") . ")", 'tvtpl.rank, tv.rank, tv.id');
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			require_once(MODX_MANAGER_PATH . 'includes/tmplvars.inc.php');
			require_once(MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php');
			$i = 0;
			while ($row = $modx->db->getRow($rs)) {
				// Go through and display all Template Variables
				if ($row['type'] == 'richtext' || $row['type'] == 'htmlarea') {
					// Add richtext editor to the list
					if (is_array($replace_richtexteditor)) {
						$replace_richtexteditor = array_merge($replace_richtexteditor, array(
							"tv" . $row['id']
						));
					} else {
						$replace_richtexteditor = array(
							"tv" . $row['id']
						);
					}
				}
				foreach ($mutate_content_fields as $k => $v) {
					if (isset($v['fields']['tv' . $row['id']])) {
						$mutate_content_fields[$k]['fields']['tv' . $row['id']]                = array(
							'tv' => $row
						);
						$mutate_content_fields[$k]['fields']['tv' . $row['id']]['tv']['help']  = $v['fields']['tv' . $row['id']]['tv']['help'];
						$mutate_content_fields[$k]['fields']['tv' . $row['id']]['tv']['title'] = $v['fields']['tv' . $row['id']]['tv']['title'];
						unset($row);
					}
				}
				if ($row['id']) {
					$mutate_content_fields['General']['fields']['tv' . $row['id']] = array(
						'tv' => $row
					);
				}
			}
		}
	}
	// end Variables
	
	$output = '';
	$output .= $modx->db->getValue($rs); //
	
	
	if($altRenderTemplate && !$editableTemplate) {
		$output .= '<style>
		.pane-item { clear: both; padding: 3px 0; border-bottom: 1px dotted #CCC; }
		.pane-item:last-child { border-bottom: none }
		.pane-item-title .warning, .pane-item .comment { display: block; padding: 0 3px;margin: 0 0 1px; }
		.pane-item:after, .tab-row:after { content: ""; display: block; clear: both; }
		.pane-item-title { float: left; width: 185px; }
		.pane-item-f { margin-left: 190px; }
		.pane-item-f.pane-item-w { margin-left: 0 }
		</style>';
	}
	if ($editableTemplate) {
		$output .= '<link rel="stylesheet" type="text/css" href="../assets/modules/templatesEdit/css/style.css">';
	}
	
	foreach ($mutate_content_fields as $tabName => $tab) {
		if ($tab['title']) {
			$tabContent = '';
			foreach ($tab['fields'] as $fieldName => $field) {
				if($field['field'] || $field['tv']) {
					if ($field['field']) {
						$field['field']['name'] = $fieldName;
					}
					if($editableTemplate || $altRenderTemplate) {
						$tabContent .= renderContentField_edit($field, $editableTemplate);
					} else {
						$tabContent .= renderContentField($field);
					}
				}
			}
			if ($tabContent) {
				if($editableTemplate || $altRenderTemplate) {
					$hideClass = $editableTemplate && $tab['hide'] ? ' pane-page-hidden' : '';
					$hideStyle = $tab['hide'] && !$editableTemplate ? ' style="display:none !important"' : '';
					$tabVisibility = $tab['hide'] ? 'hidden' : 'visible';
					$output .= '<div class="tab-page'.$hideClass.'" id="tab' . $tabName . '"' . $hideStyle . '>';
					if($editableTemplate) {
						$output .= '<h2 class="tab" data-visibility="' . $tabVisibility . '">' . $tab['title'] . '</h2>
									<script type="text/javascript">tpSettings.addTabPage(document.getElementById("tab' . $tabName . '"));</script>';
						$output .= '<div class="pane-items">' . $tabContent . '</div>';
					} else {
						if (!$tab['hide']) {
							$output .= '<h2 class="tab">' . $tab['title'] . '</h2>
									<script type="text/javascript">tpSettings.addTabPage(document.getElementById("tab' . $tabName . '"));</script>';
						}
						$output .= $tabContent;
					}
					$output .= '</div>';
				} else {
					$output .= '<!-- ' . $tabName . ' -->
					<div class="tab-page" id="tab' . $tabName . '" ' . ($tab['hide'] ? 'style="display:none !important"' : '') . '>';
					if (!$tab['hide']) {
						$output .= '<h2 class="tab">' . $tab['title'] . '</h2>
									<script type="text/javascript">tpSettings.addTabPage(document.getElementById("tab' . $tabName . '"));</script>';
					}
					$output .= '
					<table width="100%" border="0" cellspacing="0" cellpadding="0">' . $tabContent . '</table>
					</div><!-- end #tab' . $tabName . ' -->';
				}
			}
		}
	}
	
	if ($editableTemplate) {
		$loadJquery ? $output .= '<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.0.js"></script>' : '';
		$theme = $modx->config['manager_theme'];
		$output .= <<< OUT
		<!------ templatesEdit ------->
		<script type="text/javascript" src="http://code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
		<script type="text/javascript">
		if (!window.jQuery) alert("jQuery  не подключен. \\nДля включения библиотеки jQuery, в настройках плагина templatesEdit установите Load JQuery - true");
		var j = jQuery.noConflict(true);
		var tplEdit = {
		    pitems: '.pane-items',
		    pitem: '.pane-item',
		    tabs: '.tab-row',
		    tab: '.tab-row h2',
		    pages: '.tab-pages',
		    page: '.tab-page',
		    warning: '.warning:first',
		    comment: '.comment:first',
			templateid: {$template},
		    init: function () {
		        var _self = this;
		        // исправляем шаблон D3X
		        ////////////////////////////////
		        if ('D3X' == '{$theme}' || 'D3X_commerce' == '{$theme}') {
		            j('#documentPane').before('<style>.dynamic-tab-pane-control .tab-row{background-position:0 100%;background-repeat:repeat-x;height: auto;margin-bottom: 2px;}.dynamic-tab-pane-control .tab-row .tab{height:auto;background-color: #EFF4F5 !important;padding: 3px 15px;}.dynamic-tab-pane-control .tab-row .tab.selected{height:auto;background-color: #E9F1F3 !important;}</style>');
		        }
		        /////////////////////////////////
		        j(_self.pitem).append(_self.boxItemEdit);
		        j(_self.tab + '[data-visibility=hidden]').addClass('tab-hidden');
		        j(_self.page).each(function (i, el) {
		            j(_self.tab).eq(i).attr('data-tabindex', '#' + j(el).attr('id'));
		        });
		        j(_self.tab).append(_self.boxTabEdit);
		        j(_self.page).wrapAll('<div class="tab-pages">');
		        j('.pane-item-title .warning, .pane-item-title .comment, .pane-item-helparea, .tab-row h2.tab span:not(' + _self.tab + '[data-tabindex=#tabMeta] span,' + _self.tab + '[data-tabindex=#tabAccess] span)').attr('contenteditable', true);
		        j(_self.tab + '[data-tabindex=#tabMeta] .tab-edit-box,' + _self.tab + '[data-tabindex=#tabAccess] .tab-edit-box').css('visibility', 'hidden');
		        j('#actions .actionButtons li').remove();
		        j('#actions .actionButtons').append('<li id="Button1"><a href="#" class="primary"><img alt="icons_save" src="media/style/{$theme}/images/icons/save.png"> Сохранить шаблон</a></li><li id="Button2"><a href="#" onclick="location.reload();"><img src="media/style/{$theme}/images/icons/refresh.png"> Обновить страницу</a></li><li id="Button3"><a href="#"><img src="media/style/{$theme}/images/icons/information.png"> Сбросить шаблон по умолчанию</a></li>');
		        j('#Button1 a').click(function () {
		            _self.setData()
		        });
		        j('#Button3 a').click(function () {
		            _self.setDefaultTemplate()
		        });
		        _self.sortItems();
		        _self.sortTabs();
		        _self.dropTabs();
		        j(_self.tab + '[data-tabindex=#tabMeta],' + _self.tab + '[data-tabindex=#tabAccess]').droppable({
		            disabled: true
		        });
		        _self.tabEdit();
		        _self.itemHover();
		    },
		    boxTabEdit: '<div class="tab-edit-box"><div class="tab-item-drag" title="Переместить"></div><div class="tab-item-visibility" title="Изменить видимость"></div><div class="tab-item-add" title="Добавить вкладку"></div><div class="tab-item-delete" title="Удалить вкладку"></div></div>',
		    boxItemEdit: '<div class="pane-item-box-edit"><div class="pane-item-drag" title="Переместить"></div><div class="pane-item-edittitle" title="Добавить / удалить название"></div><div class="pane-item-visibility" title="Изменить видимость"></div><div class="pane-item-edithelp" title="Добавить / удалить подсказку"></div><div class="pane-item-helparea" contenteditable="true"></div></div>',
		    newTab: '<h2 class="tab selected" data-tabindex=""><span contenteditable="true">Новая вкладка</span></h2>',
		    newPage: '<div class="tab-page"><div class="pane-items"></div></div>',
		    tabEdit: function () {
		        var _self = this;
		        j('.tab-edit-box div').unbind();
		        j(_self.tab + '[data-tabindex=#tabGeneral] .tab-item-delete, ' + _self.tab + '[data-tabindex=#tabGeneral] .tab-item-visibility').remove();
		        j(_self.tab).click(function () {
		            var _id = j(this).data('tabindex');
		            j(_self.page).hide();
		            j(_id).show();
		            j(_self.tab).removeClass('selected').css({
		                zIndex: 1
		            });
		            j(this).addClass('selected').css({
		                zIndex: 999
		            });
		        });
		        j('.tab-item-drag').click(function () {
		            return false;
		        });
		        j('.tab-item-visibility').click(function () {
		            var _id = j(this).closest('h2').attr('data-tabindex');
		            if (j(_id).hasClass('pane-page-hidden') && j(_self.tab + '[data-tabindex=' + _id + ']').hasClass('tab-hidden')) {
		                j(_id).css({
		                    opacity: 1
		                }).removeClass('pane-page-hidden');
		                j(_self.tab + '[data-tabindex=' + _id + ']').css({
		                    opacity: 1
		                }).removeClass('tab-hidden')
		            } else {
		                j(_id).addClass('pane-page-hidden');
		                j(_self.tab + '[data-tabindex=' + _id + ']').addClass('tab-hidden')
		            }
		            return false;
		        });
		        j('.tab-item-add').click(function () {
		            var _id = j(this).closest('h2').attr('data-tabindex');
		            _self.addNewTab(_id);
		            return false;
		        });
		        j('.tab-item-delete').click(function () {
		            var _id = j(this).closest('h2').attr('data-tabindex');
		            confirmTxt = 'Вы действительно хотите удалить вкладку ' + j(_self.tab + '[data-tabindex=' + _id + ']').text() + '?';
		            if (confirm(confirmTxt)) {
		                var tmp_items = j(_self.pitem, j(_id));
		                j(_self.tab + '[data-tabindex=' + _id + ']').remove();
		                j(_id).remove();
		                tmp_items.appendTo(j(_self.pitems, j('#tabGeneral')));
		                j(_self.page).hide();
		                j(_self.tab).removeClass('selected');
		                j(_self.tab + '[data-tabindex=#tabGeneral]').addClass('selected');
		                j('#tabGeneral').show()
		            }
		            return false;
		        });
		    },
		    newID: function () {
		        return new Date().getTime();
		    },
		    addNewTab: function (id) {
		        var _self = this;
		        var uniqid = _self.newID();
		        j(_self.page).hide();
		        j(_self.tab).removeClass('selected');
		        j(_self.tab + '[data-tabindex=' + id + ']').after(_self.newTab);
		        j(id).after(_self.newPage);
		        j(_self.tab + '[data-tabindex=' + id + ']').next().attr('data-tabindex', '#tab' + uniqid);
		        j(id).next().attr('id', 'tab' + uniqid);
		        j(_self.tab + '[data-tabindex=#tab' + uniqid + ']').append(_self.boxTabEdit);
		        j(_self.tab + '[data-tabindex=#tab' + uniqid + '] span').focus();
		        _self.tabEdit();
		        _self.dropTabs();
		    },
		    itemHover: function () {
		        var _self = this;
		        j(_self.pitem).find('.pane-item-box-edit div').unbind();
		        j(_self.pitem).hover(function () {
		            j('.pane-item-helparea', this).text(j('img#item-help-' + j(this).data('item-name'), this).attr('alt'));
		            if (j(this).index() == 0) {
		                j(this).closest(_self.page).css({
		                    zIndex: 1000
		                })
		            } else {
		                j(this).closest(_self.page).css({
		                    zIndex: 2
		                })
		            }
		        }, function () {
		            j(this).closest(_self.page).css({
		                zIndex: 2
		            })
		        });
		        j('.pane-item-visibility').click(function () {
		            var _item = j(this).closest(_self.pitem);
		            if (_item.hasClass('pane-item-hidden')) {
		                _item.removeClass('pane-item-hidden');
		            } else {
		                _item.addClass('pane-item-hidden');
		            }
		        });
		        j('.pane-item-edittitle').click(function () {
		            var _item = j(this).closest(_self.pitem);
		            var ptitle = _item.find('.pane-item-title');
		            var confirmTxt = 'Вы действительно хотите удалить название ?';
		            if (ptitle.length) {
		                if (ptitle.text() != '') {
		                    if (confirm(confirmTxt)) {
		                        ptitle.remove();
		                        _item.find('.pane-item-f').addClass('pane-item-w');
		                    }
		                } else {
		                    ptitle.remove();
		                    _item.find('.pane-item-f').addClass('pane-item-w');
		                }
		            } else {
		                _item.prepend('<div class="pane-item-title"><span class="warning" contenteditable="true"></span><span class="comment" contenteditable="true"></span></div>');
		                _item.find('.pane-item-f').removeClass('pane-item-w');
		            }
		        });
		        j('.pane-item-edithelp').click(function () {
		            var _item = j(this).closest(_self.pitem);
		            var phelp = j('img#item-help-' + _item.data('item-name'));
		            var phelp_area = _item.find('.pane-item-helparea');
		            var confirmTxt = 'Вы действительно хотите удалить подсказку ?';
		            if (phelp.length) {
		                if (phelp_area.is(':visible')) {
		                    if (phelp.attr('alt') != '') {
		                        if (confirm(confirmTxt)) {
		                            phelp_area.text('');
		                            phelp.remove()
		                        }
		                    } else {
		                        phelp_area.text('');
		                        phelp.remove()
		                    }
		                } else {
		                    phelp_area.text(phelp.attr('alt'));
		                    phelp_area.keyup(function () {
		                        phelp.attr('alt', j(this).text())
		                    });
		                }
		            } else {
		                _item.find('.pane-item-f').append(' <img id="item-help-' + _item.data('item-name') + '" src="media/style/{$theme}/images/icons/b02_trans.gif" alt="' + phelp_area.text() + '" style="cursor:help;" class="tooltip">');
		                phelp_area.keyup(function () {
		                    j('img#item-help-' + _item.data('item-name')).attr('alt', j(this).text())
		                });
		            }
		        });
		    },
		    sortItems: function (id) {
		        var _self = this;
		        j(_self.pitems).sortable({
		            connectWith: _self.pitem,
		            handle: '.pane-item-drag',
		            zIndex: 999
		        });
		    },
		    sortTabs: function () {
		        var _self = this;
		        j(_self.tabs).sortable({
		            axis: "x",
		            delay: 100,
		            opacity: 0.7,
		            handle: 'div.tab-item-drag',
		            items: 'h2:not([data-tabindex=#tabMeta], [data-tabindex=#tabAccess])',
		            //		            revert: true,
		            tolerance: 'pointer',
		            cancel: 'span',
		            update: function (event, ui) {
		                var tmp_id;
		                j('h2', this).each(function (i, el) {
		                    var id = j(el).data('tabindex');
		                    if (j(el).hasClass('selected')) {
		                        j(el).css({
		                            zIndex: 999
		                        });
		                        tmp_id = id;
		                    } else {
		                        j(el).css({
		                            zIndex: 1
		                        });
		                        var tmp = j(id).clone();
		                        if (tmp_id) {
		                            j(id + ':first').remove();
		                            j(tmp_id).after(tmp);
		                            tmp_id = id;
		                        } else {
		                            j(id).remove();
		                            j(_self.pages).prepend(tmp);
		                            tmp_id = id;
		                        }
		                    }
		                    _self.sortItems();
		                });
		                _self.itemHover();
		            }
		        });
		    },
		    dropTabs: function () {
		        var _self = this;
		        j(_self.tab).droppable({
		            accept: _self.pitem,
		            tolerance: "pointer",
		            activeClass: "ui-drop-highlight",
		            hoverClass: "ui-drop-hover",
		            forcePlaceholderSize: true,
		            drop: function (event, ui) {
		                var _list = j(j(this).data('tabindex') + ' ' + _self.pitems);
		                ui.draggable.css({
		                    visibility: 'hidden'
		                }).hide(500, function () {
		                    j(this).appendTo(_list).show(250).css({
		                        visibility: 'visible'
		                    });
		                    if (j(this).parent().find(_self.pitem).length == 1) {
		                        _self.sortItems()
		                    }
		                });
		            }
		        });
		    },
		    htmlentities: function (s) {
		        var div = document.createElement('div');
		        var text = document.createTextNode(s);
		        div.appendChild(text);
		        return div.innerHTML;
		    },
		    cleanText: function (str) {
		        return str.replace(/"/g, "&quot;");
		    },
		    setData: function () {
		        var _self = this;
		        var tsArr = {};
		        j(_self.tab).each(function (i, h) {
		            var grpID = j(h).data('tabindex').replace('#tab', '');
		            if (grpID == 'Access' || grpID == 'Meta') {
		                return
		            } else {
		                tsArr[grpID] = {};
		                tsArr[grpID]['fields'] = {};
		                tsArr[grpID]['title'] = _self.cleanText(j(h).text());
		                tsArr[grpID]['roles'] = '';
		                tsArr[grpID]['hide'] = j('#tab' + grpID).hasClass('pane-page-hidden') ? '1' : '0';
		                j(_self.pitem, j('#tab' + grpID)).each(function () {
		                    var field_type = this.hasClass('pane-item-field') ? 'field' : 'tv';
		                    var item_id = j(this).data('item-name');
		                    var title = j(_self.warning, this).text();
		                    if (j(_self.warning, this).text() != '') {
		                        if (j(_self.comment, this).text() != '') {
		                            title = title + '||||' + j(_self.comment, this).text();
		                        }
		                    } else {
		                        title = '';
		                    }
		                    var help = j('#item-help-' + item_id, this).length ? _self.cleanText(j('#item-help-' + item_id, this).attr('alt')) : '';
		                    tsArr[grpID]['fields'][item_id] = {};
		                    tsArr[grpID]['fields'][item_id][field_type] = {};
		                    tsArr[grpID]['fields'][item_id][field_type]['title'] = _self.cleanText(title);
		                    tsArr[grpID]['fields'][item_id][field_type]['help'] = help;
		                    tsArr[grpID]['fields'][item_id][field_type]['name'] = item_id;
		                    tsArr[grpID]['fields'][item_id][field_type]['roles'] = '';
		                    tsArr[grpID]['fields'][item_id][field_type]['hide'] = this.hasClass('pane-item-hidden') ? '1' : '0';
		                })
		            }
		        });
		        var stringArr = JSON.stringify(tsArr);
		        j.ajax({
		            type: "POST",
		            cache: false,
		            url: window.location.protocol + '//' + window.location.host + '/' + 'assets/modules/templatesEdit/ajax-action.php',
		            data: {
		                templateid: _self.templateid,
		                data: _self.htmlentities(stringArr)
		            },
		            success: function (data) {
		                alert(data);
		                location.reload();
		            },
		            error: function () {
		                alert('Ошибка сохранения шаблона');
		            }
		        });
		    },
		    setDefaultTemplate: function () {
				var _self = this;
		        var confirmTxt = 'Вы действительно хотите сбросить шаблон по умолчанию ?';
		        if (confirm(confirmTxt)) {
		            j.ajax({
		                type: "POST",
		                cache: false,
		                url: window.location.protocol + '//' + window.location.host + '/' + 'assets/modules/templatesEdit/ajax-action.php',
		                data: {
		                    templateid: _self.templateid,
		                    reset: 'yes'
		                },
		                success: function (data) {
		                    alert(data);
		                    location.reload();
		                },
		                error: function () {
		                    alert('Ошибка сброса шаблона');
		                }
		            });
		        }
		    }
		}
		j(function () {
		    tplEdit.init();
		});
		</script>
		<!------ /templatesEdit/ ------->
OUT;
	}
	
	unset($mutate_content_fields);
	$e->output($output);
}
