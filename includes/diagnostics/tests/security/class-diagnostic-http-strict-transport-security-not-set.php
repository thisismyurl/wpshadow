<?php
/**
 * HTTP Strict Transport Security Not Set Diagnostic
 *
 * Checks HSTS.
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
 * Diagnostic_HTTP_Strict_Transport_Security_Not_Set Class
 *
 * Performs diagnostic check for Http Strict Transport Security Not Set.
 *
 * @since 1.6033.2033
 */
class Diagnostic_HTTP_Strict_Transport_Security_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-strict-transport-security-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Strict Transport Security Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks HSTS';

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
						'set_hsts_header' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('HSTS header not set. Add Strict-Transport-Security header to enforce HTTPS for all connections.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/http-strict-transport-security-not-set'
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
