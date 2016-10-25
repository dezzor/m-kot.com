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
class ACYSMSGateway_wire2air_gateway extends ACYSMSGateway_default_gateway{

	public $userid;
	public $password;
	public $vasid;
	public $from;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = false;
	public $answerManagement = false;

	public $port = 80;
	public $domain = 'smsapi.Wire2Air.com';

	public $name = 'Wire2Air';



	public function openSend($message,$phone){

		$params = array();
		$params['TO'] =  $this->checkNum($phone);
		$params['USERID'] =  $this->userid;
		$params['PASSWORD'] =  $this->password;
		$params['VASID'] =  $this->vasid;
		$params['FROM'] = $this->from;
		$params['TEXT'] = $message;
		$params['VERSION'] = '2.0';

		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "GET /smsadmin/submitsm.aspx?".$stringToPost." HTTP/1.1\r\n";
		$fsockParameter.= "Host: smsapi.Wire2Air.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";
		return $this->sendRequest($fsockParameter);
	}

	public function displayConfig(){
		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_user_id"><?php echo JText::_('SMS_USER_ID'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][userid]" id="senderprofile_userid" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->userid,ENT_COMPAT, 'UTF-8');?>" />
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
				<td>
					<label for="senderprofile_vasid"><?php echo 'VAS ID'; ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][vasid]" id="senderprofile_vasid" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->vasid,ENT_COMPAT, 'UTF-8');?>" />
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
		</table>

	<?php
	}

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	private function displayBalance(){

		$app = JFactory::getApplication();
		$fsockParameter = "GET /smsadmin/checksmscredits.aspx?USERID=".$this->userid."&PASSWORD=".$this->password."&VASID=".$this->vasid." HTTP/1.1\r\n";
		$fsockParameter.= "Host: smsapi.wire2air.com\r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
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

		$split = explode(':',$res);
		if(isset($split[2]) && !empty($split[2])){
			$this->smsid = $split[2];
		}
		if($split[0] == 'JOBID'){
			return true;
		}

		$this->errors[] = $res;
		return false;

	}
}
