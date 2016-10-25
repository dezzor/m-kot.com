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
class ACYSMSGateway_smslive247_gateway extends ACYSMSGateway_default_gateway{

	public $owneremail;
	public $subacct;
	public $subacctpwd;
	public $senderid;
	public $sessionId;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = 'smslive247.com';
	public $port = 80;

	public $name = 'SMSLIVE247';


	public function open(){
		$fsockParameter = "GET /http/index.aspx?cmd=login&owneremail=".$this->owneremail."&subacct=".$this->subacct."&subacctpwd=".$this->subacctpwd." 	HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smslive247.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if(preg_match('#OK: ([0-9a-z\-]*)#i', $result, $explodedResults)){
			$this->sessionId = $explodedResults[1];
			return true;
		}else{
			$this->errors[] = 'Authentification failed.';
			return false;
		}
	}
	public function openSend($message,$phone){
		$params = array();

		$params['sendto'] = $this->checkNum($phone);
		$params['sessionid'] = $this->sessionId;
		$params['msgtype'] = 0;
		$params['cmd'] = 'sendmsg';
		$params['message'] = $message;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /http/index.aspx HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smslive247.com\r\n";
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
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_owneremail"><?php echo 'Owner email'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][owneremail]" id="senderprofile_owneremail" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->owneremail,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_subacct"><?php echo 'Sub-account'; ?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][subacct]" id="senderprofile_subacct" class="inputbox"  type="text" style="width:200px;" value="<?php echo htmlspecialchars(@$this->subacct,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_subacctpwd"><?php echo 'Sub-account password'; ?></label>
				</td>
				<td>
					<input type="password" name="data[senderprofile][senderprofile_params][subacctpwd]" id="senderprofile_subacctpwd" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->subacctpwd,ENT_COMPAT, 'UTF-8');?>" />
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
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))){
			$this->open();
			$this->displayBalance();
		}
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));



		if(preg_match('#OK: ([0-9a-z\-]*)#i', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
			return true;
		}else{
			$this->errors[] = $res;
			return false;
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /http/index.aspx?cmd=querybalance&sessionid=".$this->sessionId." HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.smslive247.com \r\n";
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


		if(preg_match('#OK: ([0-9a-z\-]*)#i', $res, $explodedResults)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$explodedResults[1]), 'message');
			return true;
		}else{
			$app->enqueueMessage($res,'error');
			return false;
		}
	}
}
