<?php
/**
 * Kill Idle Sessions Implementation Diagnostic
 *
 * Checks if idle sessions are terminated.
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
 * Kill Idle Sessions Implementation Diagnostic Class
 *
 * Detects missing idle session termination.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Kill_Idle_Sessions_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'kill-idle-sessions-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Kill Idle Sessions Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if idle sessions are terminated';

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
		// Check if idle session timeout is configured
		if ( ! has_filter( 'auth_cookie_life', 'set_idle_session_timeout' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Idle session termination is not implemented. Set session timeout to 30 minutes to automatically log out inactive users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/kill-idle-sessions-implementation',
			);
		}

		return null;
	}
}
