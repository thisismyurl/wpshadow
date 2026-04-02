<?php
/**
 * Mobile vs Desktop Performance Comparison Diagnostic
 *
 * Checks if mobile and desktop performance are tracked separately.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile vs Desktop Performance Comparison Diagnostic Class
 *
 * Verifies device-specific performance tracking.
 * Like knowing if your store is harder to navigate on a wheelchair.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Desktop_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-desktop-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile vs Desktop Performance Comparison';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile and desktop performance are tracked separately';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Run the mobile/desktop performance diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if tracking issues detected, null otherwise.
	 */
	public static function check() {
		// Check if device-specific tracking is enabled.
		$device_tracking_enabled = get_option( 'wpshadow_device_tracking_enabled', false );

		if ( ! $device_tracking_enabled ) {
			// Check for analytics tools that support device segmentation.
			$has_device_analytics = false;
			
			if ( defined( 'GOOGLESITEKIT_VERSION' ) || self::has_google_analytics() ) {
				$has_device_analytics = true;
			}

			if ( ! $has_device_analytics ) {
				return array(
					'id'           => self::$slug . '-not-tracked',
					'title'        => __( 'Mobile and Desktop Performance Not Tracked Separately', 'wpshadow' ),
					'description'  => __( 'You\'re not tracking mobile vs desktop performance separately (like assuming all customers have the same shopping experience). Mobile visitors often have slower connections and smaller screens—your site might be fast on desktop but painfully slow on mobile. Over 60% of web traffic is mobile now. Set up device segmentation in Google Analytics 4 or Google Search Console to see how each device type performs. This reveals if you\'re losing mobile visitors to slow speeds.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/device-performance',
					'context'      => array(),
				);
			}
		}

		// Check recent mobile vs desktop performance data.
		$mobile_performance = get_transient( 'wpshadow_mobile_performance' );
		$desktop_performance = get_transient( 'wpshadow_desktop_performance' );

		if ( false !== $mobile_performance && false !== $desktop_performance ) {
			$mobile_avg = $mobile_performance['avg_load_time'] ?? 0;
			$desktop_avg = $desktop_performance['avg_load_time'] ?? 0;

			// Check if mobile is significantly slower (more than 50% slower).
			if ( $mobile_avg > 0 && $desktop_avg > 0 ) {
				$mobile_slowdown = ( ( $mobile_avg - $desktop_avg ) / $desktop_avg ) * 100;

				if ( $mobile_slowdown > 50 ) {
					return array(
						'id'           => self::$slug . '-mobile-much-slower',
						'title'        => __( 'Mobile Performance Significantly Slower', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: 1: mobile time, 2: desktop time, 3: percentage slower */
							__( 'Your mobile visitors experience much slower performance than desktop users (mobile: %1$s, desktop: %2$s—that\'s %3$d%% slower on mobile). This is like making mobile customers wait in a longer checkout line. Mobile-first indexing means Google judges your site primarily on mobile performance. Common causes: large unoptimized images, desktop-focused CSS/JS, no lazy loading. Use Google\'s Mobile-Friendly Test and optimize for mobile first.', 'wpshadow' ),
							number_format( $mobile_avg / 1000, 2 ) . 's',
							number_format( $desktop_avg / 1000, 2 ) . 's',
							(int) $mobile_slowdown
						),
						'severity'     => 'high',
						'threat_level' => 75,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/mobile-performance',
						'context'      => array(
							'mobile_avg_ms'   => $mobile_avg,
							'desktop_avg_ms'  => $desktop_avg,
							'slowdown_pct'    => $mobile_slowdown,
						),
					);
				}

				if ( $mobile_slowdown > 25 ) {
					return array(
						'id'           => self::$slug . '-mobile-slower',
						'title'        => __( 'Mobile Performance Could Be Better', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: 1: mobile time, 2: desktop time */
							__( 'Mobile visitors experience slower performance than desktop users (mobile: %1$s, desktop: %2$s). While not critical, there\'s room for improvement. Mobile visitors are often on slower connections, so every millisecond matters. Consider: image optimization, lazy loading, reducing JavaScript, using a CDN. With 60%+ mobile traffic, mobile performance directly impacts your bottom line.', 'wpshadow' ),
							number_format( $mobile_avg / 1000, 2 ) . 's',
							number_format( $desktop_avg / 1000, 2 ) . 's'
						),
						'severity'     => 'medium',
						'threat_level' => 55,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/mobile-performance',
						'context'      => array(
							'mobile_avg_ms'  => $mobile_avg,
							'desktop_avg_ms' => $desktop_avg,
						),
					);
				}
			}
		}

		return null; // Device-specific performance tracking is configured and reasonable.
	}

	/**
	 * Check if Google Analytics is present.
	 *
	 * @since 1.6093.1200
	 * @return bool True if GA detected.
	 */
	private static function has_google_analytics() {
		$head_content = get_transient( 'wpshadow_head_content_sample' );
		
		if ( false === $head_content ) {
			ob_start();
			wp_head();
			$head_content = ob_get_clean();
			set_transient( 'wpshadow_head_content_sample', $head_content, DAY_IN_SECONDS );
		}

		return ( false !== strpos( $head_content, 'gtag' ) 
			|| false !== strpos( $head_content, 'analytics.js' ) 
			|| false !== strpos( $head_content, 'gtm.js' )
		);
	}
}
