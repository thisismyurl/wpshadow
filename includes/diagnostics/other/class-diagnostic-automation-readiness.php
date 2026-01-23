<?php
declare(strict_types=1);
/**
 * Automation Readiness Diagnostic
 *
 * Philosophy: Identify opportunities for workflow automation
 * Guides to Pro workflow features and Guardian AI for predictive automation
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if site is ready for automation workflows.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Automation_Readiness extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$opportunities = array();

		// Check if WordPress cron is working
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$opportunities[] = 'WP-Cron is disabled - workflows may not trigger automatically without system cron';
		}

		// Check if REST API is available (for webhooks)
		$rest_enabled = rest_get_url_prefix();
		if ( empty( $rest_enabled ) ) {
			$opportunities[] = 'REST API is not available - webhook automations won\'t work';
		}

		// Check if there are manual, repetitive tasks that could be automated
		$activity_log = get_option( 'wpshadow_activity_log', array() );
		if ( is_array( $activity_log ) && count( $activity_log ) > 50 ) {
			// User has been active - check for patterns
			$actions_count = array();
			foreach ( $activity_log as $activity ) {
				if ( isset( $activity['action'] ) ) {
					$action                   = $activity['action'];
					$actions_count[ $action ] = ( $actions_count[ $action ] ?? 0 ) + 1;
				}
			}

			// Check if any action is repeated (candidate for automation)
			$repeated_actions = array_filter(
				$actions_count,
				function ( $count ) {
					return $count > 3;
				}
			);

			if ( ! empty( $repeated_actions ) ) {
				$most_repeated   = array_keys( $repeated_actions, max( $repeated_actions ) )[0];
				$opportunities[] = sprintf(
					'You\'ve performed "%s" %d times - automation could save time',
					$most_repeated,
					$repeated_actions[ $most_repeated ]
				);
			}
		}

		// Check for integration opportunities (email, Slack, etc.)
		$has_external_integrations = (
			function_exists( 'wp_mail' ) ||
			defined( 'WP_MAIL_SMTP' ) ||
			get_option( 'wpshadow_slack_enabled' )
		);

		if ( ! $has_external_integrations ) {
			$opportunities[] = 'No external service integrations configured - workflows could notify via email/Slack/webhooks';
		}

		if ( ! empty( $opportunities ) ) {
			return array(
				'id'           => 'automation-readiness',
				'title'        => 'Automation Opportunities Detected',
				'description'  => 'Your site could benefit from workflow automation: ' . implode( '. ', $opportunities ) . '. WPShadow Pro includes advanced automation rules.',
				'severity'     => 'low',
				'category'     => 'workflows',
				'kb_link'      => 'https://wpshadow.com/kb/workflow-automation/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=automation-readiness',
				'auto_fixable' => false,
				'threat_level' => 15,
			);
		}

		return null;
	}

}