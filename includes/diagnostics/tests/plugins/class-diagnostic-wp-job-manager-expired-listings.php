<?php
/**
 * WP Job Manager Expired Listings Diagnostic
 *
 * Expired job listings not cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.248.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Expired Listings Diagnostic Class
 *
 * @since 1.248.0000
 */
class Diagnostic_WpJobManagerExpiredListings extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-expired-listings';
	protected static $title = 'WP Job Manager Expired Listings';
	protected static $description = 'Expired job listings not cleaned up';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Expired jobs count
		$expired_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				 WHERE pm.meta_key = %s 
				 AND pm.meta_value < %s
				 AND p.post_type = %s
				 AND p.post_status = 'publish'",
				'_job_expires',
				current_time( 'Y-m-d' ),
				'job_listing'
			)
		);
		
		if ( $expired_count === 0 ) {
			return null;
		}
		
		// Check 2: Excessive expired listings
		if ( $expired_count > 50 ) {
			$issues[] = sprintf( __( '%d expired job listings still published', 'wpshadow' ), $expired_count );
		}
		
		// Check 3: Auto-delete expired listings
		$auto_delete = get_option( 'job_manager_delete_expired_jobs', 0 );
		$delete_days = get_option( 'job_manager_delete_expired_jobs_days', 30 );
		
		if ( ! $auto_delete && $expired_count > 20 ) {
			$issues[] = __( 'Auto-deletion of expired jobs not enabled', 'wpshadow' );
		} elseif ( $auto_delete && $delete_days > 90 ) {
			$issues[] = sprintf( __( 'Expired jobs kept for %d days (database bloat)', 'wpshadow' ), $delete_days );
		}
		
		// Check 4: Cleanup cron job
		$next_cleanup = wp_next_scheduled( 'job_manager_delete_old_previews' );
		if ( ! $next_cleanup ) {
			$issues[] = __( 'Cleanup cron job not scheduled', 'wpshadow' );
		}
		
		// Check 5: Old expired jobs (30+ days)
		$very_old = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				 WHERE pm.meta_key = %s 
				 AND pm.meta_value < DATE_SUB(%s, INTERVAL 30 DAY)
				 AND p.post_type = %s",
				'_job_expires',
				current_time( 'Y-m-d' ),
				'job_listing'
			)
		);
		
		if ( $very_old > 10 ) {
			$issues[] = sprintf( __( '%d jobs expired 30+ days ago (cleanup overdue)', 'wpshadow' ), $very_old );
		}
		
		
		// Check 6: Cache status
		if ( ! (defined( "WP_CACHE" ) && WP_CACHE) ) {
			$issues[] = __( 'Cache status', 'wpshadow' );
		}

		// Check 7: Database optimization
		if ( ! (! is_option_empty( "db_optimized" )) ) {
			$issues[] = __( 'Database optimization', 'wpshadow' );
		}

		// Check 8: Asset minification
		if ( ! (function_exists( "wp_enqueue_script" )) ) {
			$issues[] = __( 'Asset minification', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of expired listing issues */
				__( 'WP Job Manager expired listings have %d cleanup issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-expired-listings',
		);
	}
}
