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
class ACYSMSGateway_spothit_gateway extends ACYSMSGateway_default_gateway{

	public $username;
	public $password;
	public $senderid;
	public $waittosend= 0;

	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = 'spot-hit.fr';
	public $port = 80;

	public $name = 'Spot-Hit';


	public function openSend($message,$phone){

		$params = array();

		$params['destinataires'] = $this->checkNum($phone);
		$params['identifiant'] = $this->username;
		$params['motdepasse'] = $this->password;
		$params['smslong'] = 1;
		$params['message'] = $message;
		$params['expediteur'] = $this->senderid;
		$params['type'] = $this->route;


		$stringToPost = '';
		foreach($params as $oneParam => $value){
			$value = urlencode(($value));
			$stringToPost .='&'.$oneParam.'='.$value;
		}
		$stringToPost = ltrim($stringToPost,'&');

		$fsockParameter = "POST /manager/inc/actions/ajout_message.php HTTP/1.1\r\n";
		$fsockParameter.= "Host: spot-hit.fr\r\n";
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

		$routeData = array();
		$routeData[] = JHTML::_('select.option', 'premium', 'Premium', 'value', 'text');
		$routeData[] = JHTML::_('select.option', 'lowcost', 'Low Cost', 'value', 'text');

		$routeOptions =  JHTML::_('select.genericlist', $routeData, "data[senderprofile][senderprofile_params][route]" , 'size="1" class="chzn-done" style="width:auto;"', 'value', 'text', @$this->route);
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

	public function afterSaveConfig($senderprofile){
		if(in_array(JRequest::getCmd('task'),array('save','apply'))) $this->displayBalance();
	}

	protected function interpretSendResult($result){

		if(!strpos($result,'200 OK')){
			if(strpos(strtolower($result),'302 found')){
				$this->errors[] = 'Error 302 Found => Your access informations should be invalids';
				return false;
			}
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		else $res = trim(substr($result,strpos($result,"\r\n\r\n")));



		$res = json_decode($res);
		if(empty($res->id) || empty($res->resultat)){
			$this->errors[] = $this->getErrors($res->erreurs);
			return false;
		}
		if(!empty($res->id)) $this->smsid = $res->id;

		if(!empty($res->resultat) && $res->resultat == 1) return true;

		$this->errors[] = $this->getErrors($res);
		return false;
	}

	private function displayBalance(){
		$app = JFactory::getApplication();
		$fsockParameter = "GET /manager/inc/actions/credits.php?identifiant=".$this->username."&motdepasse=".$this->password." HTTP/1.1\r\n";
		$fsockParameter.= "Host: spot-hit.fr \r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			$app->enqueueMessage(implode('<br />',$this->errors), 'error');
			return false;
		}
		if(!strpos($result,'200 OK')){
			if(strpos(strtolower($result),'302 found')){
				$this->errors[] = 'Error 302 Found => Your access informations should be invalids';
				return false;
			}
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		$res = trim(substr($result,strpos($result,"\r\n\r\n")));


		$res = json_decode($res);

		if(isset($res->premium) && isset($res->lowcost)){
			$app->enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT',$res->lowcost.' Low Cost, '.$res->premium.' Premium'), 'message');
			$urlNotificationStatus = $this->_changeNotificationURL();
			if($urlNotificationStatus) $app->enqueueMessage(JText::sprintf('SMS_NOTIFICATION_URLS_SUCCESSFULLY_SET'), 'message');
			else $app->enqueueMessage(JText::sprintf('SMS_ERROR_WHILE_TRYING_SET_NOTIFICATION_URL').' => '.implode(',',$this->errors), 'warning');
		}else{
			$app->enqueueMessage($this->getErrors($res->erreurs),'error');
			return false;
		}

	}

	private function _changeNotificationURL(){
		$this->errors[] = JText::_('SMS_LOCALHOST_PROBLEM');
		if(strpos(ACYSMS_LIVE,'localhost')) return false;

		$config = ACYSMS::config();

		$deliveryReportURL = urlencode(ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=deliveryreport&gateway=spothit&pass='.$config->get('pass'));
		$answerURL = urlencode(ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=answer&gateway=spothit&pass='.$config->get('pass'));

		$fsockParameter = "GET /manager/inc/actions/modifier_urls.php?identifiant=".$this->username."&motdepasse=".$this->password."&accuses=".$deliveryReportURL."&reponses=".$answerURL." HTTP/1.1\r\n";
		$fsockParameter.= "Host: spot-hit.fr \r\n";
		$fsockParameter.= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			$app->enqueueMessage(implode('<br />',$this->errors), 'error');
			return false;
		}
		if(!strpos($result,'200 OK')){
			if(strpos(strtolower($result),'302 found')){
				$this->errors[] = 'Error 302 Found => Your access informations should be invalids';
				return false;
			}
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		$res = trim(substr($result,strpos($result,"\r\n\r\n")));

		$res = json_decode($res);
		if($res->resultat == 1) return true;

		$this->errors[] = $this->getErrors($res);
		return false;
	}

	protected function getErrors($errNo){
		$errArray =  explode(',', $errNo);

		$errors = array();
		$errors['1'] = 'Type de SMS non spécifié ou incorrect';
		$errors['2'] = 'Le message est vide';
		$errors['3'] = 'Le message contient plus de 160 caractères';
		$errors['4'] = 'Aucun destinataire valide n\'est renseigné';
		$errors['5'] = 'Numéro interdit: seuls les envois en France Métropolitaine sont autorisés pour les SMS Low Cost';
		$errors['6'] = 'Numéro de destinataire invalide';
		$errors['7'] = 'Votre compte n\'a pas de formule définie';
		$errors['8'] = 'L\'expéditeur ne peut contenir que 11 caractères';
		$errors['9'] = 'Le système a rencontré une erreur, merci de nous contacter';
		$errors['10'] = 'Vous ne disposez pas d\'assez de SMS pour effectuer cet envoi';
		$errors['11'] = 'L\'envoi des message est désactivé pour la démonstration';
		$errors['12'] = 'Votre compte a été suspendu. Contactez-nous sur info@spot-hit.fr pour plus d\'informations';

		$return = '';
		foreach($errArray as $oneError){
			$return .= isset($errors[$oneError]) ? 'Error '.$oneError.': '.$errors[$oneError] : 'Unknown error : '.$oneError;
			$return .= '<br />';
		}
		return $return;
	}

	public function deliveryReport(){

		$status = array();
		$apiAnswer = new stdClass();
		$apiAnswer->statsdetails_error = array();



		$status[1] = array(5 ,"Envoyé et bien reçu");
		$status[2] = array(-1 ,"Envoyé et non reçu");
		$status[3] = array(-1 ,"En cours");
		$status[4] = array(-1 ,"Echec");

		$completed_time = JRequest::getVar("date_update",'');
		if(empty($completed_time)){
			$apiAnswer->statsdetails_error[] =  'Unknow completed_time';
			$apiAnswer->statsdetails_received_date = time();
		}else $apiAnswer->statsdetails_received_date = $completed_time;

		$messageStatus = JRequest::getVar("statut",'');
		if(empty($messageStatus)) $apiAnswer->statsdetails_error[] = 'Empty status received';

		$smsId = JRequest::getVar("id_message",'');
		if(empty($smsId)) $apiAnswer->statsdetails_error[] = 'Can t find the message_id';

		if(!isset($status[$messageStatus])){
			$apiAnswer->statsdetails_error[] = 'Unknow status : '.$messageStatus;
			$apiAnswer->statsdetails_status = -99;
		}
		else{
			$apiAnswer->statsdetails_status = $status[$messageStatus][0];
			$apiAnswer->statsdetails_error[] = $status[$messageStatus][1];
		}

		$apiAnswer->statsdetails_sms_id = $smsId;

		return $apiAnswer;
	}

	public function answer(){

		$phoneHelper = ACYSMS::get('helper.phone');

		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = JRequest::getString("date",'');

		$apiAnswer->answer_body = JRequest::getString("message",'');

		$sender = JRequest::getString("numero",'');

		if(!empty($sender))	$apiAnswer->answer_from = $sender;

		$apiAnswer->answer_sms_id = JRequest::getString("source",'');

		return $apiAnswer;
	}
}
