<?php
/**
 * Large Size Configuration Diagnostic
 *
 * Validates large image size settings. Tests maximum dimensions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Large Size Configuration Diagnostic Class
 *
 * Detects when WordPress large image size dimensions are not properly configured.
 * WordPress automatically creates multiple image sizes when you upload an image.
 * The "large" size should be configured appropriately for your site's needs.
 *
 * @since 1.6032.1352
 */
class Diagnostic_Large_Size_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'large-size-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Large Size Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates large image size settings and tests maximum dimensions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if WordPress large image size settings are configured properly:
	 * - Checks if large_size_w and large_size_h are set
	 * - Validates dimensions are within reasonable limits (not 0, not excessively large)
	 * - Checks if both width and height are set to 0 (disabled)
	 *
	 * @since  1.6032.1352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get WordPress large image size settings
		$large_width  = (int) get_option( 'large_size_w', 1024 );
		$large_height = (int) get_option( 'large_size_h', 1024 );

		// Check if both dimensions are set to 0 (large size disabled)
		if ( 0 === $large_width && 0 === $large_height ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large image size is completely disabled (both width and height are 0). WordPress will not generate large-sized images, which may cause performance issues on the frontend when displaying full-resolution images.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/large-size-configuration',
				'family'       => self::$family,
				'meta'         => array(
					'current_width'  => $large_width,
					'current_height' => $large_height,
					'issue_type'     => 'disabled',
				),
			);
		}

		// Check if dimensions are excessively large (over 4000px)
		$max_reasonable = 4000;
		if ( $large_width > $max_reasonable || $large_height > $max_reasonable ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current width, 2: current height, 3: maximum recommended dimension */
					__( 'Large image size dimensions are excessively large (width: %1$dpx, height: %2$dpx). Dimensions over %3$dpx can cause memory issues during image processing and increase server load. Recommended maximum is %3$dpx for either dimension.', 'wpshadow' ),
					$large_width,
					$large_height,
					$max_reasonable
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/large-size-configuration',
				'family'       => self::$family,
				'meta'         => array(
					'current_width'   => $large_width,
					'current_height'  => $large_height,
					'max_recommended' => $max_reasonable,
					'issue_type'      => 'excessive',
				),
			);
		}

		// Check if dimensions are too small (less than 512px on both dimensions)
		$min_reasonable = 512;
		if ( ( $large_width > 0 && $large_width < $min_reasonable ) ||
			( $large_height > 0 && $large_height < $min_reasonable ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current width, 2: current height, 3: minimum recommended dimension */
					__( 'Large image size dimensions are too small (width: %1$dpx, height: %2$dpx). Dimensions less than %3$dpx may not be suitable for modern responsive designs. Recommended minimum is %3$dpx for at least one dimension.', 'wpshadow' ),
					$large_width,
					$large_height,
					$min_reasonable
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/large-size-configuration',
				'family'       => self::$family,
				'meta'         => array(
					'current_width'   => $large_width,
					'current_height'  => $large_height,
					'min_recommended' => $min_reasonable,
					'issue_type'      => 'too_small',
				),
			);
		}

		// All checks passed
		return null;
	}
}
