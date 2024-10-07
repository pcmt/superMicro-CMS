<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 03 August 2024 */

define('ACCESS', TRUE);

// Declare variables
$page = $filetitle = $file_contents = $datafilename = $response = $ext = $update = $preclass = "";

$example = 'Description here with no linebreaks (150 characters maximum)
"@type": "Product", 
"name": "Art Print",
"author": "Josephine Bloggs",
"image": "https://image-url.jpg",
"description": "Archival quality paper and inks",
"offers": {
"@type": "Offer",
"url": "https://page-url",
"priceCurrency": "GBP",
"price": "60"
}

FORMAT FOR STRUCTURED DATA
There should always be 12 lines, starting with a "meta description" of the page on line one with no linebreaks. Maximum recommended length is 150 characters. Then 11 more lines as the "structured data." To activate the structured data, add the following anywhere below the 11 lines, otherwise only the meta description will be used.';

$thisAdmin = 'data'; // For nav

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
<title><?php p_title('data'); ?></title>
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
			$datafilename = $filetitle . '.txt';
			if (file_exists("../data/{$datafilename}")) {
				$file_contents = stripslashes(file_get_contents("../data/{$datafilename}"));
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Data for <b><a href=\"{$fileurl}\">{$filetitle}</a></b>. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			} else {
				$response = '<em>Sorry, the data file /data/' . $datafilename . ' does not exist.</em>';
			}
		} else {
			$response = '<em>No page found.</em>';
		}
	}

?>

<div id="o"><div id="wrap">

<h1><?php h1('data'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

/* ---------------------------------------------------------------------- */
/* Get example */
	if (isset($_POST['get_example'])) {
		$file_contents = $example;
		$response = "<em>Example data. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
	}

/* ---------------------------------------------------------------------- */
/* Get data */

	if (isset($_POST['get_data'])) {
		if (strlen($_POST['data_id']) < 1) {
			$response = "<em>You didn't enter a page name.</em>";
		} else {
			$filetitle = trim($_POST['data_id']);
			$datafilename = $filetitle . '.txt';
			if (!file_exists("../data/{$datafilename}")) {
				$response = '<em>Sorry, the data file /data/' . $datafilename . ' does not exist.</em>';
			} else {
				$file_contents = stripslashes(file_get_contents("../data/{$datafilename}"));
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Data for <b><a href=\"{$fileurl}\">{$filetitle}</a></b>. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Prepare to edit data */

	if (isset($_POST['pre-submit'])) {
		if (trim($_POST['data_id']) === '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['content']) === '') {
			$response = "<em>You didn't enter any text.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$filetitle = trim($_POST['data_id']);
			$datafilename = $filetitle . '.txt';
			if (!file_exists("../data/{$datafilename}")) {
				$response = '<em>Sorry, this data file does not exist.</em>';
			} else {
				$response = "<em>You are about to edit the data for <b>{$filetitle}</b> &raquo; click 'Update data' again, or [ <a href=\"index.php?page={$filetitle}\" title=\"Abort\">abort</a> ]</em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Edit data */

	if (isset($_POST['submit'])) {
		$filetitle = trim($_POST['data_id']);
		if ($filetitle === '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['content']) === '') {
			$response = "<em>You didn't enter any text.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$datafilename = $filetitle . '.txt';
			if (!file_exists("../data/{$datafilename}")) {
				$response = '<em>Sorry, this data file does not exist.</em>';
			} else {
				$datafilename = "../data/{$datafilename}";
				$datacontent = stripslashes($_POST['content']);

				// Extract relevant lines as the data
				$dataArray = explode(PHP_EOL, $datacontent);
				$datalines = array_slice($dataArray, 0, 12);

				// Join them into a single string with line breaks
				$resultString = implode("\n", $datalines);
				$resultString = removeEmptyLines($resultString);

				$fp = fopen($datafilename, 'w+');
				fwrite($fp, $resultString);
				fclose($fp);
				if ($filetitle === 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Data for <b><a href=\"{$fileurl}\">{$filetitle}</a></b> were successfully updated. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			}
		}
	}

?>

<h3>Create/edit/delete data</h3>

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

<form action="<?php _print($self); ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Page title:</label>
<input type="text" name="data_id" size="60" value="<?php

	if (isset($_POST['pre-submit']) || isset($_POST['get_example']) || isset($_POST['get_data'])) {
		_print($_POST['data_id']);
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

<label>The data [ <a href="https://web.patricktaylor.com/cms-structured-data" target="_blank">info</a> ]</label>

		<div class="textarea-container">

<textarea class="flexitem" name="content" rows="20">
<?php

	if (isset($_POST['pre-submit']) || isset($_POST['submit'])) {
		_print(stripslashes(htmlentities($_POST['content'])));
	} elseif (isset($_POST['get_data']) || $page) {
		_print(stripslashes(htmlentities($file_contents)));
	} elseif (isset($_POST['get_example'])) {
		_print($example);
	}

?>
</textarea>

		</div>

	</div>

	<div id="buttons">

<input type="submit" name="get_example" class="fade" value="Get example">
<input type="submit" name="get_data" class="fade" value="Get page data">
<input type="submit" name="<?php

	if (isset($_POST['pre-submit']) && $update) { // Only if not whitespace
		_print('submit');
		$preclass = 'class="pre" ';
	} else {
		_print('pre-submit');
		$preclass = '';
	}

?>" <?php _print($preclass); ?>value="Update data">

<p>&laquo; <a href="index.php">back to pages</a></p>

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