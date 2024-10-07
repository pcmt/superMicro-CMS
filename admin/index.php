<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 09 September 2024 */

define('ACCESS', TRUE);

// Declare variables
$page_id = $filetitle = $pagecontent = $file_contents = $response = $do_page = $do_menu = $problem = $homepage = $page = $addmenu = $line_exists = $menuline = $menutext = $rewrite = $ext = $mode = "";

$page_link = FALSE;

$thisAdmin = 'index'; // For nav

if (!file_exists('./top.php')) { // Leave this
	echo "Error. The file '/admin/<strong>top.php</strong>' does not exist.";
	exit();
}

include('./top.php');

// For $fileurl link to successful update
if (defined('APACHE') && APACHE) { // May not yet be installed
	$rewrite = TRUE;
	$ext = '';
} else {
	$rewrite = FALSE;
	$ext = '.php';
}

/* -------------------------------------------------- */
/* Preview page */

if (isset($_POST['submit2'])) {

	// Before creating a new one, delete any previous 'preview.txt'
	// None should exist
	if (file_exists('../pages/preview.txt')) {
		unlink('../pages/preview.txt');
	}

	// Block preview of inmenu.txt
	if ($_POST['page_id'] != 'inmenu.txt') {

		$filetitle = trim($_POST['page_id']);

		if (strlen($filetitle) < 1) {
			$response = "<em>Select an existing page or enter a new page title.</em>";
			$problem = TRUE;
		}

		// $pagecontent populates the textarea even if $problem
		$pagecontent = stripslashes($_POST['content']);
		if (strlen(trim($pagecontent)) < 1) {
			$response = "<em>You didn't enter any content.</em>";
			$problem = TRUE;
		}

		if (!$problem) {
			$textfile = '../pages/preview.txt';
			$fp = @fopen($textfile, 'w+');
			fwrite($fp, $pagecontent);
			fclose($fp);

			if ($rewrite) {
				header('Location: ' . LOCATION . 'preview?page=' . $filetitle);
			} else {
				header('Location: ' . LOCATION . 'preview' . $ext . '?page=' . $filetitle);
			}

			exit(); // Ensure script stops after redirect
		}

	} else {
		$response = "<em>Menu files can't be previewed as a web page.</em>";
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('pages'); ?></title>
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

	// For previews
	// Get mode parameter from edit link in menu.php
	if (isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}

	/* For page edits (pre-submit3, submit3) */

	if (isset($_POST['page_id'])) {
		$page = trim($_POST['page_id']);
	} elseif (isset($_GET['page'])) {
		$page = trim($_GET['page']);
	}

	if ($page) {
		$filetitle = $page;
		$textfilename = $filetitle . '.txt';
		if (file_exists("../pages/{$textfilename}")) {
			$file_contents = stripslashes(file_get_contents("../pages/{$textfilename}"));
			$file_contents = htmlspecialchars($file_contents);
		}
	}

	/* -------------------------------------------------- */
	/* For menu edits: get line for $page as $menuline */
	/* ($menuline is for menu edits WITH page edits) */
	/* ($menutext is for when a new page is created) */
	/* Manual menu edits use neither */

	/* This section checks if a menu line exists for the current page */
	/* to prevent it appearing twice - see Edit page -> Edit menu */
	/* See tests above 'Existing pages' list below */

	$inmenu = '../inc/inmenu.txt';

	if (file_exists($inmenu)) { // 13 March 23 edits below
		// Get inmenu.txt as array
		$inmenuArray = file($inmenu);
		// Loop through the lines to check for a match
		foreach ($inmenuArray as $line) {
			// Skip empty lines
			if (trim($line) === '') {
				continue;
			}
			$slug = stripAnchor($line);
			// If slug matches page
			if ($slug == $filetitle) {
				$line_exists = TRUE;
				$menuline = $line;
			}
		}
	}

	/* -------------------------------------------------- */

?>

<div id="o"><div id="wrap">

<h1><?php h1('pages'); ?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error. The file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

/* ================================================== */
/* SUBMITS */

/* -------------------------------------------------- */
/* New page */

	if (isset($_POST['submit1'])) {

		$page_id = preg_replace("/[[:space:]]+/", "-", trim($_POST['page_id']));

		// Prevent conflict with existing variables and folders
		$disallowed = array('admin', 'index', 'preview', 'page', 'pages', 'content', 'example', 'e', 's', 'comments', 'extras', 'fonts', 'img', 'video', 'css', 'css-unminified', 'diagnostics', 'js', 'uploads', 'data');
		foreach ($disallowed as $title) {
			if ($title == $page_id) { // If a file is missing
				$problem = TRUE;
				$response = "<em>Sorry, <strong>{$page_id}</strong> can't be used as a page title.</em>";
			}
		}

		if (strlen($page_id) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter a page title.</em>";
		}

		if (preg_match("/[^A-Za-z0-9_-]/", $page_id)) {
			$problem = TRUE;
			$response = '<em>The page title can contain only letters, numbers, hypens, and underscores.</em>';
		}

		if (strlen($_POST['content']) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter any content.</em>";
		}

		if (!$problem) {

			/* -------------------------------------------------- */

			$filetitle = preg_replace("/^-/", "", $page_id); // Strip leading hyphen

			if (file_exists("../pages/{$filetitle}.txt")) {
				$response = '<em>Sorry, this page title already exists. Try another.</em>';
			} else {

				$pagecontent = stripslashes($_POST['content']);
				$linesArray = explode(PHP_EOL, $pagecontent);

				if (strlen($linesArray[0]) < 1) {
					$pagecontent = "#{$pagecontent}";
				}

				/* -------------------------------------------------- */
				/* For menu when new page created */

				// Check for + symbol
				if (strpos($linesArray[0], '+') !== FALSE) { // 14 March 23 edits below
					$checklines = trim(file_get_contents($inmenu));
					if (empty($checklines)) {
						$towrite = $menutext; // No new line if empty
					} else {
						$towrite = "\n" . $menutext; // File title on new line
					}

					// Add menu item
					file_put_contents($inmenu, $towrite, FILE_APPEND);
				}

				/* -------------------------------------------------- */

				$template = '<?php

include(\'inc/html.php\');
$obj = new Page;
$obj->Textfilename = \'' . $filetitle . '.txt\';
$obj->Template();

?>';

				$filename = "../{$filetitle}.php";
				$fp = fopen($filename, 'w+');
				fwrite($fp, $template);
				fclose($fp);

				$textfile = "../pages/{$filetitle}.txt";
				$fp = fopen($textfile, 'w+');
				fwrite($fp, $pagecontent);
				fclose($fp);

				$commentfile = "../comments/{$filetitle}.txt";
				// Create comment file only if it doesn't exist
				if (!file_exists($commentfile)) {
					$fp = fopen($commentfile, 'w+');
					fwrite($fp, 'No comments so far.');
					fclose($fp);
				}

				$extrafile = "../extras/{$filetitle}.txt";
				// Create extras file only if it doesn't exist
				if (!file_exists($extrafile)) {
					$fp = fopen($extrafile, 'w+');
					fwrite($fp, '<p>No extras so far.</p>');
					fclose($fp);
				}

				$datafile = "../data/{$filetitle}.txt"; // New file
				$fp = fopen($datafile, 'w+');
				fwrite($fp, 'Get example'); // New empty file
				fclose($fp);

				$anchor = $filetitle;
				if ($filetitle == 'index') {
					$filetitle = str_replace('index', '', $filetitle);
				}

				$fileurl = LOCATION . $filetitle . $ext;

				$response = "<em>Success. The page <b><a href=\"{$fileurl}\">{$anchor}</a></b> was created.</em>";
			}
		}
	}

/* -------------------------------------------------- */
/* Prepare to update page */

	if (isset($_POST['pre-submit3'])) {

		$filetitle = trim($_POST['page_id']);

		if (strlen($filetitle) < 1) {
			$response = "<em>You didn't enter a page title.</em>";
		} elseif (!file_exists("../pages/{$filetitle}.txt")) {
			$response = "<em>Sorry, this page doesn't exist so can't update it. Click 'Create new page'.</em>";
		} else {
			$response = "<em>You are about to update <b>{$filetitle}</b> &raquo; click 'Update page' again, or [ <a href=\"index.php\" title=\"Abort\">abort</a> ]</em>";
		}
	}

/* -------------------------------------------------- */
/* Update page */

	if (isset($_POST['submit3'])) {

		$filetitle = trim($_POST['page_id']);

		if (strlen($filetitle) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter a filename.</em>";
		}

		if (!$problem) {

			if (!file_exists("../pages/{$filetitle}.txt")) {
				$response = "<em>Sorry, this page title doesn't exist. Try another or 'Create new page'.</em>";
			} else {

				$textfile = "../pages/{$filetitle}.txt";
				$text = file($textfile); // ??
				$pagecontent = stripslashes($_POST['content']);
				$fp = fopen($textfile, 'w+');
				fwrite($fp, $pagecontent);
				fclose($fp);

				$anchor = $filetitle;
				if (!$rewrite) {
					if ($filetitle == 'index') {
						$fileurl = LOCATION;
						$homepage = TRUE;
					} else {
						$fileurl = LOCATION . $filetitle . $ext;
					}
				} else {
					if ($filetitle == 'index') {
						$homepage = TRUE;
						$filetitle = str_replace('index', '', $filetitle);
					}
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>The page <b><a href=\"{$fileurl}\">{$anchor}</a></b> was successfully updated.</em>";

				// Delete preview text file to remove from list
				// For non-https when no menu 'edit' link with '$mode == preview'
				// or when back button used
				if (file_exists('../pages/preview.txt')) {
					unlink('../pages/preview.txt');
				}

				/* -------------------------------------------------- */
				/* Edits menu when page edited */

				if (!$homepage) { // Exclude home page

					if (file_exists('../inc/inmenu.txt')) {

						// Get first line of page textfile
						$linesArray = explode(PHP_EOL, $pagecontent);
						// Check for + symbol
						if (strpos($linesArray[0], '+') !== FALSE) {
							$addmenu = TRUE;
						}

						// (1) First line has no + symbol
						// Remove line if it exists
						if (!$addmenu && $line_exists) {
							// $menuline already stored on page load
							$index = array_search("{$menuline}", $inmenuArray);
							if ($index !== FALSE){
								unset($inmenuArray[$index]);
							}

							// Convert array back to string
							$newmenu = implode('', $inmenuArray);
							$newmenu = rtrim($newmenu); // Trim end

							// Update inmenu.txt
							file_put_contents($inmenu, $newmenu);
						}

						// (2) First line has + symbol:
						// Add line if doesn't exist
						if ($addmenu && !$line_exists) {
							$checklines = file_get_contents($inmenu); // 14 March 23 edits below
							$towrite = empty(trim($checklines)) ? $filetitle : "\n$filetitle";

							// Append $towrite to the end of the file
							file_put_contents($inmenu, $towrite, FILE_APPEND);
						}

					} else { // inmenu.txt doesn't exist
						$response = "<em>Error. The file inc/<b>inmenu.txt</b> doesn't exist.</em>";
					}
				}

				/* -------------------------------------------------- */

			}
		}
	}

/* -------------------------------------------------- */
/* Prepare to delete page */

	if (isset($_POST['pre-submit4'])) {

		$delete = trim($_POST['page_id']);
		$to_delete = '../' . $delete . '.php';

		if (strlen($delete) < 1) {
			$response = "<em>You didn't enter a page title.</em>";
		} elseif (!file_exists($to_delete)) {
			$response = "<em>Sorry, the page <b>{$delete}</b> doesn't exist.</em>";
		} elseif ($delete == 'index') {
			$response = "<em>You can't delete <b>index.php</b>.</em>";
		} elseif ($delete == 'preview') {
			$response = "<em>You can't delete <b>preview.php</b>.</em>";
		} else {
			$response = "<em>You are about to delete <b>{$delete}</b> (and its comments and extras if any) &raquo; click 'Delete page' again, or [ <a href=\"index.php\" title=\"Abort\">abort</a> ]</em>";
		}
	}

/* -------------------------------------------------- */
/* Delete page */

	if (isset($_POST['submit4'])) {

		$delete = trim($_POST['page_id']);

		$phpfile = '../' . $delete . '.php';
		$textfile = '../pages/' . $delete . '.txt';
		$extrafile = '../extras/' . $delete . '.txt';
		$commentfile = '../comments/' . $delete . '.txt';
		$datafile = '../data/' . $delete . '.txt';

		if ($delete == 'index') {
			$response = "<em>You can't delete <b>index.php</b>.</em>";
		} elseif ($delete == 'preview') {
			$response = "<em>You can't delete <b>preview.php</b>.</em>";
		} else {

			if (file_exists($phpfile)) {
				unlink($phpfile);
				$response = "<em>Success. <b>{$delete}</b> was deleted.</em>";
			} else {
				$response = "<em>Sorry, the page <b>{$delete}</b> doesn't exist.</em>";
			}

			if (file_exists($textfile)) {
				unlink($textfile);
			}

			if (file_exists($extrafile)) {
				unlink($extrafile);
			}

			if (file_exists($commentfile)) {
				unlink($commentfile);
			}

			if (file_exists($datafile)) {
				unlink($datafile);
			}

			// Edit menu
			$inmenuArray = array();
			$inmenuArray = file($inmenu);
			// Find line to remove
			// $menuline already stored on page load
			$index = array_search("{$menuline}", $inmenuArray);
			if($index !== FALSE){
				unset($inmenuArray[$index]);
			}

			// Convert array back to string
			$newmenu = implode('', $inmenuArray);
			$newmenu = rtrim($newmenu); // Trim end

			// Update inmenu.txt
			file_put_contents($inmenu, $newmenu);
		}
	}

/* -------------------------------------------------- */
/* Get menu (for manual edits) */

	if (isset($_POST['submit9'])) {

		// $menu = '../inc/inmenu.txt';
		if (!file_exists($inmenu)) {
			$response = "<em>Sorry, the menu doesn't exist.</em>";
		} else {
			$file_contents = file_get_contents($inmenu);
		}
	}

/* -------------------------------------------------- */
/* Prepare to edit menu */

	if (isset($_POST['pre-submit10'])) {

		$response = "<em>You are about to save <b>the menu</b> &raquo; click 'Save the menu' again, or [ <a href=\"index.php\" title=\"Abort\">abort</a> ]</em>";
	}

/* -------------------------------------------------- */
/* Edit menu */

	if (isset($_POST['submit10'])) {

		// $menu = '../inc/inmenu.txt';
		if (!file_exists($inmenu)) {
			$response = "<em>Sorry, the menu doesn't exist.</em>";
		} else {
			$menucontent = stripslashes($_POST['content']);
			// Strip excess whitespace
			$menucontent = preg_replace("/\x20+/", " ", $menucontent);
			// Remove space left of [ bracket
			$menucontent = str_replace(' [', '[', $menucontent);
			$menucontent = removeEmptyLines($menucontent);
			$fp = fopen($inmenu, 'w+'); // Changed from 'wb' 30 Nov 18
			fwrite($fp, $menucontent);
			fclose($fp);

			$response = '<em>The menu was successfully saved.</em>';
		}
	}

?>

<h3>Create/edit/delete a page | edit menu</h3>

	<div id="response">

<?php

/* ================================================== */
/* ACTION BOX */

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

<?php

/* ================================================== */
/* START FORM */

/* Clean things up a bit */

	if (isset($_POST['submit1']) || isset($_POST['pre-submit3']) || isset($_POST['submit3']) || isset($_POST['submit4'])) {
		$do_page = TRUE;
	}

	if (isset($_POST['submit9']) || isset($_POST['pre-submit10']) || isset($_POST['submit10'])) {
		$do_menu = TRUE;
	}

?>

	<div id="boxes">

<label><?php
	if ($do_menu) {
		if (ALPHABETICAL) {
			?>The navigation menu is currently alphabetical (see Setup to order manually as below).<?php
		} else {
			?>Edit the navigation menu (ordered manually as below - see Setup to order alphabetically).<?php
		}
	} else {
		?>Page title (letters, numbers, hyphens, underscores only):<?php
	}
?></label>

<input type="text" name="page_id" size="42" value="<?php

/* ================================================== */
/* TITLE BOX */

	/* -------------------------------------------------- */
	// Clear all
	if (isset($_POST['submit8'])) {
		_print("");

	/* -------------------------------------------------- */
	// For create, update, delete
	} elseif ($do_page) {
		if ($homepage) {
			_print('index'); // $filetitle is empty
		} else {
			_print($filetitle);
		}

	/* -------------------------------------------------- */
	// Menu
	} elseif ($do_menu) {
		_print('inmenu.txt');

	/* -------------------------------------------------- */
	// From edit link
	} elseif ($page) {
		_print($page);

	/* -------------------------------------------------- */
	} else {
		if(isset($_POST['page_id'])) {
			$page = trim(stripslashes($_POST['page_id']));
			$page = preg_replace("/^-/", "", $page);
			_print($page);
		}
	}

?>" maxlength="60"> <label style="display: inline;"> <?php
	if ($do_menu) {
		_print('&nbsp;[ the menu file ]');
	} else {
		_print('&nbsp;[ for URL ]');
	}
?></label>

<?php

/* ================================================== */
/* TEXT ABOVE MAIN BOX */

	if ($do_menu) { ?>

<p class="pages"><b>NOTE</b>: (i) preserve the existing page names (listed below) and match the text exactly, (ii) do not include <em>index</em> (the home page is always on the menu), and (iii) ensure each item is on its own line (with no empty lines).<br>A leading # symbol (eg: #example-page) means the page is not in the navigation menu and <i>vice versa</i> [ <a href="https://web.patricktaylor.com/cms-navigation-menu" target="_blank">info</a> ]</p>

	<?php } else { ?>

<p class="pages"><strong>Line 1</strong> not displayed. Add plus symbol <em>+</em> to add page to menu <span>&#124;</span> <em>~~password~~</em> to password protect <span>&#124;</span> ampersand symbol <em>&</em> enables comments [ <a href="https://web.patricktaylor.com/cms-comments" target="_blank">info</a> ] <span>&#124;</span> dollar symbol <em>$</em> enables extras [ <a href="https://web.patricktaylor.com/cms-extras" target="_blank">info</a> ] <span>&#124;</span> add <em>~~</em> to exclude page in on-site search <span>&#124;</span> add <em>V</em> for HTML validation button <span>&#124;</span> add caret symbol <em>^</em> for Facebook share button<br><strong>Line 2</strong> = <em>page heading</em><br><span><strong>Line 3</strong>: leave blank</span><br><strong>Line 4</strong> onwards = <em>content</em> [ <a href="index.php?page=">get example</a> ] [&nbsp;<a href="markup.html" target="_blank">get&nbsp;HTML&nbsp;markup</a>&nbsp;]</p>

	<?php } ?>

		<div class="textarea-container">

<textarea class="flexitem" name="content" rows="20">
<?php

/* ================================================== */
/* MAIN TEXTAREA */

	if (isset($_POST['submit2'])) {
		_print($pagecontent);
	}

	if ($mode == 'preview') {
		if (file_exists('../pages/preview.txt')) {
			$previewtext = file_get_contents('../pages/preview.txt');
			_print(stripslashes(htmlentities($previewtext)));
			// Delete preview after viewed
			unlink('../pages/preview.txt');
		} else {
			_print("The file '../pages/preview.txt' does not exist. Try again.");
		}

	/* -------------------------------------------------- */
	// Clear all
	} elseif (isset($_POST['submit8'])) {
		$file_contents = FALSE;
		_print("");

	/* -------------------------------------------------- */
	// New page
	} elseif (isset($_POST['submit1'])) {
		if (strlen(trim($pagecontent)) > 0) {
			_print(stripslashes(htmlentities($pagecontent)));
		} else {
			_print(stripslashes(htmlentities($_POST['content'])));
		}

	/* -------------------------------------------------- */
	// Update or delete
	} elseif (isset($_POST['pre-submit3']) || isset($_POST['submit3']) || isset($_POST['pre-submit4']) || isset($_POST['submit4'])) {
		$pagecontent = stripslashes(htmlentities($_POST['content']));
		$linesArray = explode(PHP_EOL, $pagecontent);
		if (trim($linesArray[0]) == '') { // If first line empty
			$pagecontent = "#{$pagecontent}"; // add #
		}
		_print($pagecontent);

	/* -------------------------------------------------- */
	// Update styles or get ready to save the menu
	} elseif (isset($_POST['pre-submit10'])) {
		_print(trim(stripslashes($_POST['content'])));

	/* -------------------------------------------------- */
	// Get the menu
	} elseif (isset($_POST['submit9'])) {
		_print(stripslashes($file_contents));

	/* -------------------------------------------------- */
	// Save the menu
	} elseif (isset($_POST['submit10'])) {
		_print($menucontent);

	/* -------------------------------------------------- */
	// Edit page link
	} elseif ($page) {
		if (isset($file_contents)) {
			_print(stripslashes($file_contents));
		}

	/* -------------------------------------------------- */
	// This page first loaded or 'example' clicked
	} elseif (!isset($_POST['submit2'])) { // Not preview
		_print_nlb('#
Page Heading

Content...');

	}

?>
</textarea>

		</div><!-- end .textarea-container //-->

<p><?php if ($do_menu) { ?>

[ <a href="./index.php">back to pages</a> ] &nbsp; 

<?php } ?>

[ <a href="./stylesheets.php">edit the stylesheets</a> ]</p>

	</div><!-- end #boxes //-->

<?php

/* ================================================== */
/* BUTTONS */

?>
	<div id="buttons">

		<div>

<input type="submit" name="submit8" class="fade" value="Clear all">

		</div>

<p>Pages:</p>

		<div><!-- first group //-->

<input type="submit" name="submit1" value="Add new page">

<input type="submit" name="submit2" value="Preview page">

<input type="submit" name="<?php

	if (isset($_POST['pre-submit3'])) {
		_print('submit3');
		$preclass = 'class="pre" ';
	} else {
		_print('pre-submit3');
		$preclass = '';
	}

?>" <?php _print($preclass); ?>value="Update page">

<input type="submit" name="<?php

	if (isset($_POST['pre-submit4']) && file_exists($to_delete)) {
		_print('submit4');
		$preclass = 'class="caution" '; // Red
	} else {
		_print('pre-submit4');
		$preclass = 'class="light" ';
	}

?>" <?php _print($preclass); ?>value="Delete page">

		</div>

<?php

	if ((isset($_GET['page']) && $_GET['page']) || isset($_POST['pre-submit3']) || isset($_POST['submit3'])) {
		if (isset($_GET['page'])) { // This checks if 'page' exists in the $_GET array
			$page = $_GET['page']; // Safe to access $_GET['page'] since we know it exists
		}
?>
<p>In addition:</p>

		<div><!-- second group //-->

<a href="./comments.php?page=<?php _print($page); ?>" class="faux-submit">Get comments</a>
<a href="./extras.php?page=<?php _print($page); ?>" class="faux-submit">Get extras</a>
<a href="./data.php?page=<?php _print($page); ?>" class="faux-submit">Get page data</a>

		</div>

<?php } ?>

<p>Menu:</p>

		<div><!-- third group //-->

<input type="submit" name="submit9" class="fade" value="Get the menu">
<input type="submit" name="<?php

	if (isset($_POST['submit9'])) { // Get the menu
		_print('pre-submit10');
		$preclass = 'class="em" ';
	} elseif (isset($_POST['pre-submit10'])) { // Save the menu
		_print('submit10');
		$preclass = 'class="pre" ';
	} else {
		_print('pre-submit10');
		$preclass = 'class="fade" ';
	}

?>" <?php _print($preclass); ?>value="Save the menu">

		</div>

	</div><!-- end #buttons //-->

</form>

<hr>

	<div id="list">

<?php includeFileIfExists('./list.php'); ?>

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