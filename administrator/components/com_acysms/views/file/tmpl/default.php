<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content">
<form action="index.php?tmpl=component&amp;option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=file" method="post" name="adminForm"  id="adminForm" autocomplete="off">
	<fieldset class="acysmsheaderarea">
		<div class="acysmsheader" style="float: left;"><h1><?php echo JText::_('SMS_FILE').' : '.$this->escape($this->file->name); ?></h1></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table><tr>
			<td><a onclick="javascript:submitbutton('save'); return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('SMS_SAVE',true); ?>"></span><?php echo JText::_('SMS_SAVE'); ?></a></td>
			<td><a onclick="javascript:submitbutton('share'); return false;" href="#" ><span class="icon-32-share" title="<?php echo JText::_('SMS_SHARE',true); ?>"></span><?php echo JText::_('SMS_SHARE'); ?></a></td>
			</tr></table>
		</div>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'SMS_FILE').' : '.$this->escape($this->file->name); ?></legend>
		<?php if(!empty($this->showLatest)){ ?> <button type="button" class="btn btn-primary" onclick="javascript:submitbutton('latest')"> <?php echo JText::_('SMS_LOAD_LATEST_LANGUAGE'); ?> </button> <?php } ?>
		<textarea style="width:700px;" rows="18" name="content" id="translation" ><?php echo $this->escape(@$this->file->content);?></textarea>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('SMS_CUSTOM_TRANS'); ?></legend>
		<?php echo JText::_('SMS_CUSTOM_TRANS_DESC'); ?>
		<textarea style="width:100%;" rows="5" name="customcontent" ><?php echo $this->escape(@$this->file->customcontent);?></textarea>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->escape($this->file->name); ?>" />
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="file" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
