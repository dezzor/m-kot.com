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
class ACYSMSGateway_smsapipl_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "ssl://ssl.smsapi.pl";
	public $port = 443;

	public $name = 'SMSAPI.PL';

	public function openSend($message,$phone){
		$config = ACYSMS::config();
		$params = array();
		$params['to'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = md5($this->password);
		$params['message'] = $message;
		$params['encoding'] = "utf-8";
		$params['idx'] = $this->smsid;
		$params['eco'] = $this->route;
		$params['max_parts'] = 6;
		if(!empty($this->from))$params['from'] = $this->from;
		if(!strpos(ACYSMS_LIVE,'localhost'))	$params['notify_url'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=smsapipl&pass='.$config->get('pass');




		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sms.do HTTP/1.1\r\n";
		$fsockParameter.= "Host: ssl.smsapi.pl\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);

	}

	public function displayConfig(){
		$config = ACYSMS::config();
		$routeData[] = JHTML::_('select.option', '1', 'Eco', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '0', 'Pro', 'value', 'text');


		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
					<label for="senderprofile_route"><?php echo JText::_('SMS_ROUTE')?></label>
				</td>
				<td>
					<?php echo $routeOptions; ?>
				</td>
			</tr>
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>

	<?php
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

		if(preg_match('#OK:([0-9\.]*)#i', $res, $explodedResults)){
			return true;
		}else{
			if(preg_match('#ERROR:([0-9]*)#i', $res, $explodedResults)){
				$this->errors[] = $this->getErrors(trim($explodedResults[1]));
				return false;
			}
			else{
				$this->errors[] = 'Unknown error : '.$res;
				return false;
			}
		}
	}

	private function displayBalance(){


		$app = JFactory::getApplication();
		$fsockParameter = "GET /sms.do?username=".$this->username."&password=".md5($this->password)."&credits=1 HTTP/1.1 \r\n";
		$fsockParameter.= "Host: ssl.smsapi.pl \r\n";
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


		if(preg_match('#Points: ([0-9\.]*)#i', $res, $explodedResults)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',trim($explodedResults[1]), 'message'));
		}else{
			if(preg_match('#ERROR:([0-9]*)#i', $res, $explodedResults)){
				$app->enqueueMessage($this->getErrors(trim($explodedResults[1])), 'error');
				return false;
			}
		}

	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['8'] = 'Error in request (Please report)';
		$errors['11'] = 'Message too long or there is no message or parameter nounicode is set and special characters (including Polish characters) are used. For VMS messages this error may mean no wave file or tts error text (no text or different than UTF-8 encoding).';
		$errors['12'] = 'Message has more parts than defined in &max_parts parameter.';
		$errors['13'] = 'Invalid phone number';
		$errors['14'] = 'Wrong sender name';
		$errors['17'] = 'FLASH message cannot contain special characters';
		$errors['18'] = 'Invalid number of parameters';
		$errors['19'] = 'Too many messages in one request';
		$errors['20'] = 'Invalid number of IDX parameters';
		$errors['21'] = 'MMS message is too big (maximum 300kB)';
		$errors['22'] = 'Wromg SMIL format';
		$errors['23'] = 'Error in importing a file for MMS or VMS messageB';
		$errors['24'] = 'Wrong format of imported file';
		$errors['25'] = 'Parameters &normalize and &datacoding musn\'t appear in the same request.';
		$errors['26'] = 'MMS subject is to long. Subject may contain up to 30 characters.';
		$errors['27'] = 'Too long idx parameter value.';
		$errors['30'] = 'Wrong UDH parameter when &datacoding=bin';
		$errors['31'] = 'Error in TTS conversion';
		$errors['32'] = 'Eco, MMS i VMS messages may be sent only to Polish numbers or sending messages to non-Polish numbers has been disabled (for Pro messages).';
		$errors['33'] = 'No Polish mobile phone numbers for ECO sending';
		$errors['35'] = 'Wrong tts_lector given. Available values: agnieszka, ewa, jacek, jan, maja';
		$errors['36'] = 'Sending binary messages with footer is disallowed';
		$errors['40'] = 'No group with given name in phonebook';
		$errors['41'] = 'Chosen group is empty';
		$errors['50'] = 'Messages may be scheduled up to 3 months in the future';
		$errors['51'] = 'Wrong VMS sent date, VMS messages may be sent only between 8am and 10pm or combination of parameters try and interval may cause sending one of tries after 10pm';
		$errors['52'] = 'Too many requests for one phone number (maximum 10 requests within 60 seconds';
		$errors['53'] = 'Not unique idx value.';
		$errors['54'] = 'Wrong date - (only unix timestamp and ISO 8601)';
		$errors['55'] = 'No landline numbers in the recipients list and paremeter skip_gsm set';
		$errors['56'] = 'The difference between date sent and expiration date cant be less than 1 and more tha 12 hours.';
		$errors['57'] = 'Phone number is blacklisted';
		$errors['60'] = 'Group of codes with rthis name does not exist';
		$errors['61'] = 'Group of codes are expired';
		$errors['62'] = 'All codes from this group are already used';
		$errors['65'] = 'Not enough unused codes in the group. There is fewer codes in the group that numbers in request.';
		$errors['66'] = 'Parameter [%kod%] the message content is missing. This parameter is necessary for requests with parameter &discount_group.';
		$errors['101'] = 'Invalid authorization info';
		$errors['102'] = 'Invalid username or password';
		$errors['103'] = 'Insufficient credits on Your account';
		$errors['104'] = 'No such template';
		$errors['105'] = 'Wrong IP address (for IP filter turned on)';
		$errors['110'] = 'Service is not available on account (SMS, MMS, VMS or HLR).';
		$errors['200'] = 'Unsuccessful message submission';
		$errors['201'] = 'System internal error (please report)';
		$errors['202'] = 'Too many simultaneous request, message will not be sent';
		$errors['301'] = 'Message with provided ID does not exist or is scheduled for following 60 seconds (such messages cannot be deleted)';
		$errors['400'] = 'Invalid message ID of a status response';
		$errors['999'] = 'System internal error (please report)';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$status['401'] = array(-99, 'Wrong ID or report has expired');
		$status['402'] = array(-2, 'Messages expired');
		$status['403'] = array(1, 'Message is sent');
		$status['404'] = array(5, 'Delivered');
		$status['405'] = array(-1, 'Message is undelivered (invalid number, roaming error etc)');
		$status['406'] = array(-1, 'Sending message falied – please report it to usB');
		$status['407'] = array(-1, 'Message is undelivered (invalid number, roaming error etc)');
		$status['408'] = array(-99,'No report (message may be either delivered or not)');
		$status['409'] = array(4,'Message is waiting to be sent');
		$status['410'] = array(3, 'Message is delivered to operator');


		$completed_time = JRequest::getVar("donedate",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("idx",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}

	public function closeRequest(){
		header('Content-Type: text/html');
		echo "OK";
	}
}
