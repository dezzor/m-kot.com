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

class ACYSMStoggleHelper{

	var $ctrl = 'toggle';
	var $extra = '';

	function _getToggle($column,$table = ''){
		$params = new stdClass();
		$params->mode = 'pictures';
		if($column == 'published' AND !in_array($table,array('plugins','list'))){
			if(!ACYSMS_J16){
				$params->pictures = array(0=>'images/publish_r.png',1=>'images/publish_g.png',2=>ACYSMS_IMAGES.'schedule.png');
			}elseif(!ACYSMS_J30){
				$params->aclass = array(0=>'grid_false',1=>'grid_true',2=>'acyschedule');
			} else {
				$params->aclass = array(0=>'icon-unpublish',1=>'icon-publish',2=>'acyschedule');
			}
			$params->description = array(0=>JText::_('SMS_PUBLISH_CLICK',true),1=>JText::_('SMS_UNPUBLISH_CLICK',true),2=>JText::_('SMS_UNSCHEDULE_CLICK',true));
			$params->values = array(0=>1,1=>0,2=>0);
			return $params;
		}else{
			if(!ACYSMS_J16){
				$params->pictures = array(0=>'images/publish_x.png',1=>'images/tick.png');
			}elseif(!ACYSMS_J30){
				$params->aclass = array(0=>'grid_false',1=>'grid_true');
			} else {
				$params->aclass = array(0=>'icon-unpublish',1=>'icon-publish');
			}
		}

		$params->values = array(0=>1,1=>0);
		return $params;
	}


	function toggle($id,$value,$table,$extra = null){
		$column = substr($id,0,strrpos($id,'_'));
		$params = $this->_getToggle($column,$table);
		$newValue = $params->values[$value];
		if($params->mode == 'pictures'){
			static $pictureincluded = false;
			if(!$pictureincluded){
				$pictureincluded = true;
				$js = "function joomTogglePicture(id,newvalue,table){
					window.document.getElementById(id).className = 'onload';
					try{
						new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&".JSession::getFormToken()."=1',{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&".JSession::getFormToken()."=1',method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
				}";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}

			$desc = empty($params->description[$value]) ? '' : $params->description[$value];

			if(empty($params->pictures)){
				$text = ' ';
				$class='class="'.$params->aclass[$value].'"';
			}else{
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}

			return '<a href="javascript:void(-1);" '.$class.' onclick="joomTogglePicture(\''.$id.'\',\''.$newValue.'\',\''.$table.'\')" title="'.$desc.'">'.$text.'</a>';
		}elseif($params->mode == 'class'){
			static $classincluded = false;
			if(!$classincluded){
				$classincluded = true;
				$js = "function joomToggleClass(id,newvalue,table,extra){
					var mydiv=$(id); mydiv.innerHTML = ''; mydiv.className = 'onload';
					try{
						new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
					}";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a href="javascript:void(-1);" onclick="joomToggleClass(\''.$id.'\',\''.$newValue.'\',\''.$table.'\',\''.urlencode($extra['color']).'\');" title="'.$desc.'"><div class="'. $params->class[$value] .'" style="background-color:'.$extra['color'].'">';
			if(!empty($extra['tooltip'])) $return .= com_acysms_tooltip($extra['tooltip'], @$extra['tooltiptitle'],'','&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';

			return $return;
		}
	}

	function delete($lineId,$elementids,$table,$confirm = false,$text = ''){
		static $deleteJS = false;
		if(!$deleteJS){
			$deleteJS = true;
			$js = "function joomDelete(lineid,elementids,table,reqconfirm){
				if(reqconfirm){
					if(!confirm('".JText::_('SMS_VALIDDELETEITEMS',true)."')) return false;
				}
				try{
					new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl.$this->extra."&task=delete&value='+elementids+'&table='+table, { method: 'get', onComplete: function() {window.document.getElementById(lineid).style.display = 'none';}}).request();
				}catch(err){
					new Request({url:'index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl.$this->extra."&task=delete&value='+elementids+'&table='+table,method: 'get', onComplete: function() { window.document.getElementById(lineid).style.display = 'none'; }}).send();
				}
			}";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
		if(empty($text)) $text = '<img src="'.ACYSMS_IMAGES.'delete.png"/>';
		return '<a href="javascript:void(0);" onclick="joomDelete(\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','. ($confirm ? 'true' : 'false').')">'.$text.'</a>';
	}

	function display($column,$value){
		$params = $this->_getToggle($column);

		if(empty($params->pictures)){
			return '<a style="cursor:default;" class="'.$params->aclass[$value].'"> </a>';
		}else{
			return '<img src="'.$params->pictures[$value].'"/>';
		}
	}

	function callFunction(){

		acysms_loadMootools();

		$script = "function callFunction(url,updatedAreaId){
				document.getElementById(updatedAreaId).innerHTML = '<span class=\"onload\"></span>';
				try{
					new Ajax('index.php?option=com_acysms&ctrl=toggle&task=plgtrigger&'+url,{ method: 'post', update: document.getElementById(updatedAreaId)}).request();
				}catch(err){
					new Request({
					method: 'post',
					url: 'index.php?option=com_acysms&ctrl=toggle&task=plgtrigger&'+url,
					onSuccess: function(responseText, responseXML) {
						document.getElementById(updatedAreaId).innerHTML = responseText;
					}
					}).send();
				}
		}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}

	function toggleText($action= '',$value='',$table='',$url='',$text=''){
		static $jsincluded = false;
		static $id = 0;
		$id++;
		if(!$jsincluded){
			$jsincluded = true;
			$js = "function joomToggleText(id,newvalue,table,url){
				window.document.getElementById(id+'_'+newvalue).className = 'onload';
				try{
					new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+url,{ method: 'get', update: $(id+'_'+newvalue), onComplete: function() {	window.document.getElementById(id+'_'+newvalue).className = 'loading'; }}).request();
				}catch(err){
					new Request({url:'index.php?option=com_acysms&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+url,method: 'get', onComplete: function(response) { $(id+'_'+newvalue).innerHTML = response; window.document.getElementById(id+'_'+newvalue).className = 'loading'; }}).send();
				}
			}";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}

		if(!$action) return;

		return '<span id="'.$action.'_'.$value.'" ><a href="javascript:void(0);" onclick="joomToggleText(\''.$action.'\',\''.$value.'\',\''.$table.'\',\''.$url.'\')">'.$text.'</a></span>';
	}

}
