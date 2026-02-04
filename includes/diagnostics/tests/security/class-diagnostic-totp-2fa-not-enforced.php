<?php
/**
 * TOTP 2FA Not Enforced Diagnostic
 *
 * Checks TOTP 2FA.
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
 * Diagnostic_TOTP_2FA_Not_Enforced Class
 *
 * Performs diagnostic check for Totp 2fa Not Enforced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_TOTP_2FA_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'totp-2fa-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'TOTP 2FA Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks TOTP 2FA';

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
		if (   !get_option('totp_2fa_enforced' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('TOTP 2FA not enforced for admin accounts. Use libraries like PHPGangsta_GoogleAuthenticator to enable time-based one-time passwords.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/totp-2fa-not-enforced'
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
