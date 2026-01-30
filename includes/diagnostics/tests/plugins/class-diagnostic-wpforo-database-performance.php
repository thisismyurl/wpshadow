<?php
/**
 * wpForo Database Performance Diagnostic
 *
 * wpForo database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.534.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Database Performance Diagnostic Class
 *
 * @since 1.534.0000
 */
class Diagnostic_WpforoDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'wpforo-database-performance';
	protected static $title = 'wpForo Database Performance';
	protected static $description = 'wpForo database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Post count
		$post_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpforo_posts"
		);
		
		if ( $post_count > 50000 ) {
			$issues[] = sprintf( __( '%s posts (table optimization needed)', 'wpshadow' ), number_format( $post_count ) );
		}
		
		// Check 2: Database indexing
		$indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->prefix}wpforo_posts WHERE Key_name != 'PRIMARY'"
		);
		
		if ( count( $indexes ) < 3 ) {
			$issues[] = __( 'Insufficient indexes (slow queries)', 'wpshadow' );
		}
		
		// Check 3: Cache enabled
		$cache_enabled = get_option( 'wpforo_cache_enabled', 'yes' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Cache disabled (repeated queries)', 'wpshadow' );
		}
		
		// Check 4: Subscriptions
		$subscriptions = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpforo_subscribes"
		);
		
		if ( $subscriptions > 10000 ) {
			$issues[] = sprintf( __( '%s subscriptions (email overhead)', 'wpshadow' ), number_format( $subscriptions ) );
		}
		
		// Check 5: Attachment storage
		$attachments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpforo_attachment'"
		);
		
		if ( $attachments > 5000 ) {
			$issues[] = sprintf( __( '%s attachments (disk space)', 'wpshadow' ), number_format( $attachments ) );
		}
		
		// Check 6: Query logging
		$query_log = get_option( 'wpforo_query_log', 'no' );
		if ( 'yes' === $query_log ) {
			$issues[] = __( 'Query logging enabled (production overhead)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of database performance issues */
				__( 'wpForo has %d database performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wpforo-database-performance',
		);
	}
}
