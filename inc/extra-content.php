<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 24 Dec 2020 */

// Declare variables
$commentfile = $leavecomment = $closecomments = '';

if (!defined('ACCESS')) {
	die('Direct access not permitted to extra-content.php.');
}

_print_nlb('<!-- extra-content //-->');

if (file_exists('./js/slides.js')) { // From the website root
	_print_nlb('<script src="' . LOCATION . 'js/slides.js"></script>');
}

if ($share) { // See html.php ($share)

?>

<!-- Facebook share button HTML here //-->

<?php

}

/* -------------------------------------------------- */
// Extras

if ($extras && isset($pageID)) {
	$extrafile = './extras/' . $pageID . '.txt';
	if (file_exists($extrafile)) {
		$extrafile = file_get_contents($extrafile);
		_print("
		<div class=\"extras\">

{$extrafile}

		</div>
		");
	}
}

/* -------------------------------------------------- */
// Comments

if ($comments && isset($pageID)) { // See html.php ($comments)

	if (APACHE) {

		$commentfile = './comments/' . $pageID . '.txt';
		if (file_exists($commentfile)) {

			_print('
		<div id="comments">

<h4>'.TEXT50.'</h4>
			');

			#_print("\n		<div id=\"comments\">\n\n");
			#_print('<h4>' . TEXT50 . '</h4>'); // "Comments"

			// Get the text
			$commentfile = file_get_contents($commentfile);

			if (strlen($commentfile) == 0) { // If there isn't a string
				_print_nla(TEXT51); // "No comments yet."
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
				_print_nlab("{$commentfile}\n{$closecomments}");
			}

			// If further commenting still open
			if ($leavecomment) {
				// At the bottom, show "Leave a comment" plus the form
				if (file_exists(INC . 'form.php')) {
					require(INC . 'form.php');
				}
			}

			_print_nlab('		</div>');

		}

	} else { // Comments on but not Apache

		_print('
<hr>
<p>Sorry, commenting not available. Microsoft-IIS does not support PHP mail.</p>
		');

	}

} // Comments off

?>
