<?php
/**
 * ------------------------------------------------------------------------
 * JA Multilingual J2x-J3x.
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/category.php')) {
	//Register if K2 is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'virtuemart_manufacturercategories',
		3,
		JText::_('VIRTUEMART_MANUFACTURER_CATEGORY'),
		JText::_('VIRTUEMART_MANUFACTURER_CATEGORY')
	);


	class JalangHelperContentVirtuemartManufacturercategories extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table_type = 'table';
			$this->table = 'virtuemart_manufacturercategories';
			$this->primarykey = 'virtuemart_manufacturercategories_id';
			$this->edit_context = 'virtuemart.edit.manufacturercategories';
			$this->associate_context = 'virtuemart.manufacturercategories';
			$this->translate_fields = array('mf_category_name', 'mf_category_desc');
			$this->translate_filters = array();
			$this->alias_field = '';
			$this->title_field = 'mf_category_name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_virtuemart&view=manufacturercategories&task=edit&virtuemart_manufacturercategories_id='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.mf_category_name' => JText::_('JGLOBAL_TITLE')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.virtuemart_manufacturercategories_id' => 'JGRID_HEADING_ID',
				'a.mf_category_name' => 'JGLOBAL_TITLE'
			);
		}
	}
}
