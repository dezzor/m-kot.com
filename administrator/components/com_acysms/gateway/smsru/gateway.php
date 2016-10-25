<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.6
 * @author	Denve® www.denvera.net	31.05.2015 1:16
 * @copyright	(C) 2015 Denve®. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class ACYSMSGateway_smsru_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;
	public $route;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "sms.ru";
	public $port = 80;


	public $name = 'SMS.RU';

	public function openSend($message,$phone){

		$encodeHelper = ACYSMS::get('helper.encoding');
		$params = array();
		
		if (strlen($this->api_id)) $params['api_id'] = $this->api_id;
		
		if (strlen($this->login)) $params['login'] = $this->login;
		if (strlen($this->password)) $params['password'] = $this->password;
		
		if (strlen($this->from)) $params['from'] = $this->from;
		if ($this->translit) $params['translit'] = $this->translit;
		if ($this->test) $params['test'] = $this->test;
		$params['partner_id'] = 17774;
		
		$params['to'] = $phone;
		$params['text'] = $message;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /sms/send?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: sms.ru\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		return $this->sendRequest($fsockParameter);
	}


	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_api_id"><?php echo JText::_('API_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][api_id]" id="senderprofile_api_id" class="inputbox" style="width:200px;" value="<?php echo @$this->api_id ?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Нужно указать <strong>API_ID</strong> или <strong>Username + Password</strong>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_login"><?php echo JText::_('SMS_USERNAME'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo @$this->login ?>" />
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
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from, ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="hidden" name="data[senderprofile][senderprofile_params][translit]" value="0" />
					<label for="senderprofile_translit"><input type="checkbox" name="data[senderprofile][senderprofile_params][translit]" id="senderprofile_translit" class="inputbox" value="1" <?php if ($this->translit == 1) echo 'checked="checked"'; ?> /> Переводить в транслит</label>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="hidden" name="data[senderprofile][senderprofile_params][test]" value="0" />
					<label for="senderprofile_test"><input type="checkbox" name="data[senderprofile][senderprofile_params][test]" id="senderprofile_test" class="inputbox" value="1" <?php if ($this->test == 1) echo 'checked="checked"'; ?> /> Тестовый режим</label>
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
		
		list($code,$sms_id) = explode("\n", $res);
		//exit();

		$errors = $this->getArrayCodes();

		if($code == '100'){
			return true;
		}else{
			$this->errors[] = isset($errors[$code]) ? $errors[$code] : 'Unknown error : '.$res;
			return false;
		}
	}
	
	
	private function getArrayCodes() {
		$errors = array();
		$errors['100'] = 'Сообщение принято к отправке';
		$errors['200'] = 'Неправильный api_id';
		$errors['201'] = 'Не хватает средств на лицевом счету';
		$errors['202'] = 'Неправильно указан получатель';
		$errors['203'] = 'Нет текста сообщения';
		$errors['204'] = 'Имя отправителя не согласовано с администрацией';
		$errors['205'] = 'Сообщение слишком длинное (превышает 8 СМС)';
		$errors['206'] = 'Будет превышен или уже превышен дневной лимит на отправку сообщений';
		$errors['207'] = 'На этот номер (или один из номеров) нельзя отправлять сообщения, либо указано более 100 номеров в списке получателей';
		$errors['208'] = 'Параметр time указан неправильно';
		$errors['209'] = 'Вы добавили этот номер (или один из номеров) в стоп-лист';
		$errors['210'] = 'Используется GET, где необходимо использовать POST';
		$errors['211'] = 'Метод не найден';
		$errors['212'] = 'Текст сообщения необходимо передать в кодировке UTF-8 (вы передали в другой кодировке)';
		$errors['220'] = 'Сервис временно недоступен, попробуйте чуть позже.';
		$errors['230'] = 'Сообщение не принято к отправке, так как на один номер в день нельзя отправлять более 60 сообщений.';
		$errors['300'] = 'Неправильный token (возможно истек срок действия, либо ваш IP изменился)';
		$errors['301'] = 'Неправильный пароль, либо пользователь не найден';
		$errors['302'] = 'Пользователь авторизован, но аккаунт не подтвержден (пользователь не ввел код, присланный в регистрационной смс)';
		
		return $errors;
	}
	
	

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}
	
	
	private function displayBalance(){

		$app = JFactory::getApplication();
		
		$params = array();
		
		if (strlen($this->api_id)) $params['api_id'] = $this->api_id;
		
		if (strlen($this->login)) $params['login'] = $this->login;
		if (strlen($this->password)) $params['password'] = $this->password;
		
		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');
		
		$fsockParameter = "GET /my/balance?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: sms.ru\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));
		
		list($code,$balance) = explode("\n", $res);
		

		if($code == 100) {
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT', $balance), 'message');
		} else {
			$errors = $this->getArrayCodes();
			$app->enqueueMessage($errors[$code], 'error');
		}
	}
}
