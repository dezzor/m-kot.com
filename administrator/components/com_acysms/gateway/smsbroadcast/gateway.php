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

class ACYSMSGateway_smsbroadcast_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "api.smsbroadcast.com.au";
	public $port = 80;


	public $name = 'SMS Broadcast';

	public function openSend($message,$phone){

		$params = array();
		$params['from'] = $this->from;
		$params['to'] =  $this->checknum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['message'] = $message;
		$params['maxsplit'] = 5;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api-adv.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smsbroadcast.com.au\r\n";
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

		if(strpos($res,'ERROR') === false) return true;
		$this->errors[] = $res;
		return false;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("created",'');

		$apiAnswer->answer_body = JRequest::getString("message",'');

		$from = JRequest::getString("from",'');
		$to = JRequest::getString("to",'');
		if(!empty($from))	$apiAnswer->answer_from = '+'.$from;
		if(!empty($to))	$apiAnswer->answer_to = '+'.$to;

		$apiAnswer->answer_sms_id = JRequest::getString("ref",'');

		return $apiAnswer;
	}
}
