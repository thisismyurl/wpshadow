<?php
/**
 * Wp Cli Package Security Diagnostic
 *
 * Wp Cli Package Security issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1049.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Cli Package Security Diagnostic Class
 *
 * @since 1.1049.0000
 */
class Diagnostic_WpCliPackageSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-cli-package-security';
	protected static $title = 'Wp Cli Package Security';
	protected static $description = 'Wp Cli Package Security issue detected';
	protected static $family = 'security';

	public static function check() {
		// WP-CLI only available in command line or specific hosting environments
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Package directory exists
		$package_dir = getenv( 'WP_CLI_PACKAGES_DIR' );
		if ( ! $package_dir ) {
			$package_dir = getenv( 'HOME' ) . '/.wp-cli/packages';
		}
		
		if ( ! is_dir( $package_dir ) ) {
			return null; // No packages installed
		}
		
		// Check 2: composer.json validation
		$composer_file = $package_dir . '/composer.json';
		if ( ! file_exists( $composer_file ) ) {
			return null;
		}
		
		$composer_data = json_decode( file_get_contents( $composer_file ), true );
		if ( null === $composer_data ) {
			$issues[] = __( 'WP-CLI composer.json contains invalid JSON', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP-CLI package configuration corrupted', 'wpshadow' ),
				'severity'    => 75,
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-cli-package-security',
			);
		}
		
		// Check 3: Installed packages from untrusted sources
		$packages = isset( $composer_data['require'] ) ? $composer_data['require'] : array();
		$untrusted = 0;
		
		foreach ( $packages as $package => $version ) {
			if ( strpos( $package, 'wp-cli/' ) !== 0 ) {
				$untrusted++;
			}
		}
		
		if ( $untrusted > 0 ) {
			$issues[] = sprintf( __( '%d WP-CLI packages from non-official sources', 'wpshadow' ), $untrusted );
		}
		
		// Check 4: Outdated packages
		$composer_lock = $package_dir . '/composer.lock';
		if ( file_exists( $composer_lock ) ) {
			$lock_data = json_decode( file_get_contents( $composer_lock ), true );
			if ( isset( $lock_data['packages'] ) ) {
				$lock_age = time() - filemtime( $composer_lock );
				if ( $lock_age > 7776000 ) { // 90 days
					$issues[] = sprintf( __( 'WP-CLI packages not updated in %d days', 'wpshadow' ), floor( $lock_age / 86400 ) );
				}
			}
		}
		
		// Check 5: Development dependencies in production
		$require_dev = isset( $composer_data['require-dev'] ) ? $composer_data['require-dev'] : array();
		if ( ! empty( $require_dev ) && wp_get_environment_type() === 'production' ) {
			$issues[] = sprintf( __( '%d dev packages installed in production', 'wpshadow' ), count( $require_dev ) );
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
		if ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'WP-CLI packages have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-cli-package-security',
		);
	}
}
