<?php
/**
 * Image Library Detection Diagnostic
 *
 * Detects which image library is active (GD vs ImageMagick). Tests library capabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Library_Detection Class
 *
 * Validates image processing library configuration. WordPress supports
 * both GD and ImageMagick. ImageMagick generally offers better performance
 * and quality but may not be available on all servers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Library_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-library-detection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Library Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects which image library is active and tests capabilities';

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
	 * - Available image libraries
	 * - Library versions and capabilities
	 * - Format support
	 * - Performance characteristics
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check which libraries are available.
		$has_gd      = extension_loaded( 'gd' );
		$has_imagick = class_exists( 'Imagick' );

		// Get the implementation WordPress will use.
		$implementations = array();
		if ( $has_imagick ) {
			$implementations[] = 'WP_Image_Editor_Imagick';
		}
		if ( $has_gd ) {
			$implementations[] = 'WP_Image_Editor_GD';
		}

		// Get the actual editor instance.
		$editor = wp_get_image_editor( WP_CONTENT_DIR . '/index.php' ); // Use any file for testing.
		$active_editor = '';
		
		if ( ! is_wp_error( $editor ) ) {
			$active_editor = get_class( $editor );
		}

		// Neither library available.
		if ( ! $has_gd && ! $has_imagick ) {
			$issues[] = __( 'No image processing library available - images cannot be manipulated', 'wpshadow' );
		}

		// Check GD library if available.
		if ( $has_gd ) {
			$gd_info = gd_info();
			
			// Check GD version.
			if ( isset( $gd_info['GD Version'] ) ) {
				$gd_version = $gd_info['GD Version'];
				
				// Extract version number.
				if ( preg_match( '/([0-9.]+)/', $gd_version, $matches ) ) {
					$version_number = $matches[1];
					if ( version_compare( $version_number, '2.0', '<' ) ) {
						$issues[] = sprintf(
							/* translators: %s: version number */
							__( 'GD library version (%s) is outdated - recommend 2.0 or higher', 'wpshadow' ),
							$version_number
						);
					}
				}
			}

			// Check format support.
			$gd_missing_formats = array();
			
			if ( empty( $gd_info['JPEG Support'] ) && empty( $gd_info['JPG Support'] ) ) {
				$gd_missing_formats[] = 'JPEG';
			}
			if ( empty( $gd_info['PNG Support'] ) ) {
				$gd_missing_formats[] = 'PNG';
			}
			if ( empty( $gd_info['GIF Read Support'] ) && empty( $gd_info['GIF Create Support'] ) ) {
				$gd_missing_formats[] = 'GIF';
			}
			if ( empty( $gd_info['WebP Support'] ) ) {
				$gd_missing_formats[] = 'WebP';
			}

			if ( ! empty( $gd_missing_formats ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of formats */
					__( 'GD library missing format support: %s', 'wpshadow' ),
					implode( ', ', $gd_missing_formats )
				);
			}

			// Check for FreeType support (better text rendering).
			if ( empty( $gd_info['FreeType Support'] ) ) {
				$issues[] = __( 'GD library missing FreeType support - text rendering limited', 'wpshadow' );
			}
		}

		// Check ImageMagick if available.
		if ( $has_imagick ) {
			try {
				$imagick = new \Imagick();
				$version = $imagick->getVersion();
				
				// Parse version.
				if ( isset( $version['versionString'] ) && preg_match( '/ImageMagick ([0-9.]+)/', $version['versionString'], $matches ) ) {
					$im_version = $matches[1];
					
					// Check for old versions.
					if ( version_compare( $im_version, '6.2.4', '<' ) ) {
						$issues[] = sprintf(
							/* translators: %s: version number */
							__( 'ImageMagick version (%s) is outdated - recommend 6.2.4 or higher', 'wpshadow' ),
							$im_version
						);
					}

					// Check for known vulnerable versions.
					if ( version_compare( $im_version, '7.0.1-0', '>=', ) && version_compare( $im_version, '7.0.1-8', '<' ) ) {
						$issues[] = sprintf(
							/* translators: %s: version number */
							__( 'ImageMagick version (%s) has known security vulnerabilities - upgrade required', 'wpshadow' ),
							$im_version
						);
					}
				}

				// Check format support.
				$formats = $imagick->queryFormats();
				$required_formats = array( 'JPEG', 'PNG', 'GIF' );
				$im_missing_formats = array();
				
				foreach ( $required_formats as $format ) {
					if ( ! in_array( $format, $formats, true ) ) {
						$im_missing_formats[] = $format;
					}
				}

				if ( ! empty( $im_missing_formats ) ) {
					$issues[] = sprintf(
						/* translators: %s: comma-separated list of formats */
						__( 'ImageMagick missing format support: %s', 'wpshadow' ),
						implode( ', ', $im_missing_formats )
					);
				}

				// Check for WebP support.
				if ( ! in_array( 'WEBP', $formats, true ) ) {
					$issues[] = __( 'ImageMagick missing WebP support - modern format unavailable', 'wpshadow' );
				}

				// Check for AVIF support (new format).
				if ( ! in_array( 'AVIF', $formats, true ) ) {
					$issues[] = __( 'ImageMagick missing AVIF support - next-gen format unavailable', 'wpshadow' );
				}

				// Check resource limits.
				$limits = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_MEMORY );
				if ( $limits > 0 && $limits < 256 * 1024 * 1024 ) { // 256MB.
					$issues[] = sprintf(
						/* translators: %s: memory limit */
						__( 'ImageMagick memory limit (%s) is low - may fail on large images', 'wpshadow' ),
						size_format( $limits )
					);
				}

			} catch ( \Exception $e ) {
				$issues[] = sprintf(
					/* translators: %s: error message */
					__( 'ImageMagick initialization error: %s', 'wpshadow' ),
					$e->getMessage()
				);
			}
		}

		// Compare performance if both available.
		if ( $has_gd && $has_imagick ) {
			// ImageMagick is generally preferred for better quality and performance.
			if ( 'WP_Image_Editor_GD' === $active_editor ) {
				$issues[] = __( 'GD library is active but ImageMagick is available - ImageMagick offers better performance', 'wpshadow' );
			}
		}

		// Check if wp_image_editors filter is being used.
		if ( has_filter( 'wp_image_editors' ) ) {
			$issues[] = __( 'wp_image_editors filter is active - may override default library selection', 'wpshadow' );
		}

		// Only GD available - note limitations.
		if ( $has_gd && ! $has_imagick ) {
			$issues[] = __( 'Only GD library available - consider installing ImageMagick for better image processing', 'wpshadow' );
		}

		// Only ImageMagick available - note it's preferred.
		if ( ! $has_gd && $has_imagick ) {
			// This is actually fine, just informational.
			$issues[] = __( 'Only ImageMagick available - this is optimal but GD provides fallback capability', 'wpshadow' );
		}

		// Check for ghostscript (required for PDF thumbnails).
		if ( $has_imagick ) {
			try {
				$imagick = new \Imagick();
				$formats = $imagick->queryFormats( 'PDF' );
				
				if ( empty( $formats ) ) {
					$issues[] = __( 'Ghostscript not detected - PDF thumbnail generation unavailable', 'wpshadow' );
				}
			} catch ( \Exception $e ) {
				// Silently fail.
			}
		}

		// Check memory limit for image processing.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		if ( $memory_limit > 0 && $memory_limit < 128 * 1024 * 1024 ) { // 128MB.
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'PHP memory_limit (%s) is low - may cause image processing failures', 'wpshadow' ),
				size_format( $memory_limit )
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
						'%d issue detected with image library configuration',
						'%d issues detected with image library configuration',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-library-detection',
				'details'      => array(
					'issues'          => $issues,
					'has_gd'          => $has_gd,
					'has_imagick'     => $has_imagick,
					'active_editor'   => $active_editor,
					'implementations' => $implementations,
					'memory_limit'    => size_format( $memory_limit ),
				),
			);
		}

		return null;
	}
}
