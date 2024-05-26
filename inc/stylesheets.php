<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 21 May 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to stylesheets.php');
}

$col = '';

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

if (defined('LINK_COLOUR') && LINK_COLOUR) {
	$col = LINK_COLOUR;
	_print_nlab("<style>
:root { --link: {$col}; } /* Overwrite default */
</style>");
}

?>
