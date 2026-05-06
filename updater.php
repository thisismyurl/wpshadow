<?php
/**
 * GitHub Releases Updater — thisismyurl.com plugins.
 *
 * MIGRATION PATH TO WORDPRESS.ORG
 * --------------------------------
 * When this plugin moves to WordPress.org:
 *   1. Delete this file (updater.php).
 *   2. Remove the `require_once __DIR__ . '/updater.php'` call from the main
 *      plugin file (and the surrounding `add_action( 'plugins_loaded', ... )`
 *      block if the updater is the only thing in it).
 *   3. Remove the "GitHub Plugin URI", "Primary Branch", and "Update URI"
 *      plugin-header lines from the main plugin file's docblock.
 *   4. Submit to WordPress.org. WP's native .org update mechanism takes over
 *      automatically — no database cleanup required.
 *
 * @since 1.260506
 */

if ( ! class_exists( 'TIMU_GitHub_Updater' ) ) {

	class TIMU_GitHub_Updater {

		/** @var array */
		private $config;

		/** @var object|false */
		private $github_data = false;

		/**
		 * @param array $config {
		 *   @type string slug               Plugin directory name (no trailing slash).
		 *   @type string proper_folder_name Plugin directory name (same as slug).
		 *   @type string api_url            GitHub Releases API latest endpoint.
		 *   @type string github_url         GitHub repo URL (displayed in WP admin).
		 *   @type string plugin_file        Absolute path to the main plugin file.
		 * }
		 */
		public function __construct( array $config ) {
			$this->config = $config;

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
			add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
		}

		private function get_github_data() {
			if ( false !== $this->github_data ) {
				return $this->github_data;
			}

			$response = wp_remote_get(
				$this->config['api_url'],
				array(
					'timeout' => 10,
					'headers' => array( 'Accept' => 'application/vnd.github+json' ),
				)
			);

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( empty( $body->tag_name ) ) {
				return false;
			}

			$this->github_data = $body;
			return $this->github_data;
		}

		public function check_update( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$plugin_slug = plugin_basename( $this->config['plugin_file'] );
			if ( ! isset( $transient->checked[ $plugin_slug ] ) ) {
				return $transient;
			}

			$github_data    = $this->get_github_data();
			$remote_version = $github_data ? ltrim( (string) $github_data->tag_name, 'v' ) : '';

			if ( $github_data && version_compare( $remote_version, $transient->checked[ $plugin_slug ], '>' ) ) {
				$obj              = new stdClass();
				$obj->slug        = $this->config['slug'];
				$obj->new_version = $remote_version;
				$obj->url         = $this->config['github_url'];
				$obj->package     = $this->get_download_url( $github_data );

				$transient->response[ $plugin_slug ] = $obj;
			}

			return $transient;
		}

		public function plugin_popup( $result, $action, $args ) {
			if ( 'plugin_information' !== $action || $args->slug !== $this->config['slug'] ) {
				return $result;
			}

			$github_data = $this->get_github_data();
			if ( ! $github_data ) {
				return $result;
			}

			$obj                = new stdClass();
			$obj->name          = $this->config['proper_folder_name'];
			$obj->slug          = $this->config['slug'];
			$obj->version       = ltrim( (string) $github_data->tag_name, 'v' );
			$obj->author        = '<a href="https://thisismyurl.com/">thisismyurl.com</a>';
			$obj->homepage      = $this->config['github_url'];
			$obj->last_updated  = isset( $github_data->published_at ) ? $github_data->published_at : '';
			$obj->sections      = array(
				'description' => isset( $github_data->body ) ? wp_kses_post( (string) $github_data->body ) : '',
			);
			$obj->download_link = $this->get_download_url( $github_data );

			return $obj;
		}

		public function after_install( $response, $hook_extra, $result ) {
			global $wp_filesystem;

			// Only handle this plugin's own update, not other plugins'.
			if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== plugin_basename( $this->config['plugin_file'] ) ) {
				return $result;
			}

			$install_directory = plugin_dir_path( $this->config['plugin_file'] );
			$wp_filesystem->move( $result['destination'], $install_directory );
			$result['destination'] = $install_directory;

			return $result;
		}

		private function get_download_url( $github_data ) {
			if ( ! empty( $github_data->assets ) ) {
				foreach ( $github_data->assets as $asset ) {
					if ( isset( $asset->browser_download_url ) &&
					     '.zip' === substr( (string) $asset->browser_download_url, -4 ) ) {
						return $asset->browser_download_url;
					}
				}
			}
			return isset( $github_data->zipball_url ) ? (string) $github_data->zipball_url : '';
		}
	}
}
