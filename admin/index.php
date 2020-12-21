<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 21 Dec 2020 */
// Switched all echo to _print()

// Declare variables
$notice = $thisPage = $page_id = $filetitle = $pagecontent = $file_contents = $response = $do_page = $theme_selection = $theme = $do_stylesheet = $do_menu = $problem = $homepage = $page = $addmenu = $line_exists = $menuline = $menutext = $cssfilename = $rewrite = $ext = $mode = "";

if (!file_exists('./top.php')) { // Leave this
	echo "Error. The file '/admin/<strong>top.php</strong>' does not exist.";
	exit();
}

require('./top.php');

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

	// Block preview of stylesheet.css, extra.css and inmenu.txt
	if (($_POST['page_id'] != 'stylesheet.css') && ($_POST['page_id'] != 'extra.css') && ($_POST['page_id'] != 'inmenu.txt')) {

		$filetitle = trim($_POST['page_id']);
		if (strlen($filetitle) < 1) {
			$response = "<em>Select an existing page.</em>";
		} else {
			$pagecontent = stripslashes($_POST['content']);
			if (strlen(trim($pagecontent)) < 1) {
				$response = "<em>You didn't enter any content.</em>";
			} else {
				$textfilename = '../pages/preview.txt';
				$fp = @fopen($textfilename, 'w+'); // Changed from 'wb' 30 Nov 18
				fwrite($fp, $pagecontent);
				fclose($fp);

				if ($rewrite) {
					header('Location: ' . LOCATION . 'preview?page=' . $filetitle);
				} else {
					header('Location: ' . LOCATION . 'preview' . $ext . '?page=' . $filetitle);
				}
			}
		}

	} else {
		$response = "<em>The stylesheet and menu files can't be previewed as a web page.</em>";
	}
}

/* -------------------------------------------------- */
/* Comments page */
	if (isset($_POST['submit11'])) {
		$page_id = trim($_POST['page_id']);
		header('Location: ' . LOCATION . ADMIN . '/comments.php?page=' . $page_id);
	}

