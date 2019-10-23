<?php

namespace wpsolr\pro\extensions\seo;

use wpsolr\core\classes\WPSOLR_UnitTestCase_Ajax;

/**
 * Common Ajax tests for class children.
 *
 * @group ajax
 *
 * Class OptionSeoTestCase
 * @property WPSOLR_Option_Seo child
 */
abstract class WPSOLR_OptionSeo_Test_Ajax_Abstract extends WPSOLR_UnitTestCase_Ajax {

	function test_ajax_drop_permalinks_table_existing() {

		// Create the table first
		$this->child->check_db_version( 'any' );
		$this->assertTrue( $this->wpsolr_check_table_exists() );

		$_POST['security'] = wp_create_nonce( WPSOLR_NONCE_FOR_DASHBOARD );

		try {
			$this->_handleAjax( 'wpsolr_ajax_drop_permalinks_table' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'OK', $response->status->state );

		// Table has been dropped
		$this->assertFalse( $this->wpsolr_check_table_exists() );
	}

	function test_ajax_drop_permalinks_table_sql_error() {

		// Create the table first
		$this->child->check_db_version( 'any' );
		$this->assertTrue( $this->wpsolr_check_table_exists() );

		$_POST['security'] = wp_create_nonce( WPSOLR_NONCE_FOR_DASHBOARD );

		// Provoke a SQL error on the drop table SQL execution
		$this->wpsolr_trigger_sql_error( $this->child->get_sql_statement_drop_table() );

		try {
			$this->_handleAjax( 'wpsolr_ajax_drop_permalinks_table' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'KO', $response->status->state );

		// Table has not been dropped
		$this->assertTrue( $this->wpsolr_check_table_exists() );
	}

	function test_ajax_drop_permalinks_table_not_existing() {

		$this->assertFalse( $this->wpsolr_check_table_exists() );

		$_POST['security'] = wp_create_nonce( WPSOLR_NONCE_FOR_DASHBOARD );

		try {
			$this->_handleAjax( 'wpsolr_ajax_drop_permalinks_table' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'OK', $response->status->state );

		// Table has been dropped
		$this->assertFalse( $this->wpsolr_check_table_exists() );
	}

	function test_ajax_drop_permalinks_table_no_security() {

		// Create the table first
		$this->child->check_db_version( 'any' );
		$this->assertTrue( $this->wpsolr_check_table_exists() );

		foreach ( [ '', 'wrong_nonce' ] as $wrong_nonce ) {

			//
			$this->_last_response = '';

			if ( ! empty( $wrong_nonce ) ) {
				$_POST['security'] = $wrong_nonce;
			}

			try {
				$this->_handleAjax( 'wpsolr_ajax_drop_permalinks_table' );
			} catch ( \WPAjaxDieContinueException $e ) {
				// We expected this, do nothing.
			}

			$response = json_decode( $this->_last_response );
			$this->assertEquals( 'KO', $response->status->state );

			// Table has not been dropped
			$this->assertTrue( $this->wpsolr_check_table_exists() );
		}
	}

}
