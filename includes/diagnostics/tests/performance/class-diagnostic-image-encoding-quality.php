<?php
/**
 * Image Encoding Quality Diagnostic
 *
 * Analyzes JPEG/PNG encoding quality settings and optimization.
 *
 * @since   1.6033.2125
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Encoding Quality Diagnostic
 *
 * Evaluates image compression quality settings and identifies optimization opportunities.
 *
 * @since 1.6033.2125
 */
class Diagnostic_Image_Encoding_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-encoding-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Encoding Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes JPEG/PNG encoding quality settings and optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2125
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get WordPress JPEG quality setting
		$jpeg_quality = apply_filters( 'jpeg_quality', 82 );
		$wp_quality   = apply_filters( 'wp_editor_set_quality', $jpeg_quality );

		// Check if quality is set too high (unnecessary file size)
		if ( $wp_quality > 90 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current quality setting */
					__( 'JPEG quality set to %d (very high). Quality 80-85 provides excellent results with 40%% smaller file sizes.', 'wpshadow' ),
					$wp_quality
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/image-encoding-quality',
				'meta'         => array(
					'current_quality'  => $wp_quality,
					'recommended'      => 82,
					'recommendation'   => 'Set JPEG quality to 80-85 for optimal balance',
					'impact_estimate'  => '30-40% file size reduction with minimal quality loss',
					'filter_name'      => 'jpeg_quality',
					'quality_guide'    => array(
						'90-100: Unnecessary, huge files',
						'80-90: Excellent quality, reasonable size',
						'70-80: Good quality, good compression',
						'60-70: Acceptable quality, high compression',
						'<60: Visible artifacts',
					),
				),
			);
		}

		// Check if quality is set too low (visible artifacts)
		if ( $wp_quality < 70 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current quality setting */
					__( 'JPEG quality set to %d (too low). This may cause visible compression artifacts. Recommended: 80-85.', 'wpshadow' ),
					$wp_quality
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/image-encoding-quality',
				'meta'         => array(
					'current_quality' => $wp_quality,
					'recommended'     => 82,
					'recommendation'  => 'Increase JPEG quality to 80-85',
					'quality_issue'   => 'Compression artifacts may be visible',
				),
			);
		}

		return null;
	}
}
