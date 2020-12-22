<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 22 Dec 2020 */

if(!defined('ACCESS')) {
	die('Direct access not permitted to delete.php');
}

$response = ""; // Initialise

if (isset($_POST['pre-delete'])) {
	$response = '<p class="response">Are you sure you want to delete everything?<br>If not, press Refresh, otherwise press Delete again.</p>';
}

if (isset($_POST['delete'])) { // Deletes everything

	_print_nlab('<p class="response">');

	$the_date = date('l jS F Y H:i:s'); // For listhits.txt
	$since_date = $the_date;
	$time = time();

	$fp1 = fopen("count.txt", "w") or die("Error!");
	if ($fp1) {
		_print_nlb('count.txt emptied<br>');
	}
	fwrite($fp1, "0");
	fclose($fp1);

	$fp2 = fopen("listhits.txt", "w") or die("Error!");
	if ($fp2) {
		_print_nlb('listhits.txt emptied<br>');
	}
	fwrite($fp2, "");
	fclose($fp2);

	$fp3 = fopen("since.txt", "w") or die("Error!");
	if ($fp3) {
		_print_nlb('since.txt updated<br>');
	}
	fwrite($fp3, $time);
	fclose($fp3);

	$fp4 = fopen("tempcount.txt", "w") or die("Error!");
	if ($fp4) {
		_print_nlb('tempcount.txt emptied<br>');
	}
	fwrite($fp4, "0");
	fclose($fp4);

	$fp5 = fopen("pageid.txt", "w") or die("Error!");
	if ($fp5) {
		_print_nlb('pageid.txt emptied<br>');
	}
	fwrite($fp5, "");
	fclose($fp5);

	_print_nlb('</p>');

}
