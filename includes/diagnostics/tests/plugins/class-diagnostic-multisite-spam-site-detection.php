<?php
/**
 * Multisite Spam Site Detection Diagnostic
 *
 * Multisite Spam Site Detection misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.971.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Spam Site Detection Diagnostic Class
 *
 * @since 1.971.0000
 */
class Diagnostic_MultisiteSpamSiteDetection extends Diagnostic_Base {

	protected static $slug = 'multisite-spam-site-detection';
	protected static $title = 'Multisite Spam Site Detection';
	protected static $description = 'Multisite Spam Site Detection misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Spam sites in network
		$spam_sites = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->blogs} WHERE spam = %d",
				1
			)
		);
		
		if ( $spam_sites > 10 ) {
			$issues[] = sprintf( __( '%d spam sites flagged (cleanup needed)', 'wpshadow' ), $spam_sites );
		}
		
		// Check 2: Spam detection enabled
		$auto_detect = get_site_option( 'spam_detection_enabled', false );
		if ( ! $auto_detect ) {
			$issues[] = __( 'Automatic spam detection not enabled', 'wpshadow' );
		}
		
		// Check 3: Recent spam registrations
		$recent_spam = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->blogs} WHERE spam = %d AND registered > DATE_SUB(NOW(), INTERVAL 7 DAY)",
				1
			)
		);
		
		if ( $recent_spam > 5 ) {
			$issues[] = sprintf( __( '%d new spam sites in past week (detection may be delayed)', 'wpshadow' ), $recent_spam );
		}
		
		// Check 4: Akismet integration
		$akismet_check = get_site_option( 'akismet_site_check_enabled', false );
		if ( ! $akismet_check && function_exists( 'akismet_http_post' ) ) {
			$issues[] = __( 'Akismet available but not checking new sites', 'wpshadow' );
		}
		
		// Check 5: Spam site pattern detection
		$pattern_domains = $wpdb->get_results(
			"SELECT domain FROM {$wpdb->blogs} WHERE spam = 1 GROUP BY domain HAVING COUNT(*) > 1"
		);
		
		if ( ! empty( $pattern_domains ) ) {
			$issues[] = sprintf( __( '%d domain patterns associated with spam', 'wpshadow' ), count( $pattern_domains ) );
		}
		
		// Check 6: Automated cleanup
		$auto_cleanup = get_site_option( 'spam_site_auto_delete', false );
		$cleanup_days = get_site_option( 'spam_site_cleanup_days', 0 );
		
		if ( ! $auto_cleanup && $spam_sites > 20 ) {
			$issues[] = __( 'No automated spam site cleanup configured', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam detection issues */
				__( 'Multisite spam detection has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-spam-site-detection',
		);
	}
}
