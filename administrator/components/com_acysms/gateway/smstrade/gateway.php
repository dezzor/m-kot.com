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

class ACYSMSGateway_smstrade_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $route;
	public $from;
	public $key;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "gateway.smstrade.de";
	public $port = 80;

	public $name = 'Smstrade';

	public function openSend($message,$phone){

		$params = array();
		$params['to'] =  $this->checkNum($phone);
		$params['key'] =  $this->key;
		$params['from'] = $this->from;
		$params['username'] = $this->username;
		$params['route'] = $this->route;
		$params['message'] = $message;
		$params['concat'] = 1;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}

		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST / HTTP/1.1\r\n";
		$fsockParameter.= "Host: gateway.smstrade.de\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){

		$config = ACYSMS::config();
		$routeData[] = JHTML::_('select.option', 'basic', 'Basic', 'value', 'text');
		$routeData[] = JHTML::_('select.option', 'gold', 'Gold', 'value', 'text');
		$routeData[] = JHTML::_('select.option', 'direct', 'Direct', 'value', 'text');

		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
		?>
		<table>
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
					<label for="senderprofile_route"><?php echo JText::_('SMS_ROUTE')?></label>
				</td>
				<td>
					<?php echo $routeOptions; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_password" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','SMSTrade').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=smstrade&pass='.$config->get('pass').'</li>';
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','SMSTrade').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=smstrade&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
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
		$errors['10'] = 'Receiver number not valid (Parameter: to) => Use a valid format, e.g. 491701231231';
		$errors['20'] = 'Sender number not valid (Parameter: from) => Use max 11 characters of text or max 16 integer digits';
		$errors['30'] = 'Message text not valid => Use max 160 characters of text or the parameter Parameter “&concat_sms=1“ ';
		$errors['31'] = 'Message type not valid => Remove message type or use one of the following types: flash, unicode, binary, voice. ';
		$errors['40'] = 'SMS route not valid => The following routes are valid : basic, gold, direct';
		$errors['50'] = 'Identification failed => Check the gateway key';
		$errors['60'] = 'The Nexmo platform was unable to process this message, for example, an un-recognized number prefix';
		$errors['70'] = 'Not enough balance in account => Recharge your balance';
		$errors['71'] = 'Feature is not possible by the route => Choose a different route';
		$errors['80'] = 'Handover to SMSC failed => Choose a different route or contact Support for further information';


		if($res == 100 ) return true;
		else{
			$this->errors[] = isset($errors[$res]) ? $errors[$res] : 'Unknown error : '.$res;
			return false;
		}
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /credits/?key=".$this->key." HTTP/1.1\r\n";
		$fsockParameter.= "Host: gateway.smstrade.de\r\n";
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


		if($res != "ERROR"){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',strip_tags($res)), 'message');
		}else{
				$app->enqueueMessage('Error : There is an error with your Key or your secret var..','warning');
		}
	}
}
