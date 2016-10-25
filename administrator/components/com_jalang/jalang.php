<?php
/**
 * ------------------------------------------------------------------------
 * JA Multilingual J2x-J3x.
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;


if (!JFactory::getUser()->authorise('core.manage', 'com_jalang'))
{
	return JFactory::getApplication()->enqueueMessage(JText::_('JAERROR_ALERTNOAUTHOR'), 'error');
}
/*
require_once( __DIR__ . '/helpers/helper.php' );
require_once( __DIR__ . '/helpers/content/content.php' );
require_once( __DIR__ . '/helpers/translator/translator.php' );
*/
require_once( dirname(__FILE__) . '/helpers/helper.php' );
require_once( dirname(__FILE__) . '/helpers/tool.php' );
require_once( dirname(__FILE__) . '/helpers/content/content.php' );
require_once( dirname(__FILE__) . '/helpers/translator/translator.php' );  

$app = JFactory::getApplication();
$helper = new JalangHelper();
$helper->update();
$jinput = $app->input;
$itemtype = $jinput->get('itemtype', 'content');
if(!empty($itemtype)) {
	$app->setUserState('com_jalang.itemtype', $itemtype);
}
$mainlanguage = $jinput->get('mainlanguage', JalangHelper::getDefaultLanguage());
if(!empty($mainlanguage)) {
	$app->setUserState('com_jalang.mainlanguage', $mainlanguage);
}

//asset
$document = JFactory::getDocument();

if(JalangHelper::isJoomla3x()) {
	$document->addStyleSheet('components/com_jalang/asset/style.css');
} else {
	$document->addStyleSheet('components/com_jalang/asset/style_2x.css');
	$document->addScript('components/com_jalang/asset/jquery.min.js');
	$document->addScript('components/com_jalang/asset/jquery-noconflict.js');
}
$controller = JControllerLegacy::getInstance('Jalang');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
