<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 April 2023 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $problem = $new_filename = "";
$num = "0";
$thisAdmin = 'video'; // For nav
$videofolder = '../video/';

require('./top.php');

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('video');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="stylesheet.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	if (!file_exists('./login-form.php')) {
		_print("Error. The file '/admin/login-form.php' does not exist. It must be installed.");
		exit();
	} else {
		require('./login-form.php');
	}

} elseif ($login) {

/* -------------------------------------------------- */
// Logged in

?>

<div id="wrap">

<h1><?php

	if (function_exists('h1')) {
		h1('video');
	} else {
		_print('Install the latest version of functions.php');
	}

?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error. The file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

	if (array_key_exists('submit2', $_POST)) { // Delete

		$delete = str_replace('/', '', trim($_POST['delete'])); // No folders
		$_file = $videofolder . $delete;

		if (!file_exists($_file)) {
			$problem = TRUE;
			$response = '<em>Error: a video <b>' . $delete . '</b> does not exist.</em>';
		}

		if ($delete == '') {
			$problem = TRUE;
			$response = '<em>Error: no video filename was entered. Enter a filename.</em>';
		}

		if (!$problem && file_exists($_file)) {
			unlink($_file);
			$response = '<em>Gone. <b>' . $delete . '</b> was deleted.</em>';
		}

	}

?>

<h3>Uploaded video files [ <a href="./images.php" title="Image files">image files</a> ] [ <a href="./upload.php" title="Other file types">other file types</a> ]</h3>

	<div id="response">

<?php

	_print('<p><span class="padded-multiline">');
	if (!$response) {
		_print('<em>No action requested.</em>');
	} else {
		_print($response);
	}
	_print('</span></p>');

?>

	</div>

<h5>Delete a video file</h5>

<form action="<?php _print($self); ?>" method="post">

<label>Enter filename (eg: <b>myfile.mp4</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) _print($_POST['delete']); ?>" maxlength="60">
<input type="submit" name="submit2" class="images" value="Delete file">

</form>

<hr>

	<div id="list">

<h3>Existing video files</h3>

<?php

	if (defined('LOCATION')) {

		$view = LOCATION . 'video/';

		_print_nlb('<ul>');
		_print_nlb('<li class="top"><em>Click to view or copy markup to paste in pages</em></li>');
		$dirname = "../video";
		if ($folder = @opendir($dirname)) {
			$filesArray = array();
			while (FALSE !== ($file = readdir($folder))) {
				if ((stristr($file, '.mp4')) || (stristr($file, '.mp4'))) {
					$filesArray[] = $file;
				}
			}

			if (count($filesArray) > 0) {
				natcasesort($filesArray);
				foreach ($filesArray as $file) {

					$video = "../video/{$file}";
					$size = filesize($video) / 1000; // kilobytes
					$kb = number_format($size); // Whole numbers

					// For file just uploaded, otherwise no class
					if (isset($_POST['submit1']) && ($file == $new_filename)) {
						$mark = ' class="mark"';
					} else {
						$mark = NULL;
					}

					(int)$num = $num + 1;
					$num_padded = sprintf("[%03d]", $num);
					_print_nlb('<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> ' . $kb . ' kB<br><i>copy &raquo;</i><br>
<span>&lt;div class=&quot;video&quot;&gt;<br>&lt;video class=&quot;classname&quot; width=&quot;740&quot; height=&quot;auto&quot; autoplay controls loop&gt;<br>&lt;source src=&quot;./video/' . $file . '&quot; type=&quot;video/mp4&quot;&gt;<br>&lt;/video&gt;<br>&lt;/div&gt;</span></li>');
				}
			} else {
				_print_nlb('<li>No files.</li>');
			}

			closedir($folder);
			_print_nlb('</ul>');
		}

	}

?>

	</div>

<?php

	include('./footer.php');

} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>

</body>
</html>