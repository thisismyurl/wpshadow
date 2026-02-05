<?php
/**
 * Schema BreadcrumbList Markup Not Implemented Treatment
 *
 * Checks if breadcrumb schema is implemented.
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
 * Schema BreadcrumbList Markup Not Implemented Treatment Class
 *
 * Detects missing breadcrumb schema.
 *
 * @since 1.6030.2352
 */
class Treatment_Schema_BreadcrumbList_Markup_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-breadcrumblist-markup-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema BreadcrumbList Markup Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if breadcrumb schema is implemented';

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
