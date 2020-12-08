<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 01 Nov 2020 */

// Declare variables
$commentfile = $leavecomment = $closecomments = '';

if (!defined('ACCESS')) {
	die('Direct access not permitted to extra-content.php.');
}

_print("<!-- extra-content //-->\n");

if (file_exists('./js/slides.js')) { // From the website root
	_print('<script src="' . LOCATION . 'js/slides.js"></script>');
}

_print("\n");

if ($share) { // See html.php ($share)

?>

<!-- Facebook share button HTML here //-->

<?php

}

/* -------------------------------------------------- */
// Extras

if ($extras) {

	$extrafile = './extras/' . $pageID . '.txt';

	if (file_exists($extrafile)) {
		$extrafile = file_get_contents($extrafile);
		_print("\n		<div class=\"extras\">\n\n");
		_print($extrafile);
		_print("\n\n		</div>\n");
	}

}

/* -------------------------------------------------- */
// Comments

if ($comments) { // See html.php ($comments)

	if (APACHE) {

		$commentfile = './comments/' . $pageID . '.txt';
		if (file_exists($commentfile)) {

			_print("\n		<div id=\"comments\">\n\n");
			_print('<h4>' . TEXT50 . '</h4>'); // "Comments"

			// Get the text
			$commentfile = file_get_contents($commentfile);

			if (strlen($commentfile) == 0) { // If there isn't a string
				_print("\n");
				_print(TEXT51); // "No comments yet."
			} else { // A string exists

				// If further commenting closed
				if (strstr($commentfile, '~~&~~')) {
					$closecomments = '<hr>
<p class="comments_closed">' . TEXT53 . '</p>'; // "Comments are closed."
					$commentfile = str_replace("~~&~~", "", $commentfile);
				} else {
					// Comments not closed
					$leavecomment = TRUE;
				}

				// Now format and show the comments
				$commentfile = autop($commentfile);
				$commentfile = bits_and($commentfile);
				$commentfile = absolute_it($commentfile);
				$commentfile = img_path($commentfile);
				$commentfile = video_path($commentfile);
				_print("\n{$commentfile}\n{$closecomments}\n");
			}

			// If further commenting still open
			if ($leavecomment) {

				// At the bottom, show "Leave a comment" plus the form
				_print("\n");
				if (file_exists(INC . 'form.php')) {
					require(INC . 'form.php');
				}
				_print("\n");

			}

			_print("\n		</div>\n");

		}

	} else { // Comments on but not Apache

		_print("\n<hr>\n\n<p>Sorry, commenting not available. Microsoft-IIS does not support the PHP mail function.</p>\n");

	}

} // Comments off

?>
