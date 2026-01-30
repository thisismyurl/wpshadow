<?php
/**
 * Language Cookie Management Diagnostic
 *
 * Language Cookie Management misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1190.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language Cookie Management Diagnostic Class
 *
 * @since 1.1190.0000
 */
class Diagnostic_LanguageCookieManagement extends Diagnostic_Base {

	protected static $slug = 'language-cookie-management';
	protected static $title = 'Language Cookie Management';
	protected static $description = 'Language Cookie Management misconfigured';
	protected static $family = 'functionality';

	public static function check() {
			// Check if any multilingual plugin is active
		$has_multilingual = defined( 'ICL_SITEPRESS_VERSION' ) || // WPML
						   defined( 'POLYLANG_VERSION' ) ||         // Polylang
						   defined( 'TRP_PLUGIN_VERSION' ) ||       // TranslatePress
						   class_exists( 'GTranslate' );             // GTranslate

		if ( ! $has_multilingual ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check WPML language cookie
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$wpml_cookie_setting = get_option( 'icl_cookie_setting', 'wp' );
			if ( $wpml_cookie_setting === 'wp' ) {
				// Check cookie security flags
				if ( ! is_ssl() ) {
					$issues[] = 'wpml_cookie_not_secure';
					$threat_level += 25;
				}
			}
		}

		// Check Polylang language cookie
		if ( defined( 'POLYLANG_VERSION' ) ) {
			$pll_cookie = get_option( 'polylang', array() );
			$cookie_expiry = isset( $pll_cookie['cookie_expiry'] ) ? $pll_cookie['cookie_expiry'] : 365;
			if ( $cookie_expiry > 365 ) {
				$issues[] = 'polylang_cookie_expiry_too_long';
				$threat_level += 15;
			}
		}

		// Check TranslatePress cookie
		if ( defined( 'TRP_PLUGIN_VERSION' ) ) {
			$trp_settings = get_option( 'trp_settings', array() );
			$cookie_duration = isset( $trp_settings['cookie-duration'] ) ? $trp_settings['cookie-duration'] : 365;
			if ( $cookie_duration > 365 ) {
				$issues[] = 'translatepress_cookie_expiry_too_long';
				$threat_level += 15;
			}
		}

		// Check SameSite attribute (general check)
		$samesite_configured = ini_get( 'session.cookie_samesite' );
		if ( empty( $samesite_configured ) ) {
			$issues[] = 'samesite_attribute_not_configured';
			$threat_level += 20;
		}

		// Check HttpOnly flag
		$httponly = ini_get( 'session.cookie_httponly' );
		if ( ! $httponly ) {
			$issues[] = 'httponly_flag_disabled';
			$threat_level += 25;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of cookie issues */
				__( 'Language cookie management has security issues: %s. This exposes language preferences and may enable CSRF attacks.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/language-cookie-management',
			);
		}
		
		return null;
	}
}
