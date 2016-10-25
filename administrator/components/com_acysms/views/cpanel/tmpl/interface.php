<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_interface">
	<fieldset class="adminform">
		<legend>CSS</legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo ACYSMS::tooltip(JText::_('SMS_CSS_MODULE_DESC'), JText::_('SMS_CSS_MODULE'), '', JText::_('SMS_CSS_MODULE')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_module;?>
				</td>
			</tr>
			<?php if(ACYSMS_J30){ ?>
			<tr>
				<td class="key" >
				<?php echo JText::_('SMS_USE_BOOTSTRAP_FRONTEND'); ?>
				</td>
				<td>
					<?php echo $this->elements->bootstrap_frontend;?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend> <?php echo JText::_('SMS_SUBSCRIPTION'); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo JText::_('SMS_REQUIRE_CONFIRM'); ?>
				</td>
				<td>
				<?php
					$integrationNameLabel = array('joomla_subscription' => 'Joomla', 'virtuemart' => 'Virtuemart', 'hikashop' => 'Hikashop', 'jomsocial' => 'Jomsocial');
					$checked = ($this->config->get('require_confirmation')==1) ? 'checked' : '';
					echo '<input type="hidden" name="config[require_confirmation]" value="0">';
					echo '<input type="checkbox" id="confirm_acysms" name="config[require_confirmation]" value="1" '.$checked.'>';
					echo '<label for="confirm_acysms">AcySMS</label>';
					if(!empty($this->verificationCodeIntegration)){
						foreach($this->verificationCodeIntegration as $integrationName => $oneIntegration){
							if($oneIntegration){
								$checked = ($this->config->get('require_confirmation_'.$integrationName)==1) ? 'checked' : '';
								echo '<input type="hidden" name="config[require_confirmation_'.$integrationName.']" value="0">';
								echo '<input type="checkbox" id="confirm_'.$integrationName.'" name="config[require_confirmation_'.$integrationName.']" value="1" '.$checked.'>';
								echo '<label for="confirm_'.$integrationName.'">'.$integrationNameLabel[$integrationName].'</label>';
							}
						}
					}
					if(!empty($this->confirmationMessageId)){
						$linkEdit = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=message&amp;task=answermessage&amp;message_id='.$this->confirmationMessageId;
						echo '<a  class="modal" id="answer_edit" href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button class="btn" onclick="return false">'.JText::_('SMS_EDIT_SMS').'</button></a>';
					}
				?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_NOTIF_CREATE'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[admin_address]" style="width:200px" value="<?php echo $this->escape($this->config->get('admin_address')); ?>">
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend> <?php echo JText::_('SMS_FRONTEND'); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_ALLOW_FRONTEND_MANAGEMENT'); ?>
				</td>
				<td>
					<div id="frontEndManagement">
						<?php
							echo $this->frontEndManagementOption;
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="key" valign="top" >
				<?php echo JText::_('SMS_FRONTEND_FILTERS'); ?>
				</td>
				<td>
					<div id="frontEndFilters">
						<?php
							echo $this->conditionsToDisplay;
						?>
					</div>
					<button type="button" class="button btn button-margin" onclick="addCondition();return false;"><?php echo JText::_('SMS_ADD_CONDITION'); ?></button>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_REQUIRED_FILTER'); ?>
				</td>
				<td>
					<?php echo $this->requiredFilterString; ?>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend> <?php echo JText::_('SMS_CUSTOMERS'); ?></legend>
			<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_REMOVE_CREDITS_SEND_FRONT'); ?>
				</td>
				<td>
					<div id="customersManagement">
						<?php
							echo $this->customerManagementOption;
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('SMS_DEFAULT_CREDITS_URL'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[default_credits_url]" style="width:200px" value="<?php echo $this->escape($this->config->get('default_credits_url')); ?>">
				</td>
			</tr>
		</table>
	</fieldset>
</div>
