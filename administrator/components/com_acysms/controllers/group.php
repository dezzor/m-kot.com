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

class GroupController extends acysmsController{

	var $pkey = 'group_id';
	var $table = 'group';
	var $orderingColumnName = 'group_ordering';
	var $aclCat = 'groups';

	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('groups','manage')) return;

		$app = JFactory::getApplication();
		$groupClass = ACYSMS::get('class.group');
		$status = $groupClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			$app->enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($groupClass->errors)){
				foreach($groupClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('groups','delete')) return;

		$app = JFactory::getApplication();
		$group_ids = JRequest::getVar( 'cid', array(), '', 'array' );

		$groupClass = ACYSMS::get('class.group');
		$num = $groupClass->delete($group_ids);

		$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');

		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}

	function choose(){
		if(!$this->isAllowed('groups','manage')) return;
		JRequest::setVar( 'layout', 'choose'  );
		return parent::display();
	}
}
