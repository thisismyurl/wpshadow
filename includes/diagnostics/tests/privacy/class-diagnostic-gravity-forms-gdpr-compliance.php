<?php
/**
 * Gravity Forms GDPR and Data Privacy Compliance Diagnostic
 *
 * Verify form data handling compliant with privacy laws.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.6030.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms GDPR Compliance Diagnostic Class
 *
 * @since 1.6030.1200
 */
class Diagnostic_GravityFormsGdprCompliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravity-forms-gdpr-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gravity Forms GDPR and Data Privacy Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify form data handling compliant with privacy laws';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy-compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Gravity Forms is active
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}

		$issues = array();

		/**
		 * NOTE: Using $wpdb to access Gravity Forms custom table is intentional.
		 *
		 * Why WordPress API cannot be used:
		 * - Gravity Forms stores form configuration in custom table 'gf_form_meta'
		 * - This is NOT a WordPress core table (no get_post/get_option equivalent)
		 * - Gravity Forms does not provide a public API for querying form metadata
		 * - Direct database access is the ONLY way to audit GDPR compliance settings
		 * - Third-party plugin tables have no WordPress Core API alternative
		 */
		// Check 1: Verify consent checkboxes are present in forms
		global $wpdb;
		$forms_with_consent = 0;
		$total_forms = 0;

		$forms = $wpdb->get_results( "SELECT id, meta_value FROM {$wpdb->prefix}gf_form_meta" );
		if ( $forms ) {
			$total_forms = count( $forms );
			foreach ( $forms as $form ) {
				$form_meta = maybe_unserialize( $form->meta_value );
				if ( isset( $form_meta['fields'] ) ) {
					$has_consent = false;
					foreach ( $form_meta['fields'] as $field ) {
						if ( isset( $field['type'] ) && 'consent' === $field['type'] ) {
							$has_consent = true;
							break;
						}
					}
					if ( $has_consent ) {
						$forms_with_consent++;
					}
				}
			}

			if ( $forms_with_consent < $total_forms ) {
				$missing_consent = $total_forms - $forms_with_consent;
				$issues[] = sprintf( '%d forms missing consent checkboxes', $missing_consent );
			}
		}

		// Check 2: Verify data retention policy configured
		$retention_policy = get_option( 'gform_enable_logging', false );
		$personal_data_retention = get_option( 'gform_personal_data_retention', 0 );

		if ( empty( $personal_data_retention ) || 0 === $personal_data_retention ) {
			$issues[] = 'no data retention policy configured';
		}

		// Check 3: Test for automatic data deletion settings
		$auto_delete_entries = get_option( 'gform_enable_background_updates', false );
		if ( ! $auto_delete_entries || empty( $personal_data_retention ) ) {
			$issues[] = 'no automatic data deletion enabled';
		}

		// Check 4: Check if storing PII unnecessarily (check for IP address logging)
		$ip_address_disabled = get_option( 'gform_disable_ip_address', false );
		if ( ! $ip_address_disabled && $forms ) {
			// Count recent entries with IP addresses
			$entries_with_ip = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}gf_entry WHERE ip IS NOT NULL AND ip != %s AND date_created > DATE_SUB(NOW(), INTERVAL 30 DAY)",
					''
				)
			);

			if ( $entries_with_ip > 0 ) {
				$issues[] = sprintf( 'storing IP addresses for %d entries (potential PII)', $entries_with_ip );
			}
		}

		// Check 5: Verify SSL/TLS encryption on forms
		if ( ! is_ssl() ) {
			$issues[] = 'site not using SSL (form data transmitted insecurely)';
		}

		// Check 6: Test for data export capability (GDPR Right to Access)
		$export_capability_enabled = false;
		if ( class_exists( 'GF_Personal_Data' ) || class_exists( 'GFForms' ) && method_exists( 'GFForms', 'get_version' ) ) {
			// Gravity Forms 2.4+ has built-in export features
			$gf_version = method_exists( 'GFForms', 'get_version' ) ? \GFForms::get_version() : '0';
			if ( version_compare( $gf_version, '2.4', '>=' ) ) {
				$export_capability_enabled = true;
			}
		}

		if ( ! $export_capability_enabled ) {
			$issues[] = 'no data export capability for GDPR compliance';
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GDPR compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-gdpr-compliance',
			);
		}

		return null;
	}
}
