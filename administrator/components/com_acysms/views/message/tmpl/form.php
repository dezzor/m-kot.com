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
<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm"  id="adminForm" enctype="multipart/form-data">
	<table class="adminform" width="100%">
		<tr>
			<td class="key" id="subjectkey">
				<label for="message_subject">
					<?php echo JText::_('SMS_SUBJECT'); ?>
				</label>
			</td>
			<td id="subjectinput">
				<input type="text" name="data[message][message_subject]" id="message_subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->message->message_subject); ?>" />
			</td>
			<td class="key" id="categorykey">
				<label for="status">
					<?php echo JText::_('SMS_CATEGORY'); ?>
				</label>
			</td>
			<td>
				<?php echo $this->category->display('data[message][message_category_id]',@$this->message->message_category_id); ?>
			</td>
		</tr>
		<tr>
			<td class="key" id="createdkey">
				<?php echo JText::_('SMS_CREATED_DATE'); ?>
			</td>
			<td id="createdinput">
				<?php if(!empty($this->message->message_created)) echo ACYSMS::getDate(@$this->message->message_created);  ?>
			</td>
			<td class="key" id="senderkey">
				<label for="status">
					<?php echo JText::_('SMS_SENDER_PROFILE'); ?>
				</label>
			</td>
			<td>
				<?php
					echo $this->senderprofile;
				?>
			</td>
		</tr>
		<?php if(!empty($this->message->sentby)) { ?>
		<tr>
			<td class="key" id="sentbykey" colspan="2">
				<?php echo JText::_('SMS_SENT_BY'); ?>
			</td>
			<td id="sentbyinput" colspan="2">
				<?php echo $this->escape(@$this->message->sender_name); ?>
			</td>
		</tr>
		<?php } if(!empty($this->message->message_senddate)){?>
		<tr>
			<td class="key" id="senddatekey">
				<?php echo JText::_('SMS_SEND_DATE'); ?>
			</td>
			<td id="senddateinput">
				<?php echo ACYSMS::getDate(@$this->message->message_senddate);?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<table width="100%">
		<tr>
			<td valign="top">
				<div id="sms_global">
					<?php
						$countType = ACYSMS::get('type.countcharacters');
						echo $countType->countCaracters('message_body','');
					?>
					<div id="sms_body">
						<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?> onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="data[message][message_body]" id="message_body" ><?php echo $this->escape(@$this->message->message_body); ?></textarea>
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
						echo $this->sliders->startPane('infos_tab');
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
	<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->message->message_id); ?>" />
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="data[message][message_type]" value="<?php echo $this->message->message_type; ?>" />
	<input type="hidden" name="data[message][message_autotype]" value="<?php echo $this->escape(@$this->message->message_autotype); ?>" />
	<input type="hidden" name="data[message][message_status]" value="<?php echo $this->escape(@$this->message->message_status); ?>" />
	<input type="hidden" name="task" value="" />
	<?php
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		echo '<input type="hidden" name="ctrl" value="'.JRequest::getCmd('ctrl').'" />';
		echo JHTML::_('form.token');
	?>
</form>
</div>
