<?php
/**
 * Media Image Schema Markup Treatment
 *
 * Tests whether structured data for images is configured
 * via SEO plugins or schema filters.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Image_Schema_Markup Class
 *
 * Detects schema markup integration for images.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Image_Schema_Markup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-schema-markup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Schema Markup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if structured data is added for images';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Image_Schema_Markup' );
	}
}
