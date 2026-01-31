<?php
/**
 * LifterLMS Certificate Performance Diagnostic
 *
 * LifterLMS certificate generation slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.369.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Certificate Performance Diagnostic Class
 *
 * @since 1.369.0000
 */
class Diagnostic_LifterlmsCertificatePerformance extends Diagnostic_Base {

	protected static $slug = 'lifterlms-certificate-performance';
	protected static $title = 'LifterLMS Certificate Performance';
	protected static $description = 'LifterLMS certificate generation slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Certificate caching enabled
		$cert_cache = get_option( 'llms_certificate_cache_enabled', false );
		if ( ! $cert_cache ) {
			$issues[] = 'Certificate caching disabled';
		}
		
		// Check 2: Image optimization for certificates
		if ( ! extension_loaded( 'gd' ) && ! extension_loaded( 'imagick' ) ) {
			$issues[] = 'No image processing library available';
		}
		
		// Check 3: PDF generation library available
		$pdf_library = get_option( 'llms_certificate_pdf_library', '' );
		if ( empty( $pdf_library ) ) {
			$issues[] = 'PDF generation library not configured';
		}
		
		// Check 4: Background processing enabled
		$background_processing = get_option( 'llms_certificate_background_processing', false );
		if ( ! $background_processing ) {
			$issues[] = 'Background processing disabled';
		}
		
		// Check 5: Certificate template optimization
		$template_opt = get_option( 'llms_certificate_template_optimization', false );
		if ( ! $template_opt ) {
			$issues[] = 'Template optimization disabled';
		}
		
		// Check 6: Old certificate cleanup
		$cleanup_enabled = get_option( 'llms_certificate_cleanup_enabled', false );
		if ( ! $cleanup_enabled ) {
			$issues[] = 'Certificate cleanup disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 60, 30 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'LifterLMS certificate performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-certificate-performance',
			);
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
