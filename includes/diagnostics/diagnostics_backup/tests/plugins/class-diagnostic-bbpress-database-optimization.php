<?php
/**
 * bbPress Database Optimization Diagnostic
 *
 * bbPress database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.512.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Database Optimization Diagnostic Class
 *
 * @since 1.512.0000
 */
class Diagnostic_BbpressDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'bbpress-database-optimization';
	protected static $title = 'bbPress Database Optimization';
	protected static $description = 'bbPress database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Database optimization.
		$db_optimized = get_option( 'bbpress_db_optimized', '0' );
		if ( '0' === $db_optimized ) {
			$issues[] = 'database indexes not optimized';
		}

		// Check 2: Auto cleanup.
		$cleanup_enabled = get_option( 'bbpress_auto_cleanup', '0' );
		if ( '0' === $cleanup_enabled ) {
			$issues[] = 'automatic cleanup disabled';
		}

		// Check 3: Spam cleanup frequency.
		$spam_freq = get_option( 'bbpress_spam_cleanup_frequency', 'monthly' );
		if ( 'monthly' === $spam_freq || 'never' === $spam_freq ) {
			$issues[] = 'spam not cleaned frequently';
		}

		// Check 4: Archive old content.
		$archive_enabled = get_option( 'bbpress_archive_old_topics', '0' );
		if ( '0' === $archive_enabled ) {
			$issues[] = 'old content not archived';
		}

		// Check 5: Query monitoring.
		$monitor_enabled = get_option( 'bbpress_query_monitor', '0' );
		if ( '0' === $monitor_enabled ) {
			$issues[] = 'query monitoring disabled';
		}

		// Check 6: Caching.
		$cache_enabled = get_option( 'bbpress_enable_caching', '1' );
		if ( '0' === $cache_enabled ) {
			$issues[] = 'internal caching disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 55 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'bbPress database issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-database-optimization',
			);
		}

		return null;
	}
}
