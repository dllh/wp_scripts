<?php

/**
 * Takes a blogger export file and splits it into a bunch of files with $entries_per_file
 * entries in each.
 */

// Tune as needed.
ini_set( 'memory_limit', '512m' );

// Tune as needed.
$entries_per_file = 5000;


if ( ! isset( $argv[1] ) || ! file_exists( $argv[1] ) ) {
	die( "\nPlease specify a valid filename as an argument.\n\n" );
}

$input_file = $argv[1];

$entries = explode( '</entry><entry>', file_get_contents( $argv[1] ) );

// Get the first entry and extract the head data from off the beginning so that we can
// prepend this data to the beginning of each new file we create. Then put the entry
// (minus the head data) back on the beginning of the entries array.
$first_entry = array_shift( $entries );
$head = substr( $first_entry, 0, strpos( $first_entry, '<entry>' ) );
$first_entry = str_replace( $head . '<entry>', '', $first_entry );
array_unshift( $entries, $first_entry );

// Get the last entry and remove the closing </feed> tag, then put the
// entry back in the array.
$foot = '</feed>';
$last_entry = array_pop( $entries );
$last_entry = preg_replace( '!' . $foot . '$!', '', $last_entry );
array_push( $entries, $last_entry );

// Break the entries into chunks.
$entry_chunks = array_chunk( $entries, $entries_per_file );

// Make a nice filename prefix based on the original name.
$filename_prefix = array_shift( explode( '.', $input_file, 2 ) );

// Go over each chunk of entries, make a pretty filename, and fill the file
// with the $head, the imploded entries, and the $foot data.
foreach( $entry_chunks as $idx => $entry_chunk ) {
	$padded_idx = $idx +1;
	$padded_idx = str_pad( $padded_idx, 3, 0, STR_PAD_LEFT );
	$filename = $filename_prefix . '-chunk-' . $padded_idx . '.xml';
	$output = $head . '<entry>' . implode( '</entry><entry>', $entry_chunk ) . '</entry>' . $foot;
	file_put_contents( $filename, $output );
}


print "File split into " . count( $entry_chunks ) . " files (" . count( $entries ) . " entries).\n";


