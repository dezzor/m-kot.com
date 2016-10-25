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
class ACYSMSGateway_callfirevoicecall_gateway extends ACYSMSGateway_default_gateway{

	public $login;
	public $password;
	public $from;
	public $waittosend= 0;
	public $liveSoundId;
	public $machineSoundId;
	public $transferSoundId;
	public $answeringMachineConfig;
	public $transferDigit;
	public $transferNumber;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "ssl://www.callfire.com";
	public $port = 443;

	public $name = 'Callfire Voice Call';


	public function openSend($message,$phone){

		$params = array();

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['To'] = $this->checkNum($phone);
		$params['Login'] = $encodeHelper->change($this->login,'UTF-8','ISO-8859-1');
		$params['Password'] = $encodeHelper->change($this->password,'UTF-8','ISO-8859-1');
		$params['Message'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');

		$params['Type'] = 'VOICE';
		if(!empty($this->fullMessage) && !empty($this->fullMessage->message_body))	$params['BroadcastName'] =  $encodeHelper->change($this->fullMessage->message_body,'UTF-8','ISO-8859-1');
		else $params['BroadcastName'] =  $encodeHelper->change('AcySMS Text to speech','UTF-8','ISO-8859-1');
		$params['From'] = $encodeHelper->change($this->from,'UTF-8','ISO-8859-1');
		$params['AnsweringMachineConfig'] = $encodeHelper->change($this->answeringMachineConfig,'UTF-8','ISO-8859-1');
		$params['LiveSoundId'] = $encodeHelper->change($this->liveSoundId,'UTF-8','ISO-8859-1');
		if(!empty($this->transferSoundId)) $params['TransferSoundId'] = $encodeHelper->change($this->transferSoundId,'UTF-8','ISO-8859-1');
		if(!empty($this->machineSoundId)) $params['MachineSoundId'] = $encodeHelper->change($this->machineSoundId,'UTF-8','ISO-8859-1');
		if(!empty($this->transferDigit)) $params['TransferDigit'] = $encodeHelper->change($this->transferDigit,'UTF-8','ISO-8859-1');
		if(!empty($this->transferNumber)) $params['TransferNumber'] = $encodeHelper->change($this->transferNumber,'UTF-8','ISO-8859-1');


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api/1.1/rest/call HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.callfire.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n";
		$fsockParameter.="Authorization: Basic ".base64_encode($this->login.":".$this->password)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);

	}

	public function displayConfig(){
		$AnsweringMachineConfigData = array();
		$AnsweringMachineConfigData[] = JHTML::_('select.option', 'LIVE_IMMEDIATE', 'LIVE_IMMEDIATE', 'value', 'text');
		$AnsweringMachineConfigData[] = JHTML::_('select.option', 'AM_ONLY', 'AM_ONLY', 'value', 'text');
		$AnsweringMachineConfigData[] = JHTML::_('select.option', 'AM_AND_LIVE', 'AM_AND_LIVE', 'value', 'text');
		$AnsweringMachineConfigData[] = JHTML::_('select.option', 'LIVE_WITH_AMD', 'LIVE_WITH_AMD', 'value', 'text');

		$AnsweringMachineConfig =  JHTML::_('select.genericlist', $AnsweringMachineConfigData, "data[senderprofile][senderprofile_params][answeringMachineConfig]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->answeringMachineConfig);

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_login"><?php echo JText::_('SMS_API_LOGIN'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->login,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_API_PASSWORD')?></label>
				</td>
				<td>
					<input type="password" name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo 'From Number'?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_soundId"><?php echo 'Live Sound Id' ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][liveSoundId]" id="senderprofile_soundId" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->liveSoundId,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_soundId"><?php echo 'Machine Sound Id' ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][machineSoundId]" id="senderprofile_soundId" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->machineSoundId,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_soundId"><?php echo 'Transfer Sound Id' ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][transferSoundId]" id="senderprofile_soundId" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->transferSoundId,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_soundId"><?php echo 'Transfer Digit' ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][transferDigit]" id="senderprofile_soundId" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->transferDigit,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_soundId"><?php echo 'Transfer Number' ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][transferNumber]" id="senderprofile_soundId" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->transferNumber,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_domain"><?php echo 'Answering Machine Config' ;?></label>
				</td>
				<td>
					<?php echo $AnsweringMachineConfig; ?>
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

		if(strpos($result,'201 Created') === false && strpos($result,'200 OK') === false){
			$this->errors[] = 'Error => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(preg_match('#<r:Id>(.*)</r:Id>#Ui', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
			return true;
		}
		if(preg_match('#<Message>(.*)</Message>#Ui', $res, $explodedResults)){
			$this->errors[] = $explodedResults[1];
		}else{
			$this->errors[] = $res;
		}
		return false;

	}
}
