<?php
/**
 * Phone Call Conversion Tracking Diagnostic
 *
 * Checks if phone call conversions are tracked for service businesses.
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
 * Phone Call Conversion Tracking Diagnostic Class
 *
 * For service businesses, 40-60% of leads call. Without tracking, you miss
 * invisible conversions that are actually working.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Phone_Call_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'phone-call-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Phone Call Conversion Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if phone call conversions are tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues         = array();
		$tracking_score = 0;
		$max_score      = 5;

		// Check for call tracking numbers.
		$has_tracking_numbers = self::check_call_tracking_numbers();
		if ( $has_tracking_numbers ) {
			++$tracking_score;
		} else {
			$issues[] = 'call tracking numbers';
		}

		// Check for dynamic number insertion.
		$has_dni = self::check_dynamic_number_insertion();
		if ( $has_dni ) {
			++$tracking_score;
		} else {
			$issues[] = 'dynamic number insertion (DNI)';
		}

		// Check for call source attribution.
		$has_attribution = self::check_call_attribution();
		if ( $has_attribution ) {
			++$tracking_score;
		} else {
			$issues[] = 'call source attribution';
		}

		// Check for call recording.
		$has_recording = self::check_call_recording();
		if ( $has_recording ) {
			++$tracking_score;
		} else {
			$issues[] = 'call recording';
		}

		// Check for call conversion events in analytics.
		$has_analytics_events = self::check_call_analytics_events();
		if ( $has_analytics_events ) {
			++$tracking_score;
		} else {
			$issues[] = 'call conversion events in analytics';
		}

		$completion_percentage = ( $tracking_score / $max_score ) * 100;

		if ( $completion_percentage >= 40 ) {
			return null; // Call tracking present.
		}

		$severity     = $completion_percentage < 20 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 20 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Phone call tracking at %1$d%%. Missing: %2$s. Service businesses miss 30-50%% of conversions without call tracking.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/phone-call-tracking',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if call tracking numbers exist.
	 *
	 * @since 1.6093.1200
	 * @return bool True if tracking numbers exist.
	 */
	private static function check_call_tracking_numbers(): bool {
		// Check for call tracking plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$call_tracking_plugins = array(
			'calltrackingmetrics/calltrackingmetrics.php',
			'callrail-phone-tracker/callrail-phone-tracker.php',
		);

		foreach ( $call_tracking_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for call tracking script.
		global $wp_scripts;
		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		$call_tracking_scripts = array(
			'calltrackingmetrics',
			'callrail',
			'phonetracking',
		);

		foreach ( $call_tracking_scripts as $script ) {
			if ( $wp_scripts->query( $script ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if dynamic number insertion exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if DNI exists.
	 */
	private static function check_dynamic_number_insertion(): bool {
		// DNI is typically part of call tracking services.
		return self::check_call_tracking_numbers();
	}

	/**
	 * Check if call source attribution exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if attribution exists.
	 */
	private static function check_call_attribution(): bool {
		// Call attribution is part of call tracking services.
		return self::check_call_tracking_numbers();
	}

	/**
	 * Check if call recording is configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if recording exists.
	 */
	private static function check_call_recording(): bool {
		// Check for call recording option.
		$call_recording = get_option( 'wpshadow_call_recording_enabled', false );
		if ( $call_recording ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if call conversion events are in analytics.
	 *
	 * @since 1.6093.1200
	 * @return bool True if events exist.
	 */
	private static function check_call_analytics_events(): bool {
		// Check for Google Analytics event tracking.
		global $wp_scripts;
		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		// Check for GA tracking.
		if ( $wp_scripts->query( 'google-analytics' ) || $wp_scripts->query( 'gtag' ) ) {
			// If call tracking AND GA present, likely integrated.
			return self::check_call_tracking_numbers();
		}

		return false;
	}
}
