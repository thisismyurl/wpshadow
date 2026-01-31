<?php
/**
 * Caldera Forms GDPR Compliance Diagnostic
 *
 * Caldera Forms GDPR settings missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.476.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms GDPR Compliance Diagnostic Class
 *
 * @since 1.476.0000
 */
class Diagnostic_CalderaFormsGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-gdpr-compliance';
	protected static $title = 'Caldera Forms GDPR Compliance';
	protected static $description = 'Caldera Forms GDPR settings missing';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check GDPR settings
		$gdpr_enabled = get_option( 'caldera_forms_gdpr', false );
		if ( ! $gdpr_enabled ) {
			$issues[] = 'gdpr_settings_not_enabled';
			$threat_level += 25;
		}

		// Check forms for consent fields
		global $wpdb;
		$forms = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}cf_forms WHERE is_active = 1"
		);

		if ( $forms ) {
			$forms_without_consent = 0;
			foreach ( $forms as $form ) {
				$has_consent_field = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_fields 
						 WHERE form_id = %d AND (type = %s OR slug LIKE %s)",
						$form->ID,
						'checkbox',
						'%consent%'
					)
				);
				if ( ! $has_consent_field ) {
					$forms_without_consent++;
				}
			}
			if ( $forms_without_consent > 0 ) {
				$issues[] = 'forms_missing_consent_checkboxes';
				$threat_level += 20;
			}
		}

		// Check data retention policy
		$retention_period = get_option( 'caldera_forms_retention_period', 0 );
		if ( $retention_period === 0 ) {
			$issues[] = 'no_data_retention_policy';
			$threat_level += 20;
		}

		// Check personal data export capability
		if ( ! has_filter( 'wp_privacy_personal_data_exporters' ) ) {
			$issues[] = 'no_data_export_functionality';
			$threat_level += 15;
		}

		// Check personal data erasure capability
		if ( ! has_filter( 'wp_privacy_personal_data_erasers' ) ) {
			$issues[] = 'no_data_erasure_functionality';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of GDPR compliance issues */
				__( 'Caldera Forms GDPR compliance has gaps: %s. This violates GDPR requirements and exposes you to legal liability.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-gdpr-compliance',
			);
		}
		
		return null;
	}
}
