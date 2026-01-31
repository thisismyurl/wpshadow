<?php
/**
 * Multisite Sso Integration Diagnostic
 *
 * Multisite Sso Integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.963.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Sso Integration Diagnostic Class
 *
 * @since 1.963.0000
 */
class Diagnostic_MultisiteSsoIntegration extends Diagnostic_Base {

	protected static $slug = 'multisite-sso-integration';
	protected static $title = 'Multisite Sso Integration';
	protected static $description = 'Multisite Sso Integration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: SSO configuration exists
		$sso_enabled = get_site_option( 'multisite_sso_enabled', false );
		if ( ! $sso_enabled ) {
			return null;
		}
		
		// Check 2: Shared authentication secret
		$auth_secret = get_site_option( 'multisite_sso_secret', '' );
		if ( empty( $auth_secret ) ) {
			$issues[] = __( 'SSO authentication secret not configured', 'wpshadow' );
		} elseif ( strlen( $auth_secret ) < 32 ) {
			$issues[] = sprintf( __( 'SSO secret too short: %d characters (minimum 32)', 'wpshadow' ), strlen( $auth_secret ) );
		}
		
		// Check 3: Cookie domain configuration
		$cookie_domain = get_site_option( 'multisite_sso_cookie_domain', '' );
		if ( empty( $cookie_domain ) ) {
			$issues[] = __( 'SSO cookie domain not configured', 'wpshadow' );
		} else {
			// Verify cookie domain matches network domain
			$network = get_network();
			if ( ! empty( $network->domain ) && strpos( $cookie_domain, $network->domain ) === false ) {
				$issues[] = __( 'SSO cookie domain mismatch with network domain', 'wpshadow' );
			}
		}
		
		// Check 4: HTTPS requirement for SSO
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSO without HTTPS is insecure', 'wpshadow' );
		}
		
		// Check 5: Token expiration
		$token_expiry = get_site_option( 'multisite_sso_token_expiry', 0 );
		if ( $token_expiry === 0 || $token_expiry > 3600 ) {
			$issues[] = sprintf( __( 'SSO token expiry too long: %d seconds (recommended: 300-900)', 'wpshadow' ), $token_expiry );
		}
		
		// Check 6: Per-site SSO configuration conflicts
		global $wpdb;
		$sites = get_sites( array( 'number' => 100 ) );
		$conflicting_sites = 0;
		
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$site_sso = get_option( 'enable_sso', true );
			restore_current_blog();
			
			if ( ! $site_sso ) {
				$conflicting_sites++;
			}
		}
		
		if ( $conflicting_sites > 0 ) {
			$issues[] = sprintf( __( '%d sites have SSO disabled (inconsistent network)', 'wpshadow' ), $conflicting_sites );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of configuration issues */
				__( 'Multisite SSO integration has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-sso-integration',
		);
	}
}
