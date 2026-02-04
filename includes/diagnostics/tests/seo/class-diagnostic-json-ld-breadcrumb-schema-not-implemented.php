<?php
/**
 * JSON-LD Breadcrumb Schema Not Implemented Diagnostic
 *
 * Checks if JSON-LD breadcrumb schema is implemented.
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
 * JSON-LD Breadcrumb Schema Not Implemented Diagnostic Class
 *
 * Detects missing JSON-LD breadcrumb schema.
 *
 * @since 1.6030.2352
 */
class Diagnostic_JSON_LD_Breadcrumb_Schema_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-ld-breadcrumb-schema-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JSON-LD Breadcrumb Schema Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JSON-LD breadcrumb schema is implemented';

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
		// Check for breadcrumb JSON-LD
		if ( ! has_filter( 'wp_head', 'output_breadcrumb_json_ld' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'JSON-LD breadcrumb schema is not implemented. Add BreadcrumbList JSON-LD schema to improve search result appearance with breadcrumb navigation.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/json-ld-breadcrumb-schema-not-implemented',
			);
		}

		return null;
	}
}
