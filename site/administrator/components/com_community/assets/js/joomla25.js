jQuery(document).ready(function() {
	jQuery('#toolbar-box').remove();
});

jQuery(document).load(function() {
	// calculate sidebar height because it's absolute position
	var sidebarHeight = jQuery('.sidebar-collapse').position().top + jQuery('.sidebar-collapse').outerHeight();
	var contentHeight = jQuery('.page-content').outerHeight();

	var highestCol = Math.max(sidebarHeight, contentHeight);

	jQuery('.main-content').height(highestCol);

});