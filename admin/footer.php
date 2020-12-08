<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 07 Dec 2020 */
/* Updated version to 3.10 */

?>

<p id="footer"><a href="https://supermicrocms.com/" title="superMicro CMS" target="_blank">superMicro CMS</a> version <em>3.10</em> &copy;&nbsp;<a href="https://patricktaylor.com/" title="Patrick Taylor" target="_blank">Patrick Taylor</a> 2008&#8211;<?php

	echo date("Y");
	global $tm_start;
	$parse_time = 0;
	$parse_time = (array_sum(explode(' ', microtime())) - $tm_start);
	$parse_time = number_format($parse_time, 3);
	echo '&nbsp;<span>&#124;</span> Page served in ' . $parse_time . ' secs'; ?></p>
