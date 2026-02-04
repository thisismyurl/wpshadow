<?php
/**
 * No Data Privacy or GDPR Compliance Diagnostic
 *
 * Checks if data privacy and GDPR compliance are implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Privacy/GDPR Compliance Diagnostic
 *
 * Non-compliance with GDPR can result in fines up to €20 million or 4%
 * of global revenue. It's not optional if you have EU customers.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Data_Privacy_Or_Gdpr_Compliance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-data-privacy-gdpr-compliance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Data Privacy/GDPR Compliance';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data privacy and GDPR compliance are implemented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_privacy_compliance() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No GDPR/privacy compliance detected. GDPR (EU) fines: €20M or 4% revenue. California (CCPA): $2,500-$7,500 per violation. If you collect data (emails, names, IPs), you\'re subject. Implement: 1) Privacy policy (what data you collect, why, how long kept), 2) Consent (ask before collecting, especially marketing), 3) Data access (users can request their data), 4) Data deletion (users can request deletion), 5) Cookie consent (banner disclosing tracking), 6) Vendor agreements (third parties handling data), 7) Data processing addendum (with vendors), 8) Retention policy (delete old data regularly). Treat data as precious—customers trust you with it.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/data-privacy-gdpr-compliance',
				'details'     => array(
					'issue'               => __( 'No GDPR/privacy compliance detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement full GDPR and data privacy compliance', 'wpshadow' ),
					'business_impact'     => __( 'Risk of €20M fines or 4% revenue, data breaches, customer trust loss', 'wpshadow' ),
					'compliance_requirements' => self::get_compliance_requirements(),
					'data_handling'       => self::get_data_handling(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if privacy compliance exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if compliance detected, false otherwise.
	 */
	private static function has_privacy_compliance() {
		// Check for privacy-related content
		$privacy_posts = self::count_posts_by_keywords(
			array(
				'privacy policy',
				'gdpr',
				'data protection',
				'cookie consent',
				'ccpa',
			)
		);

		return $privacy_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get compliance requirements.
	 *
	 * @since  1.6035.0000
	 * @return array Compliance requirements with descriptions.
	 */
	private static function get_compliance_requirements() {
		return array(
			'privacy_policy'   => array(
				'requirement' => __( 'Privacy Policy (Required)', 'wpshadow' ),
				'what'        => __( 'What data you collect, why, how long stored, who has access', 'wpshadow' ),
				'legal'       => __( 'Must be publicly accessible and easy to understand', 'wpshadow' ),
			),
			'consent'          => array(
				'requirement' => __( 'Explicit Consent (Required)', 'wpshadow' ),
				'what'        => __( 'Ask permission before collecting data, especially for marketing', 'wpshadow' ),
				'legal'       => __( 'Pre-checked boxes don\'t count (must be affirmative action)', 'wpshadow' ),
			),
			'cookie_banner'    => array(
				'requirement' => __( 'Cookie Consent Banner (Required)', 'wpshadow' ),
				'what'        => __( 'Disclose what cookies/tracking you use, get consent', 'wpshadow' ),
				'legal'       => __( 'Analytics, ads, and tracking all require consent', 'wpshadow' ),
			),
			'data_access'      => array(
				'requirement' => __( 'User Data Access (Required by GDPR)', 'wpshadow' ),
				'what'        => __( 'Users can request to see all their data', 'wpshadow' ),
				'legal'       => __( 'Must provide within 30 days in portable format', 'wpshadow' ),
			),
			'data_deletion'    => array(
				'requirement' => __( 'Right to Be Forgotten (Required by GDPR)', 'wpshadow' ),
				'what'        => __( 'Users can request permanent deletion', 'wpshadow' ),
				'legal'       => __( 'Must delete unless you have legal reason to retain', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get data handling best practices.
	 *
	 * @since  1.6035.0000
	 * @return array Data handling practices.
	 */
	private static function get_data_handling() {
		return array(
			'minimize'        => __( 'Data Minimization: Collect only what you need', 'wpshadow' ),
			'purpose'         => __( 'Purpose Limitation: Use data only for stated purpose', 'wpshadow' ),
			'storage'         => __( 'Secure Storage: Encrypt sensitive data at rest and in transit', 'wpshadow' ),
			'retention'       => __( 'Data Retention: Delete old data (policy: 1-2 years)', 'wpshadow' ),
			'access'          => __( 'Access Control: Limit who can access customer data', 'wpshadow' ),
			'vendors'         => __( 'Vendor Management: Require privacy compliance from third parties', 'wpshadow' ),
			'incident'        => __( 'Incident Response: Plan for data breach notification', 'wpshadow' ),
			'audit'           => __( 'Regular Audits: Review compliance quarterly', 'wpshadow' ),
		);
	}
}
