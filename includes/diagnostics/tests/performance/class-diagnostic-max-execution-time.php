<?php
/**
 * Diagnostic: PHP Max Execution Time Configuration
 *
 * Checks if PHP max execution time allows long-running operations to complete.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Max_Execution_Time Class
 *
 * Detects if PHP max execution time is too restrictive. Operations that
 * commonly need extended execution time include:
 *
 * - Database backups and exports
 * - Plugin and theme updates
 * - WooCommerce inventory imports
 * - Media library optimization
 * - Bulk post edits
 * - Cron job processing
 *
 * If the execution time is too low, these operations will timeout and fail,
 * leaving the site in an inconsistent state.
 *
 * Returns different threat levels based on execution time configuration.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Max_Execution_Time extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'max-execution-time';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'PHP Max Execution Time';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP execution time allows long-running operations to complete';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP max_execution_time and compares against thresholds:
	 * - Below 30 seconds: Medium priority (may timeout during operations)
	 * - 30-60 seconds: Acceptable but tight for large operations
	 * - 60+ seconds: Good (optimal for most sites)
	 *
	 * Note: A value of 0 means unlimited time (safe), which occurs in CLI mode.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if execution time is too low, null if adequate.
	 */
	public static function check() {
		$max_exec_time = (int) ini_get( 'max_execution_time' );

		// 0 means unlimited (safe) - often happens in CLI mode
		if ( 0 === $max_exec_time ) {
			return null;
		}

		// High: Below 30 seconds (very restrictive)
		if ( $max_exec_time < 30 ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current execution time, 2: recommended minimum */
					esc_html__( 'Your PHP max execution time is %1$d seconds, which is too low for long-running operations. Updates, backups, and bulk imports may timeout. We recommend at least %2$d seconds.', 'wpshadow' ),
					$max_exec_time,
					30
				),
				'severity'           => 'high',
				'threat_level'       => 65,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-max-execution-time',
				'family'             => self::$family,
				'details'            => array(
					'current_seconds'     => $max_exec_time,
					'minimum_recommended' => 30,
					'optimal_seconds'     => 60,
					'recommendation'      => 'Contact hosting provider to increase to 60+ seconds',
				),
			);
		}

		// Medium: Between 30-60 seconds (acceptable but tight)
		if ( $max_exec_time < 60 ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current execution time, 2: optimal time */
					esc_html__( 'Your PHP max execution time is %1$d seconds. While this meets basic requirements, %2$d seconds or higher is recommended for reliable operation during large updates or imports.', 'wpshadow' ),
					$max_exec_time,
					60
				),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-max-execution-time',
				'family'             => self::$family,
				'details'            => array(
					'current_seconds'     => $max_exec_time,
					'minimum_recommended' => 30,
					'optimal_seconds'     => 60,
					'recommendation'      => 'Consider requesting increase to 60+ seconds from hosting provider',
				),
			);
		}

		// All good - execution time is adequate
		return null;
	}
}
