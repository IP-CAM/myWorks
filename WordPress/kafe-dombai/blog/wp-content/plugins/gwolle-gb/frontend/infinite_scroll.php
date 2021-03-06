<?php

/*
 * Handles AJAX request from Gwolle-GB for Infinite Scroll.
 * Will only be used if Infinite Scroll is enabled instead of default pagination.
 *
 * Prints html with a list of entries.
 */


function gwolle_gb_infinite_scroll_callback() {

	$output = '';

	$pageNum = 1;
	if ( isset($_POST['pageNum']) && is_numeric($_POST['pageNum']) ) {
		$pageNum = intval($_POST['pageNum']);
	}

	$book_id = 1;
	if ( isset($_POST['book_id']) && is_numeric($_POST['book_id']) ) {
		$book_id = intval($_POST['book_id']);
	}

	$num_entries = (int) get_option('gwolle_gb-entriesPerPage', 20);

	if ( $pageNum == 1 ) {
		$offset = 0;
	} else {
		$offset = ( $pageNum - 1 ) * $num_entries;
	}


	/* Get the entries for the frontend */
	$entries = gwolle_gb_get_entries(
		array(
			'offset'      => $offset,
			'num_entries' => $num_entries,
			'checked'     => 'checked',
			'trash'       => 'notrash',
			'spam'        => 'nospam',
			'book_id'     => $book_id
		)
	);


	/* Entries from the template */
	if ( !is_array($entries) || empty($entries) ) {
		$output .= 'false';
	} else {

		// Try to load and require_once the template from the themes folders.
		if ( locate_template( array('gwolle_gb-entry.php'), true, true ) == '') {

			$output .= '<!-- Gwolle-GB Entry: Default Template Loaded -->
				';

			// No template found and loaded in the theme folders.
			// Load the template from the plugin folder.
			require_once('gwolle_gb-entry.php');

		} else {

			$output .= '<!-- Gwolle-GB Entry: Custom Template Loaded -->
				';

		}

		$counter = $offset;
		$first = false;
		foreach ($entries as $entry) {
			$counter++;

			// Run the function from the template to get the entry.
			$entry_output = gwolle_gb_entry_template( $entry, $first, $counter );

			// Add a filter for each entry, so devs can add or remove parts.
			$output .= apply_filters( 'gwolle_gb_entry_read', $entry_output, $entry );

		}

	}

	echo $output;

	die(); // this is required to return a proper result

}
add_action( 'wp_ajax_gwolle_gb_infinite_scroll', 'gwolle_gb_infinite_scroll_callback' );
add_action( 'wp_ajax_nopriv_gwolle_gb_infinite_scroll', 'gwolle_gb_infinite_scroll_callback' );
