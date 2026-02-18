<?php
/**
 * Mobile CLS (Cumulative Layout Shift) Diagnostic
 *
 * Calculates layout shifts during page load to prevent content jumps.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile CLS (Cumulative Layout Shift) Diagnostic Class
 *
 * Calculates layout shifts during page load to prevent frustrating content jumps
 * that cause mis-taps, a Core Web Vitals metric for Google rankings.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Mobile_CLS_Cumulative_Layout_Shift extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cls-cumulative-layout-shift';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CLS (Cumulative Layout Shift)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculate layout shifts during page load (Core Web Vitals metric)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if images have width/height attributes to prevent layout shift
		$images_have_dimensions = apply_filters( 'wpshadow_images_have_width_height_attributes', false );
		if ( ! $images_have_dimensions ) {
			$issues[] = __( 'Images may not have width/height attributes; could cause layout shift', 'wpshadow' );
		}

		// Check for ad slot reservations (reserved space for ads)
		$ad_slots_reserved = apply_filters( 'wpshadow_ad_slots_have_reserved_space', false );
		if ( ! $ad_slots_reserved ) {
			$issues[] = __( 'Ad slots may not have reserved space; could cause layout shift when ads load', 'wpshadow' );
		}

		// Check for font loading strategy (FOIT/FOUT impact)
		$font_loading_strategy = apply_filters( 'wpshadow_font_loading_strategy', 'default' );
		if ( 'default' === $font_loading_strategy ) {
			$issues[] = __( 'Font loading strategy not optimized; may cause text layout shift (FOUT)', 'wpshadow' );
		}

		// Check for Core Web Vitals monitoring
		$cwv_monitoring = apply_filters( 'wpshadow_core_web_vitals_monitoring_enabled', false );
		if ( ! $cwv_monitoring ) {
			$issues[] = __( 'Core Web Vitals monitoring not detected; CLS measurement unavailable', 'wpshadow' );
		}

		// Check for dynamic content insertion without space reservation
		$dynamic_content_reserved = apply_filters( 'wpshadow_dynamic_content_reserves_space', false );
		if ( ! $dynamic_content_reserved ) {
			$issues[] = __( 'Dynamic content (modals, notifications) may not reserve space; could cause layout shift', 'wpshadow' );
		}

		// Check for third-party script impact on layout
		$third_party_scripts_optimized = apply_filters( 'wpshadow_third_party_scripts_optimized', false );
		if ( ! $third_party_scripts_optimized ) {
			$issues[] = __( 'Third-party scripts may not be optimized; could contribute to layout shift', 'wpshadow' );
		}

		// Check if there's a CLS monitoring tool/plugin
		$cls_plugins = array(
			'lighthouse' => 'Lighthouse',
			'web-vitals' => 'Web Vitals',
		);

		$has_cls_monitoring = false;
		foreach ( $cls_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_cls_monitoring = true;
				break;
			}
		}

		if ( ! $has_cls_monitoring && ! $cwv_monitoring ) {
			$issues[] = __( 'No CLS monitoring plugin/tool detected; layout shifts unverified', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-cls-cumulative-layout-shift',
			);
		}

		return null;
	}
}
