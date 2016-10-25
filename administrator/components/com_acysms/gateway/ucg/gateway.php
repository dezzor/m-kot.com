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
class ACYSMSGateway_ucg_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = 'api.infobip.com';
	public $port = 80;

	public $name = 'UCG';

	public function openSend($message,$phone){
		$config = ACYSMS::config();

		$params = array();

		$params['GSM'] = $this->checkNum($phone);
		$params['user'] = $this->username;
		$params['password'] = $this->password;
		$params['encoding'] = 'UTF-8';
		$params['pushurl'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=ucg&pass='.$config->get('pass');


		if($this->unicodeChar($message))	$params['DataCoding'] = '8';

		$params['SMSText'] = $message;
		if(strlen($message) > 160 )	$params['type'] = 'LongSMS';
		if(!empty($this->senderid))	$params['sender'] = $this->senderid;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "GET /api/v3/sendsms/plain?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.infobip.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$result = $this->sendRequest($fsockParameter);
		return $result;
	}

	public function displayConfig(){
	?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
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
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(preg_match('#<status>(.*)</status>#Ui', $res, $explodedResults)){
			if($explodedResults[1] == 0 ){
				if(preg_match('#<messageid>(.*)</messageid>#Ui', $res, $results)){
					$this->smsid = $results[1];
				}
				return true;
			}
			$this->errors[] = $this->getErrors($explodedResults[1]);
			return false;
		}
		return false;
	}


	protected function getErrors($errNo){
		$errors = array();

		$errors['0'] = 'Request was successful (all recipients)';
		$errors['-1'] = 'Error in processing the request';
		$errors['-2'] = 'Not enough credits on a specific account';
		$errors['-5'] = 'Username or password is invalid';
		$errors['-6'] = 'Destination address is missing in the request';
		$errors['-10'] = 'Username is missing in the request';
		$errors['-11'] = 'Password is missing in the request';
		$errors['-13'] = 'Number is not recognized by UCG platform';
		$errors['-22'] = 'Incorrect XML format, caused by syntax error';
		$errors['-23'] = 'General error, reasons may vary';
		$errors['-26'] = 'General API error, reasons may vary';
		$errors['-27'] = 'Invalid scheduling parametar';
		$errors['-28'] = 'Invalid PushURL in the request';
		$errors['-30'] = 'Invalid APPID in the request';
		$errors['-33'] = 'Duplicated MessageID in the request';
		$errors['-34'] = 'Sender name is not allowed';
		$errors['-99'] = 'Error in processing request, reasons may vary';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status['NOT_SENT'] = array(0, "The message is queued in the UCG system but cannot be submitted to SMSC (possiblereason : SMSC connection is down)");
		$status['SENT'] = array(1, "The message was sent over a route that does not support delivery reports");
		$status['NOT_DELIVERED'] = array(-1, "The message could not be delivered");
		$status['DELIVERED'] = array(5, "The message was successfully delivered to the recipient");
		$status['NOT_ALLOWED'] = array(-1 ,"The client has no authorization to send to the specified network (the message willnot be charged)");
		$status['INVALID_DESTINATION_ADDRESS'] = array(-1 ,"Invalid/incorrect GSM recipient");
		$status['INVALID_SOURCE_ADDRESS'] = array(-1 ,"You have specified incorrect /invalid/not allowed source address (sender name)");
		$status['ROUTE_NOT_AVAILABLE'] = array(-1 ,"You are trying to use routing that is not available for your account");
		$status['NOT_ENOUGH_CREDITS'] = array(-1 ,"There are no available credits on your account to send the message");
		$status['REJECTED'] = array(-1 ,"Message has been rejected, reasons may vary");
		$status['INVALID_MESSAGE_FORMAT'] = array(-1 ,"Your message has invalid format");

		$postData = file_get_contents("php://input");
		$dom = new DOMDocument();
		$dom->loadXML($postData);
		$xPath = new domxpath($dom);
		$reports = $xPath->query("/DeliveryReport/message");

		foreach ($reports as $node) {
			$completed_time = $node->getAttribute('donedate');
			if(empty($completed_time)){
				$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
				$apiAnswer->statsdetails_received_date = time();
			}else $apiAnswer->statsdetails_received_date = $completed_time;

			$messageStatus = $node->getAttribute('status');
			if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

			$smsId = $node->getAttribute('id');
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
		}

		return $apiAnswer;
	}


	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("Datetime",'');

		$apiAnswer->answer_body = JRequest::getString("Text",'');

		$sender = JRequest::getString("Sender",'');
		$receiver = JRequest::getString("Receiver",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($receiver))	$apiAnswer->answer_to = '+'.$receiver;

		$apiAnswer->answer_sms_id = JRequest::getString("MessageId",'');

		return $apiAnswer;
	}
}
