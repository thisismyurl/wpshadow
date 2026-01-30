<?php
/**
 * Wordpress Custom Logo Optimization Diagnostic
 *
 * Wordpress Custom Logo Optimization issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1287.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Custom Logo Optimization Diagnostic Class
 *
 * @since 1.1287.0000
 */
class Diagnostic_WordpressCustomLogoOptimization extends Diagnostic_Base {

	protected static $slug = 'wordpress-custom-logo-optimization';
	protected static $title = 'Wordpress Custom Logo Optimization';
	protected static $description = 'Wordpress Custom Logo Optimization issue detected';
	protected static $family = 'performance';

	public static function check() {
		// Check if custom logo is set
		$custom_logo_id = get_theme_mod( 'custom_logo', 0 );

		if ( ! $custom_logo_id ) {
			return null;
		}

		$issues = array();
		$logo_data = wp_get_attachment_metadata( $custom_logo_id );

		if ( ! $logo_data ) {
			return null;
		}

		// Check 1: Image size
		$file_size = filesize( get_attached_file( $custom_logo_id ) );
		if ( $file_size > ( 100 * 1024 ) ) { // 100KB
			$issues[] = sprintf( __( 'Large logo (%s, slow page load)', 'wpshadow' ), size_format( $file_size ) );
		}

		// Check 2: Image dimensions
		$width = $logo_data['width'] ?? 0;
		$height = $logo_data['height'] ?? 0;

		if ( $width > 400 || $height > 200 ) {
			$issues[] = sprintf( __( 'Large dimensions (%dx%d, should resize)', 'wpshadow' ), $width, $height );
		}

		// Check 3: Image format
		$mime_type = get_post_mime_type( $custom_logo_id );
		if ( 'image/png' === $mime_type && $file_size > ( 50 * 1024 ) ) {
			$issues[] = __( 'PNG logo (use SVG for better performance)', 'wpshadow' );
		}

		// Check 4: Retina version
		$retina_logo = get_theme_mod( 'custom_logo_retina', 0 );
		if ( ! $retina_logo && ( $width < 400 || $height < 200 ) ) {
			$issues[] = __( 'No retina version (blurry on high-DPI screens)', 'wpshadow' );
		}

		// Check 5: Lazy loading
		$logo_html = get_custom_logo();
		if ( strpos( $logo_html, 'loading="lazy"' ) === false && strpos( $logo_html, 'fetchpriority="high"' ) === false ) {
			$issues[] = __( 'No loading strategy (LCP may be slow)', 'wpshadow' );
		}

		// Check 6: Image optimization
		$image_meta = wp_get_attachment_metadata( $custom_logo_id );
		if ( ! isset( $image_meta['image_optimized'] ) ) {
			$issues[] = __( 'Logo not optimized (use image optimizer)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of custom logo optimization issues */
				__( 'WordPress custom logo has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-custom-logo-optimization',
		);
	}
}
