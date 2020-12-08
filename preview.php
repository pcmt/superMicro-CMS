<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 14 Sept 2020 */

$obj = '';
include('./inc/html.php');
$obj = new Page;
$obj->Textfilename = 'preview.txt';
$obj->Template();

?>
