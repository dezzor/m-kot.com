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
class ACYSMSGateway_clickatell_gateway extends ACYSMSGateway_default_gateway{

	public $api_id;
	public $password;
	public $username;
	public $waittosend= 0;
	public $from;
	public $mo;
	public $alphasourceaddress = 0;
	public $numericsourceaddress = 0;
	public $deliveryAcknowledgments = 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "api.clickatell.com";
	public $port = 80;


	public $name = 'Clickatell';

	public function openSend($message,$phone){

		$params = array();

		if($this->unicodeChar($message)){
			$arr = unpack('H*hex', iconv('UTF-8', 'UCS-2BE', $message));
			$message = strtoupper($arr['hex']);
			$params['unicode'] = '1';
		}

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['to'] = $encodeHelper->change($this->checkNum($phone),'UTF-8','ISO-8859-1');
		$params['user'] = $encodeHelper->change($this->username,'UTF-8','ISO-8859-1');
		$params['api_id'] = $encodeHelper->change($this->api_id,'UTF-8','ISO-8859-1');
		$params['password'] = $encodeHelper->change($this->password,'UTF-8','ISO-8859-1');
		$params['from'] = $encodeHelper->change($this->from,'UTF-8','ISO-8859-1');
		$params['mo'] = $encodeHelper->change(1,'UTF-8','ISO-8859-1');
		$params['text'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
		$params['callback'] = $encodeHelper->change('1','UTF-8','ISO-8859-1');
		$params['concat'] = $encodeHelper->change('35','UTF-8','ISO-8859-1');
		if(!empty($this->alphasourceaddress) || !empty($this->numericsourceaddress)  || !empty($this->deliveryAcknowledgments))
			$params['req_feat'] = $encodeHelper->change($this->alphasourceaddress + $this->numericsourceaddress + $this->deliveryAcknowledgments,'UTF-8','ISO-8859-1');

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /http/sendmsg HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.clickatell.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		ACYSMS::display('If you use special characters like é,à,ô,è etc. The SMS will have a length of 70 characters instead of 160.','warning');

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_api_id"><?php echo JText::_('SMS_API_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][api_id]" id="senderprofile_api_id" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->api_id,ENT_COMPAT, 'UTF-8');?>" />
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_SEND_ONLY_TO_OPERATOR_SUPPORTING')?></label>
				</td>
				<td>
					<label><input type="checkbox" name="data[senderprofile][senderprofile_params][alphasourceaddress]" value="16" title="Alpha Source Address"/>Alpha Source Address</label>
					<label><input type="checkbox" name="data[senderprofile][senderprofile_params][numericsourceaddress]" value="32" title="Numeric Source Address"/>Numeric Source Address</label>
					<label><input type="checkbox" name="data[senderprofile][senderprofile_params][deliveryAcknowledgments]" value="8192" title="Delivery Acknowledgments"/>Delivery Acknowledgments</label>
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','Clickatell').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=clickatell&pass='.$config->get('pass').'</li>';
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

		if(preg_match('#ID: *([0-9a-z_]+)#i',$result,$infos)){
			$this->smsid = $infos[1];
			return true;
		}

		$this->errors[] = $result;
		return false;
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$stringToPost = "user=".urlencode($this->username)."&password=".urlencode($this->password)."&api_id=".urlencode($this->api_id);

		$fsockParameter = "POST /http/getbalance HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.clickatell.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

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

		if(preg_match('#credit: *([0-9,\.]*)#i',$result,$credits)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$credits[1]), 'message');
			return true;
		}

		$app->enqueueMessage($result,'error');
		return false;

	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();




		$status['001'] = array(-1 ,"The message ID is incorrect or reporting is delayed.");
		$status['002'] = array(4 ,"The message could not be delivered and has been queued for attempted redelivery.");
		$status['003'] = array(5 ,"Delivered to the upstream gateway or network (delivered to the recipient).");
		$status['004'] = array(5 ,"Confirmation of receipt on the handset of the recipient.");
		$status['005'] = array(-1 ,"There was an error with the message, probably caused by the content of the message itself");
		$status['006'] = array(-1 ,"The message was terminated by a user (stop message command) or by our staff");
		$status['007'] = array(-1 ,"An error occurred delivering the message to the handset.");
		$status['008'] = array(2 ,"Message received by gateway.");
		$status['009'] = array(-1 ,"The routing gateway or network has had an error routing the message.");
		$status['010'] = array(-2 ,"Message has expired before we were able to deliver it to the upstream gateway. No charge applies.");
		$status['011'] = array(-4 ,"Message has been queued at the gateway for delivery at a later time (delayed delivery).");
		$status['012'] = array(-1 ,"The message cannot be delivered due to a lack of funds in your account. Please re-purchase credits.");
		$status['014'] = array(-1 ,"The allowable amount for MT messaging has been exceeded.");


		$completed_time = JRequest::getVar("timestamp",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("apiMsgId",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message id';

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
}
