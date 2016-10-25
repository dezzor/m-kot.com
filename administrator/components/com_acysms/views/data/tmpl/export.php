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
<?php if(JRequest::getString('tmpl') == 'component'){
?>
<fieldset>
	<div class="acyheader 48_smsexport" style="float: left;"><?php echo JText::_('SMS_EXPORT'); ?></div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<a onclick="javascript:submitbutton('doexport')" href="#" ><span class="32_acyexport" title="<?php echo JText::_('SMS_EXPORT',true); ?>"></span><?php echo JText::_('SMS_EXPORT'); ?></a>
	</div>
</fieldset>
<?php } ?>
<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=data" method="post" name="adminForm" id="adminForm" >
	<table class="table" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<fieldset class="adminform">
					<legend><?php echo JText::_( 'SMS_FIELD_EXPORT' ); ?></legend>
						<table class="adminlist table table-striped" cellpadding="1">
						<?php
							$k = 0;
							if(!empty($this->fields)) {
								foreach($this->fields as $fieldName => $fieldType){
						?>
									<tr class="<?php echo "row$k"; ?>">
										<td>
											<?php echo $fieldName ?>
										</td>
										<td align="center">
											<?php echo JHTML::_('acysmsselect.booleanlist', "exportdata[".$fieldName."]",'',in_array($fieldName,$this->selectedfields) ? 1 : 0); ?>
										</td>
									</tr>
						<?php
									$k = 1-$k;
								}
							}
						?>

						</table>
					</fieldset>
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'SMS_PARAMETERS' ); ?></legend>
						<table class="adminlist table table-striped" cellpadding="1">
							<tbody>
								<tr class="<?php echo "row$k"; $k = 1-$k;?>">
									<td>
										<?php echo JText::_('SMS_EXPORT_FORMAT'); ?>
									</td>
									<td align="center">
										<?php echo $this->charset->display('exportformat',$this->config->get('export_format','UTF-8')); ?>
									</td>
								</tr>
								<tr class="<?php echo "row$k"; ?>">
									<td>
										<?php echo JText::_('SMS_SEPARATOR'); ?>
									</td>
									<td align="center" nowrap="nowrap">
										<?php
											$values = array(
												JHTML::_('select.option', 'semicolon', JText::_('SMS_SEPARATOR_SEMICOLON')),
											JHTML::_('select.option', 'comma', JText::_('SMS_SEPARATOR_COMMA'))
										);
										$data = str_replace(array(';',','),array('semicolon','comma'), $this->config->get('export_separator',';'));
										if($data == 'colon') $data = 'comma';
										echo JHTML::_('acysmsselect.radiolist', $values, 'exportseparator', '', 'value', 'text', $data);
										?>
									</td>
								</tr>
							</tbody>
						</table>
					</fieldset>
				</td>
				<td valign="top">
					<?php if(empty($this->users)){ ?>
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'SMS_FILTERS' ); ?></legend>
						<table class="adminlist table table-striped" cellpadding="1">
							<tr class="row0">
								<td>
									<?php echo JText::_('SMS_EXPORT_SUB_LIST'); ?>
								</td>
								<td align="center" nowrap="nowrap">
									<?php echo JHTML::_('acysmsselect.booleanlist', "exportfilter[subscribed]",'onchange="if(this.value == 1){document.getElementById(\'exportgroups\').style.display = \'block\'; }else{document.getElementById(\'exportgroups\').style.display = \'none\'; }"', @$this->selectedFilters->subscribed ,JText::_('SMS_YES'),JText::_('SMS_NO').' : '.JText::_('SMS_ALL_USERS')); ?>
								</td>
							</tr>
							<tr class="row0">
								<td>
									<?php echo JText::_('SMS_USER_STATUS'); ?>
								</td>
								<td align="center" nowrap="nowrap">
									<?php echo JHTML::_('acysmsselect.radiolist',$this->userStatus, "exportfilter[userStatus]", '', 'value', 'text', @$this->selectedFilters->userStatus); ?>
								</td>
							</tr>

						</table>
					</fieldset>
					<?php } ?>
					<fieldset class="adminform" id="exportgroups" <?php echo @$this->selectedFilters->subscribed ? '' : 'style="display:none"'  ?> >
					<?php if(empty($this->users)){ ?>
						<legend><?php echo JText::_( 'SMS_GROUPS' ); ?></legend>
						<table class="adminlist table table-striped" cellpadding="1">
							<tbody>
								<?php
								$k = 0;
								foreach( $this->groups as $row){?>
									<tr class="<?php echo "row$k"; ?>">
										<td>
											<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->group_color.'"></div>';
											$text = '<b>'.JText::_('SMS_ID').' : </b>'.$row->group_id.'<br />'.$row->group_description;
											echo ACYSMS::tooltip($text, $row->group_name, 'tooltip.png', $row->group_name);
											?>
										</td>
										<td align="center" nowrap="nowrap">
											<?php  echo JHTML::_('acysmsselect.booleanlist', "exportgroups[".$row->group_id."]",'',in_array($row->group_id,$this->selectedgroups) ? 1 : 0,JText::_('SMS_YES'),JText::_('SMS_NO'),'exportgroups'.$row->group_id.'_'); ?>
										</td>
									</tr>
									<?php
									$k = 1-$k;
								}
								if(count($this->groups)>3){ 	?>
									<tr>
										<td>
										</td>
										<td align="center" nowrap="nowrap">
												<script language="javascript" type="text/javascript">
													function updateStatus(selection){
														<?php foreach($this->groups as $row){
																$languages['all'][$row->group_id] = $row->group_id;
																if($row->group_languages == 'all') continue;
																$lang = explode(',',trim($row->group_languages,','));
																foreach($lang as $oneLang){
																	$languages[strtolower($oneLang)][$row->group_id] = $row->group_id;
																}
														} ?>
														var selectedGroups = new Array();
														<?php
														foreach($languages as $val => $group_ids){
															echo "selectedGroups['$val'] = new Array('".implode("','",$group_ids)."'); ";
														}
														?>
														for(var i=0; i < selectedGroups['all'].length; i++)
														{
															<?php
															if(ACYSMS_J30){
																echo 'jQuery("label[for=exportgroups"+selectedGroups["all"][i]+"_0]").click();';
															}
															?>
															window.document.getElementById('exportgroups'+selectedGroups['all'][i]+'_0').checked = true;
														}
														if(!selectedGroups[selection]) return;
														for(var i=0; i < selectedGroups[selection].length; i++)
														{
															<?php
															if(ACYSMS_J30){
																echo 'jQuery("label[for=exportgroups"+selectedGroups[selection][i]+"_1]").click();';
															}
															?>
															window.document.getElementById('exportgroups'+selectedGroups[selection][i]+'_1').checked = true;
														}
													}
												</script>
												<?php
													$selectGroup = array();
													$selectGroup[] = JHTML::_('select.option', 'none',JText::_('SMS_NONE'));
													$selectGroup[] = JHTML::_('select.option', 'all',JText::_('SMS_ALL'));
													echo JHTML::_('acysmsselect.radiolist', $selectGroup, "selectgroups" , 'onclick="updateStatus(this.value);"', 'value', 'text');
												?>
											</td>
										</tr>
									<?php } ?>
							</tbody>
						</table>
					<?php } ?>
				</td>
			</tr>

		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
