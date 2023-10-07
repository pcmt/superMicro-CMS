<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Oct 2023 */
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

// Replace quotes etc
$titletag = str_replace(array("\"", "'"), "&#34;", $titletag);

if (defined('SITE_NAME')) {
	_print_nla('<meta property="og:site_name" content="' . SITE_NAME . '">');
}
_print('
<meta property="og:url" content="' . $canonical . '">
<meta property="og:title" content="' . $titletag . '">
<meta property="og:type" content="' . $type . '">
<meta property="og:image" content="' . LOCATION . 'img/og.jpg">
<meta property="og:image:secure_url" content="' . LOCATION . 'img/og.jpg">
<meta property="og:image:width" content="200">
<meta property="og:image:height" content="200">
<!-- end extra-head //-->'
);

?>