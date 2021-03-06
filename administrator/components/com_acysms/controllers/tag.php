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

class TagController extends acysmsController{

	var $aclCat = 'tags';

	function __construct($config = array())
	{
		parent::__construct($config);
		JHTML::_('behavior.tooltip');
		JRequest::setVar('tmpl','component');

		$this->registerDefaultTask('tag');
	}

	function tag(){
		JRequest::setVar( 'layout', 'tag');
		return parent::display();
	}
}
