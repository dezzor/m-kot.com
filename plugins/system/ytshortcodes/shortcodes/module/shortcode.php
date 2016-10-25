<?php
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('_JEXEC') or die;
function moduleYTShortcode($atts, $content = null){
	global $style_acc;
	extract(ytshortcode_atts(array(
		"module"  =>'none',
	),$atts));
	$db = JFactory::getDBO();
	$document	= JFactory::getDocument();
	$renderer	= $document->loadRenderer('module');
	$module_id = $module;
	$query = "SELECT * FROM `#__modules` AS a WHERE a.title = '" . $module_id . "' AND a.published = 1 AND a.position != '' ";
	$db->setQuery($query);
	$result = $db->loadObject();
	$_module = JModuleHelper::renderModule($result);
	return $_module;
}	
?>