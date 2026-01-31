<?php
/**
 * Expired Sessions Cleanup Not Implemented Diagnostic
 *
 * Checks if expired sessions are cleaned up.
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
 * Expired Sessions Cleanup Not Implemented Diagnostic Class
 *
 * Detects unmanaged session cleanup.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Expired_Sessions_Cleanup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'expired-sessions-cleanup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Expired Sessions Cleanup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if expired sessions are cleaned up';

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
		// Check if session cleanup is scheduled
		if ( ! wp_next_scheduled( 'wp_session_cleanup' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Expired sessions cleanup is not implemented. Schedule session cleanup to remove old session data and improve security.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/expired-sessions-cleanup-not-implemented',
			);
		}

		return null;
	}
}
