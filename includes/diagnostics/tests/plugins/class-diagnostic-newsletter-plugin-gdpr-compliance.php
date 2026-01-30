<?php
/**
 * Newsletter Plugin Gdpr Compliance Diagnostic
 *
 * Newsletter Plugin Gdpr Compliance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.718.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Plugin Gdpr Compliance Diagnostic Class
 *
 * @since 1.718.0000
 */
class Diagnostic_NewsletterPluginGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'newsletter-plugin-gdpr-compliance';
	protected static $title = 'Newsletter Plugin Gdpr Compliance';
	protected static $description = 'Newsletter Plugin Gdpr Compliance configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check for newsletter plugins
		$has_newsletter = defined( 'NEWSLETTER_VERSION' ) ||
		                  class_exists( 'Newsletter' ) ||
		                  function_exists( 'mailpoet_initialize' ) ||
		                  class_exists( 'MailPoet\Config\Initializer' );
		
		if ( ! $has_newsletter ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Double opt-in enabled
		$double_optin = get_option( 'newsletter_double_optin', false ) ||
		                get_option( 'mailpoet_subscribe_on_register_use_double_optin', false );
		
		if ( ! $double_optin ) {
			$issues[] = __( 'Double opt-in not enabled (GDPR compliance risk)', 'wpshadow' );
		}
		
		// Check 2: Consent checkbox required
		$require_consent = get_option( 'newsletter_require_privacy_consent', false );
		if ( ! $require_consent ) {
			$issues[] = __( 'Privacy consent checkbox not required', 'wpshadow' );
		}
		
		// Check 3: Unsubscribe link in emails
		$unsubscribe_required = get_option( 'newsletter_unsubscribe_in_footer', true );
		if ( ! $unsubscribe_required ) {
			$issues[] = __( 'Unsubscribe link not in email footer (GDPR required)', 'wpshadow' );
		}
		
		// Check 4: Data retention policy
		$retention_days = get_option( 'newsletter_data_retention_days', 0 );
		if ( $retention_days === 0 ) {
			$issues[] = __( 'No data retention policy configured (indefinite storage)', 'wpshadow' );
		}
		
		// Check 5: Privacy policy link
		$privacy_link = get_option( 'newsletter_privacy_url', '' );
		if ( empty( $privacy_link ) ) {
			$issues[] = __( 'Privacy policy link not in subscription forms', 'wpshadow' );
		}
		
		// Check 6: Consent records
		$log_consent = get_option( 'newsletter_log_consent', false );
		if ( ! $log_consent ) {
			$issues[] = __( 'Consent records not logged (proof of consent missing)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of GDPR compliance issues */
				__( 'Newsletter plugin GDPR compliance has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/newsletter-plugin-gdpr-compliance',
		);
	}
}
