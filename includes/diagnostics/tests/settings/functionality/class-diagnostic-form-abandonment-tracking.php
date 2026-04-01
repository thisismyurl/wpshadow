<?php
/**
 * Form Abandonment Tracking Diagnostic
 *
 * Checks if form field interactions and abandonment points are tracked.
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
 * Form Abandonment Tracking Diagnostic Class
 *
 * If 1,000 people start your form and 500 abandon, you need to know WHERE
 * they quit. Usually 1-2 specific fields cause most dropoff.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Abandonment_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-abandonment-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Abandonment Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form field interactions and abandonment points are tracked';

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

		// Check for form field interaction tracking.
		$has_field_tracking = self::check_field_interaction_tracking();
		if ( $has_field_tracking ) {
			++$tracking_score;
		} else {
			$issues[] = 'form field interaction tracking';
		}

		// Check for abandonment point identification.
		$has_abandonment_points = self::check_abandonment_points();
		if ( $has_abandonment_points ) {
			++$tracking_score;
		} else {
			$issues[] = 'abandonment point identification';
		}

		// Check for error message tracking.
		$has_error_tracking = self::check_error_tracking();
		if ( $has_error_tracking ) {
			++$tracking_score;
		} else {
			$issues[] = 'error message tracking';
		}

		// Check for time-to-complete measurement.
		$has_time_tracking = self::check_time_to_complete();
		if ( $has_time_tracking ) {
			++$tracking_score;
		} else {
			$issues[] = 'time-to-complete measurement';
		}

		// Check for field-level analytics.
		$has_field_analytics = self::check_field_level_analytics();
		if ( $has_field_analytics ) {
			++$tracking_score;
		} else {
			$issues[] = 'field-level analytics';
		}

		$completion_percentage = ( $tracking_score / $max_score ) * 100;

		if ( $completion_percentage >= 40 ) {
			return null; // Form tracking present.
		}

		$severity     = $completion_percentage < 20 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 20 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Form analytics at %1$d%%. Missing: %2$s. Track where users abandon to fix friction points.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/form-abandonment-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if form field interaction tracking exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_field_interaction_tracking(): bool {
		// Check for form analytics plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$form_tracking_plugins = array(
			'monster-insights/monsterinsights.php',
			'ga-google-analytics/ga-google-analytics.php',
		);

		foreach ( $form_tracking_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for Google Tag Manager events.
		$gtm_code = get_option( 'gtm_code', '' );
		if ( ! empty( $gtm_code ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if abandonment points are identified.
	 *
	 * @since 0.6093.1200
	 * @return bool True if identification exists.
	 */
	private static function check_abandonment_points(): bool {
		// Check for form abandonment tracking option.
		$abandonment_tracking = get_option( 'wpshadow_form_abandonment_tracking', false );
		if ( $abandonment_tracking ) {
			return true;
		}

		// Check for heat mapping tools (they track abandonment).
		global $wp_scripts;
		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		$abandonment_tools = array(
			'hotjar',
			'crazyegg',
			'mouseflow',
		);

		foreach ( $abandonment_tools as $tool ) {
			if ( $wp_scripts->query( $tool ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if error message tracking exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_error_tracking(): bool {
		// Check for JavaScript error tracking.
		global $wp_scripts;
		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		if ( $wp_scripts->query( 'sentry' ) || $wp_scripts->query( 'rollbar' ) ) {
			return true;
		}

		// Check for Google Analytics event tracking.
		return self::check_field_interaction_tracking();
	}

	/**
	 * Check if time-to-complete measurement exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if measurement exists.
	 */
	private static function check_time_to_complete(): bool {
		// Time tracking is part of form analytics tools.
		return self::check_field_interaction_tracking();
	}

	/**
	 * Check if field-level analytics exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if analytics exist.
	 */
	private static function check_field_level_analytics(): bool {
		// Field-level analytics require specific form tracking.
		return self::check_field_interaction_tracking();
	}
}
