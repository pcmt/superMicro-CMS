<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 Nov 2020 */

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Declare variables
$num_pages = $num_excluded = $terms = $search_time = $pagestime = "";

if (isset($_COOKIE['adminlink'])) {
	$admin = TRUE;
} else {
	$admin = FALSE;
}

$pageID = 's'; // For menu.php (normally comes from html.php)

// define('SEARCHPAGE', TRUE); // For the menu (no longer required since 26 Nov 20)
define('ACCESS', TRUE);

// See footer
$time = explode(' ', microtime());
$starttime = $time[1] + $time[0];

// Define absolute path to /inc/ folder (as in html.php)
$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/inc/';
define('INC', $_inc);

if (file_exists(INC . 'error-reporting.php')) {
	require(INC . 'error-reporting.php');
} else {
	echo 'Error. Please install the file /inc/error-reporting.php';
	exit();
}

if (file_exists(INC . 'lang.php')) { // <- here
	require(INC . 'lang.php');
} else {
	echo 'Error. Please install the file /inc/lang.php';
	exit();
}

// Next bit in top.php but not loaded here
if ((APACHE == FALSE) || (!file_exists('./.htaccess'))) {
	$rewrite = FALSE;
} else {
	$rewrite = TRUE; // When not WINDOWS and .htaccess exists
}

if (isset($_POST['terms'])) { // Searches are only from this page
	$terms = trim($_POST['terms']);
	// Replace multiple whitespaces with one
	$terms = preg_replace('/\s+/', ' ', $terms);
	// Clean
	$terms = strip_tags($terms);
}

?>
<!DOCTYPE html>
<html<?php

if (defined('LANG_ATTR')) {
	_print(' lang="' . LANG_ATTR . '"');
}

?>>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Search</title>
<link rel="shortcut icon" href="favicon.ico">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<?php

include(INC . 'stylesheets.php');
_print("\n");

?>

</head>
<body>

<div id="wrap">

<?php include(INC . 'menu.php'); ?>

	<main id="content">

		<div id="s">

<?php

