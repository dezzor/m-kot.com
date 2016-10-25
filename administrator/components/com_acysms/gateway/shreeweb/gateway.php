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

class ACYSMSGateway_shreeweb_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $sender;
	public $message;
	public $waittosend= 0;


	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "sms.shreeweb.com";
	public $port = 80;


	public $name = 'SHREEWEB';


	public function openSend($message,$phone){
		$params = array();
		$params['message'] = $message;
		$params['mobile'] =  $this->checkNum($phone);
		$params['sender'] = $this->sender;
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['type'] = 'TEXT';

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sendsms/sendsms.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: sms.shreeweb.com\r\n";
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
					<label for="senderprofile_username"><?php echo JText::_('SMS_USERNAME')?></label>
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_sender"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender]" id="senderprofile_sender" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender,ENT_COMPAT, 'UTF-8');?>" />
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

		$split = explode('|',$res);
		if(isset($split[0]) && strpos($split[0],'SUCCESS')){
			$this->smsid = $split[1];
			return true;
		}else{
			$this->errors[] = $res;
		}
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /sendsms/checkbalance.php?username=".$this->username."&password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: sms.shreeweb.com\r\n";
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

			if(!strpos($res,'ERR'))		$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',strip_tags($res)), 'message');
			else $app->enqueueMessage(strip_tags($res), 'error');
	}
}
