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
class ACYSMSGateway_solutions4mobiles_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $domain;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $port = 80;

	public $name = 'Solutions4mobiles';

	public function openSend($message,$phone){

		$params = array();
		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['phone'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['msgtext'] = $message;
		if($this->encoding == 'all') $params['charset'] = 8;
		$params['showDLR'] = 1;

		if(!empty($this->senderid)) $params['originator'] = $this->senderid;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			if($oneParam != 'phone' && $this->encoding == 'greek')  $value = $encodeHelper->change($value,'UTF-8','ISO-8859-7');
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');


		$fsockParameter = "POST /bulksms/bulksend.go HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain."\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);

	}

	public function displayConfig(){
		$encodingData = array();
		$encodingData[] = JHTML::_('select.option', 'greek', 'Greek language', 'value', 'text');
		$encodingData[] = JHTML::_('select.option', 'all', 'Other languages', 'value', 'text');

		$encodingOption =  JHTML::_('select.genericlist', $encodingData, "data[senderprofile][senderprofile_params][encoding]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->encoding);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
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
				<td>
					<label for="senderprofile_domain"><?php echo JText::_('SMS_DOMAIN')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][domain]" id="senderprofile_domain" class="inputbox"  style="width:200px;" value="<?php echo htmlspecialchars(@$this->domain,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_encoding"><?php echo JText::_('SMS_LANGUAGE')?></label>
				</td>
				<td >
					<?php echo $encodingOption ?>
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

		if(strpos($res,'OK') !== false) return true;
		$this->errors[] = $this->getErrors($res);
		return false;

	}

	private function displayBalance(){

		$app = JFactory::getApplication();

		$stringToPost = "username=".$this->username."&password=".$this->password;

		$fsockParameter = "GET /bulksms/getBALANCE.go?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain." \r\n\r\n";

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


		if(strpos($res,'ERROR') === false && strpos($res,'&euro')){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res), 'message');
		}else{
			$app->enqueueMessage($this->getErrors($res),'error');
			return false;
		}

	}

	protected function getErrors($errNo){

		$errors = array();
		$errors['ERROR100'] = "Temporary Internal Server Error. Try again later";
		$errors['ERROR101'] = "Authentication Error (Not valid login Information)";
		$errors['ERROR102'] = "No credits available";
		$errors['ERROR103'] = "MSIDSN (phone parameter) is invalid or prefix is not supported";
		$errors['ERROR104'] = "Tariff Error";
		$errors['ERROR105'] = "You are not allowed to send to that destination/country";
		$errors['ERROR106'] = "Not Valid Route number or you are not allowed to use this route";
		$errors['ERROR107'] = "No proper Authentication (IP restriction is activated)";
		$errors['ERROR108'] = "You have no permission to send messages through HTTP API";
		$errors['ERROR109'] = "Not Valid Originator";
		$errors['ERROR999'] = "Invalid HTTP Request";

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}
}
