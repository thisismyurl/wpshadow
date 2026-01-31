<?php
/**
 * Custom Field Suite Performance Diagnostic
 *
 * Custom Field Suite Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1056.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Field Suite Performance Diagnostic Class
 *
 * @since 1.1056.0000
 */
class Diagnostic_CustomFieldSuitePerformance extends Diagnostic_Base {

	protected static $slug = 'custom-field-suite-performance';
	protected static $title = 'Custom Field Suite Performance';
	protected static $description = 'Custom Field Suite Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'CFS' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify field value caching
		$field_cache = get_option( 'cfs_enable_field_cache', false );
		if ( ! $field_cache ) {
			$issues[] = __( 'Custom field caching not enabled', 'wpshadow' );
		}

		// Check 2: Check query optimization for field groups
		$query_optimization = get_option( 'cfs_optimize_field_queries', false );
		if ( ! $query_optimization ) {
			$issues[] = __( 'Field query optimization not enabled', 'wpshadow' );
		}

		// Check 3: Verify field group limits
		$field_groups = get_option( 'cfs_field_groups', array() );
		if ( count( $field_groups ) > 50 ) {
			$issues[] = __( 'Excessive field groups may impact performance', 'wpshadow' );
		}

		// Check 4: Check conditional logic performance
		$conditional_cache = get_option( 'cfs_conditional_logic_cache', false );
		if ( ! $conditional_cache ) {
			$issues[] = __( 'Conditional logic caching not enabled', 'wpshadow' );
		}

		// Check 5: Verify repeater field limits
		$repeater_limit = get_option( 'cfs_repeater_field_limit', 0 );
		if ( $repeater_limit > 100 || $repeater_limit === 0 ) {
			$issues[] = __( 'Repeater field limit too high or unlimited', 'wpshadow' );
		}

		// Check 6: Check AJAX loading for large field sets
		$ajax_loading = get_option( 'cfs_ajax_field_loading', false );
		if ( ! $ajax_loading ) {
			$issues[] = __( 'AJAX loading not enabled for large field sets', 'wpshadow' );
		}
		return null;
	}
}