if (!isset($_POST['terms'])) { // No search submitted

// Show H1 and form
?>

<h1><?php _print(TEXT16); ?></h1>

			<div class="searchform">

<p><?php _print(TEXT41); ?></p>

<form class="search" action="" method="post">
<input type="search" name="terms" placeholder="<?php _print(TEXT16); ?>" required>
<input type="submit" name="submit" class="submit" value="<?php _print(TEXT16); ?>">
</form>

			</div>

<?php

} else { // Search was submitted

	if (strlen($terms) > 2) { // Want at least two characters

		$result = FALSE; // Declare variable but don't print yet?

		_print('<h1>' . TEXT36 . ' "<span>' . $terms . '</span>"</h1>');
		_print("\n\n");

?>

			<div class="searchform">

<form class="search" action="" method="post">
<?php if ($terms) { ?>
<input type="search" name="terms" value="<?php _print($terms); ?>">
<?php } else { ?>
<input type="search" name="terms" placeholder="<?php _print(TEXT16); ?>" required>
<?php } ?>
<input type="submit" name="submit" class="submit" value="<?php _print(TEXT16); ?>">
</form>

			</div>

<?php

		_print('<ul class="listing">');
		_print("\n");

		/* -------------------------------------------------- */
		// Pages

		$p_dir = './pages';

		/* If the pages folder was opened =================== */
		if ($folder = opendir($p_dir)) {

			$s_time = explode(' ', microtime());
			$start_search = $s_time[1] + $s_time[0];

			// Get text files into array
			$filesArray = array();
			while (FALSE !== ($files = readdir($folder))) {
				if ('.' === $files) continue;
				if ('..' === $files) continue;
				if (strpos($files, '.txt') !== FALSE) {
					$filesArray[] = $files;
				}
			}

			// asort($filesArray);
			$num_pages = count($filesArray);

			// Initiate array of password-protected pages
			$excluded = array();

			// Loop through /pages/ $filesArray
			foreach ($filesArray as $p_file) {

				// Get title: array everything in the text file
				$text = file('./pages/' . $p_file); // File location
				// Extract the first line
				$lineone = trim(array_shift($text));
				// Extract the second line
				$title = trim(array_shift($text));
				$title = str_replace('<br>', ' ', $title);
				// Put the remaining lines back into string
				$text = trim(implode('', $text));
				// Add back title
				$alltext = $title . ' ' . $text;
				// Ignore HTML markup
				$alltext = strip_tags($alltext);
				// Remove newlines
				$alltext = str_replace("\n", " ", $alltext);

				// Output links for matches
				if (preg_match("/~~/", $lineone)) { // Check if password-protected or excluded

					$excluded[] = $p_file; // Add to array
					$num_excluded = count($excluded);

				} elseif ($count = preg_match_all("/((\S*\s){0,2})(\b$terms\b)((\s?\S*){0,2})/ui", $alltext, $matches, PREG_SET_ORDER)) { // {0,3})/ui changed to {0,2} 11 Feb 2020 (faster)
					// (\s\S*) = greedy, (\s?\S*) ungreedy

					$result = TRUE;

					// Get link, title
					$p_url = str_replace('.txt', '', $p_file);
					$p_url = preg_replace('/\Aindex\Z/', '', $p_url);

					if ($count < 2) { // Number of matches
						$w = TEXT37;
					} else {
						$w = TEXT38;
					}

				_print("<li>");

					if (!$rewrite) { // Windows
						if (!$p_url == '') {
							$suffix = '.php';
						} else { // Linux
							unset($suffix);
						}
						_print('<a href="' . LOCATION . $p_url . $suffix . '">' . $title . '</a> <span>(<b>' . $count . '</b> ' . $w . ')</span>');
					} else {
						_print('<a href="' . LOCATION . $p_url . '">' . $title . '</a> <span>(<b>' . $count . '</b> ' . $w . ')</span>');
					}

					// Add snippet
					$limit = 3; // 3 snippets only

					for ($i=0; $i<$limit; $i++) { // Loop through matches
						if (!empty($matches[$i][3])) { // The search terms
							// 2 words / search terms bold / 3 words (from preg_match_all)
							$result = $matches[$i][1] . '<strong>' . $matches[$i][3] . '</strong>' . $matches[$i][4];
							_print(' <span class="faded">...</span> <em>' . $result . '</em>');
						}
					}

					_print(' <span class="faded">...</span></li>');
					_print("\n");

					flush();
				}
			} // End foreach

			unset($filesArray);
			closedir($folder);

			$s_time = explode(' ', microtime());
			$end_search = $s_time[1] + $s_time[0];

			// Measure the interval by subtracting
			$search_time = ($end_search - $start_search);

		} /* End if the pages folder was opened */

		if (!$result) { // Print an <li></li> if nothing was found
			_print("<li>" . TEXT40 . " '<strong><span>{$terms}</span></strong>'</li>\n");
		}

		_print("</ul>\n"); // End the list

	} else { // Prints "Nothing found" if search string less then 3 characters

		_print('<h1>No ' . TEXT38 . '</h1>');
		_print("\n");

// Here's the form again (has to be after each H1)
?>

			<div class="searchform">

<?php _print("<p>" . TEXT41 . "</p>\n"); ?>

<form class="search" action="" method="post">
<?php if ($terms) { ?>
<input type="search" name="terms" value="<?php _print($terms); ?>">
<?php } else { ?>
<input type="search" name="terms" placeholder="<?php _print(TEXT16); ?>" required>
<?php } ?>
<input type="submit" name="submit" class="submit" value="<?php _print(TEXT16); ?>">
</form>

			</div>

<?php

	} // End of "Nothing found" for less than 3 characters

	// Subtract password-protected pages
	$num_pages = ($num_pages - $num_excluded);

	// Output search time
	_print('<p class="meta">');
	$search_time = number_format((float)$search_time, 4, '.', '');
	_print($num_pages . ' ' . TEXT42 . ' ' . $search_time . ' ' . TEXT43);
	_print('.</p>');

	_print("\n\n");

} // End if search submitted

// No search submitted

?>

		</div>

	</main>

<?php

include(INC . 'footer.php');
if (file_exists(INC . 'tracking.php')) { // Optional
	include(INC . 'tracking.php');
}

?>

</div>

</body>
</html>
