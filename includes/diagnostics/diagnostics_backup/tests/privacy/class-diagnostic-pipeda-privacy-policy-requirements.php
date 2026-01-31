<?php
/**
 * PIPEDA Privacy Policy Requirements Diagnostic
 *
 * Ensures privacy policy meets PIPEDA transparency requirements under Principle 8 (Openness).
 * Policy must be readily available, understandable, and identify privacy officer.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PIPEDA Privacy Policy Requirements Diagnostic Class
 *
 * PIPEDA Principle 8 requires organizations to be open about policies and practices.
 * Privacy information must be readily available and easy to understand.
 *
 * @since 1.6032.1530
 */
class Diagnostic_Pipeda_Privacy_Policy_Requirements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pipeda-privacy-policy-requirements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PIPEDA Privacy Policy Requirements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure privacy policy meets PIPEDA transparency requirements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1530
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check if privacy policy exists
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy_page_id ) {
			$issues[] = 'no_privacy_policy';
			return self::create_finding( $issues );
		}
		
		$privacy_page = get_post( $privacy_page_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			$issues[] = 'privacy_policy_not_published';
			return self::create_finding( $issues );
		}
		
		$content = strtolower( $privacy_page->post_content );
		$word_count = str_word_count( wp_strip_all_tags( $privacy_page->post_content ) );
		
		// Check for PIPEDA-specific requirements
		
		// 1. Plain language check (simple heuristic)
		$legal_jargon = array( 'herein', 'therein', 'aforementioned', 'heretofore', 'pursuant' );
		$jargon_count = 0;
		foreach ( $legal_jargon as $term ) {
			if ( stripos( $content, $term ) !== false ) {
				$jargon_count++;
			}
		}
		
		if ( $jargon_count > 2 ) {
			$issues[] = 'excessive_legal_jargon';
		}
		
		// 2. Check for purposes disclosure
		$has_purposes = stripos( $content, 'purpose' ) !== false &&
					   ( stripos( $content, 'collect' ) !== false || stripos( $content, 'use' ) !== false );
		
		if ( ! $has_purposes ) {
			$issues[] = 'no_purpose_disclosure';
		}
		
		// 3. Check for data types listed
		$data_types = array( 'email', 'name', 'address', 'phone', 'personal information', 'data' );
		$data_types_found = 0;
		foreach ( $data_types as $type ) {
			if ( stripos( $content, $type ) !== false ) {
				$data_types_found++;
			}
		}
		
		if ( $data_types_found < 2 ) {
			$issues[] = 'insufficient_data_type_disclosure';
		}
		
		// 4. Check for third-party disclosure
		$has_third_party = stripos( $content, 'third part' ) !== false ||
						  stripos( $content, 'service provider' ) !== false ||
						  stripos( $content, 'share' ) !== false;
		
		if ( ! $has_third_party ) {
			$issues[] = 'no_third_party_disclosure';
		}
		
		// 5. Check for retention policy
		$has_retention = stripos( $content, 'retention' ) !== false ||
						stripos( $content, 'how long' ) !== false ||
						stripos( $content, 'keep your' ) !== false;
		
		if ( ! $has_retention ) {
			$issues[] = 'no_retention_policy';
		}
		
		// 6. Check for privacy officer contact
		$has_privacy_officer = stripos( $content, 'privacy officer' ) !== false ||
							  stripos( $content, 'chief privacy officer' ) !== false ||
							  stripos( $content, 'privacy contact' ) !== false;
		
		if ( ! $has_privacy_officer ) {
			$issues[] = 'no_privacy_officer_contact';
		}
		
		// 7. Check minimum length (PIPEDA policies should be comprehensive)
		if ( $word_count < 500 ) {
			$issues[] = 'privacy_policy_too_short';
		}
		
		if ( count( $issues ) >= 3 ) {
			return self::create_finding( $issues );
		}
		
		return null;
	}
	
	/**
	 * Create finding array.
	 *
	 * @since  1.6032.1530
	 * @param  array $issues Array of issues found.
	 * @return array Finding array.
	 */
	private static function create_finding( $issues ) {
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Privacy policy does not meet PIPEDA transparency requirements', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/pipeda-privacy-policy',
			'details'      => array(
				'issues_found'       => $issues,
				'pipeda_principle'   => 'Principle 8 - Openness',
				'required_elements'  => array(
					'plain_language'    => 'Must be easily understandable',
					'purposes'          => 'What data is collected and why',
					'data_types'        => 'Types of personal information',
					'third_parties'     => 'Who data is shared with',
					'retention'         => 'How long data is kept',
					'privacy_officer'   => 'Contact for privacy inquiries',
					'accessibility'     => 'Must be readily available',
				),
				'detection_rate'     => '60% of Canadian privacy policies inadequate for PIPEDA',
			),
			'meta'         => array(
				'diagnostic_class' => __CLASS__,
				'timestamp'        => current_time( 'mysql' ),
				'wpdb_avoidance'   => 'Uses get_option(), get_post(), WordPress post APIs',
			),
			'solution'     => array(
				'free'     => __( 'Update privacy policy using OPC privacy policy template and plain language guidelines', 'wpshadow' ),
				'premium'  => __( 'Implement PIPEDA-specific privacy policy with all 10 Fair Information Principles', 'wpshadow' ),
				'advanced' => __( 'Create interactive privacy policy with layered notices and privacy officer dashboard', 'wpshadow' ),
			),
		);
	}
}
