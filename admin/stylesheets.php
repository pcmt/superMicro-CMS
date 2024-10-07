<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 August 2024 */

define('ACCESS', TRUE);

// Declare variables
$response = $write_this = $preclass = $file_contents = $do_stylesheet = $the_stylesheet = $cssfilename = "";

$thisAdmin = 'stylesheets'; // For nav

if (!file_exists('./top.php')) { // Leave this
	echo "Error. The file '/admin/<strong>top.php</strong>' does not exist.";
	exit();
}

include('./top.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('stylesheets'); ?></title>
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

?>

<div id="o"><div id="wrap">

<h1><?php h1('stylesheets'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

/* ================================================== */
/* SUBMITS */

/* -------------------------------------------------- */
/* Get a stylesheet */

	if (isset($_POST['submit1'])) {
/*
Handling 'none' selection: if the user selects 'none', an appropriate message is stored in $response, and $cssfilename is set to false. This prevents further processing for file operations.

Default case: added a default case to handle any unexpected or invalid input. This makes the code more robust against unforeseen issues.

File existence check: if a valid file path is assigned to $cssfilename, the code checks if the file exists using file_exists(). If the file doesn’t exist, an error message is stored in $response. If the file exists, its contents are read using file_get_contents().
*/

		$cssfilename = FALSE; // Initialize cssfilename as false

		// Determine the selected stylesheet based on the user's input
		switch ($_POST['select_style']) {
			case 'current':
			$cssfilename = '../css/stylesheet.css';
			break;

			case 'current_mobile':
			$cssfilename = '../css/mobile.css';
			break;

			case 'default':
			$cssfilename = '../css/default.css';
			break;

			case 'default_unminified':
			$cssfilename = '../css/default-unminified.css';
			break;

			case 'mobile_default':
			$cssfilename = '../css/mobile-default.css';
			break;

			case 'mobile_default_unminified':
			$cssfilename = '../css/mobile-default-unminified.css';
			break;

			case 'extra':
			$cssfilename = '../css/extra.css';
			break;

			case 'none':
			$response = "<em>No stylesheet selected. Select a stylesheet.</em>";
			break;

			default:
			$response = "<em>Invalid selection. Please select a valid stylesheet.</em>";
			break;
		}

		// Verify the selected .css file exists
		if ($cssfilename) {
			if (!file_exists($cssfilename)) {
				$response = "<em>Sorry, the selected stylesheet doesn't exist.</em>";
			} else {
				$file_contents = file_get_contents($cssfilename);
			}
		}
	}

/* -------------------------------------------------- */
/* Prepare to edit stylesheet.css, mobile.css or extra.css */

	if (isset($_POST['pre-submit2'])) {

		if (trim($_POST['stylesheet_id']) === 'extra.css') {
			$stylesheet_name = 'extra.css';
		} elseif (trim($_POST['stylesheet_id']) === 'mobile.css') {
			$stylesheet_name = 'mobile.css';
		} else {
			$stylesheet_name = 'stylesheet.css';
		}

		if (strlen(trim(stripslashes($_POST['content']))) < 1) {
			$response = "<em>Select a stylesheet.</em>";
		} elseif (!strpos($_POST['stylesheet_id'], '.css')) {
			$response = "<em>Select a stylesheet.</em>";
		} else {
			$response = "<em>You are about to update <b>{$stylesheet_name}</b> &raquo; now click 'Update styles' again.</em>";
		}
	}

/* -------------------------------------------------- */
/* Edit stylesheet */

	if (isset($_POST['submit2'])) {

		if (trim($_POST['stylesheet_id']) === 'extra.css') {
			$stylesheet_name = 'extra.css';
		} elseif (trim($_POST['stylesheet_id']) === 'mobile.css') {
			$stylesheet_name = 'mobile.css';
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
			$write_this = stripslashes($_POST['content']);
			$fp = fopen($cssfilename, 'w+'); // Changed from 'wb' 30 Nov 18
			fwrite($fp, $write_this);
			fclose($fp);
			$response = '<em>The stylesheet was successfully updated.</em>';
		}
	}

?>

<h3>Edit styles</h3>

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

	if (isset($_POST['submit1']) || isset($_POST['pre-submit2']) || isset($_POST['submit2'])) {
		$do_stylesheet = TRUE;
	}

?>

	<div id="boxes">

<label style="margin-bottom: 5px;">Edit the stylesheet:</label>

<input type="text" name="stylesheet_id" size="42" value="<?php

/* ================================================== */
/* TITLE BOX */

	/* -------------------------------------------------- */
	// Clear all
	if (isset($_POST['submit3']) || (isset($_POST['submit1']) && !$cssfilename)) {
		_print("");

	/* -------------------------------------------------- */
	// CSS files to update: extra.css, mobile.css or stylesheet.css
	} elseif ($do_stylesheet && $cssfilename) {
		if ($cssfilename == '../css/extra.css') {
			_print('extra.css');
		} elseif ($cssfilename == '../css/mobile.css') {
			_print('mobile.css');
		} elseif ($cssfilename == '../css/mobile-unminified.css') {
			_print('mobile.css');
		} elseif ($cssfilename == '../css/mobile-default.css') {
			_print('mobile.css');
		} elseif ($cssfilename == '../css/mobile-default-unminified.css') {
			_print('mobile.css');
		} else {
			_print('stylesheet.css');
		}


	/* -------------------------------------------------- */
	// From edit link
	} elseif ($the_stylesheet) {
		_print($the_stylesheet);

	/* -------------------------------------------------- */
	} else {
		if(isset($_POST['stylesheet_id'])) {
			$the_stylesheet = stripslashes($_POST['stylesheet_id']);
			_print($the_stylesheet);
		}
	}

?>" maxlength="60"> <label style="display: inline;">[ the stylesheet to update ]</label>

<p class="pages">The stylesheet:</p>

		<div class="textarea-container">

<textarea class="flexitem" name="content" rows="20">
<?php

/* ================================================== */
/* MAIN TEXTAREA */

	if (isset($_POST['submit1'])) {
		if ($cssfilename) {
			_print(stripslashes($file_contents));
		} else {
			_print('');
		}

	/* -------------------------------------------------- */
	// Update styles
	} elseif (isset($_POST['pre-submit2']) || isset($_POST['submit2'])) {
		_print(stripslashes($_POST['content']));
	} else {
		_print('No styles selected');
	}

?>
</textarea>

		</div><!-- end .textarea-container //-->

	</div>

<?php

/* ================================================== */
/* BUTTONS */

?>
	<div id="buttons">

<select id="dropdown" name="select_style">
<?php
/*
Array for options: the options are stored in an associative array $options, mapping the value attributes to their respective labels. This makes the code more scalable and easier to manage.

Conditional selection: if the form has been submitted (submit1 or pre-submit2), the script checks if the selected style exists in the $options array. If it does, it prints that option first and removes it from the array to avoid duplication.

Default pption: after handling the selected option, the script prints the default "Select a stylesheet:" option, ensuring that it remains available.

Remaining options: finally, the script loops through the remaining items in the $options array and prints them. This ensures that all unselected options are available in the dropdown.
*/

	$current = 'Current styles';
	$current_mobile = 'Current mobile styles';
	$default = 'Default styles';
	$default_unminified = 'Default unminified';
	$mobile_default = 'Mob default styles';
	$mobile_default_unminified = 'Mob def unminified';
	$extra = 'Optional extra styles';

	$options = [ // Array keys
	'current' => $current,
	'current_mobile' => $current_mobile,
	'default' => $default,
	'default_unminified' => $default_unminified,
	'mobile_default' => $mobile_default,
	'mobile_default_unminified' => $mobile_default_unminified,
	'extra' => $extra
	];

	if (isset($_POST['submit1']) || isset($_POST['pre-submit2'])) {
		if (isset($_POST['select_style']) && array_key_exists($_POST['select_style'], $options)) {
		// Print the selected option first
		_print_nlb('<option value="' . $_POST['select_style'] . '">' . $options[$_POST['select_style']] . ' &check;</option>');
		// Remove the selected option from the $options array to avoid duplication
		unset($options[$_POST['select_style']]);
		}
	}

	// Print the default "Select a stylesheet:" option
	_print_nlb('<option value="none">Select a stylesheet:</option>');

	// Print the remaining options
	foreach ($options as $value => $label) {
		_print_nlb('<option value="' . $value . '">' . $label . '</option>');
	}

?>
</select>

		<div><!-- second group //-->

<input type="submit" name="submit1" value="Get styles">
<input type="submit" name="<?php /* This bit is tricky */

	if (isset($_POST['pre-submit2']) || isset($_POST['submit1'])) {
		if ((strpos($_POST['stylesheet_id'], '.css') !== FALSE) || $cssfilename) {
			$preclass = 'class="em" '; // White text on black
		} else {
			$preclass = 'class="fade" '; // No go, so keep it faded
		}
	} else {
		$preclass = 'class="fade" '; // Default starting position class
	}

	if (isset($_POST['pre-submit2']) && (strlen(trim(stripslashes($_POST['content']))) > 1) && (strpos($_POST['stylesheet_id'], '.css') !== FALSE)) {
		_print('submit2'); // Ready to update
		$preclass = 'class="pre" ';
	} else {
		_print('pre-submit2'); // Default starting position name
	}

?>" <?php _print($preclass); ?>value="Update styles">
<input type="submit" name="submit3" class="fade" value="Clear all">

		</div>

<p>Go to <a href="./index.php" title="'Pages'">Pages</a></p>

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