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
class AnswerTriggerController extends ACYSMSController{

	var $pkey = 'answertrigger_id';
	var $table = 'answertrigger';
	var  $orderingColumnName = 'answertrigger_ordering';
	var $aclCat = 'answers_trigger';

	function copy(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('answers_trigger','copy')) return;


		$db = JFactory::getDBO();
		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cids)) return $this->listing();
		foreach($cids as $oneId){
			$query = 'INSERT INTO `#__acysms_answertrigger` (`answertrigger_name`, `answertrigger_description`,`answertrigger_actions`,`answertrigger_triggers`,`answertrigger_publish`)';
			$query .= " SELECT CONCAT('copy_',`answertrigger_name`),`answertrigger_description`,`answertrigger_actions`,`answertrigger_triggers`,`answertrigger_publish` FROM `#__acysms_answertrigger` WHERE `answertrigger_id` = ".intval($oneId);
			$db->setQuery($query);
			$db->query();
		}
		return $this->listing();
	}

	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('answers_trigger','manage')) return;

		$app = JFactory::getApplication();
		$answerTriggerClass = ACYSMS::get('class.answertrigger');

		$status = $answerTriggerClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'SMS_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'SMS_ERROR_SAVING' ), 'error');
			if(!empty($categoryClass->errors)){
				foreach($categoryClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('answers_trigger','delete')) return;

		$answerTriggerClass = ACYSMS::get('class.answertrigger');
		$app = JFactory::getApplication();

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cids)) return $this->listing();
		$num = $answerTriggerClass->delete($cids);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}
}
