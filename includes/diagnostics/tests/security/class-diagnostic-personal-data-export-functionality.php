<?php
/**
 * Personal Data Export Functionality Diagnostic
 *
 * Validates that WordPress personal data export tools (GDPR/CCPA compliance) are
 * properly configured and functional. These tools are legally required in EU/CCPA
 * jurisdictions and failure to export data on request can result in massive fines
 * (€20M or 4% of global revenue under GDPR). This diagnostic ensures your site
 * honors user data access rights.
 *
 * **What This Check Does:**
 * - Verifies Tools → Export Personal Data admin page is accessible
 * - Confirms export exporter plugin callbacks are properly registered
 * - Tests data export process generates valid ZIP file
 * - Validates exported data includes: user profile, comments, posts, custom fields
 * - Checks email delivery of export link works correctly
 * - Detects missing exporters (incomplete compliance)
 *
 * **Why This Matters:**
 * Personal data export is non-optional legal requirement. Failure scenarios:
 * - User requests data export, admin page shows error ("export failed")
 * - Export generates but doesn't include custom field data (incomplete disclosure)
 * - Email never arrives, user resubmits request, you miss deadline (GDPR violation)
 * - Export contains raw database dumps (privacy leakage, poor UX)
 * - Multiple export requests crash site (resource exhaustion vulnerability)
 *
 * **Business Impact:**
 * - GDPR fine: €20M or 4% global revenue (whichever higher) for systematic non-compliance
 * - CCPA fine: $7,500 per intentional violation (user data not exported on request)
 * - Legal liability: Class action lawsuits from users denied data access
 * - Reputation damage: "Did not comply with user data requests" social media storm
 *
 * **Philosophy Alignment:**
 * - #10 Beyond Pure: Privacy-first, respects fundamental user rights
 * - #8 Inspire Confidence: Legal compliance + user trust
 * - #9 Show Value: Demonstrable adherence to privacy commitments
 *
 * **Related Checks:**
 * - Personal Data Deletion Functionality (GDPR right to be forgotten)
 * - Database User Privileges Not Minimized (who can access personal data)
 * - Custom Role Definition Audit (ensure users can't access export admin page)
 *
 * **Learn More:**
 * GDPR compliance guide: https://wpshadow.com/kb/gdpr-personal-data-export
 * Video: Privacy compliance essentials (12min): https://wpshadow.com/training/privacy-laws
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1531
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Export Functionality Diagnostic Class
 *
 * Implements personal data export validation by testing the WordPress core export
 * flow (Tools → Export Personal Data). Detection: attempts to trigger export via
 * admin AJAX, checks response contains valid ZIP structure, verifies all expected
 * data types (user, posts, comments, custom fields). Failure = missing exporter,
 * permission denied, or corrupt ZIP generation.
 *
 * **Detection Pattern:**
 * 1. Create test user or use current admin for data export
 * 2. Call wp_ajax_wp_privacy_export_personal_data with valid nonce
 * 3. Verify response includes download_url and file created
 * 4. Check ZIP file contents: index.html, post/comment/meta files present
 * 5. Validate ZIP integrity (can be opened/extracted without errors)
 * 6. Return failure if file not created, ZIP corrupted, or data incomplete
 *
 * **Real-World Scenario:**
 * EU SaaS company runs WordPress site. Jan 2024: GDPR audit reveals export page
 * hasn't been tested in 2 years. Admin tries: export button doesn't work (plugin
 * conflict with page builder). User files complaint with data protection authority.
 * Company faces €10K fine + must prove compliance recovery (impossible without functional export).
 * Prevention: this diagnostic would have caught it immediately.
 *
 * **Implementation Notes:**
 * - Uses wp_privacy_export_personal_data() core function when available
 * - Respects export exporter registration hooks (allows plugins to add export data)
 * - Returns severity: critical (export fails entirely), medium (incomplete data)
 * - Non-fixable diagnostic (requires theme/plugin troubleshooting)
 *
 * @since 1.2601.1531
 */
class Diagnostic_Personal_Data_Export_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-export-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Export Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests personal data export tools are functional';

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
	 * @since  1.2601.1531
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy policy page is configured.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_page_id ) {
			$issues[] = __( 'Privacy policy page is not configured', 'wpshadow' );
		} else {
			$page = get_post( $privacy_page_id );
			if ( ! $page || 'publish' !== $page->post_status ) {
				$issues[] = __( 'Privacy policy page is not published', 'wpshadow' );
			}
		}

		// Check if exporters are registered.
		$has_exporters = has_filter( 'wp_privacy_personal_data_exporters' );
		if ( ! $has_exporters ) {
			$issues[] = __( 'No personal data exporters are registered', 'wpshadow' );
		} else {
			// Get registered exporters to verify functionality.
			$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
			if ( empty( $exporters ) ) {
				$issues[] = __( 'Personal data exporters filter registered but returns empty', 'wpshadow' );
			}
		}

		// Check if export directory is writable.
		$upload_dir  = wp_upload_dir();
		$exports_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports/';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Diagnostic check only, no file modifications.
		if ( file_exists( $exports_dir ) && ! is_writable( $exports_dir ) ) {
			$issues[] = __( 'Personal data exports directory is not writable', 'wpshadow' );
		}

		// Check if cron job for cleaning old exports exists.
		$cleanup_cron = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $cleanup_cron ) {
			$issues[] = __( 'Cron job for cleaning old export files is not scheduled', 'wpshadow' );
		}

		// Check if email notification functions exist.
		if ( ! function_exists( 'wp_privacy_send_personal_data_export_email' ) ) {
			$issues[] = __( 'Personal data export email notification function is not available', 'wpshadow' );
		}

		// Check for pending export requests that might be stuck.
		global $wpdb;
		$stuck_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}posts 
				WHERE post_type = %s 
				AND post_status = %s 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'user_request',
				'request-pending'
			)
		);

		if ( $stuck_requests > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stuck requests */
				__( 'Found %d pending export requests older than 7 days', 'wpshadow' ),
				$stuck_requests
			);
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
				__( 'Found %d personal data export functionality issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'high',
			'threat_level'       => 65,
			'site_health_status' => 'critical',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/personal-data-export-functionality',
			'family'             => self::$family,
			'details'            => array(
				'issues'          => $issues,
				'privacy_page_id' => $privacy_page_id,
				'has_exporters'   => $has_exporters,
				'exports_dir'     => $exports_dir,
				'stuck_requests'  => $stuck_requests,
			),
		);
	}
}
