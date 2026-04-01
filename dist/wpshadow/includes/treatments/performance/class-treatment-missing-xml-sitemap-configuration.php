<?php
/**
 * Missing XML Sitemap Configuration Treatment
 *
 * Tests for XML sitemap availability.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing XML Sitemap Configuration Treatment Class
 *
 * Tests for XML sitemap availability.
 *
 * @since 0.6093.1200
 */
class Treatment_Missing_XML_Sitemap_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-xml-sitemap-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing XML Sitemap Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for XML sitemap availability';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Missing_XML_Sitemap_Configuration' );
	}
}
