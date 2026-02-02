<?php
/**
 * WebP Support Detection Diagnostic
 *
 * Tests if server supports WebP image format. Checks conversion and display capability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WebP_Support_Detection Class
 *
 * Validates WebP format support. WebP offers superior compression compared
 * to JPEG/PNG. Server-side support requires GD 2.0+ or ImageMagick with
 * WebP delegates. Browser support is near-universal (95%+).
 *
 * @since 1.2601.2148
 */
class Diagnostic_WebP_Support_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'webp-support-detection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WebP Support Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests if server supports WebP image format';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Server-side WebP support (GD/ImageMagick)
	 * - MIME type registration
	 * - Upload capability
	 * - Conversion functionality
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check GD library support.
		$gd_supports_webp = false;
		if ( extension_loaded( 'gd' ) ) {
			$gd_info = gd_info();
			$gd_supports_webp = ! empty( $gd_info['WebP Support'] );
		}

		// Check ImageMagick support.
		$imagick_supports_webp = false;
		if ( class_exists( 'Imagick' ) ) {
			try {
				$imagick = new \Imagick();
				$formats = $imagick->queryFormats( 'WEBP' );
				$imagick_supports_webp = ! empty( $formats );
			} catch ( \Exception $e ) {
				// Silently fail.
			}
		}

		// Neither library supports WebP.
		if ( ! $gd_supports_webp && ! $imagick_supports_webp ) {
			$issues[] = __( 'No image library supports WebP format - cannot process WebP images', 'wpshadow' );
		}

		// Check if WebP is in allowed MIME types.
		$allowed_mimes = get_allowed_mime_types();
		$webp_allowed = false;
		
		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( 'image/webp' === $mime || false !== strpos( $ext, 'webp' ) ) {
				$webp_allowed = true;
				break;
			}
		}

		if ( ! $webp_allowed ) {
			$issues[] = __( 'WebP MIME type not in allowed uploads - users cannot upload WebP images', 'wpshadow' );
		}

		// Check if WebP conversion is enabled (WordPress 5.8+).
		if ( function_exists( 'wp_image_editor_supports' ) ) {
			$supports_webp = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
			
			if ( ! $supports_webp ) {
				$issues[] = __( 'WordPress image editor does not support WebP - conversion unavailable', 'wpshadow' );
			}
		}

		// Check for WebP images in media library.
		global $wpdb;
		
		$webp_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type = %s",
				'image/webp'
			)
		);

		// If WebP images exist but no support, that's a problem.
		if ( $webp_count > 0 && ! $gd_supports_webp && ! $imagick_supports_webp ) {
			$issues[] = sprintf(
				/* translators: %d: number of WebP images */
				_n(
					'%d WebP image in library but server cannot process WebP format',
					'%d WebP images in library but server cannot process WebP format',
					$webp_count,
					'wpshadow'
				),
				$webp_count
			);
		}

		// Check if imagewebp() function is available (for GD).
		if ( $gd_supports_webp && ! function_exists( 'imagewebp' ) ) {
			$issues[] = __( 'GD reports WebP support but imagewebp() function not available', 'wpshadow' );
		}

		// Check if imagecreatefromwebp() is available (for reading WebP).
		if ( $gd_supports_webp && ! function_exists( 'imagecreatefromwebp' ) ) {
			$issues[] = __( 'GD reports WebP support but imagecreatefromwebp() function not available', 'wpshadow' );
		}

		// Test WebP conversion capability with a simple test.
		if ( $gd_supports_webp || $imagick_supports_webp ) {
			$upload_dir = wp_upload_dir();
			$test_file = $upload_dir['basedir'] . '/wpshadow-webp-test.webp';
			
			$conversion_works = false;
			
			// Try to create a simple 1x1 WebP image.
			if ( function_exists( 'imagewebp' ) && wp_is_writable( $upload_dir['basedir'] ) ) {
				$img = imagecreatetruecolor( 1, 1 );
				if ( $img ) {
					$result = @imagewebp( $img, $test_file, 80 );
					if ( $result && file_exists( $test_file ) ) {
						$conversion_works = true;
						@unlink( $test_file );
					}
					imagedestroy( $img );
				}
			}

			if ( ! $conversion_works && $imagick_supports_webp ) {
				// Try ImageMagick.
				try {
					$imagick = new \Imagick();
					$imagick->newImage( 1, 1, new \ImagickPixel( 'white' ) );
					$imagick->setImageFormat( 'webp' );
					$imagick->writeImage( $test_file );
					
					if ( file_exists( $test_file ) ) {
						$conversion_works = true;
						@unlink( $test_file );
					}
				} catch ( \Exception $e ) {
					// Silently fail.
				}
			}

			if ( ! $conversion_works ) {
				$issues[] = __( 'WebP library detected but actual conversion test failed', 'wpshadow' );
			}
		}

		// Check for filters that might interfere.
		if ( has_filter( 'webp_uploads_upload_image_mime_transforms' ) ) {
			$issues[] = __( 'webp_uploads_upload_image_mime_transforms filter is active - may affect WebP conversion', 'wpshadow' );
		}

		// Check for plugins that handle WebP.
		$active_plugins = get_option( 'active_plugins', array() );
		$webp_plugins = array(
			'webp-express'          => __( 'WebP Express - handles WebP conversion', 'wpshadow' ),
			'ewww-image-optimizer'  => __( 'EWWW Image Optimizer - may convert to WebP', 'wpshadow' ),
			'imagify'               => __( 'Imagify - may convert to WebP', 'wpshadow' ),
			'shortpixel'            => __( 'ShortPixel - may convert to WebP', 'wpshadow' ),
		);

		$active_webp_plugins = array();
		foreach ( $webp_plugins as $plugin_slug => $description ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$active_webp_plugins[] = $description;
					break;
				}
			}
		}

		if ( ! empty( $active_webp_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugins */
				__( 'WebP plugins active: %s - verify they are configured correctly', 'wpshadow' ),
				implode( ', ', $active_webp_plugins )
			);
		}

		// Check browser support via .htaccess or server config (optional check).
		$upload_dir = wp_upload_dir();
		$htaccess_file = $upload_dir['basedir'] . '/.htaccess';
		
		if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
			$htaccess = file_get_contents( $htaccess_file );
			
			// Check for WebP content negotiation rules.
			if ( false === strpos( $htaccess, 'webp' ) && false === strpos( $htaccess, 'image/webp' ) ) {
				$issues[] = __( 'No WebP rules in .htaccess - browser content negotiation not configured', 'wpshadow' );
			}
		}

		// Check if WordPress 5.8+ features are available.
		if ( ! function_exists( 'wp_get_webp_info' ) ) {
			$issues[] = __( 'WordPress version does not include wp_get_webp_info() - update to 5.8+ for better WebP support', 'wpshadow' );
		}

		// Performance benefit note.
		if ( $webp_count === 0 && ( $gd_supports_webp || $imagick_supports_webp ) && $webp_allowed ) {
			$issues[] = __( 'WebP supported but not being used - consider enabling WebP conversion for 25-35% smaller file sizes', 'wpshadow' );
		}

		// Check PHP version for optimal support.
		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			$issues[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version (%s) is old - WebP support may be limited, upgrade to 7.4+ recommended', 'wpshadow' ),
				PHP_VERSION
			);
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with WebP support',
						'%d issues detected with WebP support',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/webp-support-detection',
				'details'      => array(
					'issues'                => $issues,
					'gd_supports_webp'      => $gd_supports_webp,
					'imagick_supports_webp' => $imagick_supports_webp,
					'webp_allowed'          => $webp_allowed,
					'webp_count'            => $webp_count,
					'active_webp_plugins'   => $active_webp_plugins,
				),
			);
		}

		return null;
	}
}
