<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 20 May 2024 */

define('ACCESS', TRUE);

// Declare variables etc
$response = $problem = $new_filename = $file_to_upload = "";
$num = "0";
$thisAdmin = 'upload'; // For nav
$uploadfolder = '../uploads/';

include('./top.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('uploads'); ?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
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

<h1><?php h1('other file types'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

	// Original amended by ChatGPT
	if (isset($_POST['submit1'])) { // Upload

		if (empty($_FILES['upload']['name'])) {
			$response = '<em>Nothing happened. You didn\'t choose a file.</em>';
		} else {
			$filename = $_FILES["upload"]["name"];
			$filesize = $_FILES["upload"]["size"];
			$filetype = $_FILES["upload"]["type"];

			// Check if file was uploaded
			if (!is_uploaded_file($_FILES['upload']['tmp_name'])) {
				$response = "<em>Error uploading file.</em>";
			} else {
				// Validate file extension and size
				$allowed_extensions = array('pdf', 'doc', 'docx', 'rtf', 'zip', 'txt');
				$ext = pathinfo($filename, PATHINFO_EXTENSION);

				if (!in_array(strtolower($ext), $allowed_extensions)) {
					$response = '<em>Error. Only filetypes .pdf, .doc, .docx, .rtf, .zip and .txt permitted.</em>';
				} elseif ($filesize > 2097152) {
					$response = "<em>File too large. Maximum file size is 2 megabytes.</em>";
				} else {
					// Use secure file paths
					$uploads_dir = realpath(__DIR__ . '/../uploads');
					$random_string = randomString( 2 );
					$new_filename = $random_string . '_' . $filename;
					$target_file = $uploads_dir . '/' . $new_filename;

					$file_to_upload = $_FILES['upload']['tmp_name'];
					if (move_uploaded_file($file_to_upload, $target_file)) {
						$response = '<em>The file <b>' . $new_filename . '</b> has been uploaded.</em>';
					} else {
						$response = '<em>Error moving file.</em>';
					}
				}
			}
		}
	}

	if (isset($_POST['submit2'])) { // Delete

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

<h3>Upload or delete a PDF, Word, ZIP or .txt file [ <a href="./images.php" title="Image files">image files</a> ] [ <a href="./video.php" title="Video files">video files</a> ]</h3>

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
<label>2 megabytes max.<br>2 letters are added to your filename for security</label>
<input type="file" name="upload">
<input type="submit" name="submit1" class="stacked" value="Upload file">
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
<input type="submit" name="submit2" class="stacked" value="Delete file">

<input type="submit" name="" class="stacked fade" value="Reset form">

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

			if (count($filesArray) > 0) {
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