<?php
/**
 * Wp Fastest Cache Database Cleanup Diagnostic
 *
 * Wp Fastest Cache Database Cleanup not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.940.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Fastest Cache Database Cleanup Diagnostic Class
 *
 * @since 1.940.0000
 */
class Diagnostic_WpFastestCacheDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'wp-fastest-cache-database-cleanup';
	protected static $title = 'Wp Fastest Cache Database Cleanup';
	protected static $description = 'Wp Fastest Cache Database Cleanup not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WpFastestCache' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify database cleanup is enabled
		$cleanup_enabled = get_option( 'WpFastestCacheDBClean', false );
		if ( ! $cleanup_enabled ) {
			$issues[] = __( 'Database cleanup not enabled', 'wpshadow' );
		}

		// Check 2: Check post revisions cleanup
		$revisions_limit = get_option( 'WpFastestCacheRevisions', -1 );
		if ( $revisions_limit < 0 ) {
			$issues[] = __( 'Post revisions cleanup not configured', 'wpshadow' );
		}

		// Check 3: Verify auto-drafts deletion
		$autodrafts_cleanup = get_option( 'WpFastestCacheAutodrafts', false );
		if ( ! $autodrafts_cleanup ) {
			$issues[] = __( 'Auto-drafts cleanup not enabled', 'wpshadow' );
		}

		// Check 4: Check transient cleanup
		$transients_cleanup = get_option( 'WpFastestCacheTransients', false );
		if ( ! $transients_cleanup ) {
			$issues[] = __( 'Transient cleanup not enabled', 'wpshadow' );
		}

		// Check 5: Verify orphaned post metadata cleanup
		$orphaned_meta = get_option( 'WpFastestCacheOrphanedMeta', false );
		if ( ! $orphaned_meta ) {
			$issues[] = __( 'Orphaned metadata cleanup not enabled', 'wpshadow' );
		}

		// Check 6: Check database optimization schedule
		$optimization_schedule = wp_get_schedule( 'wpfc_database_optimization' );
		if ( false === $optimization_schedule ) {
			$issues[] = __( 'Database optimization not scheduled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WP Fastest Cache database cleanup issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wp-fastest-cache-database-cleanup',
			);
		}

		return null;
	}
}
