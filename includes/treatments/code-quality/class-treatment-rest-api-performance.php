<?php
/**
 * REST API Response Caching and Performance
 *
 * Validates REST API response caching and performance optimization.
 *
 * @since   1.2034.1615
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_REST_API_Performance Class
 *
 * Checks REST API response caching and performance issues.
 *
 * @since 1.2034.1615
 */
class Treatment_REST_API_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API response caching and performance optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest-api';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Performance' );
	}
}

/**
 * Get HTTP header value
 *
 * @param string $header Header name
 * @return string|false Header value or false
 */
function get_http_header( $header ) {
	$header = 'HTTP_' . strtoupper( str_replace( '-', '_', $header ) );
	return $_SERVER[ $header ] ?? false;
}
