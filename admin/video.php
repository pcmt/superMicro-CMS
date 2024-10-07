<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 01 July 2024 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $problem = $new_filename = "";

$num = "0";
$thisAdmin = 'video'; // For nav
$videofolder = '../video/';

include('./top.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('video'); ?></title>
<?php includeFileIfExists('./icons.php'); ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	includeFileIfExists('./login-form.php');


} elseif ($login) {

/* -------------------------------------------------- */
// Logged in

?>

<div id="o"><div id="wrap">

<h1><?php h1('video'); ?></h1>

<?php

	includeFileIfExists('./nav.php');


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

<h3>Uploaded video/audio files [ <a href="./images.php" title="Image files">image files</a> ] [ <a href="./upload.php" title="Other file types">other file types</a> ]</h3>

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

<p><strong>Qwwwik</strong> doesn't upload audio (.mp3) or video (.mp4) files due to server filesize limits.</p>

<h5>Delete an .mp3 or .mp4 file</h5>

<form action="<?php _print($self); ?>" method="post">

<label>Enter filename (eg: <b>myfile.mp4</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) _print($_POST['delete']); ?>" maxlength="60">
<input type="submit" name="submit2" class="stacked" value="Delete file">

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
				if ((stristr($file, '.mp4')) || (stristr($file, '.mp3'))) {
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
					if (isset($_POST['submit1']) && ($file === $new_filename)) {
						$mark = ' class="mark"';
					} else {
						$mark = NULL;
					}

					(int)$num = $num + 1;
					$num_padded = sprintf("[%03d]", $num);
					_print_nlb('<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> ' . $kb . ' kB<br><i>copy &raquo;</i><br>
<span>&lt;div class=&quot;fw1&quot;&gt;<br>&lt;div class=&quot;video&quot;&gt;<br>&lt;video width=&quot;720&quot; height=&quot;auto&quot; autoplay controls loop&gt;<br>&lt;source src=&quot;./video/' . $file . '&quot; type=&quot;video/mp4&quot;&gt;<br>&lt;/video&gt;<br>&lt;/div&gt;<br>&lt;/div&gt;</span></li>');
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

	includeFileIfExists('./footer.php');

} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>
</div></div>

</body>
</html>