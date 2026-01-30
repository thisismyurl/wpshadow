<?php
/**
 * Siteground Email Deliverability Diagnostic
 *
 * Siteground Email Deliverability needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1002.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Siteground Email Deliverability Diagnostic Class
 *
 * @since 1.1002.0000
 */
class Diagnostic_SitegroundEmailDeliverability extends Diagnostic_Base {

	protected static $slug = 'siteground-email-deliverability';
	protected static $title = 'Siteground Email Deliverability';
	protected static $description = 'Siteground Email Deliverability needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for SiteGround hosting (or SG Optimizer plugin)
		$is_siteground = defined( 'SG_OPTIMIZER_VERSION' ) || 
		                 ( isset( $_SERVER['SERVER_ADMIN'] ) && strpos( $_SERVER['SERVER_ADMIN'], 'siteground' ) !== false );
		
		if ( ! $is_siteground ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Using PHP mail()
		$mailer = get_option( 'mailer_type', 'mail' );
		if ( 'mail' === $mailer ) {
			$issues[] = __( 'Using PHP mail() (deliverability issues)', 'wpshadow' );
		}
		
		// Check 2: SMTP configured
		$smtp_host = get_option( 'smtp_host', '' );
		if ( empty( $smtp_host ) ) {
			$issues[] = __( 'SMTP not configured (recommended for SiteGround)', 'wpshadow' );
		}
		
		// Check 3: SPF record check
		$domain = parse_url( home_url(), PHP_URL_HOST );
		$spf_record = dns_get_record( $domain, DNS_TXT );
		$has_spf = false;
		
		if ( $spf_record ) {
			foreach ( $spf_record as $record ) {
				if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=spf1' ) !== false ) {
					$has_spf = true;
					break;
				}
			}
		}
		
		if ( ! $has_spf ) {
			$issues[] = __( 'No SPF record found (emails may be marked as spam)', 'wpshadow' );
		}
		
		// Check 4: Email logging
		$log_emails = get_option( 'sg_email_logging', 'off' );
		if ( 'off' === $log_emails ) {
			$issues[] = __( 'Email logging disabled (no deliverability tracking)', 'wpshadow' );
		}
		
		// Check 5: From email address
		$from_email = get_option( 'admin_email' );
		if ( $from_email && strpos( $from_email, '@' . $domain ) === false ) {
			$issues[] = __( 'From email not matching domain (SPF failure)', 'wpshadow' );
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
				/* translators: %s: list of email deliverability issues */
				__( 'Email deliverability has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/siteground-email-deliverability',
		);
	}
}
