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
class ACYSMSGateway_telerivet_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $route;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = 'ssl://api.telerivet.com';
	public $port = 443;

	public $name = 'Telerivet';

	public function openSend($message,$phone){
		$config = ACYSMS::config();
		$params = array();

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['to_number'] = $encodeHelper->change('+'.$this->checkNum($phone),'UTF-8','ISO-8859-1');
		$params['phone_id'] = $encodeHelper->change($this->phoneId,'UTF-8','ISO-8859-1');
		$params['content'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
		$params['api_key'] = $encodeHelper->change($this->apiKey,'UTF-8','ISO-8859-1');
		if(!strpos(ACYSMS_LIVE,'localhost'))	$params['status_url'] = $encodeHelper->change(ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=telerivet&pass='.$config->get('pass'),'UTF-8','ISO-8859-1');


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /v1/projects/".$this->projectId."/messages/outgoing HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.telerivet.com\r\n";
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
					<label for="senderprofile_apiKey"><?php echo 'API key'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][apiKey]" id="senderprofile_apiKey" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->apiKey, ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_projectId"><?php echo 'Project ID'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][projectId]" id="senderprofile_projectId" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->projectId,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_phoneId"><?php echo 'Phone ID'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][phoneId]" id="senderprofile_phoneId" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->phoneId,ENT_COMPAT, 'UTF-8');?>" />
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

		$res = json_decode($res);
		if(!empty($res->id)) $this->smsid = $res->id;
		if(!empty($res->status) && ($res->status == 'sent' || $res->status == 'queued')) return true;

		$this->errors[] = $res->status;
		return false;

	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();




		$status['sent'] = array(1 ,"the message has been successfully sent to the mobile network");
		$status['queued'] = array(4 ,"the message has not been sent yet");
		$status['failed'] = array(-1 ,"The message has failed to send");
		$status['failed_queued'] = array(-1 ,"The message has failed to send, but Telerivet will try to send it again later");
		$status['delivered'] = array(5 ,"Message delivered");
		$status['not_delivered'] = array(-1 ,"The message could not be delivered (if delivery reports are enabled)");
		$status['cancelled'] = array(-1 ,"The message was cancelled by the user");


		$apiAnswer->statsdetails_received_date = time();

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("time_sent",'');

		$apiAnswer->answer_body = JRequest::getString("content",'');

		$sender = JRequest::getString("from_number",'');
		$receiver = JRequest::getString("to_number",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($receiver))	$apiAnswer->answer_to = '+'.$receiver;

		$apiAnswer->answer_sms_id = JRequest::getString("id",'');

		return $apiAnswer;
	}
}
