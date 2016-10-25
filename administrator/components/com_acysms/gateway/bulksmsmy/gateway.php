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
class ACYSMSGateway_bulksmsmy_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $connectionInformations;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "login.bulksms.my";

	public $name = 'BulkSMS.my';


	public function openSend($message,$phone){

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['type'] = 1;
		if($this->unicodeChar($message)){
			$arr = unpack('H*hex', iconv('UTF-8', 'UCS-2BE', $message));
			$message = strtoupper($arr['hex']);
			$params['type'] = '3';
		}

		$params['mobile'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['message'] = $message;
		if(!empty($this->senderid))$params['sender'] = $this->senderid;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /websmsapi/ISendSMS.aspx?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain."\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

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
		</table>
		<?php
			if(strpos(ACYSMS_LIVE,'localhost') !== false)	echo JText::_('SMS_LOCALHOST_PROBLEM');
			else{
				echo '<ul id="gateway_addresses">';
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','BulkSMS.my').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=bulksmsmy&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','BulkSMS.my').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=bulksmsmy&pass='.$config->get('pass').'</li>';
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




		$split = explode(':',$res);

		if(isset($split[1]) && !empty($split[1])){
			$this->smsid = $split[1];
		}
		if($split[0] == '1701'){
			return true;
		}

		$res = $split[0];
		$this->errors[] = $this->getErrors($res);
		return false;

	}

	private function displayBalance(){
		$app = JFactory::getApplication();

		$fsockParameter = "GET /websmsapi/creditsLeft.aspx?username=".$this->username."&password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain." \r\n";
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


		$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res), 'message');

	}

	protected function getErrors($errNo){
		$errors = array();
		$errors[1701] = "Message sent successfully";
		$errors[1702] = "Invalid username or password";
		$errors[1703] = "Internal server error";
		$errors[1704] = "Insufficient credits";
		$errors[1705] = "Invalid Mobile Number";
		$errors[1706] = "Invalid Message / Invalid SenderID";
		$errors[1718] = "Duplicate record received";
		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();





		$status[6] = array(5 ,"Deliver Successful");
		$status[7] = array(-1 ,"Deliver Failed");
		$status[8] = array(-2 ,"Expired");
		$status[9] = array(-99 ,"Unknown");
		$status[10] = array(0 ,"Rejected");

		$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
		$apiAnswer->statsdetails_received_date = time();

		$messageStatus = JRequest::getVar("Status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("MsgId",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the Message ID';

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

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = time();

		$apiAnswer->answer_body = JRequest::getString("Msg",'');

		$contentType = JRequest::getInt("ContentType");
		if(!empty($contentType) && $contentType ==3){
			$apiAnswer->answer_body=pack('H*', $apiAnswer->answer_body);
		}


		$sender = JRequest::getString("Sender",'');
		$msisdn = JRequest::getString("ShortCode",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($msisdn))	$apiAnswer->answer_to = '+'.$msisdn;

		return $apiAnswer;
	}
}
