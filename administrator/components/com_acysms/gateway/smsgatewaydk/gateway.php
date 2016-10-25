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

class ACYSMSGateway_smsgatewaydk_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = 'smschannel1.dk';
	public $port = 80;


	public $name = 'SmsGateway.dk';

	public function openSend($message,$phone){

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params = array();
		$params['to'] =  $this->checkNum($phone);
		$params['from'] = $encodeHelper->change($this->from,'UTF-8','ISO-8859-1');
		$params['message'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
		$params['username'] = $encodeHelper->change(urlencode($this->username),'UTF-8','ISO-8859-1');
		$params['password'] = $encodeHelper->change(urlencode($this->password),'UTF-8','ISO-8859-1');

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /sendsms/?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: smschannel1.dk\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		return $this->sendRequest($fsockParameter);
	}


	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_username"><?php echo JText::_('SMS_USERNAME')?></label>
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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

	protected function interpretSendResult($res){
		if(!strpos($res,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$res;
			return false;
		}

		$res = substr($res,strpos($res,"\r\n\r\n"));

		if(preg_match('#<status>(.*)</status>#Ui', $res, $explodedResults)){
			if(strpos($explodedResults[1], 'succes')) return true;
			else{
				if(preg_match('#<error>(.*)</error>#Ui', $explodedResults[1], $explodedResults2)){
					$this->errors[] = $explodedResults2[1];
				}
				if(preg_match('#<code>(.*)</code>#Ui', $explodedResults[1], $explodedResults2)){
					$this->errors[] = 'Error Code :'.$explodedResults2[1];
				}
				return false;
			}
		}
	}

}
