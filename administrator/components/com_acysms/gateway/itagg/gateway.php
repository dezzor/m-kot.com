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

class ACYSMSGateway_itagg_gateway extends ACYSMSGateway_default_gateway{

	public $account;
	public $login;
	public $password;
	public $from;
	public $route;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = false;

	public $domain = "ssl://secure.itagg.com";
	public $port = 443;

	public $name = 'Itagg';

	public function openSend($message,$phone){
		$config = ACYSMS::config();
		$params = array();
		$params['txt'] = $message;
		$params['to'] =  $this->checkNum($phone);
		$params['usr'] = $this->login;
		$params['pwd'] = $this->password;
		$params['from'] = $this->from;
		$params['route'] = $this->route;
		$params['type'] = 'text';
		if(!strpos(ACYSMS_LIVE,'localhost')) $params['dreceipt_url'] = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=itagg&pass='.$config->get('pass');


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /smsg/sms.mes HTTP/1.1\r\n";
		$fsockParameter.= "Host: secure.itagg.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$routeData[] = JHTML::_('select.option', '4', 'Budget', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '7', 'National UK', 'value', 'text');
		$routeData[] = JHTML::_('select.option', '8', 'Global', 'value', 'text');

		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_login"><?php echo JText::_('SMS_USERNAME')?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][login]" id="senderprofile_login" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->login,ENT_COMPAT, 'UTF-8');?>" />
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
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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


		$split = explode('|',$res);
		if(!empty($split['4']))	$this->smsid = substr($split['4'], 0, strpos($split['4'],'-'));

		if(strpos($res,"submitted")){
			return true;
		}else{
			$this->errors[] = strip_tags($res);
			return false;
		}
	}

	function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error  = array();





		$status['1'] = array(4 ,"Intermediate state notification that the message has not yet been delivered due to a phone related problem but is being retried.");
		$status['2'] = array(4 ,"Used to indicate that the message has not yet been delivered due to some operator related problem but is being retried within the network.");
		$status['3'] = array(2 ,"Used to indicate that the message has been accepted by the operator. For certain operators, this may be interpreted that the operator has reported successful billing of the subscriber.");
		$status['4'] = array(5 ,"The message was delivered.");
		$status['5'] = array(-1 ,"The message has been confirmed as undelivered but no detailed information related to the failure is known.");
		$status['6'] = array(-99 ,"We cannot determine whether this message has been delivered or has failed due to lack of final delivery state information from the operator");
		$status['7'] = array(4 ,"Used to indicate to the client that the message has not yet been delivered due to insufficient subscriber credit but is being retried within the network");
		$status['8'] = array(-1 ,"Used when a message expired (could not be delivered within the life time of the message) within the operator SMSC but is not associated with a reason for failure.");
		$status['20'] = array(-1 ,"Used when a message in its current form is undeliverable");
		$status['21'] = array(-1 ,"Only occurs where the operator accepts the message before performing the subscriber credit check. If there is insufficient credit then the operator will retry the message until the subscriber tops up or the message expires. If the message expires and the last failure reason is related to credit then this error code will be used.");
		$status['22'] = array(-1 ,"Only occurs where the operator performs the subscriber credit check before accepting the message and rejects messages if there are insufficient funds available");
		$status['23'] = array(-1 ,"Used when the message is undeliverable due to an incorrect / invalid / blacklisted / permanently barred MSISDN for this operator. This MSISDN should not be used again for message submissions to this operator, and the number should be deleted from your database.");
		$status['24'] = array(-1 ," Used when a message is undeliverable because the subscriber is temporarily absent, e.g. their phone is switch off, they cannot be located on the network. ");
		$status['25'] = array(-1 ,"Used when the message has failed due to a temporary condition in the operator network. This could be related to the SS7 layer, SMSC or gateway.");
		$status['26'] = array(-1 ,"sed when a message has failed due to a temporary phone related error, e.g. SIM card full, SME busy, memory exceeded etc. This does not mean the phone is unable to receive this type of message/content (refer to error code 27).");
		$status['27'] = array(-1 ," Used when a handset is permanently incompatible or unable to receive this type of message.");
		$status['28'] = array(-1 ,"Used if a message fails or is rejected due to suspicion of SPAM on the operator network. This could indicate in some geographies that the operator has no record of the mandatory MO required for an MT.");
		$status['29'] = array(-1 ,"Used when this specific content is not permitted on the network / shortcode.");
		$status['30'] = array(-1 ,"Used when message fails or is rejected because the subscriber has reached the predetermined spend limit for the current billing period");
		$status['31'] = array(-1 ,"Used when the MSISDN is for a valid subscriber on the operator but the message fails or is rejected because the subscriber is unable to be billed, e.g. the subscriber account is suspended (either voluntarily or involuntarily), the subscriber is not enabled for bill-to-phone services, the subscriber is not eligible for bill-to-phone services, etc.");
		$status['33'] = array(-1 ,"Age Verification error. See iTAGG Age Verification document for more details.");
		$status['34'] = array(-1 ,"Age Verification error. See iTAGG Age Verification document for more details.");
		$status['35'] = array(-1 ,"Age Verification error. See iTAGG Age Verification document for more details.");
		$status['36'] = array(-1 ,"Age Verification error. See iTAGG Age Verification document for more details.");
		$status['199'] = array(-1 ,"Age Verification error. See iTAGG Age Verification document for more details.");


		$xml =  JRequest::getVar('xml','','','string',JREQUEST_ALLOWRAW);

		if(preg_match('#<gmt_timestamp>(.*)</gmt_timestamp>#Ui', $xml, $explodedResults))	$apiAnswer->statsdetails_received_date = strtotime($explodedResults[1]);
		else {
			$apiAnswer->statsdetails_error[] =  'Unknow message received timestamp';
			$apiAnswer->statsdetails_received_date = time();
		}

		if(preg_match('#<reason>(.*)</reason>#Ui', $xml, $explodedResults)){
			$apiAnswer->statsdetails_status = $status[$explodedResults[1]][0];
			$apiAnswer->statsdetails_error[] = $status[$explodedResults[1]][1];
		}else{
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$explodedResults[1];
			$apiAnswer->statsdetails_status = -99;
		}

		if(preg_match('#<submission_ref>(.*)</submission_ref>#Ui', $xml, $explodedResults))	$apiAnswer->statsdetails_sms_id = (string)$explodedResults[1];
		else $apiAnswer->statsdetails_sms_id = '';


		return $apiAnswer;
	}
}
