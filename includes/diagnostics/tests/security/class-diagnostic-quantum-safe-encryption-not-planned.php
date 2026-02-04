<?php
/**
 * Quantum Safe Encryption Not Planned Diagnostic
 *
 * Checks quantum safe encryption planning.
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
 * Diagnostic_Quantum_Safe_Encryption_Not_Planned Class
 *
 * Performs diagnostic check for Quantum Safe Encryption Not Planned.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Quantum_Safe_Encryption_Not_Planned extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'quantum-safe-encryption-not-planned';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Quantum Safe Encryption Not Planned';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks quantum safe encryption planning';

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
		if (   !get_option('quantum_safe_plan_date' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Quantum safe encryption not planned. Begin planning post-quantum cryptography migration now before quantum computers become viable threats to current encryption.',
						'severity'   =>   'low',
						'threat_level'   =>   5,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/quantum-safe-encryption-not-planned'
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
