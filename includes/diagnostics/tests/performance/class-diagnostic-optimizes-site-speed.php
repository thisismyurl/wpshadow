<?php
/**
 * Speed Optimization Program Diagnostic
 *
 * Tests for active speed optimization efforts.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Speed Optimization Program Diagnostic Class
 *
 * Verifies that performance tools or workflows are in place.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Optimizes_Site_Speed extends Diagnostic_Base {

	protected static $slug = 'optimizes-site-speed';
	protected static $title = 'Speed Optimization Program';
	protected static $description = 'Tests for active speed optimization efforts';
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$performance_plugins = array(
			'autoptimize/autoptimize.php',
			'wp-rocket/wp-rocket.php',
			'litespeed-cache/litespeed-cache.php',
			'perfmatters/perfmatters.php',
			'asset-cleanup/asset-cleanup.php',
		);

		foreach ( $performance_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_speed_optimization_program' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No speed optimization program detected. Use performance tools or audits to keep pages fast.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/speed-optimization-program',
			'persona'      => 'publisher',
		);
	}
}
