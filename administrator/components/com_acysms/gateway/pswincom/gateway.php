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
class ACYSMSGateway_pswincom_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $from;
	public $password;
	public $waittosend= 0;
	public $messageToSend = array();
	public $messageResults = array();

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "secure.pswin.com/XMLHttpWrapper/process.aspx";
	public $port = 443;

	public $name = 'PSWinCom';

	public $indexMessage = 0;

	public function openSend($message,$phone){

		if(strlen($message)>804){
			$this->errors[] = JText::_('SMS_MAX_CHARACTERS_REACHED');
			return false;
		}

		$this->indexMessage += 1;
		$oneMessage = new stdClass();
		$oneMessage->rcv = $this->checkNum($phone);
		$oneMessage->txt = $message;

		$this->messageToSend[$this->indexMessage] = $oneMessage;
		return $this->indexMessage;
	}

	public function displayConfig(){
		$config = ACYSMS::config();
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_user"><?php echo JText::_('SMS_USER')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][user]" id="senderprofile_user" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->user,ENT_COMPAT, 'UTF-8');?>" />
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
				<td colspan="2">
					<label for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND','<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />');?></label>
				</td>
			</tr>
		</table>
		<?php
			if(strpos(ACYSMS_LIVE,'localhost') !== false)	echo JText::_('SMS_LOCALHOST_PROBLEM');
			else{
				echo '<ul id="gateway_addresses">';
				echo '<li>'.JText::sprintf('SMS_DELIVERY_ADDRESS','PSWinCom').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=pswincom&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	public function closeSend($idMessage){
		if(!empty($this->messageToSend)){
			$this->_sendMessages();
			$this->messageToSend = array();
		}

		if(empty($this->messageResults[$idMessage])){
			$this->errors[] = 'status not found for the message ID : '.$idMessage;
			$this->errors[] = print_r($this->messageResults,true);
			return false;
		}

		if(!empty($this->messageResults[$idMessage]->smsid)) $this->smsid  = $this->messageResults[$idMessage]->smsid;
		if(!empty($this->messageResults[$idMessage]->info)) $this->errors[] = $this->messageResults[$idMessage]->info;

		return $this->messageResults[$idMessage]->status;
	}

	private function _sendMessages(){

		$encodeHelper = ACYSMS::get('helper.encoding');

		$xml = array();
		$xml[] = "<?xml version=\"1.0\"?>";
		$xml[] = "<!DOCTYPE SESSION SYSTEM \"pswincom_submit.dtd\">";
		$xml[] = "<SESSION>";
		$xml[] = "<CLIENT>".$encodeHelper->change($this->user,'UTF-8','ISO-8859-1')."</CLIENT>";
		$xml[] = "<PW>".$encodeHelper->change($this->password,'UTF-8','ISO-8859-1')."</PW>";
		$xml[] = "<SD>gw2xmlhttpspost</SD>";
		$xml[] = "<MSGLST>";
		foreach($this->messageToSend as $oneMessageToSend){
			$xml[] = "<MSG>";
			$xml[] = "<TEXT>".$encodeHelper->change($oneMessageToSend->txt,'UTF-8','ISO-8859-1')."</TEXT>";
			$xml[] = "<RCV>".$encodeHelper->change($oneMessageToSend->rcv,'UTF-8','ISO-8859-1')."</RCV>";
			$xml[] = "<SND>".$encodeHelper->change($this->from,'UTF-8','ISO-8859-1')."</SND>";
			$xml[] = "<RCPREQ>".$encodeHelper->change('Y','UTF-8','ISO-8859-1')."</RCPREQ>";
			$xml[] = "</MSG>";
		}

		$xml[] = "</MSGLST>";
		$xml[] = "</SESSION>";
		$xmldocument = implode("\r\n", $xml)."\r\n\r\n";

		$params = array('http' =>
			array(
				'method' => 'POST',
				'header' => "Content-type: text/xml\r\n" .
				"Content-Length: " . strlen($xmldocument) . "\r\n",
				'content' => $xmldocument
			)
		);

		$ctx = stream_context_create($params);
		$fp = fopen("https://secure.pswin.com/XMLHttpWrapper/process.aspx", 'rb', false, $ctx);

		if(!$fp) return false;
		$response = @stream_get_contents($fp);

		$xmlString = substr($response,strpos($response,'<?xml'));
		$xmlObject = new SimpleXMLElement($xmlString);

		foreach($xmlObject->MSGLST->MSG as $key => $row){
			$answer = new stdClass();
			$answer->smsid = empty($row->REF) ? '' : intval($row->REF);
			if($row->STATUS == 'OK') $answer->status = true;
			else $answer->status = false;
			if(!empty($row->INFO)) $answer->info = $row->INFO;

			$this->messageResults[intval($row->ID)] = $answer;
		}
		$this->indexMessage = 0;
	}


	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();



		$status['DELIVRD'] = array(5 ,"SMS was successfully delivered to the receivers phone. ");
		$status['EXPIRED'] = array(-2 ,"The SMS expired while waiting to be delivered. The phone may be out of coverage or not switched on.");
		$status['UNDELIV'] = array(-1 ,"The SMS was undeliverable (not a valid number or no available route to destination).");
		$status['FAILED'] = array(-1 ,"The SMS failed to be delivered because no operator accepted the message or due to internal Gateway error.");
		$status['BARRED'] = array(-1 ,"The receiver number is barred/blocked/not in use. Do not retry message, and remove number from any subscriber list. (Relevant for CPA messages only)");
		$status['BARREDT'] = array(-1 ,"The receiver number is temporarily blocked. May be an empty pre-paid account. (Relevant for CPA messages only)");
		$status['ZERO_BAL'] = array(-1 ,"The receiver has an empty prepaid account. (Relevant for CPA messages only)");
		$status['INV_NET'] = array(-1 ,"Invalid network. Receiver number is not recognized by the target operator.");


		$completed_time = JRequest::getString("DELIVERYTIME",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = strtotime($completed_time);

		$messageStatus = JRequest::getString("STATE",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getString("REF",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}
		$apiAnswer->statsdetails_sms_id = (string)$smsId;

		return $apiAnswer;
	}
}
