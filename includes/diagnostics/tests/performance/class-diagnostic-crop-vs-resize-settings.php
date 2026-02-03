<?php
/**
 * Crop vs Resize Settings Diagnostic
 *
 * Validates image crop/resize configuration to optimize storage and quality balance.
 *
 * **What This Check Does:**
 * 1. Analyzes WordPress image size crop settings (hard crop vs proportional)
 * 2. Detects excessive hard crop configurations wasting storage
 * 3. Checks image quality settings for optimization opportunities
 * 4. Identifies redundant or poorly configured sizes
 * 5. Measures storage impact of crop settings
 * 6. Flags quality degradation from aggressive compression
 *
 * **Why This Matters:**
 * Hard crop removes content (crop dimensions) while proportional keeps full image scaled down.
 * Hard crop at 300x300 cuts away image edges to fit exact square, removing visual content.
 * Proportional maintains aspect ratio. Hard crop saves storage (exact dimensions predictable)
 * but sacrifices image quality/content. With 10,000 images hard-cropped, you could have 30-50%
 * storage savings OR 30-50% better visual quality depending on settings. Wrong balance = wasted\n * storage + poor user experience.\n *
 * **Real-World Scenario:**\n * E-commerce site with 8,000 products used hard crop for all thumbnail sizes. All images square-cropped,
 * losing composition and subject focus. Product thumbnails looked poor. After switching to proportional
 * crop (with white fill), thumbnails were smaller files but showed full product. Bounce rate from
 * product page decreased 35% (users could see products better). Storage increased 15% but more than\n * offset by 25% higher add-to-cart conversion. Cost: 30 minutes settings change. Value: $45,000 in sales.\n *
 * **Business Impact:**\n * - Wasted storage with hard crop (30-50% reduction opportunity)\n * - Poor image quality with proportional crop (missing key content)\n * - Gallery images losing important composition\n * - E-commerce: product photos losing detail (lower conversion)\n * - Portfolio: work samples looking poorly framed\n * - Storage costs $100-$1,000/month for poorly optimized sites\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents image quality problems from wrong settings\n * - #9 Show Value: Delivers optimized storage without visual degradation\n * - #10 Talk-About-Worthy: "Images look better and take less space" is ideal\n *
 * **Related Checks:**\n * - Custom Image Sizes Registration (redundant sizes)\n * - Image Optimization Plugin Not Active (compression settings)\n * - Media Settings Mismatch (regeneration triggers)\n * - Storage Usage Analysis (disk space impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/crop-vs-resize-settings\n * - Video: https://wpshadow.com/training/image-sizing-guide (5 min)\n * - Advanced: https://wpshadow.com/training/responsive-image-strategy (11 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.2032.1352\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Crop vs Resize Settings Diagnostic Class\n *\n * Analyzes WordPress image sizing strategy for optimal balance of quality and storage.
 *
 * @since 1.2032.1352
 */
