<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 14 Sept 2020 */

// Declare variable
$obj = '';

// If installed
if (file_exists('./inc/settings.php')) {
	require('./inc/html.php');
	$obj = new Page; // Object
	$obj->Textfilename = 'index.txt'; // Property
	$obj->Template(); // Method
} else {
	echo 'superMicro CMS is not yet installed.';
}

?>
