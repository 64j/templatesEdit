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
    global $modx, $_style, $_lang, $content, $site_name, $use_editor, $which_editor, $editor, $replace_richtexteditor, $search_default, $publish_default, $cache_default;
    $field = '';
    $name  = $data['field']['name'];
    list($item_title, $item_description) = explode('||||', $data['field']['title']);
    $fieldDescription = (!empty($item_description)) ? '<br><span class="comment">' . $item_description . '</span>' : '';
    $title            = '<span class="warning">' . $item_title . '</span>' . $fieldDescription;
    $help             = $data['field']['help'] ? ' <img src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['field']['help']) . '" style="cursor:help;" />' : '';
    $hide             = $data['field']['hide'] ? 'none' : 'table-row';
    $title_width      = 150;
    $input_width      = '';
    $mx_can_pub       = $modx->hasPermission('publish_document') ? '' : 'disabled="disabled" ';
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
        $tvInherited   = (substr($tvPBV, 0, 8) == '@INHERIT') ? '<span class="comment inherited">(' . $_lang['tmplvars_inherited'] . ')</span>' : '';
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
                        $field .= '<optgroup label="' . $thisCategory . '">';
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
                        /*$field .='
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
    if ($hide == 'table-row' && !empty($field)) {
        $field .= ContentFieldSplit();
    }
    return $field;
}

function renderContentField_edit($data, $editableTemplate) {
    global $modx, $_style, $_lang, $content, $site_name, $use_editor, $which_editor, $editor, $replace_richtexteditor, $search_default, $publish_default, $cache_default;
    $field = '';
    $name  = $data['field']['name'];
    list($item_title, $item_description) = explode('||||', $data['field']['title']);
    $fieldDescription = (!empty($item_description)) ? '<span class="comment">' . $item_description . '</span>' : '';
    $title            = '<span class="warning">' . $item_title . '</span>' . $fieldDescription;
    $help             = $data['field']['help'] ? ' <img id="item-help-' . $name . '" src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['field']['help']) . '" style="cursor:help;" />' : '';
    $hide             = $data['field']['hide'] && !$editableTemplate ? ' style="display: none"' : '';
    $itemClass        = 'pane-item pane-item-field' . ($editableTemplate && $data['field']['hide'] ? ' pane-item-hidden' : '');
    $itemAttr         = $editableTemplate ? 'class="' . $itemClass . '"' . $hide . ' data-item-name="' . $name . '"' : ' class="' . $itemClass . '"' . $hide;
    $mx_can_pub       = $modx->hasPermission('publish_document') ? '' : 'disabled="disabled" ';
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
        $tvInherited   = (substr($tvPBV, 0, 8) == '@INHERIT') ? '<span class="comment inherited">(' . $_lang['tmplvars_inherited'] . ')</span>' : '';
        $title         = '<span class="warning">' . $item_title . '</span>' . $tvDescription . $tvInherited;
        $help          = $data['tv']['help'] ? ' <img id="item-help-' . $name . '" src="' . $_style["icons_tooltip_over"] . '" alt="' . stripcslashes($data['tv']['help']) . '" style="cursor:help;" />' : '';
        $hide          = $data['tv']['hide'] && !$editableTemplate ? ' style="display: none"' : '';
        $itemClass     = 'pane-item pane-item-tv' . ($editableTemplate && $data['tv']['hide'] ? ' pane-item-hidden' : '');
        $itemAttr      = $editableTemplate ? 'class="' . $itemClass . '" style="display: ' . $hide . '" data-item-name="' . $name . '"' : ' class="' . $itemClass . '"' . $hide;
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
                        $field .= '<optgroup label="' . $thisCategory . '">';
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
                        /*$field .='
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
                if ($data['field']['title']) {
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
$editableTemplate  = $editableTemplate == 'true' ? true : false;
$altRenderTemplate = $altRenderTemplate == 'true' ? true : false;
$loadJquery        = $loadJquery == 'true' ? true : false;


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
    $rs      = $modx->db->makeArray($modx->db->select("data, templateid", $modx->getFullTableName('site_templates_settings')));
    foreach ($rs as $v) {
        $check_tpl = $modx->db->getValue($modx->db->select("templateid", $modx->getFullTableName('site_tmplvar_templates'), "tmplvarid=" . $id . " AND templateid=" . $v['templateid']));
        $data      = json_decode($v['data'], true);
        if (!$check_tpl) {
            foreach ($data as $key => $val) {
                unset($data[$key]['fields']['tv' . $id]);
            }
            $data = $tplEdit->json_encode_cyr($data);
            $modx->db->update(array(
                'data' => mysql_real_escape_string($data)
            ), $modx->getFullTableName('site_templates_settings'), "templateid=" . $v['templateid']);
        } else {
            $tv = $modx->db->getRow($modx->db->select("name, caption AS title, description", $modx->getFullTableName('site_tmplvars'), "id=" . $id));
            if (!$tplEdit->recursive_array_search($tv['name'], $data)) {
                $tv_arr['tv' . $id]        = array(
                    'tv' => array(
                        'title' => $tv['title'] . $tv['description'] ? '||||'  .$tv['description'] : '',
                        'help' => '',
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
    
    if ($altRenderTemplate && !$editableTemplate) {
        $output .= "\n\n\t" . '<!------ templatesEdit ------->' . "\n\t";
        $output .= '<link rel="stylesheet" type="text/css" href="../assets/modules/templatesEdit/css/style_alt.css">' . "\n\t";
        $output .= '<!------ /templatesEdit/ ------->' . "\n\t";
    }
    
    foreach ($mutate_content_fields as $tabName => $tab) {
        if ($tab['title']) {
            $tabContent = '';
            foreach ($tab['fields'] as $fieldName => $field) {
                if ($editableTemplate || $altRenderTemplate) {
                    if ($field['split']) {
                        if ($editableTemplate) {
                            $tabContent .= '<div class="pane-item pane-item-split" data-item-name="' . $field['split']['name'] . '"><div class="pane-item-title"><span class="warning">' . $field['split']['title'] . '</span></div></div>';
                        } else {
                            $tabContent .= '<div class="pane-item pane-item-split"><h3><span class="warning">' . $field['split']['title'] . '</span></h3></div>';
                        }
                    } else {
                        $tabContent .= renderContentField_edit($field, $editableTemplate);
                    }
                } else {
                    if ($field['split']) {
                        $tabContent .= '<tr><td colspan="2"><h3><span class="warning">' . $field['split']['title'] . '</span></h3></td></tr>';
                    } else {
                        $tabContent .= renderContentField($field);
                    }
                }
            }
            if ($tabContent) {
                if ($editableTemplate || $altRenderTemplate) {
                    $hideClass     = $editableTemplate && $tab['hide'] ? ' pane-page-hidden' : '';
                    $hideStyle     = $tab['hide'] && !$editableTemplate ? ' style="display:none !important"' : '';
                    $tabVisibility = $tab['hide'] ? 'hidden' : 'visible';
                    $output .= '<div class="tab-page' . $hideClass . '" id="tab' . $tabName . '"' . $hideStyle . '>';
                    if ($editableTemplate) {
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
        $output .= "\n\n\t" . '<!------ templatesEdit ------->' . "\n\t";
        $output .= '<link rel="stylesheet" type="text/css" href="../assets/modules/templatesEdit/css/style.css">' . "\n\t";
        $loadJquery ? $output .= '<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.0.js"></script>' . "\n\t" : '';
        $output .= '<script type="text/javascript" src="http://code.jquery.com/ui/1.11.0/jquery-ui.js"></script>' . "\n\t";
        $output .= '<script type="text/javascript"> var manager_theme="' . $modx->config['manager_theme'] . '";</script>' . "\n\t";
        $output .= '<script type="text/javascript" src="../assets/modules/templatesEdit/js/plugin.templatesedit.js"></script>' . "\n\t";
        $output .= '<!------ /templatesEdit/ ------->' . "\n\t";
    }
    
    unset($mutate_content_fields);
    $e->output($output);
}
