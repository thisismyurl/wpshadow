<?php
/**
 * Local SEO Schema Markup Not Configured Diagnostic
 *
 * Checks if local SEO schema is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local SEO Schema Markup Not Configured Diagnostic Class
 *
 * Detects missing local SEO schema.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Local_SEO_Schema_Markup_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'local-seo-schema-markup-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Local SEO Schema Markup Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if local SEO schema is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for local SEO schema filter
		if ( ! has_filter( 'wpseo_schema_organization' ) && ! has_filter( 'jetpack_enable_open_graph' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Local SEO schema markup is not configured. Add Organization, LocalBusiness, and location schema for local visibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/local-seo-schema-markup-not-configured',
			);
		}

		return null;
	}
}
