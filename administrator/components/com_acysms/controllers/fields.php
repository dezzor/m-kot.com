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
class FieldsController extends acysmsController{
	var $pkey = 'fields_fieldid';
	var $table = 'fields';
	var  $orderingColumnName = 'fields_ordering';
	var $groupMap = '';
	var $groupVal = '';

	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('configuration','manage')) return;

		$app = JFactory::getApplication();

		$class = ACYSMS::get('class.fields');
		$status = $class->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'SMS_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'SMS_ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('configuration','manage')) return;

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );

		$class = ACYSMS::get('class.fields');
		$num = $class->delete($cids);

		if($num){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');
		}

		return $this->listing();
	}

	function choose(){
		if(!$this->isAllowed('configuration','manage')) return;
		JRequest::setVar( 'layout', 'choose' );
		return parent::display();
	}

}
