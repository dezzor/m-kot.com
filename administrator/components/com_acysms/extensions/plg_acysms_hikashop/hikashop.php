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
class plgAcysmsHikaShop extends JPlugin
{
	var $sendervalues =array();

	function plgAcysmsHikaShop(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'))	return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)) {
			$plugin = JPluginHelper::getPlugin('acysms', 'jevents');
			$this->params = new acysmsParameter( $plugin->params );
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_hikashop',JPATH_SITE);
	}






	public function  onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers','1');
		if($allowCustomerManagement && !empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_ORDER','HikaShop');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('hikashoporder'))) $filters['hikashoporder'] = $newFilter;
	}

	public function onACYSMSDisplayFilterParams_hikashoporder($message){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$app = JFactory::getApplication();


		$queryCategories = 'SELECT category_id, category_name FROM #__hikashop_category WHERE category_type = "product"';
		$db->setQuery($queryCategories);
		$hikaCategories = $db->loadObjectList();

		if(!empty($hikaCategories)){
			$hikaCategoriesOptions = array();
			$hikaCategoriesOptions[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
			foreach($hikaCategories as $oneHikaCategory){
				$hikaCategoriesOptions[] = JHTML::_('select.option', $oneHikaCategory->category_name, $oneHikaCategory->category_name);
			}
			$hikaCategoryDropDown = JHTML::_('select.genericlist', $hikaCategoriesOptions, "data[message][message_receiver][standard][hikashoporders][category]" , 'size="1" style="width:auto"','value', 'text',  '0');
		}

		$orderStatus[] = JHTML::_('select.option','',JText::_('SMS_ALL_STATUS'));
		$query = 'SELECT category_name FROM `#__hikashop_category` WHERE `category_type` = "status" AND `category_name` != "order status"';
		$db->setQuery($query);
		$category = $db->loadObjectList();
		foreach($category as $oneCategory){
			if(empty($oneCategory->value)){
				$val = str_replace(' ','_',strtoupper($oneCategory->category_name));
				$oneCategory->value = JText::_($val);
				if($val == $oneCategory->value){
					$oneCategory->value = $oneCategory->category_name;
				}
			}
			$orderStatus[] = JHTML::_('select.option', $oneCategory->category_name, $oneCategory->value);
		}

		$orderStatusDropDown =  JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][standard][hikashoporders][status]" , 'size="1" style="width:auto"','value', 'text',  '0');

		$productName = '';
		if(!empty($message->message_receiver['standard']['hikashoporders']['productName'])) $productName = $message->message_receiver['standard']['hikashoporders']['productName'];

		$ctrl = 'cpanel';
		if(!$app->isAdmin()) $ctrl = 'frontcpanel';

		echo JText::sprintf('SMS_ORDER_WITH_STATUS',$orderStatusDropDown).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedHikaProduct"/>'.$productName.'</span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=plgtrigger&plg=hikashop&fctName=displayHikaArticles\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" /></a>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][hikashoporders][product]" id="selectedHikaProduct"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][hikashoporders][productName]" id="hiddenHikaProduct"/><br />';
		if(!empty($hikaCategoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$hikaCategoryDropDown;
	}

		function onAcySMSdisplayHikaArticles(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'hikashopproducts';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order', 'hikashopproduct.product_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );

		$query = 'SELECT hikashopproduct.product_id, hikashopproduct.product_name, hikashopproduct.product_description, hikacategory.category_name
				FROM #__hikashop_product AS hikashopproduct
				LEFT JOIN #__hikashop_product_category AS hikashopproductcategory ON hikashopproductcategory.product_id = hikashopproduct.product_id
				LEFT JOIN #__hikashop_category AS hikacategory ON hikashopproductcategory.category_id = hikacategory.category_id';

		$searchMap = array('product_name','product_description','category_name');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$pageInfo->elements->total = count($rows);

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );



