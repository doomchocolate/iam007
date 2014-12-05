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
?>
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
URL:<?php echo $url; ?>
 
DTSTART:<?php echo $dtstart; ?>

DTEND:<?php echo $dtend; ?>

SUMMARY:<?php echo $event->title; ?>
<?php if (!empty($event->repeat)) { ?>

RRULE:FREQ=<?php echo strtoupper($event->repeat); ?>;UNTIL=<?php echo $rend; ?>
<?php } ?>

DESCRIPTION:<?php echo $event->description ?>

LOCATION:<?php echo $event->location; ?>

END:VEVENT
END:VCALENDAR