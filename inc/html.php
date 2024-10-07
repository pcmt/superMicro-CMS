<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 11 September 2024 */

// (all required files checked in /admin/install.php)

define('ACCESS', TRUE); // Allow includes here

class Page {

	function Template() { // Assembles and outputs the HTML

		// Declare variables
		$pageID = $password = '';
		$share = $comments = $nocopy = $extras = $validate = '';

		// Define absolute path to /inc/ (this folder)
		$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/';
		define('INC', $_inc);

		// error-reporting.php loads settings.php and functions.php
		include(INC . 'error-reporting.php');

		$_textfile = $this->Textfilename;
		$source = './pages/' . $_textfile;
		// For other scripts except error-reporting.php
		$pageID = str_replace('.txt', '', $_textfile);

		if (file_exists($source)) {
			// Array everything in the text file
			$textArray = file($source);

			/* -------------------------------------------------- */
			// Extract and process the first line of array
			$lineone = trim(array_shift($textArray));
			$lineone = preg_replace('/\s+/', '', $lineone); // Strips whitespace, tabs, newlines

			// Detect password
			if (preg_match('/~~(.*?)~~/', $lineone, $match) == 1) {
				$password = $match[1];
			} else {
				$password = FALSE;
			}

			// Detect comment status
			if (strpos($lineone, '&') !== FALSE) {
				$comments = TRUE;
			}

			// Detect 'nocopy' status
			if (strpos($lineone, 'X') !== FALSE) {
				$nocopy = TRUE;
			}

			// Detect extras status
			if (strpos($lineone, '$') !== FALSE) {
				$extras = TRUE;
			}

			// Detect share status for Facebook share button
			if (strpos($lineone, '^') !== FALSE) {
				$share = TRUE;
			}

			// Detect validation status
			if (strpos($lineone, 'V') !== FALSE) {
				$validate = TRUE;
			}

			// Get the new first line (<h1>) for HTML <title> and Open Graph title
			$title = trim(array_shift($textArray));

			if (strlen(trim($title)) < 1) {
				$title = '<span>Line 2</span> (page heading) is empty';
			}

			// Put the title back at the top
			$text = trim(implode('', $textArray));

			/* -------------------------------------------------- */
			// Load 'system' texts exactly here
			include(INC . 'lang.php');

			/* -------------------------------------------------- */
			// Process the page title for HTML <title> and Open Graph title
			$titletag = str_ireplace(array('<br>', '<br/>', '<br />'), ' ', $title);
			$titletag = str_replace("\"", "'", $titletag);
			$titletag = strip_tags($titletag);

			/* -------------------------------------------------- */
			// Output some HTML (nav & main & col)
			include(INC . 'top.php');
			_print("\n<div id=\"wrap\">\n\n");
			include(INC . 'extra-body.php');
			include(INC . 'menu.php');
			_print("\n	<main id=\"content\">\n\n		<div class=\"col\">\n\n");

			/* -------------------------------------------------- */
			// If password protected
			if ($password) {

				// If correct password entered
				if (isset($_SESSION['password']) && ($_SESSION['password'] == "{$password}") ) {
					include(INC . 'content.php');
					// See ppp.php for why logout form isn't used
					// require(INC . 'logout-form.php');
				} else {
					// Logged out (show login form)
					include(INC . 'login-form.php');
				}

			} else { // Not password-protected
				include(INC . 'content.php');
			}


			/* -------------------------------------------------- */
			// Output some HTML (end col and main)
			_print_nlab('		</div><!-- end .col //-->');
			_print_nlab('	</main><!-- end #content //-->');

			if (file_exists('./js/slides.js')) { // From the website root
				_print_nlab('<script src="' . LOCATION . 'js/slides.js"></script>');
			}

			if (file_exists('./js/simpleToggle.js')) { // From the website root
				_print_nlb('<script src="' . LOCATION . 'js/simpleToggle.js"></script>');
			}

			if (file_exists('./js/modal-img.js')) { // From the website root
				_print_nlb('<script src="' . LOCATION . 'js/modal-img.js"></script>');
			}

			#if (file_exists('./js/accordion.js')) { // From the website root
			#	_print_nlb('<script src="' . LOCATION . 'js/accordion.js"></script>');
			#}

			if (file_exists('./js/facebook-sdk.js')) {
				_print_nlb('<script src="' . LOCATION . 'js/facebook-sdk.js"></script>');
			}

			if (file_exists('./js/scrollbar-width.js')) {
				_print_nlb('<script src="' . LOCATION . 'js/scrollbar-width.js"></script>');
			}

			if (file_exists('./js/accordion-animated.js')) {
				_print_nlb('<script src="' . LOCATION . 'js/accordion-animated.js"></script>');
			}

			/* -------------------------------------------------- */
			// Output footer
			include(INC . 'footer.php');

			/* -------------------------------------------------- */
			// End #wrap and conclude HTML
			_print("\n</div><!-- end #wrap //-->\n\n</body>\n</html>");

			/* -------------------------------------------------- */
			// Passworded session ends when browser closed
			// Next bit would end session immediately
			// if (isset($_SESSION['password'])) {
			// 	unset($_SESSION['password']);
			// }

		} else {

			/* -------------------------------------------------- */
			// If there is no page.txt file
			header('HTTP/1.1 404 Not Found');
			include(INC . '404.php');
			exit();

		}
	}
}

?>