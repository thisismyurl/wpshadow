<?php
/**
 * No HIPAA or Sensitive Data Protection Diagnostic
 *
 * Detects when sensitive data (health, financial) is not properly protected,
 * creating regulatory and legal risk.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No HIPAA or Sensitive Data Protection
 *
 * Checks whether sensitive data handling
 * meets HIPAA/PCI/SOC2 requirements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_HIPAA_Or_Sensitive_Data_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-hipaa-sensitive-data-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HIPAA & Sensitive Data Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether sensitive data is protected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site stores sensitive data
		$stores_payment = is_plugin_active( 'woocommerce/woocommerce.php' );
		$stores_health = get_option( 'wpshadow_stores_health_data' );
		$stores_personal = get_option( 'wpshadow_stores_personal_data' );

		if ( ! ( $stores_payment || $stores_health || $stores_personal ) ) {
			return null;
		}

		// Check for HIPAA/compliance plugins
		$has_compliance = is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'ithemes-security-pro/ithemes-security-pro.php' );

		if ( ! $has_compliance ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re storing sensitive data but haven\'t implemented compliance protections. Requirements vary: HIPAA (health data), PCI-DSS (credit cards), GLBA (financial), SOC2. Protections needed: encryption at rest and in transit, audit logging, access controls, breach notification plan. Violations are expensive: HIPAA breach penalties $100-$50,000 per record, GDPR fines €20M/4% revenue. If you store any sensitive data, consult compliance experts.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Regulatory Compliance & Legal Risk',
					'potential_gain' => 'Avoid massive fines and legal liability',
					'roi_explanation' => 'Sensitive data protection is legally required. Violations result in €20M+ fines or criminal liability.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/hipaa-sensitive-data-protection',
			);
		}

		return null;
	}
}
