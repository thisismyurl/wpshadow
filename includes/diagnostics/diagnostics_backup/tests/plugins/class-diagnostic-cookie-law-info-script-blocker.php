<?php
/**
 * Cookie Law Info Script Blocker Diagnostic
 *
 * Cookie Law Info Script Blocker not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1112.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Law Info Script Blocker Diagnostic Class
 *
 * @since 1.1112.0000
 */
class Diagnostic_CookieLawInfoScriptBlocker extends Diagnostic_Base {

	protected static $slug = 'cookie-law-info-script-blocker';
	protected static $title = 'Cookie Law Info Script Blocker';
	protected static $description = 'Cookie Law Info Script Blocker not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Cookie Law Info plugin
		if ( ! defined( 'CLI_VERSION' ) && ! class_exists( 'Cookie_Law_Info' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Script blocker enabled
		$blocker_enabled = get_option( 'cli_script_blocker_enabled', false );
		if ( ! $blocker_enabled ) {
			$issues[] = __( 'Script blocker not enabled (GDPR compliance risk)', 'wpshadow' );
		}
		
		// Check 2: Scripts to block configured
		$blocked_scripts = get_option( 'cli_blocked_scripts', array() );
		if ( empty( $blocked_scripts ) && $blocker_enabled ) {
			$issues[] = __( 'No scripts configured for blocking', 'wpshadow' );
		}
		
		// Check 3: Google Analytics script blocked by default
		$ga_blocked = false;
		if ( is_array( $blocked_scripts ) ) {
			foreach ( $blocked_scripts as $script ) {
				if ( stripos( $script, 'google-analytics' ) !== false || stripos( $script, 'gtag' ) !== false ) {
					$ga_blocked = true;
					break;
				}
			}
		}
		
		if ( ! $ga_blocked && $blocker_enabled ) {
			$issues[] = __( 'Google Analytics not configured for blocking (GDPR requirement)', 'wpshadow' );
		}
		
		// Check 4: Cookie consent mode
		$consent_mode = get_option( 'cli_consent_mode', 'opt-in' );
		if ( $consent_mode !== 'opt-in' ) {
			$issues[] = sprintf( __( 'Consent mode set to "%s" (GDPR requires opt-in)', 'wpshadow' ), $consent_mode );
		}
		
		// Check 5: Prior consent requirement
		$prior_consent = get_option( 'cli_prior_consent_required', false );
		if ( ! $prior_consent ) {
			$issues[] = __( 'Prior consent not required (scripts may load before acceptance)', 'wpshadow' );
		}
		
		// Check 6: Cookie categories configured
		$categories = get_option( 'cli_cookie_categories', array() );
		if ( empty( $categories ) ) {
			$issues[] = __( 'Cookie categories not configured', 'wpshadow' );
		} else {
			$has_necessary = false;
			foreach ( $categories as $cat ) {
				if ( isset( $cat['slug'] ) && $cat['slug'] === 'necessary' ) {
					$has_necessary = true;
					break;
				}
			}
			
			if ( ! $has_necessary ) {
				$issues[] = __( '"Necessary" cookie category not defined', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of compliance issues */
				__( 'Cookie Law Info has %d compliance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cookie-law-info-script-blocker',
		);
	}
}
