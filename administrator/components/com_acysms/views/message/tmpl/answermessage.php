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
	<form action="index.php?tmpl=component&amp;option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=message" method="post" name="adminForm"  id="adminForm" autocomplete="off" enctype="multipart/form-data">
		<fieldset class="acysmsheaderarea">
			<div class="acysmsheader icon-48-message" style="float: left;"><?php echo JText::_('SMS_MESSAGE').' : '.@$this->mail->subject; ?></div>
			<div class="toolbar" id="toolbar" style="float: right;">
				<table><tr>
				<td><a onclick="javascript:submitbutton('apply'); return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('SMS_SAVE',true); ?>"></span><?php echo JText::_('SMS_SAVE'); ?></a></td>
				</tr></table>
			</div>
		</fieldset>
	<div id="iframetemplate"></div><div id="iframetag"></div>


	<div id="iframedoc"></div>
	<script type="text/javascript">
		function insertTag(tag){
			try{
				myField = document.getElementById('message_body');
				if(document.selection) {
					myField.focus();
					sel = document.selection.createRange();
					sel.text = tag;
				} else if(myField.selectionStart || myField.selectionStart == '0') {
					var startPos = myField.selectionStart;
					var endPos = myField.selectionEnd;
					myField.value = myField.value.substring(0, startPos)
						+ tag
						+ myField.value.substring(endPos, myField.value.length);
				} else {
					myField.value += tag;
				}
				countCharacters();
			}catch(err){
				document.getElementById("messagetags_info").innerHTML = '<?php echo JText::_('SMS_COPY_TAG'); ?><br />'+tag;
			}
		}
	</script>
		<table width="100%">
			<tr>
				<td valign="top">
					<table class="adminform" width="100%">
						<tr>
							<td class="key" id="subjectkey">
								<label for="message_subject">
									<?php echo JText::_( 'SMS_SUBJECT' ); ?>
								</label>
							</td>
							<td id="subjectinput">
								<input type="text" name="data[message][message_subject]" id="message_subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->message->message_subject); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key" id="senderkey">
								<label for="status">
									<?php echo JText::_( 'SMS_SENDER_PROFILE' ); ?>
								</label>
							</td>
							<td>
								<?php
								$this->senderprofile->includeJS = true;
								echo $this->senderprofile->display('data[message][message_senderprofile_id]',@$this->message->message_senderprofile_id); ?>
							</td>
						</tr>
					</table>
					<div id="sms_global">
						<?php
							$countType = ACYSMS::get('type.countcharacters');
							echo $countType->countCaracters('message_body','');
						?>
						<div id="sms_body">
							<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?>" onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="data[message][message_body]" id="message_body" ><?php echo $this->escape(@$this->message->message_body); ?></textarea>
							<?php $phoneType = ACYSMS::get('helper.phone'); echo $phoneType->displayMMS($this,true); ?>
						</div>
						<div id="sms_bottom">
						</div>
					</div>
				</td>
				<td id="messagetags" width="400" valign="top">
					<div>
						<div id="messagetags_info"></div>
						<?php
							echo $this->sliders->startPane( 'infos_tab');
							foreach($this->tags as $oneType => $oneTag){
								echo $this->sliders->startPanel($oneTag->name,$oneType);
								echo $oneTag->content;
								echo $this->sliders->endPanel();
							}
							echo $this->sliders->endPane();
						?>
					</div>
				</td>
			</tr>
			</table>
		<div class="clr"></div>
		<input type="hidden" name="message_id" value="<?php echo $this->escape(@$this->message->message_id); ?>" />
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="data[message][message_type]" value="<?php echo $this->message->message_type; ?>" />
		<input type="hidden" name="data[message][message_autotype]" value="<?php echo $this->escape(@$this->message->message_autotype); ?>" />
		<input type="hidden" name="data[message][message_status]" value="<?php echo $this->escape(@$this->message->message_status); ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="message" />
		<input type="hidden" name="defaultform" value="answermessage" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
