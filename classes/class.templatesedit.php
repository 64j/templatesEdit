<?php
class templatesEdit {
    function __construct(&$modx) {
        $this->modx             = $modx;
        $this->dbname           = $modx->db->config['dbase'];
        $this->tbl_settings     = $modx->db->config['table_prefix'] . "site_templates_settings";
        $this->tbl_tpl          = $modx->getFullTableName('site_templates');
        $this->tbl_tpl_settings = $modx->getFullTableName('site_templates_settings');
        $this->tbl_tv_tpl       = $modx->getFullTableName('site_tmplvar_templates');
        $this->tbl_tvs          = $modx->getFullTableName('site_tmplvars');
        $this->tbl_eventnames   = $modx->getFullTableName('system_eventnames');
    }
    
    /**
     * установка модуля
     *
     */
    function modInstall() {
        $sql    = array();
        $sql[]  = "CREATE TABLE IF NOT EXISTS " . $this->tbl_tpl_settings . " (`id` INT(11) NOT NULL auto_increment, `templateid` INT(11), `data` MEDIUMTEXT NOT NULL, PRIMARY KEY (`id`));";
        $sql[]  = "INSERT INTO " . $this->tbl_tpl_settings . " VALUES (NULL, '0', '" . $this->setTemplateDefault('', true) . "');";
        $sql[]  = "INSERT INTO " . $this->tbl_eventnames . " VALUES (NULL, 'OnDocFormTemplateRender', '1', 'Documents');";
        $result = $this->modx->db->makeArray($this->modx->db->query("SELECT id FROM " . $this->tbl_tpl . ""));
        foreach ($result as $tpl) {
            $rt     = $this->modx->db->makeArray($this->modx->db->select("tv.name, tv.caption AS title, tv.description, tv.id AS id, tr.templateid, tr.rank", "{$this->tbl_tv_tpl} AS tr
				INNER JOIN {$this->tbl_tvs} AS tv ON tv.id = tr.tmplvarid
				INNER JOIN {$this->tbl_tpl} AS tm ON tr.templateid = tm.id", "tr.templateid='" . $tpl['id'] . "'", "tr.rank, tv.rank"));
            $tv_arr = array();
            foreach ($rt as $v) {
                $tv_arr['tv' . $v['id']] = array(
                    'tv' => array(
                        'title' => $tv['title'] . $tv['description'] ? '||||'  .$tv['description'] : '',
                        'help' => '',
                        'name' => $v['name'],
                        'roles' => '',
                        'hide' => ''
                    )
                );
            }
            $sql[] = "INSERT INTO " . $this->tbl_tpl_settings . " VALUES (NULL, " . $tpl['id'] . ", '" . $this->setTemplateDefault($tv_arr, true) . "');";
        }
        foreach ($sql as $line) {
            $this->modx->db->query($line);
        }
    }
    
    /**
     * Установка всех шаблонов по маске шаблона blank - templateid=0
     *
     */
    function setDefaultTemplates() {
        $result = $this->modx->db->makeArray($this->modx->db->query("SELECT id FROM " . $this->tbl_tpl . ""));
        foreach ($result as $tpl) {
            $rt     = $this->modx->db->makeArray($this->modx->db->select("tv.name, tv.caption AS title, tv.description, tv.id AS id, tr.templateid, tr.rank", "{$this->tbl_tv_tpl} AS tr
				INNER JOIN {$this->tbl_tvs} AS tv ON tv.id = tr.tmplvarid
				INNER JOIN {$this->tbl_tpl} AS tm ON tr.templateid = tm.id", "tr.templateid='" . $tpl['id'] . "'", "tr.rank, tv.rank"));
            $tv_arr = array();
            foreach ($rt as $v) {
                $tv_arr['tv' . $v['id']] = array(
                    'tv' => array(
                        'title' => $tv['title'] . $tv['description'] ? '||||'  .$tv['description'] : '',
                        'help' => '',
                        'name' => $v['name'],
                        'roles' => '',
                        'hide' => ''
                    )
                );
            }
            if ($tpl['id'] != 0) {
                $sql[] = "UPDATE " . $this->tbl_tpl_settings . " SET data='" . $this->setTemplateDefault($tv_arr, false) . "' WHERE templateid=" . $tpl['id'] . ";";
            }
        }
        foreach ($sql as $line) {
            $this->modx->db->query($line);
        }
    }
    
    /**
     * Установим шаблон по умолчанию с исползуемыми TV параметрами
     *
     */
    function reloadTemplateDefault($templateid) {
        $default_data = $templateid == '0' ? true : false;
        if (isset($templateid)) {
            $rt     = $this->modx->db->makeArray($this->modx->db->select("tv.name, tv.caption AS title, tv.description, tv.id AS id, tr.templateid, tr.rank", "{$this->tbl_tv_tpl} AS tr
				INNER JOIN {$this->tbl_tvs} AS tv ON tv.id = tr.tmplvarid
				INNER JOIN {$this->tbl_tpl} AS tm ON tr.templateid = tm.id", "tr.templateid='" . $templateid . "'", "tr.rank, tv.rank"));
            $tv_arr = array();
            foreach ($rt as $v) {
                $tv_arr['tv' . $v['id']] = array(
                    'tv' => array(
                        'title' => $tv['title'] . $tv['description'] ? '||||'  .$tv['description'] : '',
                        'help' => '',
                        'name' => $v['name'],
                        'roles' => '',
                        'hide' => ''
                    )
                );
            }
            $this->modx->db->query("UPDATE " . $this->tbl_tpl_settings . " SET data='" . $this->setTemplateDefault($tv_arr, $default_data) . "' WHERE templateid='" . $templateid . "'");
        }
    }
    
    /**
     * Установим шаблон по умолчанию с исползуемыми TV параметрами
     *
     */
    function setTemplateDefault($tv_arr = "", $default_data = false) {
        global $_lang;
        if (empty($_lang)) {
            include_once(dirname(__FILE__) . "../../../../cache/siteManager.php");
            require_once(dirname(__FILE__) . '/../../../../' . MGR_DIR . '/includes/lang/' . $this->modx->getConfig('manager_language') . '.inc.php');
        }
        if (!$default_data) {
            $data = json_decode($this->modx->db->getValue($this->modx->db->select('data', $this->tbl_tpl_settings, 'templateid=0')), true);
        } else {
            foreach ($_lang as $k => $v) {
                $_lang[$k] = $this->strClean($v);
            }
            $data = array(
                'General' => array(
                    'title' => $_lang['settings_general'],
                    'fields' => array(
                        'pagetitle' => array(
                            'field' => array(
                                'title' => $_lang['resource_title'],
                                'help' => $_lang['resource_title_help'],
                                'name' => 'pagetitle',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'longtitle' => array(
                            'field' => array(
                                'title' => $_lang['long_title'],
                                'help' => $_lang['resource_long_title_help'],
                                'name' => 'longtitle',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'description' => array(
                            'field' => array(
                                'title' => $_lang['resource_description'],
                                'help' => $_lang['resource_description_help'],
                                'name' => 'description',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'alias' => array(
                            'field' => array(
                                'title' => $_lang['resource_alias'],
                                'help' => $_lang['resource_alias_help'],
                                'name' => 'alias',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'link_attributes' => array(
                            'field' => array(
                                'title' => $_lang['link_attributes'],
                                'help' => $_lang['link_attributes_help'],
                                'name' => 'link_attributes',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'weblink' => array(
                            'field' => array(
                                'title' => $_lang['weblink'],
                                'help' => $_lang['resource_weblink_help'],
                                'name' => 'weblink',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'introtext' => array(
                            'field' => array(
                                'title' => $_lang['resource_summary'],
                                'help' => $_lang['resource_summary_help'],
                                'name' => 'introtext',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'template' => array(
                            'field' => array(
                                'title' => $_lang['page_data_template'],
                                'help' => $_lang['page_data_template_help'],
                                'name' => 'template',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'menutitle' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_menu_title'],
                                'help' => $_lang['resource_opt_menu_title_help'],
                                'name' => 'menutitle',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'menuindex' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_menu_index'],
                                'name' => 'menuindex',
                                'help' => '',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'parent' => array(
                            'field' => array(
                                'title' => $_lang['resource_parent'],
                                'help' => $_lang['resource_parent_help'],
                                'name' => 'parent',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'content' => array(
                            'field' => array(
                                'title' => $_lang['which_editor_title'],
                                'help' => '',
                                'name' => 'content',
                                'roles' => '',
                                'hide' => ''
                            )
                        )
                    ),
                    'roles' => '',
                    'hide' => ''
                ),
                'Settings' => array(
                    'title' => $_lang['settings_page_settings'],
                    'fields' => array(
                        'published' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_published'],
                                'help' => $_lang['resource_opt_published_help'],
                                'name' => 'published',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'pub_date' => array(
                            'field' => array(
                                'title' => $_lang['page_data_publishdate'],
                                'help' => $_lang['page_data_publishdate_help'],
                                'name' => 'pub_date',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'unpub_date' => array(
                            'field' => array(
                                'title' => $_lang['page_data_unpublishdate'],
                                'help' => $_lang['page_data_unpublishdate_help'],
                                'name' => 'unpub_date',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'type' => array(
                            'field' => array(
                                'title' => $_lang['resource_type'],
                                'help' => $_lang['resource_type_message'],
                                'name' => 'type',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'contentType' => array(
                            'field' => array(
                                'title' => $_lang['page_data_contentType'],
                                'help' => $_lang['page_data_contentType_help'],
                                'name' => 'contentType',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'content_dispo' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_contentdispo'],
                                'help' => $_lang['resource_opt_contentdispo_help'],
                                'name' => 'content_dispo',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'alias_visible' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_alvisibled'],
                                'help' => $_lang['resource_opt_alvisibled_help'],
                                'name' => 'alias_visible',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'isfolder' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_folder'],
                                'help' => $_lang['resource_opt_folder_help'],
                                'name' => 'isfolder',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'richtext' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_richtext'],
                                'help' => $_lang['resource_opt_richtext_help'],
                                'name' => 'richtext',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'donthit' => array(
                            'field' => array(
                                'title' => $_lang['track_visitors_title'],
                                'help' => $_lang['resource_opt_trackvisit_help'],
                                'name' => 'donthit',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'searchable' => array(
                            'field' => array(
                                'title' => $_lang['page_data_searchable'],
                                'help' => $_lang['page_data_searchable_help'],
                                'name' => 'searchable',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'cacheable' => array(
                            'field' => array(
                                'title' => $_lang['page_data_cacheable'],
                                'help' => $_lang['page_data_cacheable_help'],
                                'name' => 'cacheable',
                                'roles' => '',
                                'hide' => ''
                            )
                        ),
                        'syncsite' => array(
                            'field' => array(
                                'title' => $_lang['resource_opt_emptycache'],
                                'help' => $_lang['resource_opt_emptycache_help'],
                                'name' => 'syncsite',
                                'roles' => '',
                                'hide' => ''
                            )
                        )
                    ),
                    'roles' => '',
                    'hide' => ''
                )
            );
        }
        
        if (is_array($tv_arr)) {
            $data['General']['fields'] = array_merge($data['General']['fields'], $tv_arr);
        }
        $data = $this->json_encode_cyr($data);
        return mysql_real_escape_string($data);
    }
    
    /* Чистим строку
     *
     *
     */
    function strClean($str) {
        return htmlspecialchars($str, ENT_QUOTES);
    }
    
    /* Вывод JSON шаблона
     *
     *
     */
    function viewJSON($templateid = 0) {
        $data = $this->modx->db->getValue($this->modx->db->select('data', $this->tbl_tpl_settings, 'templateid=' . $templateid));
        return $data;
    }
	
    /* Сохранить JSON шаблона
     * @ templateid
     *
     */
    function saveJSON($json, $templateid) {
        global $sanitize_seed;
        $json = preg_replace('|\s+|', ' ', $json);
        $json = str_replace($sanitize_seed, '', $json);
        $this->modx->db->query("UPDATE " . $this->tbl_tpl_settings . " SET data='" . mysql_real_escape_string($json) . "' WHERE templateid=" . $templateid);
    }
    
    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param string $json The original JSON string to process.
     * @param string $indentStr The string used for indenting nested structures. Defaults to 4 spaces.
     * @return string Indented version of the original JSON string.
     */
    function pretty_json($json, $indentStr = '    ') {
        $result         = '';
        $level          = 0;
        $strLen         = strlen($json);
        $newLine        = "\n";
        $prevChar       = '';
        $outOfQuotes    = true;
        $openingBracket = false;
        for ($i = 0; $i <= $strLen; $i++) {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);
            // Add spaces before and after :
            if (($char == ':' && $prevChar != ' ') || ($prevChar == ':' && $char != ' ')) {
                if ($outOfQuotes) {
                    $result .= ' ';
                }
            }
            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
                // If this character is the end of a non-empty element,
                // output a new line and indent the next line.
            } else if (($char == '}' || $char == ']') && $outOfQuotes) {
                $level--;
                if (!$openingBracket) {
                    $result .= $newLine . str_repeat($indentStr, $level);
                }
            }
            // Add the character to the result string.
            $result .= $char;
            // If the last character was the beginning of a non-empty element,
            // output a new line and indent the next line.
            $openingBracket = ($char == '{' || $char == '[');
            if (($char == ',' || $openingBracket) && $outOfQuotes) {
                if ($openingBracket) {
                    $level++;
                }
                $nextChar = substr($json, $i + 1, 1);
                if (!($openingBracket && ($nextChar == '}' || $nextChar == ']'))) {
                    $result .= $newLine . str_repeat($indentStr, $level);
                }
            }
            $prevChar = $char;
        }
        return $result;
    }
    
    /**
     * Проверка, установлен модуль или нет
     *
     */
    function checkInstall() {
        if (mysql_num_rows(mysql_query("SHOW TABLES FROM " . $this->dbname . " LIKE '" . $this->tbl_settings . "'")) > 0) {
            return true;
        }
    }
    
    /**
     * JSON в кириллице
     *
     */
    function json_encode_cyr($str, $flag = false) {
        $arr_replace_utf = array('\u0410','\u0430','\u0411','\u0431','\u0412','\u0432','\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416','\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a','\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f','\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443','\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428','\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c','\u042d','\u044d','\u042e','\u044e','\u042f','\u044f'
        );
        $arr_replace_cyr = array('А','а','Б','б','В','в','Г','г','Д','д','Е','е','Ё','ё','Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о','П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш','Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я'
        );
        $str1            = json_encode($str, $flag);
        $str2            = str_replace($arr_replace_utf, $arr_replace_cyr, $str1);
        return $str2;
    }
    
    /**
     * Рекурсивынй поиск значений в массиве
     *
     */
    function recursive_array_search($needle, $haystack) {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value OR (is_array($value) && $this->recursive_array_search($needle, $value))) {
                return $current_key;
            }
        }
        return false;
    }
    
    /**
     * Удаляет все данные модуля из БД
     *
     */
    function modUninstall() {
        $sql   = array();
        $sql[] = "DROP TABLE IF EXISTS $this->tbl_tpl_settings";
        foreach ($sql as $line) {
            $this->modx->db->query($line);
        }
    }
}
