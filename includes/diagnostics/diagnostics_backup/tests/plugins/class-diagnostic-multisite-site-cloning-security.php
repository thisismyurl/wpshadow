<?php
/**
 * Multisite Site Cloning Security Diagnostic
 *
 * Multisite Site Cloning Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.940.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Site Cloning Security Diagnostic Class
 *
 * @since 1.940.0000
 */
class Diagnostic_MultisiteSiteCloningSecurity extends Diagnostic_Base {

	protected static $slug = 'multisite-site-cloning-security';
	protected static $title = 'Multisite Site Cloning Security';
	protected static $description = 'Multisite Site Cloning Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		// Check if site cloning functionality exists
		$clone_enabled = get_site_option( 'enable_site_cloning', false );
		if ( ! $clone_enabled && ! function_exists( 'wpmudev_clone_site' ) && ! class_exists( 'NS_Cloner' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cloning capability restriction
		$clone_capability = get_site_option( 'site_clone_capability', 'manage_network' );
		if ( $clone_capability !== 'manage_network' && $clone_capability !== 'manage_network_options' ) {
			$issues[] = sprintf( __( 'Site cloning allowed with "%s" capability (should be network admin only)', 'wpshadow' ), $clone_capability );
		}
		
		// Check 2: Source site validation
		$validate_source = get_site_option( 'clone_validate_source_site', false );
		if ( ! $validate_source ) {
			$issues[] = __( 'No source site validation before cloning', 'wpshadow' );
		}
		
		// Check 3: Sensitive data handling
		$sanitize_data = get_site_option( 'clone_sanitize_sensitive_data', false );
		if ( ! $sanitize_data ) {
			$issues[] = __( 'Sensitive data not sanitized during cloning (passwords, API keys)', 'wpshadow' );
		}
		
		// Check 4: Plugin/theme whitelist
		$whitelist_plugins = get_site_option( 'clone_plugin_whitelist', array() );
		if ( empty( $whitelist_plugins ) ) {
			$issues[] = __( 'No plugin whitelist for cloning (security plugins may be cloned)', 'wpshadow' );
		}
		
		// Check 5: Clone audit logging
		$audit_clones = get_site_option( 'clone_audit_logging', false );
		if ( ! $audit_clones ) {
			$issues[] = __( 'Site cloning not logged for audit trail', 'wpshadow' );
		}
		
		// Check 6: Database prefix randomization
		$randomize_prefix = get_site_option( 'clone_randomize_prefix', false );
		if ( ! $randomize_prefix ) {
			$issues[] = __( 'Cloned sites use predictable database prefix', 'wpshadow' );
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
				/* translators: %s: list of security issues */
				__( 'Multisite site cloning has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-site-cloning-security',
		);
	}
}
