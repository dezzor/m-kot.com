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

class ACYSMSmessageClass extends ACYSMSClass{
	var $tables = array('queue'=>'queue_message_id', 'stats'=>'stats_message_id', 'statsdetails' => 'statsdetails_message_id', 'message' => 'message_id');
	var $pkey = 'message_id';
	var $namekey = 'message_subject';
	var $allowedFields = array('message_id','message_receiver_table','message_userid','message_subject','message_body','message_delay','message_type','message_senddate','message_status','message_receiver','message_category_id','message_senderid','message_senderprofile_id','message_created');


	function get($id){
		if(empty($id)) return null;
		$column = is_numeric($id) ?  'message_id' : 'message_subject';
		$query = 'SELECT * FROM '.ACYSMS::table('message').' WHERE ';
		$query .= $column.' = '.$this->database->Quote($id);
		$query.= ' LIMIT 1';
		$this->database->setQuery($query);
		$message =  $this->database->loadObject();
		if(!empty($message->message_receiver)){
			$message->message_receiver = unserialize($message->message_receiver);
		}
		return $message;
	}

	function scheduleMessage($messageId, $date){
		$this->errors = array();
		if($date < time()){
			$this->errors[] = JText::_('SMS_SELECT_DATE_FUTUR');
			return false;
		}else{
			$message = $this->get($messageId);
			$message->message_senddate = $date;
			$this->save($message);
			return true;
		}

	}

