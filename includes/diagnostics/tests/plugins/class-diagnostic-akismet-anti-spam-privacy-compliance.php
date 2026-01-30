<?php
/**
 * Akismet Anti Spam Privacy Compliance Diagnostic
 *
 * Akismet Anti Spam Privacy Compliance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1444.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akismet Anti Spam Privacy Compliance Diagnostic Class
 *
 * @since 1.1444.0000
 */
class Diagnostic_AkismetAntiSpamPrivacyCompliance extends Diagnostic_Base {

	protected static $slug = 'akismet-anti-spam-privacy-compliance';
	protected static $title = 'Akismet Anti Spam Privacy Compliance';
	protected static $description = 'Akismet Anti Spam Privacy Compliance issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AKISMET_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Privacy policy disclosure.
		$privacy_notice = get_option( 'akismet_privacy_notice', '0' );
		if ( '0' === $privacy_notice ) {
			$issues[] = 'no privacy notice displayed to comment authors (GDPR requirement)';
		}
		
		// Check 2: Comment author IP collection.
		$collect_ip = get_option( 'akismet_collect_ip', '1' );
		if ( '1' === $collect_ip ) {
			$issues[] = 'collecting commenter IP addresses (requires disclosure)';
		}
		
		// Check 3: Data retention settings.
		$retention_days = get_option( 'akismet_spam_retention', 15 );
		if ( $retention_days > 30 ) {
			$issues[] = "spam kept for {$retention_days} days (consider shorter retention)";
		} elseif ( $retention_days === 0 ) {
			$issues[] = 'spam retained indefinitely (GDPR compliance issue)';
		}
		
		// Check 4: User consent for data transfer.
		$consent_checkbox = get_option( 'akismet_show_consent', '0' );
		if ( '0' === $consent_checkbox ) {
			$issues[] = 'no consent checkbox for sending data to Akismet (GDPR violation)';
		}
		
		// Check 5: Privacy policy link.
		$privacy_policy_id = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_id ) {
			$issues[] = 'WordPress privacy policy not configured (required for Akismet disclosure)';
		}
		
		// Check 6: Comment data sent to third party.
		$send_user_agent = get_option( 'akismet_send_user_agent', '1' );
		$send_referrer = get_option( 'akismet_send_referrer', '1' );
		if ( '1' === $send_user_agent || '1' === $send_referrer ) {
			$issues[] = 'sending browser metadata to Akismet (requires privacy disclosure)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Akismet privacy compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/akismet-anti-spam-privacy-compliance',
			);
		}
		
		return null;
	}
}
