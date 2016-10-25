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

class ACYSMSGateway_routesms_gateway extends ACYSMSGateway_default_gateway{

	public $account = '';
	public $login;
	public $password;
	public $from;
	public $source;
	public $dlr = 1;
	public $domain;
	public $port;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;


	public $name = 'Route SMS';


	public function openSend($message,$phone){
		$params = array();
		$params['message'] = $message;
		$params['destination'] =  $this->checkNum($phone);
		$params['source'] = $this->source;
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['dlr'] = $this->dlr;
		$params['type'] = 0;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /bulksms/bulksms HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain.":".$this->port."\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

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
					<label for="senderprofile_source"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][source]" id="senderprofile_source" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->source,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_domain"><?php echo JText::_('SMS_SERVER')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][domain]" id="senderprofile_domain" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->domain,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_port"><?php echo JText::_('SMS_PORT')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][port]" id="senderprofile_port" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->port,ENT_COMPAT, 'UTF-8');?>" />
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

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}

		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

		$errors = array(
						'1702' => 'One of the parameters was not provided',
						'1703' => 'Invalid Username or Password',
						'1704' => 'invalid Type field',
						'1705' => 'Invalid message',
						'1706' => 'Invalid destination',
						'1707' => 'Invalid Source',
						'1708' => 'Invalid value for dlr field',
						'1709' => 'User validation failed',
						'1710' => 'Internal error',
						'1025' => 'Insufficient Credit',
						'1715' => 'Response timeout');

		$split = explode('|',$res);
		if(!empty($split[2])){
			$this->smsid = $split[2];
			return true;
		}else{
			if(isset($errors[intval($split[0])])) $this->errors[] = $errors[intval($split[0])];
			else $this->errors[] = $res;
			return false;
		}
	}
}
