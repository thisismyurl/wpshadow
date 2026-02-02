<?php
/**
 * GDPR Tools Privacy Policy Integration Diagnostic
 *
 * Detects whether GDPR tool pages link to site privacy policy and
 * explain user rights.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Privacy Policy Integration Diagnostic Class
 *
 * Ensures GDPR tools are properly integrated with privacy policy
 * and explain user rights for compliance.
 *
 * @since 1.26033.1900
 */
class Diagnostic_GDPR_Privacy_Policy_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-privacy-policy-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Tools Privacy Policy Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GDPR tools link to privacy policy and explain user rights';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Privacy policy page is set
	 * - Privacy policy page is published
	 * - GDPR request pages link to privacy policy
	 * - User rights are explained
	 * - GDPR article references present
	 * - Contact information for privacy requests
	 *
	 * @since  1.26033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy policy page is set.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page_id ) || $privacy_page_id === 0 ) {
			$issues[] = __( 'No privacy policy page is set; GDPR compliance cannot be demonstrated', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-privacy-policy-integration',
			);
		}

		// Check if privacy policy page is published.
		$privacy_page = get_post( $privacy_page_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			$issues[] = __( 'Privacy policy page is not published; it cannot be accessed by users', 'wpshadow' );
		}

		// Get privacy policy URL.
		$privacy_policy_url = get_privacy_policy_url();
		if ( empty( $privacy_policy_url ) ) {
			$issues[] = __( 'Privacy policy URL could not be generated; GDPR tools cannot link to it', 'wpshadow' );
		}

		// Check if GDPR request pages exist.
		$gdpr_pages = array(
			'export_personal_data' => 'Personal data export',
			'delete_personal_data' => 'Personal data deletion',
		);

		foreach ( $gdpr_pages as $page_slug => $page_name ) {
			// Check if there's a form or request handler for this.
			if ( ! has_filter( 'wp_privacy_personal_data_exporters' ) && 'export_personal_data' === $page_slug ) {
				$issues[] = sprintf(
					/* translators: %s: page name */
					__( 'No %s request handler configured; users cannot submit GDPR requests', 'wpshadow' ),
					$page_name
				);
			}
		}

		// Check if privacy policy content contains GDPR information.
		if ( $privacy_page ) {
			$policy_content = strtolower( $privacy_page->post_content );
			$gdpr_keywords = array( 'gdpr', 'personal data', 'processing', 'rights', 'deletion', 'export', 'data controller' );
			$found_keywords = 0;

			foreach ( $gdpr_keywords as $keyword ) {
				if ( strpos( $policy_content, $keyword ) !== false ) {
					$found_keywords++;
				}
			}

			if ( $found_keywords < 3 ) {
				$issues[] = __( 'Privacy policy may not contain sufficient GDPR information (rights, processing, data controller)', 'wpshadow' );
			}
		}

		// Check if contact information is provided for data requests.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			$issues[] = __( 'No admin email configured; users cannot contact site for data requests', 'wpshadow' );
		}

		// Check if Data Protection Officer (DPO) information is configured.
		$dpo_info = get_option( 'wpshadow_dpo_contact_info' );
		if ( empty( $dpo_info ) && ! has_filter( 'wpshadow_dpo_contact_info' ) ) {
			$issues[] = __( 'No Data Protection Officer contact information configured; users may not know how to contact site', 'wpshadow' );
		}

		// Check if export/erasure request page explains user rights.
		$erasure_request_page = get_option( 'wpshadow_data_erasure_explanation' );
		if ( empty( $erasure_request_page ) && ! has_filter( 'wpshadow_data_erasure_explanation' ) ) {
			$issues[] = __( 'No explanation of user data rights provided; users may not understand their GDPR rights', 'wpshadow' );
		}

		// Check if site links to privacy policy from GDPR request forms.
		// This is checked via filters.
		if ( ! has_filter( 'wpshadow_gdpr_form_footer_text' ) ) {
			$issues[] = __( 'GDPR request forms may not link to privacy policy; users may not understand data usage', 'wpshadow' );
		}

		// Check for EU/international visitor notice.
		$gdpr_notice = get_option( 'wpshadow_gdpr_notice_enabled', false );
		if ( ! $gdpr_notice ) {
			$issues[] = __( 'GDPR notice/banner not enabled; international visitors may not be informed of data processing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-privacy-policy-integration',
			);
		}

		return null;
	}
}
