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

class ACYSMSGateway_nexmo_gateway extends ACYSMSGateway_default_gateway{

	public $from;
	public $key;
	public $secret;
	public $smsid;
	public $waittosend= 0;

	public $errors = array();
	public $debug = false;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "rest.nexmo.com";
	public $port = 80;


	public $name = 'Nexmo';

	public function openSend($message,$phone){
		$params = array();

		if($this->unicodeChar($message)){
			$params['type'] = 'unicode';
		}

		$params['text'] = $message;
		$params['to'] =  $this->checkNum($phone);
		$params['from'] = $this->from;
		$params['api_key'] = $this->key;
		$params['api_secret'] = $this->secret;
		$params['status-report-req'] = 1;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sms/xml HTTP/1.1\r\n";
		$fsockParameter.= "Host: rest.nexmo.com\r\n";
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
					<label for="senderprofile_key"><?php echo JText::_('SMS_KEY')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][key]" id="senderprofile_key" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->key,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_secret"><?php echo JText::_('SMS_SECRET')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][secret]" id="senderprofile_secret" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->secret,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>

			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" maxlength="11" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','Nexmo').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=nexmo&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Nexmo').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=nexmo&pass='.$config->get('pass').'</li>';
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

		$errors = array();
		$errors['1'] = 'You have exceeded the submission capacity allowed on this account, please back-off and retry';
		$errors['2'] = 'Your request is incomplete and missing some mandatory parameters';
		$errors['3'] = 'The value of one or more parameters is invalid';
		$errors['4'] = 'The username / password you supplied is either invalid or disabled';
		$errors['5'] = 'An error has occurred in the nexmo platform whilst processing this message';
		$errors['6'] = 'The Nexmo platform was unable to process this message, for example, an un-recognized number prefix';
		$errors['7'] = 'The number you are trying to submit to is blacklisted and may not receive messages';
		$errors['8'] = 'The username you supplied is for an account that has been barred from submitting messages';
		$errors['9'] = 'Your pre-pay account does not have sufficient credit to process this message';
		$errors['10'] = 'The number of simultaneous connections to the platform exceeds the capabilities of your account';
		$errors['11'] = 'This account is not provisioned for REST submission, you should use SMPP instead';
		$errors['12'] = 'Applies to Binary submissions, where the length of the UDH and the message body combined exceed 140 octets';

		if(preg_match('#<messageId>(.*)</messageId>#Ui', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
		}

		if(preg_match('#<status>(.*)</status>#Ui', $res, $explodedResults)){
			if($explodedResults[1] == '0') return true;
			else{
				$this->errors[] = isset($errors[$explodedResults[1]]) ? $errors[$explodedResults[1]] : 'Unknown error : '.$res;
				return false;
			}
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$stringToPost = urlencode($this->key)."/".urlencode($this->secret);
		$page = '/account/get-balance/';

		$fsockParameter = "GET ".$page.$stringToPost." HTTP/1.1\r\n";
		$fsockParameter .=	"Host: rest.nexmo.com\r\n";
		$fsockParameter .=	"Accept: application/xml\r\n";
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

		if(preg_match('#<value>(.*)</value>#Ui', $res, $explodedResults)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',trim($explodedResults[1]), 'message'));
		}else{
				$app->enqueueMessage('Error : There is an error with your Key or your secret var..','warning');
		}
	}

	protected function checkNum($phone){
		$internationalPhone = str_replace('+', '00', $phone);
		return preg_replace('#[^0-9]#','',$internationalPhone);
	}

	function deliveryReport(){

		$status = array();
		$errors = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$errors[0] = "Delivered";
		$errors[1] = "Unknown";
		$errors[2] = "Absent Subscriber - Temporary";
		$errors[3] = "Absent Subscriber - Permenant";
		$errors[4] = "Call barred by user";
		$errors[5] = "Portability Error";
		$errors[6] = "Anti-Spam Rejection";
		$errors[7] = "Handset Busy";
		$errors[8] = "Network Error";
		$errors[9] = "Illegal Number";
		$errors[10] = "Invalid Message";
		$errors[11] = "Unroutable";
		$errors[99] = "General Error";

		$status['delivered'] = array(5 ,"Message arrived to handset.");
		$status['expired'] = array(-2 ,"Message timed out after we waited 48h to receive status from mobile operator.");
		$status['failed'] = array(-1 ," Message failed to be delivered.");
		$status['accepted'] = array(3 ,"Message has been accepted by the mobile operator.");
		$status['buffered'] = array(4 ,"Message is being delivered.");
		$status['unknown'] = array(-99 ,"Un documented status from the mobile operator.");


		$completed_time = JRequest::getCmd("message-timestamp",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = strtotime($completed_time);

		$messageStatus = JRequest::getCmd("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getCmd("messageId",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}

		$errorCode = JRequest::getVar("err-code",'');
		if(!empty($errorCode)){
			if(isset($errors[$errorCode])) $apiAnswer->statsdetails_error[] = 'Error Code detected : '.$errors[$errorCode];
			else $apiAnswer->statsdetails_error[] = 'Unknow Error Code detected : '.$errorCode;
		}
		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}


	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$answer_date = JRequest::getString("message-timestamp",'');
		if(empty($answer_date)){
			$apiAnswer->answer_date = time();
		}else $apiAnswer->answer_date = strtotime($answer_date);

		$apiAnswer->answer_body = JRequest::getString("text",'');

		$msisdn = JRequest::getString("msisdn",'');
		$to = JRequest::getString("to",'');

		if(!empty($msisdn)) $apiAnswer->answer_from = '+'.$msisdn;
		if(!empty($to)) $apiAnswer->answer_to = '+'.$to;

		$apiAnswer->answer_sms_id = JRequest::getString("messageId",'');

		return $apiAnswer;
	}

	public function closeRequest(){
		echo "OK";
	}

}
