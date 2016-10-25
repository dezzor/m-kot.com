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
class ACYSMSGateway_mitake_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = 'smexpress.mitake.com.tw';
	public $port = 9600;

	public $name = 'Mitake';


	public function openSend($message,$phone){

		$params = array();
		$config = ACYSMS::config();

		$params['dstaddr'] = $this->checkNum($phone);
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['smbody'] = iconv("UTF-8","BIG5", $message);


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /SmSendGet.asp?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: smexpress.mitake.com.tw\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$result = $this->sendRequest($fsockParameter);
		return $result;
	}

	public function displayConfig(){
		?>
		<table>
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

		if(preg_match('#statuscode=(.)#i', $result, $explodedResults)){
			if($explodedResults[1] != 1){
				$this->errors[] = $this->getErrors($explodedResults[1]);
				return false;
			}
			else $res = trim(substr($result,strpos($result,"\r\n\r\n")));
		}
		else{
			$this->errors[] = 'Unknown error => '.$result;
			return false;
		}



		if(preg_match('#msgid=([0-9]*)#i', $res, $explodedResults)){
			$this->smsid = $explodedResults[1];
		}
		if(preg_match('#statuscode=([0-9]*)#i', $res, $explodedResults)){
			if($explodedResults[1] == 1) return true;
		}
	}

	protected function getErrors($errNo){
		$errors = array();

		$errors['a'] = '簡訊發送功能暫時停止服務，請稍候再試';
		$errors['b'] = '簡訊發送功能暫時停止服務，請稍候再試';
		$errors['c'] = '請輸入帳號';
		$errors['d'] = '請輸入密碼';
		$errors['e'] = '帳號、密碼錯誤';
		$errors['f'] = '帳號已過期';
		$errors['h'] = '帳號已被停用';
		$errors['k'] = '無效的連線位址';
		$errors['m'] = '必須變更密碼，在變更密碼前，無法使用簡訊發送服務';
		$errors['n'] = '密碼已逾期，在變更密碼前，將無法使用簡訊發送服務';
		$errors['p'] = '沒有權限使用外部Http程式';
		$errors['q'] = '系統暫停服務，請稍後再試';
		$errors['r'] = '帳務處理失敗，無法發送簡訊';
		$errors['s'] = '帳務處理失敗，無法發送簡訊';
		$errors['t'] = '簡訊已過期';
		$errors['u'] = '簡訊內容不得為空白';
		$errors['v'] = '無效的手機號碼';
		$errors['0'] = '預約傳送中';
		$errors['1'] = '已送達業者';
		$errors['2'] = '已送達業者';
		$errors['3'] = '已送達業者';
		$errors['4'] = '已送達手機';
		$errors['5'] = '內容有錯誤';
		$errors['6'] = '門號有錯誤';
		$errors['7'] = '簡訊已停用';
		$errors['8'] = '逾時無送達';
		$errors['9'] = '預約已取消';

		return  isset($errors[$errNo]) ? 'Error '.$errNo.': '.$errors[$errNo] : 'Unknown error : '.$errNo;
	}
}
