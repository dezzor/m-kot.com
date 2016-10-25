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
class ACYSMSuserClass extends ACYSMSClass{
	var $tables = array('groupuser' => 'groupuser_user_id', 'user'=>'user_id');
	var $pkey = 'user_id';
	var $namekey = 'user_phone_number';
	var $allowedFields = array('user_id','user_joomid', 'user_firstname', 'user_lastname', 'user_phone_number', 'user_birthdate', 'user_email');


	var $allowModif = true;
	var $restrictedFields = array('user_id','user_joomid');

	function saveForm(){
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$app = JFactory::getApplication();

		$user = new stdClass();
		$user->user_id = ACYSMS::getCID('user_id');
		$user->user_joomid = JRequest::getInt('user_joomid');

		if(!empty($formData['user']['user_birthdate'])){
			 $formData['user']['user_birthdate'] = $formData['user']['user_birthdate']['year'].'-'.$formData['user']['user_birthdate']['month'].'-'.$formData['user']['user_birthdate']['day'];
		}

		$formData['user']['user_phone_number'] = $phoneHelper->getValidNum($formData['user']['user_phone_number']['phone_country'].$formData['user']['user_phone_number']['phone_num']);

		if(!$formData['user']['user_phone_number']){
			$app->enqueueMessage($phoneHelper->error, 'warning');
			return false;
		}
		$this->checkFields($formData['user'], $user);

		$existUser = $this->getByPhone($user->user_phone_number);
		if(!empty($existUser->user_id) && $user->user_id != $existUser->user_id){
			$this->errors[] = JText::sprintf('SMS_USER_ALREADY_EXISTS',$user->user_phone_number);
			$this->errors[] = '<a href="'.ACYSMS::completeLink('user&task=edit&cid[]='.$existUser->user_id).'" >'.JText::_('SMS_CLICK_EDIT_USER').'</a>';
			return false;
			$user->user_phone_number = $existUser->user_phone_number;
			$user->user_id = $existUser->user_id;
		}
		$user_id = $this->save($user);

		if(!$user_id) return false;
		JRequest::setVar( 'user_id', $user_id);

		if(!empty($formData['groupuser'])) return $this->saveSubscription($user_id,$formData['groupuser']);
		return true;
	}

	public function save($user){

		if(isset($user->user_activationcode) && is_array($user->user_activationcode)) $user->user_activationcode = serialize($user->user_activationcode);

		if(empty($user->user_id)){
			$status = $this->database->insertObject(ACYSMS::table('user'),$user);
		}else{
			if(count((array) $user) > 1){
				$status = $this->database->updateObject(ACYSMS::table('user'),$user,'user_id');
			}else return true;
		}
		if($status) $user_id = empty($user->user_id) ? $this->database->insertid() : $user->user_id;

		$newUserId = $this->database->insertid();

		if($status && !empty($newUserId)){
			JPluginHelper::importPlugin('acysms');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAcySMSUserCreate',array($user));
		}

		return $user_id;
	}

	function saveSubscription($user_id,$formgroups){

		$addgroups = array();
		$removegroups = array();
		$updategroups = array();

		$groupids = array_keys($formgroups);
		$currentSubscription = $this->getSubscriptionStatus($user_id,$groupids);

		foreach($formgroups as $groupid => $onegroup){
			if(empty($onegroup['status'])){
				if(isset($currentSubscription[$groupid])) $removegroups[] = $groupid;
				continue;
			}

			if(!isset($currentSubscription[$groupid])){
				if($onegroup['status'] > 0) $addgroups[$onegroup['status']][] = $groupid;

				continue;
			}

			if($currentSubscription[$groupid]->groupuser_status == $onegroup['status']) continue;

			$updategroups[$onegroup['status']][] = $groupid;
		}

		$groupUserClass = ACYSMS::get('class.groupuser');
		$status = true;
		if(!empty($updategroups)) $status = $groupUserClass->updateSubscription($user_id,$updategroups) && $status;
		if(!empty($removegroups)) $status = $groupUserClass->removeSubscription($user_id,$removegroups) && $status;
		if(!empty($addgroups)) $status = $groupUserClass->addSubscription($user_id,$addgroups) && $status;

		return $status;
	}


