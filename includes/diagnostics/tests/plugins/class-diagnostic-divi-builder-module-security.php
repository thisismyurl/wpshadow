<?php
/**
 * Divi Builder Module Security Diagnostic
 *
 * Divi custom modules vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.354.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Module Security Diagnostic Class
 *
 * @since 1.354.0000
 */
class Diagnostic_DiviBuilderModuleSecurity extends Diagnostic_Base {

	protected static $slug = 'divi-builder-module-security';
	protected static $title = 'Divi Builder Module Security';
	protected static $description = 'Divi custom modules vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify SSL for module data transmission
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for Divi module data', 'wpshadow' );
		}

		// Check 2: Check input sanitization configuration
		$sanitization_enabled = get_option( 'et_divi_module_sanitization', false );
		if ( ! $sanitization_enabled ) {
			$issues[] = __( 'Module input sanitization not enforced', 'wpshadow' );
		}

		// Check 3: Verify nonce verification for module saves
		$nonce_verification = get_option( 'et_divi_module_nonce_verification', false );
		if ( ! $nonce_verification ) {
			$issues[] = __( 'Nonce verification not enabled for module operations', 'wpshadow' );
		}

		// Check 4: Check capability restrictions
		$capability_checks = get_option( 'et_divi_module_capability_checks', false );
		if ( ! $capability_checks ) {
			$issues[] = __( 'Capability checks not configured for custom modules', 'wpshadow' );
		}

		// Check 5: Verify XSS protection
		$xss_protection = get_option( 'et_divi_module_xss_protection', false );
		if ( ! $xss_protection ) {
			$issues[] = __( 'XSS protection not enabled for module content', 'wpshadow' );
		}

		// Check 6: Check file upload validation in modules
		$upload_validation = get_option( 'et_divi_module_upload_validation', false );
		if ( ! $upload_validation ) {
			$issues[] = __( 'File upload validation not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 65 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Divi Builder module security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/divi-builder-module-security',
			);
		}

		return null;
	}
}
