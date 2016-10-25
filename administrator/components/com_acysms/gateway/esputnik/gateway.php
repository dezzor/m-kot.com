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

class ACYSMSGateway_esputnik_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $from;
	public $password;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "ssl://www.esputnik.com.ua";
	public $port = 443;

	public $name = 'Esputnik';

	public function openSend($message,$phone){
		$stringToPost = '';

		$params = new stdClass();
		$params->from = $this->from;
		$params->text = $message;
		$params->phoneNumbers = $this->checkNum($phone);
		$params->headers = 0;

		$stringToPost = json_encode($params);

		$fsockParameter = "POST /api/v1/message/sms HTTP/1.1\r\n";
		$fsockParameter.= "Host: esputnik.com.ua\r\n";
		$fsockParameter.= "Content-type: application/json\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n";
		$fsockParameter.="Authorization: Basic ".base64_encode($this->username.":".$this->password)."\r\n\r\n";
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" type="password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
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

		if($answer = json_decode($res)){
			if(isset($answer->results->id) && !empty($answer->results->id)) $this->smsid = $answer->results->id;
			if($answer->results->status == "ERROR"){
				$this->errors[] = $answer->results->message;
				return false;
			}else return true;
		}
		else{
			$this->errors[] = $res;
			return false;
		}
	}
}
