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


class ACYSMSGateway_aruba_gateway extends ACYSMSGateway_default_gateway{

	public $user;
	public $sender;
	public $pass;
	public $qty;
	public $date;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "admin.sms.aruba.it";
	public $port = 80;

	public $name = 'Aruba';

	public function openSend($message,$phone){

		$params = array();
		$params['rcpt'] =  $phone;
		$params['sender'] = $this->sender;
		$params['data'] = $message;
		$params['qty'] = $this->qty;
		$params['user'] = $this->user;
		$params['pass'] = $this->pass;

		if(strlen($message)>160){
			$this->errors[] = JText::_('SMS_MAX_CHARACTERS_REACHED');
			return false;
		}

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /sms/batch.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: admin.sms.aruba.it\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){

		$config = ACYSMS::config();
		$qualityData[] = JHTML::_('select.option', 'll', 'Low', 'value', 'text');
		$qualityData[] = JHTML::_('select.option', 'l', 'Middle', 'value', 'text');
		$qualityData[] = JHTML::_('select.option', 'a', 'Automatic', 'value', 'text');
		$qualityData[] = JHTML::_('select.option', 'h', 'High', 'value', 'text');
		$qualityData[] = JHTML::_('select.option', 'n', 'High with notification', 'value', 'text');

		$qualityOptions =  JHTML::_('select.genericlist', $qualityData, "data[senderprofile][senderprofile_params][qty]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->qty);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_user"><?php echo JText::_('SMS_USERNAME')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][user]" id="senderprofile_user" placeholder="sms..." class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->user,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_pass"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][pass]" id="senderprofile_pass" type="password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->pass,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_sender"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender]" id="senderprofile_sender" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_quality">Quality</label>
				</td>
				<td>
					<?php echo $qualityOptions; ?>
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

		if(!strpos($result,'OK')){
			$this->errors[] = 'Error 200 KO => '.substr($result,strpos($result,"\r\n\r\n"));
			return false;
		}
		else $res = substr($result,strpos($result,"\r\n\r\n"));

		if(!strpos($res,'OK')){
			$this->errors[] = $res;
			return false;
		}
		return true;
	}
}
