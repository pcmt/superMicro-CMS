<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 Dec 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to stylesheets.php.');
}

// Get absolute path to /css/ folder
$__dir = str_replace('\\', '/', dirname(__FILE__));
$__css = str_replace('inc', 'css', $__dir) . '/';

if (defined('FONT_TYPE')) {

	if (FONT_TYPE == 'hosted') {
		_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-h.css">');
	}

	if (FONT_TYPE == 'google') {
		_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-g.css">');
	}

} else {
	_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-h.css">');
}

_print_nla('<link rel="stylesheet" media="screen and (min-width: 798px)" href="' . LOCATION . 'css/stylesheet.css">');
_print_nla('<link rel="stylesheet" media="screen and (max-width: 797px)" href="' . LOCATION . 'css/mobile.css">');
_print_nla('<link rel="stylesheet" media="screen" href="' . LOCATION . 'css/extra.css">');

?>
