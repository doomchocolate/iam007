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
$classArray = array(0 => 'joms-icon-globe', 20 => 'joms-icon-users', 30 => 'joms-icon-user', 40 => 'joms-icon-lock');
$_permission = array( 0=>'COM_COMMUNITY_PRIVACY_PUBLIC',
                                    10=>'COM_COMMUNITY_PRIVACY_PUBLIC',
                                    PRIVACY_MEMBERS=>'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
                                    PRIVACY_FRIENDS=>'COM_COMMUNITY_PRIVACY_FRIENDS',
                                    PRIVACY_PRIVATE=>'COM_COMMUNITY_PRIVACY_ME'
                                    );
$selectedAccess = ($selectedAccess) ? $selectedAccess : 0;
$direction = ($direction) ? $direction : '';
?>
<!--div class="<?php echo $classAttribute; ?>">
        <select name="<?php echo $nameAttribute; ?>" class="js_PrivacySelect js_PriDefault">
<?php if (isset($access['public']) && $access['public'] === true) { ?>
                    <option class="js_PriOption js_Pri-0" value="0"<?php echo $selectedAccess == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_PRIVACY_PUBLIC'); ?></option>
<?php } ?>

<?php if (isset($access['members']) && $access['members'] === true) { ?>
                    <option class="js_PriOption js_Pri-20" value="20"<?php echo $selectedAccess == 20 ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_PRIVACY_SITE_MEMBERS'); ?></option>
<?php } ?>

<?php if (isset($access['friends']) && $access['friends'] === true) { ?>
                    <option class="js_PriOption js_Pri-30" value="30"<?php echo $selectedAccess == 30 ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS'); ?></option>
<?php } ?>

<?php if (isset($access['self']) && $access['self'] === true) { ?>
                    <option class="js_PriOption js_Pri-40" value="40"<?php echo $selectedAccess == 40 ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_PRIVACY_ME'); ?></option>
<?php } ?>
        </select>
        <div class="clear"></div>
</div-->
<div id="joms-postbox-privacy-dropdown" class="joms-privacy-dropdown">
    <input type="hidden" name="<?php echo $nameAttribute; ?>" value="<?php echo $selectedAccess; ?>">
    <button type="button" class="btn form-control dropdown-toggle" data-toggle="dropdown">
        <span id="permission-placeholder"><?php echo JText::_($_permission[$selectedAccess])?></span>
        <span class="dropdown-value">
            <i class="<?php echo (isset($classArray[$selectedAccess])) ? $classArray[$selectedAccess] : ''; ?>"></i>
        </span>
        <span class="joms-icon-caret-down"></span>
    </button>
    <ul class="dropdown-menu" style="<?php
        if ( strpos( $direction, 't' ) !== false ) {
            echo 'top:auto;bottom:100%;';
        }
    ?>margin:2px 0">

        <?php if (isset($access['public']) && $access['public'] === true) { ?>
            <li><a href="javascript:" data-option-value="0"><i class="joms-icon-globe"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_PUBLIC'); ?></span></a></li>
        <?php } ?>

        <?php if (isset($access['members']) && $access['members'] === true) { ?>
            <li><a href="javascript:" data-option-value="<?php echo PRIVACY_MEMBERS ?>"><i class="joms-icon-users"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_SITE_MEMBERS'); ?></span></a></li>
        <?php } ?>

        <?php if (isset($access['friends']) && $access['friends'] === true) { ?>
            <li><a href="javascript:" data-option-value="<?php echo PRIVACY_FRIENDS ?>"><i class="joms-icon-user"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS'); ?></span></a></li>
        <?php } ?>

        <?php if (isset($access['self']) && $access['self'] === true) { ?>
            <li><a href="javascript:" data-option-value="<?php echo PRIVACY_PRIVATE ?>"><i class="joms-icon-lock"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_ME'); ?></span></a></li>
        <?php } ?>

    </ul>
</div>
<script>
    joms.jQuery(function($) {
        $(document).on('click #joms-postbox-privacy-dropdown ul li a', function(e) {
            var a = $(e.target).closest('a'),
				btn = a.closest('ul').siblings('button'),
				input = btn.siblings('input'),
                placeHolder = btn.find('span#permission-placeholder');

            btn.find('i').attr('class', a.find('i').attr('class'));
			input.val( a.attr('data-option-value') );
            placeHolder.html(a.find('span').html())
        });
    });
</script>
<!-- /btn-group -->
