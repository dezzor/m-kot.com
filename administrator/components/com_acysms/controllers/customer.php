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
class CustomerController extends ACYSMSController{

	var $aclCat = 'customer';


	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$app = JFactory::getApplication();
		$customerClass = ACYSMS::get('class.customer');

		$status = $customerClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'SMS_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'SMS_ERROR_SAVING' ), 'error');
			if(!empty($customerClass->errors)){
				foreach($customerClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$customerClass = ACYSMS::get('class.customer');
		$app = JFactory::getApplication();

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cids)) return $this->listing();
		$num = $customerClass->delete($cids);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}
}
