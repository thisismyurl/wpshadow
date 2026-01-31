<?php
/**
 * API Throttling Not Configured Diagnostic
 *
 * Checks if API throttling is configured.
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
 * API Throttling Not Configured Diagnostic Class
 *
 * Detects missing API throttling.
 *
 * @since 1.2601.2352
 */
class Diagnostic_API_Throttling_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-throttling-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Throttling Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API throttling is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for REST API rate limiting
		if ( ! has_filter( 'rest_authentication_errors', 'check_api_rate_limit' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API throttling is not configured. Limit REST API requests to 60 per minute per IP to prevent brute force attacks and ensure fair resource usage.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/api-throttling-not-configured',
			);
		}

		return null;
	}
}
