<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
	// no direct access
	defined('_JEXEC') or die;
?>

<div id="tztwd">
<?php if(isset($curlDisabled)): ?>
Your PHP doesn't have cURL extension enabled. Please contact your host and ask them to enable it.
<?php else: ?>
It seems that module parameters haven't been configured properly. Please make sure that you are using a valid twitter username, and
that you have inserted the correct keys. Detailed instructions are written in the module settings page.
<?php endif; ?>
</div>
