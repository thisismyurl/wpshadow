<?php
/**
 * Permutation Abuse Not Prevented Diagnostic
 *
 * Checks permutation abuse.
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
 * Diagnostic_Permutation_Abuse_Not_Prevented Class
 *
 * Performs diagnostic check for Permutation Abuse Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Permutation_Abuse_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permutation-abuse-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permutation Abuse Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks permutation abuse';

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
		// Check for account lockout after failed login attempts.
		// Permutation/brute force attacks try many password combinations.
		// Account lockout prevents unlimited attempts.

		$lockout_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'wordfence/wordfence.php',
			'jetpack/jetpack.php',
			'iThemes-security/iThemesSecurityPlugin.php',
		);

		$has_lockout = false;
		foreach ( $lockout_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_lockout = true;
				break;
			}
		}

		// Check if a custom filter exists for login attempt rate limiting.
		if ( ! $has_lockout && has_filter( 'authenticate' ) ) {
			// Might have custom implementation.
			$has_lockout = true;
		}

		if ( ! $has_lockout ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site allows unlimited login attempts (like leaving your door unlocked with no limit on how many times someone can try your passwords). Attackers use automated tools to try thousands of password combinations. Account lockout limits login attempts to a few per minute, making brute force attacks impossible. Install a security plugin like Limit Login Attempts Reloaded to enable this protection.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/permutation-abuse-not-prevented',
				'context'      => array(
					'login_lockout_enabled' => false,
					'recommended_plugin'    => 'Limit Login Attempts Reloaded',
				),
			);
		}

		// Lockout protection is in place.
		return null;
	}
}
