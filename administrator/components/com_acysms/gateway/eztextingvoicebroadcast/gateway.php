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
class ACYSMSGateway_eztextingvoicebroadcast_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $MessageTypeID;
	public $callerPhonenumber;
	public $voiceFile;

	public $domain = 'ssl://app.eztexting.com';
	public $port = 443 ;


	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $name = 'Ez Texting Voice Broadcast';


	public function openSend($message,$phone){

		$params = array();
		$params['format'] = 'json';
		$params['User'] = $this->username;
		$params['Password'] = $this->password;
		$params['PhoneNumbers'] = $this->checkNum($phone);
		$params['MessageTypeID'] = $this->MessageTypeID;
		$params['CallerPhonenumber'] = $this->callerPhoneNumber;


		if(preg_match('#^https?\:\/\/[a-z0-9\-\.]+\.[a-z]{2,7}[^ ]*$#i', $message, $result)){
			$params['VoiceSource'] = $message;
		}else{
			$this->errors[] = JText::sprintf('SMS_INVALID_URL', $message);
			return false;
		}

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /voice/messages HTTP/1.1\r\n";
		$fsockParameter.= "Host: app.eztexting.com\r\n";
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
					<label for="senderprofile_callerphonenumber"><?php echo 'Caller Phone Number'; ?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][callerPhoneNumber]" id="senderprofile_callerphonenumber" class="inputbox"  maxlength="10" style="width:200px;" value="<?php echo htmlspecialchars(@$this->callerPhoneNumber,ENT_COMPAT, 'UTF-8');?>" />
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

		if(!strpos($result,'201 Created') && !strpos($result,'200 OK')){

			$res = substr($result,strpos($result,"\r\n\r\n"));
			$jsonDecodedResult = json_decode($res);

			if(!empty($jsonDecodedResult->Response->Errors)) $this->errors[] = 'Error 201 KO => '.implode(',', $jsonDecodedResult->Response->Errors);
			$this->errors[] = 'Error 201 KO => '.print_r($jsonDecodedResult, true);

			return false;
		}
		else $res = substr($result,strpos($result,"\r\n\r\n"));

		$answer = json_decode($res);

		if(!empty($answer->Response->Code) && $answer->Response->Code == '201') return true;
		else{
			$this->errors[] = $res;
			return false;
		}

	}

	protected function checkNum($phone){
		if(strpos($phone, '+1') === false){
			$this->errors[] = 'The phone number is not a valid American phone or a Canadian phone number';
			return false;
		}
		$americanPhone = str_replace('+1', '', $phone);
		return $americanPhone;
	}
}
