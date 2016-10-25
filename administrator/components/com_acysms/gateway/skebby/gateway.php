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

class ACYSMSGateway_skebby_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $sender_string;
	public $method;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "gateway.skebby.it";
	public $port = 80;

	public $name = 'Skebby';

	public function openSend($message,$phone){

		$params = array();
		$params['recipients'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['text'] = $message;
		$params['method'] = $this->method;
		$params['sender_string'] = $this->sender_string;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api/send/smseasy/advanced/http.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: gateway.skebby.it\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}


	public function displayConfig(){
		$config = ACYSMS::config();

		$method = array();
		$method[] = JHTML::_('select.option', 'send_sms_basic', 'Basic SMS', 'value', 'text');
		$method[] = JHTML::_('select.option', 'send_sms_classic', 'Classic SMS', 'value', 'text');
		$method[] = JHTML::_('select.option', 'send_sms_classic_report', 'Classic Plus SMS with delivery report', 'value', 'text');


		$methodOption =  JHTML::_('select.genericlist', $method, "data[senderprofile][senderprofile_params][method]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->method);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_sendername"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender_string]" id="senderprofile_sender_string" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender_string,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
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
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_method"><?php echo JText::_('SMS_ROUTE')?></label>
				</td>
				<td>
					<?php echo $methodOption; ?>
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','Skebby').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=skebby&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Skebby').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=skebby&pass='.$config->get('pass').'</li>';
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

		$match = '#([a-z_]+)=([0-9a-z\.]+)#i';
		if(!preg_match_all($match,$res,$results)){
			$this->errors[] = $res;
			return false;
		}
		else{
			$msgFailed = false;
			foreach($results[1] as $i => $val){
				if($val == 'status' && $results[2][$i] == 'failed') $msgFailed = true;
				if($val == 'code' && $msgFailed){
					 $this->errors[] = $this->getErrors($results[2][$i]);
					 return false;
				}
				if($val == 'id') $this->smsid = $results[2][$i];
			}
			return true;
		}
	}

	private function displayBalance(){


		$app = JFactory::getApplication();
		$fsockParameter = "GET /api/send/smseasy/advanced/http.php?method=get_credit&username=".$this->username."&password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: gateway.skebby.it \r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

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

		$match = '#([a-z_]+)=([0-9\.]+)#i';
		if(!preg_match_all($match,$res,$results)) return false;

		foreach($results[1] as $i => $val){
			if($val == 'code'){
				 $app->enqueueMessage($this->getErrors($results[2][$i]));
				 return;
			}
			if($val == 'credit_left') $app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$results[2][$i]), 'message');
			else $app->enqueueMessage(str_replace('_',' ',$val).' : '.$results[2][$i], 'message');
		}
	}

	function getErrors($errNo){
		$errors = array();
		$errors['10'] = 'Generic error';
		$errors['11'] = 'Invalid charset';
		$errors['20'] = 'A compulsory parameter has not been specified';
		$errors['21'] = 'Invalid parameters';
		$errors['22'] = 'Invalid sender ID';
		$errors['23'] = 'Sender ID too long (more than 11 characters)';
		$errors['24'] = 'Invalid recipient';
		$errors['25'] = 'Sender ID not set';
		$errors['26'] = 'Invalid recipient';
		$errors['27'] = 'Too many recipients';
		$errors['29'] = 'Account not set to use gateway SMS';
		$errors['30'] = 'Not enough credit to send the message';
		$errors['31'] = 'Only request HTTP with POST method accepted';
		$errors['32'] = 'Invalid delivery_start format, use format RFC 2822 es: Mon, 15 Aug 2005 15:52:01 +0000';
		$errors['33'] = 'Invalid encoding_scheme, values accepted: normal, ucs2 view http://en.wikipedia.org/wiki/GSM_03.38 for more information. ';
		$errors['34'] = 'Invalid validity_period, must be integer (express in minutes) bigger than 0 and smaller than 2880 (2 days)';
		$errors['35'] = 'Invalid user_reference, maximum lenght allowed 20 characters [a-zA-Z0-9-_+:;';
		$errors['36'] = 'If you have set the delivery_start and you want the delivery report you have to specify the user_reference too ';
		$errors['37'] = 'Some characters in the text are not valid for the charset specified, please check encoding parameter ';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$status['DELIVERED'] = array(5 ,"Delivered to mobile");
		$status['EXPIRED'] = array(-1 ,"Message expired (device off/not reachable");
		$status['DELETED'] = array(-1 ,"Operator network error");
		$status['UNDELIVERABLE'] = array(-1 ,"Message undelivered (View below error_code variable)");
		$status['UNKNOWN'] = array(-99 ,"Generic error");
		$status['REJECTD'] = array(-99 ,"Message rejected from operator");

		$errors[401] =	"Message expired (device off/not reachable)";
		$errors[201] =	"Operator network malfunctioning";
		$errors[203] =	"Recipient unreachable (in roaming)";
		$errors[301] =	"Invalid recipient (nonexistent/on portability/not enabled)";
		$errors[302] =	"Wrong number";
		$errors[303] =	"SMS service not enabled";
		$errors[304] =	"Text identified as spam";
		$errors[501] =	"Device doesn't support SMS";
		$errors[502] =	"Device with memory full";
		$errors[901] =	"Mapping error malfunctioning";
		$errors[902] =	"Service temporary unavailable";
		$errors[903] =	"No operator available";
		$errors[904] =	"No text in the message";
		$errors[905] =	"Recipient not valid";
		$errors[906] =	"Duplicated recipients";
		$errors[907] =	"Message filled in incorrectly";
		$errors[909] =	"Wrong User_ref";
		$errors[910] =	"Text too long";
		$errors[101] =	"Generic operator malfunctioning";
		$errors[202] =	"Message rejected from operator";



		$completed_time = JRequest::getVar("completed_time",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("skebby_dispatch_id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			if($apiAnswer->statsdetails_status == -1){
				$errorCode = JRequest::getVar("error_code",'');
				if(!empty($errorCode) && isset($errors[$errorCode]))	$apiAnswer->statsdetails_error[] = $errors[$errorCode];
			}

		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("timestamp",'');

		$apiAnswer->answer_body = JRequest::getString("text",'');

		$sender = JRequest::getString("sender",'');
		$receiver = JRequest::getString("receiver",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($receiver))	$apiAnswer->answer_to = '+'.$receiver;

		return $apiAnswer;
	}
}
