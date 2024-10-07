<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Sept 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to functions.php');
}

/* --------------------------------------------------
 * General
 */

function _print($text) {
	echo $text;
}

function _print_nla($text) { // Newline above
	echo "\n" . $text;
}

function _print_nlab($text) { // Newline above and below
	echo "\n" . $text . "\n";
}

function _print_nlb($text) { // Newline below
	echo $text . "\n";
}

/* --------------------------------------------------
 * All admin pages
 */

function p_title($page) {
	if (defined('SITE_NAME')) {
		echo SITE_NAME . ' admin ' . $page;
	} else {
		echo 'superMicro CMS admin ' . $page;
	}
}

/* --------------------------------------------------
 * All admin pages
 */

function h1($page) {
	if (defined('SITE_NAME')) {
		echo SITE_NAME . ' admin <i>' . $page . '</i>';
	} else {
		echo 'Admin <i>' . $page . '</i>';
	}
}

/* --------------------------------------------------
 * Most admin files:
 * index.php
 * images.php
 * upload.php
 * htaccess.php
 * backup.php
 * setup.php
 * stopwords.php
 * stylesheets.php
 * upload.php
 * video.php
 */

function includeFileIfExists($filename) {

	// Prevent 'Undefined variable' error
	global $self; // For login-form.php
	global $tm_start; // For footer.php
	global $thisAdmin; // For nav.php
	global $rewrite; // For list.php

	if (file_exists($filename)) {
		include($filename);
	} else {
		echo "Error. The file '{$filename}' doesn't exist. It must be installed.";
		exit();
	}

	// Usage: includeFileIfExists('./example.php');
}

/* --------------------------------------------------
 * index.php
 * comments.php
 * images.php
 * upload.php
 * htaccess.php
 * backup.php
 * setup.php
 * stopwords.php
 * stylesheets.php
 * upload.php
 * video.php
 */

// Edited 14 March 2023
function phpSELF() {
	// Convert special characters to HTML entities
	$str = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES);
	if (empty($str)) { // Fix empty PHP_SELF
		// Strip query string
		$str = preg_replace("/(\?.*)?$/", "", $_SERVER['REQUEST_URI']);
	}

	return $str;
}

/* --------------------------------------------------
 * index.php
 * comments.php
 * extras.php
 * images.php
 * upload.php
 * htaccess.php
 * backup.php
 * setup.php
 * stopwords.php
 * stylesheets.php
 * upload.php
 * video.php
 */

// Displays footer on logout
function loggedoutFooter() {
	global $dofooter;
	if ($dofooter) {
		$anchor = str_replace(['http://', 'https://'], '', LOCATION);
		echo '<p><a href="' . LOCATION . '">' . $anchor . '</a></p>';
	}

	if (file_exists('./install.php') && !file_exists('./password.php')) { // For when not installed
		echo '<p>It seems superMicro CMS is not yet installed.</p>
<p><a href="./install.php">Install here&nbsp;&raquo;</a></p>';
	} else { // Is installed
		echo '<p><a href="https://web.patricktaylor.com/hash-sha256" target="_blank">Lost or forgotten password&nbsp;&raquo;</a></p>';
	}
}

/* --------------------------------------------------
 * htaccess.php
 * setup.php
 */

// HTTPS or HTTP
function get_protocol() {
	if (isset($_SERVER['HTTPS'])) {
		if ('on' === strtolower($_SERVER['HTTPS'])) {
			return TRUE;
		}

		if ('1' === $_SERVER['HTTPS']) {
			return TRUE;
		}

	} elseif (isset($_SERVER['SERVER_PORT']) && ('443' === $_SERVER['SERVER_PORT'])) {
		return TRUE;
	}

	return FALSE;
}

/* --------------------------------------------------
 * top.php
 */

function sanitizeIt($var) {
	$var = trim($var);
	$var = str_replace(["<", ">", "\"", "'", "\\", "/"], '', $var);
	$var = stripslashes($var);
	return $var;
}

