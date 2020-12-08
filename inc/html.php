<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 07 Dec 2020 */
// Looks for $source, not $textArray
// Comments detection

// Most 'if (file_exists)' were removed on 24 Jan 2020
// (all required files checked in error-reporting.php)

define('ACCESS', TRUE); // Allow includes here

class Page {

	function Template() { // Assembles and outputs the HTML

		// Declare variables
		$pageID = $password = $share = $comments = $extras = '';
		// $outputpassword = NULL;

		// Define absolute path to /inc/ (this folder)
		$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/';
		define('INC', $_inc);

		// error-reporting.php loads settings.php and functions.php
		if (file_exists(INC . 'error-reporting.php')) {
			require(INC . 'error-reporting.php');
		} else {
			echo "Error. The file '/inc/error-reporting.php' could not be found.";
			exit();
		}

		$_textfile = $this->Textfilename;
		$source = './pages/' . $_textfile;
		// For other scripts except error-reporting.php
		$pageID = str_replace('.txt', '', $_textfile);

		if (file_exists($source)) {
			// Array everything in the text file
			$textArray = file($source);

			/* -------------------------------------------------- */
			// Extract and process the first line (of array)
			$lineone = trim(array_shift($textArray));
			$lineone = str_replace(' ', '', $lineone); // Strips whitespace

			// Detect password
			if (preg_match('/~~(.*?)~~/', $lineone, $match) == 1) {
				$password = $match[1];
			} else {
				$password = FALSE;
			}

			// Detect share status for Facebook share button
			if (strpos($lineone, '^') !== FALSE) {
				$share = TRUE;
			} else {
				$share = FALSE;
			}

			// Detect comment status
			if (strpos($lineone, '&') !== FALSE) {
				$comments = TRUE;
			} else {
				$comments = FALSE;
			}

			// Detect extras status
			if (strpos($lineone, '$') !== FALSE) {
				$extras = TRUE;
			} else {
				$extras = FALSE;
			}

			// Get the new first line as title
			$title = trim(array_shift($textArray));

			if (strlen(trim($title)) < 1) {
				$title = '<span>Line 2</span> (page heading) is empty';
			}

			// Put the title back at the top
			$text = trim(implode('', $textArray));

			/* -------------------------------------------------- */
			// Load 'system' texts exactly here
			require(INC . 'lang.php');

			/* -------------------------------------------------- */
			// Process title tag (convert br to space then strip all tags)
			$titletag = str_replace(array('<br>', '<br/>', '<br />'), ' ', $title);
			$titletag = strip_tags($titletag);

			/* -------------------------------------------------- */
			// Date modified
			if (function_exists('filemtime')) {
				$modified = date("d F, Y", filemtime($source));
			} else {
				$modified = 'date unknown';
			}

			/* -------------------------------------------------- */
			// Output some HTML (nav & main)
			require(INC . 'top.php');
			_print("\n<div id=\"wrap\">\n\n");
			if (file_exists(INC . 'extra-body.php')) { // Optional
				require(INC . 'extra-body.php');
			}
			require(INC . 'menu.php');
			_print("\n	<main id=\"content\">\n\n");

			/* -------------------------------------------------- */
			// If password protected
			if ($password) {

				// If correct password entered
				if (isset($_SESSION['password']) && ($_SESSION['password'] == "{$password}") ) {
					require(INC . 'content.php');
					// See ppp.php for why logout form is not displayed (18 Jan 20)
					// require(INC . 'logout-form.php');
				} else {
					// Logged out (show login form)
					require(INC . 'login-form.php');
				}

			/* -------------------------------------------------- */
			// Not password-protected
			} else {
				require(INC . 'content.php');
			}

			/* -------------------------------------------------- */
			// Output some HTML (end main)
			_print("\n	</main>\n");

			/* -------------------------------------------------- */
			// Output footer (tracking moved to footer 07 Dec 20)
			require(INC . 'footer.php');

			/* -------------------------------------------------- */
			// Output some HTML (end #wrap)
			_print("\n</div>\n\n</body>\n</html>");

			/* -------------------------------------------------- */
			// See ppp.php (logout form normally unsets but not displayed)
			// Deativated (sessions ends when browser closed)
			// if (isset($_SESSION['password'])) {
			// 	unset($_SESSION['password']); // Need to unset session
			// }

		} else {

			/* -------------------------------------------------- */
			// If there is no page.txt file
			header('HTTP/1.1 404 Not Found');
			require(INC . '404.php');
			exit();

		}
	}
}

?>