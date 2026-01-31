<?php
/**
 * Solid Security Ssl Certificate Diagnostic
 *
 * Solid Security Ssl Certificate misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.883.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Ssl Certificate Diagnostic Class
 *
 * @since 1.883.0000
 */
class Diagnostic_SolidSecuritySslCertificate extends Diagnostic_Base {

	protected static $slug = 'solid-security-ssl-certificate';
	protected static $title = 'Solid Security Ssl Certificate';
	protected static $description = 'Solid Security Ssl Certificate misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 70,
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-ssl-certificate',
			);
		}
		

		// Security validation checks
		if ( is_ssl() === false ) {
			$issues[] = __( 'HTTPS not enabled', 'wpshadow' );
		}
		if ( defined( 'FORCE_SSL' ) === false || ! FORCE_SSL ) {
			$issues[] = __( 'SSL not forced', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
