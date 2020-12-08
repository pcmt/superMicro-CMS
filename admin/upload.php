<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 25 Nov 2020 */
/* Form action */

// Declare variables
$response = $problem = "";
$num = "0";

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

?>
	<div id="loginform">

<h1>superMicro CMS <i>login</i></h1>

<?php

	if ($notice) {
		echo "\n" . $notice . "\n"; // From top.php (cookie test response)
	}

?>

<form id="pw" action="<?php echo $self; ?>" method="post">
<label><b>Enter password:</b></label>
<input type="hidden" name="form" value="login">
<input type="password" name="password" size="25" maxlength="32">
<input type="submit" name="submit0" value="Submit Password">
</form>

<?php

	if ($response) {
		echo '<p><em>' . $response . '</em></p>'; // If the user didn't do something
		echo "\n";
	}

	// Footer link etc
	if (function_exists('loggedoutFooter')) {
		// Prints link to home page if 'dofooter' + lost/forgotten password link if logged out
		loggedoutFooter();
	} else {
		echo "\n";
		echo '<p>Missing function. Install the latest version of <strong>superMicro CMS</strong>.</p>';

	}

	echo "\n";

?>

	</div>

<?php

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

<p id="nav"><a href="<?php echo LOCATION; ?>">&#171;&nbsp;Site</a> 
<a href="./index.php" title="Create/edit/delete pages">Pages</a> 
<a href="./images.php" title="Upload or delete images">Images</a> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<a href="./backup.php" title="Backup">Backup</a> 
<a href="./setup.php" title="Setup">Setup</a> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<?php

	$uploadfolder = LOCATION . 'uploads/';

	if (array_key_exists('submit1', $_POST)) { // Upload

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

		$name = trim($_POST['delete']);
		$delete = '../uploads/' . $name;

		if (strlen($name) < 1) {
			$problem = TRUE;
			$response = '<em>No filename was entered.</em>';
		}

		if (!$problem) {
			if (file_exists($delete)) {
				unlink($delete);
				$response = '<em>Success. <b>' . $name . '</b> was deleted.</em>';
			} else {
				$response = '<em>The file <b>' . $name . '</b> doesn\'t exist. Try another.</em>';
			}
		}
	}

?>

<h3>Upload or delete a PDF, Word, ZIP or .txt file</h3>

	<div id="response">

<?php

	echo '<p><span class="padded-multiline">';

	if (!$response) {
		echo '<em>No action requested.</em>';
	} else {
		echo $response;
	}

	echo '</span></p>';

?>

	</div>

<h5>Choose a file on your device</h5>

<form enctype="multipart/form-data" action="<?php echo $self; ?>" method="post" onSubmit="displayLoading();">

<label>2 megabyte max.</label>
<input type="file" name="upload">
<label>Choose a name for the upload file (eg: <b>filename1</b> - omit file extension):</label>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="text" size="40" name="filename" value="<?php

	if (isset($_POST['submit1'])) {
		echo $_POST['filename'];
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

<form action="<?php echo $self; ?>" method="post">

<label>Enter filename (eg: <b>myfile.pdf</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) echo $_POST['delete']; ?>" maxlength="60">
<input type="submit" name="submit2" class="images" value="Delete file">

</form>

<hr>

	<div id="list">

<h3>Existing files</h3>

<?php

	$view = LOCATION . 'uploads/';

	echo "<ul>\n";
	echo "<li class=\"top\"><em>Click to view or copy markup to paste in pages</em></li>\n";
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

			echo '<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> &#124; <i>copy &raquo;</i> <span>&lt;a href="./uploads/' . $file . '"&gt;' . $file . '&lt;/a&gt;</span></li>';
			echo "\n";
		}

		closedir($folder);
		echo "</ul>\n";
	}

?>

	</div>

<?php

	include('./footer.php');
} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	echo '<p>Login could not be verified.</p>';
}

?>

</div>

</body>
</html>