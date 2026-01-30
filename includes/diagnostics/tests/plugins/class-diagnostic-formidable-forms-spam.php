<?php
/**
 * Formidable Forms Spam Protection Diagnostic
 *
 * Formidable Forms spam protection disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.260.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Spam Protection Diagnostic Class
 *
 * @since 1.260.0000
 */
class Diagnostic_FormidableFormsSpam extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-spam';
	protected static $title = 'Formidable Forms Spam Protection';
	protected static $description = 'Formidable Forms spam protection disabled';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Honeypot enabled
		$honeypot = get_option( 'frm_honeypot', 'enabled' );
		if ( 'disabled' === $honeypot ) {
			$issues[] = __( 'Honeypot disabled (bot submissions)', 'wpshadow' );
		}
		
		// Check 2: CAPTCHA configured
		$captcha_type = get_option( 'frm_captcha_type', '' );
		if ( empty( $captcha_type ) ) {
			$issues[] = __( 'No CAPTCHA configured (spam risk)', 'wpshadow' );
		}
		
		// Check 3: Akismet integration
		if ( defined( 'AKISMET_VERSION' ) ) {
			$akismet_enabled = get_option( 'frm_akismet_enabled', 'no' );
			if ( 'no' === $akismet_enabled ) {
				$issues[] = __( 'Akismet available but not enabled', 'wpshadow' );
			}
		}
		
		// Check 4: Submission rate limiting
		$rate_limit = get_option( 'frm_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (spam flooding possible)', 'wpshadow' );
		}
		
		// Check 5: Spam entry count
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}frm_items WHERE is_spam = %d",
				1
			)
		);
		
		if ( $spam_count > 100 ) {
			$issues[] = sprintf( __( '%d spam entries (database bloat)', 'wpshadow' ), $spam_count );
		}
		
		// Check 6: Auto-delete spam
		$auto_delete = get_option( 'frm_auto_delete_spam', 'no' );
		if ( 'no' === $auto_delete && $spam_count > 50 ) {
			$issues[] = __( 'Auto-delete disabled (spam accumulates)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam protection issues */
				__( 'Formidable Forms has %d spam protection issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-spam',
		);
	}
}
