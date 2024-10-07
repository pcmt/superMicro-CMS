<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 10 July 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to content.php');
}

/* START CONTENT ==================================== */
$content_string = '';
$content_string .= "\n<h1>" . $title . "</h1>\n";
if ($pageID == 'preview') {
	$content_string .= '<h5><small><span class="orange">[ preview ]</span></small></h5>';
}
$content_string .= $text;
$content_string .= "\n\n";

/* -------------------------------------------------- */
// Pass the page through the functions (order critical)
$content = autop($content_string);
$content = bits_and($content);
$content = absolute_it($content);

if (PHP_EXT || !APACHE) {
	$content = suffix_it($content);
}

$content = img_path($content);
$content = video_path($content);

/* -------------------------------------------------- */
// If content is set and actually exists
if ($content && (strlen($content) > 0)) {

	_print($content);

	if (file_exists(INC . 'extra-content.php')) {
		include(INC . 'extra-content.php');
	}

	if (file_exists(INC . 'history.php')) {
		include(INC . 'history.php');
	}

	if ($share) { ?>
		<div class="social">

			<div class="fb-share-button" data-href="<?php echo $canonical; ?>" data-layout="button" data-size="large"></div>

		</div>
	<?php }

	/* -------------------------------------------------- */
	// Date modified
	if (function_exists('filemtime')) {
		$modified = date("d F, Y", filemtime($source));
	} else {
		$modified = 'date unknown';
	}

	_print("\n<p class=\"meta\">" . TEXT00 . ' ' . $modified . "</p>\n");

	/* -------------------------------------------------- */

	if ($validate || $pageID == 'preview') {
		$suffix = (PHP_EXT || !APACHE)
			? '.php'
			: '';
		$validate_link = '<div class="faux-button"><p><a href="https://validator.w3.org/nu/?doc=' . LOCATION . $pageID . $suffix . '" title="W3C HTML validator" target="_blank">Validate HTML</a></p></div>';
		_print_nlab($validate_link);
	}

} else {
	_print("\n<p>No content. Something needs fixing.</p>\n");
}
/* END CONTENT ====================================== */

?>