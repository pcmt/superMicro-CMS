<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 19 Dec 2020 */

if(!defined('ACCESS')) {
	die('Direct access not permitted to counts.php');
}

$visits = file_get_contents("count.txt");
$since = file_get_contents("since.txt"); // Timestamp
$formatsince = date('D d M Y (H:i:s)', (int)$since);
_print_nlb('<p>Total hits: <strong>' . $visits . '</strong> since delete on ' . $formatsince . '</p>');

// Calculate hits per day
$start = strtotime(date('Y-m-d', (int)$since));
$end = strtotime(date('Y-m-d', time()));

// $days_between = ceil(abs($end - $start) / 86400);
$days_between = (($end - $start) / 86400);
$dayscounted = $days_between + 1;
$hitsperday = ceil(abs($visits / $dayscounted));
_print_nlb('<p>Days counted since delete: <strong>' . $dayscounted . '</strong>, so <strong>' . $hitsperday . '</strong> hits per day on average</p>');

?>
