<?php

namespace wpsolr\pro\extensions\all_in_one_seo_pack;

use wpsolr\pro\extensions\seo\WPSOLR_Option_Seo;

/**
 * Class WPSOLR_Plugin_AllInOneSeoPack
 * @package wpsolr\pro\extensions\all_in_one_seo_pack
 */
class WPSOLR_Plugin_AllInOneSeoPack extends WPSOLR_Option_Seo {

	/**
	 * Constructor
	 * Subscribe to actions/filters
	 **/
	function __construct() {

		// Mandatory init
		$this->init();
	}

	/**
	 *
	 * @param string $metadesc
	 *
	 * @return string
	 */
	public function aioseop_description( $metadesc ) {

		return $this->generate_meta_description( $metadesc );
	}

	/**
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function aioseop_title( $title ) {

		return $this->generate_meta_title( $title );
	}

	/**
	 *
	 * @param string $robots
	 *
	 * @return string
	 */
	public function aioseop_robots_meta( $robots ) {

		return $this->generate_meta_robots( $robots );
	}

	/**
	 * @return string
	 */
	protected function get_extension_name() {
		return self::EXTENSION_ALL_IN_ONE_SEO;
	}

	/**
	 * Add an open graph image to permalinks
	 *
	 * @param \WPSEO_OpenGraph_Image $wpseo_ogi
	 */
	public function wpseo_add_opengraph_images( \WPSEO_OpenGraph_Image $wpseo_ogi ) {
		$url = $this->generate_open_graph_image_url();
		if ( ! empty( $url ) && ( false !== $url ) ) {
			$wpseo_ogi->add_image( $url );
		}
	}

	/**
	 * Open graph url
	 *
	 * @param string $url
	 *
	 * @return string Url
	 */
	public function wpseo_opengraph_url( $url ) {
		return $this->generate_open_graph_url();
	}

	/**
	 * Open graph canonical url
	 *
	 * @param string $url
	 *
	 * @return string Url
	 */
	public function wpseo_canonical( $url ) {
		return $this->generate_open_graph_url();
	}

	/**
	 * Register the corresponding seo plugin filters
	 *
	 * @return mixed
	 */
	function add_seo_filters() {

		add_filter( 'aioseop_description', [ $this, 'aioseop_description' ], 100, 1 );
		add_filter( 'aioseop_title', [ $this, 'aioseop_title' ], 100, 1 );
		//add_filter( 'aioseop_robots_meta', [ $this, 'aioseop_robots_meta' ], 100, 1 );
		//add_action( 'wpseo_add_opengraph_images', [ $this, 'wpseo_add_opengraph_images' ], 100, 1 );
		//add_action( 'wpseo_opengraph_url', [ $this, 'wpseo_opengraph_url' ], 100, 1 );
		//add_filter( 'wpseo_canonical', [ $this, 'wpseo_canonical' ], 100, 1 );
	}

}
