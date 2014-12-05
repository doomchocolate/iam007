<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// !!!IMPORTANT!!! Remove this line for the script to work
defined('_JEXEC') or die('Restricted access');

// Change the $hostname to your site's URL
$hostname = 'yoursite.com';

// If you have subfolder on your main site, specify it here. Should not end or begin with any trailing slash.
$subfolder = '';

if ($hostname == 'yoursite.com')
{
	return;
}

$resource = @fsockopen($hostname, 80, $errorNumber, $errorString);

if ( ! $resource)
{
	echo 'Error connecting to host';
	return;
}

$output = "GET /" . $subfolder . "/index.php?option=com_community&task=cron HTTP/1.1\r\n";
$output .= "Host: " . $hostname . "\r\n";
$output .= "Connection: Close\r\n\r\n";

fwrite($resource, $output);
fclose($resource);

echo "Cronjob processed.\r\n";
return;