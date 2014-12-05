<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
$template   = JFactory::getApplication() -> getTemplate(true);
$tplparams = $template -> params;
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        if(jQuery('#community-wrap').find('.hero-area').length){
            jQuery('#community-wrap').find('.hero-area').css({
               left: (jQuery('#community-wrap').width() - jQuery(window).width()) /2,
                width: jQuery(window).width()
            });
        }
        if(jQuery(window).width() < 768){
            jQuery('.cGuest').insertAfter(jQuery('.hero-area'));
        }else{
            if(jQuery('#community-wrap').find('> .cGuest').length){
                jQuery('.content.hidden-phone').append(jQuery('#community-wrap > .cGuest'));
            }
        }
    });
    jQuery(window).bind('resize',function(){
        if(jQuery('#community-wrap').find('.hero-area').length){
            jQuery('#community-wrap').find('.hero-area').css({
                left: (jQuery('#community-wrap').width() - jQuery(window).width()) /2,
                width: jQuery(window).width()
            });
        }
        if(jQuery(window).width() < 768){
            jQuery('.cGuest').insertAfter(jQuery('.hero-area'));
        }else{
            if(jQuery('#community-wrap').find('> .cGuest').length){
                jQuery('.content.hidden-phone').append(jQuery('#community-wrap > .cGuest'));
            }
        }
    });
</script>
<div class="row-fluid hero-area">
	<div class="hero-area-wrapper">
        <?php
        $image  = JURI::root().'components/com_community/templates/default/images/guest-bg.jpg';
        if($bg = $tplparams -> get('jomsocial_bg_header')){
            $image  = JUri::root().$bg;
        }
        ?>
		<img class="hero-area-bg" src="<?php echo $image; ?>"
             alt="<?php echo JText::_('COM_COMMUNITY_GET_CONNECTED_TITLE');?>"/>

		<div class="content hidden-phone">
			<div class="content-cta">
                <div class="container-fluid">
                    <div class="row-fluid">
                        <div class="span6">
                            <h1 class="heading"><span><?php echo JText::_('COM_COMMUNITY_GET_CONNECTED_TITLE'); ?></span></h1>
                            <p><?php echo JText::_('COM_COMMUNITY_HERO_PARAGRAPH'); ?></p>
                            <?php if ($allowUserRegister) : ?>
                            <a class="btn btn-small btn-margin-top" href="<?php echo CRoute::_( 'index.php?option=com_community&view=register' , false ); ?>">
                                <?php echo JText::_('COM_COMMUNITY_JOIN_US_NOW'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
			</div>

            <div class="cGuest">


                <div class="login-area">

                    <form class="reset-gap container-fluid" action="<?php echo CRoute::getURI();?>" method="post" name="login" id="form-login" >

                        <div class="row-fluid">

                            <div class="span6">
                                <input type="text" name="username" id="username" tabindex="1" placeholder="<?php echo JText::_('COM_COMMUNITY_USERNAME'); ?>" />

                                <input type="password" name="<?php echo COM_USER_PASSWORD_INPUT;?>" id="password"  tabindex="2" placeholder="<?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?>" />

                                <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_LOGIN_BUTTON');?>" name="submit" id="submit" class="btn btn-small btn-inverse"  tabindex="3" />
                            </div>

                            <div class="span6">
                                <ul class="inline unstyled pull-right">
                                    <li>
                                        <a class="reminder-link" href="<?php echo CRoute::_( 'index.php?option='.COM_USER_NAME.'&view=remind' ); ?>" tabindex="5">
                                            <?php echo JText::_('COM_COMMUNITY_FORGOT_USERNAME_LOGIN'); ?></a>
                                    </li>
                                    <li>
                                        <a class="reminder-link" href="<?php echo CRoute::_( 'index.php?option='.COM_USER_NAME.'&view=reset' ); ?>" tabindex="6">
                                            <?php echo JText::_('COM_COMMUNITY_FORGOT_PASSWORD_LOGIN'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <?php if ($useractivation) { ?>
                                            <a class="reminder-link" href="<?php echo CRoute::_( 'index.php?option=com_community&view=register&task=activation' ); ?>" class="login-forgot-username">
                                                <span><?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION_CODE'); ?></span>
                                            </a>
                                        <?php } ?>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <input type="hidden" name="option" value="<?php echo COM_USER_NAME;?>" />
                        <input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGIN;?>" />
                        <input type="hidden" name="return" value="<?php echo $return; ?>" />
                        <?php echo JHTML::_( 'form.token' ); ?>
                    </form>

                    <?php echo $fbHtml;?>




                </div>

            </div>
		</div>

		<div class="content visible-phone">
			<?php if ($allowUserRegister) : ?>
			<a class="btn btn-small" href="<?php echo CRoute::_( 'index.php?option=com_community&view=register' , false ); ?>">
				<?php echo JText::_('COM_COMMUNITY_JOIN_US_NOW'); ?>
			</a>
			<?php endif; ?>
		</div>

	</div>
</div>