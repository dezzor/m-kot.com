<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-message">
<br  style="font-size:1px;" />
	<fieldset class="adminform" >
		<legend><?php echo JText::_( 'SMS_RECEIVER_INFORMATIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td>
					<?php echo $this->integration_list; ?>
				</td>
			</tr>
			<tr>
				<td>
				<?php
					 echo JText::sprintf('SMS_INTEGRATION_DEFAULT',$this->integrationType);
				?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform" >
		<legend><?php echo JText::_( 'SMS_DEFAULT_VALUES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_DEFAULT_COUNTRY'); ?>
				</td>
				<td>
				<?php
					echo $this->countryPrefix;
				?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					$link = ACYSMS_LIVE.'administrator/index.php?option=com_acysms&ctrl=fields';
					if(!empty($this->idPhoneField))
						$link.='&task=edit&fields_fieldid='.$this->idPhoneField;
					 echo '<a href="'.$link.'">'.JText::_('SMS_CHANGE_DEFAULT_COUNTRY_CUSTOM_FIELD').'</a>';
					?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform" >
		<legend><?php echo JText::_( 'SMS_MISCELLANEOUS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_MESSAGE_MAX_CHARACTERS'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[messageMaxChar]" style="width:50px" value="<?php echo empty($this->messageMaxChar) ? 0 : $this->escape($this->messageMaxChar); ?>">
				</td>
			</tr>
		</table>
	</fieldset>
</div>