/* -------------------------------------------------- */
/* Extras page */
	if (isset($_POST['submit12'])) {
		$page_id = trim($_POST['page_id']);
		header('Location: ' . LOCATION . ADMIN . '/extras.php?page=' . $page_id);
	}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('pages');
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

	if (file_exists($inmenu)) {
		// Get inmenu.txt as array
		$inmenuArray = file($inmenu);
		// Loop through the lines to check for a match
		foreach ($inmenuArray as $line) {
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

<div id="wrap">

<h1><?php

if (function_exists('h1')) {
	h1('pages');
} else {
	_print('Install the latest version of functions.php');
}

?></h1>

<p id="nav"><a href="<?php _print(LOCATION); ?>">&#171;&nbsp;Site</a> 
<span>Pages</span> 
<a href="./images.php" title="Upload or delete images">Images</a> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<a href="./backup.php" title="Backup">Backup</a> 
<a href="./setup.php" title="Setup">Setup</a> 
<a href="./visits/" title="Visits" target="_blank">Visits</a> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<?php

/* ================================================== */
/* SUBMITS */

/* -------------------------------------------------- */
/* New page */

	if (isset($_POST['submit1'])) {

		$page_id = preg_replace("/[[:space:]]+/", "-", trim($_POST['page_id']));

		// Prevent conflict with existing variables and folders
		$disallowed = array('index', 'preview', 'page', 'pages', 'content', 'example', 'e', 's', 'comments', 'extras', 'fonts', 'img', 'video', 'css', 'diagnostics', 'js', 'visits');
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
			$phpfilename = $filetitle . '.php';
			$textfilename = $filetitle . '.txt';
			$commentfilename = $filetitle . '.txt';
			$extrafilename = $filetitle . '.txt';
			$menutext = $filetitle; // Future development: allow foreign characters?

			/* -------------------------------------------------- */

			if (file_exists("../pages/{$textfilename}")) {
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
				if (strpos($linesArray[0], '+') !== FALSE) {
					$checklines = file_get_contents($inmenu);
					if ((strlen(trim($checklines)) == 0) && !is_null($checklines)) { // Number of bytes
						$towrite = $menutext; // No new line if empty
					} else {
						$towrite = "\n" . $menutext; // File title on new line
					}

					// Addmenu item
					$fp = fopen($inmenu, 'a+');
					fwrite($fp, $towrite);
					fclose($fp);
				}

				/* -------------------------------------------------- */

				$template = '<?php

include(\'inc/html.php\');
$obj = new Page;
$obj->Textfilename = \'' . $textfilename . '\';
$obj->Template();

?>';

				$filename = "../{$phpfilename}";
				$fp = fopen($filename, 'w+');
				fwrite($fp, $template);
				fclose($fp);

				$textfilename = "../pages/{$textfilename}";
				$fp = fopen($textfilename, 'w+');
				fwrite($fp, $pagecontent);
				fclose($fp);

				$commentfilename = "../comments/{$commentfilename}";
				// Create comment file only if it doesn't exist
				if (!file_exists($commentfilename)) {
					$fp = fopen($commentfilename, 'w+');
					fwrite($fp, 'No comments so far.');
					fclose($fp);
				}

				$extrafilename = "../extras/{$extrafilename}";
				// Create comment file only if it doesn't exist
				if (!file_exists($extrafilename)) {
					$fp = fopen($extrafilename, 'w+');
					fwrite($fp, '<p>No extras so far.</p>');
					fclose($fp);
				}

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
/* Prepare to edit page */

	if (isset($_POST['pre-submit3'])) {
		$filetitle = trim($_POST['page_id']);
		$textfilename = $filetitle . '.txt';
		if (strlen($filetitle) < 1) {
			$response = "<em>You didn't enter a page title.</em>";
		} elseif ($filetitle == 'stylesheet.css') {
			$response = "<em>You can't edit the stylesheet with this button.</em>";
		} elseif (!file_exists("../pages/{$textfilename}")) {
			$response = "<em>Sorry, this page doesn't exist so can't update it. Click 'Create new page'.</em>";
		} else {
			$response = "<em>You are about to update <b>{$filetitle}</b> &raquo; click 'Update page' again, or [ <a href=\"index.php\" title=\"Abort\">abort</a> ]</em>";
		}
	}

/* -------------------------------------------------- */
/* Edit page */

	if (isset($_POST['submit3'])) {

		$filetitle = trim($_POST['page_id']);
		if (strlen($filetitle) < 1) {
			$problem = TRUE;
			$response = "<em>You didn't enter a filename.</em>";
		} elseif ($filetitle == 'stylesheet.css') {
			$problem = TRUE;
			$response = "<em>You can't edit the stylesheet with this button.</em>";
		}

		if (!$problem) {

			$textfilename = $filetitle . '.txt';
			if (!file_exists("../pages/{$textfilename}")) {
				$response = "<em>Sorry, this page title doesn't exist. Try another or 'Create new page'.</em>";
			} else {
				$textfilename = "../pages/{$textfilename}";

				$text = file($textfilename);

				$pagecontent = stripslashes($_POST['content']);
				$fp = fopen($textfilename, 'w+');
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
							// file_put_contents($inmenu, "\n{$filetitle}", FILE_APPEND);
							// Above replaced by same as create new page

							$checklines = file_get_contents($inmenu);
							if ((strlen(trim($checklines)) == 0) && !is_null($checklines)) { // Number of bytes
								$towrite = $filetitle; // No new line if empty
							} else {
								$towrite = "\n" . $filetitle; // File title on new line
							}

							// Addmenu item
							$fp = fopen($inmenu, 'a+');
							fwrite($fp, $towrite);
							fclose($fp);
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
		$phpfilename = '../' . $delete . '.php';
		$textfilename = '../pages/' . $delete . '.txt';
		$commentfilename = '../comments/' . $delete . '.txt';
		$extrafilename = '../extras/' . $delete . '.txt';
		if ($delete == 'index') {
			$response = "<em>You can't delete <b>index.php</b>.</em>";
		} elseif ($delete == 'preview') {
			$response = "<em>You can't delete <b>preview.php</b>.</em>";
		} else {
			if (file_exists($phpfilename)) {
				unlink($phpfilename);
				$response = "<em>Success. <b>{$delete}</b> was deleted.</em>";
			} else {
				$response = "<em>Sorry, the page <b>{$delete}</b> doesn't exist.</em>";
			}
			if (file_exists($textfilename)) {
				unlink($textfilename);
			}
			if (file_exists($commentfilename)) {
				unlink($commentfilename);
			}
			if (file_exists($extrafilename)) {
				unlink($extrafilename);
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
/* Get a stylesheet */

	if (isset($_POST['submit5'])) {

		if ($_POST['select_style'] == 'current') {
			$cssfilename = '../css/stylesheet.css';
		} elseif ($_POST['select_style'] == 'default') {
			$cssfilename = '../css/default.css';
		} elseif ($_POST['select_style'] == 'default_unminified') {
			$cssfilename = '../css/default-unminified.css';
		} elseif ($_POST['select_style'] == 'extra') {
			$cssfilename = '../css/extra.css';

			if (!file_exists($cssfilename)) {
				$text = './text/extra-css.txt';
				if (!copy($text, $cssfilename)) {
					$response = "<em>Error: could not write <b>{$cssfilename}</b></em>";
				}
			}

		} elseif ($_POST['select_style'] == 'none') {
			$response = "<em>No stylesheet selected. Select a stylesheet in 'Styles:'</em>";
			$cssfilename = FALSE;
		}

		if ($cssfilename) {
			if (!file_exists($cssfilename)) {
				$response = "<em>Sorry, the selected stylesheet doesn't exist.</em>";
			} else {
				$file_contents = file_get_contents($cssfilename);
			}
		}
	}

/* -------------------------------------------------- */
/* Prepare to edit stylesheet */

	if (isset($_POST['pre-submit6'])) {

		if (trim($_POST['page_id']) == 'extra.css') {
			$stylesheet_name = 'extra stylesheet';
		} else {
			$stylesheet_name = 'stylesheet';
		}

		if (strlen(trim(stripslashes($_POST['content']))) < 1) {
			$response = "<em>No styles. You can't remove all styles.</em>";
		} elseif (!strpos($_POST['page_id'], '.css')) {
			$response = "<em>The filename must be a stylesheet.</em>";
		} else {
			$response = "<em>You are about to update the <b>{$stylesheet_name}</b> &raquo; click 'Update styles' again, or [ <a href=\"index.php\" title=\"Abort\">abort</a> ]</em>";
		}
	}

/* -------------------------------------------------- */
/* Edit stylesheet */

	if (isset($_POST['submit6'])) {

		if (trim($_POST['page_id']) == 'extra.css') {
			$stylesheet_name = 'extra.css';
		} else {
			$stylesheet_name = 'stylesheet.css';
		}

		$cssfilename = '../css/' . $stylesheet_name;
		if (!file_exists($cssfilename)) {
			$response = "<em>Sorry, the stylesheet <b>{$stylesheet_name}</b> doesn't exist.</em>";
		}

		if (strlen(trim(stripslashes($_POST['content']))) < 1) {
			$response = "<em>No styles. You can't remove all styles.</em>";
		} else {
			$csscontent = stripslashes($_POST['content']);
			$fp = fopen($cssfilename, 'w+'); // Changed from 'wb' 30 Nov 18
			fwrite($fp, $csscontent);
			fclose($fp);
			$response = '<em>The stylesheet was successfully updated.</em>';
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

<h3>Create/edit/delete a page | update styles | edit menu</h3>

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

	if (isset($_POST['submit5']) || isset($_POST['pre-submit6']) || isset($_POST['submit6'])) {
		$do_stylesheet = TRUE;
	}

	if (isset($_POST['submit9']) || isset($_POST['pre-submit10']) || isset($_POST['submit10'])) {
		$do_menu = TRUE;
	}

?>

	<div id="boxes">

<label><?php
	if ($do_stylesheet) {
		?>Edit the stylesheet:<?php
	} elseif ($do_menu) {
		if (ALPHABETICAL) {
			?>The navigation menu is currently alphabetical (see Setup to order manually as below).<?php
		} else {
			?>Edit the navigation menu (ordered manually as below - see Setup to order alphabetically).<?php
		}
	} else {
		?>Page title (words, numbers, -hyphens and _underscores only):<?php
	}
?></label>

<input type="text" name="page_id" size="60" value="<?php

/* ================================================== */
/* TITLE BOX */

	/* -------------------------------------------------- */
	// Clear all
	if (isset($_POST['submit8']) || (isset($_POST['submit5']) && !$cssfilename)) {
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
	// Styles
	} elseif ($do_stylesheet && $cssfilename) {
		if ($cssfilename == '../css/extra.css') {
			_print('extra.css');
		} else {
			_print('stylesheet.css');
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

?>" maxlength="60"> <label style="display: inline;"><?php
	if ($do_stylesheet) {
		?>[ the stylesheet file ]<?php
	} elseif ($do_menu) {
		?>[ the menu file ]<?php
	} else {
		?>[ for URL and menu text ]</label><?php
	}
?>

<label><?php

/* ================================================== */
/* TEXT ABOVE MAIN BOX */

	if ($do_stylesheet) { ?>

The default styles can be restored with <em>Get styles</em> &raquo; <em>Default styles</em> &raquo; <em>Update styles</em><br>The optional <em>extra styles</em> supplement the active stylesheet: 'stylesheet.css' [ <a href="https://supermicrocms.com/stylesheets" target="_blank">info</a> ]

<?php } elseif ($do_menu) { ?>

<b>NOTE</b>: (i) preserve the existing page names (listed below) and match the text exactly, (ii) do not include <em>index</em> (the home page is always on the menu), and (iii) ensure each item is on its own line (with no empty lines).<br>A leading # symbol (eg: #example-page) means the page is not in the navigation menu and <i>vice versa</i> [ <a href="https://supermicrocms.com/navigation-menu" target="_blank">info</a> ]

	<?php } else { ?>

<strong>Line 1</strong> not displayed. Add plus symbol <em>+</em> to add page to menu <span>&#124;</span> <em>~~password~~</em> to password protect<br><strong>Line 2</strong> = <em>page heading</em><br><span><strong>Line 3 leave blank</strong></span><br><strong>Line 4</strong> onwards = <em>content</em> [ <a href="index.php?page=">get example</a> ] [ <a href="markup.html" target="_blank">get HTML markup</a> ]

	<?php } ?></label>

<textarea name="content" rows="20">
<?php

/* ================================================== */
/* MAIN TEXTAREA */

	if ($mode == 'preview') {
		$previewtext = file_get_contents('../pages/preview.txt');
		_print(stripslashes(htmlentities($previewtext)));
		// Delete preview when viewed to prevent appearing in list
		// Relies on 'edit' link (https only - see edit page)
		unlink('../pages/preview.txt');

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
	// Get styles
	} elseif (isset($_POST['submit5'])) {
		if ($cssfilename) {
			_print(stripslashes($file_contents));
		} else {
			_print('');
		}

	/* -------------------------------------------------- */
	// Update styles or get ready to save the menu
	} elseif (isset($_POST['pre-submit6']) || isset($_POST['submit6']) || isset($_POST['pre-submit10'])) {
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
	} else {
		_print('#
Page Heading

Content...');

	}

?>
</textarea>

<?php if ($do_menu) { ?>

<p><a href="./index.php" title="Back to 'Pages'">&laquo; Back to 'Pages'</a></p>

<?php } elseif ($do_stylesheet) { ?>

<p><a href="./index.php" title="Back to 'Pages'">&laquo; Back to 'Pages'</a></p>

<?php } else { // Default to: ?>

<p><strong>Line 1</strong> ampersand symbol <em>&</em> enables comments [ <a href="https://supermicrocms.com/commenting" target="_blank">info</a> ] <span>&#124;</span> dollar symbol <em>$</em> enables extras [ <a href="https://supermicrocms.com/page-extras" target="_blank">info</a> ]</p>

<?php } ?>

	</div>

<?php

/* ================================================== */
/* BUTTONS */

?>
	<div id="buttons">

<input type="submit" name="submit8" class="fade" value="Clear all">

<p>Pages:</p>

<input type="submit" name="submit1" value="Create new page">
<input type="submit" name="submit2" value="Preview page">
<input type="submit" name="<?php

	if (isset($_POST['pre-submit3'])) {
		_print('submit3');
	} else {
		_print('pre-submit3');
	}

?>" value="Update page">
<input type="submit" name="<?php

	if (isset($_POST['pre-submit4']) && file_exists($to_delete)) {
		_print('submit4');
	} else {
		_print('pre-submit4');
	}

?>" class="caution" value="Delete page">
<input type="submit" name="submit11" value="Get comments">
<input type="submit" name="submit12" value="Get extras">

<p class="fade">Styles:</p>

<select id="dropdown" name="select_style">
<?php

	$current = 'Current styles';
	$default = 'Default styles';
	$default_unminified = 'Default unminified';
	$extra = 'Extra styles only';

	if (isset($_POST['submit5']) || isset($_POST['pre-submit6'])) {
		if ($_POST['select_style'] == 'current') {
			_print_nlb('<option value="' . $_POST['select_style'] . '">' . $current . '</option>');
		} elseif ($_POST['select_style'] == 'default') {
			_print_nlb('<option value="' . $_POST['select_style'] . '">' . $default . '</option>');
		} elseif ($_POST['select_style'] == 'default_unminified') {
			_print_nlb('<option value="' . $_POST['select_style'] . '">' . $default_unminified . '</option>');
		} elseif ($_POST['select_style'] == 'extra') {
			_print_nlb('<option value="' . $_POST['select_style'] . '">' . $extra . '</option>');
		}
	}

?>
<option value="none">Select a stylesheet:</option>
<option value="current"><?php echo $current; ?></option>
<option value="default"><?php echo $default; ?></option>
<option value="default_unminified"><?php echo $default_unminified; ?></option>
<option value="extra"><?php echo $extra; ?></option>
</select>

<input type="submit" name="submit5" class="fade" value="Get styles">
<input type="submit" name="<?php

	if (isset($_POST['pre-submit6']) && (strlen(trim(stripslashes($_POST['content']))) > 1) && (strpos($_POST['page_id'], '.css') !== FALSE)) {
		_print('submit6');
	} else {
		_print('pre-submit6');
	}

	if (isset($_POST['pre-submit6']) || isset($_POST['submit5'])) {
		if ((strpos($_POST['page_id'], '.css') !== FALSE) || $cssfilename) {
			$class1 = 'em';
		} else {
			$class1 = 'fade';
		}
	} else {
		$class1 = 'fade';
	}

?>" class="<?php echo $class1; ?>" value="Update styles">

<p class="fade">Menu:</p>

<input type="submit" name="submit9" class="fade" value="Get the menu">
<input type="submit" name="<?php

	if (isset($_POST['pre-submit10'])) {
		_print('submit10');
	} else {
		_print('pre-submit10');
	}

	if (isset($_POST['submit9']) || isset($_POST['pre-submit10'])) {
		$class2 = 'em';
	} else {
		$class2 = 'fade';
	}

?>" class="<?php _print($class2); ?>" value="Save the menu">

	</div>

</form>

<hr>

	<div id="list">

<?php include('./list.php'); ?>

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