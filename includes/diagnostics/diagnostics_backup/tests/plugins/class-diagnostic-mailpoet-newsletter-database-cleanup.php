<?php
/**
 * Mailpoet Newsletter Database Cleanup Diagnostic
 *
 * Mailpoet Newsletter Database Cleanup configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.713.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Database Cleanup Diagnostic Class
 *
 * @since 1.713.0000
 */
class Diagnostic_MailpoetNewsletterDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-database-cleanup';
	protected static $title = 'Mailpoet Newsletter Database Cleanup';
	protected static $description = 'Mailpoet Newsletter Database Cleanup configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify automatic cleanup is enabled
		$auto_cleanup = get_option( 'mailpoet_auto_cleanup', 0 );
		if ( ! $auto_cleanup ) {
			$issues[] = 'Automatic database cleanup not enabled';
		}

		// Check 2: Check for old sent newsletter cleanup
		$cleanup_sent = get_option( 'mailpoet_cleanup_sent_newsletters', 0 );
		if ( ! $cleanup_sent ) {
			$issues[] = 'Old sent newsletters not being cleaned up';
		}

		// Check 3: Verify inactive subscriber cleanup
		$cleanup_inactive = get_option( 'mailpoet_cleanup_inactive_subscribers', 0 );
		if ( ! $cleanup_inactive ) {
			$issues[] = 'Inactive subscriber cleanup not configured';
		}

		// Check 4: Check for orphaned queue data
		$cleanup_queue = get_option( 'mailpoet_cleanup_queue', 0 );
		if ( ! $cleanup_queue ) {
			$issues[] = 'Queue cleanup not enabled';
		}

		// Check 5: Verify statistics retention period
		$stats_retention = get_option( 'mailpoet_stats_retention_days', 0 );
		if ( $stats_retention <= 0 || $stats_retention > 365 ) {
			$issues[] = 'Statistics retention period not properly configured';
		}

		// Check 6: Check for scheduled cleanup task
		$cleanup_schedule = wp_next_scheduled( 'mailpoet_cleanup' );
		if ( ! $cleanup_schedule ) {
			$issues[] = 'Scheduled cleanup task not configured';
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
					'Found %d MailPoet database cleanup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-database-cleanup',
			);
		}

		return null;
	}
}
