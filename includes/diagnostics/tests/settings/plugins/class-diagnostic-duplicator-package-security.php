<?php
/**
 * Duplicator Package Security Diagnostic
 *
 * Duplicator packages publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.392.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Package Security Diagnostic Class
 *
 * @since 1.392.0000
 */
class Diagnostic_DuplicatorPackageSecurity extends Diagnostic_Base {

	protected static $slug = 'duplicator-package-security';
	protected static $title = 'Duplicator Package Security';
	protected static $description = 'Duplicator packages publicly accessible';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) {
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
				'severity'    => 85,
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-package-security',
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
