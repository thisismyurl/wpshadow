<?php
/**
 * Schema Markup Treatment
 *
 * Checks if theme includes proper Schema.org structured data markup.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema Markup Treatment Class
 *
 * Verifies that the theme includes proper Schema.org structured data
 * for better SEO and rich snippets.
 *
 * @since 0.6093.1200
 */
class Treatment_Schema_Markup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema.org Structured Data';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme includes proper Schema.org structured data markup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the schema markup treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if schema issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\SEO\Diagnostic_Schema_Markup' );
	}
}
