<?php
/**
 * AddToAny Analytics Diagnostic
 *
 * AddToAny analytics not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.436.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AddToAny Analytics Diagnostic Class
 *
 * @since 1.436.0000
 */
class Diagnostic_AddtoanyAnalyticsIntegration extends Diagnostic_Base {

	protected static $slug = 'addtoany-analytics-integration';
	protected static $title = 'AddToAny Analytics';
	protected static $description = 'AddToAny analytics not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'A2A_SHARE_SAVE_init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Analytics tracking enabled.
		$analytics_enabled = get_option( 'addtoany_analytics', '0' );
		if ( '0' === $analytics_enabled ) {
			$issues[] = 'AddToAny analytics tracking not enabled';
		}

		// Check 2: Google Analytics integration.
		$ga_tracking = get_option( 'addtoany_ga_tracking', '0' );
		if ( '0' === $ga_tracking && '1' === $analytics_enabled ) {
			$issues[] = 'analytics enabled but not integrated with Google Analytics';
		}

		// Check 3: Event tracking configured.
		$event_tracking = get_option( 'addtoany_event_tracking', 'none' );
		if ( 'none' === $event_tracking ) {
			$issues[] = 'event tracking not configured (share actions not tracked)';
		}

		// Check 4: Share counts displayed.
		$share_counts = get_option( 'addtoany_share_counts', '0' );
		if ( '0' === $share_counts ) {
			$issues[] = 'share counts not displayed (reduces social proof)';
		}

		// Check 5: Custom analytics code.
		$custom_code = get_option( 'addtoany_custom_analytics', '' );
		if ( ! empty( $custom_code ) && false === strpos( $custom_code, 'gtag' ) && false === strpos( $custom_code, '_gaq' ) ) {
			$issues[] = 'custom analytics code present but not using standard GA format';
		}

		// Check 6: Tracking on all post types.
		global $wpdb;
		$enabled_post_types = get_option( 'addtoany_post_types', array( 'post' ) );
		$all_post_types = get_post_types( array( 'public' => true ), 'names' );
		$missing_types = array_diff( $all_post_types, $enabled_post_types );
		if ( count( $missing_types ) > 0 ) {
			$issues[] = count( $missing_types ) . ' post types not tracked (' . implode( ', ', array_slice( $missing_types, 0, 3 ) ) . ')';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AddToAny analytics integration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/addtoany-analytics-integration',
			);
		}

		return null;
	}
}
