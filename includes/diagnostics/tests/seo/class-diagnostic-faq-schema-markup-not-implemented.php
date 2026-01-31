<?php
/**
 * FAQ Schema Markup Not Implemented Diagnostic
 *
 * Checks if FAQ schema is implemented.
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
 * FAQ Schema Markup Not Implemented Diagnostic Class
 *
 * Detects missing FAQ schema.
 *
 * @since 1.2601.2352
 */
class Diagnostic_FAQ_Schema_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'faq-schema-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Markup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if FAQ schema is implemented';

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
		// Check for FAQ schema
		if ( ! is_plugin_active( 'faq-schema/faq-schema.php' ) && ! has_filter( 'wp_head', 'output_faq_schema' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'FAQ schema markup is not implemented. Add FAQ schema to display commonly asked questions in search results.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/faq-schema-markup-not-implemented',
			);
		}

		return null;
	}
}
