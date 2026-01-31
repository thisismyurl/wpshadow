<?php
/**
 * Custom Field Suite Loop Detection Diagnostic
 *
 * Custom Field Suite Loop Detection issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1057.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Field Suite Loop Detection Diagnostic Class
 *
 * @since 1.1057.0000
 */
class Diagnostic_CustomFieldSuiteLoopDetection extends Diagnostic_Base {

	protected static $slug = 'custom-field-suite-loop-detection';
	protected static $title = 'Custom Field Suite Loop Detection';
	protected static $description = 'Custom Field Suite Loop Detection issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Custom Field Suite or similar ACF-like plugins
		$has_cfs = class_exists( 'CFS' ) || 
		           function_exists( 'cfs_get' ) ||
		           class_exists( 'ACF' );
		
		if ( ! $has_cfs ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Relationship fields (loop risk)
		$relationship_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE meta_key LIKE %s",
				'%_relationship%'
			)
		);
		
		if ( $relationship_fields > 100 ) {
			$issues[] = sprintf( __( '%d relationship fields (loop risk)', 'wpshadow' ), $relationship_fields );
		}
		
		// Check 2: Nested field groups
		$nested_groups = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s AND post_content LIKE %s",
				'cfs',
				'%parent%'
			)
		);
		
		if ( $nested_groups > 20 ) {
			$issues[] = sprintf( __( '%d nested field groups (complexity)', 'wpshadow' ), $nested_groups );
		}
		
		// Check 3: Loop detection enabled
		$loop_detection = get_option( 'cfs_loop_detection', 'off' );
		if ( 'off' === $loop_detection ) {
			$issues[] = __( 'Loop detection disabled (infinite recursion possible)', 'wpshadow' );
		}
		
		// Check 4: Field caching
		$cache_enabled = get_option( 'cfs_cache_enabled', 'yes' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Field caching disabled (repeated queries)', 'wpshadow' );
		}
		
		// Check 5: Bi-directional relationships
		$bidirectional = get_option( 'cfs_bidirectional_relationships', 'yes' );
		if ( 'yes' === $bidirectional ) {
			$issues[] = __( 'Bi-directional relationships (circular reference risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of loop detection issues */
				__( 'Custom Field Suite has %d loop detection issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/custom-field-suite-loop-detection',
		);
	}
}
