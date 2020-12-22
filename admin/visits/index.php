<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 22 Dec 2020 */

if (file_exists('./top.php')) {
	require('./top.php');
} else {
	die('Error: /admin/visits/top.php not found');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>superMicro CMS stats</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<link rel="stylesheet" href="stylesheet.css" type="text/css">
<meta name="robots" content="noindex,nofollow">

</head>

<body>

<div id="wrap">

<?php

if ( isset($_SESSION['password']) && $_SESSION['password'] == "v" ) {

?>

<!-- CONTENT (correct password entered) -->

<h1>Page stats for <span><a href="<?php _print($site); ?>" target="_blank"><?php _print($site); ?></a></span></h1>

<?php

	if (file_exists('./delete.php')) {
		include('./delete.php');
	} else {
		die('Error: /admin/visits/delete.php not found');
	}

?>

<form action="" method="post">
<input class="dark" type="submit" name="<?php

	if ( isset($_POST['pre-delete']) ) {
		_print('delete'); // Name after pre-delete
	} else {
		_print('pre-delete'); // Initial name
	}

?>" title="Are you sure?" value="Delete"> <input class="light" type="submit" name="refresh" value="Refresh">
</form>
<!--
<form method="post" action="" id="logout">
<input type="submit" name="page_logout" value="Logout">
</form>
//-->

<?php

if ($response) {
	_print_nlab($response);
}

?>

<hr>

<p>See also <a href="./list.php" title="List of hits">list of hits</a>&nbsp;&raquo;</p>
<?php

	if (file_exists('./counts.php')) {
		include('./counts.php');
	} else {
		die('Error: /admin/visits/counts.php not found');
	}

?>

<p>Hits per page:</p>

	<div id="results">

<ol>
<?php

	// Identify each pageID and count the number of times it appears in the list
	// Get text files into array
	$pageidArray = file('pageid.txt');
	$list = array_count_values($pageidArray);
	arsort($list, SORT_NUMERIC);
	foreach ($list as $page => $num) {
		$page = str_replace("\n", "", trim($page));
		// $anchor = $page;
		if (!APACHE) { // Add .php extension
			$page = $page . '.php';
		} elseif ($page == 'index') {
			$page = str_replace("index", "", $page);
		}
		_print_nlb('<li><a href="' . LOCATION . $page . '" target="_blank"><span>' . LOCATION .'</span>' . $page . '</a> : ' . $num .'</li>');
	}

?>
</ol>

	</div>

<p><a href="https://supermicrocms.com/" target="_blank">supermicrocms.com</a></p>

<!-- END OF CONTENT -->

<?php } else { ?>

<form method="post" action="">
<input type="password" name="pass">
<input class="password" type="submit" name="submit_pass" value="Submit">
</form>

<?php

	if ($error) {
		_print($error); // Password error
	}

}

?>

</div>

</body>
</html>
