<?php

namespace wpsolr\pro\extensions\theme_listify;

use wpsolr\core\classes\WPSOLR_Db;

/**
 * Replace geolocation query on post_metas in Listify_WP_Job_Manager_Map::geolocation_search()
 *
 * $sql = $wpdb->prepare( "
 * SELECT $wpdb->posts.ID,
 * ( %s * acos(
 * cos( radians(%s) ) *
 * cos( radians( latitude.meta_value ) ) *
 * cos( radians( longitude.meta_value ) - radians(%s) ) +
 * sin( radians(%s) ) *
 * sin( radians( latitude.meta_value ) )
 * ) )
 * AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
 * FROM $wpdb->posts
 * INNER JOIN $wpdb->postmeta
 * AS latitude
 * ON $wpdb->posts.ID = latitude.post_id
 * INNER JOIN $wpdb->postmeta
 * AS longitude
 * ON $wpdb->posts.ID = longitude.post_id
 * WHERE 1=1
 * AND ($wpdb->posts.post_status = 'publish' )
 * AND latitude.meta_key='geolocation_lat'
 * AND longitude.meta_key='geolocation_long'
 * HAVING distance < %s
 * ORDER BY " . implode( ',', $args['orderby'] ),
 * $args['earth_radius'],
 * $args['latitude'],
 * $args['longitude'],
 * $args['latitude'],
 * $args['radius']
 * );
 *
 * Class WPSOLR_Theme_Listify_Filter_By_Distance
 * @package wpsolr\pro\extensions\theme_listify
 */
class WPSOLR_Theme_Listify_Filter_By_Distance extends WPSOLR_Db {

	/** @var  float $wpsolr_latitude */
	protected $wpsolr_latitude;

	/** @var  float $wpsolr_longitude */
	protected $wpsolr_longitude;

	/** @var  float $latitude */
	protected $wpsolr_radius;

	/**
	 * @return float
	 */
	public function wpsolr_get_latitude() {
		return $this->wpsolr_latitude;
	}

	/**
	 * @return float
	 */
	public function wpsolr_get_longitude() {
		return $this->wpsolr_longitude;
	}

	/**
	 * @return float
	 *
	 */
	public function wpsolr_get_radius() {
		return $this->wpsolr_radius;
	}

	/**
	 * @inheritdoc
	 */
	protected function wpsolr_custom_prepare( $query, $args ) {

		// Extract the args for later
		$this->wpsolr_latitude  = $args[1];
		$this->wpsolr_longitude = $args[2];
		$this->wpsolr_radius    = $args[4];

		return '';
	}

	/**
	 * @inheritdoc
	 */
	protected function wpsolr_custom_get_results( $query = null, $output = OBJECT ) {

		// Prevent executing SQL by returning empty (but no null) results
		return [];
	}
}