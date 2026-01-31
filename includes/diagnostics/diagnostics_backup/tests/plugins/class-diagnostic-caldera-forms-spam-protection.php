<?php
/**
 * Caldera Forms Spam Protection Diagnostic
 *
 * Caldera Forms spam protection disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.471.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Spam Protection Diagnostic Class
 *
 * @since 1.471.0000
 */
class Diagnostic_CalderaFormsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-spam-protection';
	protected static $title = 'Caldera Forms Spam Protection';
	protected static $description = 'Caldera Forms spam protection disabled';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check reCAPTCHA settings
		$recaptcha_settings = get_option( 'caldera_forms_recaptcha', array() );
		if ( empty( $recaptcha_settings['site_key'] ) || empty( $recaptcha_settings['secret_key'] ) ) {
			$issues[] = 'recaptcha_not_configured';
			$threat_level += 20;
		}

		// Check honeypot field usage
		global $wpdb;
		$forms = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}cf_forms WHERE is_active = 1"
		);

		if ( $forms ) {
			$forms_without_honeypot = 0;
			foreach ( $forms as $form ) {
				$has_honeypot = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_fields 
						 WHERE form_id = %d AND type = %s",
						$form->ID,
						'honeypot'
					)
				);
				if ( ! $has_honeypot ) {
					$forms_without_honeypot++;
				}
			}
			if ( $forms_without_honeypot > 0 ) {
				$issues[] = 'honeypot_field_not_used';
				$threat_level += 15;
			}
		}

		// Check Akismet integration
		if ( class_exists( 'Akismet' ) ) {
			$akismet_enabled = get_option( 'caldera_forms_akismet', false );
			if ( ! $akismet_enabled ) {
				$issues[] = 'akismet_not_enabled';
				$threat_level += 15;
			}
		}

		// Check rate limiting
		$rate_limit = get_option( 'caldera_forms_rate_limit', false );
		if ( ! $rate_limit ) {
			$issues[] = 'rate_limiting_not_enabled';
			$threat_level += 15;
		}

		// Check submission logging
		$submission_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries"
		);
		if ( $submission_count > 1000 ) {
			$spam_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries WHERE status = 'spam'"
			);
			if ( $spam_count / $submission_count > 0.1 ) {
				$issues[] = 'high_spam_rate';
				$threat_level += 20;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of spam protection issues */
				__( 'Caldera Forms spam protection is weak: %s. This allows spam submissions that waste resources and fill your database.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-spam-protection',
			);
		}
		
		return null;
	}
}
