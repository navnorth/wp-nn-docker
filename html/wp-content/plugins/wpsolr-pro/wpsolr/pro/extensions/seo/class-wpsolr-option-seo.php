<?php

namespace wpsolr\pro\extensions\seo;

use wpsolr\core\classes\engines\WPSOLR_AbstractResultsClient;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\ui\WPSOLR_Query_Parameters;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\utilities\WPSOLR_Regexp;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;
use wpsolr\pro\extensions\theme\layout\color_picker\WPSOLR_UI_Layout_Color_Picker;

/**
 * Class WPSOLR_Option_Seo
 *
 * Manage SEO for filters
 */
abstract class WPSOLR_Option_Seo extends WpSolrExtensions {

	// Filters
	const WPSOLR_FILTER_SEO_GET_NB_STORED_PERMALINKS = 'wpsolr_filter_seo_get_nb_stored_permalinks';

	// Current DB version. Aligned with a WPSOLR version. Change it to upgrade the DB schema.
	const DB_VERSION = '18.3';

	// Table names
	const CONST_TABLE_NAME_SEO_PERMALINKS = 'wpsolr_permalinks';

	// SQL
	const SQL_DROP_TABLE = /** @lang text */
		'DROP TABLE IF EXISTS %s;';

	const SQL_COUNT_TABLE = /** @lang text */
		'SELECT COUNT(0) FROM %s;';

	// Add this parameter to ?s= to pass permalink structure to the search.
	const SEARCH_PARAMETER_PERMALINK = 'wpsolr_permalink';

	const CHAR_WHITESPACE = ' ';
	const CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD = '-';
	const CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS = '+';

	// Array separator in DB strings
	const DB_ARRAY_SEPARATOR = '||';

	/**
	 * Remove the ending /page/x.
	 * anything/page/2 => anything
	 */
	const URL_PATTERN_NO_PAGES = '/(.*)\/page\/.*/';
	const URL_PATTERN_NO_PAGES_1 = '/page\/.*/';
	const URL_PATTERN_PAGE_NUMBER = '/(.*)\/page\/(\d*)(.*)/';
	const URL_PATTERN_PAGE_NUMBER_1 = '/page\/(\d*)(.*)/';

	// Redirect search forms
	const SEO_SEARCH_FORM_REDIRECTION = 'wpsolr_r';

	// Permalink tags
	const REL_TAG_NOINDEX = 'noindex';
	const REL_TAG_NOFOLLOW = 'nofollow';
	const REL_TAG_SEPARATOR = ', ';

	// Table name
	/* @var string $table_name */
	protected $table_name;

	/* @var array $permalink Current permalink */
	protected $permalink = [];

	/* @var array $facets_is_can_generate_permalink List of facets that can generate a permalink */
	protected $facets_is_can_generate_permalink;

	/* @var string $permalinks_home */
	protected $permalinks_home;

	/* var bool $is_permalink */
	protected $is_permalink_url;

	/* var bool */
	protected $is_content_noindex;
	/* var bool */
	protected $is_content_nofollow;
	/* var bool */
	protected $is_rel_noindex;
	/* var bool */
	protected $is_rel_nofollow;
	/* @var bool */
	protected $is_replace_search;
	/* @var bool */
	protected $is_authorized;
	/* @var array */
	protected $facets_positions;
	/* @var string */
	protected $page_meta_variable;
	/* @var int */
	protected $nb_results;
	/** @var  bool */
	protected $is_remove_test_mode;

	/**
	 * Subscribe to actions/filters
	 **/
	function init() {

		$this->is_permalink_url = false;
		$this->set_table_name( $this->get_table_name_prefixed( self::CONST_TABLE_NAME_SEO_PERMALINKS ) );
		$this->is_remove_test_mode = false;
		$this->is_authorized       = $this->calculate_is_authorized();

		if ( $this->get_container()->get_service_wp()->is_admin() ) {
			// Check db version, only on admin.
			$this->check_db_version( self::DB_VERSION );

			add_action( 'wp_ajax_' . 'wpsolr_ajax_drop_permalinks_table', [
				$this,
				'wpsolr_ajax_drop_permalinks_table',
			] );

			add_filter( self::WPSOLR_FILTER_SEO_GET_NB_STORED_PERMALINKS, [
				$this,
				'get_nb_stored_permalinks',
			], 10, 1 );

		}

		if ( $this->is_authorized ) {

			//remove_action( 'template_redirect', 'redirect_canonical' );

			$this->nb_results = 0;

			$this->permalinks_home = $this->get_permalinks_home();

			$this->is_rel_noindex  = $this->get_is_rel_noindex();
			$this->is_rel_nofollow = $this->get_is_rel_nofollow();

			$this->is_content_noindex  = $this->get_is_content_noindex();
			$this->is_content_nofollow = $this->get_is_content_nofollow();

			$this->is_replace_search = $this->get_is_permalinks_replace_search();
			$this->facets_positions  = $this->get_container()->get_service_option()->get_facets_seo_permalink_positions();

			add_action( WPSOLR_Events::WPSOLR_FILTER_UPDATE_FACETS_DATA, [
				$this,
				'add_permalink_to_facets_data',
			], 10, 1 );

			add_action( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, [
				$this,
				'wpsolr_filter_is_replace_by_wpsolr_query',
			], 10, 1 );

			add_action( 'init', [ $this, 'wp_action_init' ], 10, 0 );

			add_action( WPSOLR_Events::WPSOLR_FILTER_UPDATE_WPSOLR_QUERY, [
				$this,
				'update_wpsolr_query_from_permalink',
			], 10, 1 );

			add_action( WPSOLR_Events::WPSOLR_FILTER_REDIRECT_SEARCH_HOME, [
				$this,
				'wpsolr_filter_redirect_search_home',
			], 10, 1 );

			add_action( WPSOLR_Events::WPSOLR_FILTER_FACET_PERMALINK_HOME, [
				$this,
				'wpsolr_filter_facet_permalink_home',
			], 10, 1 );

			add_action( WPSOLR_Events::WPSOLR_FILTER_IS_GENERATE_FACET_PERMALINK, [
				$this,
				'wpsolr_filter_is_generate_facet_permalink',
			], 10, 1 );

			add_filter( WPSOLR_Events::WPSOLR_FILTER_INCLUDE_FILE, [ $this, 'wpsolr_filter_include_file' ], 10, 1 );

			add_action( WPSOLR_Events::WPSOLR_ACTION_POSTS_RESULTS, [ $this, 'wpsolr_action_posts_results' ], 10, 2 );

		} else {
			// Cannot rewrite home without a home
			flush_rewrite_rules( false ); // Remove previous rewrites
		}

	}

