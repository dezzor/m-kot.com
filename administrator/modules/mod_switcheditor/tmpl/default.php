<?php
/**
 * @package    Switch Editor
 * @subpackage mod_switcheditor
 * @copyright  Copyright (C) 2012 Anything Digital. All rights reserved.
 * @copyright  Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
 * @license    GNU/GPLv2
 */

// no direct access
defined('_JEXEC') or die;

?>
<div class="adEditorFormBox btn-group">
	<form action="index.php?option=switcheditor" method="post" name="adEditorForm">
		<?php echo str_replace(' id="adEditor"', '', JHtml::_('select.genericlist', $options, 'adEditor', ' class="adEditor chzn-done" data-chosen="done"', 'element', 'name', $value)); ?>
		<input type="hidden" name="task" value="switch" />
	</form>
</div>
