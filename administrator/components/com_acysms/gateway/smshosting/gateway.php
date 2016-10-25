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
class ACYSMSGateway_smshosting_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;


	public $domain = 'www.smshosting.it';
	public $port = 80;

	public $name = 'SMSHosting';

	public function openSend($message,$phone){

		$params = array();

		if(strlen($message)>160){
			$params['longSms'] = 'Y';
		}

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['numero'] = $this->checkNum($phone);
		$params['user'] = $this->username;
		$params['password'] = $this->password;
		$params['testo'] = $message;
		$params['mittente'] = $this->from;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "POST /sms/services/httpInvioSmsHttp.ic HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smshosting.it\r\n";
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
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"   style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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


		if(preg_match('#<codice>(.*)</codice>#Ui', $res, $explodedResults)){
			if($explodedResults[1] == 'HTTP_00'){
				return true;
			}
			$this->errors[] = $this->getErrors($explodedResults[1]);
			return false;
		}

	}

	protected function getErrors($errNo){
		$errors = array();

		$errors['HTTP_01'] = 'Autenticazione fallita';
		$errors['HTTP_02'] = 'Invio non riuscito. Nel caso di invio ad un gruppo questo errore viene restituito quando almeno uno dei messaggi ha avuto esito negativo.';
		$errors['HTTP_03'] = 'Testo non specificato';
		$errors['HTTP_06'] = 'Il prefisso non deve essere specificato se è presente il gruppo';
		$errors['HTTP_07'] = 'Il numero non deve essere specificato se è presente il gruppo';
		$errors['HTTP_08'] = 'Il country non deve essere specificato se è presente il gruppo';
		$errors['HTTP_09'] = 'Nome del gruppo non corretto';
		$errors['HTTP_10'] = 'Formato data non valido';
		$errors['HTTP_11'] = 'La data è antecedente a quella odierna';
		$errors['HTTP_12'] = 'Temporarily unavailable';
		$errors['HTTP_18'] = 'Testo troppo lungo. Si sono superati i 160 caratteri se non è stato specificato longSms=Y oppure sono stati superati i 765 caratteri nel caso di longSms=Y.';
		$errors['HTTP_21'] = 'Country, prefisso o numero non corretti';
		$errors['HTTP_22'] = 'Data mancante';
		$errors['HTTP_23'] = 'Formato ora non valido';
		$errors['HTTP_24'] = 'Formato minuti non valido';
		$errors['HTTP_25'] = 'Ora mancante';
		$errors['HTTP_26'] = 'IP non abilitato';


		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}
}
