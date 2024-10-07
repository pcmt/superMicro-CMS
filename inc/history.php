<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 11 July 2024 */

// Declare variables
$suffix = "";

/*
$history is a string of page IDs from cookie: 'page-9:page-6:page:20'
The variables may not exist or there may only be one or two

*/

if (defined('SHOW_HISTORY')) {

	if (SHOW_HISTORY == TRUE) {

		if (isset($_COOKIE[$smcms_history])) {

			_print_nlab('		<div id="history">');
			// _print('Number of values in $historyArray = ' . $num . '<br>');

			if ($rewrite == FALSE) {
				$suffix = '.php';
			} else {
				$suffix = FALSE;
			}

			// Remove trailing '_' when only two pages to avoid empty <li></li>
			$cookie_string = rtrim($_COOKIE[$smcms_history], '_');
			$pageArray = explode("_", $cookie_string); // Make array

			_print_nla('<p id="viewed">');
			_print('You last viewed: &nbsp;');
			if (isset($pageArray[0])) {
				_print("<a href=\"" . LOCATION . $pageArray[0] . $suffix . "\">" . $pageArray[0] . "</a>");
			}
			if (isset($pageArray[1])) {
				_print(" <span class=\"grey\">&#124;</span> <a href=\"" . LOCATION . $pageArray[1] . $suffix . "\">" . $pageArray[1] . "</a>");
			}
			if (isset($pageArray[2])) {
				_print(" <span class=\"grey\">&#124;</span> <a href=\"" . LOCATION . $pageArray[2] . $suffix . "\">" . $pageArray[2] . "</a>");
			}
			_print_nlb('</p>');
			_print_nlab("		</div>");

		}

	} else {
		_print("\n		<!-- Location of history //-->\n");
	}
}

?>