/* --------------------------------------------------
 * install.php
 */

// For cookies and site ID
function randomString($length) {
	$chars = "abcdefghijklmnopqrstuvwxyz";
	$str = '';
	$size = strlen($chars);
	for ($i = 0; $i < $length; $i++) {
		$str .= $chars[rand(0, $size - 1)];
	}

	return $str;
}

/* --------------------------------------------------
 * Not used?
 */
/*
function getPostValue($key) {
	// Each variable is assigned the trimmed value IF the corresponding
	// key exists in the $_POST array, otherwise an empty string is assigned
	// eg: $_value = if condition ? if true : if false
	return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}
*/
/* --------------------------------------------------
 * setup.php
 */

function allowedChars($str) {

	$charArray = array(
		/* Symbols etc */
		'-', '_', '\,', '\.', '\'', '~', ' ',
		/* Numbers */
		'1', '2', '3', '4', '5','6', '7', '8', '9', '0',
		/* English */
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		/* Latin */
		'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ő', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ű', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ő', 'ø', 'ù', 'ú', 'û', 'ü', 'ű', 'ý', 'þ', 'ÿ'
	);

	$allowed = implode("", $charArray);
	$str = preg_replace("/[^' . $allowed . ']+/u", "", $str); // Strip all characters except $allowed
	$str = preg_replace("/[[:space:]]+/", " ", $str); // Strip multiple spaces
	$str = preg_replace("#'+#", "\'", $str); // Escape multiple single quotes for settings.php

	return $str;
}

/* --------------------------------------------------
 * install.php
 */

function allChars($str) {

	$charArray = array(
		/* Symbols etc (not quite the same as admin/functions.php) */
		'_', '!', '$', '%', '^', '?', '*', '+', '=', '@', '~', '#', '[', ']', '{', '}', '(', ')',
		/* Numbers */
		'1', '2', '3', '4', '5','6', '7', '8', '9', '0',
		/* English */
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		/* Latin */
		'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ő', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ű', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ő', 'ø', 'ù', 'ú', 'û', 'ü', 'ű', 'ý', 'þ', 'ÿ'
	);

	$allowed = implode("", $charArray);
	// Strip everything except $allowed
	$str = preg_replace('/[^' . $allowed . ']+/u', '', $str);
	$str = preg_replace('/[[:space:]]+/', ' ', $str);

	return $str;

}

/* --------------------------------------------------
 * htaccess.php
 */
/*
Initial Split (explode($start, $content)):
The explode function splits the $content string at each occurrence of the $start substring. The result is an array $r where the first element ($r[0]) contains everything before the first occurrence of $start, and the second element ($r[1]) contains everything after it.

Check if $start was found:
The isset($r[1]) check is used to determine if the $start substring was found in the $content. If $start is not found, $r[1] will not be set, and the function will return an empty string.

Second Split (explode($end, $r[1])):
If $start was found, the code then splits the second part of the string (everything after $start) at the first occurrence of the $end substring. This returns another array where $r[0] contains the string between $start and $end.

Return the Substring:
The function returns the string found between $start and $end. If $end is not found, the entire string after $start will be returned.

Fallback Return:
If $start is not found in the content, the function returns an empty string.
*/
function getBetween($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$r = explode($end, $r[1]);
		return $r[0];
	}

	return '';
}

/* --------------------------------------------------
 * index.php
 */

function removeEmptyLines($str) {
	return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
}

/* --------------------------------------------------
 * index.php
 */

// Remove text enclosed in square brackets from a given string
function stripAnchor($str) {
/*
Breakdown of the pattern:
\[: matches the literal [
[^\]]*: matches any characters that are not ] zero or more times
]: Matches the literal ]
*/
	$str = preg_replace("/\[[^\]]*]/", "", $str);
	$str = trim($str);

	return $str;
}

?>