<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 02 April 2023 */

define('ACCESS', TRUE);

// Declare variables (see also top.php)
$root = $response = $canonical_1 = $canonical_2 = $protocol = $show_protocol = $filestatus = "";

$thisAdmin = 'htaccess'; // For nav

require('./top.php');

// Detect current .htaccess file
$filename = ('../.htaccess');
if (file_exists($filename)) {
	if (strpos(file_get_contents($filename), 'Facebook') !== false) {
		$filestatus = 'extended file';
	} else {
		$filestatus = 'original file';
	}
} else {
	$filestatus = '.htaccess file does not exist.';
}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('.htaccess file generator');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="stylesheet.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	if (!file_exists('./login-form.php')) {
		_print("Error. The file '/admin/login-form.php' does not exist. It must be installed.");
		exit();
	} else {
		require('./login-form.php');
	}

} elseif ($login) {

	/* -------------------------------------------------- */
	// Logged in

/* ================================================== */
/* SECTION 1: GET THE INFO FOR EXTENDED FILE */
/* ================================================== */

	$do_htaccess = TRUE; // Falsified if check fails

	/* -------------------------------------------------- */
	// Get admin folder name

	$admin = basename(dirname(__FILE__));
	if (empty($admin)) {
		$admin = basename(getcwd());
	}
	// (Strip all slashes just in case)
	$admin = str_replace('/', '', $admin);

	/* -------------------------------------------------- */
	// Get server vars

	$httpHost = $_SERVER['HTTP_HOST']; // Domain
	$serverName = $_SERVER['SERVER_NAME']; // Domain
	$serverSoftware = $_SERVER['SERVER_SOFTWARE']; // Apache etc
	$serverScriptName = $_SERVER['SCRIPT_NAME']; // URL path to current script
	// Fallback
	if (!isset($serverScriptName) || empty($serverScriptName)) {
		$serverScriptName = phpSELF();
	}

	/* -------------------------------------------------- */
	// Get the domain as $domain otherwise don't write file

	if (!empty($httpHost)) {
		$domain = $httpHost;
	} elseif (!empty($serverName)) {
		$domain = $serverName;
	} else {
		$do_htaccess = FALSE;
		$response = '<em>Problem: site domain not detected.</em>';
	}

	/* -------------------------------------------------- */
	// Get $path as /path/ for URLs (after domain name)
	// i.e. / for root install or /subfolder/subfolder/ for subfolder install

	$localpath = $serverScriptName;
	$thisfile = basename(__FILE__);
	$strip = "{$admin}/$thisfile";
	$path = str_replace($strip, "", $localpath);
	// Strip multiple slashes just in case
	$path = preg_replace('#/{2,}#', '/', $path);

	/* -------------------------------------------------- */
	// Get protocol (https or http) otherwise don't write file

	if (function_exists('get_protocol')) {
		$protocol = get_protocol() ? 'https://' : 'http://'; // See functions.php
		// Check it's returned one or the other
		// if (($protocol == 'https://') || ($protocol == 'http://')) {
			// echo 'get_protocol function works'; // For testing only
		// }
		if ($protocol == 'https://') {
			$show_protocol = 'https: (secure)'; // Displayed in admin
		} else {
			$show_protocol = 'http: (not secure)'; // Displayed in admin
		}
	} else { // Function doesn't exist
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$protocol = 'https://';
			$show_protocol = 'https: (secure)'; // Displayed in admin
		} else {
			$protocol = 'http://';
			$show_protocol = 'http: (not secure)'; // Displayed in admin
		}
	}

	if ($protocol == " ") {
		$do_htaccess = FALSE;
		$response = '<em>Problem: https or http not detected.</em>';
		$show_protocol = 'not detected.'; // Displayed in admin
	}

	/* -------------------------------------------------- */
	// Make $site_location

	$urlpath = $domain . $path;
	// Strip multiple slashes just in case
	$urlpath = preg_replace('#/{2,}#', '/', $urlpath);
	$site_location = $protocol . $urlpath;

	/* -------------------------------------------------- */
	/* REDIRECT TO CANONICAL URL 1 (www) */

	// Conditions:
	// Condition 1. Has www so (i) must be in root and (ii) not sub-domain
	// so add www if not requested, eg:
	// domain.com/whatever/ -> www.domain.com/whatever/
	// Condition 2. Has no www, eg:
	// domain.com/whatever/ or sub.domain.com/whatever/
	// so remove www if requested, eg:
	// www.domain.com/whatever/ -> domain.com/whatever/

	// Strip www. from domain to get base domain string
	$no_w_domain = preg_replace("/^www\./i", "", $domain);
	// Escape any dots in the base domain string
	$esc_no_w_domain = str_replace('.', '\.', $no_w_domain);

	if (strpos($domain, 'www.') !== FALSE) { // Has www
		// Condition 1. Site has www so redirect to it if not requested
		$canonical_1 = '# Hostname canonicalization redirect 1 has www';
		$canonical_1 .= "\n";
		$canonical_1 .= '  RewriteCond %{HTTP_HOST} !^www\. [NC]';
		$canonical_1 .= "\n";
		$canonical_1 .= '  RewriteRule (.*) ' . $protocol . $domain .  $path . '$1 [R=301,L]';
	} else { // Has no www
		// Condition 2. Site has no www so redirect away from it if requested
		$canonical_1 = '# Hostname canonicalization redirect 1 not www';
		$canonical_1 .= "\n";
		$canonical_1 .= '  RewriteCond %{HTTP_HOST} ^www\. [NC]';
		$canonical_1 .= "\n";
		$canonical_1 .= '  RewriteRule (.*) ' . $protocol . $no_w_domain . $path . '$1 [R=301,L]';
	}

	/* -------------------------------------------------- */
	/* REDIRECT TO CANONICAL URL 2 (http or https) */

	if ($protocol && ($protocol == 'https://')) { // Is https so add the rule
		$canonical_2 = '# Hostname canonicalization redirect 2';
		$canonical_2 .= "\n";
		$canonical_2 .= '  RewriteCond %{HTTPS} !on';
		$canonical_2 .= "\n";
		$canonical_2 .= '  RewriteRule (.*) ' . $protocol . $domain . $path . '$1 [R=301,L]';
	} else { // No rule needed so do nothing
		$canonical_2 = '# Hostname canonicalization redirect 2';
		$canonical_2 .= "\n";
		$canonical_2 .= '  # Not https://';
	}

	/* -------------------------------------------------- */
	// Write the .htaccess file
	// Assume there are now no error responses on initial page load checks

