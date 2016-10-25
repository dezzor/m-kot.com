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
class ACYSMSGateway_mihansmscenter_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $from;
	public $waittosend= 0;
	public $connectionInformations;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $name = 'Mihansmscenter';

	public $domain = 'mihansmscenter.com';


	public function openSend($message,$phone){

		$params = array();

		$params['to'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['from'] = $this->from;
		$params['message'] = $message;

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /webservice/send.php?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: mihansmscenter.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$result = $this->sendRequest($fsockParameter);
		return $result;
	}

	public function displayConfig(){
		$config = ACYSMS::config();
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_from"><?php echo JText::_('SMS_FROM'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][from]" id="senderprofile_from" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->from,ENT_COMPAT, 'UTF-8');?>" />
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
		$extraInformations = '';

		if(preg_match('#Error ([0-9])#i', $res, $explodedResults)){
			$this->errors[] = $this->getErrors($explodedResults[1]);
			return false;
		}else if(preg_match('#([0-9])#i', $res, $explodedResults2)){
			$this->smsid = $explodedResults2[1];
			return true;
		}else{
			$this->errors[] = $this->getErrors($res);
			return false;
		}
	}

	protected function getErrors($errNo){
		$errors = array();
		$errors['0'] = 'بدون خطا';
		$errors['1'] = 'نام کاربری و رمز عبور نامعتبر است';
		$errors['2'] = 'شماره فرستنده نا معتبر است';
		$errors['3'] = 'شماره گیرنده نامعتبر است';
		$errors['4'] = 'اعتبار حساب کافی نمی باشد';
		$errors['5'] = 'خطا در ارتباط با سرور';
		$errors['6'] = 'پیام نامعتبر است';
		$errors['7'] = 'متن پیام بیش از حد طولانی است';
		$errors['8'] = 'خطا در برقراری ارتباط با سوئیچ مخابرات';
		$errors['9'] = 'پیام دریافتی معتبر نمی باشد';
		$errors['10'] = 'شناسه پیام نامعتبر است.';
		$errors['11'] = 'تعداد پارامترها صحیح نمی باشد.';
		$errors['12'] = 'مقدار UDH نامعتبر است.';
		$errors['13'] = 'شماره گیرنده امکان دریافت پیامک تبلیغاتی ندارد.';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}
}
