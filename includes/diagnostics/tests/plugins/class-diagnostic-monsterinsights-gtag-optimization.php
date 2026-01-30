<?php
/**
 * MonsterInsights gtag.js Optimization Diagnostic
 *
 * MonsterInsights loading multiple tracking scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.233.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights gtag.js Optimization Diagnostic Class
 *
 * @since 1.233.0000
 */
class Diagnostic_MonsterinsightsGtagOptimization extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-gtag-optimization';
	protected static $title = 'MonsterInsights gtag.js Optimization';
	protected static $description = 'MonsterInsights loading multiple tracking scripts';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Single gtag instance
		$single_gtag = get_option( 'mi_single_gtag_instance_enabled', 0 );
		if ( ! $single_gtag ) {
			$issues[] = 'Single gtag instance not enforced';
		}
		
		// Check 2: Script optimization
		$script_opt = get_option( 'mi_gtag_script_optimization_enabled', 0 );
		if ( ! $script_opt ) {
			$issues[] = 'gtag.js optimization not enabled';
		}
		
		// Check 3: Deferred loading
		$deferred = get_option( 'mi_gtag_deferred_loading_enabled', 0 );
		if ( ! $deferred ) {
			$issues[] = 'Deferred gtag loading not enabled';
		}
		
		// Check 4: Duplicate tracking prevention
		$dup_prev = get_option( 'mi_duplicate_tracking_prevention_enabled', 0 );
		if ( ! $dup_prev ) {
			$issues[] = 'Duplicate tracking prevention not enabled';
		}
		
		// Check 5: Async loading
		$async = get_option( 'mi_gtag_async_loading_enabled', 0 );
		if ( ! $async ) {
			$issues[] = 'Async gtag loading not enabled';
		}
		
		// Check 6: Conflicting tracking removal
		$conflict = get_option( 'mi_conflicting_tracking_check_enabled', 0 );
		if ( ! $conflict ) {
			$issues[] = 'Conflicting tracking not checked';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d tracking optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-gtag-optimization',
			);
		}
		
		return null;
	}
}
