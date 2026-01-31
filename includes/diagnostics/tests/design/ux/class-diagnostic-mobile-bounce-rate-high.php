<?php
/**
 * Mobile Bounce Rate Higher Than Desktop Diagnostic
 *
 * Detects when mobile bounce rate is significantly higher than desktop,
 * indicating mobile UX issues that may affect user experience and conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Bounce Rate Higher Than Desktop Diagnostic Class
 *
 * Compares mobile vs desktop bounce rates using analytics data to identify
 * mobile-specific UX issues.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Mobile_Bounce_Rate_High extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-bounce-rate-high';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Bounce Rate 50% Higher Than Desktop';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Compares mobile vs desktop bounce rates to identify mobile-specific UX issues';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_mobile_bounce_rate_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = self::check_mobile_bounce_rate();

		// Cache for 24 hours.
		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Check mobile bounce rate versus desktop.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_mobile_bounce_rate() {
		// Try to get analytics data from various sources.
		$analytics_data = self::get_analytics_data();

		if ( empty( $analytics_data ) ) {
			// No analytics data available - check for common mobile UX issues as proxy.
			return self::check_mobile_ux_indicators();
		}

		$mobile_bounce  = $analytics_data['mobile_bounce'] ?? 0;
		$desktop_bounce = $analytics_data['desktop_bounce'] ?? 0;

		// Need both values to compare.
		if ( $mobile_bounce <= 0 || $desktop_bounce <= 0 ) {
			return null;
		}

		// Calculate percentage difference.
		$difference_pct = ( ( $mobile_bounce - $desktop_bounce ) / $desktop_bounce ) * 100;

		// Determine severity based on difference.
		if ( $difference_pct <= 10 ) {
			return null; // Good - mobile bounce within acceptable range.
		}

		$severity     = $difference_pct;
		$threat_level = self::calculate_threat_level( $difference_pct );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: mobile bounce rate, 2: desktop bounce rate, 3: percentage difference */
				__( 'Mobile bounce rate (%1$.1f%%) is %3$.1f%% higher than desktop (%2$.1f%%), indicating mobile UX issues', 'wpshadow' ),
				$mobile_bounce,
				$desktop_bounce,
				$difference_pct
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-bounce-rate',
			'meta'         => array(
				'mobile_bounce_rate'  => $mobile_bounce,
				'desktop_bounce_rate' => $desktop_bounce,
				'difference_percent'  => $difference_pct,
				'analytics_source'    => $analytics_data['source'] ?? 'unknown',
			),
			'details'      => array(
				__( 'High mobile bounce rates indicate poor mobile user experience', 'wpshadow' ),
				__( 'Check mobile responsive design and navigation', 'wpshadow' ),
				__( 'Test page load speed on mobile devices', 'wpshadow' ),
				__( 'Verify touch targets are appropriately sized', 'wpshadow' ),
				__( 'Review mobile-specific layout issues', 'wpshadow' ),
			),
			'recommendations' => self::get_recommendations( $difference_pct ),
		);
	}

	/**
	 * Get analytics data from available sources.
	 *
	 * @since  1.6028.1445
	 * @return array Analytics data with mobile and desktop bounce rates.
	 */
	private static function get_analytics_data() {
		// Try Google Analytics (MonsterInsights, Site Kit, etc.).
		$ga_data = self::get_google_analytics_data();
		if ( ! empty( $ga_data ) ) {
			return $ga_data;
		}

		// Try Jetpack Stats.
		$jetpack_data = self::get_jetpack_stats_data();
		if ( ! empty( $jetpack_data ) ) {
			return $jetpack_data;
		}

		// Try custom analytics options.
		$custom_data = self::get_custom_analytics_data();
		if ( ! empty( $custom_data ) ) {
			return $custom_data;
		}

		return array();
	}

	/**
	 * Get Google Analytics data from popular plugins.
	 *
	 * @since  1.6028.1445
	 * @return array Google Analytics bounce rate data.
	 */
	private static function get_google_analytics_data() {
		// Try MonsterInsights.
		if ( class_exists( 'MonsterInsights' ) ) {
			$mi_data = get_option( 'monsterinsights_cache_mobile_bounce', array() );
			if ( ! empty( $mi_data['mobile'] ) && ! empty( $mi_data['desktop'] ) ) {
				return array(
					'mobile_bounce'  => floatval( $mi_data['mobile'] ),
					'desktop_bounce' => floatval( $mi_data['desktop'] ),
					'source'         => 'monsterinsights',
				);
			}
		}

		// Try Google Site Kit.
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			$sitekit_data = get_option( 'googlesitekit_analytics_bounce_rate', array() );
			if ( ! empty( $sitekit_data['mobile'] ) && ! empty( $sitekit_data['desktop'] ) ) {
				return array(
					'mobile_bounce'  => floatval( $sitekit_data['mobile'] ),
					'desktop_bounce' => floatval( $sitekit_data['desktop'] ),
					'source'         => 'google_site_kit',
				);
			}
		}

		return array();
	}

	/**
	 * Get Jetpack Stats data.
	 *
	 * @since  1.6028.1445
	 * @return array Jetpack bounce rate data.
	 */
	private static function get_jetpack_stats_data() {
		if ( ! class_exists( 'Jetpack' ) || ! \Jetpack::is_module_active( 'stats' ) ) {
			return array();
		}

		$jetpack_data = get_option( 'jetpack_stats_bounce_rate', array() );
		if ( ! empty( $jetpack_data['mobile'] ) && ! empty( $jetpack_data['desktop'] ) ) {
			return array(
				'mobile_bounce'  => floatval( $jetpack_data['mobile'] ),
				'desktop_bounce' => floatval( $jetpack_data['desktop'] ),
				'source'         => 'jetpack',
			);
		}

		return array();
	}

	/**
	 * Get custom analytics data from WPShadow settings.
	 *
	 * @since  1.6028.1445
	 * @return array Custom analytics data.
	 */
	private static function get_custom_analytics_data() {
		$mobile_bounce  = get_option( 'wpshadow_mobile_bounce_rate', 0 );
		$desktop_bounce = get_option( 'wpshadow_desktop_bounce_rate', 0 );

		if ( $mobile_bounce > 0 && $desktop_bounce > 0 ) {
			return array(
				'mobile_bounce'  => floatval( $mobile_bounce ),
				'desktop_bounce' => floatval( $desktop_bounce ),
				'source'         => 'wpshadow_custom',
			);
		}

		return array();
	}

	/**
	 * Check mobile UX indicators when analytics unavailable.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding if mobile UX issues detected.
	 */
	private static function check_mobile_ux_indicators() {
		$issues = array();

		// Check if site is mobile-responsive.
		if ( ! self::is_mobile_responsive() ) {
			$issues[] = __( 'Theme is not mobile-responsive', 'wpshadow' );
		}

		// Check viewport meta tag.
		if ( ! self::has_viewport_meta() ) {
			$issues[] = __( 'Missing viewport meta tag', 'wpshadow' );
		}

		// Check for mobile-specific CSS.
		if ( ! self::has_mobile_css() ) {
			$issues[] = __( 'No mobile-specific CSS detected', 'wpshadow' );
		}

		// If multiple mobile UX issues found, flag it.
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'Mobile UX Issues Detected', 'wpshadow' ),
				'description'  => __( 'Multiple mobile UX issues found that may cause high bounce rates', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-bounce-rate',
				'meta'         => array(
					'issues_found' => $issues,
					'check_type'   => 'proxy_indicators',
				),
				'details'      => $issues,
				'recommendations' => array(
					__( 'Install analytics plugin to track bounce rates', 'wpshadow' ),
					__( 'Use mobile-responsive theme', 'wpshadow' ),
					__( 'Add viewport meta tag', 'wpshadow' ),
					__( 'Implement mobile-specific CSS', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if theme is mobile-responsive.
	 *
	 * @since  1.6028.1445
	 * @return bool True if responsive.
	 */
	private static function is_mobile_responsive() {
		$theme = wp_get_theme();
		$tags  = $theme->get( 'Tags' );

		if ( is_array( $tags ) && in_array( 'responsive-layout', $tags, true ) ) {
			return true;
		}

		// Check theme support.
		return current_theme_supports( 'responsive-embeds' ) ||
		       current_theme_supports( 'html5' );
	}

	/**
	 * Check if viewport meta tag exists.
	 *
	 * @since  1.6028.1445
	 * @return bool True if viewport meta present.
	 */
	private static function has_viewport_meta() {
		// Capture homepage HTML.
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		return false !== strpos( $html, 'viewport' ) &&
		       false !== strpos( $html, 'width=device-width' );
	}

	/**
	 * Check if mobile-specific CSS exists.
	 *
	 * @since  1.6028.1445
	 * @return bool True if mobile CSS detected.
	 */
	private static function has_mobile_css() {
		// Capture homepage HTML.
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for media queries.
		return false !== strpos( $html, '@media' ) ||
		       false !== strpos( $html, 'max-width' ) ||
		       false !== strpos( $html, 'min-width' );
	}

	/**
	 * Calculate severity based on bounce rate difference.
	 *
	 * @since  1.6028.1445
	 * @param  float $difference_pct Percentage difference.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $difference_pct ) {
		if ( $difference_pct > 50 ) {
			return 'high';
		} elseif ( $difference_pct > 30 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Calculate threat level based on bounce rate difference.
	 *
	 * @since  1.6028.1445
	 * @param  float $difference_pct Percentage difference.
	 * @return int Threat level (0-100).
	 */
	private static function calculate_threat_level( $difference_pct ) {
		if ( $difference_pct > 50 ) {
			return 70;
		} elseif ( $difference_pct > 30 ) {
			return 55;
		} else {
			return 40;
		}
	}

	/**
	 * Get recommendations based on bounce rate difference.
	 *
	 * @since  1.6028.1445
	 * @param  float $difference_pct Percentage difference.
	 * @return array Recommendations.
	 */
	private static function get_recommendations( $difference_pct ) {
		$recommendations = array(
			__( 'Test site on actual mobile devices', 'wpshadow' ),
			__( 'Use mobile-friendly testing tools (Google Mobile-Friendly Test)', 'wpshadow' ),
			__( 'Optimize page load speed for mobile', 'wpshadow' ),
			__( 'Review mobile navigation and menu structure', 'wpshadow' ),
			__( 'Ensure touch targets are at least 44x44 pixels', 'wpshadow' ),
		);

		if ( $difference_pct > 50 ) {
			$recommendations[] = __( 'URGENT: Consider major mobile UX overhaul', 'wpshadow' );
			$recommendations[] = __( 'Hire mobile UX specialist if needed', 'wpshadow' );
		}

		return $recommendations;
	}
}
