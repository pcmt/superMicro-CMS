<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 27 Sept 2024 */
// Triple ===

define('ACCESS', TRUE);

// Declare variables (see also top.php)
$root = $response = $canonical_1 = $canonical_2 = $show_protocol = $filestatus = $g_zip_enabled = $GZIP_status = $htaccess_text = $to_replace1 = $to_replace2 = $has_GZIP = $not_active = "";
$add_GZIP = 0;

$thisAdmin = 'htaccess'; // For nav

include('./top.php');

// Detect current .htaccess file
$filename = ('../.htaccess');
if (file_exists($filename)) {
	$htaccess_text = file_get_contents($filename);
	if ($htaccess_text !== FALSE) {
		if (strpos($htaccess_text, 'Facebook') !== FALSE) {
			$filestatus = 'extended file';
		} else {
			$filestatus = 'original file';
		}
	} else {
		$filestatus = 'Error reading .htaccess file';
	}
} else {
	$filestatus = '.htaccess file does not exist';
}

if (defined('G_ZIP') && G_ZIP) {
	$g_zip_enabled = TRUE;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('.htaccess file generator'); ?></title>
<?php includeFileIfExists('./icons.php'); ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	includeFileIfExists('./login-form.php');

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

	$serverSoftware = $_SERVER['SERVER_SOFTWARE']; // Apache etc (for display only)
	$serverScriptName = $_SERVER['SCRIPT_NAME']; // URL path to current script
	// Fallback
	if (!isset($serverScriptName) || empty($serverScriptName)) {
		$serverScriptName = phpSELF();
	}

	/* -------------------------------------------------- */
	// top.php gets $domain or don't write file

	if (!$domain) { // From top.php
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
	// Get protocol (https or http) or don't write file (see top.php)

	if ($protocol == 'https://') {
		$show_protocol = 'https: <span>(secure)</span>'; // Displayed in admin
	} elseif ($protocol == 'http://') {
		$show_protocol = 'http: <span>(not secure)</span>'; // Displayed in admin
	} else {
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
	/* GZIP text */
	$g_zip_text = '
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE application/javascript
</IfModule>
';

	$empty_g_zip_text = '
#
';

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

# Exclude static files from being rewritten then
# rewrite non php URLs to php on server
  RewriteCond %{REQUEST_URI} !\.(css|js|jpg|jpeg|png|gif|ico|svg)$ [NC]
  RewriteCond %{REQUEST_FILENAME} !-d
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

# Forbid direct viewing txt files in pages folder
  RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\ (.*)/(pages)/(.*)\.txt [NC]
  RewriteRule ^ "-" [F]

# Exclude static files from being rewritten then
# rewrite non php URLs to php on server
  RewriteCond %{REQUEST_URI} !\.(css|js|jpg|jpeg|png|gif|ico|svg)$ [NC]
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME}\.php -f
  RewriteRule ^(.*)$ $1.php [L]

# If not found then relative path to error 404 file
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

	if ( ( isset($_POST['submit1']) || isset($_POST['submit2']) ) && ($do_htaccess === TRUE) ) {

		/* -------------------------------------------------- */
		// (1) Deal with GZIP first (add or remove without affecting original or extended)
		if(isset($_POST['gzip_status']) && $_POST['gzip_status'] == 'use') {
			$add_GZIP = TRUE;
			if ((strpos($htaccess_text, '# BEGIN GZIP') !== FALSE) || (strpos($htaccess_text, '# END GZIP') !== FALSE)) { // If the markers exist

				// Get required lines into variable "$to_replace1"
				$to_replace1 = getBetween($htaccess_text, '# BEGIN GZIP', '# END GZIP');
				// Remove it
				$htaccess_text = str_replace($to_replace1, $g_zip_text, $htaccess_text);

			}
		} else {
			if ((strpos($htaccess_text, '# BEGIN GZIP') !== FALSE) || (strpos($htaccess_text, '# END GZIP') !== FALSE)) { // If the markers exist

				// Get required lines into variable "$to_replace1"
				$to_replace1 = getBetween($htaccess_text, '# BEGIN GZIP', '# END GZIP');
				// Remove it
				$htaccess_text = str_replace($to_replace1, $empty_g_zip_text, $htaccess_text);

			}
		}

		// Check whether $g_zip text already exists
		if ($add_GZIP && strpos($htaccess_text, '<IfModule mod_deflate.c>') !== FALSE) {
			$g_zip_text = ''; // Don't add it again
		}

		/* -------------------------------------------------- */
		// (2) Now deal with original or extended (having dealt with GZIP)
		if ((strpos($htaccess_text, '# BEGIN superMicro CMS') !== FALSE) || (strpos($htaccess_text, '# END superMicro CMS') !== FALSE)) { // If the markers exist

			// Get required lines into variable "$to_replace2"
			$to_replace2 = getBetween($htaccess_text, '# BEGIN superMicro CMS', '# END superMicro CMS');
			// Replace core lines
			$str = str_replace($to_replace2, $htaccess_core, $htaccess_text);
			// Write to file
			file_put_contents('../.htaccess', $str);
			$response = '<em>.htaccess file created</em>';

		} else {
			$response = '<em>.htaccess file not touched (markers not found)</em>';
		}

		$cms_dir = NULL;

	}

/* ================================================== */
/* SECTION 5: START PAGE, H1 & NAVIGATION MENU */
/* ================================================== */

?>

<div id="o"><div id="wrap">

<h1><?php h1('.htaccess file'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

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

<p style="margin-top: 0; max-width: 640px;">An .htaccess file is a configuration file used on Apache Web Server only. <a href="" target="_blank">Read about .htaccess here</a> on Wikipedia. Alternatively, the basics <a href="https://www.danielmorell.com/guides/htaccess-seo/basics/introduction-to-the-htaccess-file" target="_blank">are covered here</a>. On this website, choose between the original simpler file or an extended file with more functions. <em>Needs mod_rewrite module enabled.</em></p>

<h3>FAILSAFE</h3>

<p style="margin-top: 0; max-width: 640px;"><strong>NOTE!</strong> The .htaccess file is in the website root folder so affects everything, including the admin pages. On Apache Web Server an error could disable the entire website. Qwwwik is throughly tested but if this occurs for some reason, the root folder contains a pristine file: 'htaccess.txt' to use to restore the website by renaming it <b><em>.htaccess</em></b> after first deleting the existing .htaccess file.</p>


<h3>Settings detected</h3>

<?php

/* ================================================== */
/* SECTION 7: FORM */
/* ================================================== */

?>

<form action="" method="post" accept-charset="UTF-8">

	<div id="info">

<?php

$GZIP_status = $g_zip_enabled ? 'offers' : "doesn't offer";

if (strpos($htaccess_text, '<IfModule mod_deflate.c>') !== FALSE) {
	$has_GZIP = '<span>(&#43; GZIP compression)</span>';
} else {
	$has_GZIP = '<span>(no GZIP compression)</span>';
}

if (!APACHE) {
	$not_active = '<br>(not Apache, not active, GZIP compression not active)';
}

_print("
<p>Server software = {$serverSoftware}</p>
<p>Domain = {$domain}</p>
<p>Site location = {$site_location}</p>
<p>Protocol = {$show_protocol}</p>
<p>Admin folder = {$admin}</p>
<p>Server {$GZIP_status} GZIP compression</p>
<p>Current .htaccess = {$filestatus} {$has_GZIP} {$not_active}</p>
<p><a href=\"https://qwwwik.com/htaccess\" target=\"_blank\">Info here</a>&nbsp;&raquo;</p>
");

/* For testing
_print_nlb('<hr>$do_htaccess = ' . $do_htaccess . '');
_print_nlb('<hr>$add_GZIP = ' . $add_GZIP . '');
_print_nlb('<hr>$htaccess_text = ' . $htaccess_text . '<br><hr>');
_print_nlb('$to_replace1 = ' . $to_replace1 . '<br><hr>');
_print_nlb('Canonical redirect 1 = <br>' . $canonical_1 . '<br><hr>');
_print_nlb('Canonical redirect 2 = <br>' . $canonical_2 . '');
*/
?>

	</div>
<?php if ($g_zip_enabled) { ?>

<label for="GZIP" class="checkbox-container"><input type="checkbox" name="gzip_status" id="GZIP" value="use"> Include GZIP compression</label>

<?php } ?>
<input type="submit" name="submit1" class="stacked" value="Create extended">
<input type="submit" name="submit2" class="stacked" value="Create original">
<input type="submit" name="" class="stacked fade" value="Reset page">

</form>

<?php

	includeFileIfExists('./footer.php');

} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>
</div></div>

</body>
</html>