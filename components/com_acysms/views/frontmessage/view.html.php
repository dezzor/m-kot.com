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
include(ACYSMS_BACK.'views'.DS.'message'.DS.'view.html.php');

class FrontmessageViewFrontmessage extends MessageViewMessage
{
	var $ctrl='frontmessage';

	function display($tpl = null)
	{
		JHTML::_('behavior.tooltip');

		global $Itemid;
		$this->assignRef('Itemid',$Itemid);

		parent::display($tpl);
	}

	function listing()
	{

		if(empty($_POST) && !JRequest::getInt('start') && !JRequest::getInt('limitstart')){
			JRequest::setVar('limitstart',0);
		}

		return parent::listing();
	}

	function summarybeforesend(){

		$config = ACYSMS::config();
		$message_id = ACYSMS::getCID('message_id');
		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($message_id);

		$app = JFactory::getApplication();

		if(!$app->isAdmin()){
			$frontEndFiltersMinimumSelection = $config->get('frontEndRequiredFilters');
			if(!empty($frontEndFiltersMinimumSelection)){
				JPluginHelper::importPlugin('acysms');
				$dispatcher = JDispatcher::getInstance();
				$answer = new stdClass();
				$answer->result = true;
				$answer->msg = '';

				$dispatcher->trigger('onAcySMSAllowSend_'.$frontEndFiltersMinimumSelection,array($message->message_receiver, &$answer));
				if(!$answer->result){
					$app->enqueueMessage($answer->msg,'notice');
					return parent::preview();
				}
			}
		}
		return parent::summarybeforesend();
	}
}
?>
