<?php
/**
 * @package Sj Video Box
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;

JHTML::script('jquery.js', 'modules/mod_sj_videobox/js/');
// Include the syndicate functions only once
require_once (dirname(__FILE__).'/core/helper.php');
//require(JModuleHelper::getLayoutPath('mod_sj_videobox'));
require JModuleHelper::getLayoutPath('mod_sj_videobox', $params->get('layout', 'default'));?>