	/**
	 * Count nb of results
	 *
	 * @param WPSOLR_Query $wpsolr_query
	 * @param WPSOLR_AbstractResultsClient $results
	 */
	public function wpsolr_action_posts_results( WPSOLR_Query $wpsolr_query, WPSOLR_AbstractResultsClient $results ) {

		$this->nb_results = $results->get_nb_results();
	}

	/**
	 * Include the file containing the help feature.
	 *
	 * @param int $help_id
	 *
	 * @return string File name & path
	 */
	public function wpsolr_filter_include_file( $help_id ) {

		$file_name = '';

		switch ( $help_id ) {

			case WPSOLR_Help::HELP_FACET_SEO_TEMPLATE:
				$file_name = 'facet-seo-template.inc.php';
				break;

			case WPSOLR_Help::HELP_FACET_SEO_TEMPLATE_LOCALIZATION:
				$file_name = 'facet-seo-template-localizations.inc.php';
				break;

			case WPSOLR_Help::HELP_FACET_SEO_TEMPLATE_POSITIONS:
				$file_name = 'form_thickbox_permalinks_positions.inc.php';
				break;
		}

		$result = ! empty( $file_name ) ? sprintf( '%s/includes/%s', dirname( __FILE__ ), $file_name ) : $help_id;

		return $result;
	}

	/**
	 * Use facet permalinks ?
	 * @return bool
	 */
	public function wpsolr_filter_is_generate_facet_permalink( $is_facet_permalink ) {

		if ( $this->is_generate_facet_permalinks() ) {
			return true;
		}

		return $is_facet_permalink;
	}

	/**
	 * Get facets permalinks home
	 * @return string
	 */
	public function wpsolr_filter_facet_permalink_home( $redirect_home ) {

		if ( ! $this->is_generate_facet_permalinks() ) {
			return $redirect_home;
		}

		if ( ! $this->is_redirect_facet_to_permalink_home() ) {
			return $redirect_home;
		}

		$permalink_home = $this->get_permalinks_home();

		return ! empty( $permalink_home ) ? $permalink_home : $redirect_home;
	}

	/**
	 * Get permalinks home if search redirection
	 * @return string
	 */
	public function wpsolr_filter_redirect_search_home( $redirect_home ) {

		if ( $this->get_is_permalinks_replace_search() ) {
			return $this->get_permalinks_home();
		}

		return $redirect_home;
	}

	/**
	 * Permalinks redirection
	 * @return string
	 */
	protected function get_permalinks_home() {

		$permalink_home = $this->get_container()->get_service_option()->get_option_seo_common_permalinks_home( $this->get_option_name( $this->get_extension_name() ) );

		// Chance to get the translated permalink home => '/fr/shop'
		$permalink_home = apply_filters( WPSOLR_Events::WPSOLR_FILTER_SEARCH_PAGE_URL, $permalink_home, null );

		return $permalink_home;
	}

	/**
	 * SEO template of meta title
	 * @return string
	 */
	protected function get_seo_template_meta_title() {

		$value = $this->get_container()->get_service_option()->get_option_seo_template_meta_title( $this->get_option_name( $this->get_extension_name() ) );

		return ! empty( $value ) ? $value : WPSOLR_Option::OPTION_SEO_META_VAR_VALUE;
	}

	/**
	 * SEO template of meta description
	 * @return string
	 */
	protected function get_seo_template_meta_description() {

		$value = $this->get_container()->get_service_option()->get_option_seo_template_meta_description( $this->get_option_name( $this->get_extension_name() ) );

		return ! empty( $value ) ? $value : WPSOLR_Option::OPTION_SEO_META_VAR_VALUE;
	}

