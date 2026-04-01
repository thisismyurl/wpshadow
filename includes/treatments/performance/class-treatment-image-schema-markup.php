<?php
/**
 * Image Schema Markup Treatment
 *
 * Tests if proper schema markup is added to images.
 * Validates structured data for SEO purposes.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Schema Markup Treatment Class
 *
 * Checks if images have proper schema.org structured data markup
 * for improved SEO and rich search results.
 *
 * @since 0.6093.1200
 */
class Treatment_Image_Schema_Markup extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-schema-markup';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Schema Markup';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if proper schema markup is added to images';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if image schema markup (schema.org/ImageObject) is being
	 * added to images on the frontend.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Schema_Markup' );
	}
}
