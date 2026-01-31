<?php
/**
 * HTTP Status Code Monitoring Not Implemented Diagnostic
 *
 * Checks if HTTP status code monitoring is implemented.
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
 * HTTP Status Code Monitoring Not Implemented Diagnostic Class
 *
 * Detects missing HTTP status code monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_HTTP_Status_Code_Monitoring_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-status-code-monitoring-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Status Code Monitoring Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP status code monitoring is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for HTTP status code tracking
		if ( ! has_filter( 'wp_headers', 'log_http_status' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP status code monitoring is not implemented. Track 4xx and 5xx errors to identify broken links, missing resources, and server issues that impact user experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http-status-code-monitoring-not-implemented',
			);
		}

		return null;
	}
}
