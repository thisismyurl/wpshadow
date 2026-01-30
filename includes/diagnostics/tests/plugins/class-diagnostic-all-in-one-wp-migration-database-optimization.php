<?php
/**
 * All-in-One WP Migration Database Diagnostic
 *
 * AIO WP Migration database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.391.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Database Diagnostic Class
 *
 * @since 1.391.0000
 */
class Diagnostic_AllInOneWpMigrationDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-database-optimization';
	protected static $title = 'All-in-One WP Migration Database';
	protected static $description = 'AIO WP Migration database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Database optimization before backup.
		$auto_optimize = get_option( 'ai1wm_auto_optimize_db', '0' );
		if ( '0' === $auto_optimize ) {
			$issues[] = 'database not optimized before backups (larger file sizes)';
		}
		
		// Check 2: Table overhead.
		global $wpdb;
		$overhead = $wpdb->get_results(
			"SELECT table_name, data_free FROM information_schema.tables 
			WHERE table_schema = DATABASE() AND data_free > 0",
			ARRAY_A
		);
		if ( ! empty( $overhead ) ) {
			$total_overhead = array_sum( wp_list_pluck( $overhead, 'data_free' ) );
			$overhead_mb = round( $total_overhead / 1048576, 2 );
			if ( $overhead_mb > 10 ) {
				$issues[] = "{$overhead_mb}MB database overhead (run OPTIMIZE TABLE)";
			}
		}
		
		// Check 3: Post revisions accumulation.
		$revision_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			)
		);
		if ( $revision_count > 1000 ) {
			$issues[] = "{$revision_count} post revisions (consider cleanup or limiting revisions)";
		}
		
		// Check 4: Transients cleanup.
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);
		if ( $expired_transients > 100 ) {
			$issues[] = "{$expired_transients} expired transients (bloating database)";
		}
		
		// Check 5: Autoload options size.
		$autoload_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);
		$autoload_mb = round( $autoload_size / 1048576, 2 );
		if ( $autoload_mb > 2 ) {
			$issues[] = "{$autoload_mb}MB autoload data (slows every page load)";
		}
		
		// Check 6: Spam comments.
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = %s",
				'spam'
			)
		);
		if ( $spam_count > 500 ) {
			$issues[] = "{$spam_count} spam comments (wasting database space)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One WP Migration database optimization issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-database-optimization',
			);
		}
		
		return null;
	}
}
