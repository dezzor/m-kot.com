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


class ACYSMSGateway_callfire_gateway extends ACYSMSGateway_default_gateway{

	public $login;
	public $password;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = true;


	public $name = 'Callfire';

	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_login"><?php echo JText::_('SMS_API_LOGIN'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->login,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_API_PASSWORD')?></label>
				</td>
				<td>
					<input type="password" name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"  style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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

	public function open(){
		if(!class_exists("SoapClient")){
			$this->errors[] = "SOAP disable on your server, please enable it to send messages with Callfire.";
			return false;
		}
		try {
			$wsdl = 'http://callfire.com/api/1.1/wsdl/callfire-service-http-soap12.wsdl';
			$this->client = new SoapClient($wsdl, array(
					'login' => $this->login,
					'password' => $this->password));

		} catch (Exception $e) {
				$this->errors[] = $e->getMessage(). "\n";
				return false;
		}

		if(!is_object($this->client)) return false;
		return true;
	}

	public function openSend($message,$phone){
		try{
			$config= ACYSMS::config();
			$postBackURL = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=callfire&pass='.$config->get('pass');

			$request = new stdClass();
			$request->Subscription = new stdClass();
			$request->Subscription->Endpoint = $postBackURL;
			$request->Subscription->NotificationFormat = 'XML';
			$request->Subscription->TriggerEvent = 'INBOUND_TEXT_FINISHED';
			$subscriptionId = $this->client->CreateSubscription($request);


			$request = new stdClass();
			$request->BroadcastName =  $this->from;
			$request->ToNumber = $this->checkNum($phone);
			$request->TextBroadcastConfig = new stdClass(); // required
			$request->TextBroadcastConfig->Message = $message;
			$broadcastId = $this->client->SendText($request);
			$return = true;
			if(!empty($broadcastId)) $return = $broadcastId;
			return $return;


		} catch (Exception $e) {
				$this->errors[] = $e->getMessage(). "\n";
				return false;
		}
	}

	public function closeSend($smsId){
		$this->smsid = $smsId;
		return true;
	}

	public function answer(){
		$phoneHelper = ACYSMS::get('helper.phone');
		$raw_data = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
		$apiAnswer = new stdClass();

		$xml = simplexml_load_string($raw_data);

		$apiAnswer = new stdClass();

		$apiAnswer->answer_date = (string)$xml->Text->Created;

		$apiAnswer->answer_body = (string)$xml->Text->Message;

		$apiAnswer->answer_from = '+'.$xml->Text->FromNumber;
		$apiAnswer->answer_to = '+'.$xml->Text->ToNumber;

		return $apiAnswer;
	}
}
