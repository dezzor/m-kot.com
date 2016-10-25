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

class ACYSMSGateway_smsmode_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $classe_msg;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "ssl://api.smsmode.com";
	public $port = 443;

	public $name = 'SMS mode';

	public function openSend($message,$phone){
		$config = ACYSMS::config();

		$params = array();
		$params['numero'] = $this->checkNum($phone);
		$params['pseudo'] = $this->username;
		$params['pass'] = $this->password;
		$params['message'] = $message;
		$params['classe_msg'] = $this->classe_msg;
		$params['emetteur'] = $this->from;
		$params['notification_url'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=smsmode&pass='.$config->get('pass');
		$params['compteRendu'] = 'true';

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /http/1.6/sendSMS.do HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.smsmode.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}
	public function displayConfig(){
		$config = ACYSMS::config();
		$routeData[] = JHTML::_('select.option', '2', 'SMS pro', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '3', 'SMS pro plus', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '4', 'SMS avec réponse autorisée', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '5', 'SMS éco', 'value', 'text');

		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][classe_msg]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->classe_msg);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_senderid" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
					<label for="senderprofile_classe_msg"><?php echo JText::_('SMS_CLASS')?></label>
				</td>
				<td>
					<?php echo $routeOptions; ?>
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','SMS Mode').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=smsmode&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','SMS Mode').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=smsmode&pass='.$config->get('pass').'</li>';
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
		if(isset($split[2]) && !empty($split[2])){
			$this->smsid = $split[2];
		}
		if($split[0] == '0'){
			return true;
		}else{
			$res = $split[0];
			$this->errors[] = $this->getErrors($res);
		}
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /http/1.6/credit.do?pseudo=".$this->username."&pass=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.smsmode.com\r\n\r\n";

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



		if(strpos($res,'|') !== false){

			$split = explode('|',$res);
			$app->enqueueMessage($split[1],'error');
			return false;
		}
		else $app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',intval($res)), 'message');
	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$status[11] = array(5 ,"Message Reçu");
		$status[13] = array(3 ,"Message délivré à l'opérateur");
		$status[33] = array(0 ,"Crédit insuffisant");
		$status[34] = array(-1 ,"Erreur routage");
		$status[35] = array(-1 ,"Erreur réception");

		$completed_time = JRequest::getVar("completed_time",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("statut",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("smsID",'');
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
}
