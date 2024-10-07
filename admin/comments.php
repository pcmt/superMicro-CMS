<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 09 September 2024 */
// Triple ===

define('ACCESS', TRUE);

// Declare variables
$page = $filetitle = $file_contents = $response = $rewrite = $ext = $update = $preclass = "";

$thisAdmin = 'comments'; // For nav

include('./top.php');

// For $fileurl link to successful update
if (APACHE) {
	$ext = '';
} else {
	$ext = '.php';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('comments') ?></title>
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

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		if ($page) {
			$filetitle = trim($page);
			$commentfilename = $filetitle . '.txt';
			if (file_exists("../comments/{$commentfilename}")) {
				$file_contents = stripslashes(file_get_contents("../comments/{$commentfilename}"));
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Comments on <b><a href=\"{$fileurl}\">{$filetitle}</a></b>. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			} else {
				$response = '<em>Sorry, this comments file does not exist.</em>';
			}
		} else {
			$response = '<em>No page found.</em>';
		}
	}

?>

<div id="o"><div id="wrap">

<h1><?php h1('comments'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

/* ---------------------------------------------------------------------- */
/* Get comments */

	if (isset($_POST['get_comments'])) {
		if (strlen($_POST['comments_id']) < 1) {
			$response = "<em>You didn't enter a page name.</em>";
		} else {
			$filetitle = trim($_POST['comments_id']);
			$commentsfilename = $filetitle . '.txt';
			if (!file_exists("../comments/{$commentsfilename}")) {
				$response = '<em>Sorry, this comments file does not exist. Try another.</em>';
			} else {
				$file_contents = stripslashes(file_get_contents("../comments/{$commentsfilename}"));
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Comments on <b><a href=\"{$fileurl}\">{$filetitle}</a></b></em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Prepare to edit comments */

	if (isset($_POST['presubmit'])) {
		if (trim($_POST['comments_id']) === '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['content']) === '') {
			$response = "<em>You didn't enter any comments.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$filetitle = trim($_POST['comments_id']);
			$commentsfilename = $filetitle . '.txt';
			if (!file_exists("../comments/{$commentsfilename}")) {
				$response = '<em>Sorry, this comments file does not exist. Try another.</em>';
			} else {
				$response = "<em>You are about to edit comments on <b>{$filetitle}</b> &raquo; click 'Update comments' again, or [ <a href=\"index.php?page={$filetitle}\" title=\"Abort\">abort</a> ]</em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Edit comments */

	if (isset($_POST['submit'])) {
		$filetitle = trim($_POST['comments_id']);
		if ($filetitle === '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['content']) === '') {
			$response = "<em>You didn't enter any comments.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$commentsfilename = $filetitle . '.txt';
			if (!file_exists("../comments/{$commentsfilename}")) {
				$response = '<em>Sorry, this comments file does not exist. Try another.</em>';
			} else {
				$commentsfilename = "../comments/{$commentsfilename}";
				$commentscontent = stripslashes($_POST['content']);
				$fp = fopen($commentsfilename, 'w+');
				fwrite($fp, $commentscontent);
				fclose($fp);
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Comments on <b><a href=\"{$fileurl}\">{$filetitle}</a></b> were successfully updated. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			}
		}
	}

?>

<h3>Create/edit/delete comments</h3>

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

<form action="<?php echo $self; ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Page title:</label>
<input type="text" name="comments_id" size="60" value="<?php

	if (isset($_POST['presubmit']) || isset($_POST['get_comments'])) {
		_print($_POST['comments_id']);
	} elseif (isset($_POST['submit'])) {
		if (strlen($filetitle) < 1) {
			$filetitle = 'index';
		}
		_print($filetitle);
	} else {
		_print($page);
	}

?>
" maxlength="60">

<label>Edit comments (leave blank line to create new paragraph). To stop further comments, enter <em>~~&~~</em> as the last line (not displayed).</label>

		<div class="textarea-container">

<textarea class="flexitem" name="content" rows="20">
<?php

	if (isset($_POST['presubmit']) || isset($_POST['submit'])) {
		_print(stripslashes(htmlentities($_POST['content'])));
	} elseif (isset($_POST['get_comments']) || $page) {
		_print(stripslashes(htmlentities($file_contents)));
	}

?>
</textarea>

		</div><!-- end .textarea-container //-->

	</div>

	<div id="buttons">

		<div>

<input type="submit" name="get_comments" class="fade" value="Get comments">

<input type="submit" name="<?php

	if (isset($_POST['presubmit']) && $update) { // Move forward only if not whitespace
		_print('submit');
		$preclass = 'class="pre" ';
	} else {
		_print('presubmit');
		$preclass = '';
	}

?>" <?php _print($preclass); ?>value="Update comments">

		</div>

	</div>

</form>

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