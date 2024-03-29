<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 April 2023 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $response1 = $display = $delete = $_file = $dimensions = $problem = "";
$num = "0";
$thisAdmin = 'images'; // For nav
$imgfolder = '../img/';
$excludedFiles = ['bg_footer.gif', 'bg_footer_monochrome.gif', 'bg-dots1.gif', 'loader.gif', 'og.jpg'];

require('./top.php');

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('images');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php echo LOCATION; ?>favicon.ico">
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
		_print("Error: the file '/admin/login-form.php' does not exist. It must be installed.");
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
		h1('images');
	} else {
		_print('Install the latest version of functions.php');
	}

?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error: the file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

	if (array_key_exists('submit1', $_POST)) { // Upload

		$filename = trim($_POST['filename']);

		if (strlen($filename) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter a new filename. Start again, selecting an image and entering a new filename.</em>";
		}

		if (preg_match("/[^~A-Za-z0-9_\-]/", $filename)) {
			$problem = TRUE;
			$response = '<em>The new filename can contain only letters, numbers, hypens, underscores, and tildes. Start again.</em>';
		}

		if (!$problem) {

			$filetype = exif_imagetype($_FILES['upload']['tmp_name']);
			$allowed = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP);
			$size = getimagesize($_FILES['upload']['tmp_name']);

			if (in_array($filetype, $allowed)) {
				$pieces = explode('.', $_FILES['upload']['name']);
				$extension = strtolower(array_pop($pieces));
				$extensions = array('jpg', 'jpeg', 'gif', 'png', 'webp');
				if ((in_array($extension, $extensions)) && $size) {
					$filename = $filename . '.' . $extension;
					if (move_uploaded_file($_FILES['upload']['tmp_name'], "../img/{$filename}")) {
						$response = '<em>The file named <b>' . $filename . '</b> has been uploaded.</em>';
						$display = '<img src="../img/' . $filename . '" class="upload" alt="">';
					} else {
						$response = '<em>The file could not be moved.</em>';
					}
				} else {
					$response = '<em>Not uploaded. The file type must be .jpg, .jpeg, .gif, or .png.</em>';
				}
			} else {
				$response = '<em>Not uploaded. The file you renamed <b>' . $filename . '</b> was not a .jpg, .jpeg, .gif, or .png.</em>';
			}
		}
	}

	if (array_key_exists('submit2', $_POST)) { // Delete

		$delete = str_replace('/', '', trim($_POST['delete'])); // No folders
		$_file = $imgfolder . $delete;

		if (!file_exists($_file)) {
			$problem = TRUE;
			$response = '<em>Error: an image <b>' . $delete . '</b> does not exist.</em>';
		}

		if ($delete == '') {
			$problem = TRUE;
			$response = '<em>Error: no image filename was entered. Enter a filename.</em>';
		}

		if (in_array($delete, $excludedFiles)) {
			$problem = TRUE;
			$response = "<em>The default images can't be deleted. Maybe upload a new one (<b>og.jpg</b> must be 200 pixels square).</em>";
		}

		if (!$problem && file_exists($_file)) {
			unlink($_file);
			$response = '<em>Gone. <b>' . $delete . '</b> was deleted.</em>';
		}

	}

?>

<h3>Upload or delete an image [ <a href="./video.php" title="Video files">video files</a> ] [ <a href="./upload.php" title="Other file types">other file types</a> ]</h3>

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

<h5>Choose an image on your device</h5>

<form enctype="multipart/form-data" action="<?php echo $self; ?>" method="post" onSubmit="displayLoading();">

<input type="file" name="upload">
<label>Choose a name for the upload file (eg: <b>image1</b> - omit file extension):</label>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="text" size="40" name="filename" value="<?php

	if (isset($_POST['submit1'])) {
		_print($_POST['filename']);
	}

?>" maxlength="60">
<input type="submit" name="submit1" class="images" value="Upload image">

</form>

<!-- display image //-->

<img src="img/img-loading.gif" width="50" height="50" id="upload-progress" style="display: none;">
<script type="text/javascript">
function displayLoading() {
	if (document.getElementById('upload-progress')) {
		document.getElementById('upload-progress').style.display='block';
	}
}
</script>

<?php echo $display; ?>

<!-- end display image //-->

<hr>

<h3>Delete an image</h3>

<form action="<?php _print($self); ?>" method="post">
<label>Enter filename (eg: <b>image1.jpg</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) _print($_POST['delete']); ?>" maxlength="60">
<input type="submit" name="submit2" class="images" value="Delete image">
</form>

<hr>

	<div id="list">

<h3>Existing image files</h3>

<?php

	if (defined('LOCATION')) {

		$view = LOCATION . 'img/';

		_print_nlb('<ul>');
		_print_nlb('<li class="top"><em>Click to view or copy markup to paste in pages</em></li>');
		$dirname = "../img";
		if ($folder = @opendir($dirname)) {
			$filesArray = array();
			$validExtensions = array('jpg', 'jpeg', 'gif', 'png', 'webp');
			while (FALSE !== ($file = readdir($folder))) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if (in_array($extension, $validExtensions)) {
					$filesArray[] = $file;
				}
			}

			natcasesort($filesArray);
			foreach ($filesArray as $file) {

				if (in_array($file, $excludedFiles)) {
					continue;
				}

				$image = "../img/{$file}";
				$dimensions = getimagesize($image);
				$size = filesize($image) / 1000; // kilobytes
				$kb = number_format($size); // Whole numbers

				// For image just uploaded, otherwise no class
				if (isset($_POST['submit1']) && ($file == $filename)) {
					$mark = ' class="mark"';
				} else {
					$mark = NULL;
				}

				$num = $num + 1;
				$num_padded = sprintf("[%03d]", $num);

				_print_nlb('<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> &#124; <i>copy &raquo;</i> <span>&lt;img src="img/' . $file . '" ' . $dimensions[3] . ' alt=""&gt;</span> ' . $kb . ' kB</li>');
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