	/**
	 * Replace search with permalinks
	 * @return string
	 */
	protected function get_is_permalinks_replace_search() {

		return $this->get_container()->get_service_option()->get_option_seo_common_is_replace_search( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Getter
	 *
	 * @return mixed
	 */
	public function get_is_permalink_url() {
		return $this->is_permalink_url;
	}

	/**
	 * Setter
	 *
	 * @param mixed $is_permalink_url
	 */
	public function set_is_permalink_url( $is_permalink_url ) {
		$this->is_permalink_url = $is_permalink_url;
	}

	/**
	 * @param bool $is_rel_noindex
	 */
	public function set_rel_noindex( $is_rel_noindex ) {
		$this->is_rel_noindex = $is_rel_noindex;
	}

	/**
	 * @param bool $is_rel_nofollow
	 */
	public function set_rel_nofollow( $is_rel_nofollow ) {
		$this->is_rel_nofollow = $is_rel_nofollow;
	}

	/**
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * @param string $table_name
	 */
	public function set_table_name( $table_name ) {
		$this->table_name = $table_name;
	}

	/**
	 * @return bool
	 */
	public function get_is_authorized() {
		return $this->is_authorized;
	}

	/**
	 * Replace search with permalinks
	 * @return string
	 */
	protected function get_is_permalinks_404() {

		return $this->get_container()->get_service_option()->get_option_seo_common_is_404_permalinks( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * rel noindex tag
	 * @return string
	 */
	protected function get_is_rel_noindex() {

		return $this->get_container()->get_service_option()->get_option_seo_common_permalinks_is_tag_noindex( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * rel nofollow tag
	 * @return string
	 */
	protected function get_is_rel_nofollow() {

		return $this->get_container()->get_service_option()->get_option_seo_common_permalinks_is_tag_nofollow( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * content noindex tag
	 * @return string
	 */
	protected function get_is_content_noindex() {

		return $this->get_container()->get_service_option()->get_option_seo_common_contents_is_tag_noindex( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * content nofollow tag
	 * @return string
	 */
	protected function get_is_content_nofollow() {

		return $this->get_container()->get_service_option()->get_option_seo_common_contents_is_tag_nofollow( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Redirect permalinks to search pages
	 * @return string
	 */
	protected function is_redirect_permalinks_to_search() {

		return $this->get_container()->get_service_option()->get_option_seo_common_is_redirect_permalinks_to_search( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Generate facet permalinks ?
	 * @return bool
	 */
	protected function is_generate_facet_permalinks() {

		return $this->get_container()->get_service_option()->get_option_seo_common_is_generate_facet_permalinks( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Redirect facets to permalinks home ?
	 * @return bool
	 */
	protected function is_redirect_facet_to_permalink_home() {

		return $this->get_container()->get_service_option()->get_option_seo_common_is_redirect_facet_to_permalink_home( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Stealth mode not activated, or user is logged
	 * @return bool
	 */
	protected function calculate_is_authorized() {

		if ( is_user_logged_in() ) {
			return true;
		}

		$this->is_remove_test_mode = $this->get_container()->get_service_option()->get_option_seo_common_is_remove_test_mode( $this->get_option_name( $this->get_extension_name() ) );
		if ( $this->is_remove_test_mode ) {
			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	abstract protected function get_extension_name();

	/**
	 * Retrieve the permalink query in storage
	 *
	 * @param $permalink_url
	 *
	 * @return array
	 */
	public function get_stored_permalink_query_parameters( $permalink_url ) {

		// Retrieve the older permalink with the same url.
		$select_statement = $this->get_container()->get_service_wp()->prepare(
			"SELECT query FROM $this->table_name WHERE url = %s order by time asc limit 1",
			$permalink_url
		);
		$permalink_query  = $this->get_container()->get_service_wp()->get_col( $select_statement );

		// Format the permalink definition
		$permalink = [];
		if ( $permalink_query ) {

			// Solve pb with urls containing '&amp;', but '&' is a url separator too!
			$permalink_query_str = str_replace( '&amp;', 'WPSOLR_AMP', $permalink_query[0] );

			parse_str( $permalink_query_str, $permalink_raw );

			if ( isset( $permalink_raw[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) ) {

				$permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] = $permalink_raw[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ];
			}

			if ( isset( $permalink_raw[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) ) {

				$result = explode( self::DB_ARRAY_SEPARATOR, $permalink_raw[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] );

				foreach ( $result as &$value ) {
					// Replace back
					$value = str_replace( 'WPSOLR_AMP', '&amp;', $value );
				}

				$permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] = $result;
			}
		}

		return $permalink;
	}

	/**
	 *
	 * Update the WPSOLR query with the data extracted from the permalink.
	 *
	 * @param WPSOLR_Query $wpsolr_query
	 *
	 * @return WPSOLR_Query
	 */
	public function update_wpsolr_query_from_permalink( WPSOLR_Query $wpsolr_query ) {

		if ( ! $this->is_permalink_url ) {
			// This url is not a permalink: do not update the query.
			return $wpsolr_query;
		}

		// Permalink is stored in the query parameter (see method custom_rewrite_rule())
		// Decode url: 'cat%C3%A9gorie-produit-2' => 'catégorie-produit-2'
		$permalink_full_url = rawurldecode( $wpsolr_query->get_wpsolr_query() );

		// Extract the page/2 from the permalink
		// Remove ending /page/xxx
		$permalink_url             = preg_replace( self::URL_PATTERN_NO_PAGES, '$1', $permalink_full_url );
		$permalink_url             = preg_replace( self::URL_PATTERN_NO_PAGES_1, '$1', $permalink_url );
		$permalink_url_page_number = preg_replace( self::URL_PATTERN_PAGE_NUMBER, '$2', $permalink_full_url );
		$permalink_url_page_number = preg_replace( self::URL_PATTERN_PAGE_NUMBER_1, '$1', $permalink_url_page_number );

		$page = ( $permalink_url_page_number !== $permalink_full_url ) ? $permalink_url_page_number : '1';
		$wpsolr_query->set_wpsolr_paged( $page );
		if ( '1' !== $page ) {
			$wpsolr_query->query_vars['paged'] = $page;
		}

		// Retrieve the permalink definition
		$permalink = $this->get_stored_permalink_query_parameters( $permalink_url );

		// Redirection ?
		if ( $this->is_redirect_permalinks_to_search() ) {
			// Stop and redirect now to search pages.
			$this->get_container()->get_service_wp()->wp_redirect(
				$this->generate_url_parameters_from_permalink( $permalink ),
				302
			);
		}

		// Map the permalink to the query
		if ( ! empty( $permalink ) ) {

			// Reset standard parameters
			$wpsolr_query->set_wpsolr_query( '' );
			$wpsolr_query->query_vars['s'] = '';

			if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) ) {
				$wpsolr_query->set_wpsolr_query( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] );
				$wpsolr_query->query_vars['s'] = $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ];
			}

			if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) ) {

				$wpsolr_query_field_values = $wpsolr_query->get_filter_query_fields();

				// Manage hierarchies: add it's parents to the query
				$facets_to_show_as_hierarchy = $this->get_container()->get_service_option()->get_facets_to_show_as_hierarchy();
				if ( ! empty( $facets_to_show_as_hierarchy ) ) {

					// Is it a sub category ?
					foreach ( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] as $wpsolr_query_field_value ) {
						$wpsolr_query_field = WPSOLR_Regexp::extract_first_separator( $wpsolr_query_field_value, ':' );

						if ( ! empty( $wpsolr_query_field ) && ! empty( $facets_to_show_as_hierarchy[ $wpsolr_query_field ] ) ) {
							// It's a hierarchy facet

							// Term
							$wpsolr_query_field_value = WPSOLR_Regexp::extract_last_separator( $wpsolr_query_field_value, ':' );

							// Taxonomy
							$taxonomy = WPSOLR_Regexp::remove_string_at_the_end( $wpsolr_query_field, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING );

							// Term id
							$term = $this->get_container()->get_service_wp()->get_term_by( 'name', $wpsolr_query_field_value, $taxonomy );

							// Term ancestors
							if ( ! empty( $term ) ) {
								$term_ancestors_ids = $this->get_container()->get_service_wp()->get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

								if ( ! empty( $term_ancestors_ids ) ) {

									$term_ancestors_names = $this->get_container()->get_service_wp()->get_terms( [
										'taxonomy'   => $taxonomy,
										'include'    => $term_ancestors_ids,
										'fields'     => 'names',
										'hide_empty' => false, // Important
									] );

									if ( ! empty( $term_ancestors_names ) ) {
										foreach ( $term_ancestors_names as $key => $term_ancestors_name ) {
											$wpsolr_query_field_values[] = sprintf( '%s:%s', $wpsolr_query_field, $term_ancestors_name );
										}
									}
								}
							}
						}
					}
				}

				// Merge url query filters with permalinks (permalink filters win over url filters)
				$wpsolr_query_field_values = array_unique( array_merge( $wpsolr_query_field_values, $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) );

				$wpsolr_query->set_filter_query_fields( $wpsolr_query_field_values );
			}


			if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_SORT ] ) ) {
				$wpsolr_query->set_wpsolr_sort( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_SORT ] );
			}

		} else {
			// Permalink not found: create a simple search with the url.
			$permalink_url = $this->format_permalink_url( $permalink_url, self::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS, self::CHAR_WHITESPACE );

			$wpsolr_query->set_wpsolr_query( $permalink_url );
			$wpsolr_query->query_vars['s'] = $permalink_url;

			$permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] = $permalink_url;
		}

		// Store the permalink definition
		$this->permalink = $permalink;

		return $wpsolr_query;
	}

	/**
	 * Generate parameters from a permalink, for a url redirection.
	 *
	 * @param array $permalink
	 */
	function generate_url_parameters_from_permalink( $permalink ) {

		// Generate the fq parameters from the fq values
		$fq_pameters = [];
		if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) ) {
			foreach ( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] as $indice => $fq_value ) {
				$fq_pameters[] = sprintf( '%s[%s]=%s', WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ, $indice, $fq_value );
			}
		}

		$q_parameter = $this->format_permalink_url( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) ? $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] : '', self::CHAR_WHITESPACE, self::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS );
		if ( empty( $fq_pameters ) ) {
			$url = sprintf( '/?s=%s', $q_parameter );

		} else {

			$url = sprintf( '/?s=%s&%s', $q_parameter, implode( '&', $fq_pameters ) );
		}

		return $url;
	}

	/**
	 *
	 * Replace WP query by a WPSOLR query ?.
	 *
	 * @param bool $is_replace_by_wpsolr_query
	 *
	 * @return bool
	 */
	public function wpsolr_filter_is_replace_by_wpsolr_query( $is_replace_by_wpsolr_query ) {

		if ( empty( $this->permalinks_home ) ) {
			return $is_replace_by_wpsolr_query;
		}

		if ( $this->get_container()->get_service_wp()->is_admin() ) {
			return $is_replace_by_wpsolr_query;
		}

		if ( ! $this->get_container()->get_service_wp()->is_main_query() ) {
			return $is_replace_by_wpsolr_query;
		}

		$url = $this->get_container()->get_service_php()->get_server_request_uri();

		if ( false !== strpos( $url, '.php' ) ) {
			// Ajax or cron
			return $is_replace_by_wpsolr_query;
		}

		if ( $this->get_is_permalinks_404() ) {
			// Option that deactivates permalinks
			return $is_replace_by_wpsolr_query;
		}

		if ( $this->get_container()->get_service_wpsolr()->starts_with_folder( $url, $this->permalinks_home ) ) {
			// Url is contained in the permalinks home.
			$this->is_permalink_url = true;

			// Now we can add seo plugin filters
			$this->add_seo_filters();

			return true;
		}

		return $is_replace_by_wpsolr_query;
	}

	public function wp_action_init() {

		$this->custom_rewrite_rule();

		$this->replace_search();
	}

	/**
	 * Rewrite rules for search permalinks.
	 */
	public function custom_rewrite_rule() {

		if ( empty( $this->permalinks_home ) ) {
			// Cannot rewrite home without a home
			flush_rewrite_rules( false ); // Remove previous rewrites

			return;
		}

		if ( $this->get_is_permalinks_404() ) {
			// Decision to not rewrite home but 404 instead
			return;
		}

		// Rewrite rule must not include the language, else error in WP redirections.
		$permalinks_home_for_rewrite = $this->get_container()->get_service_option()->get_option_seo_common_permalinks_home( $this->get_option_name( $this->get_extension_name() ) );


		$extra_parameters = apply_filters( WPSOLR_Events::WPSOLR_FILTER_EXTRA_URL_PARAMETERS, [] );
		if ( ! empty( $extra_parameters ) ) {
			$extra_parameters = sprintf( '&%s', http_build_query( $extra_parameters ) );
		} else {
			$extra_parameters = '';
		}
		// Code behind a permalink url is a search
		$this->get_container()->get_service_wp()->add_rewrite_rule( sprintf( '%s/(.*)?$', $permalinks_home_for_rewrite ), sprintf( 'index.php?s=$matches[1]%s', $extra_parameters ), 'top' );
		$this->get_container()->get_service_wp()->add_rewrite_rule( sprintf( '%s?$', $permalinks_home_for_rewrite ), sprintf( 'index.php?s=%s', $extra_parameters ), 'top' );

		flush_rewrite_rules( false );
	}

	/**
	 * Redirect all searches to permalinks, eventually.
	 */
	public function replace_search() {

		if ( empty( $this->permalinks_home ) ) {
			return;
		}

		if ( $this->get_container()->get_service_wp()->is_admin() ) {
			return;
		}

		if ( ! $this->get_container()->get_service_wp()->is_main_query() ) {
			return;
		}

		if ( ! $this->get_is_permalinks_replace_search() ) {
			return;
		}

		if ( $this->is_redirect_permalinks_to_search() ) {
			return;
		}


		if ( $this->get_container()->get_service_wpsolr()->is_wp_search() ) {
			// A search with ?s

			// Extract url parameters
			$wpsolr_query = $this->get_container()->get_service_wpsolr_query();

			$query = $this->format_permalink_url( $wpsolr_query->get_wpsolr_query(), self::CHAR_WHITESPACE, self::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS );
			$this->get_container()->get_service_wp()->wp_redirect( sprintf( '/%s/%s', $this->permalinks_home, $query ), 302 );

		} elseif ( $this->get_container()->get_service_wpsolr()->starts_with_folder( $this->get_container()->get_service_php()->get_server_request_uri(), '/search/' ) ) {
			// A /search/* permalink: redirect to /home/*

			$this->get_container()->get_service_wp()->wp_redirect(
				WPSOLR_Regexp::str_replace_first( 'search', $this->permalinks_home, $this->get_container()->get_service_php()->get_server_request_uri() )
				, 302 );
		}

	}

	/**
	 *
	 * Add permalink infos to facets data.
	 *
	 * @param array $facets_data
	 *
	 * @return array
	 */
	public function add_permalink_to_facets_data( $facets_data ) {

		if ( ! $this->is_generate_facet_permalinks() ) {
			return $facets_data;
		}

		$values = [];

		// Facet redirect to the permalinks home, or to the current page ?
		$facet_base_url = $this->is_redirect_facet_to_permalink_home() ? $this->permalinks_home : '';

		// Url parameters
		$url_parameters = $this->get_container()->get_service_php()->get_server_query_string();
		$url_parameters = ! empty( $url_parameters ) ? sprintf( '?%s', $url_parameters ) : '';

		// List of facets generating a permalink
		$this->facets_is_can_generate_permalink = $this->get_container()->get_service_option()->get_facets_seo_is_permalinks();

		foreach ( $facets_data as &$facet_data ) {
			$facet_data['is_permalink'] = ! empty( $this->facets_is_can_generate_permalink[ $facet_data['id'] ] );
			if ( ! empty( $facet_data['items'] ) && $facet_data['is_permalink'] ) {
				$this->add_permalink_to_facets_data_items( $values, $facet_data, $facet_data['items'], $facet_base_url, $url_parameters );
			}
		}

		$sql_statement = $this->prepare_multiple_insert( $this->table_name, $values );
		if ( ! empty( $sql_statement ) ) {
			$this->get_container()->get_service_wp()->query( $this->prepare_multiple_insert( $this->table_name, $values ) );
		}

		return $facets_data;
	}

	/**
	 * Reorder facets accordingly to their SEO position
	 *
	 * @param array $href_facets
	 *
	 * @return array
	 */
	function reorder_facets_data( $href_facets ) {

		if ( empty( $href_facets ) ) {
			return [];
		}

		// Reorder the facet_names
		$ordered_href_facets = [];
		if ( ! empty( $this->facets_positions ) ) {
			$positions = array_merge( $this->facets_positions, array_keys( $href_facets ) ); // Insure all facets are in positions
			if ( ! empty( $this->facets_positions ) ) {
				foreach ( $this->facets_positions as $facet_position_name ) {
					foreach ( $href_facets as $facet_name => $href_values ) {
						if ( $facet_position_name === $facet_name ) {
							$ordered_href_facets[ $facet_position_name ] = $href_values;
							break;
						}
					}
				}
			}
		} else {
			$ordered_href_facets = $href_facets;
		}

		// The keywords are treated separately
		if ( isset( $href_facets[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) && ( count( $href_facets ) > 0 ) ) {

			// Move keywords to first position
			$ordered_href_facets = array_merge( [ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q => $href_facets[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ], $ordered_href_facets );
		}

		// Reorder each facet content alphabetically and flatten it (remove the $facet key)
		$flattened_href_facets = [];
		foreach ( $ordered_href_facets as $facet_name => $facet_contents ) {
			// Sort each facet content and add it to results
			sort( $facet_contents );
			foreach ( $facet_contents as $ordered_content ) {
				$flattened_href_facets[] = $ordered_content;
			}
		}

		return $flattened_href_facets;
	}

	/**
	 *
	 * Add permalink infos to facets data items.
	 *
	 * @param $values
	 * @param $facet_data
	 * @param $facets_data_items
	 * @param $facet_base_url
	 *
	 * @return array
	 * @internal param array $facets_data
	 *
	 */
	public
	function add_permalink_to_facets_data_items(
		&$values, $facet_data, &$facets_data_items, $facet_base_url, $url_parameters = ''
	) {

		foreach ( $facets_data_items as &$facet_item ) {

			$facet_item['permalink'] = $this->generate_permalink( $values, $facet_data['id'], $facet_item, $facet_base_url, $url_parameters, self::CHAR_TO_REPLACE_WHITESPACE_INSIDE_WORD, self::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS );

			if ( ! empty( $facet_item['items'] ) ) {

				// Recursive for subcategories
				$this->add_permalink_to_facets_data_items( $values, $facet_data, $facet_item['items'], $facet_base_url, $url_parameters );
			}
		}
	}

	/**
	 * Generate a permalink from a facet
	 *
	 * @param $values
	 * @param $facet_name
	 * @param $facet_item
	 * @param $facet_base_url
	 * @param string $url_parameters
	 *
	 * @param $
	 *
	 * @param $
	 *
	 * @return array
	 * @throws \Exception
	 * @internal param $facet_layout_id
	 * @internal param $facet_type
	 * @internal param array $facet
	 */
	public
	function generate_permalink(
		&$values, $facet_name, $facet_item, $facet_base_url, $url_parameters, $char_to_replace_whitespace_inside_word, $char_to_replace_whitespace_between_words

	) {

		/**
		 * /rouge
		 *
		 * rouge => /rouge
		 * vert => /vert+rouge
		 */

		$href = [];

		$current_permalink                                                 = [];
		$current_permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] = [];

		if ( ! empty( $facet_name ) && ! empty( $facet_item ) ) {

			$facet_item_value = sprintf( '%s:%s', $facet_name, $facet_item['value'] );
			if ( ! $facet_item['selected'] ) {
				// Add a facet if not selected.
				$current_permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ][] .= $facet_item_value;
			}
		} else {
			$facet_item_value = '';
		}

		if ( ! empty( $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) ) {
			$current_permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] = $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ];
			if ( ! empty( $facet_name ) && ! empty( $facet_item ) ) {
				$href[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ][] = $this->format_permalink_url( $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ], self::CHAR_WHITESPACE, self::CHAR_TO_REPLACE_WHITESPACE_BETWEEN_WORDS );
			} else {
				$href[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ][] = $this->format_permalink_url( $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ], self::CHAR_WHITESPACE, self::CHAR_WHITESPACE );
			}
		}

