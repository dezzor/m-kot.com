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

class ACYSMSGateway_smsitdk_gateway extends ACYSMSGateway_default_gateway{

	public $apikey;
	public $senderId;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "www.smsit.dk";
	public $port = 80;


	public $name = 'Smsit.dk';

	public function openSend($message,$phone){

		$params = array();
		$params['apiKey'] = $this->apikey;
		$params['senderId'] = $this->senderId;
		$params['message'] = $message;
		$params['mobile'] =  $this->checkNum($phone);

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			if($oneParam != 'text') $value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}

		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api/sendSms.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smsit.dk\r\n";
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
					<label for="senderprofile_apikey"><?php echo JText::_('SMS_API_KEY')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][apikey]" id="senderprofile_apikey" maxlength="16" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->apikey,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_senderId"><?php echo JText::_('SMS_SENDER_ID')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderId]" id="senderprofile_senderId" class="inputbox" style="width:200px;" maxlength="11" value="<?php echo htmlspecialchars(@$this->senderId,ENT_COMPAT, 'UTF-8');?>" />
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

		$errors = array();
		$errors['0'] = 'Everything went as it should!';
		$errors['1'] = 'Ugyldig API-key';
		$errors['2'] = 'Ugyldigt afsendernavn';
		$errors['3'] = 'Ugyldigt karaktersæt (charset)';
		$errors['4'] = 'Ugyldigt mobilnummer';
		$errors['5'] = 'Der er ikke udfyldt en besked';
		$errors['6'] = 'Beskeden er for lang';
		$errors['7'] = 'API-key findes ikke';

		if($res == '0') return true;
		else{
			if(isset($errors[$res])){
				$this->errors[] = $errors[$res];
				return false;
			}
		}
	}
}
