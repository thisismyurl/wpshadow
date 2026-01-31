<?php
/**
 * Plugin Memory Usage Diagnostic
 *
 * Detects plugins consuming excessive memory.
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
 * Diagnostic_Plugin_Memory_Usage Class
 *
 * Identifies plugins that may be consuming excessive memory.
 */
class Diagnostic_Plugin_Memory_Usage extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-memory-usage';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Memory Usage';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins that may consume excessive memory';

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
		$memory_concerns = array();

		// Get memory limits
		$memory_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$memory_limit_admin = WP_MEMORY_LIMIT;
		if ( defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT !== '40M' ) {
			$memory_limit_admin = WP_MEMORY_LIMIT;
		}

		if ( $memory_limit < 67108864 ) { // 64MB
			$memory_concerns[] = sprintf(
				/* translators: %s: memory limit */
				__( 'WordPress memory limit set to %s. Plugins will struggle with large datasets.', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for plugins known to be memory-heavy
		$active_plugins = get_option( 'active_plugins', array() );

		$memory_heavy = array(
			'elementor' => 'Elementor',
			'woocommerce' => 'WooCommerce',
			'divi' => 'Divi',
			'wp-super-cache' => 'WP Super Cache',
			'akismet' => 'Akismet',
		);

		$heavy_plugins = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $memory_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$heavy_plugins[] = $name;
				}
			}
		}

		if ( ! empty( $heavy_plugins ) && $memory_limit < 134217728 ) { // 128MB
			$memory_concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Memory-heavy plugins (%s) active but memory limit is only %s. Consider increasing to 256MB+.', 'wpshadow' ),
				implode( ', ', $heavy_plugins ),
				size_format( $memory_limit )
			);
		}

		// Check for many large transients
		global $wpdb;
		$large_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '%transient%'
			AND LENGTH(option_value) > 1048576"
		);

		if ( $large_transients > 0 ) {
			$memory_concerns[] = sprintf(
				/* translators: %d: count of large transients */
				__( '%d large transients (>1MB) in cache. These consume memory.', 'wpshadow' ),
				$large_transients
			);
		}

		if ( ! empty( $memory_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $memory_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'memory_limit'          => $memory_limit,
					'memory_heavy_plugins'  => $heavy_plugins,
					'large_transients_count' => $large_transients ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-memory-usage',
			);
		}

		return null;
	}
}
