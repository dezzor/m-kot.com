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

class ACYSMSGateway_websms_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $messageContent;
	public $recipientAddressList;
	public $senderAddress;
	public $senderAddressType;
	public $notificationCallbackUrl;
	public $authToken;
	public $apiKey;
	public $test;

	public $waittosend= 0;

	public $errors = array();
	public $debug = false;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = "ssl://api.websms.com";
	public $port = 443;

	public $name = 'Web SMS (Beta)';


	public function openSend($message,$phone){
		$config = ACYSMS::config();
		$app = JFactory::getApplication();

		$objectToPost = new stdClass();
		$objectToPost->messageContent = $message;
		$objectToPost->recipientAddressList =  array($this->checkNum($phone));
		$objectToPost->senderAddress =  $this->senderAddress;
		$objectToPost->notificationCallbackUrl = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=websms&pass='.$config->get('pass');
		$objectToPost->senderAddressType = $this->senderAddressType;
		$stringToPost = '';
		$stringToPost = json_encode(($objectToPost));

		$fsockParameter = "POST /json/smsmessaging/text HTTP/1.1\r\n";
		$fsockParameter.="Authorization: Basic ".base64_encode($this->username.":".$this->password)."\r\n";
		$fsockParameter.= "Host: api.websms.com\r\n";
		$fsockParameter.= "Content-Type: application/json;charset=UTF-8\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter .= $stringToPost;

		return  $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$senderAddressTypes[] = JHTML::_('select.option', 'national', 'National', 'value', 'text');
		$senderAddressTypes[] = JHTML::_('select.option', 'international', 'International', 'value', 'text');
		$senderAddressTypes[] = JHTML::_('select.option', 'alphanumeric', 'Alphanumeric', 'value', 'text');
		$senderAddressTypes[] = JHTML::_('select.option', 'shortcode', 'Shortcode', 'value', 'text');

		$senderAddressTypeOptions =  JHTML::_('select.genericlist', $senderAddressTypes, "data[senderprofile][senderprofile_params][senderAddressType]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->senderAddressType);
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
					<label for="senderprofile_apiKey"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" type="password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_senderAddress"><?php echo JText::_('SMS_FROM')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderAddress]" id="senderprofile_senderAddress" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderAddress,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_senderAddressType"><?php echo JText::_('SMS_TYPE')?></label>
				</td>
				<td>
					<?php echo $senderAddressTypeOptions; ?>
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


		$jsonExpression = substr($res,strpos($res,"{"),(strrpos($res,"}")-strpos($res,"{"))+1);
		$answer = json_decode($jsonExpression);

		if(!empty($answer->statusMessage) && $answer->statusMessage == 'OK'){
			$this->smsid = $answer->transferId;
			return true;
		}
		else{
			if(!empty($answer->statusMessage))	$this->errors[] = $answer->statusMessage;
			else $this->errors[] = $jsonExpression;
		}
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');
		$raw_data = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
		$apiAnswer = new stdClass();


		$status[2000] = array(1,	"Die Nachricht wurde vom websms Gateway akzeptiert.");
		$status[2001] = array(	"Die Nachricht wurde zum Versand in die Warteschlange übernommen.");
		$status[4001] = array(	"Zugriff aufgrund falscher Zugangsdaten verweigert.");
		$status[4002] = array(	"Der angegebene Empfänger ist ungültig.");
		$status[4003] = array(	"Der angegebene Absender ist ungültig oder wurde am websms Gateway nicht freigeschalten.");
		$status[4008] = array(	"Die angegebene Nachrichten ID ist ungültig.");
		$status[4013] = array(	"Das zugelassene Nachrichtenlimit wurde erreicht.");
		$status[4014] = array(	"Die IP Adresse des Absenders ist nicht für den Login authorisiert.");
		$status[4015] = array(	"Der angegebene Wert für die Priorität der Nachricht ist ungültig.");
		$status[4016] = array(	"Die angegebene Antwortadresse für Empfangsbestätigungen ist ungültig");
		$status[4019] = array(	"Ein Pflichtparameter wurde nicht übergeben. Der fehlende Parameter steht in der Statusnachricht.");
		$status[4020] = array(	"Ungültiger API Key.");
		$status[4021] = array(	"Ungültiger Auth-Token.");
		$status[4022] = array(	"Schnittstellenzugriff verweigert.");
		$status[4023] = array(	"Request Limit wurde für diese IP überschritten.");
		$status[4025] = array(	"Anzahl der Empfänger wurde überschritten.");
		$status[4026] = array(	"Anzahl der Segmente pro SMS wurde überschritten.");
		$status[4027] = array(	"Ein Nachrichtensegment ist ungültig.");
		$status[5000] = array(	"Ein interner Fehler ist aufgetreten");
		$status[5003] = array(	"Das Service ist zurzeit nicht verfügbar.");

		$request = json_decode($raw_data);

		if(!empty($request->deliveryReportMessageStatus)){

			$completed_time = $request->deliveredOn;
			if(empty($completed_time)){
				$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
				$apiAnswer->statsdetails_received_date = time();
			}else $apiAnswer->statsdetails_received_date = $completed_time;

			$messageStatus = $request->deliveryReportMessageStatus;
			if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

			$smsId = $request->transferId;
			if(empty($smsId)){
				$apiAnswer->statsdetails_error[] = 'Can t find the message_id';
			}
			$apiAnswer->statsdetails_sms_id = $smsId;

			if($messageStatus == 'delivered')	$apiAnswer->statsdetails_status = 5;
			else $apiAnswer->statsdetails_status = -99;

			return $apiAnswer;

		}else if(!empty($request->recipientAddress)){

			$apiAnswer = new stdClass();

			$apiAnswer->answer_body = $request->textMessageContent;

			$sender = $request->senderAddress;
			$msisdn = $request->recipientAddress;

			if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
			if(!empty($msisdn))	$apiAnswer->answer_to = '+'.$msisdn;

			return $apiAnswer;
		}

	}

	public function deliveryReport(){
		return $this->answer();
	}

	public function closeRequest(){
		header('Content-Type: application/json');
		$newAnswer = new stdClass();
		$newAnswer->StatusCode = 2000;
		$newAnswer->StatusMessage = "ok";
		echo json_encode($newAnswer);
	 }
}
