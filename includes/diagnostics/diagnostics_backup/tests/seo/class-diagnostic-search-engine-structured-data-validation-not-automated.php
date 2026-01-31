<?php
/**
 * Search Engine Structured Data Validation Not Automated Diagnostic
 *
 * Checks if structured data validation is automated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Engine Structured Data Validation Not Automated Diagnostic Class
 *
 * Detects missing automated validation.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Search_Engine_Structured_Data_Validation_Not_Automated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-structured-data-validation-not-automated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Structured Data Validation Not Automated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if structured data validation is automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for schema validation filter
		if ( ! has_filter( 'wpseo_schema_article' ) && ! has_filter( 'jetpack_enable_open_graph' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Structured data validation is not automated. Use Google Search Console or schema testing tools regularly.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/search-engine-structured-data-validation-not-automated',
			);
		}

		return null;
	}
}