		if ( ! empty( $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) ) {
			// Map filters to the permalink query
			foreach ( $this->permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] as $filter_str ) {
				if ( ( $facet_item_value !== $filter_str ) ) {

					// Retrieve layout object for this filter'facet name
					$filter_name     = WPSOLR_Regexp::extract_first_separator( $filter_str, ':' );
					$facet_layout_id = $this->get_facet_layout_id( $filter_name );
					/** @var WPSOLR_UI_Layout_Abstract $layout_object */
					$layout_object = apply_filters( WPSOLR_Events::WPSOLR_FILTER_LAYOUT_OBJECT, null, $facet_layout_id );
					if ( is_null( $layout_object ) || $layout_object->get_is_multi_filter( $this->get_facet_is_multiple( $filter_name ) ) ) {
						$current_permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ][] .= $filter_str;
					}
				}
			}
		}


		foreach ( $current_permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] as $filter_str ) {
			if ( ! empty( $filter_str ) ) {
				$filter_name  = WPSOLR_Regexp::extract_first_separator( $filter_str, ':' );
				$filter_value = WPSOLR_Regexp::extract_last_separator( $filter_str, ':' );

				// Buid the permalink from the filter seo template and the facet value
				$facet_item_seo_template = $this->get_container()->get_service_option()->get_facets_seo_permalink_item_template( $filter_name, $filter_value );
				if ( empty( $facet_item_seo_template ) ) {
					// Facet item has no seo template: use the facet seo template
					$facet_item_seo_template = $this->get_container()->get_service_option()->get_facets_seo_permalink_template( $filter_name );

					// Get facet seo template translation
					$facet_seo_template_translated = apply_filters( WPSOLR_Events::WPSOLR_FILTER_TRANSLATION_STRING, $facet_item_seo_template,
						[
							'domain'   => WPSOLR_Option::TRANSLATION_DOMAIN_FACET_SEO_TEMPLATE,
							'name'     => $filter_name,
							'text'     => $facet_item_seo_template,
							'language' => null,
						]
					);

				} else {
					// Get facet item seo template translation
					$facet_seo_template_translated = apply_filters( WPSOLR_Events::WPSOLR_FILTER_TRANSLATION_STRING, $facet_item_seo_template,
						[
							'domain'   => WPSOLR_Option::TRANSLATION_DOMAIN_FACET_ITEM_SEO_TEMPLATE,
							'name'     => $filter_value,
							'text'     => $facet_item_seo_template,
							'language' => null,
						]
					);
				}

				// Template expansion depends on the facet type
				$facet_type      = $this->get_facet_type( $filter_name );
				$facet_layout_id = $this->get_facet_layout_id( $filter_name );
				switch ( $facet_type ) {
					case WPSOLR_Option::OPTION_FACET_FACETS_TYPE_RANGE:
						$range = explode( '-', $filter_value, 2 );

						if ( 2 !== count( $range ) ) {
							throw new \Exception( sprintf( 'Wrong format: facet range %s with wrong value "%s"', $filter_name, $filter_value ) );
						}

						$facet_seo_template_value = $facet_seo_template_translated;
						$facet_seo_template_value = str_replace( WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_START, $range[0], $facet_seo_template_value );
						$facet_seo_template_value = str_replace( WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_END, $range[1], $facet_seo_template_value );
						break;

					default:
						switch ( $facet_layout_id ) {
							case WPSOLR_UI_Layout_Color_Picker::CHILD_LAYOUT_ID:
								// Do not use #ffffff, but 'white'
								$filter_value_for_template = $filter_value;
								break;

							default:
								$filter_value_for_template = $this->get_container()->get_service_option()->get_facets_item_label( $filter_name, $filter_value );
								break;
						}

						$facet_seo_template_value = str_replace( WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_VALUE, $filter_value_for_template, $facet_seo_template_translated );
						break;
				}

				$href[ $filter_name ][] = $this->format_permalink_url( $facet_seo_template_value, self::CHAR_WHITESPACE, $char_to_replace_whitespace_inside_word );
			}
		}

		// Reorder href parts according to the SEO positions
		$reordered_href = $this->reorder_facets_data( $href );

		// Clean url
		$href_str = implode( $char_to_replace_whitespace_between_words, $reordered_href );

		if ( ! empty( $facet_name ) && ! empty( $facet_item ) ) {
			array_push( $values, $href_str, $this->generate_permalink_unique_query( $current_permalink ) );

			if ( empty( $facet_base_url ) ) {
				// Current page
				$href_str = sprintf( './%s%s', strtolower( $href_str ), $url_parameters );
			} else {
				// Permalinks home page
				$href_str = sprintf( '/%s/%s%s', $facet_base_url, $href_str, $url_parameters );
			}
		}

		return [
			'href' => $href_str,
			'rel'  => $this->generate_permalink_rel( $this->is_rel_noindex, $this->is_rel_nofollow ),
		];
	}

	/**
	 * @param $facet_name
	 *
	 * @return string
	 */
	public
	function get_facet_type(
		$facet_name
	) {

		return $this->get_container()->get_service_wp()->apply_filters__wpsolr_filter_facet_type( WPSOLR_Option::OPTION_FACET_FACETS_TYPE_FIELD, $facet_name );
	}

	/**
	 * @param $facet_name
	 *
	 * @return string
	 */
	public
	function get_facet_layout_id(
		$facet_name
	) {

		return $this->get_container()->get_service_option()->get_facets_layout_id( $facet_name );
	}

	/**
	 * @param $facet_name
	 *
	 * @return bool
	 */
	public
	function get_facet_is_multiple(
		$facet_name
	) {

		return $this->get_container()->get_service_option()->get_facets_is_multiple_value( $facet_name );
	}

	/**
	 * Generate a permalink "rel" tag.
	 *
	 * @param bool $is_tag_noindex
	 * @param bool $is_tag_nofollow
	 *
	 * @return string
	 */
	public
	function generate_permalink_rel(
		$is_tag_noindex, $is_tag_nofollow
	) {

		$rel = [];

		if ( $is_tag_noindex ) {
			$rel[] = self::REL_TAG_NOINDEX;
		}

		if ( $is_tag_nofollow ) {
			$rel[] = self::REL_TAG_NOFOLLOW;
		}

		return implode( self::REL_TAG_SEPARATOR, $rel );
	}

	/**
	 * Transform the $permalink_query so it is always unique when you permute it's parameters
	 *
	 * @param array $permalink
	 *
	 * @return mixed
	 */
	public
	function generate_permalink_unique_query(
		$permalink
	) {

		$result = [];

		if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ] ) ) {
			$keywords_cleaned = $this->replace_and_remove_doubles( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q ], '  ', ' ' );
			$result[]         = sprintf( '%s=%s', WPSOLR_Query_Parameters::SEARCH_PARAMETER_Q, $keywords_cleaned );
		}

		if ( ! empty( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) ) {
			// Insure unicity by sorting the array
			sort( $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] );
			$result[] = sprintf( '%s=%s', WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ, implode( self::DB_ARRAY_SEPARATOR, $permalink[ WPSOLR_Query_Parameters::SEARCH_PARAMETER_FQ ] ) );
		}

		return implode( '&', $result );
	}

	/**
	 * Prepare a SQL insert on several rows
	 *
	 * @param $table_name
	 * @param $values
	 *
	 * @return string
	 */
	public
	function prepare_multiple_insert(
		$table_name, $values
	) {
		global $wpdb;

		if ( empty( $values ) ) {
			return '';
		}

		if ( count( $values ) % 2 !== 0 ) {
			// Should be even
			throw new \Exception( sprintf( 'SQL error while storing permalinks. Number of inserted columns is %s, but should be even: %s', count( $values ), wp_json_encode( $values ) ) );
		}

		$parameters = [];
		$total      = count( $values ) / 2;
		for ( $i = 0; $i < $total; $i ++ ) {
			$parameters[] = '(%s,%s)';
		}

		// Use IGNORE to prevent rejection of the whole batch when some urls are already in the database.
		return $wpdb->prepare(
			"INSERT IGNORE INTO $table_name (url, query) VALUES " . implode( ',', $parameters ),
			$values
		);
	}

	/**
	 * Format a permalink url
	 *
	 * @param string $url
	 *
	 * @param $separated
	 * @param $separator
	 *
	 * @return string
	 */
	public
	function format_permalink_url(
		$url, $separated, $separator
	) {
		$url = strtolower( $url );
		$url = $this->replace_remove_special_characters( $url );
		$url = $this->replace_and_remove_doubles( $url, $separated, $separator );

		return $url;
	}

	/**
	 * Replace whitespaces (and double whitespaces)
	 *
	 * ' ' => ''
	 * ' x ' => 'x'
	 * 'x y' => 'x-y'
	 * '  x  y     z  ' => 'x-y-z'
	 *
	 * @param string $value
	 * @param string $replacement
	 *
	 * @return string
	 */
	public
	function replace_and_remove_doubles(
		$value, $replaced, $replacement
	) {

		$value = trim( $value, $replaced );

		while ( ! ( false === strpos( $value, $replaced . $replaced ) ) ) {
			// Contains double replaced character, replace by one
			$value = str_replace( $replaced . $replaced, $replaced, $value );
		}

		$value = str_replace( $replaced, $replacement, $value );


		// Ensure no double replacement characters appeared after the replacement
		$value = trim( $value, $replacement );
		while ( ! ( false === strpos( $value, $replacement . $replacement ) ) ) {
			// Contains double replaced character, replace by one
			$value = str_replace( $replacement . $replacement, $replacement, $value );
		}

		return $value;
	}

	/**
	 * Remove some characters
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function replace_remove_special_characters( $value ) {

		// Serious troubles
		$value = str_replace( "'", "", $value );
		// Uggly
		$value = str_replace( "&amp;", "", $value );

		return $value;
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *        wpsolr_permalinks - Table for storing permalinks
	 */
	public
	function create_tables() {
		global $wpdb;

		$wpdb->show_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );
	}

	/**
	 * Get Table schema
	 *
	 * @return string
	 */
	private
	function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$max_field_length_for_unique_and_utf8mb4 = '191';

		$tables = "
