<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 26 Jan 2021 */

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

$days_between = (($end - $start) / 86400);
$dayscounted = $days_between + 1;
$hitsperday = ceil(abs($visits / $dayscounted));
if ($dayscounted == 1) {
	_print_nlb('<p><strong>' . $dayscounted . '</strong> day counted since delete, so <strong>' . $hitsperday . '</strong> hits per day on average</p>');
} else {
	_print_nlb('<p><strong>' . $dayscounted . '</strong> days counted since delete, so <strong>' . $hitsperday . '</strong> hits per day on average</p>');
}
?>
