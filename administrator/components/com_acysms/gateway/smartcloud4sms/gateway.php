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
class ACYSMSGateway_smartcloud4sms_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $sender;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = 'smartcloud4sms.com';
	public $port = 80;


	public $name = 'Smartcloud4sms';

	public function openSend($message,$phone){

		$params = array();

		$params['lang'] = 'en';
		if($this->unicodeChar($message)){
			$params['lang'] = 'ar';
		}

		$params['numbers'] = $this->checkNum($phone);
		$params['user'] = $this->username;
		$params['pass'] = $this->password;
		$params['text'] = $message;
		$params['do'] = 'send';
		if(!empty($this->sender))	$params['sender'] = $this->sender;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "GET /sms/smpp2.php?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: smartcloud4sms.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$result = $this->sendRequest($fsockParameter);
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
					<label for="senderprofile_sender"><?php echo JText::_('SMS_SENDER'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender]" id="senderprofile_sender" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender,ENT_COMPAT, 'UTF-8');?>" />
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


		if($res > 10) return true;
		$this->errors[] = $this->getErrors($res);
		return false;
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['0'] = 'over maximum 50 nums';
		$errors['1'] = 'security error (you must check the sms conditions)';
		$errors['2'] = 'error in mobile numbers';
		$errors['3'] = 'exceed your sms count in your account';
		$errors['4'] = 'name sender error';
		$errors['5'] = 'error in login (such as user or pass error or account expired or units=0 )';
		$errors['6'] = 'error in lang for sms';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}



	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /sms/smpp2.php?user=".$this->username."&pass=".$this->password."&do=login HTTP/1.1\r\n";
		$fsockParameter.= "Host: smartcloud4sms.com\r\n";
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

		$split = explode('=',$res);
		if(strtolower($split[0]) == 'remain'){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$split[1]), 'message');
		}else{
			$app->enqueueMessage($this->getErrors($res),'error');
			return false;
		}
	}
}
