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
class ACYSMSGateway_iphoneps_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $name = 'Iphoneps';

	public $domain = "iphone.ps";
	public $port = 80;

	public function openSend($message,$phone){

		$params = array();

		$params['message'] = $message;

		$params['numbers'] = $this->checkNum($phone);
		$params['user'] = $this->username;
		$params['password'] = $this->password;

		if(!empty($this->senderid))	$params['sender'] = $this->senderid;

		if($this->unicodeChar($message))	$params['lang'] = 'ar';
		else	$params['lang'] = 'en';

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sendsms.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: iphone.ps\r\n";
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
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
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

		if(strpos($res, ':') === false){
			$this->errors[] = $res;
			return false;
		}else{
			$res = explode(':', $res);
			if($res[0] == '1') return true;
			else if($res[0] == '0'){
				$this->errors[] = 'Invalid Transmission';
				return false;
			}
		}

	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /sendsms.php?user=".$this->username."&password=".$this->password."&action=get HTTP/1.1\r\n";
		$fsockParameter.= "Host: iphone.ps\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			$app->enqueueMessage(implode('<br />',$this->errors), 'error');
			return false;
		}

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return;
		}
		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(is_numeric($res))	$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res), 'message');
		else	$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res), 'error');
	}
}
