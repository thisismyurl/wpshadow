<?php
/**
 * Media Sitemap Generation Treatment
 *
 * Verifies media files are included in XML sitemaps
 * and checks image sitemap functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Sitemap_Generation Class
 *
 * Checks for image sitemap providers or SEO plugin support.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Sitemap_Generation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-sitemap-generation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Sitemap Generation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media files are included in XML sitemaps';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Sitemap_Generation' );
	}
}
