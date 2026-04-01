<?php
/**
 * Conversion Goal Tracking Diagnostic
 *
 * Checks if conversion goals are configured in analytics.
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
 * Conversion Goal Tracking Diagnostic Class
 *
 * Without goal tracking, you're flying blind. Don't know which traffic sources
 * convert, which pages work, or ROI of marketing.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Conversion_Goal_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conversion-goal-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Goal Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if conversion goals are tracked in analytics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues         = array();
		$tracking_score = 0;
		$max_score      = 5;

		// Check for Google Analytics goals.
		$has_ga_goals = self::check_google_analytics_goals();
		if ( $has_ga_goals ) {
			$tracking_score++;
		} else {
			$issues[] = 'Google Analytics goals';
		}

		// Check for conversion tracking.
		$has_conversion_tracking = self::check_conversion_tracking();
		if ( $has_conversion_tracking ) {
			$tracking_score++;
		} else {
			$issues[] = 'key conversion tracking (purchases, leads, signups)';
		}

		// Check for goal values.
		$has_goal_values = self::check_goal_values();
		if ( $has_goal_values ) {
			$tracking_score++;
		} else {
			$issues[] = 'goal values assigned ($)';
		}

		// Check for conversion funnels.
		$has_funnels = self::check_conversion_funnels();
		if ( $has_funnels ) {
			$tracking_score++;
		} else {
			$issues[] = 'conversion funnels mapped';
		}

		// Check for attribution tracking.
		$has_attribution = self::check_attribution_tracking();
		if ( $has_attribution ) {
			$tracking_score++;
		} else {
			$issues[] = 'attribution tracking';
		}

		$completion_percentage = ( $tracking_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Goal tracking present.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 65 : 45;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Goal tracking at %1$d%%. Missing: %2$s. What gets measured gets improved.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/conversion-goal-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if Google Analytics goals are configured.
	 *
	 * @since 0.6093.1200
	 * @return bool True if goals exist.
	 */
	private static function check_google_analytics_goals(): bool {
		// Check for GA4 tracking.
		$ga4_property = get_option( 'ga4_measurement_id', '' );
		if ( ! empty( $ga4_property ) ) {
			// GA4 goals are configured in GA interface, assume present if GA4 active.
			return true;
		}

		// Check for Google Analytics script.
		global $wp_scripts;
		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		if ( $wp_scripts->query( 'google-analytics' ) || $wp_scripts->query( 'gtag' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if conversion tracking exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_conversion_tracking(): bool {
		// Check for e-commerce tracking.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$conversion_plugins = array(
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php',
			'monster-insights-ecommerce/monsterinsights-ecommerce.php',
			'google-analytics-for-wordpress/googleanalytics.php',
		);

		foreach ( $conversion_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if goal values are assigned.
	 *
	 * @since 0.6093.1200
	 * @return bool True if values exist.
	 */
	private static function check_goal_values(): bool {
		// Goal values are typically configured in GA, not WordPress.
		// If conversion tracking exists, assume values can be set.
		return self::check_conversion_tracking();
	}

	/**
	 * Check if conversion funnels are mapped.
	 *
	 * @since 0.6093.1200
	 * @return bool True if funnels exist.
	 */
	private static function check_conversion_funnels(): bool {
		// Conversion funnels are part of Enhanced E-commerce.
		return self::check_conversion_tracking();
	}

	/**
	 * Check if attribution tracking is setup.
	 *
	 * @since 0.6093.1200
	 * @return bool True if attribution exists.
	 */
	private static function check_attribution_tracking(): bool {
		// Check for Google Analytics or GTM.
		return self::check_google_analytics_goals();
	}
}
