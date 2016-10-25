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


class categoryViewcategory extends acysmsView
{
	var $ctrl = 'category';

	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){

		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();



		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order', 'a.category_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		if($pageInfo->filter->order->dir != "asc")	$pageInfo->filter->order->dir = 'desc';

		$searchMap = array('a.category_id','a.category_name','a.category_ordering');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}




		$query = 'SELECT '.implode(',',$searchMap).' FROM '.ACYSMS::table('category').' as a';
		if(!empty($filters))	$query.= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$queryCount = 'SELECT COUNT(a.category_id) FROM '.ACYSMS::table('category').' as a';
		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);



		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		ACYSMS::setTitle(JText::_('SMS_CATEGORIES'),'smscategories',$this->ctrl);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		if(ACYSMS::isAllowed($config->get('acl_categories_delete','all'))) JToolBarHelper::deleteList(JText::_('SMS_VALIDDELETEITEMS'));
		JToolBarHelper::spacer();
		if(ACYSMS::isAllowed($config->get('acl_categories_copy','all')))	JToolBarHelper::custom( 'copy', 'copy.png', 'copy.png', JText::_('SMS_COPY') );
		JToolBarHelper::divider();

		$bar->appendButton( 'Pophelp', 'categories');
		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage','all')))	$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );
		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.category_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		$this->assignRef('rows',$rows);
		$this->assignRef('order',$order);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('config',$config);

	}

	function form(){
		$categoryid = ACYSMS::getCID('category_id');
		$config = ACYSMS::config();

		if(!empty($categoryid)){
			$categoryClass = ACYSMS::get('class.category');
			$category = $categoryClass->get($categoryid);
		}else{
			$category = new stdClass();
			$category->name = '';
		}

		ACYSMS::setTitle(JText::_('SMS_CATEGORY'),'smscategories',$this->ctrl.'&task=edit&category_id='.$categoryid);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','categories');

		$tabs = ACYSMS::get('helper.tabs');
		$tabs->setOptions(array('useCookie' => true));
		$this->assignRef('tabs',$tabs);

		if(version_compare(JVERSION,'1.6.0','<')){
			$script = 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$script = 'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton == \'cancel\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$script .= 'if(window.document.getElementById("category_name").value.length < 2){alert(\''.JText::_('SMS_ENTER_NAME',true).'\'); return false;}';
		if(version_compare(JVERSION,'1.6.0','<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $script );
		$this->assignRef('category',$category);
		$this->assignRef('config',$config);

	}
}
