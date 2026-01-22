<?php
/**
 * Diagnostic Scheduler + Performance Impact Integration Code Examples
 * 
 * This file shows exact code snippets for integrating Performance_Impact_Classifier
 * into Diagnostic_Scheduler.
 * 
 * COPY-PASTE READY: Each section can be directly applied to class-diagnostic-scheduler.php
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ============================================================================
 * SECTION 1: Update Schedule Definitions with Impact Metadata
 * ============================================================================
 * 
 * ADD to get_default_schedules() return array:
 * For each diagnostic, add two new keys:
 * - 'impact'   => Performance_Impact_Classifier::IMPACT_*
 * - 'guardian' => Performance_Impact_Classifier::GUARDIAN_*
 */

// BEFORE (current):
$example_old = [
	'ssl' => [
		'frequency'  => 86400,
		'triggers'   => [],
		'priority'   => 'critical',
		'background' => true,
	],
];

// AFTER (with impact):
$example_new = [
	'ssl' => [
		'frequency'  => 86400,
		'triggers'   => [],
		'priority'   => 'critical',
		'background' => true,
		
		// NEW: Performance prediction
		'impact'     => Performance_Impact_Classifier::IMPACT_MEDIUM,
		'guardian'   => Performance_Impact_Classifier::GUARDIAN_BACKGROUND,
	],
];

/**
 * ============================================================================
 * SECTION 2: Add Helper Method - Check if Now is Optimal Time
 * ============================================================================
 * 
 * ADD to Diagnostic_Scheduler class:
 */

class Diagnostic_Scheduler_Example_Integration {

