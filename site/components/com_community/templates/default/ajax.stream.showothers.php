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

$item_per_page = 8;
$count = count( $users );
$pages = ceil( $count / $item_per_page );

?>
<div id="joms-others-list" <?php echo $pages > 1 ? ' style="padding-bottom:20px"' : '' ?>>
<?php

for ( $i = 0; $i < $pages; $i++ ) {

?>
<div class="clearfix" data-elem="list"<?php echo $i ? ' style="display:none"' : '' ?>>
  <ul class="unstyled">
<?php

  $start = $i * $item_per_page;
  $stop = min( $start + $item_per_page, $count );
  for ( $j = $start; $j < $stop; $j++ ) {

?>
  <li class="joms-box-list two-columns joms-padding-small" >
    <aside>
      <img src="<?php echo $users[$j]->getThumbAvatar(); ?>" alt="" >
    </aside>
    <article>
      <h6 class="reset-gap" style="font-weight:normal;"><a href="#"><?php echo $users[$j]->getDisplayName(); ?></a></h5>
    </article>
  </li>
<?php

  }

?>
  </ul>
</div>
<?php

}

if ( $pages > 1 ) {

?>
<div data-elem="nav" class="joms-box-list-pagination">
<?php

  for ( $i = 0; $i < $pages; $i++ ) {

?>
<a class="joms-inline-block <?php echo $i ? '' : ' active' ?>" href="javascript:" rel="<?php echo $i + 1 ?>"><?php echo $i + 1 ?></a>
<?php

  }

?>
</div>
<?php

}

?>
</div>
<script>
  joms.jQuery(function( $ ) {
    var ct = $('#joms-others-list');
    ct.on( 'click', '[data-elem=nav] a', function() {
      var btn  = $( this );
      var btns = btn.siblings('a');
      var page = btn.attr('rel');
      var divs = ct.find('[data-elem=list]');
      var div  = divs.eq( +page - 1 );
      divs.not( div ).hide();
      div.show();
      btns.not( btn ).removeClass('active');
      btn.addClass('active');
    });
  });
</script>