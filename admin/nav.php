<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Jan 2021 */

?>
<p id="nav"><a href="<?php _print(LOCATION); ?>"><span class="zap">&#171;&nbsp;</span>Site</a> <?php

if ($thisAdmin == 'index') {
	_print('<span>Pages</span> ');
} else {
	_print('<a href="./index.php" title="Create/edit/delete pages">Pages</a> ');
}

if ($thisAdmin == 'images') {
	_print('<span>Images</span> ');
} else {
	_print('<a href="./images.php" title="Upload or delete images">Images</a> ');
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

?><a href="./visits/" title="Visits" target="_blank">Visits</a> <a href="?status=logout" title="Logout">Logout</a> <a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help<span class="zap">&nbsp;&#187;</span></a></p>