	/**
	 * Determine if NOW is good time to run a diagnostic based on impact
	 * 
	 * This method answers: "Should I defer this diagnostic to a better time?"
	 * 
	 * @param string $guardian Guardian suitability context
	 * @param string $impact   Impact level
	 * @return bool            True if now is optimal time
	 */
	protected static function is_optimal_time_to_run( string $guardian, string $impact ): bool {
		// ANYTIME diagnostics are always safe
		if ( $guardian === Performance_Impact_Classifier::GUARDIAN_ANYTIME ) {
			return true;
		}

		// BACKGROUND jobs can run if not in direct user request
		if ( $guardian === Performance_Impact_Classifier::GUARDIAN_BACKGROUND ) {
			// Only run if this is a background task (not blocking user)
			$is_background_context = (
				( defined( 'DOING_CRON' ) && DOING_CRON ) ||           // Scheduled cron
				( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||           // AJAX request
				( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||   // Autosave
				wp_doing_ajax() ||                                     // Alternative check
				wp_is_json_request()                                   // REST request
			);
			
			return $is_background_context;
		}

		// SCHEDULED diagnostics only run during low-traffic window (2-6 AM UTC)
		if ( $guardian === Performance_Impact_Classifier::GUARDIAN_SCHEDULED ) {
			$hour = intval( gmdate( 'H' ) );
			return $hour >= 2 && $hour < 6;
		}

		// MANUAL diagnostics never auto-run
		if ( $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL ) {
			return false;
		}

		return false;
	}

	/**
	 * ========================================================================
	 * SECTION 3: Update should_run() Method
	 * ========================================================================
	 * 
	 * MODIFY existing should_run() method to add impact checking:
	 */

	/**
	 * Determine if a diagnostic should run now
	 * 
	 * Considers:
	 * 1. Frequency (has enough time passed?)
	 * 2. Impact (is this a good time for this diagnostic?)
	 * 3. Triggers (did any trigger event occur?)
	 * 
	 * @param string $slug              Diagnostic slug
	 * @param int    $now               Current timestamp (default: now)
	 * @param bool   $respect_impact    Consider impact when deciding (default: true)
	 * @return bool                     True if diagnostic should run
	 */
	public static function should_run( 
		string $slug, 
		int $now = 0, 
		bool $respect_impact = true 
	): bool {
		if ( empty( $now ) ) {
			$now = time();
		}

		// Get diagnostic configuration
		$definition = self::$schedule_definitions[ $slug ] ?? null;
		if ( ! $definition ) {
			return false;
		}

		// Step 1: Check if enough time has passed since last run
		$last_run = self::get_last_run( $slug );
		$frequency = $definition['frequency'] ?? 0;

		if ( $now - $last_run < $frequency ) {
			return false; // Not enough time passed
		}

		// Step 2: Check if any trigger event forces immediate run
		$triggers = $definition['triggers'] ?? [];
		if ( ! empty( $triggers ) && self::trigger_active( $slug, $triggers ) ) {
			return true; // Trigger overrides timing
		}

		// Step 3: NEW - Check if now is optimal time based on impact
		if ( $respect_impact ) {
			$impact = $definition['impact'] ?? null;
			$guardian = $definition['guardian'] ?? null;

			if ( $impact && $guardian ) {
				if ( ! self::is_optimal_time_to_run( $guardian, $impact ) ) {
					return false; // Defer to better time
				}
			}
		}

		return true;
	}

	/**
	 * ========================================================================
	 * SECTION 4: Add Method - Get Next Suitable Batch
	 * ========================================================================
	 * 
	 * ADD to Diagnostic_Scheduler class:
	 * This method returns diagnostics suitable for the CURRENT TIME/CONTEXT
	 */

	/**
	 * Get batch of diagnostics suitable for execution RIGHT NOW
	 * 
	 * This intelligently selects diagnostics that:
	 * - Are ready to run (frequency satisfied)
	 * - Have acceptable impact for current time
	 * - Don't cause server spike
	 * 
	 * @param int $batch_size Maximum diagnostics to return
	 * @return array           Array of diagnostic slugs
	 */
	public static function get_suitable_batch_now( int $batch_size = 5 ): array {
		$suitable = [];
		$now = time();

		// Determine current context
		$hour = intval( gmdate( 'H' ) );
		$minute = intval( gmdate( 'i' ) );
		$is_peak_hours = $hour >= 9 && $hour < 17;      // 9 AM - 5 PM
		$is_off_peak = $hour >= 2 && $hour < 6;         // 2-6 AM
		$is_background = wp_doing_ajax() || wp_is_json_request();

		// Iterate through all diagnostics
		foreach ( self::$schedule_definitions as $slug => $definition ) {
			if ( count( $suitable ) >= $batch_size ) {
				break;
			}

			// Check frequency
			$last_run = self::get_last_run( $slug );
			$frequency = $definition['frequency'] ?? 0;

			if ( $now - $last_run < $frequency ) {
				continue; // Not ready yet
			}

			// Get impact metadata
			$impact = $definition['impact'] ?? null;
			$guardian = $definition['guardian'] ?? null;

			if ( ! $impact || ! $guardian ) {
				continue; // Skip unclassified diagnostics
			}

			// Filter by current context
			if ( $is_peak_hours ) {
				// During peak hours, only run ANYTIME diagnostics
				if ( $guardian !== Performance_Impact_Classifier::GUARDIAN_ANYTIME ) {
					continue;
				}
			} elseif ( $is_background ) {
				// During AJAX/background, can run ANYTIME + BACKGROUND
				if ( ! in_array(
					$guardian,
					[
						Performance_Impact_Classifier::GUARDIAN_ANYTIME,
						Performance_Impact_Classifier::GUARDIAN_BACKGROUND,
					],
					true
				) ) {
					continue;
				}
			} elseif ( $is_off_peak ) {
				// During 2-6 AM, can run anything except MANUAL
				if ( $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL ) {
					continue;
				}
			}

			// This diagnostic is safe to run now
			$suitable[] = $slug;
		}

		return $suitable;
	}

	/**
	 * ========================================================================
	 * SECTION 5: Add Method - Get Diagnostic with Impact Details
	 * ========================================================================
	 * 
	 * ADD to Diagnostic_Scheduler class:
	 * Returns complete diagnostic info including impact prediction
	 */

	/**
	 * Get diagnostic details for dashboard display (with impact prediction)
	 * 
	 * @param string $slug Diagnostic slug
	 * @return array       Diagnostic details including impact
	 */
	public static function get_diagnostic_with_impact( string $slug ): array {
		$definition = self::$schedule_definitions[ $slug ] ?? null;

		if ( ! $definition ) {
			return [
				'slug'   => $slug,
				'status' => 'not_found',
			];
		}

		// Get impact prediction from classifier
		$prediction = Performance_Impact_Classifier::predict( $slug );

		// Get timing info
		$last_run = self::get_last_run( $slug );
		$frequency = $definition['frequency'] ?? 0;
		$next_run = $last_run + $frequency;
		$now = time();

		// Determine if can run now
		$can_run_now = self::should_run( $slug, $now, true );

		// Build display array
		return [
			'slug'                   => $slug,
			'name'                   => $definition['name'] ?? self::format_slug_as_name( $slug ),
			'frequency_seconds'      => $frequency,
			'frequency_readable'     => self::format_frequency( $frequency ),
			'last_run_timestamp'     => $last_run,
			'last_run_readable'      => self::format_timestamp_relative( $last_run ),
			'next_run_timestamp'     => $next_run,
			'next_run_readable'      => self::format_timestamp_relative( $next_run ),
			'priority'               => $definition['priority'] ?? 'medium',
			
			// Impact information
			'impact_level'           => $prediction['impact_level'] ?? 'unknown',
			'estimated_ms'           => $prediction['estimated_ms'] ?? 0,
			'estimated_seconds'      => round( ( $prediction['estimated_ms'] ?? 0 ) / 1000, 1 ),
			'guardian_context'       => $prediction['guardian_suitable'] ?? 'unknown',
			'impact_label'           => Performance_Impact_Classifier::get_impact_label(
				$prediction['impact_level'] ?? 'medium'
			),
			'impact_description'     => $prediction['description'] ?? '',
			
			// Execution status
			'can_run_now'            => $can_run_now,
			'run_decision'           => self::get_run_decision_reason( $slug ),
			'trigger_active'         => self::trigger_active(
				$slug,
				$definition['triggers'] ?? []
			),
			'trigger_reason'         => self::get_trigger_reason(
				$slug,
				$definition['triggers'] ?? []
			),
		];
	}

	/**
	 * Get human-readable reason for run decision
	 * 
	 * @param string $slug Diagnostic slug
	 * @return string      Explanation of why diagnostic should/shouldn't run
	 */
	public static function get_run_decision_reason( string $slug ): string {
		$definition = self::$schedule_definitions[ $slug ] ?? null;

		if ( ! $definition ) {
			return 'Diagnostic configuration not found';
		}

		// Get impact metadata
		$impact = $definition['impact'] ?? null;
		$guardian = $definition['guardian'] ?? null;

		if ( ! $impact || ! $guardian ) {
			return 'No impact classification assigned';
		}

		// Check frequency first
		$last_run = self::get_last_run( $slug );
		$frequency = $definition['frequency'] ?? 0;
		$time_until_ready = $frequency - ( time() - $last_run );

		if ( $time_until_ready > 0 ) {
			return sprintf(
				'%s. Not yet ready (in %s)',
				Performance_Impact_Classifier::get_guardian_explanation( $guardian ),
				self::format_seconds_as_duration( $time_until_ready )
			);
		}

		// Check if optimal time
		$is_optimal = self::is_optimal_time_to_run( $guardian, $impact );

		if ( $is_optimal ) {
			return sprintf(
				'✅ Ready to run now. %s',
				Performance_Impact_Classifier::get_guardian_explanation( $guardian )
			);
		} else {
			// Explain why deferred
			$hour = intval( gmdate( 'H' ) );
			$explanation = Performance_Impact_Classifier::get_guardian_explanation( $guardian );

			if ( $guardian === Performance_Impact_Classifier::GUARDIAN_SCHEDULED ) {
				$next_window = sprintf(
					'Next optimal window: 2-6 AM UTC. Currently: %02d:00 UTC',
					$hour
				);
			} elseif ( $guardian === Performance_Impact_Classifier::GUARDIAN_BACKGROUND ) {
				$next_window = 'Waiting for background context (AJAX/cron)';
			} else {
				$next_window = 'Deferred to better time';
			}

			return sprintf(
				'⏱️ Deferred. %s. %s',
				$explanation,
				$next_window
			);
		}
	}

	/**
	 * ========================================================================
	 * SECTION 6: Add Method - Get Statistics
	 * ========================================================================
	 * 
	 * ADD to Diagnostic_Scheduler class:
	 * Returns scheduler statistics for dashboard
	 */

	/**
	 * Get scheduler statistics for dashboard display
	 * 
	 * @return array Statistics about scheduled diagnostics
	 */
	public static function get_scheduler_stats(): array {
		$now = time();
		$total = 0;
		$ready = 0;
		$deferred = 0;
		$total_impact_ms = 0;
		$by_context = [
			'anytime'     => 0,
			'background'  => 0,
			'scheduled'   => 0,
			'manual'      => 0,
		];

		foreach ( self::$schedule_definitions as $slug => $definition ) {
			$total++;

			// Get impact
			$impact = $definition['impact'] ?? null;
			$guardian = $definition['guardian'] ?? null;

			if ( $guardian ) {
				$by_context[ str_replace( 'guardian_', '', $guardian ) ]++;
			}

			// Check if ready
			$last_run = self::get_last_run( $slug );
			$frequency = $definition['frequency'] ?? 0;

			if ( time() - $last_run >= $frequency ) {
				if ( self::is_optimal_time_to_run( $guardian, $impact ) ) {
					$ready++;
				} else {
					$deferred++;
				}
			}

			// Get predicted impact
			$prediction = Performance_Impact_Classifier::predict( $slug );
			$total_impact_ms += $prediction['estimated_ms'] ?? 0;
		}

		return [
			'total_diagnostics'           => $total,
			'ready_to_run'                => $ready,
			'deferred_pending_time'       => $deferred,
			'total_estimated_time_ms'     => $total_impact_ms,
			'total_estimated_time_sec'    => round( $total_impact_ms / 1000, 1 ),
			'by_guardian_context'         => $by_context,
			'next_batch_suitable'         => count( self::get_suitable_batch_now( 999 ) ),
		];
	}

	/**
	 * ========================================================================
	 * SECTION 7: Helper Methods (copy these too)
	 * ========================================================================
	 */

	protected static function format_slug_as_name( string $slug ): string {
		return ucwords( str_replace( [ '-', '_' ], ' ', $slug ) );
	}

	protected static function format_frequency( int $seconds ): string {
		if ( $seconds < 60 ) {
			return "$seconds seconds";
		}
		if ( $seconds < 3600 ) {
			return round( $seconds / 60 ) . ' minutes';
		}
		if ( $seconds < 86400 ) {
			return round( $seconds / 3600 ) . ' hours';
		}
		return round( $seconds / 86400 ) . ' days';
	}

	protected static function format_seconds_as_duration( int $seconds ): string {
		if ( $seconds < 60 ) {
			return "$seconds seconds";
		}
		if ( $seconds < 3600 ) {
			return round( $seconds / 60 ) . ' minutes';
		}
		if ( $seconds < 86400 ) {
			$hours = floor( $seconds / 3600 );
			$mins = floor( ( $seconds % 3600 ) / 60 );
			return "$hours hours, $mins minutes";
		}
		$days = floor( $seconds / 86400 );
		$hours = floor( ( $seconds % 86400 ) / 3600 );
		return "$days days, $hours hours";
	}

	protected static function format_timestamp_relative( int $timestamp ): string {
		$diff = $timestamp - time();

		if ( $diff < 0 ) {
			return abs( $diff ) . ' seconds ago';
		}

		if ( $diff < 60 ) {
			return 'in ' . $diff . ' seconds';
		}

		if ( $diff < 3600 ) {
			return 'in ' . round( $diff / 60 ) . ' minutes';
		}

		if ( $diff < 86400 ) {
			return 'in ' . round( $diff / 3600 ) . ' hours';
		}

		return 'in ' . round( $diff / 86400 ) . ' days';
	}

	protected static function trigger_active( string $slug, array $triggers ): bool {
		// Implementation: check if any trigger condition is met
		// Example: if plugin was recently installed, TRIGGER_PLUGIN_CHANGE fires
		return false; // Simplified - implement based on your trigger system
	}

	protected static function get_trigger_reason( string $slug, array $triggers ): string {
		// Implementation: explain which trigger fired
		return '';  // Simplified
	}

	protected static function get_last_run( string $slug ): int {
		// Implementation: retrieve from database/cache
		return 0;   // Simplified
	}
}

/**
 * ============================================================================
 * EXAMPLE USAGE IN CODE
 * ============================================================================
 */

// Get next diagnostic suitable for right now
$suitable = Diagnostic_Scheduler::get_suitable_batch_now( 5 );
// Returns: ['admin-email', 'admin-username', 'ssl', ...]

// Get detailed info for display
$diagnostic = Diagnostic_Scheduler::get_diagnostic_with_impact( 'outdated-plugins' );
// Returns: [
//     'slug' => 'outdated-plugins',
//     'estimated_ms' => 742,
//     'estimated_seconds' => 0.7,
//     'impact_level' => 'high',
//     'guardian_context' => 'scheduled',
//     'can_run_now' => false,
//     'run_decision' => '⏱️ Deferred. Scheduled job only, run during off-peak hours. Next optimal window: 2-6 AM UTC. Currently: 14:00 UTC',
// ]

// Get scheduler statistics
$stats = Diagnostic_Scheduler::get_scheduler_stats();
// Returns: [
//     'total_diagnostics' => 69,
//     'ready_to_run' => 3,
//     'deferred_pending_time' => 5,
//     'total_estimated_time_ms' => 45000,
//     'by_guardian_context' => [
//         'anytime' => 12,
//         'background' => 15,
//         'scheduled' => 38,
//         'manual' => 4,
//     ],
// ]

// Check if should run now (respects impact)
if ( Diagnostic_Scheduler::should_run( 'outdated-plugins' ) ) {
	// Safe to run this diagnostic
}

// Check if should run now (ignores impact - for testing)
if ( Diagnostic_Scheduler::should_run( 'outdated-plugins', time(), false ) ) {
	// Would run if it's time, regardless of impact
}
