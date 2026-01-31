<?php
/**
 * API Rate Limiting Not Configured Diagnostic
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
 * API Rate Limiting Not Configured Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * @since 1.2601.2352
 */
class Diagnostic_API_Rate_Limiting_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting Not Configured';

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
		// Check for rate limiting
		if ( ! has_filter( 'rest_request_before_callbacks', 'wp_rest_rate_limit' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Implement rate limiting on REST API endpoints to prevent abuse and DDoS attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-rate-limiting-not-configured',
			);
		}

		return null;
	}
}