/* ================================================== */
/* SECTION 2: EXTENDED CORE */
/* ================================================== */

	if (isset($_POST['submit1']) && ($do_htaccess == TRUE)) {

		$htaccess_core = '
<IfModule mod_rewrite.c>
  RewriteEngine on

# QUERY STRING

# Redirect Facebook query string to non-query string
  RewriteCond %{QUERY_STRING} ^(.*)(?:^|&)fbclid=(?:[^&]*)((?:&|$).*)$ [NC]
  RewriteCond %1%2 (^|&)([^&].*|$)
  RewriteRule ^(.*) /$1?%2 [R=301,L]

# EXTERNAL REDIRECTS

# Remove index php root only
  RewriteCond %{REQUEST_URI} !(' . $admin . '|diagnostics) [NC]
  RewriteRule ^index\.php$ ' . $site_location . ' [R=301,L]

# Remove php extensions root only
  RewriteCond %{REQUEST_URI} !(' . $admin . '|diagnostics) [NC]
  RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\ (.*)\.php [NC]
  RewriteRule ^(.+)\.php$ ' . $site_location . '$1 [R=301,L]

' . $canonical_1 . '

' . $canonical_2 . '

# INTERNAL REWRITES

# Forbid direct viewing txt files in pages folder
  RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\ (.*)/(pages)/(.*)\.txt [NC]
  RewriteRule ^ "-" [F]

# Rewrite non php URLs to php on server
  #RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME}\.php -f
  RewriteRule ^(.*)$ $1.php [L]

# If not found then relative path to error 404 file
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule .* inc/404.php [L]

</IfModule>
';

	$filestatus = 'extended file';

	}

