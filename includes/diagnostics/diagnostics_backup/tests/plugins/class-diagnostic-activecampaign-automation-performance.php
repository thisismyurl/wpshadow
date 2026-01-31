<?php
/**
 * Activecampaign Automation Performance Diagnostic
 *
 * Activecampaign Automation Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.729.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activecampaign Automation Performance Diagnostic Class
 *
 * @since 1.729.0000
 */
class Diagnostic_ActivecampaignAutomationPerformance extends Diagnostic_Base {

	protected static $slug = 'activecampaign-automation-performance';
	protected static $title = 'Activecampaign Automation Performance';
	protected static $description = 'Activecampaign Automation Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ActiveCampaign' ) && ! defined( 'ACTIVECAMPAIGN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Automation trigger frequency.
		$trigger_frequency = get_option( 'activecampaign_trigger_frequency', 'immediate' );
		if ( 'immediate' === $trigger_frequency ) {
			$issues[] = 'automations trigger immediately (batch processing recommended for high-traffic sites)';
		}

		// Check 2: Pending automation queue size.
		global $wpdb;
		$pending_automations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = %s",
				'activecampaign_automation_%',
				'pending'
			)
		);
		if ( $pending_automations > 100 ) {
			$issues[] = "{$pending_automations} automations pending (queue backing up)";
		}

		// Check 3: Failed automation executions.
		$failed_automations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = %s",
				'activecampaign_automation_%',
				'failed'
			)
		);
		if ( $failed_automations > 0 ) {
			$issues[] = "{$failed_automations} failed automation executions";
		}

		// Check 4: Automation processing timeout.
		$timeout = get_option( 'activecampaign_automation_timeout', 30 );
		if ( $timeout > 60 ) {
			$issues[] = "automation timeout set to {$timeout}s (may slow page loads)";
		}

		// Check 5: Synchronous vs asynchronous processing.
		$async_processing = get_option( 'activecampaign_async_automations', '0' );
		if ( '0' === $async_processing ) {
			$issues[] = 'automations running synchronously (blocks page requests)';
		}

		// Check 6: Automation logs cleanup.
		$old_logs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %s",
				'activecampaign_automation_log_%',
				date( 'Y-m-d', strtotime( '-30 days' ) )
			)
		);
		if ( $old_logs > 500 ) {
			$issues[] = "{$old_logs} old automation logs (cleanup recommended)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ActiveCampaign automation performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/activecampaign-automation-performance',
			);
		}

		return null;
	}
}
