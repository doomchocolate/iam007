<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

$browser = JBrowser::getInstance();

if (isset($this->error)) : ?>
    <div class="contact-error">
        <?php echo $this->error; ?>
    </div>
<?php endif; ?>

<div class="contact-form">
<form id="contact-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate form-horizontal">
<fieldset>
<!--			<legend>--><?php //echo JText::_('COM_CONTACT_FORM_LABEL'); ?><!--</legend>-->
<div class="control-group">
<!--    <div class="controls">-->
        <?php
        $contact_name   = $this -> form -> getInput('contact_name');
        if(!preg_match('/placeholder=\".*?\"/mi',$contact_name)){
            $contact_name   = preg_replace('/(\<input)(.*?)/mi',
                '$1 placeholder="'.JText::_('TPL_TZ_WEISS_CONTACT_NAME').'" $2',$contact_name);
        }

        if($browser -> getBrowser() == 'msie'){
            if(!preg_match('/onblur=".*?"/mi',$contact_name)){
                $contact_name   = preg_replace('/(\<input)(.*?)/mi',
                    '$1 onblur="if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_NAME').'\';" $2',$contact_name);
            }else{
                $contact_name   = preg_replace('/(\<input.*?onblur=")(.*?)(".*?\/>)/msi',
                    '$1 if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_NAME').'\'; $3',$contact_name);
            }
            if(!preg_match('/onfocus=".*?"/mi',$contact_name)){
                $contact_name   = preg_replace('/(<input)(.*?\/>)/msi','$1 onfocus="if(this.value.trim() == \''
                    .JText::_('TPL_TZ_WEISS_CONTACT_NAME').'\') this.value=\'\';" $2',$contact_name);
            }else{
                $contact_name   = preg_replace('/(<input.*?onfocus=")(.*?)(".*?\/>)/msi',
                    '$1 if(this.value.trim() == '.JText::_('TPL_TZ_WEISS_CONTACT_NAME')
                    .') this.value=\'\';" $3',$contact_name);
            }
            if(!preg_match('/value=".*?"/mi',$contact_name)){
                $contact_name   = preg_replace('/(<input)(.*?\/>)/msi','$1 value="'
                    .JText::_('TPL_TZ_WEISS_CONTACT_NAME').'" $2',$contact_name);
            }else{
                if(preg_match('/(<input.*?value=")(.*?)(".*?\/>)/msi',$contact_name,$match_name)){
                    if(isset($match_name[2]) && !strlen(trim($match_name[2]))){
                        $contact_name   = preg_replace('/(<input.*?value=")(.*?)(".*?\/>)/msi',
                            '$1 '.JText::_('TPL_TZ_WEISS_CONTACT_NAME')
                            .' $3',$contact_name);
                    }
                }
            }
        }
        echo $contact_name;
        ?>
<!--    </div>-->
</div>
<div class="control-group">
<!--    <div class="controls">-->
        <?php
        $contact_email  = $this -> form -> getInput('contact_email');
        if(!preg_match('/placeholder=\".*?\"/mi',$this -> form -> getInput('contact_email'))){
            $contact_email = preg_replace('/(\<input)(.*?)/mi',
                '$1 placeholder="'.JText::_('TPL_TZ_WEISS_CONTACT_EMAIL').'" $2',$contact_email);
        }
        if($browser -> getBrowser() == 'msie'){
            if(!preg_match('/onblur=".*?"/mi',$contact_email)){
                $contact_email   = preg_replace('/(\<input)(.*?)/mi',
                    '$1 onblur="if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_EMAIL').'\';" $2',$contact_email);
            }else{
                $contact_email   = preg_replace('/(\<input.*?onblur=")(.*?)(".*?\/>)/msi',
                    '$1 if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_EMAIL').'\'; $3',$contact_email);
            }
            if(!preg_match('/onfocus=".*?"/mi',$contact_email)){
                $contact_email   = preg_replace('/(<input)(.*?\/>)/msi','$1 onfocus="if(this.value.trim() == \''
                    .JText::_('TPL_TZ_WEISS_CONTACT_EMAIL').'\') this.value=\'\';" $2',$contact_email);
            }else{
                $contact_email   = preg_replace('/(<input.*?onfocus=")(.*?)(".*?\/>)/msi',
                    '$1 if(this.value.trim() == '.JText::_('TPL_TZ_WEISS_CONTACT_EMAIL')
                    .') this.value=\'\';" $3',$contact_email);
            }
            if(!preg_match('/value=".*?"/mi',$contact_email)){
                $contact_email   = preg_replace('/(<input)(.*?\/>)/msi','$1 value="'
                    .JText::_('TPL_TZ_WEISS_CONTACT_EMAIL').'" $2',$contact_email);
            }else{
                if(preg_match('/(<input.*?value=")(.*?)(".*?\/>)/msi',$contact_email,$match_email)){
                    if(isset($match_email[2]) && !strlen(trim($match_email[2]))){
                        $contact_email   = preg_replace('/(<input.*?value=")(.*?)(".*?\/>)/msi',
                            '$1 '.JText::_('TPL_TZ_WEISS_CONTACT_EMAIL')
                            .' $3',$contact_email);
                    }
                }
            }
        }
        echo $contact_email;
        ?>
