<?php
/**
 * Schema Markup and Structured Data Treatment
 *
 * Tests if site implements proper JSON-LD schema markup for search engine understanding.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1460
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema Markup and Structured Data Treatment Class
 *
 * Validates that the site implements proper JSON-LD schema markup
 * including Organization, Article, Product, and breadcrumb schemas.
 *
 * @since 1.7034.1460
 */
class Treatment_Schema_Markup_Structured_Data extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-structured-data';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup and Structured Data';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site implements proper JSON-LD schema markup for search engine understanding';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests schema markup implementation including Organization schema,
	 * Article schema, Product schema, and breadcrumb schema.
	 *
	 * @since  1.7034.1460
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Schema_Markup_Structured_Data' );
	}
}
