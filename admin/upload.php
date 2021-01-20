<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 20 Jan 2021 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $problem = "";
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

		// Because it's a new folder
		if (file_exists('../uploads') && is_dir('../uploads')) {
			@chmod($uploadfolder, 0775);
		} else {
			$problem = TRUE;
			$response = "<em>Error. The folder <b>/uploads/</b> does not exist. Please install the latest version of superMicro CMS.</em>";
		}

		$name = trim($_POST['filename']);

		if (strlen($name) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter a new filename. Start again, entering a new filename.</em>";
		}

		if (preg_match("/[^~A-Za-z0-9_\-]/", $name)) {
			$problem = TRUE;
			$response = '<em>The new filename can contain only letters, numbers, hypens, underscores, and tildes. Start again.</em>';
		}

		$filename = $_FILES["upload"]["name"];
		$filetype = $_FILES["upload"]["type"];
		$filesize = $_FILES["upload"]["size"];

		if ($_FILES["upload"]["size"] > 2097152) { // 2 megabtytes
			$problem = TRUE;
			$response = "<em>File to large. Maximum file size is 2 megabtyes.</em>";
		}

		if (!$problem) {

			$allowed = array('PDF', 'pdf', 'doc', 'docx', 'rtf', 'ZIP', 'zip', 'txt');

			// Verify file extension
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			// echo '<br>File extension = ' . $ext; // Testing only
			if ((in_array($ext, $allowed)) && $filesize) {
				if (move_uploaded_file($_FILES['upload']['tmp_name'], "../uploads/{$name}.{$ext}")) {
					$response = '<em>The file <b>' . $name . '.' . $ext . '</b> has been uploaded.</em>';
				} else {
					$response = '<em>The file could not be moved.</em>';
				}
			} else {
				$response = '<em>Error. Only filetypes .pdf, .doc, .docx, .rtf, .zip and .txt permitted.</em>';
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
<label>Choose a name for the upload file (eg: <b>filename1</b> - omit file extension):</label>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="text" size="40" name="filename" value="<?php

	if (isset($_POST['submit1'])) {
		_print($_POST['filename']);
	}

?>" maxlength="60">
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
			if (isset($_POST['submit1']) && ($file == $name . '.' . $ext)) {
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

</div>

</body>
</html>