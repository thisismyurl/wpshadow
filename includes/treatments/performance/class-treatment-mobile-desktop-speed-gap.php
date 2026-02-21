<?php
/**
 * Mobile vs Desktop Speed Treatment
 *
 * Checks if mobile page speed is significantly worse than desktop.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1135
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile vs Desktop Speed Treatment Class
 *
 * 58% of traffic is mobile. If mobile is 2x slower than desktop, you're giving
 * 58% of customers a bad experience.
 *
 * @since 1.6035.1135
 */
class Treatment_Mobile_Desktop_Speed_Gap extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-desktop-speed-gap';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Speed Significantly Worse Than Desktop';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile speed is within 20% of desktop speed';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile-optimization';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1135
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Desktop_Speed_Gap' );
	}

	/**
	 * Check for responsive images.
	 *
	 * @since  1.6035.1135
	 * @return bool True if responsive images exist.
	 */
	private static function check_responsive_images(): bool {
		// WordPress 4.4+ has responsive images by default.
		global $wp_version;
		return version_compare( $wp_version, '4.4', '>=' );
	}

	/**
	 * Check for adequate touch targets.
	 *
	 * @since  1.6035.1135
	 * @return bool True if touch targets adequate.
	 */
	private static function check_touch_targets(): bool {
		// Check theme CSS for mobile-friendly button sizes.
		$theme          = wp_get_theme();
		$stylesheet_dir = $theme->get_stylesheet_directory();
		$style_file     = $stylesheet_dir . '/style.css';

		if ( ! file_exists( $style_file ) ) {
			return false;
		}

		$content = file_get_contents( $style_file );

		// Check for media queries with touch target sizing.
		if ( preg_match( '/min-height.*?(4[4-9]|[5-9][0-9])px/i', $content ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for desktop-only resources.
	 *
	 * @since  1.6035.1135
	 * @return bool True if no desktop-only resources.
	 */
	private static function check_no_desktop_only_resources(): bool {
		// Assume OK if mobile optimization plugins active.
		return self::check_mobile_optimization();
	}

	/**
	 * Check for mobile optimization plugins.
	 *
	 * @since  1.6035.1135
	 * @return bool True if plugins active.
	 */
	private static function check_mobile_optimization(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$mobile_plugins = array(
			'wp-rocket/wp-rocket.php',
			'autoptimize/autoptimize.php',
			'jetpack/jetpack.php',
		);

		foreach ( $mobile_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for AMP or mobile theme.
	 *
	 * @since  1.6035.1135
	 * @return bool True if AMP or mobile theme active.
	 */
	private static function check_amp_or_mobile_theme(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for AMP plugin.
		if ( is_plugin_active( 'amp/amp.php' ) ) {
			return true;
		}

		return false;
	}
}
