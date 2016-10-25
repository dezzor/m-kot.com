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
class ACYSMSGateway_magfa_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $senderNumber;
	public $password;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "messaging.magfa.com";
	public $port = 80;


	public $name = 'Magfa';

	public function openSend($message,$phone){
		$params = array();
		$params['service'] = 'enqueue';
		$params['username'] =  $this->username;
		$params['password'] =  $this->password;
		$params['domain'] = 'magfa';
		$params['from'] = $this->senderNumber;
		$params['to'] = $this->checkNum($phone);
		$params['message'] = $message;
		$params['coding'] = "";
		$params['udh'] = "";
		$params['chkmessageid'] = "";


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /magfaHttpService?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: messaging.magfa.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		$config = ACYSMS::config();
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
					<label for="senderprofile_senderNumber"><?php echo 'Sender Number'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderNumber]" id="senderprofile_senderNumber" class="inputbox"	 style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderNumber,ENT_COMPAT, 'UTF-8');?>" />
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
				echo '<li>'.JText::sprintf('SMS_ANSWER_ADDRESS','Magfa').'<br />'.ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=magfa&pass='.$config->get('pass').'</li>';
				echo '</ul>';
			}
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /magfaHttpService?service=getCredit&username=".$this->username."&password=".$this->password."&domain=".$this->domain." HTTP/1.1\r\n";
		$fsockParameter.= "Host: sms.magfa.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		if ($idConnection == false)return;
		$result = $this->readResult($idConnection);

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));

		if(!strpos($res,'ERR'))		$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',strip_tags($res)), 'message');
		else $app->enqueueMessage(strip_tags($res), 'error');
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));


		if($res <= 1000) {
				$this->errors[] = $this->getErrors($res);
				return false;
		} else {
				return true;
		}
	}

	public function getErrors($errNo){

		$errors = array();

		$errors[1]['title'] = 'INVALID_RECIPIENT_NUMBER';
		$errors[1]['desc'] = 'the string you presented as recipient numbers are not valid phone numbers, please check them again';

		$errors[2]['title'] = 'INVALID_SENDER_NUMBER';
		$errors[2]['desc'] = 'the string you presented as sender numbers(3000-xxx) are not valid numbers, please check them again';

		$errors[3]['title'] = 'INVALID_ENCODING';
		$errors[3]['desc'] = 'are You sure You\'ve entered the right encoding for this message? You can try other encodings to bypass this error code';

		$errors[4]['title'] = 'INVALID_MESSAGE_CLASS';
		$errors[4]['desc'] = 'entered MessageClass is not valid. for a normal MClass, leave this entry empty';

		$errors[6]['title'] = 'INVALID_UDH';
		$errors[6]['desc'] = 'entered UDH is invalid. in order to send a simple message, leave this entry empty';

		$errors[10]['title'] = 'INVALID_PRIORITY';
		$errors[10]['desc'] = 'Priority parameter is invalid. ( Only valid values ​​for this parameter are considered )';

		$errors[12]['title'] = 'INVALID_ACCOUNT_ID';
		$errors[12]['desc'] = 'you\'re trying to use a service from another account??? check your UN/Password/NumberRange again';

		$errors[13]['title'] = 'NULL_MESSAGE';
		$errors[13]['desc'] = 'check the text of your message. it seems to be null';

		$errors[14]['title'] = 'CREDIT_NOT_ENOUGH';
		$errors[14]['desc'] = 'Your credit\'s not enough to send this message. you might want to buy some credit.call';

		$errors[15]['title'] = 'SERVER_ERROR';
		$errors[15]['desc'] = 'something bad happened on server side, you might want to call MAGFA Support about this:';

		$errors[16]['title'] = 'ACCOUNT_INACTIVE';
		$errors[16]['desc'] = 'Your account is not active right now, call -- to activate it';

		$errors[17]['title'] = 'ACCOUNT_EXPIRED';
		$errors[17]['desc'] = 'looks like Your account\'s reached its expiration time, call -- for more information';

		$errors[18]['title'] = 'INVALID_USERNAME_PASSWORD_DOMAIN'; // todo : note : one of them are empty
		$errors[18]['desc'] = 'the combination of entered Username/Password/Domain is not valid. check\'em again';

		$errors[19]['title'] = 'AUTHENTICATION_FAILED'; // todo : note : wrong arguments supplied ...
		$errors[19]['desc'] = 'You\'re not entering the correct combination of Username/Password';

		$errors[20]['title'] = 'SERVICE_TYPE_NOT_FOUND';
		$errors[20]['desc'] = 'check the service type you\'re requesting. we don\'t get what service you want to use. your sender number might be wrong, too.';

		$errors[22]['title'] = 'ACCOUNT_SERVICE_NOT_FOUND';
		$errors[22]['desc'] = 'your current number range doesn\'t have the permission to use Webservices';

		$errors[23]['title'] = 'SERVER_BUSY';
		$errors[23]['desc'] = 'Sorry, Server\'s under heavy traffic pressure, try testing another time please';

		$errors[24]['title'] = 'INVALID_MESSAGE_ID';
		$errors[24]['desc'] = 'entered message-id seems to be invalid, are you sure You entered the right thing?';

		$errors[25]['title'] = 'INVALID_NAMES_OF_SERVICES';
		$errors[25]['desc'] = 'Names of services included not valid . (Check the method name given to the first edition of this guide )';

		$errors[27]['title'] = 'INVALID_NAMES_OF_SERVICES';
		$errors[27]['desc'] = 'The company of first mobile dialer on the inactive list. (SMS advertising is not possible for this number).';

		$errors[102]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_SIZE_NOT_EQUAL_MESSAGE_CLASS_ARRAY';
		$errors[102]['desc'] = 'this happens when you try to define MClasses for your messages. in this case you must define one recipient number for each MClass';

		$errors[103]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_SIZE_NOT_EQUAL_SENDER_NUMBER_ARRAY';
		$errors[103]['desc'] = 'This error happens when you have more than one sender-number for message. when you have more than one sender number, for each sender-number you must define a recipient number...';

		$errors[104]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_SIZE_NOT_EQUAL_MESSAGE_ARRAY';
		$errors[104]['desc'] = 'this happens when you try to define UDHs for your messages. in this case you must define one recipient number for each udh';

		$errors[106]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_IS_NULL';
		$errors[106]['desc'] = 'array of recipient numbers must have at least one member';

		$errors[107]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_TOO_LONG';
		$errors[107]['desc'] = 'the maximum number of recipients per message is 90';

		$errors[108]['title'] = 'WEB_SENDER_NUMBER_ARRAY_IS_NULL';
		$errors[108]['desc'] = 'array of sender numbers must have at least one member';

		$errors[109]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_SIZE_NOT_EQUAL_ENCODING_ARRAY';
		$errors[109]['desc'] = 'this happens when you try to define encodings for your messages. in this case you must define one recipient number for each Encoding';

		$errors[110]['title'] = 'WEB_RECIPIENT_NUMBER_ARRAY_SIZE_NOT_EQUAL_CHECKING_MESSAGE_IDS__ARRAY';
		$errors[110]['desc'] = 'this happens when you try to define checking-message-ids for your messages. in this case you must define one recipient number for each checking-message-id';

		$errors[-1]['title'] = 'NOT_AVAILABLE';
		$errors[-1]['desc'] = 'The target of report is not available(e.g. no message is associated with entered IDs)';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo]['title'].' => '.$errors[$errNo]['desc'] : 'Unknown error : '.$errNo;
	}


	protected function checkNum($phone){
		if(strpos($phone, '+98') === false){
			$this->errors[] = 'The phone number is not a valid Iranian phone number';
			return false;
		}
		$americanPhone = str_replace('+98', '0', $phone);
		return $americanPhone;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("ENTERED_DATE",'');

		$apiAnswer->answer_body = JRequest::getString("text",'');

		$sender = JRequest::getString("from",'');
		$msisdn = JRequest::getString("to",'');

		if(!empty($sender))	$apiAnswer->answer_from = '+'.$sender;
		if(!empty($msisdn))	$apiAnswer->answer_to = '+'.$msisdn;

		return $apiAnswer;
	}
}