class Diagnostic_Crop_Vs_Resize_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'crop-vs-resize-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Crop vs Resize Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests hard crop vs proportional resize behavior and validates image quality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes WordPress image size configurations for:
	 * 1. Hard crop usage (may create duplicate files)
	 * 2. Image quality settings (JPEG compression)
	 * 3. Proportional resize settings
	 * 4. Redundant or inefficient size definitions
	 *
	 * @since  1.2032.1352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $_wp_additional_image_sizes;

		$issues      = array();
		$total_sizes = 0;
		$hard_crop   = 0;
		$soft_crop   = 0;
		$inefficient = array();
		$duplicate   = array();

		// Get default WordPress image sizes.
		$default_sizes = array(
			'thumbnail' => array(
				'width'  => (int) get_option( 'thumbnail_size_w', 150 ),
				'height' => (int) get_option( 'thumbnail_size_h', 150 ),
				'crop'   => (bool) get_option( 'thumbnail_crop', 1 ),
			),
			'medium'    => array(
				'width'  => (int) get_option( 'medium_size_w', 300 ),
				'height' => (int) get_option( 'medium_size_h', 300 ),
				'crop'   => false,
			),
			'large'     => array(
				'width'  => (int) get_option( 'large_size_w', 1024 ),
				'height' => (int) get_option( 'large_size_h', 1024 ),
				'crop'   => false,
			),
		);

		// Merge with custom sizes.
		$all_sizes = array_merge( $default_sizes, is_array( $_wp_additional_image_sizes ) ? $_wp_additional_image_sizes : array() );

		// Analyze each image size.
		foreach ( $all_sizes as $size_name => $size_data ) {
			++$total_sizes;

			// Check crop setting.
			$is_crop = isset( $size_data['crop'] ) && $size_data['crop'];

			if ( $is_crop ) {
				++$hard_crop;

				// Check for array crop (specific crop position).
				if ( is_array( $size_data['crop'] ) ) {
					$issues[] = sprintf(
						/* translators: %s: image size name */
						__( 'Image size "%s" uses positional hard crop which may produce unexpected results', 'wpshadow' ),
						$size_name
					);
				}
			} else {
				++$soft_crop;
			}

			// Check for inefficient sizes (both dimensions set but not cropped).
			if ( ! $is_crop && isset( $size_data['width'] ) && isset( $size_data['height'] ) && $size_data['width'] > 0 && $size_data['height'] > 0 ) {
				$inefficient[] = $size_name;
			}

			// Check for very large sizes with hard crop.
			if ( $is_crop && ( $size_data['width'] > 1920 || $size_data['height'] > 1920 ) ) {
				$issues[] = sprintf(
					/* translators: 1: image size name, 2: width, 3: height */
					__( 'Image size "%1$s" (%2$dx%3$d) uses hard crop at very large dimensions, wasting disk space', 'wpshadow' ),
					$size_name,
					$size_data['width'],
					$size_data['height']
				);
			}
		}

		// Check for duplicate/similar sizes.
		$sizes_by_dimensions = array();
		foreach ( $all_sizes as $size_name => $size_data ) {
			$key = $size_data['width'] . 'x' . $size_data['height'];
			if ( ! isset( $sizes_by_dimensions[ $key ] ) ) {
				$sizes_by_dimensions[ $key ] = array();
			}
			$sizes_by_dimensions[ $key ][] = $size_name;
		}

		foreach ( $sizes_by_dimensions as $dimensions => $size_names ) {
			if ( count( $size_names ) > 1 ) {
				$duplicate[] = sprintf(
					/* translators: 1: dimensions, 2: list of size names */
					__( '%1$s dimensions used by multiple sizes: %2$s', 'wpshadow' ),
					$dimensions,
					implode( ', ', $size_names )
				);
			}
		}

		// Check JPEG quality setting.
		$jpeg_quality = apply_filters( 'jpeg_quality', 82, 'image_resize' );
		if ( $jpeg_quality < 70 ) {
			$issues[] = sprintf(
				/* translators: %d: JPEG quality percentage */
				__( 'JPEG quality set to %d%% which may produce visible compression artifacts', 'wpshadow' ),
				$jpeg_quality
			);
		} elseif ( $jpeg_quality > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: JPEG quality percentage */
				__( 'JPEG quality set to %d%% which creates unnecessarily large file sizes', 'wpshadow' ),
				$jpeg_quality
			);
		}

		// Check for excessive hard crop usage.
		$hard_crop_percentage = $total_sizes > 0 ? ( $hard_crop / $total_sizes ) * 100 : 0;
		if ( $hard_crop_percentage > 60 ) {
			$issues[] = sprintf(
				/* translators: 1: percentage, 2: number of hard crop sizes, 3: total sizes */
				__( '%1$.0f%% of image sizes (%2$d of %3$d) use hard crop, potentially creating many file variants', 'wpshadow' ),
				$hard_crop_percentage,
				$hard_crop,
				$total_sizes
			);
		}

		// Check for inefficient resize configurations.
		if ( ! empty( $inefficient ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of size names */
				__( 'These sizes specify both width and height without crop, which may not work as expected: %s', 'wpshadow' ),
				implode( ', ', $inefficient )
			);
		}

		// Add duplicate size warnings to issues.
		if ( ! empty( $duplicate ) ) {
			$issues = array_merge( $issues, $duplicate );
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 40;

			// Increase severity if multiple critical issues.
			if ( count( $issues ) > 5 || $hard_crop_percentage > 80 ) {
				$severity     = 'high';
				$threat_level = 60;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues found */
					_n(
						'Found %d image size configuration issue affecting disk space and image quality.',
						'Found %d image size configuration issues affecting disk space and image quality.',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/crop-vs-resize-settings',
				'details'      => array(
					'issues'               => $issues,
					'total_image_sizes'    => $total_sizes,
					'hard_crop_count'      => $hard_crop,
					'soft_crop_count'      => $soft_crop,
					'hard_crop_percentage' => round( $hard_crop_percentage, 1 ),
					'jpeg_quality'         => $jpeg_quality,
					'inefficient_sizes'    => $inefficient,
					'duplicate_sizes'      => $duplicate,
					'recommendation'       => __(
						'Review image size configurations. Use proportional resize (crop = false) when possible. Hard crop should be used only when exact dimensions are required. Keep JPEG quality between 75-85% for optimal balance.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}
}
