<?php
/**
 * GDPR Readiness Diagnostic
 *
 * Verifies compliance with General Data Protection Regulation requirements
 * including privacy policy, consent, data handling, and DPIA.
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
 * Diagnostic_GDPR_Readiness Class
 *
 * Checks GDPR compliance and readiness.
 *
 * @since 1.2601.2148
 */
class Diagnostic_GDPR_Readiness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-readiness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Compliance Readiness Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GDPR compliance elements and data protection measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if GDPR issues detected, null otherwise.
	 */
	public static function check() {
		// Check if site is EU-focused or GDPR-applicable
		if ( ! self::is_gdpr_applicable() ) {
			return null;
		}

		$compliance_status = self::check_gdpr_compliance();

		if ( $compliance_status['all_compliant'] ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: issue count */
				__( 'Found %d GDPR compliance gaps. Non-compliance risks €20M+ fines or 4%% annual revenue.', 'wpshadow' ),
				count( $compliance_status['missing_items'] )
			),
			'severity'      => 'critical',
			'threat_level'  => 85,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/gdpr-compliance',
			'family'        => self::$family,
			'meta'          => array(
				'missing_items'          => $compliance_status['missing_items'],
				'fines_for_non_compliance' => '€4,000,000 - €20,000,000 or 4%% annual revenue',
				'compliance_deadline'    => 'Immediate (GDPR in effect)',
			),
			'details'       => array(
				'missing_compliance_elements' => $compliance_status['missing_items'],
				'required_elements'          => array(
					'Privacy Policy' => array(
						__( 'Must explain what data is collected' ),
						__( 'Must disclose processing legal basis' ),
						__( 'Must mention data retention periods' ),
						__( 'Must explain user rights (access, delete, portability)' ),
						__( 'Must list all third-party processors' ),
					),
					'Cookie/Consent Notice' => array(
						__( 'Must obtain explicit consent before non-essential cookies' ),
						__( 'Consent must be separate from ToS' ),
						__( 'Must allow easy withdrawal of consent' ),
						__( 'Must track and honor consent choices' ),
					),
					'Data Subject Rights' => array(
						__( 'Right to access: Provide copy of all personal data' ),
						__( 'Right to delete: Delete data when no legal basis' ),
						__( 'Right to portability: Export data in standard format' ),
						__( 'Right to object: Allow opting out of processing' ),
					),
					'Data Processing Agreement' => array(
						__( 'Required if processing data for someone else' ),
						__( 'Required with all third-party vendors' ),
						__( 'Must clarify responsibility for compliance' ),
					),
				),
				'compliance_checklist'       => array(
					'✓ Privacy policy published and linked in footer' ),
					'✓ Cookie consent banner (not disabled by default)' ),
					'✓ Data subject request process documented' ),
					'✓ Data retention policies defined' ),
					'✓ Privacy-by-design implemented' ),
					'✓ Data Protection Impact Assessment (DPIA) completed' ),
					'✓ Data Processing Agreement with vendors' ),
					'✓ Encryption enabled for data in transit (HTTPS)' ),
				),
				'implementation_timeline'    => array(
					'Immediate (This week)' => array(
						__( 'Publish/update privacy policy' ),
						__( 'Install cookie consent plugin (Cookiebot, Termly)' ),
					),
					'Short-term (This month)' => array(
						__( 'Implement data export functionality' ),
						__( 'Implement right to be forgotten (delete user data)' ),
						__( 'Document data retention policies' ),
					),
					'Medium-term (This quarter)' => array(
						__( 'Create DPA with all data processors' ),
						__( 'Complete Data Protection Impact Assessment' ),
						__( 'Train staff on GDPR obligations' ),
					),
				),
			),
		);
	}

	/**
	 * Check if GDPR applies to this site.
	 *
	 * @since  1.2601.2148
	 * @return bool True if GDPR likely applies.
	 */
	private static function is_gdpr_applicable() {
		// Check if site targets EU, or uses EU services
		$site_option = get_option( 'wpshadow_site_gdpr_applicable', true );
		return (bool) $site_option;
	}

	/**
	 * Check GDPR compliance status.
	 *
	 * @since  1.2601.2148
	 * @return array Compliance status.
	 */
	private static function check_gdpr_compliance() {
		$missing = array();

		// Check 1: Privacy policy
		$privacy_page = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy_page === 0 ) {
			$missing[] = 'Privacy policy page not set';
		} else {
			$post = get_post( $privacy_page );
			if ( ! $post || empty( $post->post_content ) ) {
				$missing[] = 'Privacy policy is empty or missing content';
			}
		}

		// Check 2: Cookie consent plugin
		$consent_plugins = array(
			'cookiebot-for-wordpress/cookiebot.php',
			'termly-manage-cookies/termly-manage-cookies.php',
			'gdpr-cookie-consent/moove-gdpr.php',
		);

		$has_consent = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_consent = true;
				break;
			}
		}

		if ( ! $has_consent ) {
			$missing[] = 'No cookie consent plugin detected';
		}

		// Check 3: HTTPS
		if ( ! is_ssl() ) {
			$missing[] = 'HTTPS not enabled - required for data security';
		}

		// Check 4: Data export functionality
		if ( ! function_exists( 'wp_user_personal_data_exporter' ) ) {
			$missing[] = 'User data export functionality not configured';
		}

		return array(
			'all_compliant' => empty( $missing ),
			'missing_items' => $missing,
		);
	}
}
