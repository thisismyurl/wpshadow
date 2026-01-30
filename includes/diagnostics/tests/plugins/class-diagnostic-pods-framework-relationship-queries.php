<?php
/**
 * Pods Framework Relationship Queries Diagnostic
 *
 * Pods Framework Relationship Queries issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1054.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pods Framework Relationship Queries Diagnostic Class
 *
 * @since 1.1054.0000
 */
class Diagnostic_PodsFrameworkRelationshipQueries extends Diagnostic_Base {

	protected static $slug = 'pods-framework-relationship-queries';
	protected static $title = 'Pods Framework Relationship Queries';
	protected static $description = 'Pods Framework Relationship Queries issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Pods Framework
		$has_pods = function_exists( 'pods' ) || class_exists( 'Pods' );
		
		if ( ! $has_pods ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Relationship fields
		$relationships = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}podsrel"
			)
		);
		if ( $relationships > 10000 ) {
			$issues[] = sprintf( __( '%s relationships (slow queries)', 'wpshadow' ), number_format( $relationships ) );
		}
		
		// Check 2: Query caching
		$caching = get_option( 'pods_query_cache', 'no' );
		if ( 'no' === $caching ) {
			$issues[] = __( 'Query caching disabled (repeated queries)', 'wpshadow' );
		}
		
		// Check 3: Deep relationships
		$deep_rels = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%pods_relationship%'"
		);
		if ( $deep_rels > 5000 ) {
			$issues[] = sprintf( __( '%s deep relationships (join overhead)', 'wpshadow' ), number_format( $deep_rels ) );
		}
		
		// Check 4: Bidirectional relationships
		$bidirectional = get_option( 'pods_bidirectional', 'yes' );
		if ( 'yes' === $bidirectional ) {
			$issues[] = __( 'Bidirectional relationships (double writes)', 'wpshadow' );
		}
		
		// Check 5: Relationship indexing
		$indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->prefix}podsrel"
		);
		if ( count( $indexes ) < 3 ) {
			$issues[] = __( 'Missing indexes on relationships (slow lookups)', 'wpshadow' );
		}
		
		// Check 6: Orphaned relationships
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}podsrel r 
			 LEFT JOIN {$wpdb->posts} p ON r.item_id = p.ID 
			 WHERE p.ID IS NULL"
		);
		if ( $orphaned > 100 ) {
			$issues[] = sprintf( __( '%d orphaned relationships (cleanup needed)', 'wpshadow' ), $orphaned );
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
				__( 'Pods Framework has %d relationship query issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/pods-framework-relationship-queries',
		);
	}
}