<!--    </div>-->
</div>
<div class="control-group">
<!--    <div class="controls">-->
        <?php
        $contact_subject  = $this -> form -> getInput('contact_subject');
        if(!preg_match('/placeholder=\".*?\"/mi',$this -> form -> getInput('contact_subject'))){
            $contact_subject = preg_replace('/(\<input)(.*?)/mi',
                '$1 placeholder="'.JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL').'" $2',$contact_subject);
        }
        if($browser -> getBrowser() == 'msie'){
            if(!preg_match('/onblur=".*?"/mi',$contact_subject)){
                $contact_subject   = preg_replace('/(\<input)(.*?)/mi',
                    '$1 onblur="if(!this.value.length) this.value=\''
                    .JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL').'\';" $2',$contact_subject);
            }else{
                $contact_subject   = preg_replace('/(\<input.*?onblur=")(.*?)(".*?\/>)/msi',
                    '$1 if(!this.value.length) this.value=\''
                    .JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL').'\'; $3',$contact_subject);
            }
            if(!preg_match('/onfocus=".*?"/mi',$contact_subject)){
                $contact_subject   = preg_replace('/(<input)(.*?\/>)/msi','$1 onfocus="if(this.value.trim() == \''
                    .JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL').'\') this.value=\'\';" $2',$contact_subject);
            }else{
                $contact_subject   = preg_replace('/(<input.*?onfocus=")(.*?)(".*?\/>)/msi',
                    '$1 if(this.value.trim() == '.JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL')
                    .') this.value=\'\';" $3',$contact_subject);
            }
            if(!preg_match('/value=".*?"/mi',$contact_subject)){
                $contact_subject   = preg_replace('/(<input)(.*?\/>)/msi','$1 value="'
                    .JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL').'" $2',$contact_subject);
            }else{
                if(preg_match('/(<input.*?value=")(.*?)(".*?\/>)/msi',$contact_subject,$match_subject)){
                    if(isset($match_subject[2]) && !strlen(trim($match_subject[2]))){
                        $contact_subject   = preg_replace('/(<input.*?value=")(.*?)(".*?\/>)/msi',
                            '$1 '.JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL')
                            .' $3',$contact_subject);
                    }
                }
            }
        }
        echo $contact_subject;
        ?>
<!--    </div>-->
</div>
<div class="control-group">
<!--    <div class="controls">-->
        <?php
        $contact_message  = $this -> form -> getInput('contact_message');
        if(!preg_match('/placeholder=\".*?\"/mi',$this -> form -> getInput('contact_message'))){
            $contact_message = preg_replace('/(\<textarea)(.*?)/mi',
                '$1 placeholder="'.JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE').'" $2',$contact_message);
        }
        if($browser -> getBrowser() == 'msie'){
            if(!preg_match('/onblur=".*?"/mi',$contact_message)){
                $contact_message   = preg_replace('/(<textarea)(.*?)/msi',
                    '$1 onblur="if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE').'\';" $2',$contact_message);
            }else{
                $contact_message   = preg_replace('/(<textarea.*?onblur=")(.*?)(">.*?<\/textearea>)/msi',
                    '$1 if(!this.value.length) this.value=\''
                    .JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE').'\'; $3',$contact_message);
            }
            if(!preg_match('/onfocus=".*?"/mi',$contact_message)){
                $contact_message   = preg_replace('/(<textarea)(.*?<\/textarea>)/msi',
                    '$1 onfocus="if(this.value.trim() == \''
                    .JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE').'\') this.value=\'\';" $2',$contact_message);
            }else{
                $contact_message   = preg_replace('/(<textarea.*?onfocus=")(.*?)(".*?<\/textarea>)/msi',
                    '$1 if(this.value.trim() == '.JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE')
                    .') this.value=\'\';" $3',$contact_message);
            }
            if(preg_match('/(<textarea.*?\>)(.*?)(<\/textarea>)/msi',$contact_message,$match_message)){
                if(isset($match_message[2]) && !strlen(trim($match_message[2]))){
                    $contact_message   = preg_replace('/(<textarea.*?>)(.*?)(<\/textarea>)/msi',
                        '$1 '.JText::_('TPL_TZ_WEISS_CONTACT_ENTER_MESSAGE')
                        .' $3',$contact_message);
                }
            }
        }
        echo $contact_message;
        ?>
<!--    </div>-->
</div>
<?php //Dynamically load any additional fields from plugins. ?>
<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
    <?php if ($fieldset->name != 'contact'):?>
        <?php $fields = $this->form->getFieldset($fieldset->name);?>
        <?php foreach ($fields as $field) : ?>
            <?php if ($field->hidden) : ?>
                <div><?php echo $field->input;?></div>
            <?php else:?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $field->label; ?>
                        <?php if (!$field->required && $field->type != "Spacer") : ?>
                            <span class="optional"><?php echo JText::_('COM_CONTACT_OPTIONAL');?></span>
                        <?php endif; ?>
                    </div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
            <?php endif;?>

        <?php endforeach;?>
    <?php endif ?>
<?php endforeach;?>

<div class="control-group tz-control-group">
    <!--					<div class="control-label"></div>-->
<!--    <div class="controls">-->
        <?php if ($this->params->get('show_email_copy')) { ?>
            <?php echo $this->form->getInput('contact_email_copy'); ?>
            <?php echo $this->form->getLabel('contact_email_copy'); ?>
        <?php }?>
        <button class="btn btn-embossed validate pull-right" type="submit"><?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
        <span class="clearfix"></span>
<!--    </div>-->
</div>

<div>
    <input type="hidden" name="option" value="com_contact" />
    <input type="hidden" name="task" value="contact.submit" />
    <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
    <input type="hidden" name="id" value="<?php echo $this->contact->slug; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</div>
</fieldset>
</form>
</div>
