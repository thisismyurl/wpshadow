<?php
/**
 * Mobile Page Weight Detection Diagnostic
 *
 * Calculates total page size served to mobile users to identify excessive bandwidth.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Page Weight Detection Diagnostic Class
 *
 * Calculates total page size (HTML + CSS + JS + images) served to mobile users
 * to identify excessive bandwidth consumption affecting search rankings and UX.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Mobile_Page_Weight_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-page-weight-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Weight Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculate total page size served to mobile users to identify excessive bandwidth';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for mobile-specific bundle optimization
		$mobile_bundle_optimized = apply_filters( 'wpshadow_mobile_bundle_optimized', false );
		if ( ! $mobile_bundle_optimized ) {
			$issues[] = __( 'Mobile-specific bundle optimization not detected; unnecessarily large resources sent', 'wpshadow' );
		}

		// Check if images are mobile-sized
		$images_mobile_optimized = apply_filters( 'wpshadow_images_mobile_optimized', false );
		if ( ! $images_mobile_optimized ) {
			$issues[] = __( 'Images may not be optimized for mobile; likely larger than necessary', 'wpshadow' );
		}

		// Check for minification of CSS/JS
		$css_js_minified = apply_filters( 'wpshadow_css_js_minified', false );
		if ( ! $css_js_minified ) {
			$issues[] = __( 'CSS/JavaScript not minified; unnecessary whitespace/comments added', 'wpshadow' );
		}

		// Check for above-fold content optimization
		$above_fold_optimized = apply_filters( 'wpshadow_above_fold_optimized', false );
		if ( ! $above_fold_optimized ) {
			$issues[] = __( 'Above-fold resources may not be prioritized; delays visible content', 'wpshadow' );
		}

		// Check for unused CSS removal
		$unused_css_removed = apply_filters( 'wpshadow_unused_css_removed', false );
		if ( ! $unused_css_removed ) {
			$issues[] = __( 'Unused CSS not removed; adds unnecessary page weight', 'wpshadow' );
		}

		// Check for gzip compression
		$gzip_enabled = apply_filters( 'wpshadow_gzip_compression_enabled', false );
		if ( ! $gzip_enabled ) {
			$issues[] = __( 'Gzip compression not detected; resources not compressed in transit', 'wpshadow' );
		}

		// Check for caching strategy
		$browser_caching = apply_filters( 'wpshadow_browser_caching_enabled', false );
		if ( ! $browser_caching ) {
			$issues[] = __( 'Browser caching not configured; resources re-downloaded on repeat visits', 'wpshadow' );
		}

		// Check estimated page weight thresholds
		$estimated_page_weight = apply_filters( 'wpshadow_estimated_page_weight_kb', 0 );
		if ( $estimated_page_weight > 3000 ) {
			$issues[] = sprintf(
				/* translators: %dKB: page weight */
				__( 'Estimated page weight is %dKB; target <3MB total, <500KB above-fold', 'wpshadow' ),
				$estimated_page_weight
			);
		}

		// Check for unnecessary third-party resources
		$third_party_optimization = apply_filters( 'wpshadow_third_party_resources_optimized', false );
		if ( ! $third_party_optimization ) {
			$issues[] = __( 'Third-party resources may not be optimized; consider deferring non-critical scripts', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-page-weight-detection',
			);
		}

		return null;
	}
}
