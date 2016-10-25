<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><br  style="font-size:1px;" />
<div id="dash_users">
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('SMS_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('SMS_EMAIL'); ?>
				</th>
				<th class="title titledate">
					<?php echo JText::_( 'SMS_PHONE' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'SMS_STATUS' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'SMS_ID' );?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				if(!empty($this->users)){
					foreach($this->users as $oneUser){
						$row =& $oneUser;
						$validPhone = $this->phoneHelper->getValidNum($row->receiver_phone);
						$internationalPhone = str_replace('+','00',$validPhone);

						$togglePhone = 'phone_number_'.$internationalPhone;

						if(isset($this->phones[$validPhone])) $phoneStatus = 0;
						else $phoneStatus = 1;

						if($validPhone) $statusContent = $this->toggleClass->toggle($togglePhone,$phoneStatus,'phone');
						else $statusContent =  '<img src="'.ACYSMS_IMAGES.'/warning.png" title="'.JText::_('SMS_CANT_BLOCK').'" class="warning">';

				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $row->receiver_name; ?>
					</td>
					<td>
						<a href="<?php echo $this->integration->editUserURL.$row->receiver_id; ?>"><?php echo $row->receiver_email; ?></a>
					</td>
					<td align="center" style="text-align:center">
						<?php
							if(!empty($row->receiver_phone)){
								if(!($validPhone)) echo '<font color="red" >'.$row->receiver_phone.'</font>';
								else echo $validPhone;
							}else echo "";
						?>
					</td>
					<td align="center">
						<span id="<?php echo $togglePhone  ?>" class="loading"><?php echo $statusContent; ?></span>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->receiver_id ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
					}
				}
			?>
		</tbody>
	</table>
</div>
