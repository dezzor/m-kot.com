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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=answer" method="post" name="adminForm" id="adminForm" >
		<table>
			<tr>
				<td width="100%">
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td>
					<?php echo $this->dropdownFilters->integration; ?>
				</td>
				<td>
					<?php echo $this->dropdownFilters->message; ?>
				</td>
				<td>
					<?php echo $this->dropdownFilters->answerreceiver; ?>
				</td>

			</tr>
		</table>
		<table class="adminlist table table-striped table-hover" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_( 'SMS_NUM' );?>
					</th>
					<th class="title titlebox">
						<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);" />
					</th>
					<th class="title titlebody">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_SMS_BODY'), 'answer.answer_body', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titlefrom">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_FROM'), 'answer.answer_from', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titlename">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), '', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleto">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_TO'), 'answer.answer_to', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titledate">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_RECEPTION_DATE'), 'answer.answer_date', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ID' ), 'answer.answer_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
						<?php echo $this->pagination->getListFooter(); ?>
						<?php echo $this->pagination->getResultsCounter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$k = 0;

					for($i = 0,$a = count($this->rows);$i<$a;$i++){
						$row =& $this->rows[$i];
						$attachments = explode(',',$row->answer_attachment);
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td align="center">
							<?php echo JHTML::_('grid.id', $i, $row->answer_id ); ?>
						</td>
						<td align="center">
							<?php echo nl2br(ACYSMS::dispSearch($row->answer_body, $this->pageInfo->search)); ?><br />
							<?php
								foreach($attachments as $index => $oneAttachment) {
									if(empty($oneAttachment)) continue;
									if($index == 0) echo '<fieldset style="padding: 3px; float:left;"> <span style="vertical-align: top; font-size:10px;">'.JText::_('SMS_ATTACHMENTS').' : </span>';
									echo '<a href="'.$oneAttachment.'" target="_blank" class="answer_attachment"></a>';
									if($index == 0) echo '</fieldset>';
								}
							?>
						</td>
						<td align="center">
							<?php echo ACYSMS::dispSearch($row->answer_from, $this->pageInfo->search); ?>
						</td>
						<td align="center">
							<?php echo $this->escape((isset($this->receiverNames[$row->answer_receiver_table][$this->phoneHelper->getValidNum($row->answer_from)]) ? $this->receiverNames[$row->answer_receiver_table][$this->phoneHelper->getValidNum($row->answer_from)] : '')); ?>
						</td>

						<td align="center">
							<?php echo ACYSMS::dispSearch($row->answer_to, $this->pageInfo->search); ?>
						</td>
						<td align="center">
							<?php echo ACYSMS::getDate($row->answer_date, $this->pageInfo->search); ?>
						</td>
						<td width="1%" align="center">
							<?php echo $this->escape($row->answer_id); ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<div class="clr"></div>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
