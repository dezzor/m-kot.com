<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	if(!$this->app->isAdmin())	$URL = $this->integration->editUserFrontURL;
	else $URL = $this->integration->editUserURL;
?>
<div id="acysms_content" >
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=receiver" method="post" name="adminForm" id="adminForm">
		<table>
			<tr>
				<td width="100%" id="subscriberfilter">
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td>
					<?php if(!$this->allowCustomerManagement) echo $this->filters->integration; ?>
				</td>
				<td>
					<?php echo $this->integration->displayFiltersUserListing(); ?>
				</td>
			</tr>
		</table>
		<table class="adminlist table table-striped table-hover" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_( 'SMS_NUM' ); ?>
					</th>
					<th class="title titlebox">
						<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);" />
					</th>
					<?php

					if($this->integration->componentName != 'acysms'){

					?>
					<th class="title titlename">
						<?php echo JHTML::_('grid.sort',   JText::_('SMS_NAME'), 'receiver_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleemail">
						<?php echo JHTML::_('grid.sort',   JText::_('SMS_EMAIL'), 'receiver_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titlephone">
						<?php echo JHTML::_('grid.sort',   JText::_('SMS_PHONE'), 'receiver_phone', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
					<?php
					}else{
						if(!empty($this->displayFields)){
							foreach($this->displayFields as $map => $oneField){ ?>
								<th class="title">
									<?php echo JHTML::_('grid.sort', $this->fieldsClass->trans($oneField->fields_fieldname), 'acysmsusers.'.$map, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
								</th>
					<?php
							}
						}
					}
					?>
				<?php if($this->app->isAdmin()){ ?>
					<th class="title titletoggle">
						<?php echo JText::_('SMS_STATUS'); ?>
					</th>
					<th class="title titletoggle">
						<?php echo JText::_('SMS_CONVERSATION'); ?>
					</th>
				<?php }	?>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort',   JText::_('SMS_ID'), 'receiver_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo count($this->displayFields)+8; ?>" >
						<?php echo $this->pagination->getListFooter(); ?>
						<?php echo $this->pagination->getResultsCounter();
						if(ACYSMS_J30) echo '<br />'.$this->pagination->getLimitBox(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$k = 0;
					for($i = 0,$a = count($this->rows);$i<$a;$i++){
						$row =& $this->rows[$i];
							$validPhone = $this->phoneHelper->getValidNum($row->receiver_phone);
							$internationalPhone = str_replace('+','00',$validPhone);

							$togglePhone = 'phone_number_'.$internationalPhone;

							if(isset($this->phones[$validPhone])) $phoneStatus = 0;
							else $phoneStatus = 1;

							if($validPhone) $statusContent = $this->toggleClass->toggle($togglePhone,$phoneStatus,'phone');
							else $statusContent =  '<img title="'.JText::_('SMS_CANT_BLOCK').'" src="'.ACYSMS_IMAGES.'/warning.png" class="warning hasTooltip">';

							if($this->integration->componentName == 'virtuemart_2') $integrationID = $row->virtuemart_user_id;
							else $integrationID = $row->receiver_id;
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td align="center">
							<?php echo JHTML::_('grid.id', $i, $row->receiver_id.'_'.$validPhone ); ?>
						</td>
						<?php

						if($this->integration->componentName != 'acysms'){

						?>
							<td align="center">
								<?php echo ACYSMS::dispSearch($row->receiver_name, $this->pageInfo->search); ?>
							</td>
							<td align="center">
								<a href="<?php echo $URL.$integrationID; ?>"><?php echo ACYSMS::dispSearch($row->receiver_email, $this->pageInfo->search); ?></a>
							</td>
							<td align="center">
								<?php
									if(!empty($row->receiver_phone)){
										if(!($validPhone)) echo '<font color="red" >'.ACYSMS::dispSearch($row->receiver_phone, $this->pageInfo->search).'</font>';
										else echo ACYSMS::dispSearch($validPhone, $this->pageInfo->search);
									}else echo "";
								?>
							</td>

						<?php
						}else{
							if(!empty($this->displayFields)){
								foreach($this->displayFields as $map => $oneField){ ?>
									<td align="center">
										<?php
										if($oneField->fields_type == 'phone') echo '<a href="'.$URL.$integrationID.'">'.$this->fieldsClass->listing($oneField,@$row->$map, $this->pageInfo->search).'</a>';
										else echo $this->fieldsClass->listing($oneField,@$row->$map, $this->pageInfo->search);
										?>
									</td>
						<?php
								}
							}
						}
						?>
					<?php if($this->app->isAdmin()){ ?>
						<td align="center" style="text-align:center;">
							<span id="<?php echo $togglePhone; ?>"><?php echo $statusContent; ?></span>
						</td>
						<td align="center" style="text-align:center;">
							<a class="modal"  href="index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=conversation&receiverid=<?php echo $row->receiver_id; ?>" rel="{handler: 'iframe', size: {x: 500, y: 600}}"><img src="<?php echo ACYSMS_IMAGES;?>conversation.png" /></a>
						</td>
					<?php } ?>
						<td width="1%" align="center">
							<?php echo $row->receiver_id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
