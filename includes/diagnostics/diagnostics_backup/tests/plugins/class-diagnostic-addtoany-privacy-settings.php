<?php
/**
 * AddToAny Privacy Settings Diagnostic
 *
 * AddToAny privacy settings missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.437.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AddToAny Privacy Settings Diagnostic Class
 *
 * @since 1.437.0000
 */
class Diagnostic_AddtoanyPrivacySettings extends Diagnostic_Base {

	protected static $slug = 'addtoany-privacy-settings';
	protected static $title = 'AddToAny Privacy Settings';
	protected static $description = 'AddToAny privacy settings missing';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'A2A_SHARE_SAVE_init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Cookie consent integration.
		$cookie_consent = get_option( 'addtoany_cookie_consent', '0' );
		if ( '0' === $cookie_consent ) {
			$issues[] = 'cookie consent not configured (GDPR compliance issue)';
		}

		// Check 2: Data privacy mode.
		$privacy_mode = get_option( 'addtoany_privacy_mode', '0' );
		if ( '0' === $privacy_mode ) {
			$issues[] = 'privacy mode disabled (shares user data with social networks)';
		}

		// Check 3: No-track option for EU visitors.
		$no_track_eu = get_option( 'addtoany_no_track_eu', '0' );
		if ( '0' === $no_track_eu ) {
			$issues[] = 'EU visitor tracking not disabled (GDPR concern)';
		}

		// Check 4: External resource loading.
		$local_resources = get_option( 'addtoany_local_resources', '0' );
		if ( '0' === $local_resources ) {
			$issues[] = 'loading resources from external CDN (privacy concern)';
		}

		// Check 5: Privacy policy link.
		$privacy_link = get_option( 'addtoany_privacy_link', '' );
		if ( empty( $privacy_link ) ) {
			$issues[] = 'no privacy policy link configured for share buttons';
		}

		// Check 6: Data collection settings.
		$collect_data = get_option( 'addtoany_collect_data', '1' );
		if ( '1' === $collect_data ) {
			$issues[] = 'AddToAny data collection enabled (consider disabling for privacy)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AddToAny privacy issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/addtoany-privacy-settings',
			);
		}

		return null;
	}
}
