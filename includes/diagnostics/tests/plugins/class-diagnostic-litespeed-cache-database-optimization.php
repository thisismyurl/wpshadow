<?php
/**
 * Litespeed Cache Database Optimization Diagnostic
 *
 * Litespeed Cache Database Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.902.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Database Optimization Diagnostic Class
 *
 * @since 1.902.0000
 */
class Diagnostic_LitespeedCacheDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-database-optimization';
	protected static $title = 'Litespeed Cache Database Optimization';
	protected static $description = 'Litespeed Cache Database Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Database optimization enabled
		$db_opt = get_option( 'litespeed_db_optimization', 0 );
		if ( ! $db_opt ) {
			$issues[] = 'Database optimization not enabled';
		}

		// Check 2: Table optimization schedule
		$table_opt_schedule = get_option( 'litespeed_table_optimize_schedule', '' );
		if ( empty( $table_opt_schedule ) ) {
			$issues[] = 'Table optimization schedule not configured';
		}

		// Check 3: Cleanup revisions
		$cleanup_revs = get_option( 'litespeed_cleanup_revisions', 0 );
		if ( ! $cleanup_revs ) {
			$issues[] = 'Post revision cleanup not enabled';
		}

		// Check 4: Cleanup draft posts
		$cleanup_drafts = get_option( 'litespeed_cleanup_drafts', 0 );
		if ( ! $cleanup_drafts ) {
			$issues[] = 'Draft post cleanup not enabled';
		}

		// Check 5: Cleanup spam comments
		$cleanup_spam = get_option( 'litespeed_cleanup_spam_comments', 0 );
		if ( ! $cleanup_spam ) {
			$issues[] = 'Spam comment cleanup not enabled';
		}

		// Check 6: Transient cleanup
		$cleanup_transients = get_option( 'litespeed_cleanup_transients', 0 );
		if ( ! $cleanup_transients ) {
			$issues[] = 'Expired transient cleanup not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d LiteSpeed DB optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-database-optimization',
			);
		}

		return null;
	}
}
