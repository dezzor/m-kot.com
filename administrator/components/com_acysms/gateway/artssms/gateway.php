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
class ACYSMSGateway_artssms_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "artssms.net";
	public $port = 80;

	public $name = 'ArtsSMS';


	public function openSend($message,$phone){

		$encodeHelper = ACYSMS::get('helper.encoding');

		$params['numbers'] = $this->checkNum($phone);
		$params['username'] = $encodeHelper->change($this->username,'UTF-8','ISO-8859-1');
		$params['password'] = $encodeHelper->change($this->password,'UTF-8','ISO-8859-1');
		$params['message'] = $encodeHelper->change($message,'UTF-8','ISO-8859-1');
		$params['unicode'] = 'N';
		$params['return'] = 'json';

		if(!empty($this->senderid))	$params['sender'] = $this->senderid;

		if($this->unicodeChar($message)){
			$arr = unpack('H*hex', iconv('UTF-8', 'UCS-2BE', $message));
			$message = strtoupper($arr['hex']);
			$params['unicode'] = 'U';
		}


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /api/sendsms.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.artssms.net\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;
		$result = $this->sendRequest($fsockParameter);
		if(!$result && strpos(implode(',', $this->errors),'Connection timed out') !== false && $this->port != '80'){
			$this->errors[] = 'It seems that the port you choose is blocked on you server. You should try to select the port 80';
		}
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_senderid"><?php echo JText::_('SMS_SENDER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderid]" id="senderprofile_senderid" class="inputbox" maxlength="11" style="width:200px;" value="<?php echo htmlspecialchars(@$this->senderid,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_username"><?php echo JText::_('SMS_USERNAME'); ?></label>
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
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox"  type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
		</table>
		<?php
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
		$res = json_decode($res);

		if($res->Code == 100)
			return true;
		else
		{
			$this->errors[] = $this->getErrors($res->Code);
			return false;
		}
	}

	private function displayBalance(){
		$app = JFactory::getApplication();

		$fsockParameter = "GET /api/getbalance.php?username=".$this->username."&password=".$this->password."&return=json HTTP/1.1\r\n";
		$fsockParameter.= "Host: ".$this->domain." \r\n";
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

		$res = json_decode($res);
		if($res->Code == 117 || $res->Code == 104) // Credits or 0 credits status code
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res->currentuserpoints), 'message');
		else
			$app->enqueueMessage($this->getErrors($res->Code), 'error');
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors[100] = "تم استلام الارقام بنجاح";
		$errors[101] = "البيانات ناقصة";
		$errors[102] = "اسم المستخدم غير صحيح";
		$errors[103] = "كلمة المرور غير صحيحة";
		$errors[104] = "لا يوجد رصيد فى الحساب";
		$errors[105] = "الرصيد لا يكفى";
		$errors[106] = "اسم المرسل  غير متاح";
		$errors[107] = "اسم المرسل محجوب";
		$errors[108] = "لا يوجد ارقام صالحة للارسال";
		$errors[109] = "لا يمكن الارسال لاكثر من 5 مقاطع";
		$errors[110] = "خطا فى الارسال من فضلك حاول مرة اخرى";
		$errors[111] = "الارسال مغلق";
		$errors[112] = "الرسالة تحتوى على كلمة محظورة";
		$errors[113] = "الحساب غير مفعل";
		$errors[114] = "الحساب موقوف";
		$errors[115] = "غير مفعل جوال";
		$errors[116] = "غير مفعل بريد الكترون";
		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}
}
