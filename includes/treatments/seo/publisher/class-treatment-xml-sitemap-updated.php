<?php
/**
 * XML Sitemap Updated Treatment
 *
 * Checks if XML sitemap is current and valid.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Updated Treatment Class
 *
 * Verifies that the XML sitemap is current, valid, and accessible
 * to search engines.
 *
 * @since 1.6035.1300
 */
class Treatment_XML_Sitemap_Updated extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-updated';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Updated';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML sitemap is current and valid';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the XML sitemap treatment check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if sitemap issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XML_Sitemap_Updated' );
	}
}
