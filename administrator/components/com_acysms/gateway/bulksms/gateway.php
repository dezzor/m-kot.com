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
class ACYSMSGateway_bulksms_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $route;
	public $domain;
	public $waittosend= 0;
	public $connectionInformations;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $name = 'BulkSMS';

	function ACYSMSGateway_bulksms_gateway(){
		$this->connectionInformations = array('bulksms.vsms.net' => 'bulksms.vsms.net',
										'bulksms.2way.co.za' => 'bulksms.2way.co.za',
										'usa.bulksms.com' => 'usa.bulksms.com',
										'bulksms.com.es' => 'bulksms.com.es',
										'bulksms.de' => 'bulksms.de',
										'bulksms.co.uk' => 'www.bulksms.co.uk'
										);
	}

	public function openSend($message,$phone){

		$params = array();

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['msisdn'] = $this->checkNum($phone);
		$params['username'] = $encodeHelper->change($this->username,'UTF-8','ISO-8859-1');
		$params['password'] = $encodeHelper->change($this->password,'UTF-8','ISO-8859-1');

		$params['routing_group'] = $encodeHelper->change($this->route,'UTF-8','ISO-8859-1');

		if(!empty($this->senderid))$params['sender'] = $this->senderid;

		if($this->unicodeChar($message)){
			$params['message'] = bin2hex(mb_convert_encoding($message, "UTF-16", "UTF-8"));
			$params['dca'] = '16bit';
		}else{
			$params['message'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
			$params['allow_concat_text_sms'] = 1;
			$params['concat_text_sms_max_parts'] = 10;
		}

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$array = array(
					'%7B' => '%BB%28',
					'%7D' => '%BB%29',
					'%5B' => '%BB%3C',
					'%5C' => '%BB%2F',
					'%5D' => '%BB%3E',
					'%7E' => '%BB%3D',
					'%7C' => '%BB%40',
					'%E2%82%AC' => '%BB%65'
					);

		$stringToPost = str_replace(array_keys($array), $array, $stringToPost);

		$fsockParameter = "POST /eapi/submission/send_sms/2/2.0 HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->connectionInformations[$this->domain]."\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		$result = $this->sendRequest($fsockParameter);
		if(!$result && strpos(implode(',', $this->errors),'Connection timed out') !== false && $this->port != '80'){
			$this->errors[] = 'It seems that the port you choose is blocked on you server. You should try to select the port 80';
		}
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();

		$routeData = array();
		$routeData[] = JHTML::_('select.option', '1', 'Economy', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '2', 'Standard', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '3', 'Premium', 'value', 'text');

		$domain = array();
		foreach($this->connectionInformations as $oneDomain => $oneHost){
			$domain[] = JHTML::_('select.option', $oneDomain, $oneDomain, 'value', 'text');
		}

		$portData = array();
		$portData[] = JHTML::_('select.option', '80', '80', 'value', 'text');
		$portData[] = JHTML::_('select.option', '5567', '5567', 'value', 'text');


		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
		$domainOption =  JHTML::_('select.genericlist', $domain, "data[senderprofile][senderprofile_params][domain]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->domain);
		$portOption =  JHTML::_('select.genericlist', $portData, "data[senderprofile][senderprofile_params][port]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->port);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
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
					<label for="senderprofile_route"><?php echo JText::_('SMS_ROUTE')?></label>
				</td>
				<td>
					<?php echo $routeOptions; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_domain"><?php echo JText::_('SMS_DOMAIN')?></label>
				</td>
				<td>
					<?php echo $domainOption; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_port"><?php echo JText::_('SMS_PORT')?></label>
				</td>
				<td>
					<?php echo $portOption; ?>
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','BulkSMS').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=bulksms&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','BulkSMS').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=bulksms&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
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



		$split = explode('|',$res);
		$extraInformations = '';


		if($split[0] != '0'){
			$res = $split[0];
			$this->errors[] = $this->getErrors($res);
			if(isset($split[1]) && !empty($split[1])) $this->errors[] = $split[1];
			return false;
		}else{
			if(isset($split[2]) && !empty($split[2])) $this->smsid = $split[2];
			return true;
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /eapi/user/get_credits/1/1.1?username=".$this->username."&password=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain." \r\n";
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


		$split = explode('|',$res);
		if($split[0] == '0'){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$split[1]), 'message');
		}else{
			$res = $split[0];
			$app->enqueueMessage($this->getErrors($res),'error');
			return false;
		}

	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['0'] = 'In progress (a normal message submission, with no error encountered so far).';
		$errors['1'] = 'Scheduled (see Scheduling below).';
		$errors['22'] = 'Internal fatal error';
		$errors['23'] = 'Authentication failure';
		$errors['24'] = 'Data validation failed';
		$errors['25'] = 'You do not have sufficient credits';
		$errors['26'] = 'Upstream credits not available';
		$errors['27'] = 'You have exceeded your daily quota';
		$errors['28'] = 'Upstream quota exceeded';
		$errors['40'] = 'Temporarily unavailable';
		$errors['201'] = 'Maximum batch size exceeded';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status[11] = array(5 ,"Delivered to mobile");
		$status[22] = array(-1 ,"Internal fatal error");
		$status[23] = array(-1 ,"Authentication failure");
		$status[24] = array(-1 ,"Data validation failed");
		$status[25] = array(-1 ,"You do not have sufficient credits");
		$status[26] = array(-1 ,"Upstream credits not available");
		$status[27] = array(-1 ,"You have exceeded your daily quota");
		$status[28] = array(-1 ,"Upstream quota exceeded");
		$status[29] = array(-1 ,"Message sending cancelled");
		$status[31] = array(-1 ,"Unroutable");
		$status[32] = array(-1 ,"Blocked (probably because of a recipient's complaint against you)");
		$status[33] = array(-1 ,"Failed = censored");
		$status[50] = array(-1 ,"Delivery failed - generic failure");
		$status[51] = array(-1 ,"Delivery to phone failed");
		$status[52] = array(-1 ,"Delivery to network failed");
		$status[53] = array(-2 ,"Message expired");
		$status[54] = array(-1 ,"Failed on remote network");
		$status[55] = array(-1 ,"Failed: remotely blocked (variety of reasons)");
		$status[56] = array(-1 ,"Failed: remotely censored (typically due to content of message)");
		$status[57] = array(-1 ,"Failed due to fault on handset (e.g. SIM full)");
		$status[64] = array(-1 ,"Queued for retry after temporary failure delivering, due to fault on handset (transient)");
		$status[70] = array(-1 ,"Unknown upstream status");


		$completed_time = JRequest::getVar("completed_time",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("batch_id",'');
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
		$apiAnswer->answer_date = JRequest::getString("received_time",'');

		$apiAnswer->answer_body = JRequest::getString("message",'');

		$sender = JRequest::getString("sender",'');
		$msisdn = JRequest::getString("msisdn",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($msisdn))	$apiAnswer->answer_to = '+'.$msisdn;

		$apiAnswer->answer_sms_id = JRequest::getString("referring_batch_id",'');

		return $apiAnswer;
	}
}
