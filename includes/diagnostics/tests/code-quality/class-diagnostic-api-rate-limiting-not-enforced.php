<?php
/**
 * API Rate Limiting Not Enforced Diagnostic
 *
 * Checks rate limiting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_API_Rate_Limiting_Not_Enforced Class
 *
 * Performs diagnostic check for Api Rate Limiting Not Enforced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_API_Rate_Limiting_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks rate limiting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('rest_dispatch_request',
						'check_rate_limit' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('API rate limiting not enforced. Implement per-client request throttling to prevent abuse and DoS attacks.',
						'severity'   =>   'high',
						'threat_level'   =>   60,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/api-rate-limiting-not-enforced'
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
