<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Oct 2023 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to nav.php');
}

?>
<p id="nav"><a href="<?php

if (defined('LOCATION')) {
	_print(LOCATION);
} else {
	_print('../');
}

?>"><span class="zap">&#171;&nbsp;</span>Site</a> <?php

if ($thisAdmin == 'index') {
	_print('<span>Pages</span> ');
} else {
	_print('<a href="./index.php" title="Create/edit/delete pages">Pages</a> ');
}

if ($thisAdmin == 'images') {
	_print('<span>Uploads</span> ');
} else {
	_print('<a href="./images.php" title="Upload or delete images">Uploads</a> ');
}

if ($thisAdmin == 'htaccess') {
	_print('<span>.htaccess</span> ');
} else {
	_print('<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> ');
}

if ($thisAdmin == 'backup') {
	_print('<span>Backup</span> ');
} else {
	_print('<a href="./backup.php" title="Backup">Backup</a> ');
}

if ($thisAdmin == 'setup') {
	_print('<span>Setup</span> ');
} else {
	_print('<a href="./setup.php" title="Setup">Setup</a> ');
}

?><a href="?status=logout" title="Logout">Logout</a> <a href="https://web.patricktaylor.com/" title="Help" class="ext" target="_blank">Help<span class="zap">&nbsp;&#187;</span></a></p>
