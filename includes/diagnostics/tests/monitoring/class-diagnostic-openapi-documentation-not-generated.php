<?php
/**
 * OpenAPI Documentation Not Generated Diagnostic
 *
 * Checks if OpenAPI documentation is generated.
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
 * OpenAPI Documentation Not Generated Diagnostic Class
 *
 * Detects missing OpenAPI documentation.
 *
 * @since 1.6030.2352
 */
class Diagnostic_OpenAPI_Documentation_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'openapi-documentation-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OpenAPI Documentation Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if OpenAPI documentation is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for OpenAPI/Swagger documentation
		if ( ! file_exists( ABSPATH . 'openapi.yaml' ) && ! file_exists( ABSPATH . 'openapi.json' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'OpenAPI documentation is not generated. Create OpenAPI/Swagger specification for REST APIs to enable interactive documentation and third-party integrations.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/openapi-documentation-not-generated',
			);
		}

		return null;
	}
}
