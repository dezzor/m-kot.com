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
class ACYSMSGateway_hqsms_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = 'ssl://ssl.hqsms.com';
	public $port = 443;


	public $name = 'HQSMS';

	public function openSend($message,$phone){
		$config = ACYSMS::config();
		$params = array();


		$params['to'] =  $this->checkNum($phone);
		$params['username'] =  $this->username;
		$params['password'] = md5($this->password);
		$params['message'] =  $message;
		if(!empty($this->from))	$params['from'] =  $this->from;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "POST /sms.do HTTP/1.1\r\n";
		$fsockParameter.= "Host: ssl.hqsms.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		$result = $this->sendRequest($fsockParameter);
		if(!$result && strpos(implode(',', $this->errors),'Connection timed out') !== false && $this->port != '80'){
			$this->errors[] = 'It seems that the port you choose is blocked on you server. You should try to select the port 80';
		}
		return $result;
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
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','HQSMS').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=hqsms&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	protected function interpretSendResult($result){
		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		$split = explode(':',$res);

		if($split[0] == 'OK'){
			$this->smsid = $split[1];
			return true;
		}
		$this->errors[] = $this->getErrors($split[1]);
		return false;
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['0'] = 'In progress (a normal message submission, with no error encountered so far).';
		$errors['8'] = ' Error in request (Please report)';
		$errors['11'] = 'Message too long or there is no message or parameter nounicode is set and special characters (including Polish characters) are used.';
		$errors['12'] = 'Message has more parts than defined in &max_parts parameter.';
		$errors['13'] = 'Lack of valid phone numbers (invalid or blacklisted numbers)';
		$errors['14'] = 'Wrong sender name';
		$errors['17'] = 'FLASH message cannot contain special characters';
		$errors['18'] = 'Invalid number of parameters';
		$errors['19'] = 'Too many messages in one request';
		$errors['20'] = 'Invalid number of IDX parameters';
		$errors['25'] = 'Parameters &normalize and &datacoding musn t appear in the same request.';
		$errors['30'] = 'Too long IDX parameter. Maximum 255 chars or Wrong UDH parameter when &datacoding=bin';
		$errors['40'] = 'No group with given name in phonebook';
		$errors['41'] = 'Chosen group is empty';
		$errors['50'] = 'Messages may be scheduled up to 3 months in the future';
		$errors['52'] = 'Too many attempts of sending messages to one number (maximum 10 attempts whin 60s)';
		$errors['53'] = 'Not unique idx parameter, message with the same idx has been already sent and &check_idx=1.';
		$errors['54'] = 'Wrong date - (only unix timestamp and ISO 8601)';
		$errors['56'] = 'The difference between date sent and expiration date can t be less than 1 and more than 12 hours';
		$errors['70'] = 'Invalid URL in notify_url parameter.';
		$errors['72'] = 'Parameter notify_url may be used only in requests with one recipient s number, it cannot be used for mass message sending.';
		$errors['101'] = 'Invalid authorization info';
		$errors['102'] = 'Invalid username or password';
		$errors['103'] = 'Insufficient credits on Your account';
		$errors['104'] = 'No such template';
		$errors['105'] = 'Wrong IP address (for IP filter turned on)';
		$errors['200'] = 'Unsuccessful message submission';
		$errors['201'] = 'System internal error (please report)';
		$errors['202'] = 'Too many simultaneous request, message won t be sent';
		$errors['301'] = 'ID of messages doesn t exist';
		$errors['400'] = 'Invalid message ID of a status response';
		$errors['999'] = 'System internal error (please report)';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status[401] = array(-99 ,"Wrong ID or report has expired");
		$status[402] = array(-2 ,"Messages expired");
		$status[404] = array(5 ,"Message is delivered to recipient");
		$status[405] = array(-1 ,"Message is undelivered (invalid number, roaming error etc)");
		$status[406] = array(-1 ,"Sending message failed – please report it to us");
		$status[407] = array(-1 ,"Message is undelivered (invalid number, roaming error etc)");
		$status[408] = array(-1 ,"No report (message may be either delivered or not)");
		$status[409] = array(4 ,"Message is waiting to be sent");
		$status[410] = array(3 ,"Message is delivered to operator");

		$completed_time = JRequest::getVar("donedate",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("MsgId",'');
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
		echo "OK";
	}
}
