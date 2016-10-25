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

class ACYSMSGateway_twilio_gateway extends ACYSMSGateway_default_gateway{


	public $from;
	public $accoundSID;
	public $token;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = true;

	public $domain = "ssl://api.twilio.com";
	public $port = 443;

	public $handleMMS = true;


	public $name = 'Twilio';

	public function openSend($message,$phone){
		$params = array();
		$params['To'] =  $this->checkNum($phone);
		$params['From'] = $this->from;
		$params['Body'] = $message;

		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		if(!empty($this->fullMessage->message_attachment)) {
			jimport('joomla.filesystem.file');
			$importHelper = ACYSMS::get('helper.import');
			$uploadPath = $importHelper->getUploadDirectory();
			$imageLink = str_replace(ACYSMS_ROOT,ACYSMS_LIVE,$uploadPath);
			$imageNames = explode(',',trim($this->fullMessage->message_attachment,","));
			if(!empty($imageNames)) $params['MediaUrl'] = array();

			foreach($imageNames as $oneImageName) {
				$params['MediaUrl'][] = $imageLink.$oneImageName;
			}
		}

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			if($oneParam == "MediaUrl") {
				foreach($value as $oneValue) {
					$stringToPost .='&'.$oneParam.'='.urlencode($oneValue);
				}
				continue;
			}

			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}

		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /2010-04-01/Accounts/".$this->accoundSID."/Messages.xml HTTP/1.1\r\n";
		$fsockParameter.= "Host: api.twilio.com\r\n";
		$fsockParameter .= "Authorization: Basic ".base64_encode ($this->accoundSID.':'.$this->token)."\r\n";
		$fsockParameter.= "Content-Type: application/x-www-form-urlencoded\r\n";
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
					<label for="senderprofile_accoundSID"><?php echo JText::_('SMS_ACCOUNT_SID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][accoundSID]" id="senderprofile_accoundSID" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->accoundSID,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_token"><?php echo JText::_('SMS_ACCOUNT_TOKEN'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][token]" id="senderprofile_token" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->token,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Twilio').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=twilio&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	protected function interpretSendResult($result){
		if(strpos(strtolower($result), strtolower('201 Created')) === false){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(preg_match('#<RestException>(.*)</RestException>#Ui',$res,$results)){
			if(preg_match('#<Message>(.*)</Message>#Ui',$results[1],$message)){
				$this->errors[] = trim($message[1]);
				return false;
			}
			else{
				$this->errors[] = 'Could not interpret the answer => '.$res;
				return false;
			}
		}
		else{
			return true;
		}
	}

	protected function checkNum($phone){
		return $phone;
	}

	public function answer(){
		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = time();
		$apiAnswer->answer_body = JRequest::getString("Body",'');
		$from = JRequest::getString("From",'');
		$to = JRequest::getString("To",'');
		$numberMedia = JRequest::getInt("NumMedia",0);

		if(!empty($from))	$apiAnswer->answer_from = $from;
		if(!empty($to))	$apiAnswer->answer_to = $to;

		$mediaUrl = array();
		for($i=0; $i<$numberMedia; $i++) {
			$mediaUrl[] = JRequest::getString("MediaUrl".$i,'');
		}

		$apiAnswer->answer_attachment = implode(',',$mediaUrl);

		$apiAnswer->answer_sms_id = JRequest::getString("SmsMessageSid",'');

		return $apiAnswer;
	}
}
