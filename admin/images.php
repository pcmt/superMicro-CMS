<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 08 Dec 2020 */
/* Form action */

// Declare variables
$response = $response1 = $display = $problem = "";
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
	h1('images');
} else {
	_print('Install the latest version of functions.php');
}
?></h1>

<p id="nav"><a href="<?php echo LOCATION; ?>">&#171;&nbsp;Site</a> 
<a href="./index.php" title="Create/edit/delete pages">Pages</a> 
<span>Images</span> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<a href="./backup.php" title="Backup">Backup</a> 
<a href="./setup.php" title="Setup">Setup</a> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<?php

	$imgfolder = LOCATION . 'img/';

	if (array_key_exists('submit1', $_POST)) { // Upload

		@chmod($imgfolder, 0775);

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
			$allowed = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
			$size = getimagesize($_FILES['upload']['tmp_name']);

			if (in_array($filetype, $allowed)) {
				$pieces = explode('.', $_FILES['upload']['name']);
				$extension = strtolower(array_pop($pieces));
				$extensions = array('jpg', 'jpeg', 'gif', 'png');
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

		$imagename = trim($_POST['delete']);
		$delete = '../img/' . $imagename;
		// $disallowed = array('img-loading.gif', 'nav-icon.jpg');

		if (strlen($imagename) < 1) {
			$problem = TRUE;
			$response = '<em>No image filename was entered.</em>';
		}

		// Admin images moved to admin

		if (($imagename == 'og.jpg') || ($imagename == 'bg_footer.gif') || ($imagename == 'bg_footer_monochrome.gif')) {
			$problem = TRUE;
			$response = "<em>The default images can't be deleted. Maybe upload a new one (<b>og.jpg</b> must be 200 pixels square).</em>";
		}

		if (!$problem) {
			if (file_exists($delete)) {
				unlink($delete);
				$response = '<em>Success. <b>' . $imagename . '</b> was deleted.</em>';
			} else {
				$response = '<em>Image <b>' . $imagename . '</b> doesn\'t exist. Try another.</em>';
			}
		}
	}

?>

<h3>Upload or delete an image</h3>

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

<h5>Choose an image on your device [ <a href="./upload.php" title="Upload other file types">other file types</a> ]</h5>

<form enctype="multipart/form-data" action="<?php echo $self; ?>" method="post" onSubmit="displayLoading();">

<input type="file" name="upload">
<label>Choose a name for the upload file (eg: <b>image1</b> - omit file extension):</label>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="text" size="40" name="filename" value="<?php

	if (isset($_POST['submit1'])) {
		echo $_POST['filename'];
	}

?>" maxlength="60">
<input type="submit" name="submit1" class="images" value="Upload image">

</form>

<!-- display image //-->

<img src="img/img-loading.gif" width="50" height="50" id="upload-progress" style="display:none">
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

<form action="<?php echo $self; ?>" method="post">

<label>Enter filename (eg: <b>image1.jpg</b> - include file extension):</label>
<input type="text" name="delete" size="40" value="<?php if (isset($_POST['submit2'])) echo $_POST['delete']; ?>" maxlength="60">
<input type="submit" name="submit2" class="images" value="Delete image">

</form>

<hr>

	<div id="list">

<h3>Existing image files</h3>

<?php

	$view = LOCATION . 'img/';

	echo "<ul>\n";
	echo "<li class=\"top\"><em>Click to view or copy markup to paste in pages</em></li>\n";
	$dirname = "../img";
	if ($folder = @opendir($dirname)) {
		$filesArray = array();
		while (FALSE !== ($file = readdir($folder))) {
			if ((strstr($file, '.jpg')) || (strstr($file, '.jpeg')) || (strstr($file, '.gif')) || (strstr($file, '.png'))) {
				$filesArray[] = $file;
			}
		}

		natcasesort($filesArray);
		foreach ($filesArray as $file) {
			$image = "../img/{$file}";
			$size = getimagesize($image);

			// For image just uploaded, otherwise no class
			if (isset($_POST['submit1']) && ($file == $filename)) {
				$mark = ' class="mark"';
			} else {
				$mark = NULL;
			}

			(int)$num = $num + 1;
			$num_padded = sprintf("[%03d]", $num);

			echo '<li' . $mark . '>' . $num_padded . ' Filename: <a href="' . $view . $file . '" title="View" target="_blank">' . $file . '</a> &#124; <i>copy &raquo;</i> <span>&lt;img src="img/' . $file . '" ' . $size[3] . ' alt=""&gt;</span></li>';
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