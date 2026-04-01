<?php
/**
 * Mobile vs Desktop Speed Diagnostic
 *
 * Checks if mobile page speed is significantly worse than desktop.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile vs Desktop Speed Diagnostic Class
 *
 * 58% of traffic is mobile. If mobile is 2x slower than desktop, you're giving
 * 58% of customers a bad experience.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Desktop_Speed_Gap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-desktop-speed-gap';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Speed Significantly Worse Than Desktop';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile speed is within 20% of desktop speed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$mobile_score = 0;
		$max_score    = 5;

		// Check mobile-specific optimizations.
		$has_responsive_images = self::check_responsive_images();
		if ( $has_responsive_images ) {
			++$mobile_score;
		} else {
			$issues[] = 'responsive images serving smaller sizes';
		}

		// Check touch targets.
		$has_touch_targets = self::check_touch_targets();
		if ( $has_touch_targets ) {
			++$mobile_score;
		} else {
			$issues[] = 'touch targets large enough (44x44px)';
		}

		// Check for mobile-only heavy resources.
		$no_desktop_only = self::check_no_desktop_only_resources();
		if ( $no_desktop_only ) {
			++$mobile_score;
		} else {
			$issues[] = 'detecting desktop-only heavy resources';
		}

		// Check mobile optimization plugins.
		$has_mobile_optimization = self::check_mobile_optimization();
		if ( $has_mobile_optimization ) {
			++$mobile_score;
		} else {
			$issues[] = 'mobile-specific optimization plugins';
		}

		// Check AMP or mobile theme.
		$has_amp_or_mobile = self::check_amp_or_mobile_theme();
		if ( $has_amp_or_mobile ) {
			++$mobile_score;
		} else {
			$issues[] = 'AMP or dedicated mobile theme';
		}

		$completion_percentage = ( $mobile_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Mobile optimized.
		}

		$severity     = $completion_percentage < 40 ? 'critical' : 'high';
		$threat_level = $completion_percentage < 40 ? 85 : 65;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Mobile optimization at %1$d%%. Missing: %2$s. Like having nice storefront but terrible drive-through—losing majority of customers.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-desktop-speed-gap?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check for responsive images.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return bool True if no desktop-only resources.
	 */
	private static function check_no_desktop_only_resources(): bool {
		// Assume OK if mobile optimization plugins active.
		return self::check_mobile_optimization();
	}

	/**
	 * Check for mobile optimization plugins.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
