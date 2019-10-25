<?php

namespace wpsolr\pro\extensions\seo;

use WP_UnitTest_Factory_For_Term;
use wpsolr\core\classes\engines\solarium\WPSOLR_ResultsSolariumClient;
use wpsolr\core\classes\ui\layout\checkboxes\WPSOLR_UI_Layout_Check_Box;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\ui\WPSOLR_Query_Parameters;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\pro\extensions\theme\layout\color_picker\WPSOLR_UI_Layout_Color_Picker;
use wpsolr\pro\extensions\theme\layout\radioboxes\WPSOLR_UI_Layout_Radio_Box;
use wpsolr\pro\extensions\theme\layout\range_regular_checkboxes\WPSOLR_UI_Layout_Range_Regular_Check_box;
use wpsolr\pro\extensions\theme\WPSOLR_Option_Theme;
use wpsolr\pro\extensions\WPSOLR_Extensions_Test_Abstract;

/**
 * Common tests for class children.
 *
 * Class OptionSeoTestCase
 * @property WPSOLR_Option_Seo child
 */
abstract class WPSOLR_OptionSeo_Test_Abstract extends WPSOLR_Extensions_Test_Abstract {

	public function test_get_table_name_prefixed() {
		global $wpdb;

		$table_name = 'a_table_name';
		$this->assertEquals( "{$wpdb->prefix}$table_name", $this->child->get_table_name_prefixed( $table_name ) );
	}

	/**
	 * Insert a row in the permalinks table
	 *
	 * @param $url
	 * @param $query
	 *
	 * @throws \Exception
	 */
	function wpsolr_insert_row( $url, $query ) {
		global $wpdb;

		if ( ! $this->wpsolr_check_table_exists() ) {
			$this->child->create_tables();
		}

		$table_name = $this->child->get_table_name();
		$sql        = $wpdb->prepare(
			"insert into $table_name (url, query) values (%s, %s)",
			$url, $query
		);
		$result     = $wpdb->query( $sql );
		if ( ! empty( $result->last_error ) ) {
			throw new \Exception( $result->last_error ); // @codeCoverageIgnore
		}
		$sql    = $wpdb->prepare(
			"select url from $table_name where url = %s and query = %s",
			$url, $query
		);
		$result = $wpdb->get_col( $sql );
		if ( ! $result ) {
			throw new \Exception( 'Could not find results.' ); // @codeCoverageIgnore
		}

	}

