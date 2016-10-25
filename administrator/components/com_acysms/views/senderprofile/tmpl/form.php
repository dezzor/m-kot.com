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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=senderprofile" method="post" name="adminForm"  id="adminForm" autocomplete="off" enctype="multipart/form-data">
		<fieldset class="adminform" width="100%" id="bodyfieldset">
			<legend><?php echo JText::_( 'SMS_SENDER_PROFILE' ); ?></legend>
			<table>
				<tr>
					<td>
						<label for="senderprofile_name"><?php echo JText::_('SMS_SENDER_PROFILE_NAME'); ?></label>
					</td>
					<td>
						<input type="text" name="data[senderprofile][senderprofile_name]" id="senderprofile_name" class="inputbox" style="width:200px;" value="<?php echo $this->escape(@$this->senderprofile->senderprofile_name);?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="senderprofile_gateway"><?php echo JText::_('SMS_GATEWAY'); ?></label>
					</td>
					<td>
						<?php echo $this->gatewaydropdown; ?>
					</td>
				</tr>
			</table>
			<fieldset class="adminform">
				<legend><?php echo JText::_('SMS_PARAMETERS'); ?></legend>
				<div id="gateway_params">
				<?php if(!empty($this->senderprofile->senderprofile_gateway)){
					$senderprofileClass = ACYSMS::get('class.senderprofile');
					$gateway = $senderprofileClass->getGateway($this->senderprofile->senderprofile_gateway,$this->senderprofile->senderprofile_params);
					if(method_exists($gateway, 'displayConfig')) $gateway->displayConfig();
				}?>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'SMS_ACCESS_LEVEL' ); ?></legend>
				<?php echo $this->acltype->display('data[senderprofile][senderprofile_access]', @$this->senderprofile->senderprofile_access); ?>
			</fieldset>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_('SMS_SEND_TEST'); ?></legend>
			<div id="message-test">
			<?php
				$countryType = ACYSMS::get('type.country');
				$phoneHelper = ACYSMS::get('helper.phone');
				 if(!empty($this->userInformations) && !empty($this->userInformations->receiver_phone)) echo JText::sprintf('SMS_SEND_TEST_TO','<span id="test_phone">'.$this->userInformations->receiver_name.' ('.$phoneHelper->getValidNum($this->userInformations->receiver_phone).')</span>');
				 else echo  JText::sprintf('SMS_SEND_TEST_TO','<span id="test_phone"></span>');
				$app = JFactory::getApplication();
				if($app->isAdmin()){
					echo ' <a id="selectreceiver" class="modal"  href="index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" /></a>';
				}
			?>
			<button class="btn" type="submit" onclick="if(document.getElementById('testID').value=='' || undefined){window.acysms_js.openBox(document.getElementById('selectreceiver'),'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose');return false;}else{ <?php  if(ACYSMS_J30) echo "Joomla.submitbutton('sendTest');}"; else echo "submitbutton('sendTest');}"; ?> "><?php echo JText::_('SMS_SEND_TEST')?></button>
			</div>

			<div id="sms_global">
				<?php
					$countType = ACYSMS::get('type.countcharacters');
					echo $countType->countCaracters('message_body','');
				?>
				<div id="sms_body">
					<textarea onclick="countCharacters();" onkeyup="countCharacters();" style="width:98%" rows="20" name="message_body" id="message_body" ><?php echo @$this->message_body; ?></textarea>
				</div>
				<div id="sms_bottom"></div>
			</div>
		</fieldset>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->senderprofile->senderprofile_id); ?>" />
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="senderprofile" />
		<input type="hidden" name="currentIntegration" value="<?php echo $this->currentIntegration; ?>" />
		<input type="hidden" name="<?php echo $this->currentIntegration.'_testID' ?>"  id="testID" value="<?php if(!empty($this->userInformations->receiver_id)) echo $this->userInformations->receiver_id; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
