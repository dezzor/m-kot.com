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

class ACYSMSGateway_mobily_gateway extends ACYSMSGateway_default_gateway{

	public $password;
	public $phone;
	public $sender;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $domain = "www.mobily.ws";
	public $port = 80;


	public $name = 'Mobily';

	public function openSend($message,$phone){
		$params = array();
		$params['numbers'] = $this->checkNum($phone);
		$params['sender'] = $this->sender;
		$params['msg'] = $this->unicode($message);
		$params['applicationType'] = 24;

		$stringToPost = "mobile=".urlencode($this->phone)."&password=".urlencode($this->password);
		foreach($params as $oneParam => $value){
			if($oneParam != 'msg') $value = urlencode($value);
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$fsockParameter = "POST /api/msgSend.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.mobily.ws\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_sender"><?php echo JText::_('SMS_SENDER'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][sender]" id="senderprofile_sender" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->sender,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_phone"><?php echo JText::_('SMS_PHONE'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][phone]" id="senderprofile_phone" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->phone,ENT_COMPAT, 'UTF-8');?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD')?></label>
				</td>
				<td>
					<input type="password" name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password,ENT_COMPAT, 'UTF-8');?>" />
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

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	public function beforeSaveConfig(&$senderprofile){
		$senderprofile->senderprofile_params['phone'] = $this->checkNum($senderprofile->senderprofile_params['phone']);
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));


		if($res == '1') return true;

		$errors = array();
		$errors['-2'] = 'Not connect to server';
		$errors['-1'] = 'Not connect to the database';
		$errors['2'] = 'Balance = 0';
		$errors['3'] = 'Balance is not enough';
		$errors['4'] = 'Mobile number is not available';
		$errors['5'] = 'wrong password';
		$errors['6'] = 'Web Page is not effective "Not Active", try posting again';
		$errors['13'] = 'The name of the sender is not acceptable.';
		$errors['14'] = 'The name of the sender used in sending process is not defined.';
		$errors['15'] = 'recipient value is incorrect or empty.';
		$errors['16'] = 'The name of the sender is empty.';
		$errors['17'] = 'The text of the message is not encrypted properly.';

