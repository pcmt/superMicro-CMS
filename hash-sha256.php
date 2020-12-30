<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 Dec 2020 */

define('ACCESS', TRUE);

// Declare variables
$admin = $response1 = $response2 = $problem1 = $problem2 = "";
$pageID = 'hash-sha256';

// Define absolute path to /inc/ folder (as in html.php)
$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/inc/';
define('INC', $_inc);

if (file_exists(INC . 'prelims.php')) {
	require(INC . 'prelims.php');
} else {
	echo 'Error. Please install the file /inc/prelims.php';
	exit();
}

function allowedChars($str) {

	$charArray = array(
		/* Symbols etc (not quite the same as admin/functions.php) */
		'_', '!', '$', '%', '^', '?', '*', '+', '=', '@', '~', '#', '[', ']', '{', '}', '(', ')',
		/* Numbers */
		'1', '2', '3', '4', '5','6', '7', '8', '9', '0',
		/* English */
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		/* Latin */
		'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ő', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ű', 'Ý', 'Þ', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ő', 'ø', 'ù', 'ú', 'û', 'ü', 'ű', 'ý', 'þ', 'ÿ',
		/* Greek */
		'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'Ά', 'Έ', 'Ί', 'Ό', 'Ύ', 'Ή', 'Ώ', 'Ϊ', 'Ϋ', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'ά', 'έ', 'ί', 'ό', 'ύ', 'ή', 'ώ', 'ς', 'ϊ', 'ΰ', 'ϋ', 'ΐ',
		/* Turkish */
		'Ş', 'İ', 'Ç', 'Ü', 'Ö', 'Ğ', 'ş', 'ı', 'ç', 'ü', 'ö', 'ğ',
		/* Russian */
		'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
		/* Ukrainian */
		'Є', 'І', 'Ї', 'Ґ', 'є', 'і', 'ї', 'ґ',
		/* Czech */
		'Č', 'Ď', 'Ě', 'Ň', 'Ř', 'Š', 'Ť', 'Ů', 'Ž', 'č', 'ď', 'ě', 'ň', 'ř', 'š', 'ť', 'ů', 'ž',
		/* Polish */
		'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
		/* Latvian */
		'Ā', 'Č', 'Ē', 'Ģ', 'Ī', 'Ķ', 'Ļ', 'Ņ', 'Š', 'Ū', 'Ž', 'ā', 'č', 'ē', 'ģ', 'ī', 'ķ', 'ļ', 'ņ', 'š', 'ū', 'ž'
	);

	$allowed = implode("", $charArray);
	// Strip everything except $allowed
	$str = preg_replace('/[^' . $allowed . ']+/u', '', $str);
	$str = preg_replace('/[[:space:]]+/', ' ', $str);

	return $str;

}

// Form
if (isset($_POST['submit'])) { // Handle the form.

	$string1 = trim($_POST['password']);
	$string1 = stripslashes($string1);
	$string1 = allowedChars($string1);

	$string2 = trim($_POST['salt']);
	$string2 = stripslashes($string2);
	$string2 = allowedChars($string2);

	if (strlen(trim($string1)) < 1) {
		$response1 = '<p><span>Enter your chosen password.</span></p>';
		$problem1 = TRUE;
	}

	if (strlen(trim($string2)) < 1) {
		$response2 = '<p><span>Add some salt.</span></p>';
		$problem2 = TRUE;
	}

	if (!$problem1 && !$problem2) {
		$fullstring = $string1 . $string2;
		$hashed = hash('sha256', $fullstring);
		$response1 = '<p><span>Your [salted and hashed] password is:</span></p>
<p><mark>' . $hashed . '</mark></p>
<p><span>Copy and paste into \'Install\' together with \'Salt\' below. Remember the actual password.</span></p>
<hr>';
	}

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hash a password with salt</title>
<link rel="shortcut icon" href="favicon.ico">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<?php

include(INC . 'stylesheets.php');
_print("\n");

?>
<style>
.response p { color: #000; }
.response p mark:hover { background: #ebebeb; }
</style>

</head>

<body>

<div id="wrap">

<?php include(INC . 'menu.php'); ?>

	<main id="content">

<h1>Hash a password with salt</h1>

<h4>Admin password processing for superMicro <span>CMS</span></h4>

<hr>

<?php if (!isset($_POST['submit'])) { ?>
<p>Enter your password and salt then press 'Salt &amp; Hash'.</p>
<?php } ?>

<?php

// Response section
if (isset($_POST['submit'])) {
	echo "\n";
	echo '		<div class="response">';
	echo "\n\n";
	echo $response1;
	echo $response2;
	echo "\n\n";
	echo '		</div>';
	echo "\n";
}

?>

<form method="post" class="contactform" action="">
<input type="text" name="password" id="password" size="22" value="<?php

if (isset($_POST['submit'])) {
	echo $string1;
} else {
	echo 'password';
} 

?>" maxlength="60"><label for="password"><?php

if (isset($_POST['submit']) && !$problem1) {
	echo 'Password';
} else {
	echo 'Enter a long unguessable password';
}

?></label><br>
<input type="text" name="salt" id="salt" size="22" value="<?php

if (isset($_POST['submit'])) {
	echo $string2; 
} else {
	echo 'salt';
}

?>" maxlength="10"><label for="salt"><?php

if (isset($_POST['submit']) && !$problem2) {
	echo 'Salt';
} else {
	echo 'Enter up to 10 characters as salt';
}

?></label><br>
<input type="submit" name="submit" class="submit" value="Salt &amp; Hash">
</form>

<p>Allowed special characters: <strong>$ ! _ % ^ ? * + = @ ~ # [ ] { } ( )</strong></p>

<hr>

<section>

<h6>Hashing and salting for passwords</h6>
<p>Hashing a password converts it into a long hexadecimal number that is difficult to decode, especially with added salt.</p>
<p>Logging in to a password-protected page is done using the actual password (un-hashed). The system then hashes it and checks that it matches the original [salted and hashed] version. The actual password should always be hard to 'guess' &#8211; not a memorable word but a meaningless mix of characters, eg:</p>
<p><strong>n38]E*s5j4h5^</strong></p>
<p>Mix upper and lower case letters with numerals and keyboard symbols.</p>
<p>The salt can be a few random characters.</p>

		<div class="bg2">

<p>Make sure you have copied and saved (i) the [salted and hashed] password generated by this page, (ii) your original password, and (iii) your 'salt'. The <strong>original password</strong> is used to login to admin, not the [salted and hashed] version. The [salted and hashed] password and 'salt' are only required when installing.</p>

		</div>

<h4>Improve security</h4>

		<div class="bg1">

<p>(1) Choose an unguessable password.</p>
<p>(2) Change the name of the /<strong>admin</strong>/ folder.</p>
<p>(3) Logout of admin after every admin session.</p>
<p>(4) Make sure everything gets backed up.</p>
<p>(5) Run a website as <strong>https://</strong> (not http://).</p>

		</div>

<p>This page applies only to the 'admin' password, not public pages. For password-protecting public pages see <a href="https://supermicrocms.com/passwords">passwords</a>&nbsp;&raquo;</p>

<hr class="section">

</section>

<p class="meta">Page last modified: 29 December, 2020</p>

	</main>

<?php

include(INC . 'footer.php');

?>

</div>

</body>
</html>
