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

class ACYSMSGateway_sms77_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;
	public $route;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "gateway.sms77.de";
	public $port = 80;


	public $name = 'SMS77';

	public function openSend($message,$phone){

		$encodeHelper = ACYSMS::get('helper.encoding');
		$params = array();

		$params['from'] = $encodeHelper->change($this->from,'UTF-8','ISO-8859-1');
		$params['to'] = $encodeHelper->change($this->checkNum($phone),'UTF-8','ISO-8859-1');
		$params['u'] = $encodeHelper->change($this->username,'UTF-8','ISO-8859-1');
		$params['p'] = $encodeHelper->change($this->password,'UTF-8','ISO-8859-1');
		$params['text'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
		$params['type'] = $encodeHelper->change($this->route,'UTF-8','ISO-8859-1');

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: gateway.sms77.de\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		return $this->sendRequest($fsockParameter);
	}


	public function displayConfig(){
			$routeData[] = JHTML::_('select.option', 'basicplus', 'basicplus', 'value', 'text');
			$routeData[] = JHTML::_('select.option', 'quality', 'quality', 'value', 'text');
			$routeData[] = JHTML::_('select.option', 'festnetz', 'festnetz', 'value', 'text');
			$routeData[] = JHTML::_('select.option', 'flash', 'flash', 'value', 'text');

			$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_type"><?php echo JText::_('SMS_TYPE')?></label>
				</td>
				<td>
					<?php echo $routeOptions; ?>
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
		$errors['100'] = 'SMS wurde erfolgreich verschickt';
		$errors['101'] = 'Versand an mindestens einen Empfänger fehlgeschlagen';
		$errors['201'] = 'Ländercode für diesen SMS-Typ nicht gültig. Bitte als Basic SMS verschicken.';
		$errors['202'] = 'Empfängernummer ungültig';
		$errors['300'] = 'Bitte Benutzer/Passwort angeben';
		$errors['301'] = 'Variable to nicht gesetzt';
		$errors['304'] = 'Variable type nicht gesetzt';
		$errors['305'] = 'Variable text nicht gesetzt';
		$errors['306'] = 'Absendernummer ungültig Diese muss vom Format 0049... sein und eine gültige';
		$errors['307'] = 'Variable url nicht gesetzt';
		$errors['400'] = 'type ungültig. Siehe erlaubte Werte oben.';
		$errors['401'] = 'Variable text ist zu lang';
		$errors['402'] = 'Reloadsperre – diese SMS wurde bereits innerhalb der letzten';
		$errors['90'] = 'Sekunden verschickt';
		$errors['500'] = 'Zu wenig Guthaben vorhanden.';
		$errors['600'] = 'Carrier Zustellung misslungen';
		$errors['700'] = 'Unbekannter Fehler';
		$errors['801'] = 'Logodatei nicht angegeben';
		$errors['802'] = 'Logodatei existiert nicht';
		$errors['803'] = 'Klingelton nicht angegeben';
		$errors['900'] = 'Benutzer/Passwort-Kombination falsch';
		$errors['902'] = 'http API für diesen Account deaktiviert';
		$errors['903'] = 'Server IP ist falsch';

		if($res == '100'){
			return true;
		}else{
			$this->errors[] = isset($errors[$res]) ? $errors[$res] : 'Unknown error : '.$res;
			return false;
		}
	}

	protected function checkNum($phone){
		$internationalPhone = str_replace('+', '00', $phone);
		return preg_replace('#[^0-9]#','',$internationalPhone);
	}
}
