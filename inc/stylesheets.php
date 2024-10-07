<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 June 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to stylesheets.php');
}

$link_colour = $ver = $parameter = '';

if (defined('TIMESTAMP')) {
	$ver = str_replace('.', '', VERSION);
	$parameter = '?v=' . $ver .'&' . TIMESTAMP;
} else {
	$parameter = '?v=' . $ver;
}

if (defined('FONT_TYPE')) {

	if (FONT_TYPE === 'hosted') {
		_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-h.css' . $parameter . '">');
	}

	if (FONT_TYPE === 'google') {
		_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-g.css' . $parameter . '">');
	}

} else {
	_print('<link rel="stylesheet" media="all" href="' . LOCATION . 'css/common-h.css' . $parameter . '">');
}

_print_nla('<link rel="stylesheet" media="screen and (min-width: 798px)" href="' . LOCATION . 'css/stylesheet.css' . $parameter . '">');
_print_nla('<link rel="stylesheet" media="screen and (max-width: 797px)" href="' . LOCATION . 'css/mobile.css' . $parameter . '">');

if (defined('FOOTER_OPTION') && FOOTER_OPTION) {
_print_nla('<link rel="stylesheet" media="screen" href="' . LOCATION . 'css/footer.css">');
}

_print_nla('<link rel="stylesheet" media="screen" href="' . LOCATION . 'css/extra.css' . $parameter . '">');

if (defined('LINK_COLOUR') && LINK_COLOUR) {
	$link_colour = LINK_COLOUR;
	_print_nlab("<style>
:root { --link: {$link_colour}; } /* Overwrite default */
</style>");
}

?>
