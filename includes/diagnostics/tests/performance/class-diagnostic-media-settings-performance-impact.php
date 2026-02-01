<?php
/**
 * Media Settings Performance Impact Diagnostic
 *
 * Measures performance impact of media size generation and identifies optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1410
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Settings Performance Impact Diagnostic Class
 *
 * Analyzes registered image sizes to identify performance bottlenecks from
 * excessive media size generation. Checks WordPress core, theme, and plugin
 * registered sizes to calculate disk space and processing overhead.
 *
 * @since 1.26032.1410
 */
class Diagnostic_Media_Settings_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures performance impact of media size generation and identifies optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Performance impact threshold (number of registered image sizes)
	 *
	 * @var int
	 */
	private const HIGH_IMPACT_THRESHOLD = 10;

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes registered image sizes and calculates performance impact.
	 *
	 * @since  1.26032.1410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all registered image sizes.
		$sizes = self::get_registered_image_sizes();

		if ( empty( $sizes ) ) {
			return null;
		}

		$total_sizes = count( $sizes );

		// Calculate performance impact.
		$impact_data = self::calculate_performance_impact( $sizes );

		// Check if impact exceeds threshold.
		if ( $total_sizes >= self::HIGH_IMPACT_THRESHOLD ) {
			$description = sprintf(
				/* translators: 1: number of registered image sizes, 2: estimated processing time per upload */
				__( 'Your site has %1$d registered image sizes. Each uploaded image generates all these sizes, which can significantly impact upload times (estimated: %2$s per image) and consume substantial disk space. Consider disabling unused sizes to improve performance.', 'wpshadow' ),
				$total_sizes,
				self::format_time( $impact_data['estimated_time_seconds'] )
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $total_sizes >= 15 ? 'high' : 'medium',
				'threat_level' => min( 50 + ( $total_sizes - self::HIGH_IMPACT_THRESHOLD ) * 3, 90 ),
				'auto_fixable' => false,
				'details'      => array(
					'total_sizes'                  => $total_sizes,
					'core_sizes'                   => $impact_data['core_sizes'],
					'theme_plugin_sizes'           => $impact_data['theme_plugin_sizes'],
					'estimated_time_seconds'       => $impact_data['estimated_time_seconds'],
					'estimated_space_per_image_mb' => $impact_data['estimated_space_mb'],
					'sizes'                        => $sizes,
					'recommendations'              => self::get_recommendations( $sizes, $impact_data ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/media-settings-performance-impact',
			);
		}

		return null;
	}

	/**
	 * Get all registered image sizes.
	 *
	 * Retrieves image sizes from WordPress core using get_intermediate_image_sizes()
	 * and combines with additional registered sizes.
	 *
	 * @since  1.26032.1410
	 * @return array Array of registered image sizes with dimensions.
	 */
	private static function get_registered_image_sizes(): array {
		global $_wp_additional_image_sizes;

		$sizes     = array();
		$get_sizes = get_intermediate_image_sizes();

		foreach ( $get_sizes as $size_name ) {
			$size_data = array(
				'name'   => $size_name,
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
			);

			// Check if it's a WordPress core size.
			if ( in_array( $size_name, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {
				$size_data['width']  = (int) get_option( "{$size_name}_size_w" );
				$size_data['height'] = (int) get_option( "{$size_name}_size_h" );
				$size_data['crop']   = (bool) get_option( "{$size_name}_crop" );
				$size_data['source'] = 'core';
			} elseif ( isset( $_wp_additional_image_sizes[ $size_name ] ) ) {
				// Additional size registered by themes/plugins.
				$size_data['width']  = (int) $_wp_additional_image_sizes[ $size_name ]['width'];
				$size_data['height'] = (int) $_wp_additional_image_sizes[ $size_name ]['height'];
				$size_data['crop']   = (bool) $_wp_additional_image_sizes[ $size_name ]['crop'];
				$size_data['source'] = 'theme_plugin';
			}

			// Only include sizes that have dimensions.
			if ( $size_data['width'] > 0 || $size_data['height'] > 0 ) {
				$sizes[ $size_name ] = $size_data;
			}
		}

		return $sizes;
	}

	/**
	 * Calculate performance impact of registered image sizes.
	 *
	 * Estimates processing time and disk space usage based on registered sizes.
	 *
	 * @since  1.26032.1410
	 * @param  array $sizes Array of registered image sizes.
	 * @return array Impact data including time and space estimates.
	 */
	private static function calculate_performance_impact( array $sizes ): array {
		$core_sizes         = 0;
		$theme_plugin_sizes = 0;
		$total_pixels       = 0;

		foreach ( $sizes as $size ) {
			if ( isset( $size['source'] ) && 'core' === $size['source'] ) {
				++$core_sizes;
			} else {
				++$theme_plugin_sizes;
			}

			// Calculate total pixels (width * height) for space estimation.
			$width         = isset( $size['width'] ) ? $size['width'] : 0;
			$height        = isset( $size['height'] ) ? $size['height'] : 0;
			$total_pixels += $width * $height;
		}

		// Estimate processing time.
		// Rough estimate: 0.5 seconds per size on average hardware.
		$estimated_time = count( $sizes ) * 0.5;

		// Estimate disk space per image.
		// Average: ~100KB per size for JPEG at reasonable quality.
		// This is a rough estimate and varies significantly based on image content.
		$estimated_space_mb = ( count( $sizes ) * 100 ) / 1024;

		return array(
			'core_sizes'             => $core_sizes,
			'theme_plugin_sizes'     => $theme_plugin_sizes,
			'estimated_time_seconds' => round( $estimated_time, 1 ),
			'estimated_space_mb'     => round( $estimated_space_mb, 2 ),
		);
	}

	/**
	 * Generate optimization recommendations.
	 *
	 * Analyzes registered sizes and provides specific recommendations.
	 *
	 * @since  1.26032.1410
	 * @param  array $sizes       Array of registered image sizes.
	 * @param  array $impact_data Performance impact data.
	 * @return array Array of recommendations.
	 */
	private static function get_recommendations( array $sizes, array $impact_data ): array {
		$recommendations = array();

		// Check for duplicate or similar sizes.
		$similar_sizes = self::find_similar_sizes( $sizes );
		if ( ! empty( $similar_sizes ) ) {
			$recommendations[] = sprintf(
				/* translators: %d: number of similar size pairs found */
				__( 'Found %d pairs of similar image sizes. Consider consolidating duplicate sizes.', 'wpshadow' ),
				count( $similar_sizes )
			);
		}

		// Check for very large sizes.
		$large_sizes = self::find_large_sizes( $sizes );
		if ( ! empty( $large_sizes ) ) {
			$recommendations[] = sprintf(
				/* translators: %d: number of large image sizes */
				__( '%d image sizes exceed 2000px. Review if these large sizes are necessary.', 'wpshadow' ),
				count( $large_sizes )
			);
		}

		// Recommend using "Stop Generating Unused Thumbnails" plugin or similar.
		if ( $impact_data['theme_plugin_sizes'] > 5 ) {
			$recommendations[] = __( 'Many theme/plugin sizes detected. Use a plugin like "Stop Generating Unused Thumbnails" to disable unnecessary sizes.', 'wpshadow' );
		}

		// General recommendation.
		$recommendations[] = __( 'Audit your theme and plugins to identify which image sizes are actually used on your site.', 'wpshadow' );

		return $recommendations;
	}

	/**
	 * Find similar image sizes.
	 *
	 * Identifies pairs of image sizes with similar dimensions (within 10% tolerance).
	 *
	 * @since  1.26032.1410
	 * @param  array $sizes Array of registered image sizes.
	 * @return array Array of similar size pairs.
	 */
	private static function find_similar_sizes( array $sizes ): array {
		$similar = array();
		$keys    = array_keys( $sizes );
		$count   = count( $keys );

		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = $i + 1; $j < $count; $j++ ) {
				$size1 = $sizes[ $keys[ $i ] ];
				$size2 = $sizes[ $keys[ $j ] ];

				if ( self::are_sizes_similar( $size1, $size2 ) ) {
					$similar[] = array(
						'size1' => $keys[ $i ],
						'size2' => $keys[ $j ],
					);
				}
			}
		}

		return $similar;
	}

	/**
	 * Check if two sizes are similar.
	 *
	 * @since  1.26032.1410
	 * @param  array $size1 First size data.
	 * @param  array $size2 Second size data.
	 * @return bool True if sizes are similar, false otherwise.
	 */
	private static function are_sizes_similar( array $size1, array $size2 ): bool {
		$width1  = isset( $size1['width'] ) ? $size1['width'] : 0;
		$height1 = isset( $size1['height'] ) ? $size1['height'] : 0;
		$width2  = isset( $size2['width'] ) ? $size2['width'] : 0;
		$height2 = isset( $size2['height'] ) ? $size2['height'] : 0;

		if ( 0 === $width1 || 0 === $width2 || 0 === $height1 || 0 === $height2 ) {
			return false;
		}

		// Consider sizes similar if within 10% of each other.
		$width_diff  = abs( $width1 - $width2 ) / max( $width1, $width2 );
		$height_diff = abs( $height1 - $height2 ) / max( $height1, $height2 );

		return ( $width_diff < 0.1 && $height_diff < 0.1 );
	}

	/**
	 * Find large image sizes.
	 *
	 * Identifies image sizes with dimensions exceeding 2000px.
	 *
	 * @since  1.26032.1410
	 * @param  array $sizes Array of registered image sizes.
	 * @return array Array of large size names.
	 */
	private static function find_large_sizes( array $sizes ): array {
		$large = array();

		foreach ( $sizes as $name => $size ) {
			$width  = isset( $size['width'] ) ? $size['width'] : 0;
			$height = isset( $size['height'] ) ? $size['height'] : 0;

			if ( $width > 2000 || $height > 2000 ) {
				$large[] = $name;
			}
		}

		return $large;
	}

	/**
	 * Format time in seconds to human-readable format.
	 *
	 * @since  1.26032.1410
	 * @param  float $seconds Time in seconds.
	 * @return string Formatted time string.
	 */
	private static function format_time( float $seconds ): string {
		if ( $seconds < 1 ) {
			return sprintf(
				/* translators: %d: milliseconds */
				__( '%dms', 'wpshadow' ),
				round( $seconds * 1000 )
			);
		}

		return sprintf(
			/* translators: %s: seconds with one decimal place */
			__( '%ss', 'wpshadow' ),
			number_format_i18n( $seconds, 1 )
		);
	}
}
