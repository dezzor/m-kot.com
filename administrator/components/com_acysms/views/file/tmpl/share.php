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
		<div class="acysmsheader icon-48-share" style="float: left;"><h1><?php echo JText::_('SMS_SHARE').' : '.$this->file->name; ?></h1></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table><tr>
			<td><a onclick="if(confirm('<?php echo JText::_('SMS_CONFIRM_SHARE_TRANS',true); ?>')){ javascript:submitbutton('send');} return false;" href="#" ><span class="icon-32-share" title="<?php echo JText::_('SMS_SHARE',true); ?>"></span><?php echo JText::_('SMS_SHARE'); ?></a></td>
			</tr></table>
		</div>
	</fieldset>
	<fieldset class="adminform">
		<?php ACYSMS::display(JText::_('SMS_SHARE_CONFIRMATION_1').'<br />'.JText::_('SMS_SHARE_CONFIRMATION_2').'<br />'.JText::_('SMS_SHARE_CONFIRMATION_3'),'info'); ?><br />
		<textarea style="width:700px;" rows="8" name="msgbody">Hi Acyba team,
Here is a new version of the language file, I translated few more strings...</textarea>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="file" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
