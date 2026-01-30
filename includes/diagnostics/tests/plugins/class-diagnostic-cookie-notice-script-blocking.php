<?php
/**
 * Cookie Notice Script Blocking Diagnostic
 *
 * Cookie Notice not blocking scripts properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.420.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Notice Script Blocking Diagnostic Class
 *
 * @since 1.420.0000
 */
class Diagnostic_CookieNoticeScriptBlocking extends Diagnostic_Base {

	protected static $slug = 'cookie-notice-script-blocking';
	protected static $title = 'Cookie Notice Script Blocking';
	protected static $description = 'Cookie Notice not blocking scripts properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'COOKIE_NOTICE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Script blocking enabled
		$blocking = get_option( 'cookie_notice_script_blocking_enabled', 0 );
		if ( ! $blocking ) {
			$issues[] = 'Script blocking not enabled';
		}
		
		// Check 2: Blocked scripts configured
		$blocked_count = absint( get_option( 'cookie_notice_blocked_scripts_count', 0 ) );
		if ( $blocked_count <= 0 ) {
			$issues[] = 'No blocked scripts configured';
		}
		
		// Check 3: Consent categories
		$categories = get_option( 'cookie_notice_consent_categories_configured', 0 );
		if ( ! $categories ) {
			$issues[] = 'Consent categories not configured';
		}
		
		// Check 4: Cookiebot integration
		$cookiebot = get_option( 'cookie_notice_cookiebot_integration_enabled', 0 );
		if ( ! $cookiebot ) {
			$issues[] = 'Cookiebot integration not enabled';
		}
		
		// Check 5: Consent tracking
		$tracking = get_option( 'cookie_notice_consent_tracking_enabled', 0 );
		if ( ! $tracking ) {
			$issues[] = 'Consent tracking not enabled';
		}
		
		// Check 6: Banner customization
		$banner = get_option( 'cookie_notice_banner_customized', 0 );
		if ( ! $banner ) {
			$issues[] = 'Banner not customized';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d cookie consent issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cookie-notice-script-blocking',
			);
		}
		
		return null;
	}
}
