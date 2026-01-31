<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Noncompressed_Image_Formats extends Diagnostic_Base {
	protected static $slug = 'html-detect-noncompressed-image-formats';
	protected static $title = 'Non-Compressed Image Formats';
	protected static $description = 'Detects uncompressed image formats like BMP';
	protected static $family = 'performance';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$uncompressed = array();
		if ( preg_match_all( '/<img[^>]*src=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $src ) {
				if ( preg_match( '/\.(bmp|tiff|psd)$/i', $src ) ) {
					$uncompressed[] = basename( $src );
				}
			}
		}
		if ( count( $uncompressed ) < 2 ) 
		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
{ return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d uncompressed image(s). BMP, TIFF, and PSD are not web-optimized. Use PNG for graphics, JPEG for photos, or WebP for best compression.', 'wpshadow' ), count( $uncompressed ) ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-noncompressed-image-formats',
			'meta' => array( 'count' => count( $uncompressed ) ),
		);
	}
}
