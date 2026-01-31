<?php
/**
 * JSON Structured Data Markup Not Implemented Diagnostic
 *
 * Checks if JSON structured data is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JSON Structured Data Markup Not Implemented Diagnostic Class
 *
 * Detects missing JSON structured data.
 *
 * @since 1.2601.2352
 */
class Diagnostic_JSON_Structured_Data_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-structured-data-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JSON Structured Data Markup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JSON structured data is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for JSON-LD schema markup
		if ( ! has_filter( 'wp_head', 'add_json_ld_markup' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'JSON structured data markup is not implemented. Add schema.org markup for organization, breadcrumbs, products, and reviews to enable rich snippets in search results.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/json-structured-data-markup-not-implemented',
			);
		}

		return null;
	}
}
