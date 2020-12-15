<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 Dec 2020 */
/* .svg added */

if(!defined('ACCESS')) {
	die('Direct access not permitted to functions.php.');
}

/* --------------------------------------------------
 * General
 */

function _print($text) {
	echo $text;
}

function _print_nla($text) { // Newline above
	echo "\n" . $text;
}

function _print_nlab($text) { // Newline above and below
	echo "\n" . $text . "\n";
}

function _print_nlb($text) { // Newline below
	echo $text . "\n";
}

/* --------------------------------------------------
 * content.php
 */

/*
 * Newline preservation help function for autop()
 * (see autop 'Optionally insert line breaks').
 */

/* Lifted from WordPress, last updated 27 Aug 2020 */

function _autop_newline_preservation_helper( $matches ) {
    return str_replace( "\n", '<WPPreserveNewline />', $matches[0] );
}

/* --------------------------------------------------
 * content.php
 */

/*
 * A group of 'replaces' identify text formatted with newlines and
 * replace double line-breaks with HTML <p></p> tags. The remaining
 * line-breaks after conversion become <br> tags. return = $content
 */

/* Lifted from WordPress, last updated 27 Aug 2020 */
/* THE ONLY EDIT is comment out function "wp_replace_in_html_tags" */

function autop( $pee, $br = true ) {
    $pre_tags = array();
 
    if ( trim( $pee ) === '' ) {
        return '';
    }
 
    // Just to make things a little easier, pad the end.
    $pee = $pee . "\n";
 
    /*
     * Pre tags shouldn't be touched by autop.
     * Replace pre tags with placeholders and bring them back after autop.
     */
    if ( strpos( $pee, '<pre' ) !== false ) {
        $pee_parts = explode( '</pre>', $pee );
        $last_pee  = array_pop( $pee_parts );
        $pee       = '';
        $i         = 0;
 
        foreach ( $pee_parts as $pee_part ) {
            $start = strpos( $pee_part, '<pre' );
 
            // Malformed HTML?
            if ( false === $start ) {
                $pee .= $pee_part;
                continue;
            }
 
            $name              = "<pre wp-pre-tag-$i></pre>";
            $pre_tags[ $name ] = substr( $pee_part, $start ) . '</pre>';
 
            $pee .= substr( $pee_part, 0, $start ) . $name;
            $i++;
        }
 
        $pee .= $last_pee;
    }
    // Change multiple <br>'s into two line breaks, which will turn into paragraphs.
    $pee = preg_replace( '|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee );
 
    $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
 
    // Add a double line break above block-level opening tags.
    $pee = preg_replace( '!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee );
 
    // Add a double line break below block-level closing tags.
    $pee = preg_replace( '!(</' . $allblocks . '>)!', "$1\n\n", $pee );
 
    // Add a double line break after hr tags, which are self closing.
    $pee = preg_replace( '!(<hr\s*?/?>)!', "$1\n\n", $pee );
 
    // Standardize newline characters to "\n".
    $pee = str_replace( array( "\r\n", "\r" ), "\n", $pee );
 
    // Find newlines in all elements and add placeholders.
    // $pee = wp_replace_in_html_tags( $pee, array( "\n" => ' <!-- wpnl --> ' ) );
 
    // Collapse line breaks before and after <option> elements so they don't get autop'd.
    if ( strpos( $pee, '<option' ) !== false ) {
        $pee = preg_replace( '|\s*<option|', '<option', $pee );
        $pee = preg_replace( '|</option>\s*|', '</option>', $pee );
    }
 
    /*
     * Collapse line breaks inside <object> elements, before <param> and <embed> elements
     * so they don't get autop'd.
     */
    if ( strpos( $pee, '</object>' ) !== false ) {
        $pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
        $pee = preg_replace( '|\s*</object>|', '</object>', $pee );
        $pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
    }
 
    /*
     * Collapse line breaks inside <audio> and <video> elements,
     * before and after <source> and <track> elements.
     */
    if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
        $pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
        $pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
        $pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
    }
 
    // Collapse line breaks before and after <figcaption> elements.
    if ( strpos( $pee, '<figcaption' ) !== false ) {
        $pee = preg_replace( '|\s*(<figcaption[^>]*>)|', '$1', $pee );
        $pee = preg_replace( '|</figcaption>\s*|', '</figcaption>', $pee );
    }
 
    // Remove more than two contiguous line breaks.
    $pee = preg_replace( "/\n\n+/", "\n\n", $pee );
 
    // Split up the contents into an array of strings, separated by double line breaks.
    $pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY );
 
    // Reset $pee prior to rebuilding.
    $pee = '';
 
    // Rebuild the content as a string, wrapping every bit with a <p>.
    foreach ( $pees as $tinkle ) {
        $pee .= '<p>' . trim( $tinkle, "\n" ) . "</p>\n";
    }
 
    // Under certain strange conditions it could create a P of entirely whitespace.
    $pee = preg_replace( '|<p>\s*</p>|', '', $pee );
 
    // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
    $pee = preg_replace( '!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee );
 
    // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
    $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );
 
    // In some cases <li> may get wrapped in <p>, fix them.
    $pee = preg_replace( '|<p>(<li.+?)</p>|', '$1', $pee );
 
    // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
    $pee = preg_replace( '|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee );
    $pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );
 
    // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
    $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)!', '$1', $pee );
 
    // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
    $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );
 
    // Optionally insert line breaks.
    if ( $br ) {
        // Replace newlines that shouldn't be touched with a placeholder.
        $pee = preg_replace_callback( '/<(script|style|svg).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee );
 
        // Normalize <br>
        $pee = str_replace( array( '<br>', '<br/>' ), '<br />', $pee );
 
        // Replace any new line characters that aren't preceded by a <br /> with a <br />.
        $pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee );
 
        // Replace newline placeholders with newlines.
        $pee = str_replace( '<WPPreserveNewline />', "\n", $pee );
    }
 
    // If a <br /> tag is after an opening or closing block tag, remove it.
    $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*<br />!', '$1', $pee );
 
    // If a <br /> tag is before a subset of opening or closing block tags, remove it.
    $pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );
    $pee = preg_replace( "|\n</p>$|", '</p>', $pee );
 
    // Replace placeholder <pre> tags with their original content.
    if ( ! empty( $pre_tags ) ) {
        $pee = str_replace( array_keys( $pre_tags ), array_values( $pre_tags ), $pee );
    }
 
    // Restore newlines in all elements.
    if ( false !== strpos( $pee, '<!-- wpnl -->' ) ) {
        $pee = str_replace( array( ' <!-- wpnl --> ', '<!-- wpnl -->' ), "\n", $pee );
    }
 
    return $pee;
}

