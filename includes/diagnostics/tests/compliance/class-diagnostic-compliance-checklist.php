<?php
/**
 * Compliance Checklist Diagnostic
 *
 * Checks HIPAA/SOC2/ISO27001 compliance status.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compliance Checklist Diagnostic Class
 *
 * Verifies that enterprise compliance requirements (HIPAA, SOC2, ISO27001)
 * are being met and monitored.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Compliance_Checklist extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'compliance-checklist';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Compliance Checklist';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks HIPAA/SOC2/ISO27001 compliance status';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the compliance checklist diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if compliance issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check HIPAA compliance.
		$hipaa_enabled = get_option( 'wpshadow_hipaa_compliance_enabled' );
		$stats['hipaa_mode'] = boolval( $hipaa_enabled );

		if ( ! $hipaa_enabled ) {
			$warnings[] = __( 'HIPAA compliance mode not enabled', 'wpshadow' );
		} else {
			// Check HIPAA sub-requirements.
			$hipaa_encryption = get_option( 'wpshadow_hipaa_encryption' );
			$hipaa_audit_logs = get_option( 'wpshadow_hipaa_audit_logs' );
			$hipaa_access_control = get_option( 'wpshadow_hipaa_access_control' );

			$stats['hipaa_encryption'] = boolval( $hipaa_encryption );
			$stats['hipaa_audit_logs'] = boolval( $hipaa_audit_logs );
			$stats['hipaa_access_control'] = boolval( $hipaa_access_control );

			if ( ! $hipaa_encryption ) {
				$warnings[] = __( 'HIPAA encryption not fully implemented', 'wpshadow' );
			}

			if ( ! $hipaa_audit_logs ) {
				$warnings[] = __( 'HIPAA audit logging not enabled', 'wpshadow' );
			}

			if ( ! $hipaa_access_control ) {
				$warnings[] = __( 'HIPAA access control not fully configured', 'wpshadow' );
			}
		}

		// Check SOC2 compliance.
		$soc2_enabled = get_option( 'wpshadow_soc2_compliance_enabled' );
		$stats['soc2_mode'] = boolval( $soc2_enabled );

		if ( ! $soc2_enabled ) {
			$warnings[] = __( 'SOC2 compliance mode not enabled', 'wpshadow' );
		} else {
			// Check SOC2 controls.
			$soc2_availability = get_option( 'wpshadow_soc2_availability' );
			$soc2_security = get_option( 'wpshadow_soc2_security' );
			$soc2_integrity = get_option( 'wpshadow_soc2_integrity' );
			$soc2_confidentiality = get_option( 'wpshadow_soc2_confidentiality' );
			$soc2_privacy = get_option( 'wpshadow_soc2_privacy' );

			$stats['soc2_availability'] = boolval( $soc2_availability );
			$stats['soc2_security'] = boolval( $soc2_security );
			$stats['soc2_integrity'] = boolval( $soc2_integrity );
			$stats['soc2_confidentiality'] = boolval( $soc2_confidentiality );
			$stats['soc2_privacy'] = boolval( $soc2_privacy );

			if ( ! $soc2_availability || ! $soc2_security ) {
				$warnings[] = __( 'SOC2 availability or security controls not configured', 'wpshadow' );
			}
		}

		// Check ISO27001 compliance.
		$iso_enabled = get_option( 'wpshadow_iso27001_compliance_enabled' );
		$stats['iso27001_mode'] = boolval( $iso_enabled );

		if ( ! $iso_enabled ) {
			$warnings[] = __( 'ISO27001 compliance mode not enabled', 'wpshadow' );
		} else {
			// Check ISO27001 requirements.
			$iso_information_security = get_option( 'wpshadow_iso_information_security_policy' );
			$iso_risk_assessment = get_option( 'wpshadow_iso_risk_assessment' );
			$iso_access_control = get_option( 'wpshadow_iso_access_control' );
			$iso_incident_response = get_option( 'wpshadow_iso_incident_response' );

			$stats['iso_policy'] = boolval( $iso_information_security );
			$stats['iso_risk_assessment'] = boolval( $iso_risk_assessment );
			$stats['iso_access_control'] = boolval( $iso_access_control );
			$stats['iso_incident_response'] = boolval( $iso_incident_response );

			if ( ! $iso_information_security ) {
				$warnings[] = __( 'ISO27001 information security policy not documented', 'wpshadow' );
			}

			if ( ! $iso_risk_assessment ) {
				$warnings[] = __( 'ISO27001 risk assessment not completed', 'wpshadow' );
			}
		}

		// Check GDPR compliance.
		$gdpr_enabled = get_option( 'wpshadow_gdpr_compliance_enabled' );
		$stats['gdpr_mode'] = boolval( $gdpr_enabled );

		if ( ! $gdpr_enabled ) {
			$warnings[] = __( 'GDPR compliance mode not enabled', 'wpshadow' );
		}

		// Check for compliance audit trail.
		$audit_trail = get_option( 'wpshadow_compliance_audit_trail' );
		$stats['audit_trail'] = boolval( $audit_trail );

		if ( ! $audit_trail ) {
			$warnings[] = __( 'Compliance audit trail not enabled', 'wpshadow' );
		}

		// Check for compliance certification status.
		$certification_date = get_option( 'wpshadow_compliance_certification_date' );
		$stats['certification_current'] = ! empty( $certification_date );

		if ( empty( $certification_date ) ) {
			$warnings[] = __( 'No current compliance certification', 'wpshadow' );
		}

		// Check for compliance assessment frequency.
		$assessment_frequency = get_option( 'wpshadow_compliance_assessment_frequency' );
		$stats['assessment_frequency'] = $assessment_frequency ?: 'Not scheduled';

		if ( ! $assessment_frequency ) {
			$warnings[] = __( 'Regular compliance assessments not scheduled', 'wpshadow' );
		}

		// Check for data retention policy.
		$retention_policy = get_option( 'wpshadow_data_retention_policy' );
		$stats['retention_policy'] = boolval( $retention_policy );

		if ( ! $retention_policy ) {
			$warnings[] = __( 'Data retention policy not documented', 'wpshadow' );
		}

		// Check for third-party compliance verification.
		$third_party_audit = get_option( 'wpshadow_third_party_compliance_audit' );
		$stats['third_party_audit'] = boolval( $third_party_audit );

		if ( ! $third_party_audit ) {
			$warnings[] = __( 'Third-party compliance audit not scheduled', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Compliance checklist has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/compliance-checklist',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Compliance checklist has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/compliance-checklist',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Compliance checklist is complete.
	}
}