	 ?>
	 <script language="javascript" type="text/javascript">
		var selectedContents = new Array();
		var selectedContentsName = new Array();
		function addProduct()
		{
			var selectedProduct = "";
			var selectedProductId = "";
			var form = document.adminForm;
			for (i=0 ; i<= form.length-1 ; i++)
			{
				if(form[i].type == 'checkbox')
				{

					if(!document.getElementById("productId"+form[i].id)) continue;
					if(document.getElementById("productId"+form[i].id).innerHTML.lentgth == 0) continue;
					oneProductId = document.getElementById("productId"+form[i].id).innerHTML.trim();

					productId = "productId"+form[i].id
					if(!document.getElementById("productName"+form[i].id)) continue;
					if(document.getElementById("productName"+form[i].id).innerHTML.lentgth == 0) continue;
					oneProduct = document.getElementById("productName"+form[i].id).innerHTML;

					var tmp = selectedContents.indexOf(oneProductId);
					if(tmp != -1 && form[i].checked == false)
					{
						delete selectedContents[tmp];
						delete selectedContentsName[tmp];
					}else if(tmp == -1 && form[i].checked == true){
						selectedContents.push(oneProductId);
						selectedContentsName.push(oneProduct);
					}
				 }				
			}

			for(var i in selectedContents)
			{
				if(selectedContents[i] && !isNaN(i))	selectedProductId += selectedContents[i].trim()+",";
				if(selectedContentsName[i] && !isNaN(i))	selectedProduct += " "+selectedContentsName[i].trim()+" , ";
			}

			window.document.getElementById("productSelected").value = selectedProductId;
			window.document.getElementById("productDisplayed").value = selectedProduct;
		}

