<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 Nov 2022 */

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
<link rel="stylesheet" href="stylesheet.css" type="text/css">
<meta name="robots" content="noindex,nofollow">

</head>

<body>

<div id="wrap">

<h1>Page Stats<br><span><a href="<?php _print($site); ?>" target="_blank"><?php _print($site); ?></a></span></h1>

	<main>

<?php

if (isset($_SESSION['password']) && $_SESSION['password'] == "v") {

?>

<!-- CONTENT (correct password entered) -->

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

<p>Hits per page with overall running total to 1000 maximum:</p>

		<div id="results">

<ol>
<?php

	// Identify each pageID and count the number of times it appears in the list
	// Get text files into array
	$pageidArray = file('pageid.txt');
	$list = array_count_values($pageidArray);
	arsort($list, SORT_NUMERIC);

	$running = 0;

	foreach ($list as $page => $num) {
		$page = str_replace("\n", "", trim($page));
		if (!APACHE) { // Add .php extension
			$page = $page . '.php';
		} elseif ($page == 'index') {
			$page = str_replace("index", "", $page);
		}

		$running = $running + $num;

		_print_nlb('<li class="index"><a href="' . LOCATION . $page . '" target="_blank"><span>' . LOCATION .'</span>' . $page . '</a> : ' . $num . ' <span class="mute">: ' . $running . '</span></li>');
	}

?>
</ol>

		</div>

<p class="footer"><a href="https://web.patricktaylor.com/cms" target="_blank">superMicro CMS</a></p>

<!-- END OF CONTENT -->

<?php } else { ?>

<form class="pw" method="post" action="">
<input type="password" name="pass">
<input class="password" type="submit" name="submit_pass" value="Submit">
</form>

<?php

	if ($error) {
		_print($error); // Password error
	}

}

?>

	</main>

</div>

</body>
</html>
