<?php
/**
 * Shared GitHub release updater.
 *
 * @package TIMU_GitHub_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TIMU_GitHub_Release_Updater' ) ) {
	/**
	 * Register update checks against GitHub releases.
	 */
	class TIMU_GitHub_Release_Updater {
		/**
		 * Plugin file path.
		 *
		 * @var string
		 */
		private $plugin_file;

		/**
		 * Plugin basename.
		 *
		 * @var string
		 */
		private $plugin_basename;

		/**
		 * Plugin slug.
		 *
		 * @var string
		 */
		private $slug;

		/**
		 * GitHub repository owner/name.
		 *
		 * @var string
		 */
		private $repo;

		/**
		 * Create updater instance.
		 *
		 * @param array<string, string> $config Updater config.
		 */
		public function __construct( $config ) {
			$this->plugin_file     = (string) ( $config['plugin_file'] ?? '' );
			$this->plugin_basename = plugin_basename( $this->plugin_file );
			$this->slug            = sanitize_key( (string) ( $config['slug'] ?? '' ) );
			$this->repo            = trim( (string) ( $config['repo'] ?? '' ) );

			if ( '' === $this->plugin_file || '' === $this->slug || '' === $this->repo ) {
				return;
			}

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
			add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
		}

		/**
		 * Normalize Git tag versions for version_compare.
		 *
		 * @param string $version Raw version.
		 * @return string
		 */
		private function normalize_version( $version ) {
			$version = trim( (string) $version );
			$version = ltrim( $version, "vV \t\n\r\0\x0B" );

			return $version;
		}

		/**
		 * Return release metadata from GitHub API.
		 *
		 * @return array<string, string>|false
		 */
		private function get_release_data() {
			$cache_key = 'timu_gh_release_' . md5( $this->repo );
			$cached    = get_site_transient( $cache_key );
			if ( is_array( $cached ) && ! empty( $cached['version'] ) && ! empty( $cached['zipball_url'] ) ) {
				return $cached;
			}

			$api_url  = 'https://api.github.com/repos/' . $this->repo . '/releases/latest';
			$response = wp_remote_get(
				$api_url,
				array(
					'timeout' => 15,
					'headers' => array(
						'Accept'     => 'application/vnd.github+json',
						'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$status_code = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 !== $status_code ) {
				return false;
			}

			$body    = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			$tag     = isset( $body['tag_name'] ) ? $this->normalize_version( (string) $body['tag_name'] ) : '';
			$zip_url = isset( $body['zipball_url'] ) ? (string) $body['zipball_url'] : '';

			if ( '' === $tag || '' === $zip_url ) {
				return false;
			}

			$release = array(
				'version'      => $tag,
				'zipball_url'  => $zip_url,
				'html_url'     => isset( $body['html_url'] ) ? (string) $body['html_url'] : 'https://github.com/' . $this->repo,
				'published_at' => isset( $body['published_at'] ) ? (string) $body['published_at'] : '',
				'body'         => isset( $body['body'] ) ? (string) $body['body'] : '',
			);

			set_site_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );

			return $release;
		}

		/**
		 * Inject update metadata into core update transient.
		 *
		 * @param object $transient Update transient.
		 * @return object
		 */
		public function check_for_updates( $transient ) {
			if ( ! is_object( $transient ) || empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
				return $transient;
			}

			$current_version = isset( $transient->checked[ $this->plugin_basename ] ) ? $this->normalize_version( (string) $transient->checked[ $this->plugin_basename ] ) : '';
			if ( '' === $current_version ) {
				return $transient;
			}

			$release = $this->get_release_data();
			if ( ! is_array( $release ) ) {
				return $transient;
			}

			if ( version_compare( $release['version'], $current_version, '>' ) ) {
				$update              = new stdClass();
				$update->slug        = $this->slug;
				$update->plugin      = $this->plugin_basename;
				$update->new_version = $release['version'];
				$update->url         = $release['html_url'];
				$update->package     = $release['zipball_url'];

				$transient->response[ $this->plugin_basename ] = $update;
			}

			return $transient;
		}

		/**
		 * Render plugin information popup.
		 *
		 * @param false|object|array<string, mixed> $result Existing result.
		 * @param string                             $action Action type.
		 * @param object                             $args   Request args.
		 * @return false|object|array<string, mixed>
		 */
		public function plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $this->slug !== $args->slug ) {
				return $result;
			}

			$release = $this->get_release_data();
			if ( ! is_array( $release ) ) {
				return $result;
			}

			$info              = new stdClass();
			$info->name        = ucfirst( str_replace( '-', ' ', $this->slug ) );
			$info->slug        = $this->slug;
			$info->version     = $release['version'];
			$info->author      = '<a href="https://github.com/' . esc_attr( strtok( $this->repo, '/' ) ) . '">' . esc_html( strtok( $this->repo, '/' ) ) . '</a>';
			$info->homepage    = $release['html_url'];
			$info->download_link = $release['zipball_url'];
			$info->last_updated  = $release['published_at'];
			$info->sections      = array(
				'description' => wp_kses_post( wpautop( $release['body'] ) ),
				'changelog'   => wp_kses_post( wpautop( $release['body'] ) ),
			);

			return $info;
		}

		/**
		 * Keep installed folder stable after GitHub zip extraction.
		 *
		 * @param array<string, mixed> $response   Installer response.
		 * @param array<string, mixed> $hook_extra Extra context.
		 * @param array<string, mixed> $result     Install result.
		 * @return array<string, mixed>
		 */
		public function after_install( $response, $hook_extra, $result ) {
			if ( empty( $hook_extra['plugin'] ) || $this->plugin_basename !== $hook_extra['plugin'] ) {
				return $result;
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem || empty( $result['destination'] ) ) {
				return $result;
			}

			$install_directory = plugin_dir_path( $this->plugin_file );
			if ( $wp_filesystem->exists( $install_directory ) ) {
				$wp_filesystem->delete( $install_directory, true );
			}

			if ( $wp_filesystem->move( $result['destination'], $install_directory, true ) ) {
				$result['destination'] = $install_directory;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $this->plugin_basename ) ) {
				activate_plugin( $this->plugin_basename );
			}

			return $result;
		}
	}
}

if ( ! function_exists( 'timu_boot_github_release_updater' ) ) {
	/**
	 * Bootstrap shared GitHub updater for a plugin.
	 *
	 * @param array<string, string> $config Updater config.
	 * @return void
	 */
	function timu_boot_github_release_updater( $config ) {
		static $instances = array();

		$plugin_file = isset( $config['plugin_file'] ) ? (string) $config['plugin_file'] : '';
		$key         = plugin_basename( $plugin_file );
		if ( '' === $key || isset( $instances[ $key ] ) ) {
			return;
		}

		$instances[ $key ] = new TIMU_GitHub_Release_Updater( $config );
	}
}
