<?php
/**
 * Sendinblue Smtp Configuration Diagnostic
 *
 * Sendinblue Smtp Configuration configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.732.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sendinblue Smtp Configuration Diagnostic Class
 *
 * @since 1.732.0000
 */
class Diagnostic_SendinblueSmtpConfiguration extends Diagnostic_Base {

	protected static $slug = 'sendinblue-smtp-configuration';
	protected static $title = 'Sendinblue Smtp Configuration';
	protected static $description = 'Sendinblue Smtp Configuration configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Sendinblue (Brevo) plugin
		$has_sendinblue = class_exists( 'SIB_Manager' ) ||
		                  defined( 'SIB_VERSION' ) ||
		                  get_option( 'sib_api_key', '' ) !== '';
		
		if ( ! $has_sendinblue ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configured
		$api_key = get_option( 'sib_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'No API key (SMTP disabled)', 'wpshadow' );
		}
		
		// Check 2: SMTP authentication
		$smtp_auth = get_option( 'sib_smtp_authentication', 'yes' );
		if ( 'no' === $smtp_auth ) {
			$issues[] = __( 'SMTP authentication disabled (delivery issues)', 'wpshadow' );
		}
		
		// Check 3: Port configuration
		$smtp_port = get_option( 'sib_smtp_port', 587 );
		if ( $smtp_port != 587 && $smtp_port != 465 ) {
			$issues[] = sprintf( __( 'Non-standard port %d (may be blocked)', 'wpshadow' ), $smtp_port );
		}
		
		// Check 4: SPF/DKIM verification
		$domain_auth = get_option( 'sib_domain_authenticated', 'no' );
		if ( 'no' === $domain_auth ) {
			$issues[] = __( 'Domain not authenticated (spam risk)', 'wpshadow' );
		}
		
		// Check 5: Email logging
		$log_emails = get_option( 'sib_log_emails', 'no' );
		if ( 'no' === $log_emails ) {
			$issues[] = __( 'Email logging disabled (no audit trail)', 'wpshadow' );
		}
		
		// Check 6: Bounce handling
		$bounce_webhook = get_option( 'sib_bounce_webhook', '' );
		if ( empty( $bounce_webhook ) ) {
			$issues[] = __( 'No bounce webhook (untracked failures)', 'wpshadow' );
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
				/* translators: %s: list of SMTP configuration issues */
				__( 'Sendinblue SMTP has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/sendinblue-smtp-configuration',
		);
	}
}
