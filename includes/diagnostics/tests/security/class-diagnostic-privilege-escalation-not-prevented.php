<?php
/**
 * Privilege Escalation Not Prevented Diagnostic
 *
 * Checks privilege escalation.
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
 * Diagnostic_Privilege_Escalation_Not_Prevented Class
 *
 * Performs diagnostic check for Privilege Escalation Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Privilege_Escalation_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privilege-escalation-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privilege Escalation Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks privilege escalation';

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
						'prevent_privilege_escalation' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Privilege escalation not prevented. Strictly validate user roles and never trust user input for capability checks.',
						'severity'   =>   'high',
						'threat_level'   =>   90,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/privilege-escalation-not-prevented'
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
