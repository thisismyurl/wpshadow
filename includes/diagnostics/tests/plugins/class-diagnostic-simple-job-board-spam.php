<?php
/**
 * Simple Job Board Spam Diagnostic
 *
 * Simple Job Board spam protection weak.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.544.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Job Board Spam Diagnostic Class
 *
 * @since 1.544.0000
 */
class Diagnostic_SimpleJobBoardSpam extends Diagnostic_Base {

	protected static $slug = 'simple-job-board-spam';
	protected static $title = 'Simple Job Board Spam';
	protected static $description = 'Simple Job Board spam protection weak';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SJB_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: CAPTCHA enabled for job applications
		$captcha_enabled = get_option( 'sjb_captcha_enabled', false );
		if ( ! $captcha_enabled ) {
			$issues[] = __( 'CAPTCHA not enabled for job applications', 'wpshadow' );
		}
		
		// Check 2: Application rate limiting
		$rate_limit = get_option( 'sjb_rate_limit_enabled', false );
		if ( ! $rate_limit ) {
			$issues[] = __( 'Application rate limiting not configured', 'wpshadow' );
		}
		
		// Check 3: Moderation queue enabled
		$moderation = get_option( 'sjb_moderation_enabled', false );
		if ( ! $moderation ) {
			$issues[] = __( 'Job application moderation not enabled', 'wpshadow' );
		}
		
		// Check 4: Check for spam applications in database
		$spam_apps = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sjb_applications WHERE application_status = 'spam'"
		);
		
		if ( $spam_apps > 10 ) {
			$issues[] = sprintf( __( '%d spam applications detected (improve filtering)', 'wpshadow' ), $spam_apps );
		}
		
		// Check 5: Email domain blacklist
		$blacklist = get_option( 'sjb_email_blacklist', array() );
		if ( empty( $blacklist ) ) {
			$issues[] = __( 'Email domain blacklist not configured', 'wpshadow' );
		}
		
		
		// Check 6: SSL/HTTPS verification
		if ( ! (is_ssl() || get_option( "require_https" ) === "1") ) {
			$issues[] = __( 'SSL/HTTPS verification', 'wpshadow' );
		}

		// Check 7: Security headers check
		if ( ! (get_option( "security_headers_enabled" ) === "1") ) {
			$issues[] = __( 'Security headers check', 'wpshadow' );
		}

		// Check 8: Nonce validation
		if ( ! (function_exists( "wp_verify_nonce" )) ) {
			$issues[] = __( 'Nonce validation', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Simple Job Board has %d spam protection issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/simple-job-board-spam',
		);
	}
}
