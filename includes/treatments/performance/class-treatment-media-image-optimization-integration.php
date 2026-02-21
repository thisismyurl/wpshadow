<?php
/**
 * Media Image Optimization Integration Treatment
 *
 * Tests for image optimization plugin integration and validates
 * compression settings for optimal file sizes.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1545
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Image_Optimization_Integration Class
 *
 * Ensures image optimization plugins are configured properly
 * and images are being compressed efficiently.
 *
 * @since 1.6033.1545
 */
class Treatment_Media_Image_Optimization_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-optimization-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests image optimization plugin configuration and compression';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Image_Optimization_Integration' );
	}
}
