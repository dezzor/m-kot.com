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

class ACYSMSGateway_mobistar_gateway extends ACYSMSGateway_default_gateway{

	public $phone;
	public $activationCode;
	public $registered;
	public $email;
	public $password;
	public $apiPassword;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;
	public $fastDelivery = false; //CURL function with their helper

	public $name = 'Mobistar';

	public function ACYSMSGateway_mobistar_gateway(){
		if(!include_once(ACYSMS_GATEWAY.'mobistar'.DS.'mobistarhelper.php')){
			echo 'This gateway could not work without the '.ACYSMS_GATEWAY.'mobistar'.DS.'mobistarhelper.php file';
			return;
		}
	}

	public function openSend($message,$phone){
		$app = JFactory::getApplication();

		if(!isset($this->mobistarHelper)) $this->afterSaveConfig($this);
		$result = $this->mobistarHelper->sendSMS(array($phone), $message);
		if(!$result){
			$this->errors[] = $this->mobistarHelper->error_str;
			return false;
		}
		return true;
	}


	public function displayConfig(){
		?>
		<script langage="javascript">
			window.addEvent('load', function(){hideActivationCodeButton();});
			function hideActivationCodeButton(){
				if(document.getElementById('senderprofile_activationCode').value.length != 0) document.getElementById('activationCodeButton').style.display="none";
			}
			function resetActivation(){
				document.getElementById('activationCodeButton').style.display="block";
				document.getElementById('senderprofile_activationCode').value = "";
				resetRegistered();
			}

			function resetRegistered(){
				document.getElementById('registered').value = "";
				document.getElementById('apiPassword').value = "";
			}
		</script>
		<table>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input type="password" onChange="resetActivation();" name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_phone"><?php echo JText::_('SMS_PHONE')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][phone]" id="senderprofile_phone" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->phone,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_email"><?php echo JText::_('SMS_EMAIL')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][email]" id="senderprofile_email" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->email,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr id="activationCodeButton">
				<td colspan="1"></td>
				<td>
					<button class="btn" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('save')"; else echo "submitbutton('save')"; ?> "><?php echo JText::_('SMS_GET_ACTIVATION_CODE')?></button>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_activationCode"><?php echo JText::_('SMS_ACTIVATION_CODE_MOBISTAR')?></label>
				</td>
				<td>
					<input type="text" onChange="resetRegistered();" name="data[senderprofile][senderprofile_params][activationCode]" id="senderprofile_activationCode" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->activationCode,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>
		<input type="hidden" id="registered" name="data[senderprofile][senderprofile_params][registered]" value="<?php echo htmlspecialchars(@$this->registered,ENT_COMPAT, 'UTF-8'); ?>" />
		<input type="hidden" id="apiPassword" name="data[senderprofile][senderprofile_params][apiPassword]" value="<?php echo htmlspecialchars(@$this->apiPassword,ENT_COMPAT, 'UTF-8'); ?>" />
	<?php
	}

	public function afterSaveConfig($senderprofile){
		$app = JFactory::getApplication();
		$this->mobistarHelper = new mobistarHelper($this->phone,$this->email,$this->password,'');

		if(empty($this->activationCode)){
			$result = $this->mobistarHelper->startRegistration();
			if(!$result){
				$app->enqueueMessage('Error while trying to register','error');
				return false;
			}
		}
		else{
			if(empty($this->registered) ){
			$result = $this->mobistarHelper->verifyRegistration($this->phone,$this->activationCode);
			if(!$result) $app->enqueueMessage('Error while trying to verify the registration : '.$this->mobistarHelper->error_str,'error');

			}
			else{
				$senderprofileClass = ACYSMS::get('class.senderprofile');
				$mobistarGateway = $senderprofileClass->get($senderprofile->senderprofile_name);
				$mobistarGateway->senderprofile_params['registered'] = true;
				$mobistarGateway->senderprofile_params['apiPassword'] = $this->mobistarHelper->password;
				$mobistarGateway->senderprofile_params = serialize($mobistarGateway->senderprofile_params);
				$senderprofileClass->save($mobistarGateway);
			}
		}
		if(!empty($this->apiPassword)) $this->mobistarHelper->password = $this->apiPassword;
	}

	public function closeSend($openSendResult){
		return $openSendResult;
	}
}
