<?php
/**
 * Heap Analytics Session Replay Diagnostic
 *
 * Heap Analytics Session Replay misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1391.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heap Analytics Session Replay Diagnostic Class
 *
 * @since 1.1391.0000
 */
class Diagnostic_HeapAnalyticsSessionReplay extends Diagnostic_Base {

	protected static $slug = 'heap-analytics-session-replay';
	protected static $title = 'Heap Analytics Session Replay';
	protected static $description = 'Heap Analytics Session Replay misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'heap_analytics_tracking_code' ) && ! defined( 'HEAP_ANALYTICS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify App ID is configured
		$app_id = get_option( 'heap_analytics_app_id', '' );
		if ( empty( $app_id ) ) {
			$issues[] = 'Heap Analytics App ID not configured';
		}
		
		// Check 2: Check session replay privacy settings
		$privacy_mode = get_option( 'heap_session_replay_privacy', '' );
		if ( empty( $privacy_mode ) ) {
			$issues[] = 'Session replay privacy settings not configured';
		}
		
		// Check 3: Verify sensitive data masking
		$data_masking = get_option( 'heap_data_masking', 0 );
		if ( ! $data_masking ) {
			$issues[] = 'Sensitive data masking not enabled';
		}
		
		// Check 4: Check for GDPR compliance settings
		$gdpr_mode = get_option( 'heap_gdpr_compliance', 0 );
		if ( ! $gdpr_mode ) {
			$issues[] = 'GDPR compliance mode not enabled';
		}
		
		// Check 5: Verify user consent tracking
		$consent_tracking = get_option( 'heap_consent_tracking', 0 );
		if ( ! $consent_tracking ) {
			$issues[] = 'User consent tracking not configured';
		}
		
		// Check 6: Check for IP anonymization
		$ip_anonymization = get_option( 'heap_ip_anonymization', 0 );
		if ( ! $ip_anonymization && $gdpr_mode ) {
			$issues[] = 'IP anonymization not enabled';
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
					'Found %d Heap Analytics session replay issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/heap-analytics-session-replay',
			);
		}
		
		return null;
	}
}
