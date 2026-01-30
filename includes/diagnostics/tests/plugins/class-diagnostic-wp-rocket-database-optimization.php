<?php
/**
 * WP Rocket Database Optimization Diagnostic
 *
 * WP Rocket database cleanup disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.440.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Database Optimization Diagnostic Class
 *
 * @since 1.440.0000
 */
class Diagnostic_WpRocketDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-database-optimization';
	protected static $title = 'WP Rocket Database Optimization';
	protected static $description = 'WP Rocket database cleanup disabled';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Database cleanup enabled
		$cleanup = get_option( 'wp_rocket_db_cleanup', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Database cleanup not enabled';
		}
		
		// Check 2: Revisions cleanup
		$revisions = get_option( 'wp_rocket_db_cleanup_revisions', 0 );
		if ( ! $revisions ) {
			$issues[] = 'Post revisions cleanup not configured';
		}
		
		// Check 3: Drafts cleanup
		$drafts = get_option( 'wp_rocket_db_cleanup_drafts', 0 );
		if ( ! $drafts ) {
			$issues[] = 'Draft posts cleanup not configured';
		}
		
		// Check 4: Spam comments cleanup
		$spam = get_option( 'wp_rocket_db_cleanup_spam_comments', 0 );
		if ( ! $spam ) {
			$issues[] = 'Spam comments cleanup not configured';
		}
		
		// Check 5: Transients cleanup
		$transients = get_option( 'wp_rocket_db_cleanup_transients', 0 );
		if ( ! $transients ) {
			$issues[] = 'Transients cleanup not configured';
		}
		
		// Check 6: Optimization schedule
		$schedule = get_option( 'wp_rocket_db_cleanup_schedule', '' );
		if ( empty( $schedule ) ) {
			$issues[] = 'Cleanup schedule not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d DB optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-database-optimization',
			);
		}
		
		return null;
	}
}
