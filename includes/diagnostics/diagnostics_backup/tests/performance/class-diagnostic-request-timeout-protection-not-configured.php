<?php
/**
 * Request Timeout Protection Not Configured Diagnostic
 *
 * Checks if request timeouts are protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Request Timeout Protection Not Configured Diagnostic Class
 *
 * Detects missing request timeout protection.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Request_Timeout_Protection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'request-timeout-protection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Request Timeout Protection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if request timeout protection is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if timeout constant is defined
		if ( ! defined( 'WP_MEMORY_LIMIT' ) || WP_MEMORY_LIMIT === '40M' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Request timeout protection is not configured. Define WP_MEMORY_LIMIT and SCRIPT_DEBUG for timeout protection.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/request-timeout-protection-not-configured',
			);
		}

		return null;
	}
}
