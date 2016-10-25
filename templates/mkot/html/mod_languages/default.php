<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('stylesheet', 'mod_languages/template.css', array(), true);

if ($params->get('dropdown', 1) && !$params->get('dropdownimage', 0))
{
	JHtml::_('formbehavior.chosen');
}
?>

<?foreach ($list as $language):?>
    <?if($language->lang_code == 'ru-RU'):?>
        <div class="col-md-6"><a href="<?php echo $language->link; ?>" class="lang_ru"></a></div>
    <?else:?>
        <div class="col-md-6"><a href="<?php echo $language->link; ?>" class="lang_en"></a></div>
    <?endif;?>
<?endforeach?>

