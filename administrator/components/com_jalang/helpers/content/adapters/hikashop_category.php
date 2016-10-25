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

if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_hikashop/classes/category.php')) {
	//Register if Hikashop is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'hikashop_category',
		4,
		JText::_('HIKASHOP_CATEGORY'),
		JText::_('HIKASHOP_CATEGORY')
	);

	//require_once( JPATH_ADMINISTRATOR . '/components/com_k2/models/categories.php' );

	class JalangHelperContentHikashopCategory extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table = 'hikashop_category';
			$this->edit_context = 'com_hikashop.edit.category';
			$this->associate_context = 'com_hikashop.category';
			$this->translate_fields = array('category_name', 'category_description');
			$this->translate_filters = array('category_id <> 1 AND category_type = "product" AND category_namekey NOT LIKE "%jalang"');
			$this->alias_field = 'category_alias';
			$this->nested_field = 'category_parent_id';
			$this->nested_value = 1;
			$this->title_field = 'category_name';
			$this->primarykey = 'category_id';
			$this->table_type = 'table_alone';
			$this->unique_field = 'category_namekey';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_hikashop&ctrl=category&task=edit&cid[]='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.category_name' => JText::_('JGLOBAL_TITLE'),
				'a.category_access' => JText::_('JGRID_HEADING_ACCESS'),
				'a.category_id' => JText::_('JGRID_HEADING_ID')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.category_id' => 'JGRID_HEADING_ID',
				'a.category_name' => 'JGLOBAL_TITLE'
			);
		}
		
		// sourceid is the default item id.
		public function afterSave(&$translator, $sourceid, &$row) {
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' ', 'file_ref_id', $row['category_id'], 'AND file_type="category"');
			$obj = (object)$row;
			if (!empty($translator->aAssociation['hikashop_category'][$row['category_parent_id']][$translator->toLangTag])) {
				$obj->category_parent_id = $translator->aAssociation['hikashop_category'][$row['category_parent_id']][$translator->toLangTag];
				JFactory::getDbo()->updateObject('#__hikashop_category', $obj, 'category_id');
			}
		}

		// sourceid is the default item id.
		public function afterRetranslate(&$translator, $sourceid, &$row) {
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' AND file_type="category"', 'file_ref_id', $val, 'AND file_type="category"');
			$val = $translator->aAssociation['hikashop_category'][$sourceid][$translator->toLangTag];
			$row['category_id'] = $val;
			$obj = (object)$row;
			if (!empty($translator->aAssociation['hikashop_category'][$row['category_parent_id']][$translator->toLangTag])) {
				$obj->category_parent_id = $translator->aAssociation['hikashop_category'][$row['category_parent_id']][$translator->toLangTag];
				JFactory::getDbo()->updateObject('#__hikashop_category', $obj, 'category_id');
			}
		}
		
		// transfer file and price. table that not depend on relationship.
		public function transferData($table, $where, $ref_field, $value, $and='') {
			$db = JFactory::getDbo();
			$delete = 'DELETE FROM #__hikashop_'.$table.' WHERE '.$ref_field.' = '.$value.' '.$and;
			$db->setQuery($delete);
			$db->execute();
			
			$sql = 'SELECT * FROM #__hikashop_'.$table.' '.$where.' '.$and;
			$db->setQuery($sql);
			$results = $db->loadObjectList();
			if (!empty($results)) {
				foreach ($results AS $r) {
					$obj = $r;
					$obj->{$table.'_id'}=NULL;
					$obj->$ref_field = $value;
					JFactory::getDbo()->insertObject('#__hikashop_'.$table.'', $obj);
				}
			}
		}
		
	}
}
