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


class ACYSMSGateway_nexmotexttospeech_gateway extends ACYSMSGateway_default_gateway{

	public $from;
	public $key;
	public $secret;
	public $delay = 0;
	public $lg;
	public $text;
	public $voice;
	public $repeat;
	public $machine_detection;
	public $machine_timeout;
	public $speaking_rate;

	public $errors = array();
	public $debug = false;

	public $sendMessage = false;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "rest.nexmo.com";
	public $port = 80;


	public $name = 'Nexmo Text To Speech';



	public function openSend($message,$phone){
		$params = array();
		if(!empty($this->delay))	$message = '<break time="'.$this->delay.'s"/>'.$message;

		if(!empty($this->speaking_rate)) $message = '<prosody rate="'.$this->speaking_rate.'%">'.$message.'</prosody>';


		$params['text'] = $message;
		$params['to'] =  $this->checkNum($phone);
		$params['lg'] = $this->lg;
		$params['voice'] = $this->voice;
		$params['api_key'] = $this->key;
		$params['api_secret'] = $this->secret;
		$params['machine_detection'] = $this->machine_detection;
		$params['machine_timeout'] = $this->machine_timeout;
		$params['from'] = $this->from;
		$params['repeat'] = $this->repeat;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /tts/json?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: rest.nexmo.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$voice = array();
		$voice[] = JHTML::_('select.option', 'female', 'Female', 'value', 'text');
		$voice[] = JHTML::_('select.option', 'male', 'Male', 'value', 'text');
		$voiceOptions =  JHTML::_('select.genericlist', $voice, "data[senderprofile][senderprofile_params][voice]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->voice);

		$machineDetection = array();
		$machineDetection[] = JHTML::_('select.option', '----', '----', 'value', 'text');
		$machineDetection[] = JHTML::_('select.option', 'true', 'True', 'value', 'text');
		$machineDetection[] = JHTML::_('select.option', 'hangup', 'Hangup', 'value', 'text');
		$machineDetectionOptions =  JHTML::_('select.genericlist', $machineDetection, "data[senderprofile][senderprofile_params][machine_detection]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->machine_detection);

		$nbrepeat = array();
		for($i=1;$i<11;$i++){
			$nbrepeat[] = JHTML::_('select.option', $i, $i, 'value', 'text');
		}
		$nbrepeat = JHTML::_('select.genericlist', $nbrepeat, "data[senderprofile][senderprofile_params][repeat]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->repeat);


		$language = array();
		$language[] = JHTML::_('select.option', 'en-us', 'En-US', 'value', 'text');
		$language[] = JHTML::_('select.option', 'zh-cn', 'Zh-Cn', 'value', 'text');
		$language[] = JHTML::_('select.option', 'ja-jp', 'Ja-Jp', 'value', 'text');
		$language[] = JHTML::_('select.option', 'ko-kr', 'Ko-Kr', 'value', 'text');
		$language[] = JHTML::_('select.option', 'es-mx', 'Es-Mx', 'value', 'text');
		$language[] = JHTML::_('select.option', 'fr-ca', 'Fr-Ca', 'value', 'text');

		$languageOptions =  JHTML::_('select.genericlist', $language, "data[senderprofile][senderprofile_params][lg]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->lg);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM').' : '?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_key"><?php echo JText::_('SMS_KEY')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][key]" id="senderprofile_key" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->key,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_secret"><?php echo JText::_('SMS_SECRET')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][secret]" id="senderprofile_secret" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->secret,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_voice"><?php echo JText::_('SMS_VOICE').' : '?></label>
				</td>
				<td>
					<?php echo $voiceOptions; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_language"><?php echo JText::_('SMS_LANGUAGE').' : '?></label>
				</td>
				<td>
					<?php echo $languageOptions; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_machine_detection"><?php echo 'Machine Detection'; ?></label>
				</td>
				<td>
					<?php echo $machineDetectionOptions; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_machine_timeout"><?php echo 'Machine Timeout'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][machine_timeout]" id="senderprofile_machine_timeout" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->machine_timeout,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_machine_timeout"><?php echo 'Repeat'; ?></label>
				</td>
				<td>
					<?php echo $nbrepeat; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_speaking_rate"><?php echo 'Speaking Rate'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][speaking_rate]" id="senderprofile_speaking_rate" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->speaking_rate,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="senderprofile_delay"><?php echo JText::_('SMS_DELAY_TO_CALL').' : <input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][delay]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->delay).'" /> seconds';?></label>
				</td>
			</tr>

		</table>

	<?php
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

		$res = json_decode($res);
		$errorText = 'error-text';
		if(empty($res->$errorText)) return true;
		else{
			$this->errors[] = $res->$errorText;
			return false;
		}
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$stringToPost = urlencode($this->key)."/".urlencode($this->secret);
		$page = '/account/get-balance/';
		$fsockParameter = "GET ".$page.$stringToPost." HTTP/1.1\r\n";
		$fsockParameter .=	"Host: rest.nexmo.com\r\n";
		$fsockParameter .=	"Accept: application/xml\r\n\r\n";


		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			$app->enqueueMessage('<div onclick="document.getElementById(\'errors\').style.display=\'block\';" style="cursor:pointer"> Authentification error : Click to see it</div><div id="errors" style="display:none">'.implode('<br />',$this->errors).'</div>', 'error');
			return false;
		}

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(preg_match('#<value>(.*)</value>#Ui', $res, $explodedResults)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',trim($explodedResults[1]), 'message'));
		}else{
				$app->enqueueMessage('Error : There is an error with your Key or your secret var..','warning');
		}
	}
}
