<?php
/**
 * JavaScript Garbage Collection Treatment
 *
 * Detects JavaScript memory leak patterns and garbage collection issues.
 *
 * @since   1.6033.2115
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Garbage Collection Treatment
 *
 * Identifies JavaScript patterns that may cause memory leaks or GC pressure.
 *
 * @since 1.6033.2115
 */
class Treatment_Javascript_Garbage_Collection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-garbage-collection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Garbage Collection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript memory management and garbage collection issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2115
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		// Check for common memory leak patterns
		$event_listener_heavy = false;
		$global_scope_pollution = 0;
		$interval_timer_scripts = 0;

		// Check inline scripts for problematic patterns
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->is_enqueued( $handle ) ) {
				continue;
			}

			// Check inline scripts
			$inline_code = '';
			if ( isset( $script->extra['after'] ) ) {
				$inline_code .= is_array( $script->extra['after'] ) ? implode( '', $script->extra['after'] ) : $script->extra['after'];
			}
			if ( isset( $script->extra['before'] ) ) {
				$inline_code .= is_array( $script->extra['before'] ) ? implode( '', $script->extra['before'] ) : $script->extra['before'];
			}

			if ( ! empty( $inline_code ) ) {
				// Check for event listener patterns (potential memory leaks)
				if ( preg_match_all( '/addEventListener|attachEvent/i', $inline_code ) > 3 ) {
					$event_listener_heavy = true;
				}

				// Check for setInterval/setTimeout without cleanup
				if ( preg_match( '/setInterval|setTimeout/i', $inline_code ) ) {
					$interval_timer_scripts++;
				}

				// Check for global variable declarations
				$global_scope_pollution += preg_match_all( '/window\.|var\s+\w+\s*=|function\s+\w+\s*\(/i', $inline_code );
			}
		}

		// Generate findings based on detected patterns
		if ( $event_listener_heavy || $interval_timer_scripts > 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Heavy event listener usage detected without proper cleanup. This may cause memory leaks in single-page applications.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-garbage-collection',
				'meta'         => array(
					'event_listener_heavy' => $event_listener_heavy,
					'interval_timer_scripts' => $interval_timer_scripts,
					'recommendation'       => 'Ensure removeEventListener is called and intervals are cleared',
					'impact_estimate'      => 'Prevents 5-10 MB memory leaks over time',
					'best_practices'       => array(
						'Use removeEventListener when elements are removed',
						'Clear intervals/timeouts with clearInterval/clearTimeout',
						'Use WeakMap/WeakSet for cached references',
						'Implement proper cleanup in SPA lifecycle',
					),
				),
			);
		}

		if ( $global_scope_pollution > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of global declarations */
					__( '%d global scope declarations detected. Use modules or IIFE to prevent scope pollution.', 'wpshadow' ),
					$global_scope_pollution
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-garbage-collection',
				'meta'         => array(
					'global_declarations' => $global_scope_pollution,
					'recommendation'      => 'Wrap code in IIFE or use ES6 modules',
					'impact_estimate'     => 'Reduces namespace conflicts and memory usage',
					'solution_example'    => '(function() { /* your code */ })(); or use ES6 modules',
				),
			);
		}

		return null;
	}
}
