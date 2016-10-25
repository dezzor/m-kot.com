<?php 
/*
 * ------------------------------------------------------------------------
 * Copyright (C) 2009 - 2013 The YouTech JSC. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: The YouTech JSC
 * Websites: http://www.smartaddons.com - http://www.cmsportal.net
 * ------------------------------------------------------------------------
*/
 
 // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

Class viewActionRow{
	static $arrayAction = array("1" => "delete",
								"2" => "copy",
	                            "3" => "addElements",	
						        );
	private static function delete(){
		$html = '<span class="sj_pageBuild_remove_row"></span>';
		return $html;
	}
	
	private static function deleteJs(){
		$js = '$("body").on("click",".sj_pageBuild_remove_row",function(){
					$(this).parent().parent().parent().parent().parent().parent().remove();
					check_wrap_sj_add_row = 0;
			  });';
		return $js;
	}
	
	private static function addElements(){
		$html = '<span class="sj_pageBuild_add_element_row sj_icon_row"></span>';
		return $html;
	}
	
	private static function addElementsJs(){		
		return '';
	}
	
	private static function copy(){
		$html = '<span class="sj_pageBuild_copy_row sj_icon_row"></span>';
		return $html;
	}
	
	private static function copyJs(){
		$js = '$("body").on("click",".sj_pageBuild_copy_row",function(){
					var html = $(this).parent().parent().parent().parent().parent().parent().html();
					var parent = $(this).parent().parent().parent().parent().parent().parent();
					parent.after(\'<div class="row-fluid">\'+html+\'</div>\');
			  });';
		return $js;
	}
	
	public static function display(){
		$html = '<span class="sj_pageBuild_wrap_action_row">';
			foreach(self::$arrayAction as $action){
				$html = $html . self::$action();
			}
		$html = $html.'</span>';
		return $html;
	}
	
	public static function displayJs(){
		$html = '';
		foreach(self::$arrayAction as $action){
			$action = $action.'Js';
			$html = $html . self::$action();				
		}
		return $html;
	}
}
?>