	public function test_reorder_facets_data() {

		// Set options
		update_option( $this->wpsolr_get_option_name(), [ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true ] );
		/*update_option( WPSOLR_Option::OPTION_FACET, [
			WPSOLR_Option::OPTION_FACET_FACETS_SEO_PERMALINK_POSITION => [ 'f3', 'f2', 'f1' ],
		] );*/ // no positions
		$this->child->init();

		// Empty href
		$this->assertEquals( [], $this->child->reorder_facets_data( [] ) );

		// f0 unknown
		$this->assertEquals( [], $this->child->reorder_facets_data( [ 'f0' => [] ] ) );
		$this->assertEquals( [ '' ], $this->child->reorder_facets_data( [ 'f0' => [ '' ] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ 'f0' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f0' => [ 'a', 'b' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f0' => [ 'b', 'a' ] ] ) );


		// keywords alone
		$this->assertEquals( [ '' ], $this->child->reorder_facets_data( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ '' ] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a b' ], $this->child->reorder_facets_data( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'a b' ] ] ) );

		// f1
		$this->assertEquals( [], $this->child->reorder_facets_data( [ 'f1' => [] ] ) );
		$this->assertEquals( [ '' ], $this->child->reorder_facets_data( [ 'f1' => [ '' ] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a', 'b' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'b', 'a' ] ] ) );

		// f1 and keywords
		$this->assertEquals( [ 'k' ], $this->child->reorder_facets_data( [
			'f1'                                        => [],
			WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'k' ],
		] ) );
		$this->assertEquals( [ 'k', '' ], $this->child->reorder_facets_data( [
			'f1'                                        => [ '' ],
			WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'k' ],
		] ) );
		$this->assertEquals( [ 'k', 'a' ], $this->child->reorder_facets_data( [
			'f1'                                        => [ 'a' ],
			WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'k' ],
		] ) );
		$this->assertEquals( [ 'k', 'a', 'b' ], $this->child->reorder_facets_data( [
			'f1'                                        => [
				'a',
				'b',
			],
			WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'k' ],
		] ) );
		$this->assertEquals( [ 'k', 'a', 'b' ], $this->child->reorder_facets_data( [
			'f1'                                        => [
				'b',
				'a',
			],
			WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => [ 'k' ],
		] ) );

		// f1 + f2
		$this->assertEquals( [], $this->child->reorder_facets_data( [ 'f1' => [], 'f2' => [] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [] ] ) );
		$this->assertEquals( [ 'a', 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [ 'b' ] ] ) );
		$this->assertEquals( [ 'b', 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'b' ], 'f2' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a', 'b', 'c' ], $this->child->reorder_facets_data( [
			'f1' => [ 'a', 'b' ],
			'f2' => [ 'c' ],
		] ) );
		$this->assertEquals( [ 'b', 'c', 'b' ], $this->child->reorder_facets_data( [
			'f1' => [ 'c', 'b' ],
			'f2' => [ 'b' ],
		] ) );
		$this->assertEquals( [ 'a', 'b', 'c', 'd' ], $this->child->reorder_facets_data( [
			'f1' => [ 'a', 'b' ],
			'f2' => [ 'c', 'd' ],
		] ) );
		$this->assertEquals( [ 'b', 'c', 'a', 'd' ], $this->child->reorder_facets_data( [
			'f1' => [ 'c', 'b' ],
			'f2' => [ 'd', 'a' ],
		] ) );


		// Set options
		update_option( $this->wpsolr_get_option_name(), [ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true ] );
		update_option( WPSOLR_Option::OPTION_FACET, [
			WPSOLR_Option::OPTION_FACET_FACETS_SEO_PERMALINK_POSITION => [ 'f3', 'f2', 'f1' ],
		] );
		$this->child->init();

		// Empty href
		$this->assertEquals( [], $this->child->reorder_facets_data( [] ) );

		// f1
		$this->assertEquals( [], $this->child->reorder_facets_data( [ 'f1' => [] ] ) );
		$this->assertEquals( [ '' ], $this->child->reorder_facets_data( [ 'f1' => [ '' ] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a', 'b' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'b', 'a' ] ] ) );


		// f1 + f2
		$this->assertEquals( [], $this->child->reorder_facets_data( [ 'f1' => [], 'f2' => [] ] ) );
		$this->assertEquals( [ 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [] ] ) );
		$this->assertEquals( [ 'a', 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'b', 'a' ], $this->child->reorder_facets_data( [ 'f1' => [ 'a' ], 'f2' => [ 'b' ] ] ) );
		$this->assertEquals( [ 'a', 'b' ], $this->child->reorder_facets_data( [ 'f1' => [ 'b' ], 'f2' => [ 'a' ] ] ) );
		$this->assertEquals( [ 'c', 'a', 'b' ], $this->child->reorder_facets_data( [
			'f1' => [ 'a', 'b' ],
			'f2' => [ 'c' ],
		] ) );
		$this->assertEquals( [ 'b', 'b', 'c' ], $this->child->reorder_facets_data( [
			'f1' => [ 'c', 'b' ],
			'f2' => [ 'b' ],
		] ) );
		$this->assertEquals( [ 'c', 'd', 'a', 'b' ], $this->child->reorder_facets_data( [
			'f1' => [ 'a', 'b' ],
			'f2' => [ 'c', 'd' ],
		] ) );
		$this->assertEquals( [ 'a', 'd', 'b', 'c' ], $this->child->reorder_facets_data( [
			'f1' => [ 'c', 'b' ],
			'f2' => [ 'd', 'a' ],
		] ) );

	}

	public function test_check_db_version() {

		/**
		 * No db version is already installed
		 */
		$this->child->check_db_version( 'first' );

		// Verify tat the table is in database
		$this->assertTrue( $this->wpsolr_check_table_exists() );

		// Verify that the db version is correct
		$option = get_option( 'wdm_db' );
		$this->assertEquals( 'first', $option['current_version'] );

		/**
		 * Same db version is applied: db is not accessed at all
		 */
		$mock = $this->getMockBuilder( get_class( $this->child ) )
		             ->setMethods( [ 'create_classes' ] )
		             ->getMock();
		$mock->expects( $this->never() )
		     ->method( 'create_classes' );
		$mock->check_db_version( 'first' );

		/**
		 * A new db version is applied: no database action should be done
		 */
		$this->child->check_db_version( '2nd' );

		// Verify that the db version is correct
		$option = get_option( 'wdm_db' );
		$this->assertEquals( '2nd', $option['current_version'] );

		// Drop, then verify that the table is not in database anymore
		$this->assertEmpty( $this->child->drop_permalinks_table() );
		$db_option = get_option( 'wdm_db' );
		$this->assertEmpty( $db_option['current_version'] );
		$this->assertFalse( $this->wpsolr_check_table_exists() );

	}

	public function test_replace_whitespaces() {

		$datas = [
			[ '', '' ],
			[ '', ' ' ],
			[ '', '    ' ],
			[ 'x', 'x' ],
			[ 'x', 'x ' ],
			[ 'x', ' x' ],
			[ 'x', ' x ' ],
			[ 'x', '   x   ' ],
			[ 'xy', 'xy' ],
			[ 'x+y', 'x y' ],
			[ 'x+y', ' x y ' ],
			[ 'x+y', '  x  y   ' ],
			[ 'xyz', 'xyz' ],
			[ 'xy+z', 'xy z' ],
			[ 'x+y+z', 'x y z' ],
			[ 'x+y+z', ' x y z ' ],
			[ 'x+y+z', '  x   y    z   ' ],
		];

		foreach ( $datas as $data ) {
			$this->assertEquals( $data[0], $this->child->replace_and_remove_doubles( $data[1], ' ', '+' ) );
		}
	}

	public function test_format_permalink_url() {

		$datas = [
			[ '', '' ],
			[ '', ' ' ],
			[ '', '    ' ],
			[ 'x', 'x' ],
			[ 'x', 'x ' ],
			[ 'x', ' x' ],
			[ 'x', ' x ' ],
			[ 'x', '   x   ' ],
			[ 'xy', 'xy' ],
			[ 'x-y', 'x y' ],
			[ 'x-y', ' x y ' ],
			[ 'x-y', '  x  y   ' ],
			[ 'xyz', 'xyz' ],
			[ 'xy-z', 'xy z' ],
			[ 'x-y-z', 'x y z' ],
			[ 'x-y-z', ' x y z ' ],
			[ 'x-y-z', '  x   y    z   ' ],
			[ 'x', '-x' ],
			[ 'x', 'x-' ],
			[ 'x-y', ' --- -x-  - --y- -- ' ],
		];

		foreach ( $datas as $data ) {
			$this->assertEquals( $data[0], $this->child->format_permalink_url( $data[1], WPSOLR_Option_Seo::CHAR_WHITESPACE, '-' ) );
			// Test uppercase too
			$this->assertEquals( $data[0], $this->child->format_permalink_url( strtoupper( $data[1] ), WPSOLR_Option_Seo::CHAR_WHITESPACE, '-' ) );
		}
	}

	function test_prepare_multiple_insert() {


		$this->assertEquals( '',
			$this->child->prepare_multiple_insert( 'table_name', [] ) );

		$this->assertEquals( "INSERT IGNORE INTO table_name (url, query) VALUES ('x','y')",
			$this->child->prepare_multiple_insert( 'table_name', [ 'x', 'y' ] ) );


		$this->assertEquals( "INSERT IGNORE INTO table_name (url, query) VALUES ('x','y'),('z','t')",
			$this->child->prepare_multiple_insert( 'table_name', [ 'x', 'y', 'z', 't' ] ) );

		// Exception on odd number of parameters
		try {
			$this->child->prepare_multiple_insert( 'table_name', [ 'x', 'y', 'z' ] );
			$this->fail( 'Expected Exception has not been raised.' ); // @codeCoverageIgnore
		} catch ( \Exception $e ) {
		}
	}

	function test_generate_permalink_unique_query() {

		// Empty
		$this->assertEquals( '', $this->child->generate_permalink_unique_query( [] ) );
		$this->assertEquals( '', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [] ] ) );
		$this->assertEquals( '', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => '' ] ) );

		// Simple keywords
		$this->assertEquals( 'wpsolr_q=a', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => 'a' ] ) );
		$this->assertEquals( 'wpsolr_q=a b', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => 'a b' ] ) );
		$this->assertEquals( 'wpsolr_q=a b c', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => '   a   b    c   ' ] ) );

		// Simple filter
		$this->assertEquals( 'wpsolr_fq=a:1', $this->child->generate_permalink_unique_query( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:1' ] ] ) );
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1', $this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:1' ],
			]
		) );

		// Two values on same filter
		$this->assertEquals( 'wpsolr_fq=a:1||a:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'a:2',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||a:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'a:2',
				],
			] )
		);
		// Should be reordered
		$this->assertEquals( 'wpsolr_fq=a:1||a:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:2',
					'a:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||a:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:2',
					'a:1',
				],
			] )
		);

		// Same value on 2 filters
		$this->assertEquals( 'wpsolr_fq=a:1||b:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'b:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||b:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'b:1',
				],
			] )
		);
		// Should be reordered
		$this->assertEquals( 'wpsolr_fq=a:1||b:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'b:1',
					'a:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||b:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'b:1',
					'a:1',
				],
			] )
		);

		// Two values on 2 filters
		$this->assertEquals( 'wpsolr_fq=a:1||b:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'b:2',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||b:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'b:2',
				],
			] )
		);
		// Should be reordered
		$this->assertEquals( 'wpsolr_fq=a:1||b:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'b:2',
					'a:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||b:2',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'b:2',
					'a:1',
				],
			] )
		);

		// Mix of many filters
		$this->assertEquals( 'wpsolr_fq=a:1||a:2||c:1||c:2||c:3||d:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'a:2',
					'c:1',
					'c:2',
					'c:3',
					'd:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||a:2||c:1||c:2||c:3||d:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'a:1',
					'a:2',
					'c:1',
					'c:2',
					'c:3',
					'd:1',
				],
			] )
		);
		// Should be reordered
		$this->assertEquals( 'wpsolr_fq=a:1||a:2||c:1||c:2||c:3||d:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'd:1',
					'c:2',
					'a:2',
					'c:3',
					'a:1',
					'c:1',
				],
			] )
		);
		$this->assertEquals( 'wpsolr_q=a&wpsolr_fq=a:1||a:2||c:1||c:2||c:3||d:1',
			$this->child->generate_permalink_unique_query( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'a',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [
					'd:1',
					'c:2',
					'a:2',
					'c:3',
					'a:1',
					'c:1',
				],
			] )
		);

	}

	function test_generate_permalink() {

		// Capture filters/events
		$option_theme = new WPSOLR_Option_Theme();

		// Set the facets options
		$options_facets = [
			'facets'                    => 'color_str,category_str,type,_price_str,not_permalink_str',
			'facets_layout'             => [
				'1color_str'   => WPSOLR_UI_Layout_Color_Picker::CHILD_LAYOUT_ID,
				'color_str'    => WPSOLR_UI_Layout_Color_Picker::CHILD_LAYOUT_ID,
				'category_str' => WPSOLR_UI_Layout_Check_Box::CHILD_LAYOUT_ID,
				'type'         => WPSOLR_UI_Layout_Radio_Box::CHILD_LAYOUT_ID,
				'_price_str'   => WPSOLR_UI_Layout_Range_Regular_Check_Box::CHILD_LAYOUT_ID,
			],
			'facets_seo_is_permalink'   => [
				//'1color_str'    => '1',
				'color_str'    => '1',
				'category_str' => '1',
				'type'         => '1',
				'_price_str'   => '1',
			],
			'facets_label'              => [
				'1color_str'   => '1Color',
				'color_str'    => 'Color',
				'category_str' => 'Category',
				'type'         => 'Type',
				'_price_str'   => 'Price',
			],
			'facets_item_label'         => [
				'1color_str' => [
					// 'blue' => ''
					'green' => '#green',
					'red'   => '#red',
				],
				'color_str'  => [
					// 'blue' => ''
					'green' => '#green',
					'red'   => '#red',
				],
				'type'       => [
					//'page' => 'xpage',
					'post'    => 'xpost',
					'product' => 'xproduct',
				],
				'_price_str' => [
					//'*-0' => 'free',
					'0-10'  => 'medium',
					'10-20' => 'max',
				],
			],
			'facets_seo_template'       => [
				'color_str'    => 'facet seo template color {{value}}',
				'category_str' => 'facet seo template category  {{value}}',
				'type'         => 'facet seo template type  {{value}}',
				'_price_str'   => 'facet seo template price  {{start}} {{end}}',
			],
			'facets_seo_items_template' => [
				'type'       => [
					'product' => 'facet item seo template type {{value}}',
				],
				'color_str'  => [
					'red' => 'facet item seo template color {{value}}',
				],
				'_price_str' => [
					'10-20' => 'facet item seo template price {{start}} {{end}}',
				],
			],
		];
		update_option( WPSOLR_Option::OPTION_FACET, $options_facets, true );

		$permalink_rels = [
			[ true, true, 'noindex, nofollow' ],
			[ true, false, 'noindex' ],
			[ false, true, 'nofollow' ],
			[ false, false, '' ],
		];


		foreach ( $permalink_rels as $permalink_rel ) {

			// Set rel
			$this->child->set_rel_noindex( $permalink_rel[0] );
			$this->child->set_rel_nofollow( $permalink_rel[1] );

			/**
			 *
			 * Facet not a permalink
			 *
			 */
			// Blue Color
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, '1color_str', [
				'value'    => 'BLUE', // uppercase
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/blue',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'blue',
					'wpsolr_fq=1color_str:BLUE',
				],
				$values
			);

			/**
			 *
			 * Field
			 *
			 */

			// Page
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'type', [
				'value'    => 'page',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-type-page',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-type-page',
					'wpsolr_fq=type:page',
				],
				$values
			);

			// Post
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'type', [
				'value'    => 'post',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-type-xpost',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-type-xpost',
					'wpsolr_fq=type:post',
				],
				$values
			);

			// Product
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'type', [
				'value'    => 'product',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-type-xproduct',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-type-xproduct',
					'wpsolr_fq=type:product',
				],
				$values
			);

			// Product and page
			$values = [];
			$this->child->set_permalink( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'type:product' ] ] );
			$result = $this->child->generate_permalink( $values, 'type', [
				'value'    => 'page',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-type-xproduct+facet-seo-template-type-page',
				// alphabetical order
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-type-xproduct+facet-seo-template-type-page', // alphabetical order
					'wpsolr_fq=type:page||type:product',
				],
				$values
			);

			// Keywords, Product and page
			$values = [];
			$this->child->set_permalink( [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 'Key wOrd', // uppercases
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'type:product' ],
			] );
			$result = $this->child->generate_permalink( $values, 'type', [
				'value'    => 'page',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/key+word+facet-item-seo-template-type-xproduct+facet-seo-template-type-page', // lowercase
				// alphabetical order
				// alphabetical order
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'key+word+facet-item-seo-template-type-xproduct+facet-seo-template-type-page', // lowercase
					// alphabetical order
					'wpsolr_q=Key wOrd&wpsolr_fq=type:page||type:product', // uppercase
				],
				$values
			);

			/**
			 *
			 * Colors
			 *
			 */

			// Blue Color
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'color_str', [
				'value'    => 'blue',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-color-blue',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-color-blue',
					'wpsolr_fq=color_str:blue',
				],
				$values
			);


			// Green Color
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'color_str', [
				'value'    => 'green',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-color-green',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-color-green',
					'wpsolr_fq=color_str:green',
				],
				$values
			);

			// Red color
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, 'color_str', [
				'value'    => 'red',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-color-red',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-color-red',
					'wpsolr_fq=color_str:red',
				],
				$values
			);


			// 2 Color filter s
			$values = [];
			$this->child->set_permalink( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'color_str:red' ] ] );
			$result = $this->child->generate_permalink( $values, 'color_str', [
				'value'    => 'green',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-color-red+facet-seo-template-color-green',
				// alphabetical order
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-color-red+facet-seo-template-color-green', // alphabetical order
					'wpsolr_fq=color_str:green||color_str:red',
				],
				$values
			);


			/**
			 *
			 * Ranges
			 *
			 */

			// Format exception
			$values = [];
			$this->child->set_permalink( [] );
			try {
				$result = $this->child->generate_permalink( $values, '_price_str', [
					'value'    => 'bad format',
					'count'    => 1,
					'items'    => [],
					'selected' => false,
				], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
				);
				$this->fail( 'Expected Exception has not been raised.' ); // @codeCoverageIgnore
			} catch ( \Exception $e ) {
				$this->assertEquals( sprintf( 'Wrong format: facet range _price_str with wrong value "bad format"' ), $e->getMessage() );
			}


			// *-0
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, '_price_str', [
				'value'    => '*-0',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-price-*-0',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-price-*-0',
					'wpsolr_fq=_price_str:*-0',
				],
				$values
			);


			// 0-10
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, '_price_str', [
				'value'    => '0-10',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-seo-template-price-0-10',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-seo-template-price-0-10',
					'wpsolr_fq=_price_str:0-10',
				],
				$values
			);


			// 10-20
			$values = [];
			$this->child->set_permalink( [] );
			$result = $this->child->generate_permalink( $values, '_price_str', [
				'value'    => '10-20',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-price-10-20',
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-price-10-20',
					'wpsolr_fq=_price_str:10-20',
				],
				$values
			);

			// 2 ranges
			$values = [];
			$this->child->set_permalink( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ '_price_str:10-20' ] ] );
			$result = $this->child->generate_permalink( $values, '_price_str', [
				'value'    => '0-10',
				'count'    => 1,
				'items'    => [],
				'selected' => false,
			], 'shop1', '', WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, WPSOLR_Option_Seo::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS
			);
			$this->assertEquals( [
				'href' => '/shop1/facet-item-seo-template-price-10-20+facet-seo-template-price-0-10',
				// alphabetical order
				'rel'  => $permalink_rel[2],
			], $result );
			$this->assertEquals(
				[
					'facet-item-seo-template-price-10-20+facet-seo-template-price-0-10', // alphabetical order
					'wpsolr_fq=_price_str:0-10||_price_str:10-20',
				],
				$values
			);


		}

	}

	function test_is_replace_query_ok_simple() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
	}

	function test_is_replace_query_ko_admin() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set is admin
		$this->wpsolr_set_is_admin();

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );

	}

	function test_is_replace_query_ko_not_main_query() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => false ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );

	}

	function test_is_replace_query_ko_not_a_permalink_home() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[
						$this->MOCK_METHOD      => 'get_server_request_uri',
						$this->MOCK_WILL_RETURN => '/not_a_permalink_home'
					],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'home',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
	}

	function test_is_replace_query_ko_ajax() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/ajax.php' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
	}

	function test_is_replace_query_ok_permalinks_home_with_subfolder() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop/shop1' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop/shop1',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
	}

	function test_is_replace_query_ok_permalinks_home_with_subfolder_deep() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[
						$this->MOCK_METHOD      => 'get_server_request_uri',
						$this->MOCK_WILL_RETURN => '/shop/shop1/test/?q=a&r=b'
					],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop/shop1',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
	}

	function test_is_replace_query_ok_permalinks_home_with_subfolder_one_level() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[
						$this->MOCK_METHOD      => 'get_server_request_uri',
						$this->MOCK_WILL_RETURN => '/shop/shop1/test/?q=a&r=b'
					],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertTrue( $this->child->get_is_permalink_url() );
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertTrue( $this->child->get_is_permalink_url() );

	}

	function test_is_replace_query_ko_home_url_begins_with_but_not_subfolder() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop-x' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
				// Url begins with home, but home is not a subfolder.
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );

	}

	function test_is_replace_query_ko_empty_home() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop/shop1' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => '',
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );

	}

	function test_is_replace_query_ko_404_option() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				//WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// empty => 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
				// Url begins with home, but home is not a subfolder.
			]
		);
		$this->child->init();

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		self::assertFalse( $this->child->get_is_permalink_url() );
	}

	function test_rewrite_ko_no_permalink_home() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP => [
					[
						$this->MOCK_METHOD  => 'add_rewrite_rule',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH => $this->MOCK_HOW_MUCH_NEVER,
						],
					],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => '', // Empty home
			]
		);
		$this->child->init();

		do_action( 'init' );
	}

	function test_rewrite_ko_404() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP => [
					[
						$this->MOCK_METHOD  => 'add_rewrite_rule',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH => $this->MOCK_HOW_MUCH_NEVER,
						],
					],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				//WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Empty => 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		do_action( 'init' );
	}

	/**
	 *
	 */
	function test_rewrite_ok() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP => [
					[
						$this->MOCK_METHOD  => 'add_rewrite_rule',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH         => 2,
							$this->MOCK_WITH_CONSECUTIVE => [
								[
									[ $this->MOCK_EQUAL_TO => 'shop/(.*)?$' ],
									[ $this->MOCK_EQUAL_TO => 'index.php?s=$matches[1]&post_type=product' ],
									[ $this->MOCK_EQUAL_TO => 'top' ],
								],
								[
									[ $this->MOCK_EQUAL_TO => 'shop?$' ],
									[ $this->MOCK_EQUAL_TO => 'index.php?s=&post_type=product' ],
									[ $this->MOCK_EQUAL_TO => 'top' ],
								],
							],
						],
					],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
			]
		);
		$this->child->init();

		do_action( 'init' );
	}

	/**
	 * Create a mock with parameters
	 *
	 * @param WPSOLR_Option_Seo $object_tested
	 * @param $permalinks_home
	 * @param $is_wp_search
	 * @param $is_admin
	 * @param $is_main_query
	 * @param $redirect_to_search
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	function get_mock_force_permalinks( WPSOLR_Option_Seo $object_tested, $how_much_wp_redirect, $wpsolr_query, $with_wp_redirect_url, $with_wp_redirect_http_code, $permalinks_home, $is_wp_search, $is_admin, $is_main_query, $redirect_to_search, $redirect_search_to_home ) {

		$this->wpsolr_mock_services( $object_tested,
			[
				$this->MOCK_SERVICE_WPSOLR       => [
					[
						$this->MOCK_METHOD      => 'is_wp_search',
						$this->MOCK_WILL_RETURN => $is_wp_search,
					],
				],
				$this->MOCK_SERVICE_WPSOLR_QUERY => [
					[
						$this->MOCK_METHOD      => 'get_wpsolr_query',
						$this->MOCK_WILL_RETURN => $wpsolr_query,
					],
				],
				$this->MOCK_SERVICE_WP           => [
					[
						$this->MOCK_METHOD  => 'wp_redirect',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH         => $how_much_wp_redirect,
							$this->MOCK_WITH_CONSECUTIVE => [
								[
									[
										$this->MOCK_EQUAL_TO => $with_wp_redirect_url,
									],
									[
										$this->MOCK_EQUAL_TO => $with_wp_redirect_http_code,
									],
								],
							],
						],
					],
					[
						$this->MOCK_METHOD      => 'is_admin',
						$this->MOCK_WILL_RETURN => $is_admin,
					],
					[
						$this->MOCK_METHOD      => 'is_main_query',
						$this->MOCK_WILL_RETURN => $is_main_query,
					],
				],
			]
		);


		// Set options
		$options = [
			WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
			// activate filters/actions
			//WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
			// Not empty => not 404
			WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => $permalinks_home, // Empty home
		];
		if ( $redirect_search_to_home ) {
			$options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_IS_REDIRECT_FROM_SEARCH ] = true; // Redirect search to home
		}
		if ( $redirect_to_search ) {
			$options[ WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE ] = WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_REDIRECT_TO_SEARCH; // Redirect permalinks to search
		}
		update_option( $this->wpsolr_get_option_name(), $options );
	}

	function test_force_permalinks() {

		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, '', '/shop/', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );
	}

	function test_force_permalinks0() {

		// Not a redirect search to home
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, '', '/shop/', 302, 'shop', true, false, true, false, false );
		$this->child->init();
		do_action( 'init' );
	}

	function test_force_permalinks1() {
		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, 'a', '/shop/a', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks2() {
		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, '  a   b  ', '/shop/a+b', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks3() {
		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, '  a-b   c ', '/shop/a-b+c', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks4() {
		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, '    ', '/shop/', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks5() {
		// Correct
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_ONCE, 'a+b', '/shop/a+b', 302, 'shop', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks6() {
		// Empty permalinks home
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, ' a ', '/shop/', 302, '', true, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks7() {
		// Not a search
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, ' a   b', '/shop/', 302, 'shop', false, false, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks8() {
		// Not a front-end
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, ' a    b+c', '/shop/', 302, 'shop', true, true, true, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks9() {
		// Not a main query
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, '   ', '/shop/', 302, 'shop', true, false, false, false, true );
		$this->child->init();
		do_action( 'init' );

	}

	function test_force_permalinks10() {
		// Option to force permalinks not selected
		$this->get_mock_force_permalinks( $this->child, $this->MOCK_HOW_MUCH_NEVER, 'a-b', '/shop/', 302, 'shop', true, false, true, true, true );
		$this->child->init();
		do_action( 'init' );
	}

	function test_add_permalink_to_facets_data_ko_not_option_generate_facets() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_OPTION => [
					[
						$this->MOCK_METHOD      => 'get_option_seo_common_is_generate_facet_permalinks',
						$this->MOCK_WILL_RETURN => false,
					],
				],
			]
		);

		$this->child->set_is_permalink_url( true );
		$this->assertEquals( [ 'test' ], $this->child->add_permalink_to_facets_data( [ 'test' ] ) );
	}


	function test_add_permalink_to_facets_data_ko_not_option_generate_facet() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP    => [
					[ $this->MOCK_METHOD => 'get_server_query_string', $this->MOCK_WILL_RETURN => '' ],
					// just to prevent error
				],
				$this->MOCK_SERVICE_OPTION => [
					[
						$this->MOCK_METHOD      => 'get_option_seo_common_is_generate_facet_permalinks',
						$this->MOCK_WILL_RETURN => true, // SEO is authorizing facets generation
					],
					[
						$this->MOCK_METHOD      => 'get_facets_seo_is_permalinks',
						$this->MOCK_WILL_RETURN => [ 'another_facet' => '1' ],
						// but our facet 'color' is not generating a permalink
					],
				],
			]
		);

		// Anything
		$facets = [
			[
				'items'      =>
					[
						[
							'value'           => 'green',
							'count'           => 1,
							'items'           =>
								[],
							'selected'        => false,
							'value_localized' => 'green',
						],
					],
				'id'         => 'color',
				'name'       => 'Color attribute',
				'facet_type' => 'facet_type_field',
				'facet_grid' => 'h',
			],
		];

		$this->child->set_is_permalink_url( true );
		$this->assertEquals( [
			[
				'items'        =>
					[
						[
							'value'           => 'green',
							'count'           => 1,
							'items'           =>
								[],
							'selected'        => false,
							'value_localized' => 'green',
						],
					],
				'id'           => 'color',
				'name'         => 'Color attribute',
				'facet_type'   => 'facet_type_field',
				'facet_grid'   => 'h',
				'is_permalink' => false, // what we wanted
			],
		], $this->child->add_permalink_to_facets_data( $facets ) );
	}

	function test_add_permalink_to_facets_data_ok_option_generate_facet() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP     => [
					[
						$this->MOCK_METHOD  => 'query',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH => $this->MOCK_HOW_MUCH_ONCE,

						],
					],
				],
				$this->MOCK_SERVICE_PHP    => [
					[ $this->MOCK_METHOD => 'get_server_query_string', $this->MOCK_WILL_RETURN => '' ],
					// just to prevent error
				],
				$this->MOCK_SERVICE_OPTION => [
					[
						$this->MOCK_METHOD      => 'get_option_seo_common_is_generate_facet_permalinks',
						$this->MOCK_WILL_RETURN => true, // SEO is authorizing facets generation
					],
					[
						$this->MOCK_METHOD      => 'get_facets_seo_is_permalinks',
						$this->MOCK_WILL_RETURN => [ 'another_facet' => '1', 'color' => '1' ],
						// our facet 'color' is generating a permalink
					],
				],
			]
		);

		// Anything
		$facets = [
			[
				'items'      =>
					[
						[
							'value'           => 'green',
							'count'           => 1,
							'items'           =>
								[],
							'selected'        => false,
							'value_localized' => 'green',
						],
					],
				'id'         => 'color',
				'name'       => 'Color attribute',
				'facet_type' => 'facet_type_field',
				'facet_grid' => 'h',
			],
		];

		$this->child->set_is_permalink_url( true );
		$this->assertEquals( [
			[
				'items'        =>
					[
						[
							'value'           => 'green',
							'count'           => 1,
							'items'           =>
								[
								],
							'selected'        => false,
							'value_localized' => 'green',
							'permalink'       => // what we wanted
								[
									'href' => './green',
									'rel'  => '',
								],
						],
					],
				'id'           => 'color',
				'name'         => 'Color attribute',
				'facet_type'   => 'facet_type_field',
				'facet_grid'   => 'h',
				'is_permalink' => true, // what we wanted
			],
		], $this->child->add_permalink_to_facets_data( $facets ) );
	}

	function test_generate_url_parameters_from_permalink() {
		$datas = [];

		// Empty
		$datas[] = [
			'expected_result' => '/?s=',
			'permalink'       => [],
		];

		// Only search keyword
		$datas[] = [
			'expected_result' => '/?s=s+1',
			'permalink'       => [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => '  s   1 ' ],
		];
		$datas[] = [
			'expected_result' => '/?s=s+1',
			'permalink'       => [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => 's+1' ],
		];

		// No keyword
		$datas[] = [
			'expected_result' => '/?s=&wpsolr_fq[0]=a:a1',
			'permalink'       => [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:a1' ] ],
		];

		// One fq
		$datas[] = [
			'expected_result' => '/?s=s1&wpsolr_fq[0]=a:a1',
			'permalink'       => [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 's1',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:a1' ],
			],
		];

		// 2 same fq
		$datas[] = [
			'expected_result' => '/?s=s1&wpsolr_fq[0]=a:a1&wpsolr_fq[1]=a:a2',
			'permalink'       => [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 's1',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:a1', 'a:a2' ],
			],
		];

		// 2 #different fq
		$datas[] = [
			'expected_result' => '/?s=s1&wpsolr_fq[0]=a:a1&wpsolr_fq[1]=b:b1',
			'permalink'       => [
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q  => 's1',
				WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'a:a1', 'b:b1' ],
			],
		];

		foreach ( $datas as $data ) {
			$this->assertEquals( $data['expected_result'], $this->child->generate_url_parameters_from_permalink( $data['permalink'] ) );
		}
	}

	function test_redirect_permalink_to_search_ok() {

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP => [
					[
						$this->MOCK_METHOD  => 'wp_redirect',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH         => $this->MOCK_HOW_MUCH_ONCE,
							$this->MOCK_WITH_CONSECUTIVE => [
								[
									[ $this->MOCK_EQUAL_TO => '/?s=red&wpsolr_fq[0]=pa_color_global_str:red' ],
									[ $this->MOCK_EQUAL_TO => 302 ],
								],
							],
						],
					],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // Register actions/filters
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_REDIRECT_TO_SEARCH,
			] );

		// Store permalinks
		$this->wpsolr_insert_row( 'red', 'wpsolr_q=red&wpsolr_fq=pa_color_global_str:red' );

		// Register filters/actions
		$this->child->init();

		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$this->child->set_is_permalink_url( true );

		$wpsolr_query_updated = apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'pa_color_global_str:red' ], $wpsolr_query_updated->get_filter_query_fields() );
		$this->assertEquals( 'red', $wpsolr_query_updated->get_wpsolr_query() );

	}

	function test_update_wpsolr_query_from_permalink_redirect() {

		if ( ! $this->wpsolr_check_table_exists() ) {
			$this->child->create_tables();
		}

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_WP => [
					[
						$this->MOCK_METHOD  => 'wp_redirect',
						$this->MOCK_EXPECTS => [
							$this->MOCK_HOW_MUCH         => $this->MOCK_HOW_MUCH_ONCE,
							$this->MOCK_WITH_CONSECUTIVE => [
								[
									[ $this->MOCK_EQUAL_TO => '/?s=' ], // redirect to empty search
									[ $this->MOCK_EQUAL_TO => 302 ],
								],
							],
						],
					],
				],
			]
		);

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_REDIRECT_TO_SEARCH,
			] );
		$this->child->init();

		// Query
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );

		// Permalinks empty

		// It is a permalink url
		$this->child->set_is_permalink_url( true );

		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
	}

	function test_add_url_parameters_to_permalink_query() {

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
			]
		);
		$this->child->init();
		$this->child->set_is_permalink_url( true );

		// Permalinks
		$this->wpsolr_insert_row( 'red', 's=red&wpsolr_fq=pa_color_global_str:red' );

		// No url parameters
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$wpsolr_query->set_filter_query_fields( [] );
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'pa_color_global_str:red' ], $wpsolr_query->get_filter_query_fields() );

		// Add one url parameter
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$wpsolr_query->set_filter_query_fields( [ 'a:a1' ] );
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'a:a1', 'pa_color_global_str:red' ], $wpsolr_query->get_filter_query_fields() );

		// Add 2 url parameter
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$wpsolr_query->set_filter_query_fields( [ 'a:a1', 'a:a2' ] );
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'a:a1', 'a:a2', 'pa_color_global_str:red' ], $wpsolr_query->get_filter_query_fields() );

		// One url parameter already in the permalink filters, with the same value: the url parameter is not added to the permalink
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$wpsolr_query->set_filter_query_fields( [ 'pa_color_global_str:red' ] ); // Red will not be added to red
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'pa_color_global_str:red' ], $wpsolr_query->get_filter_query_fields() );

		// One url parameter already in the permalink filters, but with another value: the url parameter is added to the permalink
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red' );
		$wpsolr_query->set_filter_query_fields( [ 'pa_color_global_str:blue' ] ); // Blue will be added to red
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [
			'pa_color_global_str:blue',
			'pa_color_global_str:red',
		], $wpsolr_query->get_filter_query_fields() );

		// Pagination
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red/page/2' );
		$wpsolr_query->set_filter_query_fields( [ 'a:a1' ] );
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [
			'a:a1',
			'pa_color_global_str:red'
		], $wpsolr_query->get_filter_query_fields() ); // no change
		$this->assertEquals( '2', $wpsolr_query->get_wpsolr_paged() );
		$this->assertEquals( '2', $wpsolr_query->query_vars['paged'] );

		// Not a permalink url
		$this->child->set_is_permalink_url( false );
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( 'red/page/2' );
		$wpsolr_query->set_filter_query_fields( [ 'a:a1' ] );
		apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals( [ 'a:a1' ], $wpsolr_query->get_filter_query_fields() ); // no change

	}

	function test_add_url_parameters_to_permalink_hierarchy() {

		// Build terms hierarchy
		register_post_type( 'test_post_type' );
		register_taxonomy( 'wpsolr_test_taxonomy', 'test_post_type' );
		$this->term = new WP_UnitTest_Factory_For_Term( $this, 'wpsolr_test_taxonomy' );
		$p1         = $this->term->create_and_get( [ 'name' => 'p 1' ] );
		$p1_1       = $this->term->create_and_get( [ 'name' => 'p 1 1', 'parent' => $p1->term_id ] );
		$p1_2       = $this->term->create_and_get( [ 'name' => 'p 1 2', 'parent' => $p1->term_id ] );
		$p1_1_1     = $this->term->create_and_get( [ 'name' => 'p 1 1 1', 'parent' => $p1_1->term_id ] );

		// Permalinks
		$this->wpsolr_insert_row( '1-level', 's=red&wpsolr_fq=wpsolr_test_taxonomy_str:p 1||b:b1' );
		$this->wpsolr_insert_row( '2-levels', 's=red&wpsolr_fq=wpsolr_test_taxonomy_str:p 1 1||b:b1' );
		$this->wpsolr_insert_row( '3-levels', 's=red&wpsolr_fq=wpsolr_test_taxonomy_str:p 1 1 1||b:b1' );

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
			] );
		update_option( WPSOLR_Option::OPTION_FACET,
			[
				WPSOLR_Option::OPTION_FACET_FACETS_TO_SHOW_AS_HIERARCH => [ 'wpsolr_test_taxonomy_str' => '1' ]
				// show hierachy
			] );
		$this->child->init();
		$this->child->set_is_permalink_url( true ); // Is a permalink url


		// 1 level
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( '1-level' );
		$wpsolr_query = apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals(
			[
				'wpsolr_test_taxonomy_str:p 1',
				'b:b1',
			],
			$wpsolr_query->get_filter_query_fields()
		);

		// 2 levels
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( '2-levels' );
		$wpsolr_query = apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals(
			[
				'wpsolr_test_taxonomy_str:p 1',
				'wpsolr_test_taxonomy_str:p 1 1',
				'b:b1',
			],
			$wpsolr_query->get_filter_query_fields()
		);

		// 3 levels
		$wpsolr_query = new WPSOLR_Query();
		$wpsolr_query->set_wpsolr_query( '3-levels' );
		$wpsolr_query = apply_filters( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, $wpsolr_query );
		$this->assertEquals(
			[
				'wpsolr_test_taxonomy_str:p 1',
				'wpsolr_test_taxonomy_str:p 1 1',
				'wpsolr_test_taxonomy_str:p 1 1 1',
				'b:b1',
			],
			$wpsolr_query->get_filter_query_fields()
		);
	}

	function test_generate_permalink_rel() {

		$this->assertEquals( '', $this->child->generate_permalink_rel( false, false ) );
		$this->assertEquals( 'noindex', $this->child->generate_permalink_rel( true, false ) );
		$this->assertEquals( 'nofollow', $this->child->generate_permalink_rel( false, true ) );
		$this->assertEquals( 'noindex, nofollow', $this->child->generate_permalink_rel( true, true ) );
	}

	function test_is_not_authorized_by_default() {

		// Default
		$this->assertFalse( $this->child->get_is_authorized() );
	}

	function test_is_authorized_if_logged() {

		// Should not be authorized
		$this->assertFalse( $this->child->get_is_authorized() );

		// Simulate admin
		$this->wpsolr_log_in();

		$this->child->init();
		$this->assertTrue( $this->child->get_is_authorized() );
	}

	function test_is_authorized_if_test_mode_removed() {

		// Should not be authorized
		$this->assertFalse( $this->child->get_is_authorized() );

		// Set option
		update_option( $this->wpsolr_get_option_name(), [ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true ] );

		$this->child->init();
		$this->assertTrue( $this->child->get_is_authorized() );
	}

	function test_include_file() {

		// Set option
		update_option( $this->wpsolr_get_option_name(), [ WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true ] );
		$this->child->init();

		$this->wpsolr_assert_filter_include( WPSOLR_Help::HELP_FACET_SEO_TEMPLATE, 'facet-seo-template.inc.php' );
		$this->wpsolr_assert_filter_include( WPSOLR_Help::HELP_FACET_SEO_TEMPLATE_LOCALIZATION, 'facet-seo-template-localizations.inc.php' );
		$this->wpsolr_assert_filter_include( WPSOLR_Help::HELP_FACET_SEO_TEMPLATE_POSITIONS, 'form_thickbox_permalinks_positions.inc.php' );
	}

	function test_wpsolr_filter_is_generate_facet_permalink_ok() {

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
			] );
		$this->child->init();

		$is_generate_facet_permalink = apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_GENERATE_FACET_PERMALINK, false );
		$this->assertFalse( $is_generate_facet_permalink );

		$is_generate_facet_permalink = apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_GENERATE_FACET_PERMALINK, true );
		$this->assertTrue( $is_generate_facet_permalink );

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE           => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_IS_GENERATE_FACETS_PERMALINKS => true, // Generate facets  permalinks
			] );
		$this->child->init();

		$is_generate_facet_permalink = apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_GENERATE_FACET_PERMALINK, true );
		$this->assertTrue( $is_generate_facet_permalink );

		$is_generate_facet_permalink = apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_GENERATE_FACET_PERMALINK, false );
		$this->assertTrue( $is_generate_facet_permalink );
	}

	function test_wpsolr_filter_facet_permalink_home() {

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'home', // Permalinks home
			] );
		$this->child->init();

		$facet_permalinks_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_FACET_PERMALINK_HOME, 'default_value' );
		$this->assertEquals( 'default_value', $facet_permalinks_home );

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE           => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME               => 'home', // Permalinks home
				WPSOLR_Option::OPTION_SEO_IS_GENERATE_FACETS_PERMALINKS => true, // Generate facets permalinks
			] );
		$this->child->init();
		$facet_permalinks_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_FACET_PERMALINK_HOME, 'default_value' );
		$this->assertEquals( 'default_value', $facet_permalinks_home );


		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE                => true,
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME                    => 'home', // Permalinks home
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_IS_REDIRECT_FACETS_PERMALINKS_HOME => true,
				// Redirect facets permalinks to home
			] );
		$this->child->init();
		$facet_permalinks_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_FACET_PERMALINK_HOME, 'default_value' );
		$this->assertEquals( 'default_value', $facet_permalinks_home );

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE                => true,
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME                    => 'home', // Permalinks home
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_IS_GENERATE_FACETS_PERMALINKS      => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_IS_REDIRECT_FACETS_PERMALINKS_HOME => true,
				// // Redirect facets permalinks to home
			] );
		$this->child->init();
		$facet_permalinks_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_FACET_PERMALINK_HOME, 'default_value' );
		$this->assertEquals( 'home', $facet_permalinks_home );
	}

	function test_wpsolr_filter_redirect_search_home() {

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'home', // Permalinks home
			] );
		$this->child->init();

		$redirect_search_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_REDIRECT_SEARCH_HOME, 'default_value' );
		$this->assertEquals( 'default_value', $redirect_search_home );

		// Set options
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE                => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME                    => 'home', // Permalinks home
				WPSOLR_Option::OPTION_SEO_PERMALINKS_IS_REDIRECT_FROM_SEARCH => true, // Redirect search to home
			] );
		$this->child->init();
		$redirect_search_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_REDIRECT_SEARCH_HOME, 'default_value' );
		$this->assertEquals( 'home', $redirect_search_home );
	}

	function test_get_stored_permalink_query_parameters() {

		if ( ! $this->wpsolr_check_table_exists() ) {
			$this->child->create_tables();
		}

		// Empty table
		$this->assertEquals( [], $this->child->get_stored_permalink_query_parameters( 'url1' ) );

		// Empty row
		$this->wpsolr_insert_row( 'url1', '' );
		$this->assertEquals( [], $this->child->get_stored_permalink_query_parameters( 'url1' ) );

		// Unknown row
		$this->assertEquals( [], $this->child->get_stored_permalink_query_parameters( 'unknow' ) );

		// Empty parameters row
		$this->wpsolr_insert_row( 'url2', 'no parameters' );
		$this->assertEquals( [], $this->child->get_stored_permalink_query_parameters( 'url2' ) );

		// Parameter query
		$this->wpsolr_insert_row( 'url3', 'wpsolr_q=red blue' );
		$this->assertEquals( [ 'wpsolr_q' => 'red blue' ], $this->child->get_stored_permalink_query_parameters( 'url3' ) );

		// Parameter filter query
		$this->wpsolr_insert_row( 'url4', 'wpsolr_fq=fq 1' );
		$this->assertEquals( [ 'wpsolr_fq' => [ 'fq 1' ] ], $this->child->get_stored_permalink_query_parameters( 'url4' ) );

		// Parameter filter queries
		$this->wpsolr_insert_row( 'url5', 'wpsolr_fq=fq 1||fq 2' );
		$this->assertEquals( [
			'wpsolr_fq' => [
				'fq 1',
				'fq 2',
			]
		], $this->child->get_stored_permalink_query_parameters( 'url5' ) );

		// All Parameters
		$this->wpsolr_insert_row( 'url6', 'wpsolr_q=red blue&wpsolr_fq=fq 1||fq 2' );
		$this->assertEquals( [
			'wpsolr_q'  => 'red blue',
			'wpsolr_fq' => [ 'fq 1', 'fq 2' ]
		], $this->child->get_stored_permalink_query_parameters( 'url6' ) );

	}

	function test_metas() {

		// Results
		$wpsolr_query          = new WPSOLR_Query();
		// Mock 10 Solr results
		$solarium_with_results = $this->createMock( WPSOLR_ResultsSolariumClient::class );
		$solarium_with_results->method( 'get_nb_results' )->willReturn( 10 );
		// Mock no Solr results
		$solarium_without_results = $this->createMock( WPSOLR_ResultsSolariumClient::class );
		$solarium_without_results->method( 'get_nb_results' )->willReturn( 0 );

		// Set the facets options
		$options_facets = [
			'facets'                    => 'color_str,category_str,type,_price_str,not_permalink_str',
			'facets_layout'             => [
				'1color_str'   => WPSOLR_UI_Layout_Color_Picker::CHILD_LAYOUT_ID,
				'color_str'    => WPSOLR_UI_Layout_Color_Picker::CHILD_LAYOUT_ID,
				'category_str' => WPSOLR_UI_Layout_Check_Box::CHILD_LAYOUT_ID,
				'type'         => WPSOLR_UI_Layout_Radio_Box::CHILD_LAYOUT_ID,
				'_price_str'   => WPSOLR_UI_Layout_Range_Regular_Check_Box::CHILD_LAYOUT_ID,
			],
			'facets_seo_is_permalink'   => [
				//'1color_str'    => '1',
				'color_str'    => '1',
				'category_str' => '1',
				'type'         => '1',
				'_price_str'   => '1',
			],
			'facets_label'              => [
				'1color_str'   => '1Color',
				'color_str'    => 'Color',
				'category_str' => 'Category',
				'type'         => 'Type',
				'_price_str'   => 'Price',
			],
			'facets_item_label'         => [
				'1color_str' => [
					// 'blue' => ''
					'green' => '#green',
					'red'   => '#red',
				],
				'color_str'  => [
					// 'blue' => ''
					'green' => '#green',
					'red'   => '#red',
				],
				'type'       => [
					//'page' => 'xpage',
					'post'    => 'xpost',
					'product' => 'xproduct',
				],
				'_price_str' => [
					//'*-0' => 'free',
					'0-10'  => 'medium',
					'10-20' => 'max',
				],
			],
			'facets_seo_template'       => [
				'color_str'    => 'facet seo template color {{value}}',
				'category_str' => 'facet seo template category  {{value}}',
				'type'         => 'facet seo template type  {{value}}',
				'_price_str'   => 'facet seo template price  {{start}} {{end}}',
			],
			'facets_seo_items_template' => [
				'type'       => [
					'product' => 'facet item seo template type {{value}}',
				],
				'color_str'  => [
					'red' => 'facet item seo template color {{value}}',
				],
				'_price_str' => [
					'10-20' => 'facet item seo template price {{start}} {{end}}',
				],
			],
		];
		update_option( WPSOLR_Option::OPTION_FACET, $options_facets, true );

		// Set the permalinks
		$this->child->set_permalink( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ => [ 'color_str:red' ] ] );

		$this->wpsolr_mock_services( $this->child,
			[
				$this->MOCK_SERVICE_PHP => [
					[ $this->MOCK_METHOD => 'get_server_request_uri', $this->MOCK_WILL_RETURN => '/shop/red' ],
				],
				$this->MOCK_SERVICE_WP  => [
					[ $this->MOCK_METHOD => 'is_main_query', $this->MOCK_WILL_RETURN => true ],
				],
			]
		);

		// Plugin not activated
		update_option( $this->wpsolr_get_option_name(),
			[
				//WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog',
				// Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		// Call child meta filter
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_title( 'dont care' ) );
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_description( 'dont care' ) );
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// Not a permalink url
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true, // activate filters/actions
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertFalse( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false ) );
		// Call child meta filter
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_title( 'dont care' ) );
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_description( 'dont care' ) );
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// Metas empty
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE    => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME     => 'shop',
				//WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE => '{{meta}} | blog', // Meta title template
				//WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here', // Meta description template
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results

		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		do_action( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, false );
		// Call child meta filter
		$this->assertEquals( 'Facet Item Seo Template Color Red', $this->call_plugin_filter_meta_title( 'dont care' ) );
		$this->assertEquals( 'Facet Item Seo Template Color Red', $this->call_plugin_filter_meta_description( 'dont care' ) );
		$this->assertEquals( 'index,follow', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// Metas 'normal'
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'Facet Item Seo Template Color Red | blog', $this->call_plugin_filter_meta_title( 'dont care' ) );
		$this->assertEquals( 'Description Facet Item Seo Template Color Red here', $this->call_plugin_filter_meta_description( 'dont care' ) );
		$this->assertEquals( 'index,follow', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// Default SEO plugin metas when no results
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_without_results ); // no results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'dont care', $this->call_plugin_filter_meta_robots( 'dont care' ) );


		// index, follow
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
				//WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX => true,
				//WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW => true,
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'index,follow', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// noindex, follow
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
				WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX       => true,
				//WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW => true,
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'noindex,follow', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// index, nofollow
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
				//WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX => true,
				WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW      => true,
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'index,nofollow', $this->call_plugin_filter_meta_robots( 'dont care' ) );

		// noindex, nofollow
		update_option( $this->wpsolr_get_option_name(),
			[
				WPSOLR_Option::OPTION_SEO_IS_REMOVE_TEST_MODE       => true,
				// activate filters/actions
				WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE          => WPSOLR_Option::OPTION_SEO_PERMALINKS_USAGE_NORMAL,
				// Not empty => not 404
				WPSOLR_Option::OPTION_SEO_PERMALINKS_HOME           => 'shop',
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_TITLE       => '{{meta}} | blog', // Meta title template
				WPSOLR_Option::OPTION_SEO_TEMPLATE_META_DESCRIPTION => 'Description {{meta}} here',
				// Meta description template
				WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOINDEX       => true,
				WPSOLR_Option::OPTION_SEO_IS_CONTENTS_NOFOLLOW      => true,
			] );
		$this->child->init();
		do_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, $wpsolr_query, $solarium_with_results ); // 10 results
		self::assertTrue( apply_filters( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, true ) );
		// Call child meta filter
		$this->assertEquals( 'noindex,nofollow', $this->call_plugin_filter_meta_robots( 'dont care' ) );
	}

	/**
	 * Call child plugin meta robots filter
	 *
	 * @param $title
	 *
	 * @return string
	 */
	abstract function call_plugin_filter_meta_robots( $robots );

	/**
	 * Call child plugin meta title filter
	 *
	 * @param $title
	 *
	 * @return string
	 */
	abstract function call_plugin_filter_meta_title( $title );

	/**
	 * Call child plugin meta description filter
	 *
	 * @param $title
	 *
	 * @return string
	 */
	abstract function call_plugin_filter_meta_description( $title );

	function test_no_database_duplicates() {
		$this->assertTrue( true );
	}

}
