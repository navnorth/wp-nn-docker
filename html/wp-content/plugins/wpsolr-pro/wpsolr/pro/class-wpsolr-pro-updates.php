<?php

namespace wpsolr\pro;

use wpsol\pro\includes\readmetxt\WPSOLRPro_Readme_Txt;
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\managed_solr_servers\OptionManagedSolrServer;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;

if ( ! class_exists( WPSOLR_Pro_Updates::class ) ) {

	/**
	 * Class WPSOLR_Pro_Updates
	 * @package wpsolr\pro
	 */
	class WPSOLR_Pro_Updates {

		const ADMIN_PAGE_EXTENSIONS = 'admin.php?page=solr_settings&tab=solr_plugins';
		const REMOTE_ERROR_MESSAGE = '<br/>To receive automatic updates license activation is required. Please register or buy %s to activate your WPSOLR PRO.';
		const LICENSE_IS_NOT_REGISTERED = '%s license is not registered.';

		protected $remote_error;
		const WPSOLR_PLUGIN_PRO_UPGRADE_URL = 'https://api.wpsolr.com/v1/providers/8c25d2d6-54ae-4ff6-a478-e2c03f1e08a4/accounts/24b7729e-02dc-47d1-9c15-f1310098f93f/addons/a23c3faf-0b31-4b76-b1ea-f5daa0f63a9e/update-manager/e6b0af84-5697-4805-a522-65b538dcdf11/licenses';
		protected $managed_solr_server;

		/**
		 * @var object $remote_plugin_info Release information from the plugin remote repository
		 * */
		private $remote_plugin_info;

		private $plugin_slug; // plugin base name
		private $plugin_data; // Remote plugin data
		private $plugin_file; // __FILE__ of our plugin

		/**
		 * Constructor.
		 *
		 * @param $plugin_file
		 */
		function __construct( $plugin_slug, $plugin_file ) {

			$this->managed_solr_server = new OptionManagedSolrServer();

			$this->plugin_slug = $plugin_slug;
			$this->plugin_file = $plugin_file;
			/*$transient                                       = get_site_transient( 'update_plugins' );
					$transient->checked['wpsolr-pro/wpsolr-pro.php'] = '15.0';
					set_site_transient( 'update_plugins', $transient );*/

			add_filter( 'pre_set_site_transient_update_plugins', [
					$this,
					'set_transient',
				]
			);

			add_filter( sprintf( 'plugin_action_links_%s', $this->plugin_slug ), [
					$this,
					'plugin_action_links',
				]
			);

			// https://developer.wordpress.org/reference/hooks/in_plugin_update_message-file/
			add_action( sprintf( 'in_plugin_update_message-%s', $this->plugin_slug ), [
				$this,
				'write_plugin_update_error_message',
			], 10, 2 );

			add_filter( 'plugins_api', [ $this, 'get_remote_readme_txt' ], 10, 3 );

		}


		/**
		 * Plugin description displayed in plugin version details link on plugins page
		 *
		 * @param bool $false
		 * @param array $action
		 * @param object $arg
		 *
		 * @return bool|object
		 */
		public function get_remote_readme_txt( $false, $action, $arg ) {

			if ( ! ( ( 'plugin_information' === $action ) && isset( $arg->slug ) && ( $arg->slug === $this->plugin_slug ) ) ) {
				return false;
			}

			// Get remote readme.txt
			try {
				$response_object = $this->get_remote_plugin_infos( 'description' );
			} catch ( \Exception $e ) {
				return new \WP_Error( 'WPSOLR_PRO_UNEXPECTED', $e->getMessage() );
			}

			if ( isset( $response_object ) && OptionManagedSolrServer::is_response_ok( $response_object ) ) {

				include_once( 'includes/readmetxt/class-wpsolrpro-readme-txt.php' );

				$readme_txt_content = OptionManagedSolrServer::get_response_result( $response_object, 'description' );

				$readme_parser = new WPSOLRPro_Readme_Txt();
				$info          = $readme_parser->parse_readme_contents( $readme_txt_content );

				$info['slug'] = $this->plugin_slug;

				return (object) $info;

			} else {

				return new \WP_Error( 'WPSOLR_PRO_UNEXPECTED', OptionManagedSolrServer::get_response_error_message( $response_object ) );
			}

		}

		/**
		 * Get plugin information from WordPress
		 */
		private function init_plugin_data() {
			$this->plugin_data = get_plugin_data( $this->plugin_file );
		}

		/**
		 * Get plugin download url from remote repository
		 */
		private function get_remote_plugin_download_url() {

			// Only do this once
			if ( ! empty( $this->remote_plugin_info ) ) {
				return;
			}

			// Get download url
			try {
				$response_object = $this->get_remote_plugin_infos( 'url' );
			} catch ( \Exception $e ) {
				$this->remote_error = $e->getMessage();

				return;
			}

			if ( isset( $response_object ) && OptionManagedSolrServer::is_response_ok( $response_object ) ) {
				// Set remote plugin info.
				$this->remote_plugin_info = (object) [
					'new_version' => OptionManagedSolrServer::get_response_result( $response_object, 'version' ),
					'package'     => apply_filters( WPSOLR_Events::WPSOLR_FILTER_ENV_PACKAGE_URL, OptionManagedSolrServer::get_response_result( $response_object, 'fileUrl' ) ),
				];

				// Erase last update error
				$this->erase_last_update_error();

			} else {
				$this->remote_error = OptionManagedSolrServer::get_response_error_message( $response_object );

				// Save the last update error to show permanently on plugins update page
				$this->save_last_update_error( $this->remote_error );
			}

			return;
		}

		/**
		 * Get plugin infos from remote repository
		 *
		 * @param $action 'url' or 'description'
		 *
		 * @return array
		 * @throws \Exception
		 */
		private function get_remote_plugin_infos( $action ) {

			$license_manager = new OptionLicenses();

			$license_activation_uuid = $license_manager->get_any_license();
			if ( empty( $license_activation_uuid ) ) {
				throw new \Exception( sprintf( self::LICENSE_IS_NOT_REGISTERED, WPSOLR_PLUGIN_SHORT_NAME ) );
			}

			$response_object = $this->managed_solr_server->call_rest_post(
				$this->get_update_api_url() . '/' . $license_activation_uuid,
				[
					'version' => WPSOLR_PLUGIN_VERSION,
					'siteUrl' => admin_url(),
					'action'  => $action,
				]
			);

			return $response_object;
		}

		/**
		 * Clear last update errors
		 */
		public function erase_last_update_error() {

			// Erase previous errors
			$last_update_error = WPSOLR_Service_Container::getOption()->get_update_last_error();
			if ( ! empty( $last_update_error ) ) {
				$updates = WPSOLR_Service_Container::getOption()->get_option_updates();
				unset( $updates[ WPSOLR_Option::OPTION_UPDATES_LAST_ERROR ] );
				update_option( WPSOLR_Option::OPTION_UPDATES, $updates );
			}

		}

		/**
		 * Set last update error
		 *
		 * @param string $last_update_error
		 */
		public function save_last_update_error( $last_update_error ) {

			if ( ! empty( $last_update_error ) ) {
				update_option( WPSOLR_Option::OPTION_UPDATES, [ WPSOLR_Option::OPTION_UPDATES_LAST_ERROR => $last_update_error ] );
			}

		}

		/**
		 * Get update api url
		 */
		public function get_update_api_url() {
			return apply_filters( WPSOLR_Events::WPSOLR_FILTER_ENV_CHECK_UPGRADE_VERSION_URL, self::WPSOLR_PLUGIN_PRO_UPGRADE_URL );
		}

		/**
		 * Push in plugin version information to get the update notification
		 *
		 * @param $transient
		 *
		 * @return mixed
		 */
		public function set_transient( $transient ) {

			// If we have checked the plugin data before, don't re-check
			if ( empty( $transient->checked ) || ! isset( $transient->checked[ $this->plugin_slug ] ) ) {
				return $transient;
			}

			// Next, we will get the plugin information that we are going to use:
			// Get plugin & GitHub release information
			$this->init_plugin_data();
			$this->get_remote_plugin_download_url();

			if ( ! isset( $this->remote_error ) ) {
				// Check the versions if we need to do an update
				$is_update = version_compare( $this->remote_plugin_info->new_version, $transient->checked[ $this->plugin_slug ] );

				if ( ( 1 === $is_update ) && ! empty( $this->remote_plugin_info->package ) ) {
					// Update the transient to include our updated remote plugin data

					$obj                                       = new \stdClass();
					$obj->slug                                 = $this->plugin_slug;
					$obj->new_version                          = $this->remote_plugin_info->new_version;
					$obj->url                                  = $this->plugin_data['PluginURI'];
					$obj->package                              = $this->remote_plugin_info->package;
					$transient->response[ $this->plugin_slug ] = $obj;
				}
			} else {

				$obj                                       = new \stdClass();
				$obj->slug                                 = $this->plugin_slug;
				$obj->new_version                          = $transient->checked[ $this->plugin_slug ];
				$obj->url                                  = $this->plugin_data['PluginURI'];
				$obj->package                              = ''; // Empty to replace message 'There is a new version ...' by 'Automatic update is unavailable for this plugin.'
				$transient->response[ $this->plugin_slug ] = $obj;

			}

			return $transient;
		}

		/**
		 * Push in plugin version information to display in the details lightbox
		 *
		 * @param $false
		 * @param $action
		 * @param $response
		 *
		 * @return mixed
		 */
		public function set_plugin_info( $false, $action, $response ) {
			// code ehre
			return $response;
		}

		/**
		 * Post install our plugin
		 *
		 * @param $true
		 * @param $hook_extra
		 * @param $result
		 *
		 * @return mixed
		 */
		public function post_install( $true, $hook_extra, $result ) {
			// code here
			return $result;
		}

		/**
		 * Add links the "Plugin" column on the Plugins page.
		 *
		 * @param array $links
		 *
		 * @return array
		 */
		public function plugin_action_links( array $links ) {

			return $links;
		}

		/**
		 * Show error message on plugin page.
		 *
		 * @param array $plugin_data
		 * @param array $response
		 */
		public function write_plugin_update_error_message( $plugin_data, $response ) {

			$last_update_error = WPSOLR_Service_Container::getOption()->get_update_last_error();
			if ( ! empty( $last_update_error ) ) {
				$url      = admin_url( self::ADMIN_PAGE_EXTENSIONS );
				$redirect = sprintf( '<a href="%s" target="_blank">%s</a>', $url, 'extensions' );

				echo '<div class="wc_plugin_upgrade_notice">';
				echo sprintf( '%s', $last_update_error );
				echo sprintf( self::REMOTE_ERROR_MESSAGE, $redirect );
				echo '</div>';
			}
		}

		public function activate() {

			$test = WPSOLR_PLUGIN_BASE_NAME;
			//deactivate_plugins( plugin_basename( __FILE__ ) );
		}

	}

}