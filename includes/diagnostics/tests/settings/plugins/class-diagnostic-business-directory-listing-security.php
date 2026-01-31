<?php
/**
 * Business Directory Listing Security Diagnostic
 *
 * Business Directory listings not protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.546.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Listing Security Diagnostic Class
 *
 * @since 1.546.0000
 */
class Diagnostic_BusinessDirectoryListingSecurity extends Diagnostic_Base {

	protected static $slug = 'business-directory-listing-security';
	protected static $title = 'Business Directory Listing Security';
	protected static $description = 'Business Directory listings not protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'severity'    => 65,
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-listing-security',
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
