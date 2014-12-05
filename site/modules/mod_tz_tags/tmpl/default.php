<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    TemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
$link = $params->get('tag-link');

?>
<ul class="mod_tz_tag">
<?php

foreach ($list as $tag) { ?>
        <?php if ($link =='yes'){ ?>
             <li class="tag_item"><a style="font-size: <?php echo $tag->size;?>%" href="<?php echo $tag->taglink;?>"><?php echo $tag->tagname;?></a> </li>

    <?php } else {?>
        <li class="tag_item"><span style="font-size: <?php echo $tag->size;?>%"><?php echo $tag->tagname;?></span> </li>
    <?php } } ?>

</ul>