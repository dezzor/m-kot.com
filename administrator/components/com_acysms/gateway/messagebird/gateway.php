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
class ACYSMSGateway_messagebird_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $sender;
	public $waittosend = 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "api.messagebird.com";

	public $name = 'MessageBird';


	public function openSend($message,$phone){

		$params = array();
		$params['destination'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['body'] = $this->checkMessage($message);
		$params['reference'] = $this->smsid = 'ACYSMS_'.time().'_'.$this->username.'_'.$params['destination'];
		$params['responsetype'] = 'PLAIN';
		if(!empty($this->sender))	$params['sender'] = $this->sender;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /api/sms?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain."\r\n";
		$fsockParameter.= "Content-type: application/xwww-form-urlencode\r\n\r\n";
		$result = $this->sendRequest($fsockParameter);
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_sender"><?php echo JText::_('SMS_SENDER'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender]" id="senderprofile_sender" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender,ENT_COMPAT, 'UTF-8');?>" />
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
		if(strpos(ACYSMS_LIVE,'localhost') !== false)	echo JText::_('SMS_LOCALHOST_PROBLEM');
			else{
				echo '<ul id="gateway_addresses">';
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','MessageBird').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=messagebird&pass='.$config->get('pass').'</li>';
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

		$split = explode('|',$res);
		if($split[0] == '01'){
			return true;
		}

		$res = $split[0];
		$this->errors[] = $this->getErrors($res);
		return false;

	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /api/credits?username=".$this->username."&password=".$this->password." HTTP/1.1\r\n";
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

		if (preg_match('#<credits>(.*)</credits>#Ui', $res, $credits))
			$nbCredit = $credits[1];
		if (preg_match('#<responseCode>(.*)</responseCode>#Ui', $res, $responseCode))
			$nbError = $responseCode[1];
		if(!isset($nbError))
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',(int)$nbCredit), 'message');
		else{
			if((string)$nbError!='01') //we have an error
				$app->enqueueMessage($this->getErrors((string)$nbError),'error');
			else //we don't have an error but we don't have credits
				$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',0), 'message');
			return false;
		}
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['01'] = 'Request has been processed	successfully';
		$errors['69'] = 'The message cannot be scheduled on this date';
		$errors['70'] = 'An incorrect timestamp notation has been used';
		$errors['72'] = 'The message is too long';
		$errors['89'] = 'Invalid sender';
		$errors['93'] = 'One or several receivers are invalid';
		$errors['95'] = 'No	message	has	been selected';
		$errors['96'] = 'The number of credits is insufficient';
		$errors['97'] = 'Invalid username and/or password';
		$errors['98'] = 'Your ip address is not authorized based on this account';
		$errors['99'] = 'Cannot	connect	to	the	server';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status['delivered'] = array(5 ,"Delivered to mobile");
		$status['not delivered'] = array(-1 ,"The message could	not	be delivered");
		$status['buffered'] = array(4 ,"The message is on hold (phone cannot be reached/is off)");


		$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
		$apiAnswer->statsdetails_received_date = time();


		$messageStatus = strtolower(JRequest::getVar("STATUS",''));
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("REFERENCE",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message reference';

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
}
