<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 06 Oct 2020 */

// Declare variables
$filetitle = $response = $rewrite = $ext = "";

require('./top.php');

// For $fileurl link to successful update
if (APACHE) {
	$ext = '';
} else {
	$ext = '.php';
}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('comments');
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

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		if ($page) {
			$filetitle = trim($page);
			$commentfilename = $filetitle . '.txt';
			if (file_exists("../comments/{$commentfilename}")) {
				$file_contents = stripslashes(file_get_contents("../comments/{$commentfilename}"));
				$fileurl = LOCATION . $filetitle . $ext;
				$response = "<em>Comments on <b><a href=\"{$fileurl}\">{$filetitle}</a></b></em>";
			} else {
				$response = '<em>Sorry, this comments file does not exist.</em>';
			}
		} else {
			$response = '<em>No page found.</em>';
		}
	}

?>

<div id="wrap">

<h1><?php

if (function_exists('h1')) {
	h1('comments');
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
				if ($filetitle == 'index') {
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

	if (isset($_POST['presubmit1'])) {
		if (strlen($_POST['comments_id']) < 1) {
			$response = "<em>You didn't enter a page name.</em>";
		} else {
			$filetitle = trim($_POST['comments_id']);
			$commentsfilename = $filetitle . '.txt';
			if (!file_exists("../comments/{$commentsfilename}")) {
				$response = '<em>Sorry, this comments file does not exist. Try another.</em>';
			} else {
				$response = "<em>You are about to edit comments on <b>{$filetitle}</b> &raquo; click 'Update comments' again, or [ <a href=\"comments.php\" title=\"Abort\">abort</a> ]</em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Edit comments */

	if (isset($_POST['submit1'])) {
		$filetitle = trim($_POST['comments_id']);
		if (strlen($filetitle) < 1) {
			$response = "<em>You didn't enter a page name.</em>";
		} else {
			$commentsfilename = $filetitle . '.txt';
			if (!file_exists("../comments/{$commentsfilename}")) {
				$response = '<em>Sorry, this comments file does not exist. Try another.</em>';
			} else {
				$commentsfilename = "../comments/{$commentsfilename}";
				$commentscontent = stripslashes($_POST['content']);
				$fp = fopen($commentsfilename, 'w+');
				fwrite($fp, $commentscontent);
				fclose($fp);
				if ($filetitle == 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Comments on <b><a href=\"{$fileurl}\">{$filetitle}</a></b> were successfully updated. <a href=\"index.php?page={$filetitle}\">Edit</a>&nbsp;&raquo;</em>";
			}
		}
	}

?>

<h3>Create/edit/delete comments</h3>

	<div id="response">

<?php

	echo '<p><span class="padded-multiline">';
	if (!$response) {
		echo '<em>No page\'s comments requested.</em>';
	} else {
		echo $response;
	}
	echo '</span></p>';

?>

	</div>

<form action="<?php echo $self; ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Page title:</label>
<input type="text" name="comments_id" size="60" style="font-weight: bold; color: #c63;" value="<?php

	if (isset($_POST['presubmit1']) || isset($_POST['get_comments'])) {
		echo $filetitle;
	} elseif (isset($_POST['submit1'])) {
		if (strlen($filetitle) < 1) {
			$filetitle = 'index';
		}
		echo $filetitle;
	} else {
		echo $page;
	}

?>
" maxlength="60">

<label>Edit comments (leave blank line to create new paragraph). To stop further comments, enter <em>~~&~~</em> as the last line.</label>
<textarea name="content" cols="90" rows="21">
<?php

	if (isset($_POST['presubmit1']) || isset($_POST['submit1'])) {
		echo stripslashes(htmlentities($_POST['content']));
	} elseif (isset($_POST['get_comments']) || $page) {
		echo stripslashes(htmlentities($file_contents));
	}

?>
</textarea>

	</div>

	<div id="buttons">

<input type="submit" name="get_comments" class="fade" value="Get comments">
<input type="submit" name="<?php

	if (isset($_POST['presubmit1'])) {
		echo 'submit1';
	} else {
		echo 'presubmit1';
	}

?>" value="Update comments">
	</div>

</form>

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
