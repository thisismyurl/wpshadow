<?php
/**
 * Image Title Generation Treatment
 *
 * Tests automatic title generation from filenames.
 * Validates title sanitization and SEO implications.
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
 * Image Title Generation Treatment Class
 *
 * Checks if image titles are being properly generated from filenames
 * and whether they follow SEO best practices.
 *
 * @since 0.6093.1200
 */
class Treatment_Image_Title_Generation extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-title-generation';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Title Generation';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic title generation from filenames';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes image titles to check if they're properly formatted,
	 * meaningful, and SEO-friendly vs. raw filenames.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Title_Generation' );
	}
}
