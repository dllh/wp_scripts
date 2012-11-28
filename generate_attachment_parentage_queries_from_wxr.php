<?php

/**
 * This script reads in a WXR file and prints out a bunch of queries that'll update associations 
 * between attachments and their parent posts based on ids given in the WXR.
 *
 * It's useful in cases in which a WordPress import failed to complete successfully and do
 * the image backfill that creates the parenthood associations and in cases in which an
 * import was split into batches so that all ids needed for backfilling weren't available
 * for each of the imports.
 *
 * Note that it'll only work if there are no id changes between the export and the import.
 * The importer tries to use the same ids, but of course there are circumstances in which
 * this isn's possible (e.g. the id already exists when you do the import). So make sure
 * you've backed up your database before running the queries this script spits out.
 * 
 * If you specify a list of parent post ids in $affected_posts, the script will spit out
 * queries pertaining only to those posts. This doesn't increase efficiency of the script,
 * as it still has to parse the whole file, but it will limit output noise. So if you 
 * have a list of posts whose galleries are messed up, you can specify a list of ids in 
 * the array and reduce the number of queries you run (which reduces risk somewhat).
 */

// Specify an array of parent post ids to print queries for. If empty, we'll print them all.
$affected_posts = array ();

$export_file = isset( $argv[1] ) && file_exists( $argv[1] ) ? $argv[1] : '';

if ( '' == $export_file )
	die( "\nPlease specify a WXR file as an argument.\n" );

$attachments = array();

$lines = file_get_contents( $export_file );
$items = explode( '<item>', $lines );

$check_post_whitelist = count( $affected_posts ) > 0 ? true : false;

foreach ( $items as $item ) {
	$post_id = $post_parent = 0;
	$post_date = $post_name = $guid = '';

	// We need to consider only attachments, so skip non-attachment posts.
	if ( ! preg_match( '!<wp:post_type>attachment</wp:post_type>!Ums', $item, $matches ) )
		continue;

	// Get the post_id	
	preg_match( '!<wp:post_id>(\d+)</wp:post_id>!Ums', $item, $matches );
	if ( $matches[1] ) 
		$post_id = $matches[1];

	// Get the post_parent id
	preg_match( '!<wp:post_parent>(\d+)</wp:post_parent>!Ums', $item, $matches );
	if ( $matches[1] ) 
		$post_parent = $matches[1];

	if ( $post_id > 0 ) {
		$query = sprintf( "UPDATE wp_posts SET post_parent = %d WHERE post_id = %d;\n", $post_parent, $post_id );

		if ( $check_post_whitelist ) {
			if ( in_array( $post_parent, $affected_posts ) )
				print $query;
		} else {
			print $query;
		}
	}
}
