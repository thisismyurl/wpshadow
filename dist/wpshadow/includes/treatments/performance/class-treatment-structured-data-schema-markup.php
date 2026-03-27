<?php
/**
 * Structured Data Schema Markup Treatment
 *
 * Checks if structured data (JSON-LD, Schema.org) is properly implemented
 * to help search engines understand page content.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Structured Data Schema Markup Treatment Class
 *
 * Verifies schema.org implementation:
 * - JSON-LD markup presence
 * - Organization schema
 * - Product schema
 * - Article schema
 *
 * @since 1.6093.1200
 */
class Treatment_Structured_Data_Schema_Markup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'structured-data-schema-markup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data Schema Markup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for structured data markup implementation for SEO';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Structured_Data_Schema_Markup' );
	}
}
