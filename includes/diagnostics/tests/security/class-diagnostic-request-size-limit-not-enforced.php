<?php
/**
 * Request Size Limit Not Enforced Diagnostic
 *
 * Checks request limits.
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
 * Diagnostic_Request_Size_Limit_Not_Enforced Class
 *
 * Performs diagnostic check for Request Size Limit Not Enforced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Request_Size_Limit_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'request-size-limit-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Request Size Limit Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks request limits';

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
						'enforce_request_size_limit' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Request size limit not enforced. Set max upload size and POST size limits to prevent resource exhaustion.',
						'severity'   =>   'high',
						'threat_level'   =>   60,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/request-size-limit-not-enforced'
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