/* --------------------------------------------------
 * content.php
 */

// Miscellaneous 'replaces' by pcmt (previously added to bottom of autop)
function bits_and($pieces) {

	// Don't want <br /> or <br/> (prefer <br>)
	$pieces = str_replace(array("<br />", "<br/>"), "<br>", $pieces);

	// Still get <p></p> (remove it and leave empty line)
	$pieces = str_replace('<p></p>', '', $pieces);

	// Preserve guillemets
	$pieces = str_replace(array("»", "&raquo;"), "&#187;", $pieces);
	$pieces = str_replace(array("«", "&laquo;"), "&#171;", $pieces);

	return $pieces;
}

/* --------------------------------------------------
 * content.php
 */

// Creates absolute URL from relative link (28 Aug 20)
function absolute_it($content) {
	$content = preg_replace('!(<a href=")(./|/)([A-Za-z0-9_-]+")!', '$1' . LOCATION . '$3', $content);
	return $content;
}

/* --------------------------------------------------
 * content.php
 */

// Creates full image path to website root
function img_path($content) { // Path to images includes subfolders
	$content = preg_replace('!(<img src=")(img/|/img/|./img/)([A-Za-z0-9_\-\/]+.)(jpg|jpeg|gif|png|svg)"!', '$1' . LOCATION . 'img/$3$4"', $content);
	return $content;
}

/* --------------------------------------------------
 * content.php
 */

// Creates full video path to website root
function video_path($content) { // Path to video includes subfolders
	$content = preg_replace('!(<source src=")(video/|/video/|./video/)([A-Za-z0-9_\-\/]+.)(.mp4)"!', '$1' . LOCATION . 'video/$3$4"', $content);
	return $content;
}

/* --------------------------------------------------
 * content.php
 */

// Adds .php suffix to non .php URLs (12 Sept 20)
// Enabled for WINDOWS in content.php
function suffix_it($content) {
	// Replaces https://example.com/filename with https://example.com/filename.php
	// ([A-Za-z0-9_-]+) is the same as $page_id filter in admin/index.php
	$content = preg_replace('!(' . LOCATION . ')([A-Za-z0-9_-]+)!', '$1$2.php', $content);
	return $content;
}

/* --------------------------------------------------
 * menu.php (removed 05 Dec 20)
 */

function removeEmptyLines($str) {
	return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
}

?>