	public function manageAttachment($messageId){
		$filename = array();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		jimport('joomla.filesystem.file');
		$importedFiles =  JRequest::getVar('importfile', array(), 'files','array');
		if(empty($importedFiles)) return false;
		for($i=0; $i<count($importedFiles['name']); $i++) {
			$importFile = array();
			foreach($importedFiles as $fileAttribute => $fileValue) {
				$importFile[$fileAttribute] = $fileValue[$i];
			}
			$fileError = $importFile['error'];
			if($fileError == 4) continue; //if we don't have file we don't continue

			if($fileError > 0) {
				switch ($fileError) {
					case 1:
						$app->enqueueMessage('The uploaded file exceeds the upload_max_filesize directive in php configuration.', 'error');
						continue;
					case 2:
						$app->enqueueMessage('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'error');
						continue;
					case 3:
						$app->enqueueMessage('The uploaded file was only partially uploaded.', 'error');
						continue;
				}
			}
			$allowedFileFormat = 'jpeg,jpg,gif,png,bmp,mp3,midi,ogg,mp4,mpeg,pdf,vcard,csv';
			if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$allowedFileFormat).')$#Ui',$importFile['name'],$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui',$importFile['name'])){
				$app->enqueueMessage(JText::sprintf( 'SMS_ACCEPTED_TYPE',substr($importFile['name'],strrpos($importFile['name'],'.')+1),$allowedFileFormat), 'warning');
				return false;
			}

			$importHelper = ACYSMS::get('helper.import');
			$uploadPath = $importHelper->getUploadDirectory();

			ACYSMS::createDir($uploadPath);

			if(!is_writable($uploadPath)){
				@chmod($uploadPath,'0755');
				if(!is_writable($uploadPath)){
					$app->enqueueMessage(JText::sprintf('SMS_WRITABLE_FOLDER',$uploadPath), 'notice');
					return false;
				}
			}

			$tempfilename = $filename[] = 'importFile_'.time().preg_replace("#[^a-z0-9]#i","_",JFile::stripExt($importFile['name'])).".".JFile::getExt($importFile['name']);

			if(!JFile::upload($importFile['tmp_name'], $uploadPath.$tempfilename)){
				if( !move_uploaded_file($importFile['tmp_name'], $uploadPath.$tempfilename)) {
					$app->enqueueMessage(JText::sprintf( 'SMS_FAIL_UPLOAD','<b><i>'.$importFile['tmp_name'].'</i></b>','<b><i>'.$uploadPath.$tempfilename.'</i></b>'), 'error');
					continue;
				}
			}
		}
		$fileToDelete = JRequest::getString('message_attachment_delete','');
		$fileToDeleteExploded = explode(",",$fileToDelete);
		$db = JFactory::getDBO();
		$db->setQuery('SELECT message_attachment FROM #__acysms_message WHERE message_id='.intval($messageId));
		$media = $db->loadResult();
		$mediaExploded = (empty($media)) ? array() : explode(",",$media);
		if(!empty($fileToDelete)) {
			foreach($fileToDeleteExploded as $oneFileToDelete) {
				unset($mediaExploded[array_search($oneFileToDelete, $mediaExploded)]);
			}
		}
		$mediaExploded = array_merge($mediaExploded,$filename);
		$media = implode(",",$mediaExploded);
		$db->setQuery('UPDATE #__acysms_message SET message_attachment = '.$db->Quote($media).' WHERE message_id='.intval($messageId));
		$db->query();
	}


	function saveForm(){
		$app = JFactory::getApplication();
		$message = new stdClass();

		$message->message_id = ACYSMS::getCID('message_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );

		if(!empty($formData['message']['message_receiver']) && is_array($formData['message']['message_receiver'])) $formData['message']['message_receiver'] = serialize($formData['message']['message_receiver']);
		foreach($formData['message'] as $column => $value){
			if($app->isAdmin() OR in_array($column,$this->allowedFields)){
				ACYSMS::secureField($column);
				if($column == 'params' || $column == 'message_receiver'){
					$message->$column = $value;
				}else{
					$message->$column = strip_tags($value);
				}
			}
		}

		$messageid = $this->save($message);
		if(!$messageid) return false;
		JRequest::setVar( 'message_id', $messageid);
		$this->manageAttachment($messageid);
		return true;
	}

	function save($message){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		if(!empty($message->message_type)){
			if($message->message_type == 'standard') $message->message_autotype = '';
			if($message->message_type == 'standard' && $message->message_status != 'scheduled' && !empty($message->message_senddate)) $message->message_senddate = time();
			if($message->message_type == 'auto' && empty($message->message_autotype)) $message->message_type ='draft';
		}


		if(!$app->isAdmin() && $message->message_type == 'standard' ){
			$allowCustomerManagement = $config->get('allowCustomersManagement');
			if($allowCustomerManagement){
				$my = JFactory::getUser();
				$message->message_usecredits = 1;
			}
		}

		if(!empty($message->message_body)){
			$message->message_body = trim($message->message_body);

			$message->message_body = str_replace(chr(194),chr(32),$message->message_body);
		}

		if(!empty($message->message_receiver) && !is_string($message->message_receiver)) $message->message_receiver = serialize($message->message_receiver);

		if(empty($message->message_id)){
			if(empty($message->message_created)) $message->message_created = time();
			if(empty($message->message_userid)){
				$user = JFactory::getUser();
				$message->message_userid = $user->id;
			}
			if(empty($message->message_receiver_table)){
				$integration = ACYSMS::getIntegration();
				$message->message_receiver_table = $integration->componentName;
			}
			$status = $this->database->insertObject(ACYSMS::table('message'),$message);
		}else{
			$status = $this->database->updateObject(ACYSMS::table('message'),$message,'message_id');
		}
		if($status) return empty($message->message_id) ? $this->database->insertid() : $message->message_id;
		return false;
	}

	function getAutoMessage($autotype){
		$query = 'SELECT * FROM #__acysms_message WHERE message_type = "auto" AND message_autotype = '.$this->database->Quote($autotype);
		$this->database->setQuery($query);
		$allMessages = $this->database->loadObjectList();
		foreach($allMessages as $i => $oneMessage){
			$allMessages[$i]->message_receiver = unserialize($oneMessage->message_receiver);
		}
		return $allMessages;
	}


	public function sendOneShotSMS($message, $receiverIds){

		if(empty($receiverIds) || empty($message)) return false;

		$senderProfileclass = ACYSMS::get('class.senderprofile');
		$phoneHelper = ACYSMS::get('helper.phone');

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);

		foreach($receiverIds as $oneReceiverId){
			$newUser = new stdClass();
			$newUser->queue_receiver_id = $oneReceiverId;
			$userInformationsArray[$oneReceiverId] = $newUser;
		}
		$integration->addUsersInformations($userInformationsArray);

		$conversationTitle = '';
		foreach($userInformationsArray as $oneUserInformations){
			if(!empty($oneUserInformations->receiver_name))	$conversationTitle .= ' '.$oneUserInformations->receiver_name;
		}

		$message->message_subject = JText::_('SMS_CONVERSATION').' '.ACYSMS::getDate(time(),2).' : '.$conversationTitle;
		$message->message_receiver_table = $integration->componentName;
		$message->message_senddate = $message->message_created = time();
		$messageId = $this->save($message);
		if(!$messageId){
			ACYSMS::display(JText::_('SMS_ERROR_SAVING'), 'warning');
			return false;
		}

		$acyquery = ACYSMS::get('class.acyquery');
		$integration->initQuery($acyquery);
		if(!empty($receiverIds)) $acyquery->addUserFilters($receiverIds, $integration->componentName, $integration->componentName);
		$querySelect = $acyquery->getQuery(array($messageId.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($message->message_receiver_table).','.time().',0,2, ""'));

		$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`, `queue_paramqueue`) '.$querySelect;
		$db->setQuery($finalQuery);
		$db->query();

		$this->manageAttachment($messageId);

		$queueHelper = ACYSMS::get('helper.queue');
		$queueHelper->total = 1;
		$queueHelper->report = false;
		$queueHelper->detailedReport = false;
		$queueHelper->message_id = $messageId;
		$queueHelper->process();

		if(!empty($queueHelper->detailledMessageLogs)){
			foreach($queueHelper->detailledMessageLogs as $messageStatus => $oneMessage){
				if($messageStatus === 1){
					ACYSMS::display($oneMessage);
				}else{
					ACYSMS::display($oneMessage,'warning');
				}
			}
		}
	}

	public function countMessageParts($msg){

		$gsm7bitChars = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
		$gsm7bitExChar = "^{}\\[~]|€";
		$gsm7bitUnits = 0;
		$utf16codeUnits = 0;

		for ($i = 0, $msgSize = mb_strlen($msg); $i < $msgSize; $i++) {
			if(isset($gsm7bitUnits)){
				if(ord($msg[$i]) != 13){
					if(strpos($gsm7bitChars, $msg[$i]) !== false) {
						$gsm7bitUnits++;
					} else if(strpos($gsm7bitExChar, $msg[$i]) !== false) {
						$gsm7bitUnits += 2;
					} else {
						$gsm7bitUnits = null;
					}
				}
			}
			$utf16codeUnits += ord($msg[$i]) < 0x10000 ? 1 : 2;
		}

		if(!empty($gsm7bitUnits)) {
			$messageSize = $gsm7bitUnits;
			$messageLimit = 160;
			$messageCut = 153;
		}else if(empty($gsm7bitUnits) && !empty($utf16codeUnits) && strlen($msg) > 0){
			$messageSize = $utf16codeUnits;
			$messageLimit = 70;
			$messageCut = 67;
		}
		if($messageSize > $messageLimit){
				$smsPart = round(($messageSize/$messageCut)+1);
		}else{
			$smsPart = 1;
		}

		$partInformations = new stdClass();
		$partInformations->messageSize = $messageSize;
		$partInformations->nbParts = $smsPart;
		$partInformations->messageLimit = $messageLimit;

		return $partInformations;
	}

	public function checkMsgAccess($messageId,$user) {
		$db = JFactory::getDBO();
		$db->setQuery('SELECT message_id FROM #__acysms_message WHERE message_userid = '.intval($user->id));
		$messageAvailableForUser = $db->loadObjectList();
		foreach($messageAvailableForUser as $oneMessage) {
			if($oneMessage->message_id == $messageId) return true;
		}
		return false;
	}
}
