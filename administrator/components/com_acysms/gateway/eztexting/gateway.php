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

class ACYSMSGateway_eztexting_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $MessageTypeID;

	public $domain = 'ssl://app.eztexting.com';
	public $port = 443 ;


	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = true;

	public $name = 'Ez Texting';

	public function openSend($message,$phone){
		$params = array();
		$params['format'] = "json";
		$params['User'] = $this->username;
		$params['Password'] = $this->password;
		$phoneNumbers = $this->checkNum($phone);
		if(!$phoneNumbers) return false;
		else $params['PhoneNumbers'] = $phoneNumbers;
		$params['Message'] = $message;
		$params['MessageTypeID'] = $this->MessageTypeID;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sending/messages HTTP/1.1\r\n";
		$fsockParameter.= "Host: app.eztexting.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}
	public function displayConfig(){

		$config = ACYSMS::config();

		$domain = array();
		$MessageTypeID[] = JHTML::_('select.option', '1', 'Express', 'value', 'text');
		$MessageTypeID[] = JHTML::_('select.option', '0', 'Standard', 'value', 'text');

		$msgTypeOption =  JHTML::_('select.genericlist', $MessageTypeID, "data[senderprofile][senderprofile_params][MessageTypeID]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->MessageTypeID);
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
					<label for="senderprofile_domain"><?php echo JText::_('SMS_ROUTE')?></label>
				</td>
				<td>
					<?php echo $msgTypeOption; ?>
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
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Ez Texting').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=eztexting&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'201 Created') && !strpos($result,'200 OK')){
			$this->errors[] = 'Error 201 KO => '.$result;
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

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /billing/credits/get?format=json&User=".$this->username."&Password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: app.eztexting.com \r\n";
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


		$answer = json_decode($res);

		if(!empty($answer->Response->Entry->TotalCredits)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$answer->Response->Entry->TotalCredits), 'message');
		}else{
			$app->enqueueMessage($res,'error');
			return false;
		}
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();

		$apiAnswer->answer_date = time();

		$apiAnswer->answer_body = JRequest::getString("message",'');

		$from = JRequest::getString("from",'');
		if(!empty($from))	$apiAnswer->answer_from = '+1'.JRequest::getString("from",'');

		return $apiAnswer;
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
