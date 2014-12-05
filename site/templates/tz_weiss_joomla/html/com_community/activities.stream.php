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

if(!isset($stream->groupid))
    $stream->groupid='';
?>

<?php if(is_object($stream->actor)):?>

    <div class="joms-stream-avatar">
        <a href="<?php echo ((int)$stream->actor->id !== 0) ? CUrlHelper::userLink($stream->actor->id) : 'javascript:void(0);'; ?>">
            <img class="img-responsive joms-radius-rounded" data-author="<?php echo $stream->actor->id; ?>" src="<?php echo $stream->actor->getThumbAvatar(); ?>">
        </a>
    </div>

    <?php else: ?>
    <span class="joms-stream-avatar">
        <img class="img-responsive joms-radius-rounded" src="components/com_community/assets/user-Male-thumb.png" />
    </span>
    <?php endif;?>

<div class="joms-stream-content">
    <header>
      <?php echo $stream->headline; ?>
       <!-- Privacy -->
        <div>
          <?php echo (!$stream->groupid) ? CActivitiesHelper::getStreamPermissionHTML($stream->access,$stream->actor->id) : ''; ?>
          <span class="joms-share-meta date">
            <?php echo (isset($stream->createdtime) ? $stream->createdtime: ''); ?>
          </span>
        </div>
    </header>

    <?php
    // Contain message ?
    if ($stream->message) {
        ?>

            <p><span><?php echo $stream->message; ?></span></p>

    <?php } ?>

    <?php
    if (!empty($stream->attachments)) {
        foreach ($stream->attachments as $attachment) {
            switch ($attachment->type) {
                case 'media':
                    ?>
                    <div class="cStream-Attachment">
                        <div class="joms-stream-single-photo clearfix">
                                <?php if( isset($attachment->thumbnail) && !is_array($attachment->thumbnail) ) {?>
                                <div class="joms-stream-multi-photo row-fluid">
                                  <div class="span3">
                                    <a href="#"><img class="joms-stream-single-photo" src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>" alt="photo"></a>
                                  </div>
                                </div>
                                <?php } else {
                                    if(count($attachment->thumbnail) >= 5){
                                  ?>
                                    <div class="joms-stream-multi-photo-hero">
                                      <a href="#">
                                        <img src="<?php echo $attachment->thumbnail[0];?>" />
                                      </a>
                                    </div>

                                  <?php
                                    unset($attachment->thumbnail[0]);
                                  } ?>
                                  <div class="joms-stream-multi-photo row-fluid">
                                  <?php foreach($attachment->thumbnail as $key=>$thumb){ ?>
                                    <div class="span3">
                                      <a href="<?php echo $attachment->link[$key]; ?>"><img src="<?php echo (isset($thumb)) ? $thumb : ''; ?>" alt="photo"></a>
                                    </div>
                                  <?php } ?>
                                  </div>
                                <?php } ?>
                            </div>
                    </div>
                    <?php
                    break;

                case 'photo': ?>
                <div class="cStream-Attachment">
                    <div class="joms-stream-single-photo">
                        <a href="<?php echo $attachment->link; ?>" >
                            <img src="<?php echo $attachment->singlephoto; ?>" />
                        </a>
                        <p class="joms-stream-photo-caption"><?php echo $attachment->caption; ?></p>
                    </div>
                </div>
                <?php
                break;

                case 'photos': ?>
                    <div class="cStream-Attachment">
                        <div class="clearfix">
                          <div class="row-fluid">
                          <?php foreach($attachment->thumbnail as $key=>$thumb){ ?>
                            <div class="span3">
                              <a class="joms-stream-single-photo" href="<?php echo $attachment->link[$key] ?>"><img src="<?php echo (isset($thumb)) ? $thumb : ''; ?>" alt="photo"></a>
                            </div>
                          <?php } ?>
                          </div>
                        </div>
                    </div>
                <?php
                break;

                case 'cover':
                ?>
                <div class="cStream-Attachment">
                    <div class="clearfix">
                        <?php if( isset($attachment->thumbnail) && !is_array($attachment->thumbnail) ) {?>
                            <a href="#"><img class="joms-stream-single-photo" src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>" alt="photo"></a>
                        <?php } ?>
                    </div>
                </div>
                <?php
                break;

                case 'album':
                    ?>
                    <div class="cStream-Attachment">
                        <div class="cStream-Photo">
                            <div class="cStream-PhotoRow row-fluid">
                                <div class="span3">
                                    <a class="cPhoto-Thumb" href="#"><img src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>" alt="photo"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    break;

                case 'video':
                    // the id is optional. If avaiable, it linked to internal video id

                    if(isset($attachment->video_type) && $attachment->video_type == 'break'){
                        $anchorAttr = 'href="'.$attachment->link.'" target="_blank"';
                    }else{
                        $link = (!empty($attachment->id)) ? "javascript:joms.walls.showVideoWindow('{$attachment->id}')" : "#";
                        $anchorAttr = 'href="'.$link.'"';
                    }


                    ?>

                    <div class="joms-stream-box joms-fetch-wrapper clearfix" >
                        <div style="position:relative;">
                            <div class="row-fluid">
                                <div class="span3">
                                    <a <?php echo $anchorAttr; ?> class="cVideo-Thumb">
                                        <div style=" position:relative">
                                            <img src="<?php echo $attachment->thumbnail; ?>"
                                              alt="<?php echo $this->escape($attachment->title); ?>"
                                              style="max-width:100%" />
                                            <b><?php echo $attachment->duration; ?></b>
                                        </div>
                                    </a>
                                </div>
                                <div class="span9">
                                    <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                        <a <?php echo $anchorAttr; ?>><?php echo $attachment->title; ?></a>
                                        <p class="reset-gap">
                                            <?php echo JHTML::_('string.truncate', $attachment->description, $config->getInt('streamcontentlength'), true, false); ?>
                                        </p>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    break;

                case 'quote':
                    ?>
                    <div class="cStream-Attachment">
                        <div class="cStream-Quote">
                            <?php echo $attachment->message; ?>
                        </div>
                    </div>
                    <?php
                    break;

                case 'discussion_reply':
                    ?>
                    <div class="cStream-Attachment">
                        <div class="cStream-Quote">
                            <?php echo $attachment->message; ?>
                        </div>
                    </div>
                    <?php
                    break;

                case 'text': ?>
                    <p><?php echo $attachment->message; ?></p>
                <?php
                break;

                case 'create_discussion': ?>

                    <div class="joms-stream-box joms-responsive clearfix">
                        <aside>
                            <i class="joms-icon-comments joms-icon-thumbnail"></i>
                        </aside>
                        <article>
                          <a href="<?php echo $stream->link; ?>"><?php echo $stream->title; ?></a>
                          <div class="separator"></div>
                          <p class="reset-gap"><?php echo $attachment->message; ?></p>
                          <div class="content-details"><?php echo $stream->group->name ?></div>
                        </article>
                    </div>

                <?php
                break;

                case 'create_announcement': ?>

                    <div class="joms-stream-box joms-responsive clearfix">
                        <aside>
                            <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
                        </aside>
                        <article>
                          <a href="<?php echo $stream->link; ?>"><?php echo $stream->title; ?></a>
                          <div class="separator"></div>
                          <p class="reset-gap"><?php echo $attachment->message; ?></p>
                          <div class="content-details"><?php echo $stream->group->name ?></div>
                        </article>
                    </div>

                <?php
                break;
                case 'event_share': ?>
                    <div class="joms-stream-box joms-responsive">
                        <aside>
                          <i class="joms-icon-thumbnail joms-icon-calendar-empty"></i>
                        </aside>
                        <article>
                          <a href="<?php echo $attachment->message->getLink();?>"> <i class="joms-icon-calendar-empty portrait-phone-only"></i> <?php echo JHTML::_('string.truncate', $attachment->message->title, 60, true, false ); ?></a>
                          <div class="separator"></div>
                          <p><?php echo $attachment->message->summary?></p>
                          <ul class="list-unstyled content-details">
                            <li>
                                <i class="joms-icon-calendar"></i>
                                <?php echo JHtml::date($attachment->message->startdate,'D F n, Y g:i a',false); ?>
                            </li>
                            <li>
                                <i class="joms-icon-map-marker"></i>
                                <?php echo $attachment->message->location; ?>
                            </li>
                          </ul>
                        </article>
                    </div>
                <?php
                break;
                case 'group_share':?>
                  <div class="joms-stream-box joms-responsive clearfix">
                        <aside>
                          <i class="joms-icon-users joms-icon-thumbnail"></i>
                        </aside>
                        <article>
                          <a href="<?php echo $attachment->message->link;?>">
                              <i class="joms-icon-users portrait-phone-only"></i>
                              <?php echo JHTML::_('string.truncate', $attachment->message->title, 60, true, false ); ?>
                          </a>
                          <p><?php echo $attachment->message->description?></p>
                        </article>
                    </div>
                <?php
                break;
                case 'fetched':
                    //this is the fecthed content from url
                    echo $attachment->message;
                break;
                case 'share.groups.discussion':
                ?>
                    <div class="joms-stream-box joms-responsive clearfix">
                        <aside>
                            <i class="joms-icon-comments joms-icon-thumbnail"></i>
                        </aside>
                        <article>
                          <a href="<?php echo $attachment->link; ?>"><?php echo $attachment->discussion_title; ?></a>
                          <div class="separator"></div>
                          <p class="reset-gap"><?php echo $attachment->discussion_message; ?></p>
                          <div class="content-details"><?php echo $attachment->group_name ?></div>
                        </article>
                    </div>
                <?php
                break;
                case 'share.groups.discussion.reply';
                ?>
                <div class="cStream-Attachment">
                    <div class="cStream-Quote">
                        <?php echo $attachment->comment; ?>
                    </div>
                </div>
                <?php
                default:
                    # code...
                    break;
            }
        } // end foreach
    } // end if

    $this->load('activities.actions');
    ?>

</div>