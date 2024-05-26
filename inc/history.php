<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 23 March 2024 */

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

			_print_nlab('<ul class="inline">');
			_print('<li class="viewed">You last viewed: &nbsp;</li>');
			if (isset($pageArray[0])) {
				_print("<li><a href=\"" . LOCATION . $pageArray[0] . $suffix . "\">" . $pageArray[0] . "</a></li>");
			}
			if (isset($pageArray[1])) {
				_print("<li> <span class=\"grey\">&#124;</span> <a href=\"" . LOCATION . $pageArray[1] . $suffix . "\">" . $pageArray[1] . "</a></li>");
			}
			if (isset($pageArray[2])) {
				_print_nlb("<li> <span class=\"grey\">&#124;</span> <a href=\"" . LOCATION . $pageArray[2] . $suffix . "\">" . $pageArray[2] . "</a></li>");
			}
			_print_nlb('</ul>');
			_print_nlab("		</div>\n");

		}

	} else {
		_print("\n		<!-- Location of history //-->\n");
	}
}

?>
