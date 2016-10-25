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

class ACYSMSGateway_textlocal_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $hash;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "www.txtlocal.com";
	public $port = 80;


	public $name = 'TextLocal';


	public function openSend($message,$phone){
		$stringToPost = '';
		$config = ACYSMS::config();

		$params = array();
		$params['selectednums'] =  $this->checkNum($phone);
		$params['from'] = $this->from;
		$params['uname'] = $this->username;
		$params['hash'] = $this->hash;
		$params['message'] = $message;
		$params['info'] = 1;
		if(!empty($this->smsid))	$params['custom'] = $this->smsid;
		$params['rcpurl'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=textlocal&pass='.$config->get('pass');


		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "POST /sendsmspost.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.txtlocal.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}


	public function displayConfig(){
		$config = ACYSMS::config();
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_username"><?php echo JText::_('SMS_USERNAME'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][username]" id="senderprofile_username" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->username,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_hash">API Hash</label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][hash]" id="senderprofile_hash" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->hash,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>
		<?php
			if(strpos(ACYSMS_LIVE,'localhost') !== false)	echo JText::_('SMS_LOCALHOST_PROBLEM');
			else{
				echo '<ul id="gateway_addresses">';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','TextLocal').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=textlocal&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(!strpos(strip_tags($res),'Error')){
			return true;
		}else{
			$this->errors[] = strip_tags($res);
			return false;
		}
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /getcredits.php?uname=".$this->username."&hash=".$this->hash." HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.txtlocal.com\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			$app->enqueueMessage(implode('<br />',$this->errors), 'error');
			return false;
		}

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

			if(!strpos($res,'ERR'))		$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',strip_tags($res)), 'message');
			else $app->enqueueMessage(strip_tags($res), 'error');
	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();





		$status['D'] = array(5 ,"Message arrived to handset.");
		$status['I'] = array(-1 ,"Phone number is invalid..");
		$status['U'] = array(-1 ," Message failed to be delivered.");
		$status['?'] = array(-99 ," Message we haven't received a delivery receipt after 4 days.");


		$completed_time = '';
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = strtotime($completed_time);

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("customID",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}
		$apiAnswer->statsdetails_sms_id = (string)$smsId;

		return $apiAnswer;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("created",'');

		$apiAnswer->answer_body = JRequest::getString("content",'');

		$sender = JRequest::getString("sender",'');
		$inNumber = JRequest::getString("inNumber",'');
		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($inNumber))	$apiAnswer->answer_to = '+'.$inNumber;

		$apiAnswer->answer_sms_id = JRequest::getString("refid",'');

		return $apiAnswer;
	}
}
