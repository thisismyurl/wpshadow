<?php
/**
 * Ninja Forms Conditional Logic Performance Diagnostic
 *
 * Ninja Forms Conditional Logic Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1188.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms Conditional Logic Performance Diagnostic Class
 *
 * @since 1.1188.0000
 */
class Diagnostic_NinjaFormsConditionalLogicPerformance extends Diagnostic_Base {

	protected static $slug = 'ninja-forms-conditional-logic-performance';
	protected static $title = 'Ninja Forms Conditional Logic Performance';
	protected static $description = 'Ninja Forms Conditional Logic Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Conditional logic caching
		$cache = get_option( 'nf_conditional_logic_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Conditional logic caching not enabled';
		}
		
		// Check 2: Rule evaluation optimization
		$eval_opt = get_option( 'nf_rule_evaluation_optimization_enabled', 0 );
		if ( ! $eval_opt ) {
			$issues[] = 'Rule evaluation optimization not enabled';
		}
		
		// Check 3: JavaScript optimization
		$js_opt = get_option( 'nf_conditional_js_optimization_enabled', 0 );
		if ( ! $js_opt ) {
			$issues[] = 'Conditional JS optimization not enabled';
		}
		
		// Check 4: Debouncing enabled
		$debounce = get_option( 'nf_conditional_debouncing_enabled', 0 );
		if ( ! $debounce ) {
			$issues[] = 'Conditional debouncing not enabled';
		}
		
		// Check 5: Rule complexity audit
		$complexity = get_option( 'nf_rule_complexity_audit_enabled', 0 );
		if ( ! $complexity ) {
			$issues[] = 'Rule complexity audit not enabled';
		}
		
		// Check 6: Performance monitoring
		$monitor = get_option( 'nf_conditional_performance_monitoring', 0 );
		if ( ! $monitor ) {
			$issues[] = 'Performance monitoring not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-forms-conditional-logic-performance',
			);
		}
		
		return null;
	}
}
