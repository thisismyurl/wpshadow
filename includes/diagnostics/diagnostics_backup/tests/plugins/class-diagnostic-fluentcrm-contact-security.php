<?php
/**
 * FluentCRM Contact Security Diagnostic
 *
 * FluentCRM contact data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.485.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Contact Security Diagnostic Class
 *
 * @since 1.485.0000
 */
class Diagnostic_FluentcrmContactSecurity extends Diagnostic_Base {

	protected static $slug = 'fluentcrm-contact-security';
	protected static $title = 'FluentCRM Contact Security';
	protected static $description = 'FluentCRM contact data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'FLUENTCRM' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Data encryption
		$encryption = get_option( 'fluentcrm_contact_encryption', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Contact data encryption not enabled';
		}

		// Check 2: GDPR compliance
		$gdpr = get_option( 'fluentcrm_gdpr_compliance', 0 );
		if ( ! $gdpr ) {
			$issues[] = 'GDPR compliance not enabled';
		}

		// Check 3: Access control
		$access_control = get_option( 'fluentcrm_access_control_enabled', 0 );
		if ( ! $access_control ) {
			$issues[] = 'Access control not configured';
		}

		// Check 4: Data retention policy
		$retention = get_option( 'fluentcrm_data_retention_policy', '' );
		if ( empty( $retention ) ) {
			$issues[] = 'Data retention policy not set';
		}

		// Check 5: Export functionality
		$export = get_option( 'fluentcrm_data_export_enabled', 0 );
		if ( ! $export ) {
			$issues[] = 'Data export functionality not enabled';
		}

		// Check 6: Audit logging
		$audit = get_option( 'fluentcrm_access_audit_logging', 0 );
		if ( ! $audit ) {
			$issues[] = 'Audit logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 60;
			$threat_multiplier = 6;
			$max_threat = 90;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d contact security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-contact-security',
			);
		}

		return null;
	}
}
