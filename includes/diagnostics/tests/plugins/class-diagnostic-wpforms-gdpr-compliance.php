<?php
/**
 * WPForms GDPR Compliance Diagnostic
 *
 * WPForms lacks GDPR compliance features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.254.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms GDPR Compliance Diagnostic Class
 *
 * @since 1.254.0000
 */
class Diagnostic_WpformsGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'wpforms-gdpr-compliance';
	protected static $title = 'WPForms GDPR Compliance';
	protected static $description = 'WPForms lacks GDPR compliance features';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify GDPR enhancements are enabled
		$gdpr_enabled = get_option( 'wpforms_gdpr', 0 );
		if ( ! $gdpr_enabled ) {
			$issues[] = 'GDPR compliance features not enabled';
		}
		
		// Check 2: Check for cookie consent integration
		$cookie_consent = get_option( 'wpforms_cookie_consent', 0 );
		if ( ! $cookie_consent ) {
			$issues[] = 'Cookie consent integration not configured';
		}
		
		// Check 3: Verify data retention settings
		$retention_period = get_option( 'wpforms_data_retention', 0 );
		if ( $retention_period <= 0 ) {
			$issues[] = 'Data retention period not configured';
		}
		
		// Check 4: Check for consent checkboxes on forms
		$forms = wpforms()->form->get( '', array( 'orderby' => 'ID' ) );
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$form_data = wpforms_decode( $form->post_content );
				if ( ! isset( $form_data['fields'] ) ) {
					continue;
				}
				$has_consent = false;
				foreach ( $form_data['fields'] as $field ) {
					if ( isset( $field['type'] ) && $field['type'] === 'gdpr-checkbox' ) {
						$has_consent = true;
						break;
					}
				}
				if ( ! $has_consent ) {
					$issues[] = sprintf( 'Form "%s" missing GDPR consent checkbox', $form->post_title );
					break;
				}
			}
		}
		
		// Check 5: Verify IP address anonymization
		$anonymize_ip = get_option( 'wpforms_anonymize_ip', 0 );
		if ( ! $anonymize_ip ) {
			$issues[] = 'IP address anonymization not enabled';
		}
		
		// Check 6: Check for data export/deletion tools
		$export_tool = get_option( 'wpforms_enable_user_data_export', 0 );
		if ( ! $export_tool ) {
			$issues[] = 'User data export tool not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WPForms GDPR compliance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-gdpr-compliance',
			);
		}
		
		return null;
	}
}
