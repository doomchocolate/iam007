<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<html>
	<head>
		<script src="../assets/cookies-1.0.js" type="text/javascript"></script>
		<script type="text/javascript">
		// Delete all numeric cookies from facebook that is causing the stupid
		// "Illegal variable _files ..." error
		var myCookies = getCookies();
		for (cook in myCookies)
		{
			if (isNumber(cook) ){
				eraseCookie(cook);
			}
		}
		
		// all cleared? redirect to the correct one
		var url = getParameterByName('redirect_to');
		window.location = url
		</script>
	</head>
	<body>
	</body>
</html>