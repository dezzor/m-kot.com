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

class ACYSMSGateway_nexmoshortcode_gateway extends ACYSMSGateway_default_gateway{

	public $from;
	public $key;
	public $secret;
	public $smsid;
	public $nbtemplate;
	public $waittosend= 0;

	public $errors = array();
	public $debug = false;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "rest.nexmo.com";
	public $port = 80;


	public $name = 'Nexmo (Short codes only)';

	public function openSend($message,$phone){
		$params = array();
		$plgHelper = ACYSMS::get('helper.plugins');

		if(!empty($message)){ //we don't have any tags
			if(!preg_match_all('#(?:{|%7B)extraparam_([a-z0-9]*):(.*)(?:}|%7D)#Ui',$message,$results)) {
				$params['content'] = $message;
			} else { //we have any tags
				foreach($results[1] as $oneId => $oneTag){
					if(empty($oneTag) || empty($results[2][$oneId])) continue;
					if(!empty($params[$oneTag])) continue;
					$params[$oneTag] =  $results[2][$oneId];
				}
			}
		}

		$params['to'] =  $this->checkNum($phone);
		$params['api_key'] = $this->key;
		$params['api_secret'] = $this->secret;
		$params['template'] = $this->nbtemplate;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sc/us/alert/xml HTTP/1.1\r\n";
		$fsockParameter.= "Host: rest.nexmo.com\r\n";
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
					<label for="senderprofile_key"><?php echo JText::_('SMS_KEY');?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][key]" id="senderprofile_key" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->key,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_secret"><?php echo JText::_('SMS_SECRET');?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][secret]" id="senderprofile_secret" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->secret,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_nbtemplate"><?php echo 'Template Number';?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][nbtemplate]" id="senderprofile_nbtemplate" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->nbtemplate,ENT_COMPAT, 'UTF-8');?>" />
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
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		$errors = array();
		$errors['1'] = 'You have exceeded the submission capacity allowed on this account, please back-off and retry';
		$errors['2'] = 'Your request is incomplete and missing some mandatory parameters';
		$errors['3'] = 'The value of one or more parameters is invalid';
		$errors['4'] = 'The username / password you supplied is either invalid or disabled';
		$errors['5'] = 'An error has occurred in the nexmo platform whilst processing this message';
		$errors['6'] = 'The Nexmo platform was unable to process this message, for example, an un-recognized number prefix';
		$errors['7'] = 'The number you are trying to submit to is blacklisted and may not receive messages';
		$errors['8'] = 'The username you supplied is for an account that has been barred from submitting messages';
		$errors['9'] = 'Your pre-pay account does not have sufficient credit to process this message';
		$errors['10'] = 'The number of simultaneous connections to the platform exceeds the capabilities of your account';
		$errors['11'] = 'This account is not provisioned for REST submission, you should use SMPP instead';
		$errors['12'] = 'Applies to Binary submissions, where the length of the UDH and the message body combined exceed 140 octets';

		if(preg_match('#<messageId>(.*)</messageId>#Ui', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
		}

		if(preg_match('#<status>(.*)</status>#Ui', $res, $explodedResults)){
			if($explodedResults[1] == '0') return true;
			else{
				$this->errors[] = isset($errors[$explodedResults[1]]) ? $errors[$explodedResults[1]] : 'Unknown error : '.$res;
				return false;
			}
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$stringToPost = urlencode($this->key)."/".urlencode($this->secret);
		$page = '/account/get-balance/';

		$fsockParameter = "GET ".$page.$stringToPost." HTTP/1.1\r\n";
		$fsockParameter .=	"Host: rest.nexmo.com\r\n";
		$fsockParameter .=	"Accept: application/xml\r\n";
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

		if(preg_match('#<value>(.*)</value>#Ui', $res, $explodedResults)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',trim($explodedResults[1]), 'message'));
		}else{
				$app->enqueueMessage('Error : There is an error with your Key or your secret var..','warning');
		}
	}

	protected function checkNum($phone){
		$internationalPhone = str_replace('+', '', $phone);
		return preg_replace('#[^0-9]#','',$internationalPhone);
	}
}
