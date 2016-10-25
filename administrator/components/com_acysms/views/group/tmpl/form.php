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
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=group" method="post" name="adminForm"  id="adminForm" autocomplete="off">
		<table class="adminform" cellspacing="1" width="100%">
			<tr>
				<td>
					<label for="name">
						<?php echo JText::_( 'SMS_GROUP_NAME' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[group][group_name]" id="name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->group->group_name); ?>" />
				</td>
				<td>
					<label for="enabled">
						<?php echo JText::_( 'SMS_ENABLED' ); ?>
					</label>
				</td>
				<td>
					<?php echo JHTML::_('acysmsselect.booleanlist', "data[group][group_published]" , '',$this->group->group_published); ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="alias">
						<?php echo JText::_( 'SMS_ALIAS' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[group][group_alias]" id="alias" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->group->group_alias); ?>" />
				</td>
				<td class="key">
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
					<label for="creator">
						<?php echo JText::_( 'SMS_CREATOR' ); ?>
					</label>
				</td>
				<td>
					<input type="hidden" id="groupcreator" name="data[group][group_user_id]" value="<?php echo $this->escape(@$this->group->group_user_id); ?>" />
					<?php echo '<span id="creatorname">'.@$this->group->group_creatorname.'</span>';
					echo ' <a class="modal" title="'.JText::_('SMS_EDIT',true).'"  href="index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=user&amp;task=choosejoomuser&currentIntegration=acysms" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('SMS_EDIT',true).'"/></a>';
					?>
				</td>
				<td>
					<?php echo JText::_('SMS_COLOUR'); ?>
				</td>
				<td>
					<?php echo $this->colorBox->displayAll('','data[group][group_color]',@$this->group->group_color); ?>
				</td>
			</tr>
		</table>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'SMS_DESCRIPTION' ); ?></legend>
			<?php echo $this->editor->display();?>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_( 'SMS_ACCESS_LEVEL' ); ?></legend>
			<?php echo $this->acltype->display('data[group][group_access_manage]', @$this->group->group_access_manage); ?>
		</fieldset>

		<div class="clr"></div>

		<input type="hidden" name="cid[]" value="<?php echo @$this->group->group_id; ?>" />
		<input type="hidden" name="option" value="com_acysms" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="group" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
