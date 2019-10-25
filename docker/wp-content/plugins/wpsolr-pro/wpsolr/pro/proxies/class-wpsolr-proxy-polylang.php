<?php

namespace wpsolr\pro\proxies;

/**
 * Class WPSOLR_Proxy_Polylang
 * @package wpsolr\pro\proxies
 */
trait WPSOLR_Proxy_Polylang {

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function pll__( $string ) {

		return pll__( $string );
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	public function pll_translate_string( $string, $language ) {

		return pll_translate_string( $string, $language );
	}

	/**
	 * @param string $name a unique name for the string
	 * @param string $string the string to register
	 * @param string $context optional the group in which the string is registered, defaults to 'polylang'
	 * @param bool $multiline optional wether the string table should display a multiline textarea or a single line input, defaults to single line
	 *
	 */
	public function pll_register_string( $name, $string, $context = 'polylang', $multiline = false ) {

		return pll_register_string( $name, $string, $context, $multiline );
	}

	/**
	 * Returns the post language
	 *
	 * @param int $post_id
	 * @param string $field optional the language field to return 'name', 'locale', defaults to 'slug'
	 *
	 * @return bool|string the requested field for the post language, false if no language is associated to that post
	 */
	public function pll_get_post_language( $post_id, $field = 'slug' ) {
		return pll_get_post_language( $post_id, $field );
	}

	/**
	 * Returns the list of available languages
	 *
	 * @param array $args list of parameters
	 *
	 * @return array
	 */
	public function pll_languages_list( $args = [] ) {
		return pll_languages_list( $args );
	}

	/**
	 * Among the post and its translations, returns the id of the post which is in the language represented by $slug
	 *
	 * @param int $post_id post id
	 * @param string $slug optional language code, defaults to current language
	 *
	 * @return int|false|null post id of the translation if exists, false otherwise, null if the current language is not defined yet
	 */
	public function pll_get_post( $post_id, $slug = '' ) {
		return pll_get_post( $post_id, $slug );
	}

	/**
	 * Save posts translations
	 *
	 * @param array $arr an associative array of translations with language code as key and post id as value
	 */
	public function pll_save_post_translations( $arr ) {
		pll_save_post_translations( $arr );
	}


	/**
	 * Returns the current language on frontend
	 * Returns the language set in admin language filter on backend ( false if set to all languages )
	 *
	 * @param string $field optional the language field to return 'name', 'locale', defaults to 'slug'
	 *
	 * @return string|bool the requested field for the current language
	 */
	function pll_current_language( $field = 'slug' ) {
		return pll_current_language( $field );
	}

	/**
	 * Returns the default language
	 *
	 * @param string $field optional the language field to return 'name', 'locale', defaults to 'slug'
	 *
	 * @return string the requested field for the default language
	 */
	function pll_default_language( $field = 'slug' ) {
		return pll_default_language( $field );
	}

}