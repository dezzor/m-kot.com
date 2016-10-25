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
class ACYSMSGateway_envoyersmspro_gateway extends ACYSMSGateway_default_gateway{

	public $login;
	public $password;
	public $sendername;
	public $waittosend = 0;
	public $accountid;
	public $stop = 1;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = 'api.envoyersmspro.com';
	public $port = 80;

	public $name = 'envoyerSMSpro';

	public function openSend($message,$phone){

		$params = array();
		$params['recipients'] = $this->checkNum($phone);
		$params['login'] = $this->login;
		$params['sendername'] = $this->sendername;
		$params['password'] = $this->password;
		$params['stopsms'] = $this->stop;
		$params['longmessageallowed'] = 1;
		$params['text'] = $message;
		if(!empty($this->sendername))$params['sendername'] = $this->sendername;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api/message/send HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.envoyersmspro.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		$result = $this->sendRequest($fsockParameter);
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_sendername"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sendername]" id="senderprofile_sendername" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sendername,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_username"><?php echo JText::_('SMS_LOGIN'); ?></label>
				</td>
				<td>
					<input placeholder="<?php echo JText::_('SMS_PHONE') ?>" type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->login,ENT_COMPAT, 'UTF-8');?>" />
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

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	protected function interpretSendResult($result){
		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(!preg_match('#<status>(.*)</status>#Ui', $res, $status)) return false;

		if ($status[1]=='success'){
			preg_match('#<message_id>(.*)</message_id>#Ui', $res, $smsid);
			$this->smsid = $smsid[1] ;
			return true;
		}else{
			if(preg_match('#<error_id>(.*)</error_id>#Ui', $res, $errors))
				$this->errors[] = $this->getErrors($errors[1]);
			return false;
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /api/account/status?login=".$this->login."&password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.envoyersmspro.com \r\n";
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

		if(!preg_match('#<status>(.*)</status>#Ui', $res, $status)) return false;

		if($status[1]=='success'){
			if(preg_match('#<sms_remaining>(.*)</sms_remaining>#Ui', $res, $smsremaining));
				$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$smsremaining[1]), 'message');
		}else{
			preg_match('#<error>(.*)</error>#Ui', $res, $error);
			$app->enqueueMessage($error[1],'error');
			return false;
		}
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['1'] = 'Authentication failed';
		$errors['2'] = 'Missing parameters';
		$errors['3'] = 'Bad value of parameters';
		$errors['4'] = 'Message too long';
		$errors['5'] = 'Not enough SMS';
		$errors['7'] = 'Unknown messageid';
		$errors['8'] = 'Message is pending';
		$errors['9'] = 'Message is being sent';
		$errors['10'] = 'Message has been sent';
		$errors['11'] = 'Message is already cancelled';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

}
