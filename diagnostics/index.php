<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 02 Sept 2024 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = $full_URL = $get_URL = "";

function is_definitely_ssl() {

	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		return true;
	}

	if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return true;
	}

	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		return true;
	}

	return 0;
}

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

	// Make it numerical
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

/* Get requested URL using ternary operator */
$get_URL = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$textfilename = 'test.txt';
if (file_exists($textfilename)) {
	unlink($textfilename);
}

if (isset($_POST['submit'])) {

	$textfilename = 'test.txt';
	if (!file_exists($textfilename)) {
		$fp = fopen('test.txt', 'w+') or die("Can't create file.");
		fwrite($fp, 'Some text.');
		fclose($fp);
		$message = '<span>File</span> ' . $textfilename . ' <span>created </span> ' . date ("F d Y H:i:s", filemtime($textfilename)) . ', <span>chmod =</span> ' . getchmod($textfilename);
	} else {
		$message = "<span>File</span> {$textfilename} <span>already exists</span>";
	}

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<title>PHP / server diagnostics</title>
<link rel="stylesheet" href="stylesheet.css" type="text/css">
<meta name="robots" content="noindex,nofollow">

</head>

<body>

<div id="wrap">

<h1>PHP / server diagnostics</h1>

<h4>(1) Test file:</h4>

<form action="" method="post" accept-charset="UTF-8">
<input type="submit" name="submit" value="Create file">
</form>

<?php

echo '<p>' . $message;
echo "</p>\n";

echo "<h4>(2) PHP info:</h4>\n";

echo '<p><span>PHP_VERSION = </span>' . PHP_VERSION . ' <span>(version server is using)</span>';
echo "</p>\n";

echo '<p><span>phpversion() = </span>' . phpversion() . ' <span>(version server is using)</span>';
echo "</p>\n";

echo '<p><span>PHP_VERSION_ID = </span>' . PHP_VERSION_ID . ' <span>(version server is using)</span>';
echo "</p>\n";

echo "<h4>(3) Absolute paths (forward slashes normally backslashed on Windows):</h4>\n";

echo "<p>DIR</p>\n";

echo '<p><span>__DIR__ = </span>' . __DIR__ . ' <span>(directory the file is in)</span>';
echo "</p>\n";

echo '<p><span>dirname(__DIR__) = </span>' . dirname(__DIR__) . ' <span>(parent directory of the file)</span>';
echo "</p>\n";

echo '<p><span>dirname(__DIR__, 1) = </span>' . dirname(__DIR__, 1) . ' <span>(also one directory higher)</span>';
echo "</p>\n";

echo '<p><span>dirname(dirname(__DIR__)) = </span>' . dirname(dirname(__DIR__)) . ' <span>(two directories higher)</span>';
echo "</p>\n";

echo "<p>FILE</p>\n";

echo '<p><span>__FILE__ = </span>' . __FILE__ . ' <span>(actual filename)</span>';
echo "</p>\n";

echo '<p><span>dirname(__FILE__) = </span>' . dirname(__FILE__) . ' <span>(directory the file is in)</span>';
echo "</p>\n";

echo '<p><span>dirname(__FILE__) unbackslashed = </span>' . str_replace('\\', '/', dirname(__FILE__)) . ' <span>(directory the file is in)</span>';
echo "</p>\n";

echo "<h4>(4) Basename (no paths)</h4>\n";

echo '<p><span>basename(__FILE__) = </span>' . basename(__FILE__) . ' <span>(current file only)</span>';
echo "</p>\n";

echo '<p><span>basename(dirname(__FILE__)) = </span>' . basename(dirname(__FILE__)) . ' <span>(current directory of the file only)</span>';
echo "</p>\n";

echo '<p><span>basename(getcwd()) = </span>' . basename(getcwd()) . ' <span>(current directory of the file only)</span>';
echo "</p>\n";

echo '<p><span>basename(realpath(dirname(__FILE__))) = </span>' . basename(realpath(dirname(__FILE__))) . ' <span>(current directory of the file only)</span>';
echo "</p>\n";

// Get the directory where the script is located
$currentDir = dirname(__FILE__);

// Get the parent directory's full path
$parentDir = dirname($currentDir);

$parentDirName = basename($parentDir);

echo '<p><span>basename($parentDir) = </span>' . basename($parentDir) . ' <span>(directory above the directory of the file - see the script)</span>';
echo "</p>\n";

echo '<p><span>basename(dirname(dirname(__FILE__))) =  </span>' . basename(dirname(dirname(__FILE__))) . ' <span>(directory above the directory of the file)</span>';
echo "</p>\n";

echo "<h4>(5) Superglobal variables:</h4>\n";

echo '<p><span>$_SERVER[\'SERVER_NAME\'] = </span>' . $_SERVER['SERVER_NAME'] . ' <span>(name of the host server)</span>';
echo "</p>\n";

echo '<p><span>$_SERVER[\'SERVER_SOFTWARE\'] = </span>' . $_SERVER['SERVER_SOFTWARE'] . ' <span>(server identification string)</span>';
echo "</p>\n";

echo '<p><span>$_SERVER[\'HTTP_HOST\'] = </span>' . $_SERVER['HTTP_HOST'] . ' <span>(host name)</span>';
echo "</p>\n";

echo '<p><span>$_SERVER[\'SCRIPT_FILENAME\'] = </span>' . $_SERVER['SCRIPT_FILENAME'] . ' <span>(absolute pathname of the currently executing script)</span>';
echo "</p>\n";

echo '<p><span>$_SERVER[\'HTTPS\'] = </span>' . $_SERVER['HTTPS'] . ' <span>(HTTPS status)</span>';
echo "</p>\n";

echo '<p><span>$_SERVER[\'SERVER_PORT\'] = </span>' . $_SERVER['SERVER_PORT'] . ' <span>(port on the server machine, 443 = secure, 80 = non-secure)</span>';
echo "</p>\n";

echo "<h4>(6) This page:</h4>\n";

/* Added 21 Aug 2020 */
echo '<p><span>$get_URL = </span>' . $get_URL . ' <span>(web address of this page, as requested)</span>';
echo "</p>\n";

echo "\n";

echo "<h4>(7) Operating system:</h4>\n";

$opSystem = (strtoupper(PHP_OS));

echo '<p><span>PHP_OS = </span>' . $opSystem . ' <span>(operating system)</span>';
echo "</p>\n";

echo '<p><span>SSL = </span>' . is_definitely_ssl($_SERVER['PHP_SELF']) . ' <span>(SSL status)</span>';
echo "</p>\n";

echo "\n";

?>

</div>

</body>

</html>

