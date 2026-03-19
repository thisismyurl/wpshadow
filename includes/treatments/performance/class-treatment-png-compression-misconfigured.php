<?php
/**
 * PNG Compression Level Misconfigured Treatment
 *
 * Validates PNG compression level settings to ensure PNG files are
 * being optimized effectively for bandwidth and performance benefits.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PNG Compression Misconfigured Treatment Class
 *
 * Checks PNG compression settings in image optimization plugins.
 * PNG compression typically provides 10-40% file size reduction for
 * graphics and transparent images.
 *
 * Based on EWWW Image Optimizer test suite patterns (test-optimize.php lines 200-250).
 *
 * @since 1.6093.1200
 */
class Treatment_Png_Compression_Misconfigured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'png-compression-misconfigured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'PNG Compression Settings Misconfigured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates PNG compression level settings for optimal file size reduction';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks PNG compression settings in active optimization plugins.
	 * PNG compression provides significant file size savings.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Png_Compression_Misconfigured' );
	}
}