	function getByPhone($userPhone,$default = null){
		$this->database->setQuery('SELECT * FROM #__acysms_user WHERE user_phone_number = '.$this->database->Quote(trim($userPhone)).' LIMIT 1');
		return $this->database->loadObject();
	}
	function get($userId,$default = null){
		$this->database->setQuery('SELECT * FROM #__acysms_user WHERE user_id = '.intval($userId).' LIMIT 1');
		return $this->database->loadObject();
	}
	function getByJoomid($joomId,$default = null){
		$this->database->setQuery('SELECT * FROM #__acysms_user WHERE user_joomid = '.intval($joomId).' LIMIT 1');
		return $this->database->loadObject();
	}

	function getSubscription($userid,$index = ''){
		$query = 'SELECT groupuser.*, groups.* FROM '.ACYSMS::table('group').' AS groups ';
		$query .= 'LEFT JOIN '.ACYSMS::table('groupuser').' AS groupuser on groupuser.groupuser_group_id = groups.group_id AND groupuser.groupuser_user_id = '.intval($userid);
		$query .= ' ORDER BY groups.group_ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList($index);
	}

	function getSubscriptionStatus($userid,$groupids = null){
		$query = 'SELECT groupuser_status, groupuser_group_id FROM '.ACYSMS::table('groupuser').' WHERE groupuser_user_id = '.intval($userid);
		if(!empty($groupids)){
			JArrayHelper::toInteger($groupids, array(0));
			$query .= ' AND groupuser_group_id IN ('.implode(',',$groupids).')';
		}
		$this->database->setQuery($query);
		return $this->database->loadObjectList('groupuser_group_id');
	}

	public function checkFields(&$data,&$user){
		foreach($data as $column => $value){

			if($this->allowModif || !in_array($column,$this->restrictedFields)){
				ACYSMS::secureField($column);
				if(is_array($value)){
					if(isset($value['day']) || isset($value['month']) || isset($value['year'])){
						$value = (empty($value['year']) ? '0000' :intval($value['year'])).'-'.(empty($value['month']) ? '00' : $value['month']).'-'.(empty($value['day']) ? '00' : $value['day']);
					}else{
						$value = implode(',',$value);
					}
				}
				$user->$column = strip_tags($value);
			}
		}
		if(!empty($_FILES)){
			jimport('joomla.filesystem.file');
			$uploadFolder = trim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS.' ').DS;
			$uploadPath = JPath::clean(ACYSMS_ROOT.$uploadFolder.'userfiles'.DS);
			ACYSMS::createDir(JPath::clean(ACYSMS_ROOT.$uploadFolder),true);
			ACYSMS::createDir($uploadPath,true);

			foreach($_FILES as $typename => $type){
				$type2 = isset($type['name']['user']) ? $type['name']['user'] : $type['name'];
				if(empty($type2)) continue;
				foreach($type2 as $fieldname => $filename){
					if(empty($filename)) continue;
					ACYSMS::secureField($fieldname);
					$attachment = new stdClass();
					$filename = JFile::makeSafe(strtolower(strip_tags($filename)));
					$attachment->filename = time().rand(1,999).'_'.$filename;
					while(file_exists($uploadPath . $attachment->filename)){
						$attachment->filename = time().rand(1,999).'_'.$filename;
					}

					if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$config->get('allowedfiles')).')$#Ui',$attachment->filename,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui',$attachment->filename)){
						echo "<script>alert('".JText::sprintf( 'ACCEPTED_TYPE',substr($attachment->filename,strrpos($attachment->filename,'.')+1),$config->get('allowedfiles'))."');window.history.go(-1);</script>";
						exit;
					}
					$attachment->filename = str_replace(array('.',' '),'_',substr($attachment->filename,0,strpos($attachment->filename,$extension[0]))).$extension[0];

					$tmpFile = isset($type['name']['user']) ? $_FILES[$typename]['tmp_name']['user'][$fieldname] : $_FILES[$typename]['tmp_name'][$fieldname];
					if(!JFile::upload($tmpFile, $uploadPath . $attachment->filename)){
						echo "<script>alert('".JText::sprintf( 'FAIL_UPLOAD','<b><i>'.$tmpFile.'</i></b>','<b><i>'.$uploadPath . $attachment->filename.'</i></b>')."');window.history.go(-1);</script>";
						exit;
					}

					$user->$fieldname = $attachment->filename;
				}
			}
		}
	}

	public function getUsersInformationsById($Ids){
		if(!is_array($Ids)) return false;

		$app = JFactory::getApplication();
		$receiversArray = array();

		foreach($Ids as $oneId){
			$newObject = new stdClass();
			$newObject->queue_receiver_id = $oneId;
			$receiversArray[] = $newObject;
		}

		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);

		$integration->addUsersInformations($receiversArray);
		return $receiversArray;
	}
}
