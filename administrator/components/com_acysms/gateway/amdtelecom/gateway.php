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
class ACYSMSGateway_amdtelecom_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $port = 8208;
	public $domain = "api2.amdtelecom.net";

	public $name = 'AMD Telecom';

	public function openSend($message,$phone){

		$params = array();
		$params['to'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['text'] = $message;
		if(!empty($this->from)) $params['from'] = $this->from;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST / HTTP/1.1\r\n";
		$fsockParameter.= "Host: api2.amdtelecom.net\r\n";
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
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>
			<?php
				if(strpos(ACYSMS_LIVE,'localhost') !== false)	echo JText::_('SMS_LOCALHOST_PROBLEM');
				else{
					echo '<ul>';
						echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','AMD Telecom').'<br />'.ACYSMS_LIVE.'?option=com_acysms&c=d&g=amdtelecom&p='.$config->get('pass').'</li>';
						if(strlen(urlencode(ACYSMS_LIVE.'?option=com_acysms&c=d&g=amdtelecom&p='.$config->get('pass'))) > 85){
							echo '<li><font color="red">Your URL is longer than 85 characters (API restriction), please go in your database and reduce the value for the field "pass" in the ACYSMS configuration table (acysms_config).</font></li>';
						}
					echo '</ul>';
				}
	}


	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));



		if(strpos($res, 'SUCCESS') === false){
			$this->errors[] = $res;
			return false;
		}
		if(preg_match('#MessageId: *([0-9a-z\-]*)#i', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
		}
		return true;

	}

	public function deliveryReport(){

		$status = array();
		$errors = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status[1] = array(5 ,"Delivered");
		$status[2] = array(-1 ,"Undelivered");
		$status[4] = array(4 ,"EnRoute");
		$status[8] = array(1 ,"Acknowledged");

		$errors[001] = "Absent Subscribe / Interm/Final";
		$errors[003] = "Accepted / Final";
		$errors[004] = "Accepted by SMSC / Intermediate";
		$errors[005] = "Call Barred / Final";
		$errors[006] = "Controlling MSC failure / Final";
		$errors[007] = "Delivered";
		$errors[009] = "HLR Aborted / Final";
		$errors[018] = "Rejected by SMSC / Final";
		$errors[020] = "Unavailable while roaming to / Final";
		$errors[021] = "Unexpected data value / Final";
		$errors[022] = "Unknown Subscriber / Final";
		$errors[999] = "Uncategorized Status / Final";

		$receiver = JRequest::getVar("receiver",'');
		if(empty($receiver)) return;


		$completed_time = JRequest::getVar("donedate",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("type",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_status = -99;

			$errorcode = JRequest::getVar("status",'');
			if(!isset($errors[$errorcode]))	$apiAnswer->statsdetails_error[] = 'Unknow status : '.$errorcode;
			else $apiAnswer->statsdetails_error[] = $errors[$errorcode];

		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}
}