/* ================================================== */
/* SECTION 3: DEFAULT CORE */
/* ================================================== */

	if (isset($_POST['submit2']) && ($do_htaccess == TRUE)) {

		$htaccess_core = '
<IfModule mod_rewrite.c>
  RewriteEngine on
# Forbid direct viewing of txt files in pages folder
  RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\ (.*)/pages/(.*)\.txt [NC]
  RewriteRule ^ "-" [F]
# Rewrite non php URLs to php on server
# php URLs still usable
#     is not a directory
  RewriteCond %{REQUEST_FILENAME} !-d
#     is a php file
  RewriteCond %{REQUEST_FILENAME}\.php -f
# Internally rewrite to actual php file
  RewriteRule ^(.*)$ $1.php [L]
# If not found, relative path to error 404 file
#     is not an actual file or directory
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule .* inc/404.php [L]
</IfModule>
';

	$filestatus = 'original file';

	}

/* ================================================== */
/* SECTION 4: SUBMIT SELECTION (1 or 2) */
/* ================================================== */

	if ( ( isset($_POST['submit1']) || isset($_POST['submit2']) ) && ($do_htaccess == TRUE) ) {

		/* -------------------------------------------------- */
		// Test for markers / replace core lines
		if (file_exists('../.htaccess')) {

			// Get entire .htaccess as string
			$htaccess_text = file_get_contents('../.htaccess');

			if ((strpos($htaccess_text, '# BEGIN superMicro CMS') != FALSE) || (strpos($htaccess_text, '# END superMicro CMS') != FALSE)) {
				// Get required lines into variable "$to_replace"
				$to_replace = getBetween($htaccess_text, '# BEGIN superMicro CMS', '# END superMicro CMS');
				// Replace core lines
				$str = str_replace($to_replace, $htaccess_core, $htaccess_text);
				// Write to file
				file_put_contents('../.htaccess', $str);
				// _print($str); /* For testing */
				$response = '<em>.htaccess file created</em>';
			} else {
				$response = '<em>.htaccess file not touched (markers not found)</em>';
			}

		} else {
			$response = '<em>.htaccess file does not exist</em>';
		}

		$cms_dir = NULL;

	}

/* ================================================== */
/* SECTION 5: START PAGE, H1 & NAVIGATION MENU */
/* ================================================== */

?>

<div id="wrap">

<h1><?php

	if (function_exists('h1')) {
		h1('.htaccess file');
	} else {
		_print('Install the latest version of functions.php');
	}

?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error. The file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

?>

<h3>.htaccess generator for superMicro CMS (Apache Web Server only)</h3>

	<div id="response">

<?php

/* ================================================== */
/* SECTION 6: TOP BOX FEEDBACK */
/* ================================================== */

	_print('<p><span class="padded-multiline">');
	if (!$response) {
		_print('<em>No action requested.</em>');
	} else {
		_print($response);
	}
	_print('</span></p>');

?>

	</div>

<h3>What is .htaccess?</h3>

<p style="max-width: 640px;">An .htaccess file is a configuration file used on Apache Web Server only. <a href="" target="_blank">Read about .htaccess here</a> on Wikipedia. Alternatively, the basics <a href="https://www.danielmorell.com/guides/htaccess-seo/basics/introduction-to-the-htaccess-file" target="_blank">are covered here</a>. On this website, choose between the original simpler file or an extended file with more functions. Both work fine on Apache web server.</p>


<h3>Settings detected</h3>

<?php

/* ================================================== */
/* SECTION 7: FORM */
/* ================================================== */

?>

<form action="" method="post" accept-charset="UTF-8">

	<div id="info">

<?php

_print("
<p>Server software = {$serverSoftware}</p>
<p>Domain = {$domain}</p>
<p>Site location = {$site_location}</p>
<p>Protocol = {$show_protocol}</p>
<p>Admin folder = {$admin}</p>
<p>Current .htaccess = {$filestatus}</p>
<p><a href=\"https://web.patricktaylor.com/cms-htaccess\" target=\"_blank\">Info here</a>&nbsp;&raquo;</p>
");

/* For testing
echo '<p>Canonical redirect 1 = <br>' . $canonical_1 . '</p>';
echo "\n";
echo '<p>Canonical redirect 2 = <br>' . $canonical_2 . '</p>';
echo "\n";
*/
?>

	</div>

<input type="submit" name="submit1" class="images" value="Create extended"> <input type="submit" name="submit2" class="images" value="Create original">

</form>

<?php

	include('./footer.php');

} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>

</body>
</html>