		function confirmProductSelection()
		{
			selected = window.document.getElementById("productSelected").value;
			displayed = window.document.getElementById("productDisplayed").value;

			parent.window.document.getElementById("selectedHikaProduct").value = selected.substring(0,selected.length-1);

			parent.window.document.getElementById("displayedHikaProduct").innerHTML = displayed.substring(1,displayed.length-3);
			parent.window.document.getElementById("hiddenHikaProduct").value = displayed.substring(1,displayed.length-3);


			acysms_js.closeBox(true);
		}
	</script>
	<form action="#" method="post" name="adminForm"  id="adminForm"  autocomplete="off">
		<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%" class="adminform">
			<tr>
				<td width="100%">
					<input type="hidden" id="productSelected"/>
					<input type="textbox" size="30" id="productDisplayed" readonly value=""/>
					<input type="button" onclick="confirmProductSelection()" value="<?php echo JText::_( 'SMS_VALIDATE' )?>" />
				</td>
			</tr>
			<tr>
				<td width="100%">
					<input placeholder="<?php echo JText::_('SMS_SEARCH'); ?>" type="text" name="search" id="acysmssearch" value="<?php echo $pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
					<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'SMS_GO' ); ?></button>
					<button class="btn" onclick="document.getElementById('acysmssearch').value='';this.form.submit();"><?php echo JText::_( 'SMS_RESET' ); ?></button>
				</td>
			</tr>
		</table>
		<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
			<thead>
					<th class="title titlebox">
						<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this); addProduct();" />
					</th>
					<th class="title titlename">
						<?php echo JHTML::_('grid.sort', JText::_( 'SMS_NAME'), 'hikashopproduct.product_name', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titledesc">
						<?php echo JHTML::_('grid.sort', JText::_( 'SMS_DESCRIPTION'), 'hikashopproduct.product_description', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titlecode">
						<?php echo JHTML::_('grid.sort',   JText::_( 'SMS_CATEGORY' ), 'hikacategory.category_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort',   JText::_( 'SMS_ID' ), 'hikashopproduct.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>

				<?php
					$k = 0;
					for($i = 0,$a = count($rows);$i<$a;$i++){
						$row = $rows[$i];
				?>
					<tr  class="<?php echo "row$k"; ?>">
						<td align="center">
							<input type="checkbox" value="<?php echo $row->product_id ?>" id="cb<?php echo $i; ?>" onclick="addProduct();">
						</td>
						<td align="center" id="productNamecb<?php echo $i; ?>">
							<?php
								echo $row->product_name;
							?>
						</td>
						<td align="center">
							<?php
								if(!empty($row->product_description)) echo substr(strip_tags($row->product_description,'<br>'),0,200).'...';
							?>
						</td>
						<td align="center">
							<?php
								echo $row->category_name;
							?>
						</td>
						<td align="center" id="productIdcb<?php echo $i; ?>">
							<?php
								echo $row->product_id;
							?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>" />
	</form>
	<?php
	}

	function onACYSMSSelectData_hikashoporder(&$acyquery,$message){
		$db = JFactory::getDBO();
		if(empty($message->message_receiver['standard']['hikashoporders']['product']) && empty($message->message_receiver['standard']['hikashoporders']['category']) && empty($message->message_receiver['standard']['hikashoporders']['status'])) return;

		if(!isset($acyquery->join['hikausers']) && $message->message_receiver_table != 'hikashop') $acyquery->join['hikausers'] = ' LEFT JOIN #__hikashop_user AS hikausers ON hikausers.user_cms_id = joomusers.id ';
		if(!isset($acyquery->join['hikaaddress']) && $message->message_receiver_table != 'hikashop') $acyquery->join['hikaaddress'] = ' LEFT JOIN #__hikashop_address AS hikaaddress ON hikaaddress.address_user_id = hikausers.user_id ';

		$acyquery->join['hikashoporder'] = 'JOIN #__hikashop_order AS hikashoporder ON hikashoporder.order_user_id = hikaaddress.address_user_id';
		$acyquery->join['hikashoporderproduct'] = 'LEFT JOIN #__hikashop_order_product AS hikashoporderproduct ON hikashoporderproduct.order_id =  hikashoporder.order_id';
		$acyquery->join['hikashopproduct'] = 'LEFT JOIN #__hikashop_product AS hikashopproduct ON hikashopproduct.product_id =  hikashoporderproduct.product_id';
		$acyquery->join['hikashopproductcategory'] = 'LEFT JOIN #__hikashop_product_category AS hikashopproductcategory ON hikashopproductcategory.product_id = hikashopproduct.product_id';


		if(!empty($message->message_receiver['standard']['hikashoporders']['product'])){
			$listProduct = $message->message_receiver['standard']['hikashoporders']['product'];
			$listProductExploded = explode(',',$listProduct);
			JArrayHelper::toInteger($listProductExploded);
			$acyquery->where[] = ' hikashopproduct.product_id IN ('.implode(',',$listProductExploded).')';
		}
		if(!empty($message->message_receiver['standard']['hikashoporders']['category']))
			$acyquery->where[] = ' hikashopproductcategory.category_id ='.intval($message->message_receiver['standard']['hikashoporders']['category']);
		if(!empty($message->message_receiver['standard']['hikashoporders']['status']) && !empty($message->message_receiver['standard']['hikashoporders']['status']))
			$acyquery->where[] = ' hikashoporder.order_status = '.$db->Quote($message->message_receiver['standard']['hikashoporders']['status']);
	}




	function onACYSMSGetMessageType(&$types, $integration){
		if($integration == 'acysms') return;
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_AUTO_ORDER_STATUS',JText::_('SMS_HIKASHOP'));
		$types['hikashoporders'] = $newType;
	}


	function onACYSMSdisplayParamsAutoMessage_hikashoporders($message){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load('com_hikashop',JPATH_SITE);
		$timevalue = array();
		$timevalue[] = JHTML::_('select.option','hours',JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option','days',JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option','weeks',JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option','months',JText::_('SMS_MONTHS'));

		$orderStatus[] = JHTML::_('select.option','',JText::_(' - - - '));
		$query = 'SELECT category_name FROM `#__hikashop_category` WHERE `category_type` = "status" AND `category_name` != "order status"';
		$db->setQuery($query);
		$category = $db->loadObjectList();
		foreach($category as $oneCategory){
			if(empty($oneCategory->value)){
				$val = str_replace(' ','_',strtoupper($oneCategory->category_name));
				$oneCategory->value = JText::_($val);
				if($val==$oneCategory->value){
					$oneCategory->value = $oneCategory->category_name;
				}
			}
			$orderStatus[] = JHTML::_('select.option', $oneCategory->category_name, $oneCategory->value);
		}

		$addressType[] = JHTML::_('select.option','billing',JText::_('HIKASHOP_BILLING_ADDRESS'));
		$addressType[] = JHTML::_('select.option','shipping',JText::_('HIKASHOP_SHIPPING_ADDRESS'));

		$receiverType = array();
		$receiverType[] = JHTML::_('select.option','buyer',JText::_('SMS_BUYER'));
		$receiverType[] = JHTML::_('select.option','all',JText::_('SMS_ALL_USERS'));

		$delay =  JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][hikashoporders][delay][timevalue]" , 'size="1" style="width:auto"','value', 'text',  '0');
		$status1 =  JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][hikashoporders][status][status1]" , 'size="1" style="width:auto"','value', 'text',  '0');
		$status2 =  JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][hikashoporders][status][status2]" , 'size="1" style="width:auto"','value', 'text',  '0');

		$address = JHTML::_('select.genericlist', $addressType, "data[message][message_receiver][auto][hikashoporders][address]" , 'size="1" style="width:auto"','value', 'text',  '0');
		$receiver = JHTML::_('select.genericlist', $receiverType, "data[message][message_receiver][auto][hikashoporders][receiver_type]" , 'size="1" style="width:auto"','value', 'text',  '0');
		$integrationName = $message->message_receiver_table;
		$displayedDropDown = ($integrationName =='hikashop') ?  $address : $receiver;

		$queryCategories = 'SELECT category_id, category_name FROM #__hikashop_category WHERE category_type = "product"';
		$db->setQuery($queryCategories);
		$hikaCategories = $db->loadObjectList();

		if(!empty($hikaCategories)){
			$hikaCategoriesOptions = array();
			$hikaCategoriesOptions[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
			foreach($hikaCategories as $oneHikaCategory){
				$hikaCategoriesOptions[] = JHTML::_('select.option', $oneHikaCategory->category_id, $oneHikaCategory->category_name);
			}
			$hikaCategoryDropDown = JHTML::_('select.genericlist', $hikaCategoriesOptions, "data[message][message_receiver][auto][hikashoporders][category]" , 'size="1" style="width:auto"','value', 'text',  '0');
		}

		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][hikashoporders][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_AFTER_ORDER_MODIF',$timeNumber.' '.$delay).'<br />';
		echo str_replace(array('%s','%t'), array($status1,$status2), JText::_('SMS_STATUS_CHANGES')).'<br />';
		echo JText::sprintf('SMS_SENDTO_ADDRESS', $displayedDropDown).'<br />';

		$productName = '';
		if(!empty($message->message_receiver['auto']['hikashoporders']['productName'])) $productName = $message->message_receiver['auto']['hikashoporders']['productName'];

		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedHikaProduct"/>'.$productName.'</span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=cpanel&task=plgtrigger&plg=hikashop&fctName=displayHikaArticles\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" /></a>';
		echo '<input type="hidden" name="data[message][message_receiver][auto][hikashoporders][product]" id="selectedHikaProduct"></input><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][hikashoporders][productName]" id="hiddenHikaProduct"/><br />';
		if(!empty($hikaCategoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$hikaCategoryDropDown;

	}




	function onACYSMSGetTags(&$tags) {
	 	$oneIntegration = ACYSMS::getIntegration('hikashop');
		if(!$oneIntegration->isPresent()) return;

		$db = JFactory::getDBO();

	 	$tags['hikashopUser'] = new stdClass();
		$tags['hikashopUser']->name = JText::sprintf('SMS_X_USER_INFO','HikaShop');

		$tags['hikashopOrder'] = new stdClass();
		$tags['hikashopOrder']->name = JText::sprintf('SMS_X_ORDER_INFO','HikaShop');

		$tableFieldsOrder = acysms_getColumns('#__hikashop_order');
		$tableFieldsUser = acysms_getColumns('#__hikashop_address') ;

		$tags['hikashopUser']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$k = 0;
		foreach($tableFieldsUser as $oneField => $fieldType){
			$tags['hikashopUser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{hikashop:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1-$k;
		}
		$tags['hikashopUser']->content .= '</tbody></table>';

		$tags['hikashopOrder']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$k = 0;
		foreach($tableFieldsOrder as $oneField => $fieldType){
			$tags['hikashopOrder']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{hikashop:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1-$k;
		}
		$tags['hikashopOrder']->content .= '</tbody></table>';



	 	$tags['hikashopCoupon'] = new stdClass();
		$tags['hikashopCoupon']->name = JText::sprintf('SMS_X_COUPON','Hikashop');
		$prefix = 'hika';


		if(empty($field->options['format'])) $field->options['format'] = "%d %m %Y";
		$days = array();
		for($i=1;$i<32;$i++) $days[] = JHTML::_('select.option',(strlen($i) == 1) ? '0'.$i : $i,(strlen($i) == 1) ? '0'.$i : $i);
		$years = array();
		for($i=date('Y');$i<=date('Y')+5;$i++) $years[] = JHTML::_('select.option',$i,$i);
		$months = array();
		$months[] = JHTML::_('select.option','01',JText::_('JANUARY'));
		$months[] = JHTML::_('select.option','02',JText::_('FEBRUARY'));
		$months[] = JHTML::_('select.option','03',JText::_('MARCH'));
		$months[] = JHTML::_('select.option','04',JText::_('APRIL'));
		$months[] = JHTML::_('select.option','05',JText::_('MAY'));
		$months[] = JHTML::_('select.option','06',JText::_('JUNE'));
		$months[] = JHTML::_('select.option','07',JText::_('JULY'));
		$months[] = JHTML::_('select.option','08',JText::_('AUGUST'));
		$months[] = JHTML::_('select.option','09',JText::_('SEPTEMBER'));
		$months[] = JHTML::_('select.option','10',JText::_('OCTOBER'));
		$months[] = JHTML::_('select.option','11',JText::_('NOVEMBER'));
		$months[] = JHTML::_('select.option','12',JText::_('DECEMBER'));
		$dayField = JHTML::_('select.genericlist',   $days, '', 'style="width:50px;" class="inputbox"','value','text', ACYSMS::getDate(time(),'d'), $prefix.'datascheduleddateday');
		$monthField = JHTML::_('select.genericlist', $months, '', 'style="width:100px;" class="inputbox"','value','text',  ACYSMS::getDate(time(),'m'), $prefix.'datascheduleddatemonth');
		$yearField = JHTML::_('select.genericlist',$years, '', 'style="width:70px;" class="inputbox"','value','text',  ACYSMS::getDate(time(),'Y'), $prefix.'datascheduleddateyear');
		$timeField = array($dayField,$monthField, $yearField);

		$value = array();
		$value[0] = JHTML::_('select.option','percent','%');
		$value[1] = JHTML::_('select.option','price',JText::_('DISCOUNT_FLAT_AMOUNT'));
		$listCouponValue = JHTML::_('select.genericlist', $value, $prefix."dropDownPercentPrice","", 'value', 'text','percent', $prefix.'valueType_' );
		$expiry = array();
		$expiry[0] = JHTML::_('select.option','date',JText::_('SMS_FIELD_DATE'));
		$expiry[1] = JHTML::_('select.option','delay',JText::_('SMS_DELAY'));
		$radioListExpiry = JHTML::_('acysmsselect.radiolist', $expiry, $prefix."radioDateDelay", 'onclick="displayTypeOfDelay(\''.$prefix.'\')"', 'value', 'text', 'date', $prefix.'expiryType_' );

		$delay = array();
		$delay[] = JHTML::_('select.option','days',JText::_('SMS_DAYS'));
		$delay[] = JHTML::_('select.option','months',JText::_('SMS_MONTHS'));
		$delay[] = JHTML::_('select.option','years',JText::_('SMS_YEARS'));

		$delayNumber = array();
		for ($i=0;$i<100;$i++)
		{
			$delayNumber[] = JHTML::_('select.option',$i+1,$i+1);
		}
		$displayDelay = array();
		$displayDelay[] = JHTML::_('select.genericlist', $delayNumber, $prefix.'numberdelay',"", 'value', 'text','1', $prefix.'numberdelay' );
		$displayDelay[] = JHTML::_('select.genericlist',$delay, $prefix.'delayLength',"", 'value', 'text','days', $prefix.'delayLength' );

		$tags['hikashopCoupon']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$tags['hikashopCoupon']->content .= '<tr> <th>'.JText::_('HIKASHOP_COUPON').'</th> <td colspan="2"><input id="'.$prefix.'coupon" type="textbox" maxlength="255" value="[key][user]"/> </td>';
		$tags['hikashopCoupon']->content .= '<tr> <th>'.JText::_('VALUE').'</th> <td><input id="'.$prefix.'couponvalue" size="5" type="textbox"/> ';
		$tags['hikashopCoupon']->content .= $listCouponValue.'</td></tr>';
		$tags['hikashopCoupon']->content .= '<tr> <th>'.JText::_('SMS_EXPIRY_DATE').'</th> <td colspan="2">'.$radioListExpiry.'</td> </tr>';
		$tags['hikashopCoupon']->content .= '<tr id="'.$prefix.'expiryDate"> <th>'.JText::_('SMS_FIELD_DATE').'</th> <td colspan="2">'.$timeField[0].$timeField[1].$timeField[2].'</td></tr>';
		$tags['hikashopCoupon']->content .= '<tr id="'.$prefix.'expiryDelay" style="display:none"> <th>'.JText::_('SMS_DELAY').'</th> <td colspan="2">'.$displayDelay[0].$displayDelay[1].'</td></tr>';
		$tags['hikashopCoupon']->content .= '<tr><td colspan="3"> <input type="button" value="'.JText::_('SMS_INSERT_COUPON').'" onclick="createTaghikashopCoupon(\''.$prefix.'\')"/> </td></tr>';
		$tags['hikashopCoupon']->content .= '</tbody></table>';
		?>
		<script language="javascript" type="text/javascript">

		function displayTypeOfDelay(prefix){

			if(document.getElementById(prefix+'expiryType_delay').checked)
			{
				document.getElementById(prefix+'expiryDelay').style.display='table-row';
				document.getElementById(prefix+'expiryDate').style.display='none';
			}
			else if(document.getElementById(prefix+'expiryType_date').checked)
			{
				document.getElementById(prefix+'expiryDate').style.display='table-row';
				document.getElementById(prefix+'expiryDelay').style.display='none';
			}
		}

		function createTaghikashopCoupon(prefix)
		{   //end date of the coupon
			if(document.getElementById(prefix+'expiryType_delay').checked)
			{
				difference = document.getElementById(prefix+'numberdelay').value;
				difference = parseInt(difference);
				typeOfDifference = document.getElementById(prefix+'delayLength').value;
			}
			else if(document.getElementById(prefix+'expiryType_date').checked)
			{
				endDate = new Date;
				var day = document.getElementById(prefix+'datascheduleddateday').value;
				var month = document.getElementById(prefix+'datascheduleddatemonth').value;
				var year = document.getElementById(prefix+'datascheduleddateyear').value;
				endDate.setDate(day);
				endDate.setMonth(month-1);
				endDate.setFullYear(year);
			}
			var typeValue = document.getElementById(prefix+'valueType_').value;

			var couponName  = document.getElementById(prefix+'coupon').value;
			var couponValue = document.getElementById(prefix+'couponvalue').value;

			if(document.getElementById(prefix+'expiryType_delay').checked)
			{
				var finalCoupon = "{hikacoupon:"+couponName+"|value:"+couponValue+"|typevalue:"+typeValue+"|delay:"+difference+"|typeofdelay:"+typeOfDifference+"}";
			}
			else
			{
				var day = endDate.getDate();
				var month = endDate.getMonth()+1;
				var year = endDate.getFullYear();
				if(day<10)
					day = '0'+day.toString();
				else
					day = day.toString();
				if(month<10)
					month = '0'+month.toString();
				else
					month = month.toString();
				var endDate = year.toString()+"-"+month+"-"+day;
				var finalCoupon = "{hikacoupon:"+couponName+"|value:"+couponValue+"|typevalue:"+typeValue+"|expiry:"+endDate+"}";
			}
			insertTag(finalCoupon);
		}

		</script>
		<?php
	 }

	private function _replaceCouponTags($message, $send, $user){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'hikacoupon');

		foreach($tags as $oneTag)
		{
			$key = ACYSMS::generateKey(5);
			$couponName = $oneTag->id;
			$couponValue = $oneTag->value;
			if($oneTag->typevalue == "percent")
				$couponTypeValue  = 'discount_percent_amount';
			else
				$couponTypeValue  = 'discount_flat_amount';
			if(isset($oneTag->expiry))
				$couponExpiry  =  $oneTag->expiry;
			else
			{
				switch($oneTag->typeofdelay)
				{
					case 'days':
						$couponExpiry = date("Y-m-d",mktime(0,0,0,date("m"),date("d")+$oneTag->delay,date("Y")));
						break;
					case 'months':
						$couponExpiry = date("Y-m-d",mktime(0,0,0,date("m")+$oneTag->delay,date("d"),date("Y")));
						break;
					case 'years':
						$couponExpiry = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")+$oneTag->delay));
				}
			}
			if(!empty($couponName))
			{
				$couponName = str_replace("[key]",$key,$couponName);
				if (!empty($user))
					$userName = str_replace(' ','',$user->receiver_name);
				else
					$userName = JFactory::getUser()->name;
				$couponName = str_replace("[user]",$userName,$couponName);

				$couponName = substr($couponName,0,255);

				$message->message_body = str_replace(array_search($oneTag,$tags),$couponName,$message->message_body);
				if($send)
				{
					$db = JFactory::getDBO();
					$query = "INSERT INTO #__hikashop_discount (discount_code,discount_type,discount_start,discount_end,$couponTypeValue,discount_quota,discount_used_times,discount_published)
					VALUES(".$db->Quote($couponName).",'coupon',UNIX_TIMESTAMP(),".intval(strtotime($couponExpiry)).",".intval($couponValue).",1,0,1);";
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}


	 function onACYSMSReplaceUserTags(&$message,&$user,$send = true){
	 	$this->_replaceCouponTags($message,$send,$user); //we replace coupon tags by real value

		$config = ACYSMS::config();
	 	$db = JFactory::getDBO();

	 	$match = '#(?:{|%7B)hikashop:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match,$message->message_body,$results)) return;
		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$address = new stdClass();
		if(!isset($user->hikashop) && !empty($user->queue_paramqueue) && !empty($user->queue_paramqueue->address_id)){
			$query = 'SELECT * FROM #__hikashop_address WHERE address_id = '.intval($user->queue_paramqueue->address_id);
			$db->setQuery($query);
			$address = $db->loadObject();
		}
		else if(!isset($user->hikashop) && isset($user->joomla->id)){
			$query = 'SELECT hikaaddress.*
						FROM #__hikashop_address as hikaaddress
						JOIN #__hikashop_user as hikausers ON hikausers.user_id = hikaaddress.address_user_id
						WHERE user_cms_id = '.intval($user->joomla->id).'
						ORDER BY address_default ASC
						LIMIT 1';

						$db->setQuery($query);
						$address = $db->loadObject();
		}elseif(isset($user->hikashop)){
			$address = $user->hikashop;
		}


		if(empty($user->hikashop->order_id) && !empty($user->queue_paramqueue->order_id) && !empty($user->queue_paramqueue->order_id)){
			$query = 'SELECT * FROM #__hikashop_order WHERE order_id = '.intval($user->queue_paramqueue->order_id);
			$db->setQuery($query);
			$orders = $db->loadObject();
			$address = (object) array_merge((array) $address, (array) $orders);
		}
		else if($integration->componentName == 'hikashop' && !empty($user->hikashop)){
			$query = 'SELECT * FROM #__hikashop_order WHERE order_user_id = '.intval($user->hikashop->user_id).' ORDER BY order_id LIMIT 1';
			$db->setQuery($query);
			$orders = $db->loadObject();
			$address = (object) array_merge((array) $address, (array) $orders);
		}
		else if(!empty($user->joomla->id)){
			$query = 'SELECT * FROM #__hikashop_order JOIN #__hikashop_user ON user_id = order_user_id WHERE user_cms_id = '.intval($user->joomla->id);
			$db->setQuery($query);
			$orders = $db->loadObject();
			$address = (object) array_merge((array) $address, (array) $orders);
		}

		$tags = array();
		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$arguments = explode('|',strip_tags($results[1][$i]));
			$field = $arguments[0];
			unset($arguments[0]);
			$mytag = new stdClass();
			$mytag->default = '';
			if(!empty($arguments)){
				foreach($arguments as $onearg){
					$args = explode(':',$onearg);
					if(isset($args[1])){
						$mytag->$args[0] = $args[1];
					}else{
						$mytag->$args[0] = 1;
					}
				}
			}
			$tags[$oneTag] = (isset($address->$field) && strlen($address->$field) > 0) ? $address->$field : $mytag->default;
		}
		$message->message_body = str_replace(array_keys($tags),$tags,$message->message_body);
	}



	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_ORDER','HikaShop');
		$authorizedFilters['hikashoporder'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_rseventspro(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}
}//endclass
