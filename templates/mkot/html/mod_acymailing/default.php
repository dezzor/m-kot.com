<div id="subscription">
  <form id="<?php echo $formName; ?>" action="<?php echo JRoute::_('index.php'); ?>" onsubmit="return submitacymailingform('optin','<?php echo $formName;?>')" method="post" name="<?php echo $formName ?>" <?php if(!empty($fieldsClass->formoption)) echo $fieldsClass->formoption; ?> >
    <p class="subscription-yellow"><?php echo JText::_('SL_YELLOW'); ?></p>
    <p class="subscription-white"><?php echo JText::_('SL_WHAIT'); ?></p>
    <div class="form-group">
      <input id="user_name_<?php echo $formName; ?>" <?php if(!empty($identifiedUser->userid)) echo 'disabled="disabled" ';?> placeholder="<?php echo JText::_('NAMECAPTION'); ?>" class="form-control" type="text" name="user[name]" style="width:<?php echo $fieldsize; ?>" value="<?php if(!empty($identifiedUser->userid)) echo $identifiedUser->name; ?>" />
    </div>
    <div class="form-group">
      <input id="user_email_<?php echo $formName; ?>" <?php if(!empty($identifiedUser->userid)) echo 'disabled="disabled" ';?>  placeholder="Email" class="form-control" type="text" name="user[email]" style="width:<?php echo $fieldsize; ?>" value="<?php if(!empty($identifiedUser->userid)) echo $identifiedUser->email; ?>" />
    </div>
    <button type="submit" class="btn btn-success"><?php echo JText::_('SUBSCRIBECAPTION'); ?></button>
    <?$ajax = ($params->get('redirectmode') == '3') ? 1 : 0;?>
    <input type="hidden" name="ajax" value="<?php echo $ajax; ?>"/>
    <input type="hidden" name="ctrl" value="sub"/>
    <input type="hidden" name="task" value="notask"/>
    <input type="hidden" name="redirect" value="<?php echo urlencode($redirectUrl); ?>"/>
    <input type="hidden" name="redirectunsub" value="<?php echo urlencode($redirectUrlUnsub); ?>"/>
    <input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT ?>"/>
    <?php if(!empty($identifiedUser->userid)){ ?><input type="hidden" name="visiblelists" value="<?php echo $visibleLists;?>"/><?php } ?>
    <input type="hidden" name="hiddenlists" value="<?php echo $hiddenLists;?>"/>
    <input type="hidden" name="acyformname" value="<?php echo $formName; ?>" />
    <?php if(JRequest::getCmd('tmpl') == 'component'){ ?>
      <input type="hidden" name="tmpl" value="component" />
      <?php if($params->get('effect','normal') == 'mootools-box' AND !empty($redirectUrl)){ ?>
        <input type="hidden" name="closepop" value="1" />
    <?php } } ?>
    <?php $myItemId = $config->get('itemid',0); if(empty($myItemId)){ global $Itemid; $myItemId = $Itemid;} if(!empty($myItemId)){ ?><input type="hidden" name="Itemid" value="<?php echo $myItemId;?>"/><?php } ?>
  </form>
</div>