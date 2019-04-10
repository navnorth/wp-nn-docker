<?php

namespace wpsolr\pro\extensions\wp_all_import;

use wpsolr\core\classes\extensions\WpSolrExtensions;

/**
 * Class WPSOLR_Plugin_WPAllImport
 *
 */
class WPSOLR_Plugin_WPAllImport extends WpSolrExtensions {

	/**
	 * Constructor
	 * Subscribe to actions/filters
	 **/
	function __construct() {

		// Catch actions sent by WP All Import
		add_action( 'pmxi_delete_post', [ $this, 'delete_post_ids' ], 10, 1 );
	}

	/**
	 *
	 * Delete posts from the index with WP All Import.
	 *
	 * @param string[] $ids
	 *
	 */
	function delete_post_ids( $ids = [] ) {

		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		foreach ( $ids as $pid ) {
			$post = get_post( $pid );
			if ( $post ) {
				// Ensure a delete by setting the status
				$post->post_status = 'trash';
				add_remove_document_to_solr_index( $pid, $post );
			}
		}
	}

}
