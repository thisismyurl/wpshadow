<?php
/**
 * Advanced Ads Injection Security Diagnostic
 *
 * Advanced Ads vulnerable to script injection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.289.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Injection Security Diagnostic Class
 *
 * @since 1.289.0000
 */
class Diagnostic_AdvancedAdsInjectionSecurity extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-injection-security';
	protected static $title = 'Advanced Ads Injection Security';
	protected static $description = 'Advanced Ads vulnerable to script injection';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Plain text ads with unescaped content.
		global $wpdb;
		$plain_text_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_content NOT LIKE %s",
				'advanced_ads',
				'%<script%',
				'%esc_%'
			)
		);
		if ( $plain_text_ads > 0 ) {
			$issues[] = "{$plain_text_ads} ads with unescaped script tags (XSS vulnerability)";
		}

		// Check 2: Ad content sanitization.
		$sanitization_enabled = get_option( 'advads_sanitize_content', '1' );
		if ( '0' === $sanitization_enabled ) {
			$issues[] = 'ad content sanitization disabled (security risk)';
		}

		// Check 3: User role permissions.
		$manage_role = get_option( 'advads_manage_role', 'administrator' );
		if ( 'administrator' !== $manage_role ) {
			$issues[] = "non-admin role '{$manage_role}' can manage ads (security concern)";
		}

		// Check 4: External script sources.
		$external_scripts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_content NOT LIKE %s",
				'advanced_ads',
				'%src=%',
				'%' . home_url() . '%'
			)
		);
		if ( $external_scripts > 0 ) {
			$issues[] = "{$external_scripts} ads loading external scripts (verify sources)";
		}

		// Check 5: iframe sandbox attributes.
		$unsafe_iframes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_content NOT LIKE %s",
				'advanced_ads',
				'%<iframe%',
				'%sandbox=%'
			)
		);
		if ( $unsafe_iframes > 0 ) {
			$issues[] = "{$unsafe_iframes} iframe ads without sandbox attributes";
		}

		// Check 6: Ad code validation.
		$code_validation = get_option( 'advads_code_validation', '1' );
		if ( '0' === $code_validation ) {
			$issues[] = 'ad code validation disabled (malicious code could be injected)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads injection security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-injection-security',
			);
		}

		return null;
	}
}
