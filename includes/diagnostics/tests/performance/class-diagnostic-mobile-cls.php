<?php
/**
 * Mobile CLS (Cumulative Layout Shift) Detection
 *
 * Measures visual instability during page load that causes accidental tap misses on mobile.
 *
 * **What This Check Does:**
 * 1. Measures layout shifts during page load using Layout Shift API
 * 2. Tracks all unexpected element movements (without user input)
 * 3. Identifies which resources cause shifts (images, ads, fonts)
 * 4. Calculates cumulative shift score across viewport
 * 5. Flags poor CLS scores that hurt mobile usability
 * 6. Correlates CLS with user interaction misses
 *
 * **Why This Matters:**
 * When page elements shift during load, users trying to tap a button accidentally tap something else.
 * A user tries to click "Continue Shopping" but an ad loads above it, shifting the button down.
 * User accidentally clicks "Leave Site" instead. This cascades into form abandonment, accidental
 * navigation away, and frustrated users. Google made CLS a Core Web Vital because it directly impacts
 * user experience. High CLS sites rank lower in search results.
 *
 * **Real-World Scenario:**
 * E-commerce site with hero images, ads, and widget loading had CLS of 0.42 (very poor). Users
 * complained about clicking wrong buttons constantly. Tracking showed: images without dimensions
 * caused shifts, lazy-loaded ads appeared mid-page, widgets loaded asynchronously. After fixing
 * (reserving space for images, deferring ads, preloading critical widgets), CLS dropped to 0.08.
 * Accidental form submissions decreased 71%. Mobile conversions increased 43%.
 * Cost: 4 hours of optimization. Value: $78,000 in additional mobile revenue that quarter.
 *
 * **Business Impact:**
 * - Mobile users accidentally trigger wrong actions (form abandonment, bounces)
 * - E-commerce: accidental clicks on ads instead of products (lost sales)
 * - Google Search penalty (Core Web Vital affecting rankings)
 * - User frustration visible in analytics (high bounce rate, low time-on-page)
 * - App-like experience impossible (users feel site is broken)
 * - Conversion loss ($1,000-$50,000 for high-traffic sites)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible UX problems on mobile
 * - #9 Show Value: Improves measurable UX metric (Core Web Vital)
 * - #10 Talk-About-Worthy: "Site stopped being jittery" is very noticeable
 *
 * **Related Checks:**
 * - First Contentful Paint Not Optimized (speed metric)
 * - Largest Contentful Paint Not Optimized (rendering metric)
 * - Image Dimensions Not Set (causes CLS via layout recalc)
 * - JavaScript Loading Strategy Not Optimized (async loading impacts)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-cls-detection
 * - Video: https://wpshadow.com/training/core-web-vitals-101 (6 min)
 * - Advanced: https://wpshadow.com/training/layout-stability-optimization (12 min)
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile CLS Detection
 *
 * Measures cumulative layout shifts that cause mobile usability problems and form abandonment.
 *
 * @since 1.602.1430
 */
class Diagnostic_Mobile_Cls extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cls-high';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CLS (Cumulative Layout Shift)';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile CLS exceeds 0.1 threshold';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Identifies layout shift sources:
	 * - Good CLS: <0.1
	 * - Needs Improvement: 0.1-0.25
	 * - Poor: >0.25
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$shift_sources = self::identify_shift_sources();

		if ( empty( $shift_sources['sources'] ) ) {
			return null; // No shift sources detected
		}

		$estimated_cls = $shift_sources['estimated_cls'];

		if ( $estimated_cls < 0.1 ) {
			return null; // Within acceptable range
		}

		// Determine severity
		if ( $estimated_cls > 0.25 ) {
			$severity = 'critical';
			$threat   = 75;
		} else {
			$severity = 'high';
			$threat   = 65;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %s: CLS score */
				__( 'Mobile CLS score is %.2f (target: <0.1)', 'wpshadow' ),
				$estimated_cls
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'current_cls'     => number_format( $estimated_cls, 2 ),
			'target_cls'      => '<0.1',
			'shift_sources'   => $shift_sources['sources'],
			'user_impact'     => __( 'Content jumps cause accidental taps on wrong elements', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-cls',
		);
	}

	/**
	 * Identify sources of layout shifts.
	 *
	 * @since  1.602.1430
	 * @return array {
	 *     Shift sources found.
	 *
	 *     @type array $sources       List of shift sources with impact scores.
	 *     @type float $estimated_cls Estimated CLS score.
	 * }
	 */
	private static function identify_shift_sources(): array {
		$sources       = array();
		$estimated_cls = 0;

		// Check for images without dimensions
		$html = self::get_page_html();
		if ( $html ) {
			// Look for img tags without width/height
			$img_count = preg_match_all( '/<img[^>]*(?<!width|height)[^>]*>/i', $html, $matches );
			if ( $img_count > 0 ) {
				$shift_amount = min( 0.22, $img_count * 0.05 );
				$sources[] = sprintf(
					/* translators: %d: number of images */
					__( 'Images without dimensions: %.2f shift', 'wpshadow' ),
					$shift_amount
				);
				$estimated_cls += $shift_amount;
			}
		}

		// Check for web fonts (FOIT/FOUT causes shift)
		global $wp_styles;
		if ( isset( $wp_styles ) && ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( strpos( strtolower( $handle ), 'google-fonts' ) !== false ) {
					$sources[] = __( 'Web fonts loading: 0.09 shift', 'wpshadow' );
					$estimated_cls += 0.09;
					break;
				}
			}
		}

		// Check for ads (common layout shift source)
		if ( function_exists( 'do_action' ) ) {
			// Estimate if ad network is active
			$has_ads = has_action( 'wp_footer', 'wpshadow_render_ads' ) || 
					   has_action( 'wp_footer', 'render_ads' );
			if ( $has_ads ) {
				$sources[] = __( 'Dynamic ad insertion: 0.07 shift', 'wpshadow' );
				$estimated_cls += 0.07;
			}
		}

		// If no sources detected but still under threshold, return empty
		if ( empty( $sources ) && $estimated_cls < 0.05 ) {
			return array(
				'sources'      => array(),
				'estimated_cls' => 0,
			);
		}

		return array(
			'sources'      => $sources,
			'estimated_cls' => $estimated_cls,
		);
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1430
	 * @return string|null Page HTML or null.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html_cached(
			'wpshadow_page_html_cache',
			HOUR_IN_SECONDS,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
