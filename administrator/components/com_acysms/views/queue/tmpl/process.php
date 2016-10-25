<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php ACYSMS::display(JText::sprintf( 'SMS_QUEUE_STATUS',ACYSMS::getDate(time()) ),'info'); ?>
<form action="index.php?tmpl=component&amp;option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=queue" method="post" name="adminForm"  id="adminForm" autocomplete="off">
	<div>
	<?php if(!empty($this->queue)){ ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'SMS_QUEUE_READY' ); ?></legend>
			<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				$total = 0;
				foreach($this->queue as $messageid => $row) {
					$total += $row->nbsub;
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('SMS_READY',$row->queue_message_id,$row->message_subject,$row->nbsub);
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
			<br />
			<input type="hidden" name="totalsend" value="<?php echo $total; ?>" />
			<input type="submit" onclick="document.adminForm.task.value = 'processQueue';" value="<?php echo JText::_('SMS_SEND',true); ?>">
		</fieldset>
	<?php }?>
	<?php if(!empty($this->schedMsgs)){?>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'SMS_SCHEDULE_MESSAGE' ); ?></legend>
			<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				$sendButton = false;
				foreach($this->schedMsgs as $row) {

					if($row->message_senddate < time()) $sendButton = true; ?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('SMS_QUEUE_SCHED',$row->message_id,$row->message_subject,ACYSMS::getDate($row->message_senddate));
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
			<?php if($sendButton) { ?><br /><input onclick="document.adminForm.task.value = 'genschedule';" type="submit" value="<?php echo JText::_('SMS_SEND',true); ?>"><?php } ?>
		</fieldset>
	<?php } ?>
	<?php if(!empty($this->nextqueue)){?>
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf( 'SMS_QUEUE_STATUS',ACYSMS::getDate(time()) ); ?></legend>
			<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				foreach($this->nextqueue as $message_id => $row) {?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('SMS_READY',$row->queue_message_id,$row->message_subject,$row->nbsub);
							echo '<br />'.JText::sprintf('SMS_QUEUE_NEXT_SCHEDULE',ACYSMS::getDate($row->senddate));
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
		</fieldset>
	<?php } ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="message_id" value="<?php echo $this->infos->message_id; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="processQueue" />
	<input type="hidden" name="ctrl" value="message" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
