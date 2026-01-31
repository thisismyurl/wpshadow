<?php
/**
 * Rate Limiting Not Configured For API Diagnostic
 *
 * Checks if API rate limiting is configured.
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
 * Rate Limiting Not Configured For API Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Rate_Limiting_Not_Configured_For_API extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rate-limiting-not-configured-for-api';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rate Limiting Not Configured For API';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API rate limiting is configured';

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
		// Check if rate limiting is set
		if ( ! has_filter( 'rest_request_before_callbacks', 'check_api_rate_limit' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Implement rate limiting to prevent abuse and ensure fair API access.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rate-limiting-not-configured-for-api',
			);
		}

		return null;
	}
}
