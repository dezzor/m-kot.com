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
class ACYSMSGateway_lox24_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $from;
	public $password;
	public $service;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "www.lox24.eu";
	public $port = 80;

	public $name = 'LOX24';

	public function openSend($message,$phone){
		$params = array();
		$params['konto'] =  $this->username;
		$params['password'] =  md5($this->password);
		$params['text'] = $message;
		$params['from'] = $this->from;
		$params['to'] = $this->checkNum($phone);
		$params['return'] = 'xml';
		$params['service'] = $this->service;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /API/httpsms.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.lox24.eu\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$service[] = JHTML::_('select.option', '1903', 'Basic', 'value', 'text');
		$service[] = JHTML::_('select.option', '1904', 'Economic', 'value', 'text');
		$service[] = JHTML::_('select.option', '1905', 'Pro', 'value', 'text');

		$serviceOptions =  JHTML::_('select.genericlist', $service, "data[senderprofile][senderprofile_params][service]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->$service);
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" type="password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
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
				<td>
					<label for="senderprofile_route"><?php echo JText::_('SMS_SERVICE')?></label>
				</td>
				<td>
					<?php echo $serviceOptions; ?>
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
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));


		if(preg_match('#<code>(.*)</code>#Ui', $res, $explodedResults)){
			if($explodedResults[1] == 100 || $explodedResults[1] == 101 || $explodedResults[1] == 102 ){
				return true;
			}else{
				if(preg_match('#<codetext>(.*)</codetext>#Ui', $res, $explodedResults1)){
					$this->errors[] = 'Error Code :'.$explodedResults[1];
					$this->errors[] = $explodedResults1[1];
				}
			}
		}
	}
}
