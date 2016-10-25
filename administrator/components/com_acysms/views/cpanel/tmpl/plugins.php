<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="config_plugins">
	<br  style="font-size:1px;" />
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'SMS_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('SMS_NAME'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_ENABLED'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_( 'SMS_ID' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->plugins);$i<$a;$i++){
					$row =& $this->plugins[$i];
					$publishedid = 'published_'.$row->id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $i+1 ?>
					</td>
					<td>
						<a target="_blank" href="<?php echo version_compare(JVERSION,'1.6.0','<') ? 'index.php?option=com_plugins&amp;view=plugin&amp;client=site&amp;task=edit&amp;cid[]=' : 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id='; echo $row->id?>" ><?php echo $row->name; ?></a>
					</td>
					<td align="center">
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleHelper->toggle($publishedid,$row->published,'plugins') ?></span>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</div>
