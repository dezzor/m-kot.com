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
<style type="text/css">
#acysmscpanel div.icon {
	border:1px solid #F0F0F0;
	color:#666666;
	display:block;
	float:left;
	text-decoration:none;
	vertical-align:middle;
	width:100%;
	float:left;
	margin-bottom:5px;
	margin-right:5px;
	text-align:center;
	width: 100%;
}
#acysmscpanel ul{
	padding-left:10px;
}
#acysmscpanel div.icon:hover {
	-moz-background-clip:border;
	-moz-background-inline-policy:continuous;
	-moz-background-origin:padding;
	background:#F9F9F9 none repeat scroll 0 0;
	border-color:#EEEEEE #CCCCCC #CCCCCC #EEEEEE;
	border-style:solid;
	border-width:1px;
	color:#0B55C4;
}
#acysmscpanel span {
	display:block;
	text-align:center;
}
#acysmscpanel img {
	margin:0 auto;
	padding:10px 0;
}

#acysmscpanel{
	padding-right:10px;
}
</style>
<div id="iframedoc"></div>
<table class="adminform" width="100%">
	<tr>
		<td width="45%" valign="top">
			<div id="acysmscpanel">
				<?php
					foreach($this->buttons as $oneButton){
						echo $oneButton;
					}
					?>
			</div>
		</td>
		<td valign="top">
			<?php
				echo $this->tabs->startPane( 'dash_tab');
				if(ACYSMS::isAllowed($this->config->get('acl_receivers_manage','all'))){
					echo $this->tabs->startPanel( JText::_( 'SMS_USERS' ), 'dash_users');
					include(dirname(__FILE__).DS.'users.php');
					echo $this->tabs->endPanel();
				}
				echo $this->tabs->endPane();
			?>
		</td>
	</tr>
</table>
</div>
