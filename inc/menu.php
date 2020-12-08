<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 05 Dec 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to menu.php.');
}

if (defined('SITE_NAME') && (strlen(SITE_NAME) > 0)) { // &nbsp; nudges right for centred letter-spacing
	_print('<p id="sitename">&nbsp;<a href="' . LOCATION . '" title="' . SITE_NAME . '">' . SITE_NAME . "</a></p>");
	_print("\n\n");
}

?>
	<nav>

<ul>
<?php

if ($pageID == 'index') {
	_print('<li class="home"><strong>' . HOME_LINK . "</strong></li>");
} else {
	_print('<li class="home"><a href="' . LOCATION . '" title="' . HOME_LINK . '">' . HOME_LINK . '</a></li>');
}

// See also settings.php which defines CONTACT_MENU text string as set
// by user in setup.php (empty string removes menu contact page link)

// Load the menu
$inmenu = INC . 'inmenu.txt';

if (file_exists($inmenu)) {
	$inmenuArray = array();
	$inmenuArray = file($inmenu);
	if (ALPHABETICAL) {
		natcasesort($inmenuArray);
	}

/*
NOTES
$pageID is the filename of the current URL (eg: example-page.php)
$line is the text for each line in 'inmenu.txt' (eg: example-page)
IMPORTANT: $thisPage must be able to match $link
$anchor is a capitalised 'spaced' version of $line (eg: Example Page)
or, if used, [anchor text] as from v7.12
*/

	foreach ($inmenuArray as $line) {
		$line = trim($line);

		if (strlen($line) == 0) { // Fallback attempt to remove empty lines
			continue; // Skip
		}

		if ($line[0] == '#') { // Ignore lines starting with '#'
			continue; // Skip
		}

		if ($rewrite) { // From top.php
			$link = $line;
		} else {
			$link = $line . '.php';
		}

		// Remove any [anchor text]
		$link = trim(preg_replace('/\[[^\]]*]/', '', $link));

		// Get anchor text only
		$anchor = substr($line, ($pos = strpos($line, '[')) !== false ? $pos + 1 : 0);
		$anchor = str_replace(array('-', '_', ']'), ' ', $anchor);
		$anchor = stripslashes(ucwords($anchor));
		$anchor = trim($anchor);

		if (($pageID == $link) || (($pageID . '.php') == $link)) {
			_print('<li><strong>' . $anchor . '</strong></li>');
		} else {
			_print('<li><a href="' . LOCATION . $link . '" title="' . $anchor . '">' . $anchor . '</a></li>');
		}
	}

} else { // inmenu.txt not found
	_print("<li><strong>Menu not found</strong></li>\n");
}

if (file_exists('s.php')) { // Nothing if there isn't a file
	if ($pageID == 's') { // In search page, so must be on it
			_print('<li><strong>' . TEXT16 . '</strong></li>');
	} else { // Not on search page
		if ($rewrite) {
			_print('<li><a href="' . LOCATION . 's" title="' . TEXT16 . '">' . TEXT16 . '</a></li>');
		} else { // Not apache
			_print('<li><a href="' . LOCATION . 's.php" title="' . TEXT16 . '">' . TEXT16 . '</a></li>');
		}
	}
}

if (file_exists('e.php')) { // Nothing if there isn't a file
	if (defined('CONTACT_MENU') && (CONTACT_MENU != '')) { // Nothing if blank in setup
		if ($pageID == 'e') { // In contact page, so must be on it
			_print('<li><strong>' . CONTACT_MENU . '</strong></li>');
		} else { // Not on contact page
			if ($rewrite) {
				_print('<li><a href="' . LOCATION . 'e" title="' . CONTACT_MENU . '">' . CONTACT_MENU . '</a></li>');
			} else { // Not apache
				_print('<li><a href="' . LOCATION . 'e.php" title="' . CONTACT_MENU . '">' . CONTACT_MENU . '</a></li>');
			}
		}
	}
}

if ($admin) {
	if ($pageID == 'preview') {
		$page = ""; /* 03 Aug 20 edit ($page) to avoid undefined var error */
		if (isset($_GET['page']) && isset($page)) { /* 03 Aug 20 edit (page) */
			$page = trim($_GET['page']);
		}
		_print('<li class="admin"><a href="' . LOCATION . ADMIN . '/index.php?page=' . $page . '&mode=preview" title="' . TEXT47 . '">' . TEXT47 . '</a></li>');
	} else {
		$_textfile = str_replace('.txt', '', $_textfile);
		_print('<li class="admin"><a href="' . LOCATION . ADMIN . '/index.php?page=' . $_textfile . '&mode=normal" title="' . TEXT47 . '">' . TEXT47 . '</a></li>');
	}
}

_print("\n");

?>
</ul>

	</nav>
