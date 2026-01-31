<?php
/**
 * REST API Endpoint Documentation Not Generated Diagnostic
 *
 * Checks if REST API documentation is generated.
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
 * REST API Endpoint Documentation Not Generated Diagnostic Class
 *
 * Detects missing REST API documentation.
 *
 * @since 1.2601.2352
 */
class Diagnostic_REST_API_Endpoint_Documentation_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-endpoint-documentation-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Endpoint Documentation Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API documentation is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for REST API documentation
		if ( ! has_filter( 'rest_api_init', 'wp_rest_documentation' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API endpoint documentation is not generated. Document all REST API endpoints for developers who use your API.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-endpoint-documentation-not-generated',
			);
		}

		return null;
	}
}
