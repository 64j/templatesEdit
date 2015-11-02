<?php
include_once(dirname(__FILE__)."/../../cache/siteManager.php");
require_once(dirname(__FILE__).'/../../../'.MGR_DIR.'/includes/protect.inc.php');
if(empty($_SERVER['HTTP_REFERER'])) exit;

define('MODX_MANAGER_PATH', "../../../".MGR_DIR."/");
require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
define('MODX_API_MODE', true);
require_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');

session_name($site_sessionname);
session_id($_COOKIE[session_name()]);
session_start();

$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();

if($modx->config['modx_charset'] == "UTF-8"){
  header('Content-Type: text/html; charset=utf-8');
}elseif($charset=="windows-1251"){
  header('Content-Type: text/html; charset=windows-1251');
}

if(isset($_POST['templateid']) && $_POST['data']) {
	global $sanitize_seed;
	$data = str_replace($sanitize_seed, '', $_POST['data']);
	$templateid = $_POST['templateid'];
	
	$modx->db->update(array('data' => mysql_real_escape_string($data)), $modx->getFullTableName('site_templates_settings'), "templateid=" . $templateid);
}

if(isset($_POST['templateid']) && $_POST['reset'] == 'yes') {
	require_once MODX_BASE_PATH . "assets/modules/templatesEdit/classes/class.templatesedit.php";
	$tplEdit = new templatesEdit($modx);
	$templateid = $_POST['templateid'];
	$tplEdit->reloadTemplateDefault($templateid);
	
	if($templateid == 0) {
		$templatename = 'blank';
	} else {
		$templatename = $modx->db->getValue($modx->db->select('templatename', $modx->getFullTableName('site_templates'), "id=" . $templateid));
	}
	echo 'Шаблон "' . $templatename .'" (' . $templateid . ') сброшен по умолчанию.';
}

?>
