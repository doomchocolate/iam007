 <?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<div id="mood" class="btn-group joms-mood-dropdown">
        <button class="btn dropdown-toggle" data-toggle="dropdown" id="joms-postbox-mood" data-default-string="Mood"><i class="joms-icon-smiley"></i> Mood</button>
        <ul class="dropdown-menu">
                <li><a data-mood="no-mood" href="#"><i class="joms-icon-remove" style="color:#c83030;"></i>Remove Mood</a></li>
                <li class="divider"></li>
                <li><a data-mood="happy" href="#"><i class="joms-emoticon joms-emo-happy"></i> Happy</a></li>
                <li><a data-mood="sad" href="#"><i class="joms-emoticon joms-emo-sad"></i> Sad</a></li>
                <li><a data-mood="excited" href="#"><i class="joms-emoticon joms-emo-excited"></i> Excited</a></li>
                <li><a data-mood="tired" href="#"><i class="joms-emoticon joms-emo-sleepy"></i> Tired</a></li>
                <li><a data-mood="great" href="#"><i class="joms-emoticon joms-emo-laugh"></i> Great</a></li>
                <li><a data-mood="bored" href="#"><i class="joms-emoticon joms-emo-bored"></i> Bored</a></li>
                <li><a data-mood="loved" href="#"><i class="joms-emoticon joms-emo-in-love"></i> Loved</a></li>
                <li><a data-mood="angry" href="#"><i class="joms-emoticon joms-emo-angry"></i> Angry</a></li>
                <li><a data-mood="sick" href="#"><i class="joms-emoticon joms-emo-sick"></i> Sick</a></li>
                <li><a data-mood="blessed" href="#"><i class="joms-emoticon joms-emo-angel"></i> Blessed</a></li>
                <li><a data-mood="depressed" href="#"><i class="joms-emoticon joms-emo-upset"></i> Depressed</a></li>
                <li><a data-mood="sleepy" href="#"><i class="joms-emoticon joms-emo-sleeping"></i> Sleepy</a></li>
                <li><a data-mood="wonderful" href="#"><i class="joms-emoticon joms-emo-sunglass"></i> Wonderful</a></li>
                <li><a data-mood="surprised" href="#"><i class="joms-emoticon joms-emo-surprised"></i> Surprised</a></li>
                <li><a data-mood="shocked" href="#"><i class="joms-emoticon joms-emo-shocked"></i> Shocked</a></li>
        </ul>
</div>

<script type="text/javascript">
        joms.jQuery('#mood .dropdown-menu a').click(function(e){
                 e.preventDefault();
                var t = joms.jQuery(this);
                var m = t.data('mood');
                var i = joms.jQuery(this).find('i').attr('class');
                if (m != 'no-mood') {
                    t.parents('ul.dropdown-menu').siblings('button').html('<i class="' + i + '"></i> ' + m);
                    jax.call('community', 'activities,ajaxSaveMood', <?php echo $activityId ?>,m);
                } else {
                    i = 'joms-icon-smiley';
                    b = t.parents('ul.dropdown-menu').siblings('button').data('default-string');
                    t.parents('ul.dropdown-menu').siblings('button').html('<i class="' + i + '"></i> ' + b);
                }
        });
</script>