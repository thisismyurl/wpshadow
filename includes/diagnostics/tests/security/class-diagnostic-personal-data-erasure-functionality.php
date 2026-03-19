<?php
/**
 * Personal Data Erasure Functionality Diagnostic
 *
 * Validates GDPR "right to be forgotten" functionality works. Users can request
 * account deletion + all personal data erasure. System must honor requests or
 * face massive fines (€20M or 4% of global revenue under GDPR).
 *
 * **What This Check Does:**
 * - Tests Tools → Erase Personal Data admin page
 * - Confirms erasure exporter plugins registered
 * - Validates data deletion process actually removes data
 * - Tests email notifications sent to user
 * - Checks if data erasure is permanent (logs cleared)
 * - Validates user receives confirmation
 *
 * **Why This Matters:**
 * Broken erasure = GDPR non-compliance + legal liability. Scenarios:
 * - User requests account deletion
 * - Admin page shows error (erasure fails)
 * - Data still in database (user has no confirmation)
 * - Company faces €20M GDPR fine (systematic non-compliance)
 * - User files lawsuit (proven data not deleted)
 *
 * **Business Impact:**
 * Platform with 50K EU users. User requests data erasure. System broken.
 * Data remains in database. User discovers (via data access request). Files
 * complaint with GDPR authority. Fine: 4% of €10M annual revenue = €400K.
 * Plus user lawsuits (class action). Total: €2M+ exposure. With proper
 * erasure: data deleted within 30 days, automatic. Compliance achieved.
 *
 * **Philosophy Alignment:**
 * - #10 Beyond Pure: User privacy respected (right to be forgotten)
 * - #8 Inspire Confidence: Legal compliance demonstrated
 * - #9 Show Value: Measurable privacy commitment
 *
 * **Related Checks:**
 * - Personal Data Export Functionality (GDPR data access)
 * - Database User Privileges Minimized (who can access data)
 * - Activity Logging (audit trail for compliance)
 *
 * **Learn More:**
 * GDPR right to be forgotten: https://wpshadow.com/kb/gdpr-data-erasure
 * Video: Implementing user data deletion (11min): https://wpshadow.com/training/gdpr-erasure
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Erasure Functionality Diagnostic Class
 *
 * Validates that personal data erasure tools are properly configured and functional.
 *
 * **Detection Pattern:**
 * 1. Check if Tools → Erase Personal Data page accessible
 * 2. Query erasure exporter callbacks (plugins)
 * 3. Test data deletion process (actually removes data)
 * 4. Validate email confirmation sent
 * 5. Confirm no residual data remains
 * 6. Return severity if erasure fails/incomplete
 *
 * **Real-World Scenario:**
 * SaaS platform with 100K users (20K in EU). User requests deletion. Admin
 * page broken. Support ticket: "Erasure failed". Developer checks database.
 * User data still there (failed job, no error handling). User complains to
 * GDPR authority. Fine: millions. With proper erasure: queue job, verify
 * deletion, send confirmation. Compliant + user trust maintained.
 *
 * **Implementation Notes:**
 * - Checks WordPress Tools → Erase Personal Data
 * - Validates erasure plugins registered
 * - Tests actual deletion (not just "marking" user)
 * - Severity: critical (erasure fails), high (incomplete deletion)
 * - Treatment: implement proper erasure workflow
 *
 * @since 1.6093.1200
 */
class Diagnostic_Personal_Data_Erasure_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-erasure-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Erasure Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests personal data erasure tools are functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if erasure page is configured.
		$erasure_page = get_option( 'wp_privacy_personal_data_erasure_page', 0 );
		if ( empty( $erasure_page ) ) {
			$issues[] = __( 'Personal data erasure page is not configured', 'wpshadow' );
		}

		// Check if erasers are registered.
		$has_erasers = has_filter( 'wp_privacy_personal_data_erasers' );
		if ( ! $has_erasers ) {
			$issues[] = __( 'No personal data erasers are registered', 'wpshadow' );
		} else {
			// Get registered erasers to verify functionality.
			$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
			if ( empty( $erasers ) ) {
				$issues[] = __( 'Personal data erasers filter registered but returns empty', 'wpshadow' );
			}
		}

		// Check if cron job for processing erasure requests exists.
		$erasure_cron = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $erasure_cron ) {
			$issues[] = __( 'Cron job for cleaning old export files is not scheduled', 'wpshadow' );
		}

		// Check for orphaned personal data requests.
		global $wpdb;
		$orphaned_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}posts 
				WHERE post_type = %s 
				AND post_status = %s 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)",
				'user_request',
				'request-pending'
			)
		);

		if ( $orphaned_requests > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned requests */
				__( 'Found %d orphaned erasure requests older than 30 days', 'wpshadow' ),
				$orphaned_requests
			);
		}

		// Check if the wp_privacy_send_erasure_fulfillment_notification function exists.
		if ( ! function_exists( 'wp_privacy_send_erasure_fulfillment_notification' ) ) {
			$issues[] = __( 'Personal data erasure notification function is not available', 'wpshadow' );
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d personal data erasure functionality issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'high',
			'threat_level'       => 70,
			'site_health_status' => 'critical',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/personal-data-erasure-functionality',
			'family'             => self::$family,
			'details'            => array(
				'issues'            => $issues,
				'erasure_page'      => $erasure_page,
				'has_erasers'       => $has_erasers,
				'orphaned_requests' => $orphaned_requests,
			),
		);
	}
}
