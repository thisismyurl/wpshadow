<?php
/**
 * Category Archives Schema Not Implemented Diagnostic
 *
 * Checks if category archive schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category Archives Schema Not Implemented Diagnostic Class
 *
 * Detects missing category archive schema.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Category_Archives_Schema_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'category-archives-schema-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Category Archives Schema Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if category archive schema is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for category archive schema
		if ( ! has_filter( 'wp_head', 'output_category_archive_schema' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Category archive schema is not implemented. Add CollectionPage schema to category archive pages for better semantic understanding.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/category-archives-schema-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
