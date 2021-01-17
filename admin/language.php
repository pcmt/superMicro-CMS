<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 19 Oct 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to language.php');
}

// Declare variables
$lang = $language = $problem = $response2 = "";

// Only four language texts exist
if (isset($_POST['submit1'])) {
	$lang = $_POST['lang_attr'];

	// Check that the files exist
	if (($lang == 'de') && !file_exists('../inc/languages/de.php')) {
		$problem = TRUE;
		$response2 = '<em>The file <b>/inc/languages/de.php</b> must be installed.</em>';
	}
	if (($lang == 'es') && !file_exists('../inc/languages/es.php')) {
		$problem = TRUE;
		$response2 = '<em>The file <b>/inc/languages/es.php</b> must be installed.</em>';
	}
	if (($lang == 'fr') && !file_exists('../inc/languages/fr.php')) {
		$problem = TRUE;
		$response2 = '<em>The file <b>/inc/languages/fr.php</b> must be installed.</em>';
	}

} else { // Nothing submitted so "en"
	if (!defined('LANG_ATTR')) {
		define("LANG_ATTR", "en");
		$lang = LANG_ATTR;
	}
}

// $lang exists only in this file (for switching case)

switch ($lang) {
	case "en":
		$language = "English";
		break;
	case "fr":
		$language = "French";
		break;
	case "de":
		$language = "German";
		break;
	case "es":
		$language = "Spanish";
		break;
	default:
		$language = "English";
}

?>