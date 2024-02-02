<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 Jan 2024 */

// Declare variables
$suffix = "";

/*
$history is a string of page IDs from cookie: 'page-9:page-6:page:20'
The variables may not exist or there may only be one or two

*/

if (defined('SHOW_HISTORY')) {
	if (SHOW_HISTORY == TRUE) {

		if (isset($_COOKIE["supermicro_history"])) {

			_print("\n			<div id=\"history\">\n");
			// _print('Number of values in $historyArray = ' . $num . '<br>');

			if ($rewrite == FALSE) {
				$suffix = '.php';
			} else {
				$suffix = FALSE;
			}

			$pageArray = explode(" ", $_COOKIE["supermicro_history"]); // Make array

			_print("\n<ul class=\"inline\">");
			_print("<li>You last viewed: &nbsp;</li>");
			if (isset($pageArray[0])) {
				_print("<li><a href=\"" . LOCATION . $pageArray[0] . $suffix . "\">" . $pageArray[0] . "</a></li>");
			}
			if (isset($pageArray[1])) {
				_print("<li> <span>|</span> <a href=\"" . LOCATION . $pageArray[1] . $suffix . "\">" . $pageArray[1] . "</a></li>");
			}
			if (isset($pageArray[2])) {
				_print("<li> <span>|</span> <a href=\"" . LOCATION . $pageArray[2] . $suffix . "\">" . $pageArray[2] . "</a></li>\n");
			}
			_print("</ul>\n");
			_print("\n			</div>\n");

		}

	} else {
		_print("\n		<!-- Location of history //-->\n");
	}
}

?>
