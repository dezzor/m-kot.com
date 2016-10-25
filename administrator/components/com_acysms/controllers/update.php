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

class UpdateController extends acysmsController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}
	function listing(){
		return $this->update();
	}

	function install(){
		ACYSMS::increasePerf();

		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = ACYSMS::config();
		$config->save($newConfig);
		if(!$config->save($newConfig)){

			echo '<h2>The installation failed, some tables are missing, we will try to create them now...</h2>';

			$queries = file_get_contents(ACYSMS_BACK.'tables.sql');
			$queriesTable = explode("CREATE TABLE",$queries);

			$db= JFactory::getDBO();
			$success = true;
			foreach($queriesTable as $oneQuery){
				$oneQuery = trim($oneQuery);
				if(empty($oneQuery)) continue;
				$db->setQuery("CREATE TABLE ".$oneQuery);
				if(!$db->query()){
					echo '<br /><br /><span style="color:red">Error creating table : '.$db->getErrorMsg().'</span><br />';
					$success = false;
				}else{
					echo '<br /><span style="color:green">Table successfully created</span>';
				}
			}

			if($success){
				echo '<h2>Please install again AcySMS via the Joomla Extensions manager, the tables are now created so the installation will work</h2>';
			}else{
				echo '<h2>Some tables could not be created, please fix the above issues and then install again AcySMS.</h2>';
			}
			return;
		}

		$updateHelper = ACYSMS::get('helper.update');
		$updateHelper->installMenu();
		$updateHelper->installExtensions();
		$updateHelper->fixDoubleExtension();
		$updateHelper->addUpdateSite();
		$updateHelper->fixMenu();
		$updateHelper->installDefaultSenderProfile();
		$updateHelper->installDefaultAnswerTrigger();
		$updateHelper->installDefaultCustomFields();
		$updateHelper->installDefaultOptinMessage();
		ACYSMS::setTitle('AcySMS','acysms','dashboard');

		$this->_iframe(ACYSMS_UPDATEURL.'install&fromversion='.JRequest::getCmd('fromversion'));
	}

	function update(){
		$config = ACYSMS::config();

		ACYSMS::setTitle(JText::_('UPDATE_ABOUT'),'acyupdate','update');

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'cancel', JText::_('SMS_CLOSE'), ACYSMS::completeLink('dashboard') );

		return $this->_iframe(ACYSMS_UPDATEURL.'update');
	}

	function _iframe($url){

		$config = ACYSMS::config();
		$url .= '&version='.$config->get('version').'&component=acysms&level=pro';
?>
				<div id="acysms_div">
						<iframe allowtransparency="true" scrolling="auto" height="800px" frameborder="0" width="100%" name="acysms_frame" id="acysms_frame" src="<?php echo $url; ?>">
						</iframe>
				</div>
<?php
	}

}
