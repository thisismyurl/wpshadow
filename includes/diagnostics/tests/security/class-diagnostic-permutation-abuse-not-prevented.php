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
		if (   !has_filter('init',
						'prevent_permutation_abuse' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Permutation abuse not prevented. Implement account lockout after failed login attempts to prevent credential stuffing.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/permutation-abuse-not-prevented'
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
