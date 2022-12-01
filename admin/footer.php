<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 Nov 2022 */
/* Updated version to 3.11 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to footer.php');
}

?>

<p id="footer"><a href="https://web.patricktaylor.com/cms" title="superMicro CMS" target="_blank">superMicro CMS</a> version <em>3.11</em> &copy;&nbsp;<a href="https://patricktaylor.com/" title="Patrick Taylor" target="_blank">Patrick Taylor</a> 2008&#8211;<?php

	_print(date("Y"));
	global $tm_start;
	$parse_time = 0;
	$parse_time = (array_sum(explode(' ', microtime())) - $tm_start);
	$parse_time = number_format($parse_time, 3);
	_print('&nbsp;<span>&#124;</span> Page served in ' . $parse_time . ' secs'); ?></p>

</div>
