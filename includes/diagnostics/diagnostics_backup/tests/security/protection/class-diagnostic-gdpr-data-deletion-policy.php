<?php
/**
 * GDPR Data Deletion Policy Diagnostic
 *
 * Verifies systems in place to permanently delete
 * customer data upon request (GDPR Article 17).
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GDPR_Data_Deletion_Policy Class
 *
 * Verifies GDPR data deletion procedures.
 *
 * @since 1.2601.2148
 */
class Diagnostic_GDPR_Data_Deletion_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-deletion-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Deletion Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies user data deletion capability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'protection';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if GDPR deletion not configured, null otherwise.
	 */
	public static function check() {
		$gdpr_status = self::check_gdpr_deletion_policy();

		if ( $gdpr_status['is_compliant'] ) {
			return null; // GDPR deletion configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No GDPR data deletion process configured. EU customer requests data deletion = you cannot comply = €20M fine or 4% revenue (whichever is higher). Implement immediately if serving EU.', 'wpshadow' ),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-data-deletion',
			'family'       => self::$family,
			'meta'         => array(
				'gdpr_deletion_configured' => false,
			),
			'details'      => array(
				'gdpr_data_deletion_requirements'   => array(
					'Article 17: Right to Erasure' => array(
						'Rule: User can demand deletion',
						'Timeline: 30 days to comply',
						'Scope: All personal data (not backups)',
						'Exception: Legal obligation to retain',
					),
					'Affected Data' => array(
						'Name, email: Must delete',
						'User ID: Often kept (anonymized)',
						'Order history: Can keep (legitimate interest)',
						'Payment data: Must delete',
					),
					'Process' => array(
						'Request: Via form or email',
						'Verify: Confirm identity',
						'Delete: All personal data',
						'Confirm: Send deletion confirmation',
					),
				),
				'wordpress_gdpr_tools'              => array(
					'Built-in: User Export/Delete Tool' => array(
						'Access: wp-admin → Tools → Erase personal data',
						'Function: Deletes user + associated data',
						'Hooks: Plugins register custom data',
					),
					'Plugin: GDPR for WordPress' => array(
						'Features: Consent management, deletion',
						'Cost: Free',
						'Scope: Comprehensive GDPR features',
					),
					'Plugin: DPO' => array(
						'Features: Deletion, consent, export',
						'Cost: Free',
						'Admin: Dashboard for data requests',
					),
				),
				'implementing_gdpr_deletion'        => array(
					'Create Policy' => array(
						'Document: How customers request deletion',
						'Process: Step-by-step for staff',
						'Timeline: 30 days from request',
						'Publish: On website (Privacy Policy)',
					),
					'Implement Tools' => array(
						'Install: GDPR plugin (above)',
						'Or: Use WordPress native (Tools → Erase data)',
						'Test: Request deletion for test user',
					),
					'Verify Completeness' => array(
						'Database: No user records remain',
						'Emails: Remove from email service',
						'Analytics: Anonymize user ID',
						'Backups: Note: Can retain backups',
					),
				),
				'common_gdpr_deletion_issues'       => array(
					'Email Service Not Deleted' => array(
						'Problem: User deleted from WP',
						'But: Email still in Mailchimp',
						'Fix: Set up Mailchimp auto-delete',
						'Or: Manual process via API',
					),
					'Customer Data in Old Backups' => array(
						'Issue: Deleted, but backup not aged out',
						'GDPR: Allows data in backups',
						'Action: Set old backups immutable',
					),
					'Analytics Still Tracks' => array(
						'Problem: User deleted from WP',
						'But: Google Analytics still sees them',
						'Fix: Anonymize in GA (hash user ID)',
					),
				),
				'data_to_include_in_deletion'      => array(
					'Website Data' => array(
						'User account: Delete',
						'Profile info: Delete',
						'User meta: Delete',
						'Posts by user: Keep or anonymize',
					),
					'Business Records' => array(
						'Orders: Anonymize (keep for tax)',
						'Comments: Delete or anonymize',
						'Support tickets: Anonymize',
					),
					'Third-Party Services' => array(
						'Email list: Delete from Mailchimp',
						'Analytics: Delete/anonymize user ID',
						'CRM: Delete contact record',
					),
				),
				'managing_deletion_requests'        => array(
					'Document Requests' => array(
						'Use form: Custom contact form',
						'Confirm: Email verification',
						'Track: Spreadsheet of requests',
					),
					'Process Workflow' => array(
						'1. Receive request (form/email)',
						'2. Verify identity (email + answer question)',
						'3. Mark for deletion (tag in system)',
						'4. Execute deletion',
						'5. Confirm to user (email)',
					),
					'Timeline' => array(
						'Receive: Day 1',
						'Verify: Day 1-2',
						'Delete: Day 3-30',
						'Confirm: Day 30',
					),
				),
			),
		);
	}

	/**
	 * Check GDPR deletion policy.
	 *
	 * @since  1.2601.2148
	 * @return array GDPR deletion status.
	 */
	private static function check_gdpr_deletion_policy() {
		$is_compliant = false;

		// Check if GDPR plugin active
		if ( is_plugin_active( 'gdpr/gdpr.php' ) || is_plugin_active( 'gdpr-for-wordpress/gdpr-for-wordpress.php' ) ) {
			$is_compliant = true;
		}

		// Check if privacy policy mentions deletion
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( ! empty( $privacy_page ) ) {
			// Has privacy policy (though may not mention deletion)
			// Conservative: don't mark as compliant without plugin
		}

		return array(
			'is_compliant' => $is_compliant,
		);
	}
}
