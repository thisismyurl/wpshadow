<?php
/**
 * Module Hub Initializer - Consolidates repetitive hub initialization patterns
 *
 * Provides DRY initialization for all hub modules (Media, Vault, etc).
 * Eliminates duplicate code for constants, registration, menu setup, and more.
 *
 * @package wp_support_SUPPORT
 * @since 1.2602.10010
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Hub Initializer
 *
 * Reusable hub initialization logic to eliminate code duplication.
 */
class WPS_Module_Hub_Initializer {

	/**
	 * Register a hub module with Core.
	 *
	 * @param array $hub_config {
	 *     @type string $slug         Hub slug (e.g., 'media-support-thisismyurl')
	 *     @type string $name         Display name
	 *     @type string $suite        Suite name (e.g., 'media', 'storage')
	 *     @type string $description  Hub description
	 *     @type string $version      Hub version
	 *     @type string $path         Hub directory path (ending with /)
	 *     @type string $url          Hub URL (ending with /)
	 *     @type string $basename     Hub basename
	 *     @type array  $capabilities Hub capabilities array
	 *     @type string $text_domain  Textdomain for translations
	 * }
	 * @return void
	 */
	public static function register_hub_module( array $hub_config ): void {
		// Validate required keys.
		$required_keys = array( 'slug', 'name', 'suite', 'version', 'path', 'url', 'basename' );
		foreach ( $required_keys as $key ) {
			if ( empty( $hub_config[ $key ] ) ) {
				error_log( "WPS Hub Initializer: Missing required key '$key' in hub config" );
				return;
			}
		}

		$text_domain = $hub_config['text_domain'] ?? 'plugin-wp-support-thisismyurl';

		// Register via action for Core to pick up.
		do_action(
			'WPS_register_module',
			array(
				'slug'         => $hub_config['slug'],
				'name'         => $hub_config['name'],
				'type'         => 'hub',
				'suite'        => $hub_config['suite'],
				'version'      => $hub_config['version'],
				'description'  => $hub_config['description'] ?? '',
				'capabilities' => $hub_config['capabilities'] ?? array(),
				'path'         => $hub_config['path'],
				'url'          => $hub_config['url'],
				'basename'     => $hub_config['basename'],
			)
		);
	}

	/**
	 * Register a hub feature with Core.
	 *
	 * @param string $feature_slug Hub slug (used as feature ID).
	 * @param array  $feature_config {
	 *     @type string $name        Feature display name
	 *     @type string $description Feature description
	 *     @type string $version     Feature version
	 * }
	 * @return void
	 */
	public static function register_hub_feature( string $feature_slug, array $feature_config ): void {
		if ( ! function_exists( '\\WPS\\CoreSupport\\register_WPS_feature' ) ) {
			return;
		}

		register_WPS_feature(
			$feature_slug,
			array(
				'plugin'      => $feature_slug,
				'name'        => $feature_config['name'] ?? $feature_slug,
				'description' => $feature_config['description'] ?? '',
				'version'     => $feature_config['version'] ?? '1.0.0',
			)
		);
	}

	/**
	 * Initialize hub module constants in a standard way.
	 *
	 * Defines VERSION, PATH, URL, BASENAME, TEXT_DOMAIN, MIN_PHP, MIN_WP
	 * without repetition across modules.
	 *
	 * @param string $module_file    The __FILE__ constant from module.php
	 * @param string $slug           Hub slug (e.g., 'media-support-thisismyurl')
	 * @param string $text_domain    Text domain for translations
	 * @param string $version        Module version
	 * @param string $min_php        Minimum PHP version
	 * @param string $min_wp         Minimum WordPress version
	 * @return array Array of defined constants for reference
	 */
	public static function define_module_constants(
		string $module_file,
		string $slug,
		string $text_domain,
		string $version,
		string $min_php = '8.1.29',
		string $min_wp = '6.4.0',
		?string $constant_prefix = null
	): array {
		$computed_prefix = strtoupper( str_replace( array( '-', '/' ), '_', $slug ) );
		$primary_prefix  = $constant_prefix ? strtoupper( $constant_prefix ) : $computed_prefix;

		$build_constants = static function ( string $prefix ) use ( $module_file, $text_domain, $version, $min_php, $min_wp ): array {
			return array(
				"{$prefix}_VERSION"     => $version,
				"{$prefix}_FILE"        => $module_file,
				"{$prefix}_PATH"        => plugin_dir_path( $module_file ),
				"{$prefix}_URL"         => plugin_dir_url( $module_file ),
				"{$prefix}_BASENAME"    => plugin_basename( $module_file ),
				"{$prefix}_TEXT_DOMAIN" => $text_domain,
				"{$prefix}_MIN_PHP"     => $min_php,
				"{$prefix}_MIN_WP"      => $min_wp,
			);
		};

		// Always define the primary prefix constants; optionally also define computed prefix for backward compatibility.
		$constants = $build_constants( $primary_prefix );

		if ( $primary_prefix !== $computed_prefix ) {
			$constants = array_merge( $constants, $build_constants( $computed_prefix ) );
		}

		// Define each constant if not already defined.
		foreach ( $constants as $const_name => $const_value ) {
			if ( ! defined( $const_name ) ) {
				define( $const_name, $const_value );
			}
		}

		return $constants;
	}

	/**
	 * Load hub text domain for translations.
	 *
	 * @param string $text_domain  Text domain
	 * @param string $plugin_path  Plugin directory path
	 * @return void
	 */
	public static function load_hub_textdomain( string $text_domain, string $plugin_path ): void {
		load_plugin_textdomain(
			$text_domain,
			false,
			dirname( plugin_basename( $plugin_path . 'module.php' ) ) . '/languages'
		);
	}

	/**
	 * Check if Core is available and display notice if not.
	 *
	 * @param string $hub_name    Display name of hub (e.g., 'Media Support')
	 * @param string $text_domain Text domain for translation
	 * @return bool True if Core is available, false otherwise
	 */
	public static function check_core_availability( string $hub_name, string $text_domain ): bool {
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Module_Registry' ) ) {
			return true;
		}

		// Display notice if Core is missing.
		add_action(
			'admin_notices',
			static function () use ( $hub_name, $text_domain ): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html(
						sprintf(
						/* translators: %s: Hub name */
							__( '%s requires WP Support to be installed and active.', $text_domain ),
							$hub_name
						)
					)
				);
			}
		);

		return false;
	}

	/**
	 * Get module catalog for a hub's dependent spokes.
	 *
	 * @param string $hub_slug Hub slug
	 * @return array Array of module data for spokes
	 */
	public static function get_hub_spoke_modules( string $hub_slug ): array {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Module_Registry' ) ) {
			return array();
		}

		$catalog = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
		return array_filter(
			$catalog,
			static function ( $m ) use ( $hub_slug ) {
				return ( $m['requires_hub'] ?? '' ) === $hub_slug;
			}
		);
	}

	/**
	 * Get activity logs if Vault is available.
	 *
	 * @param int $limit Limit results to this many
	 * @return array Activity log entries
	 */
	public static function get_vault_activity_logs( int $limit = 10 ): array {
		if ( ! class_exists( '\\WPS\\VaultSupport\\WPS_Vault' ) ) {
			return array();
		}

		return \WPS\VaultSupport\WPS_Vault::get_logs( 0, $limit );
	}
}
