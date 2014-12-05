
<script>
joms.jQuery(document).ready(function($){
  $(document).on("click", ".joms-share-status-privacy ul.dropdown-menu a", function(e) {
        e.preventDefault();
        var val = $(this).data('option-value');
        var icon = $(this).find('i').attr('class');
        $('input[name="joms-postbox-privacy"]').val(val);
        $(this).parents('ul.dropdown-menu').siblings('button').find('span.dropdown-value i').attr('class', icon);
    });
  $(document).on("click","#cWindowAction button.btn-primary",function(e){
      var data = {
                  msg:joms.jQuery("div#joms-share-popup textarea#joms-write-status").val(),
                  privacy:joms.jQuery('div#joms-share-popup input[name="joms-postbox-privacy"]').val()
                };
        data =  JSON.stringify(data);
      joms.share.add(<?php echo $this->act->id;?>,data);
  });
});
</script>

<div id="joms-share-popup">
  <div class="joms-popup-body">
    <div class="joms-share-status-popup">
      <div style="position:relative">
        <textarea class="joms-radius-normal" name="joms-write-status" data-minlength="0" data-maxlength="200" id="joms-write-status"
          placeholder="<?php echo JText::_('COM_COMMUNITY_SAY_SOMETHING')?>"
          style="min-height: 80px"></textarea>
        <div id="joms-write-status-charcounter" style="position: absolute; bottom: 11px; right: 5px"></div>
      </div>
      <script>
        joms.jQuery(function($) {
          var maxchar = +'<?php echo CFactory::getConfig()->get("statusmaxchar");?>',
              el = $('#joms-write-status'),
              cn = $('#joms-write-status-charcounter');
          el.off().on( 'keyup', function() {
            var text = el.val(),
                counter = Math.max( 0, maxchar - text.length );
            cn.html( counter );
          }).trigger('keyup');
        });
      </script>
      <div style="clear:both">
        <div data-stream-id="2" class="joms-privacy-dropdown joms-share-status-privacy">
          <input type="hidden" name="joms-postbox-privacy" value="<?php echo PRIVACY_PUBLIC?>">
          <?php echo CActivitiesHelper::getStreamPermissionHTML($act->access); ?>
        </div>
      </div>
    </div>
    <div class="joms-share-status-preview clearfix">

    <!-- Group discussion -->
    <?php if ($this->act->app == 'groups.discussion') { ?>

      <div class="joms-stream-box joms-responsive clearfix reset-gap">
        <aside>
            <i class="joms-icon-comments joms-icon-thumbnail"></i>
        </aside>
        <article>
          <?php $link = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $this->data->id. '&topicid=' . $this->act->cid );?>
          <a href="<?php echo $link; ?>"><?php echo $this->data->title; ?></a>
          <div class="separator"></div>
          <p class="reset-gap"><?php echo $this->act->content; ?></p>
          <div class="content-details"><?php echo $this->data->name; ?></div>
        </article>
      </div>

    <?php } else { ?>


      <div class="joms-share-status-inner">
        <?php if ( $this->act->app == 'profile' || $this->act->app == 'videos' ) { ?>
          <p><?php echo $this->act->title?><p>
        <?php } ?>
        <p class="joms-share-status-content"><?php echo $this->act->content; ?></p>
        <div class="joms-share-status-action">

          <span class="joms-share-status-username"> by <?php echo $user->getDisplayName()?></span>
        </div>
      </div>
    </div>

    <?php } ?>
  </div>
</div>