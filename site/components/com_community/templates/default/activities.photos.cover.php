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
$user = CFactory::getUser($this->act->actor);
$params = $this->act->params;
$type = $params->get('type');

$date = JFactory::getDate();
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$extraMessage = '';
if (strtolower($type) !== 'profile') {
    $id = $type . 'id';
    if ($type == 'group' || $type == 'event') {
        $cTable = JTable::getInstance(ucfirst($type), 'CTable');
        if ($cTable) { /* Make sure we had correct cTable */
            $cTable->load($this->act->$id);
            if ($type == 'group') {
                $extraMessage = ', <a href="' . $cTable->getLink() . '">' . $cTable->name . '</a>';
            }
            if ($type == 'event') {
                $extraMessage = ', <a href="' . CUrlHelper::eventLink($cTable->id) . '">' . $cTable->title . '</a>';
            }
        } else {
            $extraMessage = '';
        }
    }
}
/**
* Get cover path
*/
$coverPath = $params->get('attachment');

if ( !file_exists($coverPath) ) {
	$s3 = CStorage::getStorage('s3');
	$coverPath = $s3->getURI($coverPath);
} else {
    $coverPath = JURI::root().$coverPath;
}
?>

<div class="joms-stream-avatar">
    <a href="<?php echo ((int)$user->id !== 0) ? CUrlHelper::userLink($user->id) : 'javascript:void(0);'; ?>">
        <img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->_userid; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
    </a>
</div>


<div class="joms-stream-content">
    <header>
        <a class="cStream-Author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
        <?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_COVER_UPLOAD', Jtext::_('COM_COMMUNITY_COVER_' . strtoupper($type))) . $extraMessage; ?>
        <p class="joms-share-meta date"><?php echo $createdTime; ?></p>
    </header>
    <div class="cStream-Attachment">
        <img src="<?php echo $coverPath; ?>" class="joms-stream-single-photo" />
    </div>
    <?php $this->load('activities.actions'); ?>
</div>
