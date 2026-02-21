<?php
/**
 * XML Sitemap For Video Not Generated Treatment
 *
 * Checks if video sitemap is generated.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap For Video Not Generated Treatment Class
 *
 * Detects missing video sitemap.
 *
 * @since 1.6030.2352
 */
class Treatment_XML_Sitemap_For_Video_Not_Generated extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-for-video-not-generated';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap For Video Not Generated';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if video sitemap is generated';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XML_Sitemap_For_Video_Not_Generated' );
	}
}
