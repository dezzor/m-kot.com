<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content" >
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm" >
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'SMS_IMPORT_FROM' ); ?></legend>
		<?php echo JHTML::_('acysmsselect.radiolist',   $this->importvalues, 'importfrom', 'class="inputbox" size="1" onclick="updateImport(this.value);"', 'value', 'text',JRequest::getCmd('importfrom','textarea')); ?>
	</fieldset>
	<div>
	<?php foreach($this->importdata as $div => $name){
		echo '<div id="'.$div.'"';
		if($div != JRequest::getCmd('importfrom','textarea')) echo ' style="display:none"';
		echo '>';
		echo '<fieldset class="adminform">';
		echo '<legend>'.$name.'</legend>';
		include(dirname(__FILE__).DS.$div.'.php');
		echo '</fieldset>';
		echo '</div>';
		}?>
	</div>
</fieldset>
</form>
</div>
