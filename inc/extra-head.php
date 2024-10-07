<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 03 August 2024 */
/* $canonical from top.php */

if (!defined('ACCESS')) {
	die('Direct access not permitted to extra-head.php');
}

_print("<!-- extra-head //-->");

// Home page
if ($pageID == 'index') {
	$type = 'website';
} else {
	$type = 'article';
}

// Make single quotes
$titletag = str_replace("\"", "'", $titletag);

if (defined('SITE_NAME')) {
	_print_nla('<meta property="og:site_name" content="' . SITE_NAME . '">');
}
_print('
<meta property="og:url" content="' . $canonical . '">
<meta property="og:title" content="' . $titletag . '">
<meta property="og:type" content="' . $type . '">');
if ($description && strlen($description) > 12) { _print('
<meta property="og:description" content="' . $description . '">'
); }
_print('
<meta property="og:image" content="' . LOCATION . 'img/og.png">
<meta property="og:image:secure_url" content="' . LOCATION . 'img/og.png">
<meta property="og:image:width" content="200">
<meta property="og:image:height" content="200">
<!-- end extra-head //-->'
);

?>
