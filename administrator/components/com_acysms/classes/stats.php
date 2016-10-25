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
class ACYSMSstatsClass extends ACYSMSClass{

	var $tables = array('statsdetails' => 'statsdetails_message_id', 'stats'=>'stats_message_id');
	var $pkey = 'stats_message_id';

	function addDeliveryInformations($apiAnswer){
		if(empty($apiAnswer->statsdetails_sms_id)){
			$cronHelper = ACYSMS::get('helper.cron');
			$cronHelper->messages = array('DELIVERY REQUEST : NO ID');
			$cronHelper->detailMessages = array(print_r($_REQUEST, true));
			$cronHelper->saveReport();
			return false;
		}
		if(!empty($apiAnswer->statsdetails_error) && is_array($apiAnswer->statsdetails_error))	$apiAnswer->statsdetails_error = implode(',', $apiAnswer->statsdetails_error);
		$db = JFactory::getDBO();

		$status = $db->updateObject(ACYSMS::table('statsdetails'),$apiAnswer,'statsdetails_sms_id');

		if($db->getAffectedRows() == 0){

			sleep(10);
			$status = $db->updateObject(ACYSMS::table('statsdetails'),$apiAnswer,'statsdetails_sms_id');
			if($db->getAffectedRows() == 0){
				$cronHelper = ACYSMS::get('helper.cron');
				$cronHelper->messages = array('DELIVERY REQUEST ISSUE : No stats details information were updated => '.print_r($apiAnswer, true));
				$cronHelper->detailMessages = array(print_r($_REQUEST, true));
				$cronHelper->saveReport();
			}
		}
	}
}
