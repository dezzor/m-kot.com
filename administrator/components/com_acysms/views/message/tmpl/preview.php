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
	<script langage="javascript">
		window.addEvent("domready", function(){showAutoParams()});
		function showAutoParams()
		{
			document.getElementById('auto').style.display="none";
			document.getElementById('standard').style.display="none";
			document.getElementById('draft').style.display="none";
			document.getElementById('delay').style.display="none";
			document.getElementById('acysms_filters').style.display="none";
			document.getElementById('acysms_filter_params').style.display="none";
			document.getElementById('submitsend').style.display="none";
			document.getElementById('submitsave').style.display="none";


			if(document.getElementById('messageType_auto') && document.getElementById('messageType_auto').checked)
			{
				document.getElementById('auto').style.display="block";
				document.getElementById('submitsave').style.display="block";
				document.getElementById('acysms_filters').style.display="block";
				document.getElementById('acysms_filter_params').style.display="block";
			}
			else if (document.getElementById('messageType_standard').checked && document.getElementById('messageType_scheduled').checked)
			{
				document.getElementById('standard').style.display="block";
				document.getElementById('delay').style.display="block";
				document.getElementById('submitsend').style.display="block";
				document.getElementById('acysms_filter_params').style.display="block";
				document.getElementById('acysms_filters').style.display="block";
			}
			else if (document.getElementById('messageType_standard').checked )
			{
				document.getElementById('standard').style.display="block";
				document.getElementById('acysms_filters').style.display="block";
				document.getElementById('submitsend').style.display="block";
				document.getElementById('acysms_filter_params').style.display="block";
			}
			else if (document.getElementById('messageType_draft').checked)
			{
				document.getElementById('draft').style.display="block";
			}

		}
	</script>
	<div id="iframedoc"></div>
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm"  id="adminForm" autocomplete="off">
		<fieldset class="adminform" width="100%" id="smsParams">
			<legend><?php  echo JText::_( 'SMS_PARAMS' ); ?></legend>
			<!-- -->
				<div id="acysmsIntegrationDropdown">
					<?php
						if($this->integrations->nbIntegrations > 1) echo JText::sprintf('SMS_SEND_THIS_SMS_TO',$this->integrations->display);
						else echo '<input type="hidden" id="selectedIntegration" value="'.$this->currentIntegration.'" />';
					?>
				</div>
				<div style="padding: 5px;">
					<?php echo JText::_( 'SMS_WHAT_TYPE' ); ?>
					<?php echo  JHTML::_('acysmsselect.radiolist', $this->message_types, 'data[message][message_type]', 'onclick="setTimeout(function(){showAutoParams();}, 50);"', 'value', 'text', $this->message->message_type, 'messageType_' );?>
				</div>
				<div class="acysms_message_params">
		<!-- auto message -->
					<div id="auto" <?php if($this->message->message_type != "auto") echo 'style="display:none"'; ?>>
						<div style="padding: 10px;" id="autoSendParameters">
							<div id="sendBasedOn">
								<?php echo $this->messageBasedOn; ?>
							</div>
							<div id="autosms_params"><?php
								if(!empty($this->message->message_autotype)){
									$this->dispatcher->trigger('onACYSMSDisplayParamsAutoMessage_'.$this->message->message_autotype, array($this->message));
								 }
								 ?>
							</div>
						</div>
					</div>
		<!-- Standard message -->
					<div id="standard" <?php if($this->message->message_type != "standard") echo 'style="display:none"'; ?>>
						<div style="padding:10px;">
							<?php echo  JHTML::_( 'acysmsselect.radiolist', $this->message_senddate, 'data[message][message_status]', 'onclick="setTimeout(function(){showAutoParams();}, 50);"', 'value', 'text',(isset($this->message->message_status) && $this->message->message_status == 'scheduled') ? 'scheduled' : 'notsent','messageType_');?>
						</div>
						<div  id="delay" style="padding: 10px;<?php if($this->message->message_status != "scheduled") echo 'display:none;'; ?>" >
							 <?php  foreach($this->timeField as $oneField) echo $oneField.' '; ?>
						</div>
					</div>
		<!-- Filters -->
					<div id="acysms_filters" style="display:none"></div>
						<div id="acysms_filter_params">
						<?php
							if(!empty($this->filters)){
								echo JText::_('SMS_ONY_USERS_SELECTED_INTEGRATION').JText::_('SMS_REFINE_SELECTION_ADDIND_CRITERIA').'<br />';
								foreach($this->filters as $oneType => $filter){
									echo '<input type="checkbox" id="filter_'.$oneType.'" name="data[message][message_receiver][standard][type]['.$oneType.']" value="'.$oneType.'" onclick="loadFilterParams(\''.$oneType.'\')" style="margin: 7px 0 0 7px; vertical-align:bottom;"/><label id="label_'.$oneType.'" for="filter_'.$oneType.'" style="margin: 7px 0 0 7px; vertical-align:bottom; line-height:initial;">'.$filter->name.'</label>';
								}
								foreach($this->filters as $oneType => $filter){
									if(isset($this->message->message_receiver['standard']['type'][$oneType])){
										echo '<div id="DisplayFilterParams_'.$oneType.'">';
										echo '<fieldset class="adminform" width="100%" id="smsParams"><legend>'.$filter->name.'</legend>';
										$this->dispatcher->trigger('onACYSMSDisplayFilterParams_'.$oneType,array($this->message));
										echo '</fieldset></div>';
									}
								}
							}
						?>
						</div>
					<div id="submitsave" style="padding:20px; <?php if($this->message->message_type != "auto") echo 'display:none;'; ?>">
						<button class="btn btn-primary" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('save')"; else echo "submitbutton('save')"; ?> "><?php echo JText::_('SMS_SAVE')?></button>
					</div>
					<div id="submitsend" style="padding:20px;<?php if($this->message->message_type != "standard") echo 'display:none;'; ?>">
						<button class="btn btn-primary" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('summaryBeforeSend')"; else echo "submitbutton('summaryBeforeSend')"; ?> "><?php echo JText::_('SMS_SEND')?></button>
					</div>
		<!-- Draft -->
					<div id="draft" style="padding:20px;<?php if($this->message->message_type != "draft") echo 'display:none;'; ?>" >
						<button class="btn btn-primary" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('save')"; else echo "submitbutton('save')"; ?> "><?php echo JText::_('SMS_SAVE')?></button>
					</div>
				</div>
		</fieldset>
		<!-- Test Part of the preview -->
		<fieldset class="adminform" width="100%" id="smsPreview">
			<legend><?php echo JText::_( 'SMS_PREVIEW' ); ?></legend>

			<!-- Test Message -->
			<div id="message-test">
				<?php
					$countryType = ACYSMS::get('type.country');
					$phoneHelper = ACYSMS::get('helper.phone');
					$app = JFactory::getApplication();
					if($app->isAdmin()) {
						if(!empty($this->userInformations) && !empty($this->userInformations->receiver_phone)) echo JText::sprintf('SMS_SEND_TEST_TO','<span id="test_phone">'.$this->userInformations->receiver_name.' ('.$phoneHelper->getValidNum($this->userInformations->receiver_phone).')</span>');
				 		else echo  JText::sprintf('SMS_SEND_TEST_TO','<span id="test_phone"></span>');
						echo '<a class="modal"  href="index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose&currentIntegration='.$this->currentIntegration.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" /></a>';
						if(ACYSMS_J30) $submitButton = "Joomla.submitbutton('sendTest')"; else $submitButton = "submitbutton('sendTest')";
						echo '<button class="btn" type="submit" onclick="'.$submitButton.'">'.JText::_('SMS_SEND_TEST').'</button>';
					}
				?>
			</div>
			<div id="sms_global">
				<?php
					$countType = ACYSMS::get('type.countcharacters');
					echo $countType->countCaracters('message_body','');
				?>
				<div id="sms_body">
					<div onclick="countCharacters();" onkeyup="countCharacters();" style="width:241px !important;overflow: auto; height:318px;" rows="20" name="data[message][message_body]" id="message_body" ><?php echo nl2br(@$this->message->message_body); ?></div>
					<?php $phoneType = ACYSMS::get('helper.phone'); echo $phoneType->displayMMS($this,false); ?>
				</div>
				<div id="sms_bottom">
				</div>
			</div>
		</fieldset>

		<input type="hidden" name="cid[]" value="<?php echo $this->message->message_id; ?>" />
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="sendtest" />
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
		<input type="hidden" name="currentTestIntegration" value="<?php echo  $this->currentIntegration; ?>" />
		<input type="hidden" name="<?php echo  $this->currentIntegration.'_testID'; ?>" id="testID" value="<?php if(!empty($this->userInformations->receiver_id)) echo $this->userInformations->receiver_id; ?>" />
		<?php
			if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
			echo JHTML::_( 'form.token' );
		?>
	</form>
</div>
