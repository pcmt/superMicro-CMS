<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 17 March 2023 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $problem = $new_filename = "";
$num = "0";
$thisAdmin = 'upload'; // For nav
$uploadfolder = '../uploads/';

require('./top.php');

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('uploads');
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
		h1('uploads');
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

	if (array_key_exists('submit1', $_POST)) { // Upload

		// Sanitize user input
		$filename = htmlspecialchars($_FILES["upload"]["name"]);
		$filetype = htmlspecialchars($_FILES["upload"]["type"]);
		$filesize = $_FILES["upload"]["size"];

		$random_string = randomString( 2 );

		// Validate uploaded file
		$allowed_extensions = array('pdf', 'doc', 'docx', 'rtf', 'zip', 'txt');
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if (!in_array(strtolower($ext), $allowed_extensions)) {
			$response = '<em>Error. Only filetypes .pdf, .doc, .docx, .rtf, .zip and .txt permitted.</em>';
		} elseif ($filesize > 2097152) {
			$response = "<em>File too large. Maximum file size is 2 megabytes.</em>";
		} elseif (!file_exists($_FILES['upload']['tmp_name']) || !is_uploaded_file($_FILES['upload']['tmp_name'])) {
			$response = "<em>Error uploading file.</em>";
		} else {
			// Use secure file paths
			$uploads_dir = realpath(__DIR__ . '/../uploads');
			$new_filename = $_FILES['upload']['name'] . $random_string . '.' . $ext;
			$target_file = $uploads_dir . '/' . $new_filename;

			if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) {
				$response = '<em>The file <b>' . $new_filename . '</b> has been uploaded.</em>';
			} else {
				$response = '<em>Error moving file.</em>';
			}
		}

	}

	if (array_key_exists('submit2', $_POST)) { // Delete

		$delete = str_replace('/', '', trim($_POST['delete'])); // No folders
		$_file = $uploadfolder . $delete;

		if (!file_exists($_file)) {
			$problem = TRUE;
			$response = '<em>Error: a file <b>' . $delete . '</b> does not exist.</em>';
		}

		if ($delete == '') {
			$problem = TRUE;
			$response = '<em>Error: no document filename was entered. Enter a filename.</em>';
		}

		if (!$problem && file_exists($_file)) {
			unlink($_file);
			$response = '<em>Gone. <b>' . $delete . '</b> was deleted.</em>';
		}

	}

?>

<h3>Upload or delete a PDF, Word, ZIP or .txt file</h3>

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

<h5>Choose a file on your device</h5>

<form enctype="multipart/form-data" action="<?php _print($self); ?>" method="post" onSubmit="displayLoading();">
<label>2 megabytes max.</label>
<input type="file" name="upload">
<input type="submit" name="submit1" class="images" value="Upload file">
</form>

<!-- display progress //-->

<img src="img/img-loading.gif" width="50" height="50" id="upload-progress" style="display:none">
<script type="text/javascript">
function displayLoading() {
	if (document.getElementById('upload-progress')) {
		document.getElementById('upload-progress').style.display='block';
	}
}
</script>

<!-- end display progress //-->

<hr>

<h3>Delete a file</h3>

<form action="<?php _print($self); ?>" method="post">

<label>Enter filename (eg: <b>myfile.pdf</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) _print($_POST['delete']); ?>" maxlength="60">
<input type="submit" name="submit2" class="images" value="Delete file">

</form>

<hr>

	<div id="list">

<h3>Existing files</h3>

<?php

	if (defined('LOCATION')) {

		$view = LOCATION . 'uploads/';

		_print_nlb('<ul>');
		_print_nlb('<li class="top"><em>Click to view or copy markup to paste in pages</em></li>');
		$dirname = "../uploads";
		if ($folder = @opendir($dirname)) {
			$filesArray = array();
			while (FALSE !== ($file = readdir($folder))) {
				if ((stristr($file, '.pdf')) || (stristr($file, '.doc')) || (stristr($file, '.docx')) || (stristr($file, '.rtf')) || (stristr($file, '.zip')) || (stristr($file, '.txt'))) {
					$filesArray[] = $file;
				}
			}

			natcasesort($filesArray);
			foreach ($filesArray as $file) {

				// For file just uploaded, otherwise no class
				if (isset($_POST['submit1']) && ($file == $new_filename)) {
					$mark = ' class="mark"';
				} else {
					$mark = NULL;
				}

				(int)$num = $num + 1;
				$num_padded = sprintf("[%03d]", $num);

				_print_nlb('<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> &#124; <i>copy &raquo;</i> <span>&lt;a href="./uploads/' . $file . '"&gt;' . $file . '&lt;/a&gt;</span></li>');
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