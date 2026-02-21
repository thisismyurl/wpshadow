<?php
/**
 * Session Replay Attacks Treatment
 *
 * Checks for timestamp validation in tokens, single-use tokens for sensitive
 * actions, and nonce replay protection.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since      1.6035.1550
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Replay Attacks Treatment Class
 *
 * Verifies proper token validation, nonce usage, and protection
 * against session replay attacks.
 *
 * @since 1.6035.1550
 */
class Treatment_Session_Replay extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_session_replay';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Replay Attacks';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tokens are timestamp-validated and single-use for sensitive actions';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1550
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Replay' );
	}
}
