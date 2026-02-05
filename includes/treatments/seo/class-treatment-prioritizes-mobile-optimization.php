<?php
/**
 * Mobile Optimization Priority Treatment
 *
 * Tests if mobile-first approach is evident.
 *
 * @since   1.6050.0000
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Optimization Priority Treatment Class
 *
 * Verifies mobile optimization is prioritized through responsive theme
 * support and mobile enhancement plugins.
 *
 * @since 1.6050.0000
 */
class Treatment_Prioritizes_Mobile_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'prioritizes-mobile-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Optimization Priority';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if mobile-first approach is evident';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
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
