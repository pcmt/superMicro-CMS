<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 25 June 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to list.php');
}

if (defined('LOCATION') && defined('ADMIN')) {

	// Declare variables
	$mark = NULL;

?>

<h3>Existing pages</h3>

<ul>
<li class="top"><em>Click to view or edit page</em></li>
<?php

	$dirname = '../';

	if ($folder = @opendir($dirname)) {

		// Put all .php files into array
		$filesArray = array();
		while (false !== ($file = readdir($folder))) {
			if (strpos($file, '.php') !== FALSE) {
				$filesArray[] = $file;
			}
		}

		$num = 0;

		// Loop through the rest of the files
		natcasesort($filesArray);
		foreach ($filesArray as $file) {

			// Check that corresponding .txt file exists
			$textfile = str_replace('.php', '.txt', $file);
			if (!file_exists("../pages/{$textfile}")) {
				continue;
			}

			if ($textfile == 'preview.txt') { // Skip
				continue;
			}

			$pagename = str_replace('.php', '', $file);

			if (isset($filetitle) && $pagename == $filetitle) {
				$mark = ' class="mark"'; // To highlight the page on
			} else {
				$mark = NULL;
			}

			$anchor = str_replace('.php', '', $file);
			if ($pagename == 'index') {
				$pagename = str_replace('index', '', $pagename);
			}

			if (!$rewrite && ($file != 'index.php')) {
				$link = $file;
			} else {
				$link = $pagename;
			}

			$num = $num + 1;
			$num_padded = sprintf("[%03d]", $num);

			echo "<li{$mark}>";
			echo $num_padded . ' Page: <a href="'. LOCATION . $link . '" title="' . $anchor . '">' . $anchor . '</a>';

			if ($file != 'index.php') { // Edit link
				echo ' &#124; <span><a href="'. LOCATION . ADMIN . '/index.php?page=' . $pagename . '" title="Edit page">edit page</a></span>';
			} else {
				echo ' &#124; <span><a href="'. LOCATION . ADMIN . '/index.php?page=' . $anchor . '" title="Edit page">edit page</a></span>';
			}
			echo "</li>\n";
		}

	closedir($folder);

	}

}

?>
</ul>
