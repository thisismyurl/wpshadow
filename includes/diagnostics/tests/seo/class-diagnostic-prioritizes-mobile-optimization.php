<?php
/**
 * Mobile Optimization Priority Diagnostic
 *
 * Tests if mobile-first approach is evident.
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
 * Mobile Optimization Priority Diagnostic Class
 *
 * Verifies mobile optimization is prioritized through responsive theme
 * support and mobile enhancement plugins.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Prioritizes_Mobile_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'prioritizes-mobile-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Optimization Priority';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if mobile-first approach is evident';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

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

		$mobile_plugins = array(
			'amp/amp.php',
			'wptouch/wptouch.php',
			'mobile-detect/mobile-detect.php',
		);

		foreach ( $mobile_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$theme_supports_responsive = (bool) get_theme_support( 'responsive-embeds' );
		$manual_flag = get_option( 'wpshadow_mobile_optimization_priority' );

		if ( $theme_supports_responsive || $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No clear mobile-first signals detected. Prioritize responsive design and mobile testing to protect search rankings.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-optimization-priority',
			'persona'      => 'publisher',
		);
	}
}
