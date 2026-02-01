<?php
/**
 * Custom Image Sizes Registration Diagnostic
 *
 * Tests custom image sizes from themes/plugins and validates add_image_size calls.
 * Detects excessive image sizes, poorly configured dimensions, and potential storage bloat.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.0852
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Image Sizes Registration Diagnostic Class
 *
 * Validates custom image sizes registered via add_image_size() to ensure
 * optimal performance and storage usage. Checks for excessive sizes,
 * unused registrations, and poorly configured dimensions.
 *
 * @since 1.6032.0852
 */
class Diagnostic_Custom_Image_Sizes_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-image-sizes-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Image Sizes Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests custom image sizes from themes/plugins. Validates add_image_size calls.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Maximum recommended custom image sizes
	 *
	 * @var int
	 */
	private const MAX_RECOMMENDED_SIZES = 10;

	/**
	 * WordPress default image sizes that should be excluded from checks
	 *
	 * @var array
	 */
	private const DEFAULT_SIZES = array(
		'thumbnail',
		'medium',
		'medium_large',
		'large',
		'1536x1536',
		'2048x2048',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Excessive number of custom image sizes
	 * - Very large dimensions that could cause storage issues
	 * - Duplicate or similar size registrations
	 * - Poorly named sizes (generic names that may conflict)
	 *
	 * @since  1.6032.0852
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $_wp_additional_image_sizes;

		$issues = array();

		// Get all registered image sizes
		$all_sizes = get_intermediate_image_sizes();

		// Filter out WordPress default sizes to get custom sizes
		$custom_sizes = array_diff( $all_sizes, self::DEFAULT_SIZES );
		$custom_count = count( $custom_sizes );

		// Check 1: Excessive number of custom image sizes
		if ( $custom_count > self::MAX_RECOMMENDED_SIZES ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom image sizes */
				__( '%1$d custom image sizes registered (recommended: %2$d or fewer)', 'wpshadow' ),
				$custom_count,
				self::MAX_RECOMMENDED_SIZES
			);
		}

		// Check 2: Analyze dimensions of custom sizes
		if ( ! empty( $_wp_additional_image_sizes ) ) {
			$large_sizes          = array();
			$duplicate_candidates = array();
			$dimension_map        = array();

			foreach ( $_wp_additional_image_sizes as $size_name => $size_data ) {
				$width  = isset( $size_data['width'] ) ? absint( $size_data['width'] ) : 0;
				$height = isset( $size_data['height'] ) ? absint( $size_data['height'] ) : 0;

				// Check for very large dimensions (potential storage bloat)
				if ( $width > 2000 || $height > 2000 ) {
					$large_sizes[] = sprintf(
						/* translators: 1: size name, 2: width, 3: height */
						__( '%1$s (%2$dx%3$d)', 'wpshadow' ),
						$size_name,
						$width,
						$height
					);
				}

				// Check for duplicate or similar dimensions
				$dimension_key = sprintf( '%dx%d', min( $width, $height ), max( $width, $height ) );
				if ( isset( $dimension_map[ $dimension_key ] ) ) {
					$duplicate_candidates[] = sprintf(
						/* translators: 1: first size name, 2: second size name */
						__( '%1$s and %2$s have similar dimensions', 'wpshadow' ),
						$dimension_map[ $dimension_key ],
						$size_name
					);
				} else {
					$dimension_map[ $dimension_key ] = $size_name;
				}
			}

			if ( count( $large_sizes ) > 0 ) {
				$issues[] = sprintf(
					/* translators: %s: list of large image sizes */
					__( 'Large image sizes detected: %s', 'wpshadow' ),
					implode( ', ', array_slice( $large_sizes, 0, 3 ) )
				);
			}

			if ( count( $duplicate_candidates ) > 0 ) {
				$issues[] = sprintf(
					/* translators: %s: list of duplicate size candidates */
					__( 'Potential duplicate sizes: %s', 'wpshadow' ),
					implode( ', ', array_slice( $duplicate_candidates, 0, 2 ) )
				);
			}
		}

		// Check 3: Warn about generic naming that might conflict
		$generic_names     = array( 'small', 'medium', 'large', 'big', 'custom' );
		$problematic_names = array();

		foreach ( $custom_sizes as $size_name ) {
			if ( in_array( $size_name, $generic_names, true ) ) {
				$problematic_names[] = $size_name;
			}
		}

		if ( count( $problematic_names ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %s: list of generic size names */
				__( 'Generic size names that may conflict: %s', 'wpshadow' ),
				implode( ', ', $problematic_names )
			);
		}

		// Check 4: Calculate storage impact
		if ( $custom_count > self::MAX_RECOMMENDED_SIZES ) {
			global $wpdb;

			// Get count of attachments to estimate storage impact
			$attachment_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
			);

			if ( $attachment_count > 500 ) {
				$issues[] = sprintf(
					/* translators: 1: number of images, 2: number of custom sizes */
					__( '%1$d images × %2$d custom sizes = significant storage overhead', 'wpshadow' ),
					$attachment_count,
					$custom_count
				);
			}
		}

		// No issues found
		if ( empty( $issues ) ) {
			return null;
		}

		// Calculate threat level based on severity
		$threat_level = 50; // Base threat level
		if ( $custom_count > self::MAX_RECOMMENDED_SIZES * 2 ) {
			$threat_level = 75; // High threat for excessive sizes
		} elseif ( $custom_count > self::MAX_RECOMMENDED_SIZES ) {
			$threat_level = 60; // Medium-high threat
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of issues, 2: list of issues */
				__( 'Custom image sizes have %1$d issues: %2$s', 'wpshadow' ),
				count( $issues ),
				implode( '; ', $issues )
			),
			'severity'     => self::get_severity_from_threat( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/custom-image-sizes-registration',
		);
	}

	/**
	 * Convert threat level to severity string.
	 *
	 * @since  1.6032.0852
	 * @param  int $threat_level Threat level (0-100).
	 * @return string Severity level (low|medium|high|critical).
	 */
	private static function get_severity_from_threat( int $threat_level ): string {
		if ( $threat_level >= 75 ) {
			return 'high';
		} elseif ( $threat_level >= 50 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}
}
