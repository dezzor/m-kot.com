<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
JHTML::_('behavior.modal','a.modal');
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcySMS Component';
}

if(!ACYSMS_J16){

	class JElementCustomfields extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=fields&amp;task=choose&amp;values='.$value.'&amp;control='.$control_name;
			$text = '<input class="inputbox" id="'.$control_name.'customfields" name="'.$control_name.'['.$name.']" type="text" style="width:100px" value="'.$value.'">';
			$text .= '<a class="modal" id="link'.$control_name.'customfields" title="'.JText::_('SMS_EXTRA_FIELDS').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">'.JText::_('Select').'</button></a>';

			return $text;

		}
	}
}else{
	class JFormFieldCustomfields extends JFormField
	{
		var $type = 'help';

		function getInput() {
			$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=fields&amp;task=choose&amp;values='.$this->value.'&amp;control='.$this->name;
			$text = '<input class="inputbox" id="'.$this->name.'customfields" name="'.$this->name.'" type="text" style="width:100px" value="'.$this->value.'">';
			$text .= '<a class="modal" id="link'.$this->name.'customfields" title="'.JText::_('SMS_EXTRA_FIELDS').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">'.JText::_('Select').'</button></a>';

			return $text;

		}
	}
}
