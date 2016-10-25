/**
 * @version    $Id$
 * @package    Switch Editor
 * @subpackage mod_switcheditor
 * @copyright  Copyright (C) 2012 Anything Digital. All rights reserved.
 * @copyright  Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
 * @license    GNU/GPLv2
 */

(function($){
	$(document).ready(function(){
		$('.adEditor').change(function(ev){
			var t = $(this), f = t.closest('form');
			$.post(f.attr('action'), f.serialize());
		});
	});
})(jQuery);