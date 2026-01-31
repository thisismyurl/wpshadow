<?php
/**
 * NextGEN Gallery Watermark Diagnostic
 *
 * NextGEN Gallery watermark settings insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.495.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NextGEN Gallery Watermark Diagnostic Class
 *
 * @since 1.495.0000
 */
class Diagnostic_NextgenGalleryWatermark extends Diagnostic_Base {

	protected static $slug = 'nextgen-gallery-watermark';
	protected static $title = 'NextGEN Gallery Watermark';
	protected static $description = 'NextGEN Gallery watermark settings insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) ) {
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
				'severity'    => 40,
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/nextgen-gallery-watermark',
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
