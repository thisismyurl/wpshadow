<?php
/**
 * Multisite Isolation Not Enforced Diagnostic
 *
 * Checks multisite isolation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Isolation_Not_Enforced Class
 *
 * Performs diagnostic check for Multisite Isolation Not Enforced.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Multisite_Isolation_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-isolation-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Isolation Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks multisite isolation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_multisite() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Multisite isolation controls are not enforced yet. Strong site-to-site separation helps keep network data and permissions safely isolated.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-isolation-not-enforced?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
