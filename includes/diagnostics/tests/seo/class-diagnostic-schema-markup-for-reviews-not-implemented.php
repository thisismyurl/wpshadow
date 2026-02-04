<?php
/**
 * Schema Markup For Reviews Not Implemented Diagnostic
 *
 * Checks if review schema is implemented.
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
 * Schema Markup For Reviews Not Implemented Diagnostic Class
 *
 * Detects missing review schema.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Schema_Markup_For_Reviews_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-for-reviews-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup For Reviews Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if review schema is implemented';

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
		// Check for review schema plugin
		if ( ! is_plugin_active( 'schema-and-structured-data-for-json-ld/schema-plugin.php' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Review schema markup is not implemented. Add Review schema for better search visibility of product and business reviews.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/schema-markup-for-reviews-not-implemented',
			);
		}

		return null;
	}
}