CREATE TABLE $this->table_name (
  time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  url varchar($max_field_length_for_unique_and_utf8mb4) UNIQUE NOT NULL,
  query varchar($max_field_length_for_unique_and_utf8mb4) NOT NULL,
  KEY time (time)
  ) $collate;
		";

		return $tables;
	}


	/**
	 * Get proper table name with db prefix
	 *
	 * @return string
	 */
	function get_table_name_prefixed( $table_name ) {
		global $wpdb;

		return "{$wpdb->prefix}$table_name";
	}

	/**
	 * Check version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public
	function check_db_version(
		$db_version
	) {

		if ( $this->get_container()->get_service_option()->get_db_current_version() !== $db_version ) {
			$this->create_tables();

			// Update current version
			$this->get_container()->get_service_option()->set_db_current_version( $db_version );
		}
	}


	/**
	 * @param array $permalink
	 */
	public
	function set_permalink(
		$permalink
	) {
		$this->permalink = $permalink;
	}


	/**
	 * Get the drop table sql statement
	 * @return string
	 */
	public
	function get_sql_statement_drop_table() {
		return sprintf( self::SQL_DROP_TABLE, $this->table_name );
	}

	/**
	 * @return string
	 */
	public
	function drop_permalinks_table() {

		$this->get_container()->get_service_wp()->query( $this->get_sql_statement_drop_table() );
		$last_error = $this->get_container()->get_service_wp()->get_wpdb()->last_error;

		// Reset the db version too.
		$this->get_container()->get_service_option()->set_db_current_version( '' );

		return $last_error;
	}

	public
	function wpsolr_ajax_drop_permalinks_table() {

		$result = [ 'status' => [ 'state' => 'OK', 'message' => '' ] ];

		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], WPSOLR_NONCE_FOR_DASHBOARD ) ) {
			$result['status']['state']   = 'KO';
			$result['status']['message'] = 'Security: wrong nonce.';
			echo wp_json_encode( $result );

			wp_die();
		}

		$last_error = $this->drop_permalinks_table();
		if ( ! empty( $last_error ) ) {
			$result['status']['state']   = 'KO';
			$result['status']['message'] = sprintf( 'An error occured that prevented the deletion. <br/>%s', $last_error );
		}

		echo wp_json_encode( $result );
		wp_die();
	}

	/**
	 * Register the corresponding seo plugin filters
	 *
	 * @return mixed
	 */
	abstract function add_seo_filters();


	/**
	 * Generate current url meta variable
	 * @return string
	 */
	protected function generate_page_meta_variable() {

		if ( is_null( $this->page_meta_variable ) ) {
			// Create

			$values = [];
			$href   = $this->generate_permalink( $values, '', [], '', '', self::CHAR_WHITESPACE, self::CHAR_WHITESPACE );

			// Cache it
			$meta_variable = ucwords( $href['href'] );
			$meta_variable = apply_filters( WPSOLR_Events::WPSOLR_FILTER_SEO_PAGE_META_VALUE, $meta_variable );

			// Cache it
			$this->page_meta_variable = $meta_variable;
		}

		return $this->page_meta_variable;
	}

	/**
	 * Build a meta HTML
	 *
	 * @param string $default_html 'search page'
	 * @param string $template '{{var}} || blog'
	 * @param string $meta_var '{{var}}'
	 *
	 * @return string 'red t-shirt || blog'
	 */
	protected function generate_meta_html( $default_html, $template, $meta_var ) {

		$meta_html = str_replace( $meta_var, $this->generate_page_meta_variable(), $template );

		return ! empty( $meta_html ) ? $meta_html : $default_html;
	}


	/**
	 * Build a meta robots
	 *
	 * @param $robots
	 *
	 * @return string
	 */
	protected function generate_meta_robots( $robots ) {

		$seo_robots = ( 0 === $this->nb_results )
			? $robots // no results: use the SEO plugin robots
			: sprintf( '%s,%s', $this->is_content_noindex ? 'noindex' : 'index', $this->is_content_nofollow ? 'nofollow' : 'follow' );

		return $seo_robots;
	}

	/**
	 * Build a meta title
	 *
	 * @param $title
	 *
	 * @return string
	 */
	protected function generate_meta_title( $title ) {

		$seo_meta_template = $this->get_seo_template_meta_title();

		$seo_title = $this->generate_meta_html( $title, $seo_meta_template, WPSOLR_Option::OPTION_SEO_META_VAR_VALUE );

		return ! empty( $seo_title ) ? $seo_title : $title;
	}

	/**
	 * Build a meta title
	 *
	 * @param $description
	 *
	 * @return string
	 */
	protected function generate_meta_description( $description ) {

		$seo_meta_template = $this->get_seo_template_meta_description();

		$seo_description = $this->generate_meta_html( $description, $seo_meta_template, WPSOLR_Option::OPTION_SEO_META_VAR_VALUE );

		return ! empty( $seo_description ) ? $seo_description : $description;
	}

	/**
	 * Defaut open graph image
	 *
	 * @return string
	 */
	protected function generate_open_graph_image_url() {
		return $this->get_container()->get_service_option()->get_option_seo_open_graph_image_url( $this->get_option_name( $this->get_extension_name() ) );
	}

	/**
	 * Current open graph url
	 *
	 * @return string
	 */
	protected function generate_open_graph_url() {
		return home_url() . $this->get_container()->get_service_php()->get_server_request_uri();
	}

	/**
	 * Canonical open graph url
	 *
	 * @return string
	 */
	protected function generate_open_graph_canonical_url() {
		return home_url() . $this->get_container()->get_service_php()->get_server_request_uri();
	}

	/**
	 * Get the count table sql statement
	 * @return string
	 */
	private function get_sql_statement_count_table() {
		return sprintf( self::SQL_COUNT_TABLE, $this->table_name );
	}

	/**
	 * Nb permalinks stored in table
	 *
	 * @param string $default
	 *
	 * @return string '1,897,901'
	 */
	public function get_nb_stored_permalinks( $default = '' ) {

		$results = $this->get_container()->get_service_wp()->get_col( $this->get_sql_statement_count_table() );

		if ( $results ) {
			return number_format( $results[0] ); // '1897901' => '1,897,901'
		}

		return $default;
	}
}