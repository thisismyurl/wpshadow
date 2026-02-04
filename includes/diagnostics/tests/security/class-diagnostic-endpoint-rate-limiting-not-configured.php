<?php
/**
 * Endpoint Rate Limiting Not Configured Diagnostic
 *
 * Checks endpoint rate limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Endpoint_Rate_Limiting_Not_Configured Class
 *
 * Performs diagnostic check for Endpoint Rate Limiting Not Configured.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Endpoint_Rate_Limiting_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'endpoint-rate-limiting-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Endpoint Rate Limiting Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks endpoint rate limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'apply_endpoint_rate_limiting' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Endpoint rate limiting not configured. Implement per-endpoint rate limits based on IP and user.',
						'severity'   =>   'high',
						'threat_level'   =>   65,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/endpoint-rate-limiting-not-configured'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
