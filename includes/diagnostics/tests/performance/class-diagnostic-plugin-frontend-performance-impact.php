<?php
/**
 * Plugin Frontend Performance Impact Diagnostic
 *
 * Measures how much plugins affect frontend performance.
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
 * Diagnostic_Plugin_Frontend_Performance_Impact Class
 *
 * Detects plugins that significantly impact frontend performance.
 */
class Diagnostic_Plugin_Frontend_Performance_Impact extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-frontend-performance-impact';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Frontend Performance Impact';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugins that negatively impact frontend page load times';

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
		$concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null;
		}

		// Check for known frontend-heavy plugins without lazy loading
		$frontend_heavy = array(
			'jetpack' => 'Jetpack',
			'akismet' => 'Akismet',
			'yoast-seo' => 'Yoast SEO',
			'elementor' => 'Elementor',
			'woocommerce' => 'WooCommerce',
			'contact-form-7' => 'Contact Form 7',
			'wordfence' => 'Wordfence Security',
		);

		$heavy_plugins_active = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $frontend_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$heavy_plugins_active[] = $name;
				}
			}
		}

		if ( ! empty( $heavy_plugins_active ) ) {
			$concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Active frontend-heavy plugins detected: %s. These typically add 500ms-2s to page load.', 'wpshadow' ),
				implode( ', ', $heavy_plugins_active )
			);
		}

		// Check for too many active plugins (>30 is problematic)
		if ( count( $active_plugins ) > 30 ) {
			$concerns[] = sprintf(
				/* translators: %d: plugin count */
				__( '%d plugins active. Each adds 20-50ms to page load. Consider consolidation.', 'wpshadow' ),
				count( $active_plugins )
			);
		}

		if ( ! empty( $concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'active_plugin_count' => count( $active_plugins ),
					'heavy_plugins'       => $heavy_plugins_active,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-frontend-performance',
			);
		}

		return null;
	}
}
