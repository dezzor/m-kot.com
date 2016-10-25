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
class plgSystemAcySMSVerifNumber extends JPlugin
{
	var $option = '';
	var $view = '';
	var $phoneNumber = '';
	var $firstName = '';
	var $lastName = '';


	function plgSystemAcySMSVerifNumber(&$subject, $config){
		parent::__construct($subject, $config);
	}

	private function init() {
		if(defined('ACYSMS_COMPONENT'))
			return true;
		$acySmsHelper = rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper))	include_once $acySmsHelper;
		return defined('ACYSMS_COMPONENT');
	}

	private function verifyFileIncluded($view, $option) {
		$view_component = array();
		$view_component['com_virtuemart'] = 'cart';
		$view_component['com_users'] = 'registration';
		$view_component['com_user'] = 'register';
		$view_component['com_community'] = 'register';

		if(!isset($view_component[$option]) || $view_component[$option] != $view) return;

		if(!$this->init()) return;
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'component.css');
	}

	function onAfterRoute(){
		$this->phoneNumber = JRequest::getCmd('phonenumber','');
		$verificationCodeSubmited = JRequest::getCmd('verificationcodesubmited','');
		$task = JRequest::getCmd('task','');
		$option = JRequest::getCmd('option','');
		$view = JRequest::getCmd('view','');

		$this->verifyFileIncluded($view,$option);

		if(!empty($verificationCodeSubmited)){
			$this->init();

			$result = $this->_verifyCode($verificationCodeSubmited,true,true);
			$result = json_decode($result);
			if(!$result->verify) echo "<script>alert(".$result->errorMessage.");history.back();</script>";
		}
		else {
			if(($task == 'register_save' && $option == 'com_user')||($task == 'registration.register' && $option == 'com_users')){
				if($this->isIntegrationNeeded('joomlasub')){
					echo '<script>alert("Phonenumber confirmation didn t process");history.back();</script>';
					exit;
				}
			}
			if($task == 'confirm' && $option == 'com_virtuemart' && !isset($_POST['setpayment'])){
				if(!$this->_getPaymentMethodVM()) return;
				if($this->isIntegrationNeeded('virtuemart')){
					echo '<script>alert("Phonenumber confirmation didn t process");history.back();</script>';
					exit;
				}
			}
			if($task == 'registerProfile' && $option == 'com_community' && $task == 'registerUpdateProfile'){
				if($this->isIntegrationNeeded('jomsocial')){
					echo '<script>alert("Phonenumber confirmation didn t process");history.back();</script>';
					exit;
				}
			}
		}
	}

	function onAfterRender(){
		$option = JRequest::getCmd('option','');
		$view = JRequest::getCmd('view','');
		$task = JRequest::getCmd('task','');

		$verificationCode = JRequest::getCmd('verificationcode','');
		$sendCode = JRequest::getCmd('sendCode','0');
		$this->lastName = JRequest::getString('lastname','');
		$this->firstName = JRequest::getString('firstname','');

		if(!empty($verificationCode)){
			$this->init();
			$integration = JRequest::getCmd('integration','');
			$deleteCodeInDB = ($integration == 'hikashop') ? true : false;
			$this->_verifyCode($verificationCode,$deleteCodeInDB);
			return;
		}

		if($sendCode == 1){
			$this->_sendCode();
			return;
		}

		if(empty($option)||empty($view)) return;
		if($option == 'com_virtuemart' && $view == 'cart'){
			$this->_displayConfirmationAreaVM();
		}
		if($option == 'com_users' && $view == 'registration'){
			$this->_displayConfirmationAreaJoomlaSub();
		}
		if($option == 'com_user' && $view == 'register'){
			$this->_displayConfirmationAreaJoomlaSub15();
		}
		if($option == 'com_community' && $view == 'register' && $task == 'registerProfile'){
			$this->_displayConfirmationAreaJomsocial();
		}

	}

	public function onACYSMSgetVerificationCodeIntegrations(&$integrationVerificationCode) {
		$integrationVerificationCode['joomla_subscription'] = true;
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop')) $integrationVerificationCode['hikashop'] = true;
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_virtuemart')) $integrationVerificationCode['virtuemart'] = true;
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_community')) $integrationVerificationCode['jomsocial'] = true;
	}


	public function isIntegrationNeeded($integration) {
		if(!$this->init()) return false;

		$config = ACYSMS::config();
		switch($integration){
			case 'virtuemart':
				return $config->get('require_confirmation_virtuemart');
			case 'joomlasub':
				return $config->get('require_confirmation_joomla_subscription');
			case 'hikashop':
				return $config->get('require_confirmation_hikashop');
			case 'jomsocial':
				return $config->get('require_confirmation_jomsocial');
		}
	}

	private function _sendCode() {
		$std_result = new stdClass();
		$std_result->sendingResult = false;
		$std_result->display = '';

		if(empty($this->phoneNumber)) {
			$std_result->display = JText::_('SMS_NO_PHONE');
			echo json_encode($std_result);
			exit;
		}
		$phoneHelper = ACYSMS::get('helper.phone');
		$phoneNumber = $phoneHelper->getValidNum($this->phoneNumber); //false if not valid
		if($phoneNumber == false) {
			$std_result->display = $phoneHelper->error;
			echo json_encode($std_result);
			exit;
		}
		$this->phoneNumber = $phoneNumber; //if we succeeded getValidNum
		if(empty($this->firstName)) $this->firstName = " "; //if we have only one field who contains name

		$classUser = ACYSMS::get('class.user');
		$user = $classUser->getByPhone($phoneNumber);

		if(empty($user)) $this->_createAcySMSUser($this->phoneNumber,$this->lastName,$this->firstName);

		$std_result->sendingResult = $phoneHelper->sendVerificationCode($this->phoneNumber);
		if(!$std_result->sendingResult){
			$std_result->display = $phoneHelper->error.' '.JText::_('SMS_CONTACT_ADMIN');
			echo json_encode($std_result);
			exit;
		}

		$std_result->sendingResult = true;
		$std_result->display = '<div id="acysms_phoneverification">
					<span style="color:#1EA0FC">'.JText::_('SMS_VERIFICATION_CODE_ENTER').'</span><br />
					<input type="hidden" id="sms_sent_to" value="'.$this->phoneNumber.'">
					<label for="verification_code">'.JText::_('SMS_VERIFICATION_CODE').'</label>
					<input type="text" name="verification_code" id="verification_code">
					<div id="spinner_button">
						<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>
					</div>
			</div>
			<span id="validation_result"></span>';
		echo json_encode($std_result);
		exit;
	}

	private function _verifyCode($verificationCode,$deleteCodeInDB = false, $afterUserSubmited = false) {
		$std_result = new stdClass();
		$std_result->verify = false;
		$std_result->errorMessage = '';

		$phoneHelper = ACYSMS::get('helper.phone');
		$this->phoneNumber = $phoneHelper->getValidNum($this->phoneNumber);
		if($this->phoneNumber == false) {
			$std_result->errorMessage = $phoneHelper->error;
			$string_result = json_encode($std_result); //we encode in json to access the result in js later
			if($afterUserSubmited) return $string_result;
			echo $string_result;
			exit;
		}

		$result = $phoneHelper->verifyActivation($this->phoneNumber,$verificationCode,'activation_optin',$deleteCodeInDB);
		$std_result->verify = $result;
		$std_result->errorMessage = $phoneHelper->error;
		$string_result = json_encode($std_result); //we encode in json to access the result in js later
		if($afterUserSubmited) return $string_result;
		echo $string_result;
		exit;
	}

	private function _checkIfConfirmed() {
		$phoneHelper = ACYSMS::get('helper.phone');
		$userPhoneNumber = $phoneHelper->getValidNum($this->phoneNumber);
		if(empty($userPhoneNumber))	return false;
		$userClass = AcySMS::get('class.user');
		$user = $userClass->getByPhone($userPhoneNumber);
		if(empty($user)) return false;
		$result = unserialize($user->user_activationcode);
		if(isset($result['activation_optin']) && empty($result['activation_optin'])) return true;
		return false;
	}

	private function _createAcySMSUser($phoneNumber, $lastName = '', $firstName = '') {
		$userClass = ACYSMS::get('class.user');
		$user = new stdClass();
		$user->user_firstname = $firstName;
		$user->user_lastname = $lastName;
		$user->user_phone_number = $phoneNumber;
		$userClass->save($user);
	}

	private function _getUserInformationVM($information) {
		if(empty($_SESSION['__vm']['vmcart'])) {
			echo JText::_('SMS_CART_PROBLEM');
			return false;
		}
		$results = unserialize($_SESSION['__vm']['vmcart']);

		switch($information) {
			case 'phonenumber':
				if(!empty($results->BT['phone_2']))return $results->BT['phone_2'];
			case 'firstname':
				if(!empty($results->BT['first_name']))return $results->BT['first_name'];
			case 'lastname':
				if(!empty($results->BT['last_name']))return $results->BT['last_name'];
		}
		return false;
	}


	private function _displayConfirmationAreaVM() {
		if(!$this->isIntegrationNeeded('virtuemart')) return;
		if(!$this->_getPaymentMethodVM()) return;

		$phoneNumber = $this->_getUserInformationVM('phonenumber');
		if($phoneNumber == false) return; //if phonenumber not valid


		$this->phoneNumber = $phoneNumber;

		if($this->_checkIfConfirmed()) return;

		$this->firstName = $this->_getUserInformationVM('firstname');
		$this->lastName = $this->_getUserInformationVM('lastname');

		$newField = $this->displayPhoneField('virtuemart'); //we generate the html/js we need for phonenumber validation
		$this->_replaceConfirmButtonVM($newField);
	}

	private function _replaceConfirmButtonVM($newField) {
		$body = JResponse::getBody();
		$body = preg_replace('#<button.*id=\"checkoutFormSubmit\".*>.*<\/button>#',$newField,$body);
		JResponse::setBody($body);
	}

	private function _getPaymentMethodVM() {
		if(!empty($_SESSION['__vm']['vmcart']))
			$results = unserialize($_SESSION['__vm']['vmcart']);
		if(empty($results->order_language))
			$tableName = '#__virtuemart_paymentmethods';
		else
			$tableName = '#__virtuemart_paymentmethods_'.str_replace('-','_',$results->order_language);
		$db = JFactory::getDBO();
		$db->setQuery('SELECT payment_element FROM #__virtuemart_paymentmethods AS pm WHERE virtuemart_paymentmethod_id ='.intval($results->virtuemart_paymentmethod_id.' LIMIT 1'));
		$element = $db->loadResult();

		if(!empty($element) && $element == 'standard')
			return true;
		return false;
	}

	function displayPhoneField($integration, $extraInformations = null) {
		$ajaxURLForCodeRequest = '';
		$idElementCodeRequest = '';
		$phoneFieldToDisplay = '';
		$additionalTreatmentForCodeRequest = '';
		$actionToAddFormCodeRequest = '';
		$ajaxURLForSendCode = '';
		$additionalTreatmentForSendCode = '';
		$jtextInstruction = 'SMS_VERIFICATION_CODE_SELECT';

		if($integration == 'joomlasub') {
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber="+phonenumber';
			$idElementCodeRequest = 'member-registration';
			$additionalTreatmentForCodeRequest = 'if(document.getElementById("jform_profile_phone") == undefined) phonenumber = document.getElementById("sms_sent_to").value; else phonenumber = document.getElementById("jform_profile_phone").value;';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&phonenumber="+phonenumber';
			$additionalTreatmentForSendCode = '
					form = document.getElementById("member-registration");
					if(!document.formvalidator.isValid(form)) return;
					if(document.getElementById("jform_profile_phone") == undefined)
							phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
					else
							phonenumber = document.getElementById("jform_profile_phone").value;
					name = document.getElementById("jform_name").value;
			';

			$body = JResponse::getBody();
			if(!preg_match("#<input type=\"tel|text\".*id=\"jform_profile_phone\".*>#",$body)){
				$countryType = ACYSMS::get('type.country');
				$countryType = new ACYSMScountryType();
				$countryType->phonewidth = 20;
				$phoneFieldToDisplay = $countryType->displayPhone('','phonenumber_verification');
			}
		}
		else if($integration == 'virtuemart') {
			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber='.$this->phoneNumber.'"';
			$idElementCodeRequest = 'checkoutForm';
			$actionToAddFormCodeRequest = '"phonenumber='.$this->phoneNumber.'&verificationcodesubmited="+verificationCode';
			$ajaxURLForSendCode = '"?sendCode=1&lastname='.$this->lastName.'&firstname='.$this->firstName.'&phonenumber='.$this->phoneNumber.'"';
		}
		else if($integration == 'hikashop') {
			$ajaxURLForCodeRequest = '"?integration=hikashop&verificationcode="+verificationCode+"&phonenumber='.$this->phoneNumber.'"';
			$ajaxURLForSendCode = '"?sendCode=1&lastname='.$this->lastName.'&firstname='.$this->firstName.'&phonenumber='.$this->phoneNumber.'"';
		}
		else if($integration == 'joomlasub15') {
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '';
			$idElementCodeRequest = 'josForm';
			$phoneFieldToDisplay = '';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$additionalTreatmentForCodeRequest = 'phonenumber = document.getElementById("sms_sent_to").value;';
			$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&phonenumber="+phonenumber';
			$additionalTreatmentForSendCode =
				'form = document.getElementById("josForm");
				if(!document.formvalidator.isValid(form)) return;
				phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
				name = document.getElementById("name").value;';
				$countryType = ACYSMS::get('type.country');
				$countryType = new ACYSMScountryType();
				$countryType->phonewidth=20;
			$phoneFieldToDisplay = $countryType->displayPhone('','phonenumber_verification');
		}
		else if($integration == 'jomsocial'){
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber="+phonenumber';
			$idElementCodeRequest = 'jomsForm';
			$additionalTreatmentForCodeRequest = 'phonenumber = document.getElementById("field'.$extraInformations['fieldid'].'").value;';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$ajaxURLForSendCode = '"?sendCode=1&lastname='.$extraInformations['name'].'&phonenumber="+phonenumber';
			$additionalTreatmentForSendCode = 'phonenumber = document.getElementById("field'.$extraInformations['fieldid'].'").value;';
		}else{
			return;
		}

		$phoneField = '
		<script>
			codeRequest = function(){
				verificationCode = document.getElementById("verification_code").value;
				if(!verificationCode){ alert("'.JText::_('SMS_PLEASE_ENTER_CODE').'"); return;}
				document.getElementById("spinner_button").innerHTML = \'<span id=\"ajaxSpan\" class=\"onload\"></span>\';
				'.$additionalTreatmentForCodeRequest.'
				try{
					new Ajax('.$ajaxURLForCodeRequest.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify) {';
								if($integration == 'hikashop') {
									$phoneField .='document.getElementById("validation_result").innerHTML = \''.str_replace("'","\'",JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
									document.getElementById("validation_result").style.color="green";
									document.getElementById("acysms_phoneverification").style.display="none";
							}';
								}else{
									$phoneField .= 'signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
									document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
									document.getElementById("'.$idElementCodeRequest.'").submit();
							}';
								}
								$phoneField .='else {
													document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
													document.getElementById("validation_result").innerHTML = response.errorMessage;
													document.getElementById("validation_result").style.color="red";
												}
						}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForCodeRequest.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify) {';
								if($integration == 'hikashop') {
									$phoneField .='document.getElementById("validation_result").innerHTML = \''.str_replace("'","\'",JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
									document.getElementById("validation_result").style.color="green";
									document.getElementById("acysms_phoneverification").style.display="none";
							}';
								}else{
									$phoneField .= 'signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
									document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
									document.getElementById("'.$idElementCodeRequest.'").submit();
							}';
								}
								$phoneField .='else {
													document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
													document.getElementById("validation_result").innerHTML = response.errorMessage;
													document.getElementById("validation_result").style.color="red";
												}
						}
					}).send();
				}
			};
			sendCode = function(){
				'.$additionalTreatmentForSendCode.'
				document.getElementById("spinner_button").innerHTML = "<span id=\"ajaxSpan\" class=\"onload\"></span>";
				try{
					new Ajax('.$ajaxURLForSendCode.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'","\'",JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForSendCode.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'","\'",JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).send();
				}
			};
		</script>
		<div id="acysms_button_send">
				<span style="color:#1EA0FC">'.str_replace("'","\'",JText::_($jtextInstruction)).'</span>
				'.$phoneFieldToDisplay.'
				<div id="spinner_button"><button id="send_code" type="button" onclick="sendCode();">Send a Code</button></div>
				<span style="color:red" id="sending_result"></span>
		</div>';
		return $phoneField;
	}


	public function onACYSMSbeforeSaveConfig($configObject) {
		if(!is_array($configObject) || !isset($configObject['require_confirmation_hikashop']))return;
		$config = ACYSMS::config();
		$isConfirmationEnabled = $this->isIntegrationNeeded('hikashop');

		if($isConfirmationEnabled == $configObject['require_confirmation_hikashop']) return;
		if($isConfirmationEnabled != 0) return;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT config_value FROM #__hikashop_config WHERE config_namekey = "checkout"');
		$checkoutWorkFlow = $db->loadResult();

		if(strpos($checkoutWorkFlow, 'plg.acysms.acysmsverifnumber') === false) {
			$db->setQuery('UPDATE #__hikashop_config SET config_value = REPLACE(config_value, "confirm", "plg.acysms.acysmsverifnumber_confirm") WHERE config_namekey = "checkout"');
			$db->query();
		}
	}

	public function onCheckoutStepList(&$list) {
		$this->init();
		if(!$this->isIntegrationNeeded('hikashop')) return;
		$list['plg.acysms.acysmsverifnumber'] = JText::_('SMS_VERIFICATION_NUMBER');
	}


	public function onAfterCheckoutStep($controllerName, &$go_back, $original_go_back, &$controller) {
		if(!$this->init()) return;
		if(!$this->isIntegrationNeeded('hikashop')) return;
		if($controllerName != 'plg.acysms.acysmsverifnumber') return;
		$cart = $controller->initCart(); //we load the cart
		if(empty($cart->payment)) return;
		$app = JFactory::getApplication();
		$paymentId = (int)$app->getUserState(HIKASHOP_COMPONENT.'.payment_id', 0);
		if(empty($paymentId)){
			if(empty($cart->payment->payment_id)) return;
			$paymentId = $cart->payment->payment_id;
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT payment_type FROM #__hikashop_payment WHERE payment_id = "'.intval($paymentId).'"');
		$paymentMethod = $db->loadResult();
		if($paymentMethod != 'collectondelivery') return;
		$this->phoneNumber = $cart->billing_address->address_telephone;
		$go_back = !$this->_checkIfConfirmed();
	}

	public function onCheckoutStepDisplay($layoutName, &$html, &$view) {
		if(!$this->init()) return;
		if(!$this->isIntegrationNeeded('hikashop')) return;
		if($layoutName != 'plg.acysms.acysmsverifnumber') return;

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'component.css');

		$cart = $view->initCart(); //we load the cart to load the customer information
		if(empty($cart->payment)) return;

		$this->firstName = !empty($cart->billing_address->address_firstname) ? $cart->billing_address->address_firstname : '';
		$this->lastName = !empty($cart->billing_address->address_lastname) ? $cart->billing_address->address_lastname : '';
		$this->phoneNumber = !empty($cart->billing_address->address_telephone) ? $cart->billing_address->address_telephone : '' ;

		$paymentId = $cart->payment->payment_id;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT payment_type FROM #__hikashop_payment WHERE payment_id = "'.intval($paymentId).'"');
		$paymentMethod = $db->loadResult();

		if(!$this->_checkIfConfirmed() && $paymentMethod == 'collectondelivery')
			echo $this->displayPhoneField('hikashop');
	}

	private function _displayConfirmationAreaJoomlaSub() {
		if(!$this->isIntegrationNeeded('joomlasub')) return;
		$newField = $this->displayPhoneField('joomlasub');
		$this->_replaceConfirmButtonJoomlaSub($newField);

	}

	private function _replaceConfirmButtonJoomlaSub($newField) {
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"member-registration\".*>.*)<button.*type=\"submit\".*>.*<\/button>(.*<\/form>)#sU",'$1'.$newField.'$2',$body);
		JResponse::setBody($body);
	}

	private function _replaceConfirmButtonJoomlaSub15($newField) {
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"josForm	\".*>.*)<button.*type=\"submit\".*>.*<\/button>(.*<\/form>)#sU",'$1'.$newField.'$2',$body);
		JResponse::setBody($body);
	}

	private function _displayConfirmationAreaJoomlaSub15() {
		if(!$this->isIntegrationNeeded('joomlasub')) return;
		$newField = $this->_displayPhoneField('joomlasub15');
		$this->_replaceConfirmButtonJoomlaSub15($newField);

	}

	private function _displayConfirmationAreaJomsocial() {
		if(!$this->isIntegrationNeeded('jomsocial')) return;
		$data = $this->_loadDBinformation();
		$newField = $this->displayPhoneField('jomsocial', $data);
		$this->_replaceConfirmButtonJomsocial($newField);

	}

	private function _replaceConfirmButtonJomsocial($newField) {
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"jomsForm\".*>.*)<input[ a-zA-Z0-9\"\'_=-]*type=\"submit\"[ a-zA-Z0-9\"\'_=-]*>(.*<\/form>)#sU",'$1'.$newField.'$2',$body);
		JResponse::setBody($body);
	}

	private function _loadDBinformation() {
		$data = array();
		$db = JFactory::getDBO();

		$db->setQuery('SELECT value FROM #__acysms_config WHERE namekey = \'jomsocial_field\'');
		$data['fieldid'] = $db->loadResult();
		if(empty($fieldId)) {
			$db->setQuery('SELECT id FROM #__community_fields WHERE fieldcode = \'FIELD_MOBILE\'');
			$data['fieldid'] = $db->loadResult();
		}
		$currentSession = JFactory::getSession() ;
		$session_id = $currentSession->get('session.token');
		$db->setQuery('SELECT name FROM #__community_register WHERE token = '.$db->quote($session_id).' ORDER BY created DESC LIMIT 1');
		$data['name'] = $db->loadResult();

		return $data;
	}



}//endclass
