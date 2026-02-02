<?php
/**
 * AVIF Support Detection Diagnostic
 *
 * Tests if server supports AVIF image format. Checks modern format compatibility.
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
 * Diagnostic_AVIF_Support_Detection Class
 *
 * Validates AVIF format support. AVIF offers even better compression than
 * WebP (30-50% smaller files). Requires ImageMagick 7.0.25+ or GD with
 * libavif. Browser support is growing rapidly (Chrome 85+, Firefox 93+).
 *
 * @since 1.2601.2148
 */
class Diagnostic_AVIF_Support_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'avif-support-detection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'AVIF Support Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests if server supports AVIF image format';

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
	 * - Server-side AVIF support (ImageMagick/GD)
	 * - MIME type registration
	 * - Upload capability
	 * - Conversion functionality
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check GD library support (requires libavif).
		$gd_supports_avif = false;
		if ( extension_loaded( 'gd' ) ) {
			$gd_info = gd_info();
			// AVIF support in GD is very new, check for it.
			$gd_supports_avif = ! empty( $gd_info['AVIF Support'] );
			
			// Also check for the actual functions.
			if ( ! function_exists( 'imageavif' ) ) {
				$gd_supports_avif = false;
			}
		}

		// Check ImageMagick support (requires 7.0.25+).
		$imagick_supports_avif = false;
		$imagick_version = '';
		
		if ( class_exists( 'Imagick' ) ) {
			try {
				$imagick = new \Imagick();
				$version_info = $imagick->getVersion();
				
				// Extract version number.
				if ( isset( $version_info['versionString'] ) && preg_match( '/ImageMagick ([0-9.]+)/', $version_info['versionString'], $matches ) ) {
					$imagick_version = $matches[1];
					
					// AVIF support requires ImageMagick 7.0.25+.
					if ( version_compare( $imagick_version, '7.0.25', '>=' ) ) {
						$formats = $imagick->queryFormats( 'AVIF' );
						$imagick_supports_avif = ! empty( $formats );
					}
				}
			} catch ( \Exception $e ) {
				// Silently fail.
			}
		}

		// Neither library supports AVIF.
		if ( ! $gd_supports_avif && ! $imagick_supports_avif ) {
			// This is informational, not critical.
			$issues[] = __( 'No image library supports AVIF format - next-generation format unavailable', 'wpshadow' );
			
			// Provide version info if available.
			if ( ! empty( $imagick_version ) ) {
				if ( version_compare( $imagick_version, '7.0.25', '<' ) ) {
					$issues[] = sprintf(
						/* translators: 1: current version, 2: required version */
						__( 'ImageMagick version %1$s detected - AVIF requires version %2$s or higher', 'wpshadow' ),
						$imagick_version,
						'7.0.25'
					);
				}
			}
		}

		// Check if AVIF is in allowed MIME types.
		$allowed_mimes = get_allowed_mime_types();
		$avif_allowed = false;
		
		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( 'image/avif' === $mime || false !== strpos( $ext, 'avif' ) ) {
				$avif_allowed = true;
				break;
			}
		}

		if ( ! $avif_allowed && ( $gd_supports_avif || $imagick_supports_avif ) ) {
			$issues[] = __( 'Server supports AVIF but MIME type not in allowed uploads - add "avif" => "image/avif"', 'wpshadow' );
		}

		// Check if WordPress supports AVIF (WordPress 6.0+).
		if ( function_exists( 'wp_image_editor_supports' ) ) {
			$supports_avif = wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );
			
			if ( ! $supports_avif && ( $gd_supports_avif || $imagick_supports_avif ) ) {
				$issues[] = __( 'WordPress image editor does not recognize AVIF - update WordPress or check configuration', 'wpshadow' );
			}
		}

		// Check for AVIF images in media library.
		global $wpdb;
		
		$avif_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type = %s",
				'image/avif'
			)
		);

		// If AVIF images exist but no support, that's a problem.
		if ( $avif_count > 0 && ! $gd_supports_avif && ! $imagick_supports_avif ) {
			$issues[] = sprintf(
				/* translators: %d: number of AVIF images */
				_n(
					'%d AVIF image in library but server cannot process AVIF format',
					'%d AVIF images in library but server cannot process AVIF format',
					$avif_count,
					'wpshadow'
				),
				$avif_count
			);
		}

		// Test AVIF conversion capability.
		if ( $gd_supports_avif || $imagick_supports_avif ) {
			$upload_dir = wp_upload_dir();
			$test_file = $upload_dir['basedir'] . '/wpshadow-avif-test.avif';
			
			$conversion_works = false;
			
			// Try GD first.
			if ( function_exists( 'imageavif' ) && wp_is_writable( $upload_dir['basedir'] ) ) {
				$img = @imagecreatetruecolor( 1, 1 );
				if ( $img ) {
					$result = @imageavif( $img, $test_file, 80 );
					if ( $result && file_exists( $test_file ) ) {
						$conversion_works = true;
						@unlink( $test_file );
					}
					@imagedestroy( $img );
				}
			}

			// Try ImageMagick if GD failed.
			if ( ! $conversion_works && $imagick_supports_avif ) {
				try {
					$imagick = new \Imagick();
					$imagick->newImage( 1, 1, new \ImagickPixel( 'white' ) );
					$imagick->setImageFormat( 'avif' );
					$imagick->writeImage( $test_file );
					
					if ( file_exists( $test_file ) ) {
						$conversion_works = true;
						@unlink( $test_file );
					}
				} catch ( \Exception $e ) {
					$issues[] = sprintf(
						/* translators: %s: error message */
						__( 'AVIF conversion test failed: %s', 'wpshadow' ),
						$e->getMessage()
					);
				}
			}

			if ( ! $conversion_works && ( $gd_supports_avif || $imagick_supports_avif ) ) {
				$issues[] = __( 'AVIF library detected but actual conversion test failed - check libavif installation', 'wpshadow' );
			}
		}

		// Check for plugins that handle AVIF.
		$active_plugins = get_option( 'active_plugins', array() );
		$avif_plugins = array(
			'ewww-image-optimizer' => __( 'EWWW Image Optimizer - may support AVIF conversion', 'wpshadow' ),
			'imagify'              => __( 'Imagify - may support AVIF conversion', 'wpshadow' ),
		);

		$active_avif_plugins = array();
		foreach ( $avif_plugins as $plugin_slug => $description ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$active_avif_plugins[] = $description;
					break;
				}
			}
		}

		if ( ! empty( $active_avif_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugins */
				__( 'AVIF-capable plugins active: %s - verify AVIF support is enabled', 'wpshadow' ),
				implode( ', ', $active_avif_plugins )
			);
		}

		// Browser support note (informational).
		if ( $gd_supports_avif || $imagick_supports_avif ) {
			// AVIF browser support is good but not universal yet.
			$issues[] = __( 'AVIF supported but browser support is ~85% (Chrome 85+, Firefox 93+, Safari 16+) - consider fallbacks', 'wpshadow' );
		}

		// Performance benefit note.
		if ( $avif_count === 0 && ( $gd_supports_avif || $imagick_supports_avif ) && $avif_allowed ) {
			$issues[] = __( 'AVIF supported but not being used - offers 30-50% smaller files than WebP', 'wpshadow' );
		}

		// Check PHP version.
		if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
			$issues[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version (%s) may have limited AVIF support - PHP 8.0+ recommended', 'wpshadow' ),
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
						'%d issue detected with AVIF support',
						'%d issues detected with AVIF support',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/avif-support-detection',
				'details'      => array(
					'issues'                => $issues,
					'gd_supports_avif'      => $gd_supports_avif,
					'imagick_supports_avif' => $imagick_supports_avif,
					'imagick_version'       => $imagick_version,
					'avif_allowed'          => $avif_allowed,
					'avif_count'            => $avif_count,
					'active_avif_plugins'   => $active_avif_plugins,
				),
			);
		}

		return null;
	}
}