		$this->errors[] = isset($errors[$res]) ? $errors[$res] : 'Unknown error : '.$res;
		return false;
	}

	private function displayBalance(){
		$app = JFactory::getApplication();

		$stringToPost = "mobile=".urlencode($this->phone)."&password=".urlencode($this->password);
		$fsockParameter = "POST /api/balance.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: www.mobily.ws\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter.= "Content-length: ".strlen($stringToPost)."\r\n\r\n";
		$fsockParameter.= $stringToPost;

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

		if(strpos($res,'/')){
			$split = explode('/',$res);
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$split[1]), 'message');
		}else{
			$errors = array();
			$errors['1'] = 'Your mobile number is not available';
			$errors['2'] = JText::_('SMS_PASSWORD_UNVALID');

			if(isset($errors[$res])){
				$app->enqueueMessage($errors[$res], 'error');
			}else{
				$app->enqueueMessage('Error unknown : '.$res, 'error');
			}

		}

	}

	private function unicode($message)
	{

		$chrArray[63] = "0";
		$unicodeArray[63] = "0030";
		$chrArray[64] = "1";
		$unicodeArray[64] = "0031";
		$chrArray[65] = "2";
		$unicodeArray[65] = "0032";
		$chrArray[66] = "3";
		$unicodeArray[66] = "0033";
		$chrArray[67] = "4";
		$unicodeArray[67] = "0034";
		$chrArray[68] = "5";
		$unicodeArray[68] = "0035";
		$chrArray[69] = "6";
		$unicodeArray[69] = "0036";
		$chrArray[70] = "7";
		$unicodeArray[70] = "0037";
		$chrArray[71] = "8";
		$unicodeArray[71] = "0038";
		$chrArray[72] = "9";
		$unicodeArray[72] = "0039";
		$chrArray[0] = "ØŒ";
		$unicodeArray[0] = "060C";
		$chrArray[1] = "Ø›";
		$unicodeArray[1] = "061B";
		$chrArray[2] = "ØŸ";
		$unicodeArray[2] = "061F";
		$chrArray[3] = "Ø¡";
		$unicodeArray[3] = "0621";
		$chrArray[4] = "Ø¢";
		$unicodeArray[4] = "0622";
		$chrArray[5] = "Ø£";
		$unicodeArray[5] = "0623";
		$chrArray[6] = "Ø¤";
		$unicodeArray[6] = "0624";
		$chrArray[7] = "Ø¥";
		$unicodeArray[7] = "0625";
		$chrArray[8] = "Ø¦";
		$unicodeArray[8] = "0626";
		$chrArray[9] = "Ø§";
		$unicodeArray[9] = "0627";
		$chrArray[10] = "Ø¨";
		$unicodeArray[10] = "0628";
		$chrArray[11] = "Ø©";
		$unicodeArray[11] = "0629";
		$chrArray[12] = "Øª";
		$unicodeArray[12] = "062A";
		$chrArray[13] = "Ø«";
		$unicodeArray[13] = "062B";
		$chrArray[14] = "Ø¬";
		$unicodeArray[14] = "062C";
		$chrArray[15] = "Ø­";
		$unicodeArray[15] = "062D";
		$chrArray[16] = "Ø®";
		$unicodeArray[16] = "062E";
		$chrArray[17] = "Ø¯";
		$unicodeArray[17] = "062F";
		$chrArray[18] = "Ø°";
		$unicodeArray[18] = "0630";
		$chrArray[19] = "Ø±";
		$unicodeArray[19] = "0631";
		$chrArray[20] = "Ø²";
		$unicodeArray[20] = "0632";
		$chrArray[21] = "Ø³";
		$unicodeArray[21] = "0633";
		$chrArray[22] = "Ø´";
		$unicodeArray[22] = "0634";
		$chrArray[23] = "Øµ";
		$unicodeArray[23] = "0635";
		$chrArray[24] = "Ø¶";
		$unicodeArray[24] = "0636";
		$chrArray[25] = "Ø·";
		$unicodeArray[25] = "0637";
		$chrArray[26] = "Ø¸";
		$unicodeArray[26] = "0638";
		$chrArray[27] = "Ø¹";
		$unicodeArray[27] = "0639";
		$chrArray[28] = "Øº";
		$unicodeArray[28] = "063A";
		$chrArray[29] = "Ù�";
		$unicodeArray[29] = "0641";
		$chrArray[30] = "Ù‚";
		$unicodeArray[30] = "0642";
		$chrArray[31] = "Ùƒ";
		$unicodeArray[31] = "0643";
		$chrArray[32] = "Ù„";
		$unicodeArray[32] = "0644";
		$chrArray[33] = "Ù…";
		$unicodeArray[33] = "0645";
		$chrArray[34] = "Ù†";
		$unicodeArray[34] = "0646";
		$chrArray[35] = "Ù‡";
		$unicodeArray[35] = "0647";
		$chrArray[36] = "Ùˆ";
		$unicodeArray[36] = "0648";
		$chrArray[37] = "Ù‰";
		$unicodeArray[37] = "0649";
		$chrArray[38] = "ÙŠ";
		$unicodeArray[38] = "064A";
		$chrArray[39] = "Ù€";
		$unicodeArray[39] = "0640";
		$chrArray[40] = "Ù‹";
		$unicodeArray[40] = "064B";
		$chrArray[41] = "ÙŒ";
		$unicodeArray[41] = "064C";
		$chrArray[42] = "Ù�";
		$unicodeArray[42] = "064D";
		$chrArray[43] = "ÙŽ";
		$unicodeArray[43] = "064E";
		$chrArray[44] = "Ù�";
		$unicodeArray[44] = "064F";
		$chrArray[45] = "Ù�";
		$unicodeArray[45] = "0650";
		$chrArray[46] = "Ù‘";
		$unicodeArray[46] = "0651";
		$chrArray[47] = "Ù’";
		$unicodeArray[47] = "0652";
		$chrArray[48] = "!";
		$unicodeArray[48] = "0021";
		$chrArray[49]='"';
		$unicodeArray[49] = "0022";
		$chrArray[50] = "#";
		$unicodeArray[50] = "0023";
		$chrArray[51] = "$";
		$unicodeArray[51] = "0024";
		$chrArray[52] = "%";
		$unicodeArray[52] = "0025";
		$chrArray[53] = "&";
		$unicodeArray[53] = "0026";
		$chrArray[54] = "'";
		$unicodeArray[54] = "0027";
		$chrArray[55] = "(";
		$unicodeArray[55] = "0028";
		$chrArray[56] = ")";
		$unicodeArray[56] = "0029";
		$chrArray[57] = "*";
		$unicodeArray[57] = "002A";
		$chrArray[58] = "+";
		$unicodeArray[58] = "002B";
		$chrArray[59] = ",";
		$unicodeArray[59] = "002C";
		$chrArray[60] = "-";
		$unicodeArray[60] = "002D";
		$chrArray[61] = ".";
		$unicodeArray[61] = "002E";
		$chrArray[62] = "/";
		$unicodeArray[62] = "002F";
		$chrArray[73] = ":";
		$unicodeArray[73] = "003A";
		$chrArray[74] = ";";
		$unicodeArray[74] = "003B";
		$chrArray[75] = "<";
		$unicodeArray[75] = "003C";
		$chrArray[76] = "=";
		$unicodeArray[76] = "003D";
		$chrArray[77] = ">";
		$unicodeArray[77] = "003E";
		$chrArray[78] = "?";
		$unicodeArray[78] = "003F";
		$chrArray[79] = "@";
		$unicodeArray[79] = "0040";
		$chrArray[80] = "A";
		$unicodeArray[80] = "0041";
		$chrArray[81] = "B";
		$unicodeArray[81] = "0042";
		$chrArray[82] = "C";
		$unicodeArray[82] = "0043";
		$chrArray[83] = "D";
		$unicodeArray[83] = "0044";
		$chrArray[84] = "E";
		$unicodeArray[84] = "0045";
		$chrArray[85] = "F";
		$unicodeArray[85] = "0046";
		$chrArray[86] = "G";
		$unicodeArray[86] = "0047";
		$chrArray[87] = "H";
		$unicodeArray[87] = "0048";
		$chrArray[88] = "I";
		$unicodeArray[88] = "0049";
		$chrArray[89] = "J";
		$unicodeArray[89] = "004A";
		$chrArray[90] = "K";
		$unicodeArray[90] = "004B";
		$chrArray[91] = "L";
		$unicodeArray[91] = "004C";
		$chrArray[92] = "M";
		$unicodeArray[92] = "004D";
		$chrArray[93] = "N";
		$unicodeArray[93] = "004E";
		$chrArray[94] = "O";
		$unicodeArray[94] = "004F";
		$chrArray[95] = "P";
		$unicodeArray[95] = "0050";
		$chrArray[96] = "Q";
		$unicodeArray[96] = "0051";
		$chrArray[97] = "R";
		$unicodeArray[97] = "0052";
		$chrArray[98] = "S";
		$unicodeArray[98] = "0053";
		$chrArray[99] = "T";
		$unicodeArray[99] = "0054";
		$chrArray[100] = "U";
		$unicodeArray[100] = "0055";
		$chrArray[101] = "V";
		$unicodeArray[101] = "0056";
		$chrArray[102] = "W";
		$unicodeArray[102] = "0057";
		$chrArray[103] = "X";
		$unicodeArray[103] = "0058";
		$chrArray[104] = "Y";
		$unicodeArray[104] = "0059";
		$chrArray[105] = "Z";
		$unicodeArray[105] = "005A";
		$chrArray[106] = "[";
		$unicodeArray[106] = "005B";
		$char="\ ";
		$chrArray[107]=trim($char);
		$unicodeArray[107] = "005C";
		$chrArray[108] = "]";
		$unicodeArray[108] = "005D";
		$chrArray[109] = "^";
		$unicodeArray[109] = "005E";
		$chrArray[110] = "_";
		$unicodeArray[110] = "005F";
		$chrArray[111] = "`";
		$unicodeArray[111] = "0060";
		$chrArray[112] = "a";
		$unicodeArray[112] = "0061";
		$chrArray[113] = "b";
		$unicodeArray[113] = "0062";
		$chrArray[114] = "c";
		$unicodeArray[114] = "0063";
		$chrArray[115] = "d";
		$unicodeArray[115] = "0064";
		$chrArray[116] = "e";
		$unicodeArray[116] = "0065";
		$chrArray[117] = "f";
		$unicodeArray[117] = "0066";
		$chrArray[118] = "g";
		$unicodeArray[118] = "0067";
		$chrArray[119] = "h";
		$unicodeArray[119] = "0068";
		$chrArray[120] = "i";
		$unicodeArray[120] = "0069";
		$chrArray[121] = "j";
		$unicodeArray[121] = "006A";
		$chrArray[122] = "k";
		$unicodeArray[122] = "006B";
		$chrArray[123] = "l";
		$unicodeArray[123] = "006C";
		$chrArray[124] = "m";
		$unicodeArray[124] = "006D";
		$chrArray[125] = "n";
		$unicodeArray[125] = "006E";
		$chrArray[126] = "o";
		$unicodeArray[126] = "006F";
		$chrArray[127] = "p";
		$unicodeArray[127] = "0070";
		$chrArray[128] = "q";
		$unicodeArray[128] = "0071";
		$chrArray[129] = "r";
		$unicodeArray[129] = "0072";
		$chrArray[130] = "s";
		$unicodeArray[130] = "0073";
		$chrArray[131] = "t";
		$unicodeArray[131] = "0074";
		$chrArray[132] = "u";
		$unicodeArray[132] = "0075";
		$chrArray[133] = "v";
		$unicodeArray[133] = "0076";
		$chrArray[134] = "w";
		$unicodeArray[134] = "0077";
		$chrArray[135] = "x";
		$unicodeArray[135] = "0078";
		$chrArray[136] = "y";
		$unicodeArray[136] = "0079";
		$chrArray[137] = "z";
		$unicodeArray[137] = "007A";
		$chrArray[138] = "{";
		$unicodeArray[138] = "007B";
		$chrArray[139] = "|";
		$unicodeArray[139] = "007C";
		$chrArray[140] = "}";
		$unicodeArray[140] = "007D";
		$chrArray[141] = "~";
		$unicodeArray[141] = "007E";
		$chrArray[142] = "Â©";
		$unicodeArray[142] = "00A9";
		$chrArray[143] = "Â®";
		$unicodeArray[143] = "00AE";
		$chrArray[144] = "Ã·";
		$unicodeArray[144] = "00F7";
		$chrArray[145] = "Ã—";
		$unicodeArray[145] = "00F7";
		$chrArray[146] = "Â§";
		$unicodeArray[146] = "00A7";
		$chrArray[147] = " ";
		$unicodeArray[147] = "0020";
		$chrArray[148] = "\n";
		$unicodeArray[148] = "000D";
		$chrArray[149] = "\r";
		$unicodeArray[149] = "000A";


		$newMessage = array();
		$found = true;
		while($found){
			$found = false;
			foreach($chrArray as $key => $char){
				$pos = strpos($message,$char);
				if($pos !== false){
					$newMessage[$pos] = $unicodeArray[$key];
					$message[$pos] = 'Ã©';
					$found = true;
				}
			}
		}

		ksort($newMessage);
		return implode('',$newMessage);
	}

}
