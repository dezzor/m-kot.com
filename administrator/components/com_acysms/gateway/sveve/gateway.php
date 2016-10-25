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
class ACYSMSGateway_sveve_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $port = 80;
	public $domain = 'sveve.no';

	public $name = 'Sveve';


	public function openSend($message,$phone){

		$params = array();
		$params['to'] = $this->checkNum($phone);
		$params['msg'] = $message;
		$params['user'] = $this->username;
		$params['passwd'] = $this->password;
		$params['from'] = $this->senderid;
		$params['reply'] = 'true';

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /SMS/SendMessage?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: sveve.no\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$result = $this->sendRequest($fsockParameter);
		if(!$result && strpos(implode(',', $this->errors),'Connection timed out') !== false && $this->port != '80'){
			$this->errors[] = 'It seems that the port you choose is blocked on you server. You should try to select the port 80';
		}
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();
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
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
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
			echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','Sveve').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=sveve&pass='.$config->get('pass').'</li>';
			echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Sveve').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=sveve&pass='.$config->get('pass').'</li>';
			echo '</ul>';
		}
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(preg_match('#<id>(.*)</id>#Ui', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
			return true;
		}else if(preg_match('#<fatal>(.*)</fatal>#Ui', $res, $explodedResults) || preg_match('#<message>(.*)</message>#Ui', $res, $explodedResults)){
			$this->errors[] = $explodedResults[1];
			return false;
		}else{
			$this->errors[] = $res;
			return false;
		}
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();




		$completed_time = JRequest::getVar("completed_time",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$smsId = JRequest::getVar("id",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		$errorCode = JRequest::getVar("errorCode",'');


		$messageStatus = JRequest::getVar("status",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';


		if($messageStatus == 'true'){
			$apiAnswer->statsdetails_error[] = "Delivered to mobile";
			$apiAnswer->statsdetails_status = 5;
		}else{
			$apiAnswer->statsdetails_status = $errorCode;
			$apiAnswer->statsdetails_error[] = -1;
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}


	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("received_time",'');

		$apiAnswer->answer_body = JRequest::getString("msg",'');

		$sender = JRequest::getString("number",'');
		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;

		$apiAnswer->answer_sms_id = JRequest::getString("referring_batch_id",'');

		return $apiAnswer;
	}
}
