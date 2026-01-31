<?php
/**
 * Plugin API Request Performance Diagnostic
 *
 * Detects plugins making excessive external API requests.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_API_Request_Performance Class
 *
 * Identifies plugins making too many external API calls.
 */
class Diagnostic_Plugin_API_Request_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-api-request-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin API Request Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins making excessive external API requests';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$api_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for plugins known to make frequent API calls
		$api_heavy = array(
			'jetpack' => 'Jetpack',
			'akismet' => 'Akismet',
			'yoast-seo' => 'Yoast SEO',
			'google-analytics' => 'Google Analytics',
			'woocommerce' => 'WooCommerce',
		);

		$api_heavy_active = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $api_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$api_heavy_active[] = $name;
				}
			}
		}

		if ( ! empty( $api_heavy_active ) ) {
			$api_concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'API-heavy plugins active: %s. These make external requests on every page load.', 'wpshadow' ),
				implode( ', ', $api_heavy_active )
			);
		}

		// Check if caching plugins are active (should be)
		$cache_plugins = array(
			'wp-super-cache',
			'w3-total-cache',
			'wp-fastest-cache',
			'wp-rocket',
		);

		$cache_active = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $cache_plugins as $cache_key ) {
				if ( strpos( $plugin, $cache_key ) !== false ) {
					$cache_active = true;
					break;
				}
			}
		}

		if ( ! empty( $api_heavy_active ) && ! $cache_active ) {
			$api_concerns[] = __( 'API-heavy plugins active but no caching plugin detected. API calls will block every page load.', 'wpshadow' );
		}

		// Check for HTTPS everywhere (cURL performance)
		global $wpdb;
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options} 
			WHERE option_value LIKE '%https://api%' 
			LIMIT 5"
		);

		if ( ! empty( $options ) && empty( $cache_active ) ) {
			$api_concerns[] = __( 'HTTPS API endpoints detected. Without caching, HTTPS handshake adds 500ms+ latency per request.', 'wpshadow' );
		}

		if ( ! empty( $api_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $api_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'api_heavy_plugins' => $api_heavy_active,
					'cache_plugin_active' => $cache_active,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-api-request-performance',
			);
		}

		return null;
	}
}
