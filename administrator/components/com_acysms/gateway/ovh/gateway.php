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

class ACYSMSGateway_ovh_gateway extends ACYSMSGateway_default_gateway{

	public $account = 'sms-nic-X';
	public $login;
	public $password;
	public $from = '00336X...';
	public $stop = 1;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "ssl://www.ovh.com";
	public $port = 443;


	public $name = 'OVH';


	public function openSend($message,$phone){

		$params = array();
		$params['message'] = $message;
		$params['account'] = $this->account;
		$params['to'] =  $this->checkNum($phone);
		$params['from'] = $this->from;
		if(empty($this->stop)) $params['noStop'] = '1';
		else $params['noStop'] = '0';
		$params['contentType'] = 'text/json';
		$params['login'] = $this->login;
		$params['password'] = $this->password;

		if($this->unicodeChar($message))
			$params['smscoding']=2; //utf-8
		else
			$params['smscoding']=1; //7bit encoding

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /cgi-bin/sms/http2sms.cgi HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.ovh.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);

	}


	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_account"><?php echo JText::_('SMS_ACCOUNT')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][account]" id="senderprofile_account" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->account,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_login"><?php echo JText::_('SMS_LOGIN')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->login,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>

			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_stoptag"><?php echo JText::_('SMS_STOP_TAG')?><br /><?php echo JText::_('SMS_REQUIRE_COMMERCIAL')?></label>
				</td>
				<td>
					<?php
					echo JHTML::_('select.booleanlist', 'data[senderprofile][senderprofile_params][stop]' , '', intval(@$this->stop));
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>
	<?php
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}

		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

		$jsonExpression = substr($res,strpos($res,"{"),(strrpos($res,"}")-strpos($res,"{"))+1);
		$answer = json_decode($jsonExpression);

		if($answer->status == '100' OR $answer->status == '101') return true;

		$this->errors[] = $answer->status.' : '.$answer->message;
		return false;
	}


	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$detailledStatus[1] = array(-1 ,"Intermediate state notification that the message has not yet been delivered due to a phone related problem but is being retried.");
		$detailledStatus[2] = array(-1 ," Used to indicate that the message has not yet been delivered due to some operator related problem but is being retried within the network.");
		$detailledStatus[3] = array(3 ,"Used to indicate that the message has been accepted by the operator.");
		$detailledStatus[4] = array(5 ,"The message was delivered.");
		$detailledStatus[5] = array(-1 ,"The message has been confirmed as undelivered but no detailed information related to the failure is known.");
		$detailledStatus[6] = array(-1 ,"Cannot determine whether this message has been delivered or has failed due to lack of final delivery state information from the operator.");
		$detailledStatus[8] = array(-2 ,"Used when a message expired (could not be delivered within the life time of the message) within the operator SMSC but is not associated with a reason for failure.");
		$detailledStatus[20] = array(-1 ,"Used when a message in its current form is undeliverable.");
		$detailledStatus[21] = array(-1 ,"Only occurs where the operator accepts the message before performing the subscriber credit check. If there is insufficient credit then the operator will retry the message until the subscriber tops up or the message expires. If the message expires and the last failure reason is related to credit then this error code will be used.");
		$detailledStatus[23] = array(-1 ,"Used when the message is undeliverable due to an incorrect / invalid / blacklisted / permanently barred MSISDN for this operator. This MSISDN should not be used again for message submissions to this operator.");
		$detailledStatus[24] = array(-1 ,"Used when a message is undeliverable because the subscriber is temporarily absent, e.g. their phone is switch off, they cannot be located on the network.");
		$detailledStatus[25] = array(-1 ,"Used when the message has failed due to a temporary condition in the operator network. This could be related to the SS7 layer, SMSC or gateway");
		$detailledStatus[26] = array(-1 ,"Used when a message has failed due to a temporary phone related error, e.g. SIM card full, SME busy, memory exceeded etc. This does not mean the phone is unable to receive this type of message/content (refer to error code 27).");
		$detailledStatus[27] = array(-1 ,"Used when a handset is permanently incompatible or unable to receive this type of message.");
		$detailledStatus[28] = array(-1 ,"Used if a message fails or is rejected due to suspicion of SPAM on the operator network. This could indicate in some geographies that the operator has no record of the mandatory MO required for an MT.");
		$detailledStatus[29] = array(-1 ,"Used when this specific content is not permitted on the network / shortcode.");
		$detailledStatus[33] = array(-1 ,"Used when the subscriber cannot receive adult content because of a parental lock.");
		$detailledStatus[39] = array(-1 ,"New operator failure");
		$detailledStatus[73] = array(-1 ,"The message was failed due to the ported combinations being unreachable.");
		$detailledStatus[74] = array(-1 ,"The message was failed due to the MSISDN being roaming.");
		$detailledStatus[76] = array(-1 ,"The message was failed due to the ported combinations being blocked for client (the client has been blacklisted from the ported destination).");
		$detailledStatus[202] = array(-1 ,"The message was failed due to the ported combinations being blocked for the client. Please contact Client Support for additional information.");

		$status = array();
		$status[0] = 1;
		$status[1] = 5;
		$status[2] = -1;
		$status[4] = 4;
		$status[8] = 4;
		$status[16] = -1;


		$completed_time = JRequest::getVar("date",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("description",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		$detailledErrorCode = JRequest::getVar("ptt",'');

		if(!empty($messageStatus) && !empty($status[$messageStatus])){
			$apiAnswer->statsdetails_status = $status[$messageStatus];
			if(!empty($detailledStatus[$detailledErrorCode])) $apiAnswer->statsdetails_error[] = $detailledStatus[$detailledErrorCode][1];
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}


	protected function checkNum($phone){
		$internationalPhone = str_replace('+', '00', $phone);
		return preg_replace('#[^0-9]#','',$internationalPhone);
	}

}
