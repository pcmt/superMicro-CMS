<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 19 Jan 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to lang.php');
}

if (defined('LANG_ATTR')) {

	// English
	if (LANG_ATTR == 'en') {
		include(INC . 'languages/en.php');
	} elseif (LANG_ATTR == 'de') {
		include(INC . 'languages/de.php');
	} elseif (LANG_ATTR == 'es') {
		include(INC . 'languages/es.php');
	} elseif (LANG_ATTR == 'fr') {
		include(INC . 'languages/fr.php');
	} else {
		// Default to English
		include(INC . 'languages/en.php');
	}

} else {
	// Fallback to English
	include(INC . 'languages/en.php');
}

?>