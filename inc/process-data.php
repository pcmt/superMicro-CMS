<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 July 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to process-data.php');
}

// Declare variables
$file_contents = $dataArray = $description = $num = "";

$datafile = 'data/' . $pageID . '.txt';

if (file_exists($datafile)) {

	$file_contents = file_get_contents($datafile);
	$dataArray = explode(PHP_EOL, $file_contents);
	$num = count($dataArray);

	// Extract line 1 for meta description and Open Graph
	$description = stripslashes(trim($dataArray[0]));

	if ($num == 12) {
		// Next 11 lines as data
		$nextLines = array_slice($dataArray, 1, 12);

		// Join them into a single string with line breaks
		$resultString = implode("\n", $nextLines);

		$structured_data = '<script type="application/ld+json">
{
"@context": "https://schema.org/",' . "\n" . 
$resultString . '
}
</script>';
	}

}
?>