<?php
/**
 * JSON-LD Markup Not Implemented For Organization Diagnostic
 *
 * Checks if organization schema is implemented.
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
 * JSON-LD Markup Not Implemented For Organization Diagnostic Class
 *
 * Detects missing organization schema.
 *
 * @since 1.2601.2352
 */
class Diagnostic_JSON_LD_Markup_Not_Implemented_For_Organization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-ld-markup-not-implemented-for-organization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JSON-LD Markup Not Implemented For Organization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if organization schema is implemented';

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
		// Check for schema plugin
		if ( ! is_plugin_active( 'schema-and-structured-data-for-json-ld/schema-plugin.php' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Organization JSON-LD markup is not implemented. Add Organization schema to help search engines understand your business.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/json-ld-markup-not-implemented-for-organization',
			);
		}

		return null;
	}
}
