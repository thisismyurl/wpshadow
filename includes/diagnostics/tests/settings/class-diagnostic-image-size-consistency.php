<?php
/**
 * Image Size Consistency Diagnostic
 *
 * Tests if all registered image sizes generate correctly and validates theme image sizes.
 * Ensures that WordPress image sizes are properly configured and consistent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Size Consistency Diagnostic Class
 *
 * Validates that all registered image sizes are properly configured and can generate correctly.
 * Checks for missing sizes, invalid dimensions, and theme-specific image size issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Size_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-size-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Size Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if all registered image sizes generate correctly and validates theme image sizes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks all registered image sizes for proper configuration and validates theme-specific sizes.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all registered image sizes.
		$sizes = self::get_all_image_sizes();

		if ( empty( $sizes ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No image sizes are registered. WordPress requires at least default image sizes (thumbnail, medium, large).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'issues' => array( __( 'No image sizes registered', 'wpshadow' ) ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/image-size-consistency',
			);
		}

		// Check default WordPress image sizes exist.
		$default_sizes    = array( 'thumbnail', 'medium', 'medium_large', 'large' );
		$missing_defaults = array();

		foreach ( $default_sizes as $size_name ) {
			if ( ! isset( $sizes[ $size_name ] ) ) {
				$missing_defaults[] = $size_name;
			}
		}

		if ( ! empty( $missing_defaults ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing sizes */
				__( 'Missing default WordPress image sizes: %s', 'wpshadow' ),
				implode( ', ', $missing_defaults )
			);
		}

		// Validate image size dimensions.
		$invalid_sizes = array();
		foreach ( $sizes as $size_name => $size_data ) {
			if ( isset( $size_data['width'] ) && isset( $size_data['height'] ) ) {
				$width  = $size_data['width'];
				$height = $size_data['height'];

				// Check for invalid dimensions.
				if ( $width < 0 || $height < 0 ) {
					$invalid_sizes[] = sprintf(
						/* translators: %s: image size name */
						__( '%s has negative dimensions', 'wpshadow' ),
						$size_name
					);
				} elseif ( 0 === $width && 0 === $height ) {
					$invalid_sizes[] = sprintf(
						/* translators: %s: image size name */
						__( '%s has zero dimensions (both width and height are 0)', 'wpshadow' ),
						$size_name
					);
				} elseif ( $width > 9999 || $height > 9999 ) {
					$invalid_sizes[] = sprintf(
						/* translators: %s: image size name */
						__( '%s has excessively large dimensions (potential memory issue)', 'wpshadow' ),
						$size_name
					);
				}
			}
		}

		if ( ! empty( $invalid_sizes ) ) {
			$issues = array_merge( $issues, $invalid_sizes );
		}

		// Check for duplicate dimensions (multiple sizes with same dimensions).
		$duplicates = self::find_duplicate_dimensions( $sizes );
		if ( ! empty( $duplicates ) ) {
			foreach ( $duplicates as $dimensions => $size_names ) {
				if ( count( $size_names ) > 1 ) {
					$issues[] = sprintf(
						/* translators: 1: dimensions, 2: comma-separated list of size names */
						__( 'Multiple sizes with identical dimensions (%1$s): %2$s', 'wpshadow' ),
						$dimensions,
						implode( ', ', $size_names )
					);
				}
			}
		}

		// Check theme-specific image sizes.
		$theme_issues = self::check_theme_image_sizes( $sizes );
		if ( ! empty( $theme_issues ) ) {
			$issues = array_merge( $issues, $theme_issues );
		}

		// Check for unused image sizes (sizes registered but never used in theme).
		$unused_sizes = self::find_unused_image_sizes( $sizes );
		if ( count( $unused_sizes ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unused sizes */
				__( '%d registered image sizes appear to be unused by the theme', 'wpshadow' ),
				count( $unused_sizes )
			);
		}

		// Report findings if any issues exist.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d image size configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'issues'           => $issues,
					'registered_sizes' => count( $sizes ),
					'size_details'     => $sizes,
				),
				'kb_link'      => 'https://wpshadow.com/kb/image-size-consistency',
			);
		}

		return null;
	}

	/**
	 * Get all registered image sizes
	 *
	 * Retrieves both default WordPress sizes and additional theme/plugin sizes.
	 *
	 * @since 1.6093.1200
	 * @return array Array of image sizes with their dimensions.
	 */
	private static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		// Get default WordPress sizes.
		$default_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );
		foreach ( $default_sizes as $size ) {
			$width  = get_option( "{$size}_size_w", 0 );
			$height = get_option( "{$size}_size_h", 0 );
			$crop   = get_option( "{$size}_crop", 0 );

			$sizes[ $size ] = array(
				'width'  => intval( $width ),
				'height' => intval( $height ),
				'crop'   => (bool) $crop,
			);
		}

		// Get additional custom sizes.
		if ( isset( $_wp_additional_image_sizes ) && is_array( $_wp_additional_image_sizes ) ) {
			foreach ( $_wp_additional_image_sizes as $name => $size_data ) {
				$sizes[ $name ] = array(
					'width'  => isset( $size_data['width'] ) ? intval( $size_data['width'] ) : 0,
					'height' => isset( $size_data['height'] ) ? intval( $size_data['height'] ) : 0,
					'crop'   => isset( $size_data['crop'] ) ? (bool) $size_data['crop'] : false,
				);
			}
		}

		return $sizes;
	}

	/**
	 * Find duplicate image size dimensions
	 *
	 * Identifies multiple image sizes that have identical dimensions.
	 *
	 * @since 1.6093.1200
	 * @param  array $sizes Array of image sizes.
	 * @return array Array of duplicate dimensions grouped by size names.
	 */
	private static function find_duplicate_dimensions( $sizes ) {
		$dimensions_map = array();

		foreach ( $sizes as $name => $size_data ) {
			if ( isset( $size_data['width'] ) && isset( $size_data['height'] ) ) {
				$key = $size_data['width'] . 'x' . $size_data['height'];
				if ( ! isset( $dimensions_map[ $key ] ) ) {
					$dimensions_map[ $key ] = array();
				}
				$dimensions_map[ $key ][] = $name;
			}
		}

		// Filter to only duplicates.
		return array_filter(
			$dimensions_map,
			function ( $names ) {
				return count( $names ) > 1;
			}
		);
	}

	/**
	 * Check theme-specific image size issues
	 *
	 * Validates that theme-registered image sizes are properly configured.
	 *
	 * @since 1.6093.1200
	 * @param  array $sizes Array of all image sizes.
	 * @return array Array of theme-specific issues.
	 */
	private static function check_theme_image_sizes( $sizes ) {
		$issues = array();
		$theme  = wp_get_theme();

		// Check if theme registers post thumbnails support.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Theme does not declare post-thumbnails support (may affect image size generation)', 'wpshadow' );
		}

		// Look for theme-specific size naming patterns.
		$theme_slug  = get_stylesheet();
		$theme_sizes = array();

		foreach ( $sizes as $size_name => $size_data ) {
			// Check if size name contains theme slug or common theme prefixes.
			if ( strpos( $size_name, $theme_slug ) !== false ||
				strpos( $size_name, 'theme-' ) === 0 ||
				strpos( $size_name, 'custom-' ) === 0 ) {
				$theme_sizes[] = $size_name;
			}
		}

		// Warn if theme has many custom sizes (potential bloat).
		if ( count( $theme_sizes ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of theme-specific sizes */
				__( 'Theme registers %d custom image sizes (consider reducing to improve performance)', 'wpshadow' ),
				count( $theme_sizes )
			);
		}

		// Check for missing post_thumbnail size if theme supports post thumbnails.
		if ( current_theme_supports( 'post-thumbnails' ) && ! has_post_thumbnail() ) {
			// Check if set_post_thumbnail_size was called.
			$post_thumbnail_size = get_option( 'thumbnail_size_w', 0 );
			if ( empty( $post_thumbnail_size ) ) {
				$issues[] = __( 'Theme supports post thumbnails but does not define post_thumbnail size dimensions', 'wpshadow' );
			}
		}

		return $issues;
	}

	/**
	 * Find unused image sizes
	 *
	 * Attempts to identify image sizes that are registered but not used in theme templates.
	 *
	 * @since 1.6093.1200
	 * @param  array $sizes Array of all image sizes.
	 * @return array Array of unused size names.
	 */
	private static function find_unused_image_sizes( $sizes ) {
		$unused       = array();
		$template_dir = get_template_directory();

		// Get theme templates.
		$templates = array();
		if ( is_dir( $template_dir ) ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $template_dir, \FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() && 'php' === $file->getExtension() ) {
					$templates[] = $file->getPathname();
				}
			}
		}

		// Check if each size is referenced in any template.
		foreach ( $sizes as $size_name => $size_data ) {
			// Skip default WordPress sizes (always considered used).
			if ( in_array( $size_name, array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ) {
				continue;
			}

			$found = false;
			foreach ( $templates as $template_file ) {
				if ( file_exists( $template_file ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local theme files.
					$content = file_get_contents( $template_file );
					if ( false !== strpos( $content, $size_name ) ) {
						$found = true;
						break;
					}
				}
			}

			if ( ! $found ) {
				$unused[] = $size_name;
			}
		}

		return $unused;
	}
}
