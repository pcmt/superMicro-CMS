<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Dec 2022 */

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
 * setup.php
 */

function getchmod($file) {

	$perms = fileperms($file);

	if (($perms & 0xC000) == 0xC000) {
		// Socket
		$info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		// Symbolic Link
		$info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		// Regular
		$info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		// Block special
		$info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		// Directory
		$info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		// Character special
		$info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		// FIFO pipe
		$info = 'p';
	} else {
		// Unknown
		$info = 'u';
	}

	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
		(($perms & 0x0800) ? 's' : 'x' ) :
		(($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
		(($perms & 0x0400) ? 's' : 'x' ) :
		(($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
		(($perms & 0x0200) ? 't' : 'x' ) :
		(($perms & 0x0200) ? 'T' : '-'));

	$realmode = '';
	$legal =  array('', 'w', 'r', 'x', '-');
	$attarray = preg_split('//', $info);

	for ($i=0; $i < count($attarray); $i++)
		if ($key = array_search($attarray[$i], $legal))
			$realmode .= $legal[$key];

		$info = str_pad($realmode, 10, '-', STR_PAD_LEFT);
		$trans = array('-'=>'0', 'r'=>'4', 'w'=>'2', 'x'=>'1');
		$info = strtr($info, $trans);

		$newmode = $info[0];
		$newmode .= $info[1] + $info[2] + $info[3];
		$newmode .= $info[4] + $info[5] + $info[6];
		$newmode .= $info[7] + $info[8] + $info[9];

	return $newmode;
}

/* --------------------------------------------------
 * htaccess.php
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

function stripAnchor($str) {
	$str = preg_replace("/\[[^\]]*]/", "", $str);
	$str = trim($str);

	return $str;
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
 */

// Edited 27 Feb 2020
function phpSELF() {
	// Convert special characters to HTML entities
	$str = htmlspecialchars($_SERVER['PHP_SELF']);
	if (empty($str)) { // Fix empty PHP_SELF
		// Strip query string
		$str = preg_replace("/(\?.*)?$/", "", $_SERVER['REQUEST_URI']);
	}

	return $str;
}

/* --------------------------------------------------
 * install.php
 * htaccess.php
 */

// Replaces PHP is_writable() as workaround for Windows bug in is_writable() function
function isWritable($path) {
	if ('WIN' === strtoupper(substr(PHP_OS, 0, 3 ))) {
		return win_isWritable($path);
	} else {
		return @is_writable($path);
	}
}

/* --------------------------------------------------
 * ??.php
 */

// This function is called only on Windows servers (adapted from WordPress 4.0)
// Not used since Nov 18
function win_isWritable($path) {
	if ($path[strlen($path) - 1] == '/') { // If it looks like a directory...
		return win_isWritable($path . uniqid(mt_rand()) . '.tmp');
	} elseif (is_dir($path)) { // If it is a directory and not a file...
		return win_isWritable($path . '/' . uniqid(mt_rand()) . '.tmp');
	}

	// Check tmp file for read/write capabilities
	$should_delete_tmp_file = !file_exists($path);
	$f = @fopen($path, 'a');
	if ($f === FALSE) {
		return FALSE;
	}
	fclose($f);

	if ($should_delete_tmp_file) {
		unlink($path);
	}

	return TRUE;
}

/* --------------------------------------------------
 * htaccess.php
 * setup.php
 */

// HTTPS or HTTP (from 22 Nov 18)
function get_protocol() {
	if (isset($_SERVER['HTTPS'])) {
		if ('on' == strtolower($_SERVER['HTTPS'])) {
			return TRUE;
		}

		if ('1' == $_SERVER['HTTPS']) {
			return TRUE;
		}

	} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return TRUE;
	}

	return FALSE;
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
 */

// Displays footer on logout
function loggedoutFooter() {
	global $dofooter; // What was $cms_dir (12 Aug 2020) ?
	if ($dofooter) {
		$anchor = str_replace('http://', '', LOCATION);
		echo '<p><a href="' . LOCATION . '">' . $anchor . '</a> (logged out)</p>';
	}

	if (file_exists('./install.php') && !file_exists('./password.php')) { // For when not installed
		echo '<p>It seems superMicro CMS is not yet installed.</p>
<p><a href="./install.php">Install here&nbsp;&raquo;</a></p>';
	} else { // Is installed
		echo '<p><a href="https://patricktaylor.com/hash-sha256.php" target="_blank">Lost or forgotten password&nbsp;&raquo;</a></p>';
	}
}

/* --------------------------------------------------
 * backup.php
 */

// Creates a compressed zip file from array
// https://davidwalsh.name/create-zip-php
function zip($files = array(), $destination = '', $overwrite = FALSE) {

	// If the zip file already exists and overwrite is false, return false
	if (file_exists($destination) && !$overwrite) {
		return FALSE;
	}

	$valid_files = array();
	if (is_array($files)) { // If files were passed in
		foreach($files as $file) {
			if (file_exists($file) && is_file($file)) {
				$valid_files[] = $file;
			}
		}
	}

	// If files exist
	if (count($valid_files)) {

		// Create the archive
		$zip = new ZipArchive();
		if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== TRUE) {
			return FALSE;
		}

		// Add the files
		foreach ($valid_files as $file) {
			$zip->addFile($file, $file);
		}

		// Close the zip (done!)
		$zip->close();
		unset($zip);

		// Check to make sure the file exists
		return file_exists($destination);

	} else {
		return FALSE;
	}
}

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
		'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ő', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ű', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ő', 'ø', 'ù', 'ú', 'û', 'ü', 'ű', 'ý', 'þ', 'ÿ',
		/* Greek */
		'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'Ά', 'Έ', 'Ί', 'Ό', 'Ύ', 'Ή', 'Ώ', 'Ϊ', 'Ϋ', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'ά', 'έ', 'ί', 'ό', 'ύ', 'ή', 'ώ', 'ς', 'ϊ', 'ΰ', 'ϋ', 'ΐ',
		/* Turkish */
		'Ş', 'İ', 'Ç', 'Ü', 'Ö', 'Ğ', 'ş', 'ı', 'ç', 'ü', 'ö', 'ğ',
		/* Russian */
		'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
		/* Ukrainian */
		'Є', 'І', 'Ї', 'Ґ', 'є', 'і', 'ї', 'ґ',
		/* Czech */
		'Č', 'Ď', 'Ě', 'Ň', 'Ř', 'Š', 'Ť', 'Ů', 'Ž', 'č', 'ď', 'ě', 'ň', 'ř', 'š', 'ť', 'ů', 'ž',
		/* Polish */
		'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
		/* Latvian */
		'Ā', 'Č', 'Ē', 'Ģ', 'Ī', 'Ķ', 'Ļ', 'Ņ', 'Š', 'Ū', 'Ž', 'ā', 'č', 'ē', 'ģ', 'ī', 'ķ', 'ļ', 'ņ', 'š', 'ū', 'ž'
	);

	$allowed = implode("", $charArray);
	$str = preg_replace("/[^' . $allowed . ']+/u", "", $str); // Strip all characters except $allowed
	$str = preg_replace("/[[:space:]]+/", " ", $str); // Strip multiple spaces
	$str = str_replace("'", "\'", $str); // Escape single quotes for settings.php

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
		'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ő', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ű', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ő', 'ø', 'ù', 'ú', 'û', 'ü', 'ű', 'ý', 'þ', 'ÿ',
		/* Greek */
		'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'Ά', 'Έ', 'Ί', 'Ό', 'Ύ', 'Ή', 'Ώ', 'Ϊ', 'Ϋ', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'ά', 'έ', 'ί', 'ό', 'ύ', 'ή', 'ώ', 'ς', 'ϊ', 'ΰ', 'ϋ', 'ΐ',
		/* Turkish */
		'Ş', 'İ', 'Ç', 'Ü', 'Ö', 'Ğ', 'ş', 'ı', 'ç', 'ü', 'ö', 'ğ',
		/* Russian */
		'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
		/* Ukrainian */
		'Є', 'І', 'Ї', 'Ґ', 'є', 'і', 'ї', 'ґ',
		/* Czech */
		'Č', 'Ď', 'Ě', 'Ň', 'Ř', 'Š', 'Ť', 'Ů', 'Ž', 'č', 'ď', 'ě', 'ň', 'ř', 'š', 'ť', 'ů', 'ž',
		/* Polish */
		'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
		/* Latvian */
		'Ā', 'Č', 'Ē', 'Ģ', 'Ī', 'Ķ', 'Ļ', 'Ņ', 'Š', 'Ū', 'Ž', 'ā', 'č', 'ē', 'ģ', 'ī', 'ķ', 'ļ', 'ņ', 'š', 'ū', 'ž'
	);

	$allowed = implode("", $charArray);
	// Strip everything except $allowed
	$str = preg_replace('/[^' . $allowed . ']+/u', '', $str);
	$str = preg_replace('/[[:space:]]+/', ' ', $str);

	return $str;

}

/* --------------------------------------------------
 * install.php
 */

// For cookies
function randomString( $length ) {
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";

	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
}

?>