<?php

/**
 * Posterous has a handy dandy backup utility that outputs a file in WordPress's WXR format. Ish.
 * There are some things the importer doesn't like about the file, though. This script fixes the
 * worst of them.
 * 
 * To use the script, set $wxr_file to the path of the WXR file you wish to correct. Then just
 * run the script at the command line.
 * 
 * It'll spit out the corrected WXR file, so you probably want to redirect the output to a file
 * that you can then import using WordPress's WXR importer.
 */

// Change this to the path of the XML file Posterous provides in their backup.
$wxr_file = '';

/* No need to edit below this line. */

if ( 'cli' != php_sapi_name() )
	die( "Don't put me on your server, please.\n" );

if ( ! file_exists( $wxr_file ) )
	die( "File $wxr_file doesn't exist.\n" );

$lines = file_get_contents( $wxr_file );

// Fix up bad date formats (including some nasty template code that makes it into the imports in place of actual dates).
$lines = preg_replace_callback( '!<wp:post_date>(([a-zA-Z]{3}) ([a-zA-Z]{3}) (\d\d) (\d\d:\d\d:\d\d) ([+-]?\d{4}) (\d{4}))</wp:post_date>.+<wp:post_date_gmt>.*</wp:post_date_gmt>!U', 'do_replace', $lines );

// The regex parser doesn't like the fact that the export content is all on one line. Let's try to split it up a little.
// The export at present has everything on one line. Converting tabs to newlines may be too much, but it seemed to help in my testing.
$lines = str_replace( "\t", "\n", $lines );

// Explicitly add a newline between items.
$lines = str_replace( "</item><item>", "</item>\n<item>", $lines );

// None of the other patterns caught this, so do a hard-coded rule to get it on its own line.
$lines = str_replace( "<guid ", "\n<guid ", $lines );

// Callback function for preg_repace_callback that spits out correct post_date and post_date_gmt XML.
function do_replace( $matches ) {
	global $last_date;
	$months = array(
		'Jan' => '01',
		'Feb' => '02',
		'Mar' => '03',
		'Apr' => '04',
		'May' => '05',
		'Jun' => '06',
		'Jul' => '07',
		'Aug' => '08',
		'Sep' => '09',
		'Oct' => '10',
		'Nov' => '11',
		'Dec' => '12',
	);
	$last_date = strtotime( $matches[1] );

	return '<wp:post_date>' . date( 'Y-m-d H:i:s', $last_date ) . '</wp:post_date>' . "\n" . '<wp:post_date_gmt>' . gmdate( 'Y-m-d H:i:s', $last_date ) . '</wp:post_date_gmt>';
}

print $lines;
