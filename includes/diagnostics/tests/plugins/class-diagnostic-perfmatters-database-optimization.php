<?php
/**
 * Perfmatters Database Optimization Diagnostic
 *
 * Perfmatters Database Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.922.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perfmatters Database Optimization Diagnostic Class
 *
 * @since 1.922.0000
 */
class Diagnostic_PerfmattersDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'perfmatters-database-optimization';
	protected static $title = 'Perfmatters Database Optimization';
	protected static $description = 'Perfmatters Database Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Perfmatters
		$perfmatters_active = defined( 'PERFMATTERS_VERSION' ) || get_option( 'perfmatters_options' );
		if ( ! $perfmatters_active ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Auto DB optimization enabled
		$auto_optimize = get_option( 'perfmatters_database_schedule', false );
		if ( ! $auto_optimize ) {
			$issues[] = __( 'Automatic database optimization not scheduled', 'wpshadow' );
		}
		
		// Check 2: Post revisions
		$revision_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			)
		);
		
		if ( $revision_count > 500 ) {
			$issues[] = sprintf( __( '%d post revisions (database bloat)', 'wpshadow' ), $revision_count );
		}
		
		// Check 3: Transients cleanup
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				 WHERE option_name LIKE %s
				 AND CAST(option_value AS UNSIGNED) < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);
		
		if ( $expired_transients > 100 ) {
			$issues[] = sprintf( __( '%d expired transients (cleanup needed)', 'wpshadow' ), $expired_transients );
		}
		
		// Check 4: Spam/trashed comments
		$trash_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved IN (%s, %s)",
				'spam',
				'trash'
			)
		);
		
		if ( $trash_count > 100 ) {
			$issues[] = sprintf( __( '%d spam/trashed comments (permanent cleanup recommended)', 'wpshadow' ), $trash_count );
		}
		
		// Check 5: Orphaned post meta
		$orphan_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE p.ID IS NULL"
		);
		
		if ( $orphan_meta > 50 ) {
			$issues[] = sprintf( __( '%d orphaned post meta entries', 'wpshadow' ), $orphan_meta );
		}
		
		// Check 6: Autoloaded data size
		$autoload_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = %s",
				'yes'
			)
		);
		
		if ( $autoload_size > 1000000 ) { // 1MB
			$issues[] = sprintf( __( 'Autoloaded data: %s (performance impact)', 'wpshadow' ), size_format( $autoload_size ) );
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
				/* translators: %s: list of database optimization issues */
				__( 'Perfmatters database optimization has %d opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/perfmatters-database-optimization',
		);
	}
}
