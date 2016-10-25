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


class ACYSMSGateway_46elks_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $from;
	public $password;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "ssl://api.46elks.com";
	public $port = 443;

	public $name = '46elks';


	public function openSend($message,$phone){
		$config = ACYSMS::config();

		$params = array();
		$params['to'] =  $this->checkNum($phone);
		$params['from'] = $this->from;
		$params['message'] = $message;
		if(!strpos(ACYSMS_LIVE,'localhost'))	$params['whendelivered'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=46elks&pass='.$config->get('pass');
		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /a1/sms HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.46elks.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
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

	public function beforeSaveConfig(&$senderProfile){
		if(!empty($senderProfile->senderprofile_params['from'])) $senderProfile->senderprofile_params['from'] = preg_replace('#[^a-z0-9]#i', '',$senderProfile->senderprofile_params['from']);
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if($answer = json_decode($res)){
			if(isset($answer->id) && !empty($answer->id)) $this->smsid = $answer->id;
			if($answer->direction == "outgoing") return true;
			else $this->errors[] = print_r($answer, true);
		}
		else{
			$this->errors[] = $res;
			return false;
		}
	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$status['delivered'] = array(5 ,"Message arrived to handset.");
		$status['sent'] = array(-2 ,"Message sent.");
		$status['failed'] = array(-1 ," Message failed to be delivered.");


		$completed_time = JRequest::getVar("delivered",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = strtotime($completed_time);

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}
		$apiAnswer->statsdetails_sms_id = (string)$smsId;

		return $apiAnswer;
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

		$apiAnswer->answer_sms_id = JRequest::getString("id",'');

		return $apiAnswer;
	}
}
