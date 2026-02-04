<?php
/**
 * Schema BreadcrumbList Markup Not Implemented Diagnostic
 *
 * Checks if breadcrumb schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema BreadcrumbList Markup Not Implemented Diagnostic Class
 *
 * Detects missing breadcrumb schema.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Schema_BreadcrumbList_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-breadcrumblist-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema BreadcrumbList Markup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if breadcrumb schema is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for breadcrumb schema
		if ( ! has_filter( 'wp_head', 'wp_add_breadcrumb_schema' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Schema BreadcrumbList markup is not implemented. Add breadcrumb schema for better search result presentation and site navigation clarity.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/schema-breadcrumblist-markup-not-implemented',
			);
		}

		return null;
	}
}
