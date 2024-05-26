<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 21 May 2024 */

ob_start();
header('HTTP/1.1 404 Not Found');

// Declare variables
$protocol = $anchor = $output = NULL;

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

define('ACCESS', TRUE); // For settings.php

$settings = dirname(__FILE__) . '/settings.php';
include($settings);

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

if ($protocol) {
	$anchor = str_replace($protocol, '', LOCATION);
	$output = '<p>&#8212;&nbsp;<a href="' . LOCATION . '">' . $anchor . '</a>&nbsp;&#8212;</p>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Page not found</title>

<style>

* { margin: 0; padding: 0; }

body {
  margin: 30px;
  background: #f9f9f9;
  color: #000;
  font-family: 'Verdana', Sans-serif;
  font-size: 100%;
  text-align: center;
  line-height: 1.0;
}

#wrap {
  margin: 0 auto;
  padding: 40px;
  width: auto;
  max-width: 320px;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1.0em;
  line-height: 1.6;
}

a {
  color: #ff0099;
  text-decoration: none;
}

a:hover {
  color: #000;
  text-decoration: none;
}

h1 {
  font-size: 1.2em;
  margin-bottom: 0.6em;
}

p {
  color: #000;
  font-size: 0.9em;
}

img {
  margin: 0;
  padding: 0.4em 0 0.6em 0;
}

</style>

</head>
<body>

	<div id="wrap">

<h1>Sorry. Not found.</h1>

<img src="<?php echo LOCATION; ?>img/og.jpg" width="200" height="200" alt="">

<?php

if ($protocol) { // No protocol, no output
	echo $output;
	echo "\n";
}

?>

	</div>

</body>
</html>
