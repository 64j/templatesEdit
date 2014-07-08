<?php
/*
* Управление шаблонами
*
* templatesEdit
*/
defined('IN_MANAGER_MODE') or die();
global $_lang;
$theme       = $modx->config['manager_theme'];
$mod_page    = "index.php?a=112&id=" . $_GET['id'];

require_once MODX_BASE_PATH . "assets/modules/templatesEdit/classes/class.templatesedit.php";
$tplEdit            = new templatesEdit($modx);
$tplEdit->modx_page = $mod_page;

$action = !empty($_GET['action']) ? $_GET['action'] : (!empty($_POST['action']) ? $_POST['action'] : '');
switch ($action) {
	
	//Установка модуля
	case 'install':
	$tplEdit->modInstall();
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;
	
	//Удаление модуля
	case "uninstall":
	$tplEdit->modUninstall();
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;

	//Установка всех щаблонов по маске blank
	case "set_default_templates":
	$tplEdit->setDefaultTemplates();
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;

	//Страница модуля
	default:
	include "tpl/header.tpl.php";
	
	if ($tplEdit->checkInstall()) {		
		include "tpl/index.tpl.php";
	} else {
		echo '<div><ul class="actionButtons"><li><a href="' . $mod_page . '&action=install"><img src="media/style/'.$theme.'/images/icons/save.png" alt="">&nbsp; Установить модуль</a></li></ul></div>';
	}
	
	include "tpl/footer.tpl.php";
	break; 
}
?>
