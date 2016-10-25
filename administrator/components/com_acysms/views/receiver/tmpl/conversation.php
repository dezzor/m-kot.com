<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script>
(function(){

var progressSupport = ('onprogress' in new Browser.Request());

Request.File = new Class({

		Extends: Request,

		options: {
				emulation: false,
				urlEncoded: false
		},

		initialize: function(options){
				this.xhr = new Browser.Request();
				this.formData = new FormData();
				this.setOptions(options);
				this.headers = this.options.headers;
		},

		append: function(key, value){
				this.formData.append(key, value);
				return this.formData;
		},

		reset: function(){
				this.formData = new FormData();
		},

		send: function(options){
				if (!this.check(options)) return this;

				this.options.isSuccess = this.options.isSuccess || this.isSuccess;
				this.running = true;

				var xhr = this.xhr;
				if (progressSupport){
						xhr.onloadstart = this.loadstart.bind(this);
						xhr.onprogress = this.progress.bind(this);
						xhr.upload.onprogress = this.progress.bind(this);
				}

				xhr.open('POST', this.options.url, true);
				xhr.onreadystatechange = this.onStateChange.bind(this);

				Object.each(this.headers, function(value, key){
						try {
								xhr.setRequestHeader(key, value);
						} catch (e){
								this.fireEvent('exception', [key, value]);
						}
				}, this);

				this.fireEvent('request');
				xhr.send(this.formData);

				if (!this.options.async) this.onStateChange();
				if (this.options.timeout) this.timer = this.timeout.delay(this.options.timeout, this);
				return this;
		}

});

})();


var idSelected = new Array();

function addNewReceiver(currentValue){
	try{
		var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=getReceiversByName&nameSearched='+currentValue,{
			method: 'get',
			onComplete: function(responseText, responseXML) {
				document.getElementById('acysms_divSelectReceiver').style.display = "block";
				document.getElementById('acysms_receiversTable').innerHTML = responseText;
				receiversList = document.getElementById('acysms_receiversTable');
				if(receiversList.getElementsByClassName("row_user").length==0) {
					document.getElementById("acysms_divSelectReceiver").style.display= "none";
				}
			}
		}).request();

	}catch(err){
		new Request({
			url:'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=getReceiversByName&nameSearched='+currentValue,
			method: 'get',
			onSuccess: function(responseText, responseXML) {
				document.getElementById('acysms_divSelectReceiver').style.display = "block";
				document.getElementById('acysms_receiversTable').innerHTML = responseText;
				receiversList = document.getElementById('acysms_receiversTable');
				if(receiversList.getElementsByClassName("row_user").length==0) {
					document.getElementById("acysms_divSelectReceiver").style.display= "none";
				}
			}
		}).send();
	}
}

function setUser(userName, receiverId){
	document.getElementById('usersSelected').innerHTML += '<span class="selectedUsers">'+userName+'<span class="removeUser" onclick="removeUser(this, '+receiverId+');"></span></span>';
	document.getElementById('message_receivers').value = '';
	document.getElementById('acysms_divSelectReceiver').style.display = "none";

	idSelected.push(parseInt(receiverId));

	loadConversation();
}


function removeUser(element, receiverId){
	element.parentElement.remove();
	var index = idSelected.indexOf(receiverId);
	if (index > -1) {
		idSelected.splice(index, 1);
	}

	loadConversation();
}

function loadConversation(){
	receiverid = idSelected.join('-');
	try{
		var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=conversation&receiverid='+receiverid+'&tmpl=component&isAjax=1',{
			method: 'get',
			update: document.getElementById('sms_conversation')
		}).request();

	}catch(err){
		new Request({
			url:'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=conversation&receiverid='+receiverid+'&tmpl=component&isAjax=1',
			method: 'get',
			onSuccess: function(responseText, responseXML) {
				document.getElementById('sms_conversation').innerHTML = responseText;
			}
		}).send();
	}
}

function sendOneShotSMS(){
	receiverid = idSelected.join('-');
	senderProfile = document.getElementById('senderProfile_id').value;
	messageBody = document.getElementById('message_body').value;
	fileInputs = document.getElementsByClassName('importfile');
	document.getElementById('sendOneShotSMSButton').innerHTML = "<span id=\"ajaxSpan\" class=\"onload\"></span>";
	var request = new Request.File({
			url: 'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=sendOneShotSMS&receiverIds='+receiverid+'&isAjax=1&senderProfile_id='+senderProfile+'&messageBody='+messageBody,
			onSuccess: function(responseText, responseXML) {
				document.getElementById('message_body').value = '';
				document.getElementById('acysms_errors').innerHTML = responseText;
				document.getElementById('sendOneShotSMSButton').innerHTML = '<button class="btn" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND')?></span></button>';
				loadConversation();
			}
	});

	for(i=0; i<fileInputs.length; i++) {
		if(fileInputs[i].files.length>0) {
			request.append("importfile[]",fileInputs[i].files[0]);
		}
	}

	request.send();
}

</script>

<div id="acysms_errors"></div>
<div id="acysms_content" >
<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm"  id="adminForm" >
	<table class="adminform" width="100%">
		<tr>
			<td class="key" id="subjectkey">
				<label for="message_subject">
					<?php echo JText::_( 'SMS_RECEIVERS' ); ?>
				</label>
			</td>
			<td id="subjectinput">
			<div id="userSelection">
				<span id="usersSelected"></span>
				<input type="text" id="message_receivers" onkeyup="addNewReceiver(this.value)" class="inputbox" style="width:100%" value="<?php echo $this->escape(@$this->message->message_receivers); ?>" autocomplete="off"/>
				<div id="acysms_divSelectReceiver" style="display:none; overflow-y:scroll !important;">
					<div id="acysms_receiversTable"></div>
				</div>
			</div>
			</td>
		</tr>
		<tr>
			<td class="key" id="senderkey">
				<label for="status">
					<?php echo JText::_( 'SMS_SENDER_PROFILE' ); ?>
				</label>
			</td>
			<td>
				<?php $this->senderprofile->includeJS = true;
				echo $this->senderprofile->display('senderProfile_id',@$this->message->message_senderprofile_id); ?>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td valign="top">
				<div id="sms_global">
					<?php
						$countType = ACYSMS::get('type.countcharacters');
						echo $countType->countCaracters('message_body','');
					?>
					<div id="sms_body">
						<div id="sms_conversation" class="conversation">
							<?php echo $this->conversation; ?>
						</div>
						<div id="answerArea">
							<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?> onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="messageBody" id="message_body" ><?php echo $this->escape(@$this->message->message_body); ?></textarea>
							<span id="sendOneShotSMSButton">
								<button class="btn" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND')?></span></button>
							</span>
							<?php $phoneType = ACYSMS::get('helper.phone'); echo $phoneType->displayMMS($this,true); ?>
						</div>
					</div>
					<div id="sms_bottom"></div>
				</div>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="task" value="" />
	<?php 	echo JHTML::_( 'form.token' ); ?>
<div/>
