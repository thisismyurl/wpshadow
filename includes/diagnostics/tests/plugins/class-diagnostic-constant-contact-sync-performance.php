<?php
/**
 * Constant Contact Sync Performance Diagnostic
 *
 * Constant Contact Sync Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.722.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constant Contact Sync Performance Diagnostic Class
 *
 * @since 1.722.0000
 */
class Diagnostic_ConstantContactSyncPerformance extends Diagnostic_Base {

	protected static $slug = 'constant-contact-sync-performance';
	protected static $title = 'Constant Contact Sync Performance';
	protected static $description = 'Constant Contact Sync Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ConstantContact' ) && ! get_option( 'constant_contact_api_key', '' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify sync interval
		$sync_interval = get_option( 'constant_contact_sync_interval', 0 );
		if ( $sync_interval <= 0 ) {
			$issues[] = 'Sync interval not configured';
		}
		
		// Check 2: Check for batch size limits
		$batch_size = get_option( 'constant_contact_batch_size', 0 );
		if ( $batch_size > 500 ) {
			$issues[] = 'Batch size too large (over 500)';
		}
		
		// Check 3: Verify API rate limiting
		$rate_limit = get_option( 'constant_contact_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not configured';
		}
		
		// Check 4: Check for sync logging
		$sync_logging = get_option( 'constant_contact_sync_logging', 0 );
		if ( $sync_logging ) {
			$issues[] = 'Sync logging enabled (performance impact)';
		}
		
		// Check 5: Verify retry backoff
		$retry_backoff = get_option( 'constant_contact_retry_backoff', 0 );
		if ( ! $retry_backoff ) {
			$issues[] = 'Retry backoff not configured';
		}
		
		// Check 6: Check for sync queue cleanup
		$queue_cleanup = get_option( 'constant_contact_queue_cleanup', 0 );
		if ( ! $queue_cleanup ) {
			$issues[] = 'Sync queue cleanup not enabled';
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
					'Found %d Constant Contact sync issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/constant-contact-sync-performance',
			);
		}
		
		return null;
	}
}
