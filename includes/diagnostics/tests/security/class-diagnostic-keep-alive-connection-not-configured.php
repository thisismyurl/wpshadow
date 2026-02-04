<?php
/**
 * Keep-Alive Connection Not Configured Diagnostic
 *
 * Checks keep-alive.
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
 * Diagnostic_Keep_Alive_Connection_Not_Configured Class
 *
 * Performs diagnostic check for Keep Alive Connection Not Configured.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Keep_Alive_Connection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keep-alive-connection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keep-Alive Connection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks keep-alive';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'enable_keep_alive' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Keep-Alive connection not configured. Enable persistent connections to reduce connection overhead.',
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/keep-alive-connection-not-configured'
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
