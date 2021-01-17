<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 15 Sept 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to content.php');
}

/* START CONTENT ==================================== */
$content_string = '';
$content_string .= "\n<h1>" . $title . "</h1>\n";
$content_string .= $text;
// $content_string .= $outputpassword;
$content_string .= "\n<p class=\"meta\">" . TEXT00 . ' ' . $modified . "</p>\n";

/* -------------------------------------------------- */
// Pass the page through the functions (order critical)
$content = autop($content_string);
$content = bits_and($content);
$content = absolute_it($content); // 28 Aug 20
if (PHP_EXT == TRUE) { // 15 Sept 20
	if (APACHE == FALSE) {
		$content = suffix_it($content);
	}
}
$content = img_path($content);
$content = video_path($content);

/* -------------------------------------------------- */
// If content is set and actually exists
if ($content && (strlen($content) > 0)) {
	_print($content);
	if (file_exists(INC . 'extra-content.php')) {
		require(INC . 'extra-content.php');
	}
} else {
	_print("\n<p>No content darling. Something needs fixing.</p>\n");
}
/* END CONTENT ====================================== */

?>